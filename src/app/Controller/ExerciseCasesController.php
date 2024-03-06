<?php
App::uses('AppController', 'Controller');
App::uses('Folder', 'Utility');

class ExerciseCasesController extends AppController
{

  public $components = array('Paginator');

  private $studentAuthorized = array('viewinput', 'viewoutput');
  private $professorAuthorized = array('add', 'delete', 'addbatch', 'edit', 'viewinput', 'viewoutput', 'toggleshowinput', 'toggleshowexpectedoutput', 'toggleshowuseroutput');

  public function isAuthorized($user = null)
  {
    $this->loadModel('User');
    $this->loadModel('Enrollment');
    $this->loadModel('Exercise');
    $this->ExerciseCase->recursive = -1;
    $this->Exercise->recursive = -1;
    $this->Enrollment->recursive = -1;
    $this->User->recursive = -1;

    if ($user['type'] >= $this->User->getAdminIndex()) {
      return true;
    }
    if (strtolower($this->request->params['action']) == "add" || strtolower($this->request->params['action']) == "addbatch") {
      $exercise = $this->Exercise->findById($this->request->params['pass'][0]);
      if ($this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $exercise['Exercise']['offering_id'])) {
        return true;
      }
      return false;
    }

    $case = $this->ExerciseCase->findById($this->request->params['pass'][0], array('ExerciseCase.exercise_id'));
    $exercise = $this->Exercise->findById($case['ExerciseCase']['exercise_id'], array('Exercise.offering_id'));
    $offering = $exercise['Exercise']['offering_id'];
    if ($user['type'] <= $this->User->getProfessorIndex()) {
      if (in_array(strtolower($this->request->params['action']), $this->professorAuthorized)) {
        if ($this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $offering)) {
          return true;
        }
      } else {
        return false;
      }
    }
    if ($user['type'] <= $this->User->getAdminIndex()) {
      if (in_array(strtolower($this->request->params['action']), $this->studentAuthorized)) {
        if ($this->Enrollment->isEnrolled($this->currentUser['email'], $offering)) {
          return true;
        }
      } else {
        return false;
      }
    }
  }

  public function viewInput($id = null)
  {
    if (!$this->ExerciseCase->exists($id)) {
      throw new NotFoundException(__('Invalid exercise case'));
    }
    $this->loadModel('Archive');
    $memoryStart = memory_get_usage();
    $this->layout = "ajax";

    $options = array('recursive' => -1, 'fields' => 'input, input_type,input_md5', 'conditions' => array('ExerciseCase.' . $this->ExerciseCase->primaryKey => $id));
    $exerciseCase = $this->ExerciseCase->find('first', $options);
    $exerciseCase['ExerciseCase']['input'] = $this->Archive->getExerciseCaseInputFromAwsS3($id);
    $memoryEnd = memory_get_usage();
    $memoryUsage = $memoryEnd - $memoryStart;
    if ($memoryUsage > 1000000) {
      unset($exerciseCase);
      $exerciseCase['ExerciseCase']['input'] = __('This case input is so big that can not be visualized in browser');
    }
    $this->set('exerciseCase', $exerciseCase);
  }
  public function viewOutput($id = null)
  {
    if (!$this->ExerciseCase->exists($id)) {
      throw new NotFoundException(__('Invalid exercise case'));
    }
    $this->loadModel('Archive');
    $memoryStart = memory_get_usage();
    $this->layout = "ajax";

    $options = array('recursive' => -1, 'fields' => 'output, output_type,abs_error,output_md5', 'conditions' => array('ExerciseCase.' . $this->ExerciseCase->primaryKey => $id));
    $exerciseCase = $this->ExerciseCase->find('first', $options);
    $exerciseCase['ExerciseCase']['output'] = $this->Archive->getExerciseCaseOutputFromAwsS3($id);
    $memoryEnd = memory_get_usage();
    $memoryUsage = $memoryEnd - $memoryStart;
    if ($memoryUsage > 1000000) {
      unset($exerciseCase);
      $exerciseCase['ExerciseCase']['output'] = __('This case output is so big that can not be visualized in browser');
      $exerciseCase['ExerciseCase']['output_type'] = 1;
    }
    $this->set('exerciseCase', $exerciseCase);
  }

  /**
   * add method
   *
   * @return void
   */
  public function add($id_exercise = null)
  {
    $this->layout = "template2015";
    $this->loadModel('Exercise');
    $this->loadModel('Archive');
    if (!$this->Exercise->exists($id_exercise)) {
      throw new NotFoundException(__('The selected exercise does not exists'));
    }
    $this->loadModel('Offering');
    $this->Exercise->recursive = -1;
    $this->Offering->recursive = -1;
    $this->Enrollment->recursive = -1;
    $exercise = $this->Exercise->find('first', array('conditions' => array('Exercise.id' => $id_exercise)));
    $offering = $this->Offering->findById($exercise['Exercise']['offering_id']);
    $exercise['Offering'] = $offering['Offering'];
    $this->loadModel('Enrollment');
    //Verifica se participa desta turma
    if ($this->currentUser['type'] < 3) {
      if (count($this->Enrollment->findByUserEmailAndOfferingId($this->currentUser['email'], $exercise['Exercise']['offering_id'])) == 0) {
        throw new NotFoundException(__('The selected exercise does not exists'));
      }
    }
    if ($exercise['Exercise']['type'] == 1) {
      $this->Session->setFlash(__('Sorry') . "! " . __("Add exercise cases is not possible in Simple File Exercises"));
      $this->redirect(array("controller" => "Exercises", "action" => "viewProfessor", $id_exercise));
    }
    if ($exercise['Exercise']['ghost']) {
      return $this->redirect(array('controller' => 'ExerciseCases', 'action' => 'add', $exercise['Exercise']['real_id']));
    }
    if ($this->request->is('post')) {
      $this->ExerciseCase->create();
      $this->request->data['ExerciseCase']['input_type'] = 1;
      $this->ExerciseCase->validator()->getField('output_type')->setRule('inList', array(
        'rule' => array('inList', array('1', '2', '3')),
        'message' => __("Invalid output type"),
        'required' => true
      ));
      if ($this->request->data['ExerciseCase']['output_type'] == 1) {
        $this->request->data['ExerciseCase']['output'] = $this->request->data['ExerciseCase']['outputText'];
      } elseif ($this->request->data['ExerciseCase']['output_type'] == 2) {
        $this->request->data['ExerciseCase']['output'] = $this->request->data['ExerciseCase']['outputNumber'];
        $this->request->data['ExerciseCase']['abs_error'] = $this->request->data['ExerciseCase']['outputError'];
      } elseif ($this->request->data['ExerciseCase']['output_type'] == 3) {
        $this->request->data['ExerciseCase']['output'] = $this->request->data['ExerciseCase']['outputBinary'];
      }

      $inputContent = $this->request->data['ExerciseCase']['input'];
      $outputContent = $this->request->data['ExerciseCase']['output'];
      $this->request->data['ExerciseCase']['input_md5'] = md5($inputContent);
      $this->request->data['ExerciseCase']['output_md5'] = md5($outputContent);
      $inputMeta = array("md5" => $this->request->data['ExerciseCase']['input_md5']);
      $outputMeta = array("md5" => $this->request->data['ExerciseCase']['output_md5']);
      $this->request->data['ExerciseCase']['maxmemsize'] = 4096;
      $this->request->data['ExerciseCase']['stacksize'] = 2048;
      $this->request->data['ExerciseCase']['file_size'] = 10000;
      $this->ExerciseCase->begin();
      if ($this->ExerciseCase->saveAll($this->request->data)) {
        $exerciseCaseId = $this->ExerciseCase->id;
        if (
          $this->Archive->saveExerciseCaseInputToAwsS3($exerciseCaseId, $inputContent, $inputMeta) === false ||
          $this->Archive->saveExerciseCaseOutputToAwsS3($exerciseCaseId, $outputContent, $outputMeta) === false
        ) {
          $this->Session->setFlash(__('The exercise case could not be saved. Please, try again.'));
          $this->ExerciseCase->rollback();
        } else {
          $this->ExerciseCase->commit();
          $this->Session->setFlash(__('The exercise case has been saved'), 'default', array(), 'success');

          if (isset($this->request->data['ExerciseCaseFile'])) {
            $this->ExerciseCase->recursive = -1;

            $options = array('conditions' => array('ExerciseCase.' . $this->ExerciseCase->primaryKey => $this->ExerciseCase->id));
            $exerciseCase = $this->ExerciseCase->find('first', $options);
            $exercise = $this->Exercise->findById($exerciseCase['ExerciseCase']['exercise_id'], array('id', 'offering_id'));
            $offering = $this->Offering->findById($exercise['Exercise']['offering_id'], array('id', 'course_id'));

            $files = $this->request->data['ExerciseCaseFile'];
            foreach ($files as $file) {
              if ($file['hash'] != "0") {
                $tmpKey = "tmp/inputfiles/" . $file['hash'] . "/" . $file['path'];
                if (!$this->Archive->copyExerciseCaseFilesToAwsS3($exerciseCase['ExerciseCase']['id'], $tmpKey, $file['path'])) {
                  $this->Session->setFlash(__('The exercise file was not moved'));
                }
              }
            }
          }
        }
        Log::register("Added an exercise case in Exercise #" . $id_exercise, $this->currentUser);
        $this->redirect(array('controller' => 'Exercises', 'action' => 'viewProfessor', $this->ExerciseCase->field('exercise_id')));
      } else {
        $this->Session->setFlash(__('The exercise case could not be saved. Please, try again.'));
      }
    }

    $timeoptions = array(
      '1' => __('1sec'),
      '2' => __('2sec'),
      '3' => __('3sec'),
      '4' => __('4sec'),
      '5' => __('5sec'),
      '10' => __('10sec'),
      '20' => __('20sec'),
      '30' => __('30sec'),
      '40' => __('40sec'),
      '50' => __('50sec'),
      '60' => __('1min'),
    );
    $breadcrumbs = array();
    $this->loadModel('Course');
    $this->Course->recursive = -1;
    $course = $this->Course->findById($exercise['Offering']['course_id'], array('fields' => 'code'));
    array_push($breadcrumbs, array('link' => array('controller' => 'offerings', 'action' => 'view', $exercise['Offering']['id']), 'text' => $course['Course']['code']));
    array_push($breadcrumbs, array('link' => array('controller' => 'exercises', 'action' => 'view', $exercise['Exercise']['id']), 'text' => $exercise['Exercise']['title']));
    array_push($breadcrumbs, array('link' => '#', 'text' => __("Add Case")));
    //        $this->set(compact('exercise','timeoptions','memsizeoptions','stacksizeoptions','blocksizeoptions','breadcrumbs'));
    $this->set(compact('exercise', 'timeoptions', 'breadcrumbs'));
    $this->render("form");
  }


  public function addBatch($id_exercise = null)
  {
    $this->layout = "template2015";
    $this->loadModel('Exercise');
    $this->loadModel('Archive');
    if (!$this->Exercise->exists($id_exercise)) {
      throw new NotFoundException(__('The selected exercise does not exists'));
    }
    $this->loadModel('Archive');
    $this->loadModel('Enrollment');
    $this->loadModel('Offering');
    $this->ExerciseCase->recursive = -1;
    $this->Exercise->recursive = -1;
    $this->Enrollment->recursive = -1;
    $this->Offering->recursive = -1;
    $exercise = $this->Exercise->find('first', array('conditions' => array('Exercise.id' => $id_exercise)));
    if ($exercise['Exercise']['type'] == 1) {
      $this->Session->setFlash(__('Sorry') . "! " . __("Add exercise cases in batch is not possible in Simple File Exercises"));
      $this->redirect(array("controller" => "Exercises", "action" => "viewProfessor", $id_exercise));
    }
    if ($exercise['Exercise']['type'] == 2) {
      $this->Session->setFlash(__('Sorry') . "! " . __("Add exercise cases in batch is not possible in R Statistical Software exercises"));
      $this->redirect(array("controller" => "Exercises", "action" => "viewProfessor", $id_exercise));
    }
    if ($exercise['Exercise']['ghost']) {
      return $this->redirect(array('controller' => 'ExerciseCases', 'action' => 'add', $exercise['Exercise']['real_id']));
    }
    $offering = $this->Offering->findById($exercise['Exercise']['offering_id']);
    $exercise['Offering'] = $offering['Offering'];
    //Verifica se participa desta turma
    if ($this->currentUser['type'] < 3) {
      if (count($this->Enrollment->findByUserEmailAndOfferingId($this->currentUser['email'], $exercise['Exercise']['offering_id'])) == 0) {
        throw new NotFoundException(__('The selected exercise does not exists'));
      }
    }

    if ($this->request->is('post')) {
      $this->request->data['ExerciseCase']['maxmemsize'] = 4096;
      $this->request->data['ExerciseCase']['stacksize'] = 2048;
      $this->request->data['ExerciseCase']['file_size'] = 10000;
      $exerciseConfig = $this->request->data['ExerciseCase'];
      if (isset($this->request->data['ExerciseCaseFile'])) {
        $addedCases = array();
        function sortFile($a, $b)
        {
          return strnatcmp($a['path'], $b['path']);
        }
        $files = $this->request->data['ExerciseCaseFile'];
        usort($files, "sortFile");
        foreach ($files as $fIn) {
          $elements = explode('.', $fIn['path']);
          $extIn = $elements[count($elements) - 1];
          unset($elements[count($elements) - 1]);
          $nameIn = implode('.', $elements);
          if ($extIn == "in") {
            //                                debug ("Name: ".$nameIn." Ext: ".$extIn." File: ".$fIn['path']);
            foreach ($files as $fOut) {
              $elements = explode('.', $fOut['path']);
              $extOut = $elements[count($elements) - 1];
              unset($elements[count($elements) - 1]);
              $nameOut = implode('.', $elements);
              if ($nameIn == $nameOut && $extOut == "out") {
                $exerciseCase = array();

                $inputContent = 'tmp/inputfiles/' . $fIn['hash'] . "/" . $fIn['path'];
                $outputContent = 'tmp/inputfiles/' . $fOut['hash'] . "/" . $fOut['path'];
                $exerciseCase['ExerciseCase']['input'] = "";
                $exerciseCase['ExerciseCase']['output'] = "";

                $exerciseCase['ExerciseCase'] = $exerciseConfig;
                $exerciseCase['ExerciseCase']['input_type'] = 1;
                if ($exerciseConfig['output_type'] == 2) {
                  $exerciseCase['ExerciseCase']['output_type'] = 2;
                  $exerciseCase['ExerciseCase']['abs_error'] = $exerciseConfig['outputError'];
                } else {
                  $exerciseCase['ExerciseCase']['output_type'] = 1;
                }

                $tmpInputMetadata = $this->Archive->getAwsFileMetadata('tmp/inputfiles/' . $fIn['hash'] . "/" . $fIn['path']);
                $tmpOutputMetadata = $this->Archive->getAwsFileMetadata('tmp/inputfiles/' . $fOut['hash'] . "/" . $fOut['path']);

                //                                        debug($exerciseCase);
                $this->ExerciseCase->begin();
                $this->ExerciseCase->create();
                $exerciseCase['ExerciseCase']['input_md5'] = isset($tmpInputMetadata["md5"]) ? $tmpInputMetadata["md5"] : null;
                $exerciseCase['ExerciseCase']['output_md5'] = isset($tmpOutputMetadata["md5"]) ? $tmpOutputMetadata["md5"] : null;
                if ($this->ExerciseCase->save($exerciseCase)) {
                  $exerciseCaseId = $this->ExerciseCase->id;

                  if (
                    $this->Archive->copyExerciseCaseInputToAwsS3($exerciseCaseId, $inputContent) === false ||
                    $this->Archive->copyExerciseCaseOutputToAwsS3($exerciseCaseId, $outputContent) === false
                  ) {
                    $this->ExerciseCase->rollback();
                  } else {
                    $this->ExerciseCase->commit();
                    array_push($addedCases, $nameIn);

                    foreach ($files as $fOther) {
                      $elements = explode('.', $fOther['path']);
                      $extOther = $elements[count($elements) - 1];
                      unset($elements[count($elements) - 1]);
                      $nameOther = implode('.', $elements);
                      if ($nameIn == $nameOther && $extOther != "out" && $extOther != "in") {
                        $this->loadModel('ExerciseCaseFile');
                        $tmpKey = "tmp/inputfiles/" . $fOther['hash'] . "/" . $fOther['path'];
                        //                                                $targetKey = "inputfiles/" . $exercise['Exercise']['id'] . "/" . $exerciseCaseId . "/" . $fOther['path'];
                        if (!$this->Archive->copyExerciseCaseFilesToAwsS3($exerciseCaseId, $tmpKey, $fOther['path'])) {
                          $this->Session->setFlash(__('The exercise case file could not be saved'));
                        } else {
                          $exerciseCaseFile = array('ExerciseCaseFile' => array());
                          $exerciseCaseFile['ExerciseCaseFile']['path'] = $fOther['path'];
                          $exerciseCaseFile['ExerciseCaseFile']['exercise_case_id'] = $exerciseCaseId;
                          $this->ExerciseCaseFile->create();
                          $this->ExerciseCaseFile->save($exerciseCaseFile);
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
        $casesNames = implode(";", $addedCases);
        Log::register("Added " . count($addedCases) . " exercise cases in Exercise #" . $id_exercise . " (using batch process)", $this->currentUser);
        $this->Session->setFlash(__('The exercise cases %s have been loaded and saved',  $casesNames), 'default', array(), 'success');
        return $this->redirect(array('controller' => 'Exercises', 'action' => 'viewProfessor', $exerciseConfig['exercise_id']));
      }
    }
    $timeoptions = array('1' => __('1sec'), '2' => __('2sec'), '3' => __('3sec'),);
    $memsizeoptions = array('1024' => '1024', '2048' => '2048', '4096' => '4096', '8192' => '8192', '16384' => '16384', '32768' => '32768');
    $stacksizeoptions = array('512' => '512', '1024' => '1024', '2048' => '2048', '4096' => '4096', '8192' => '8192');
    $blocksizeoptions = array(
      '500' => '500 blocos ou aprox. 256 kbytes', '1000' => '1000 blocos ou aprox. 512 kbytes', '5000' => '5000 blocos ou aprox. 2.44 Mbytes',
      '10000' => '10000 blocos ou aprox. 4.88 Mbytes', '100000' => '100000 blocos ou aprox. 48.83 Mbytes', '200000' => '200000 blocos ou aprox. 97.65 Mbytes'
    );

    $breadcrumbs = array();
    $this->loadModel('Course');
    $course = $this->Course->findById($exercise['Offering']['course_id'], array('fields' => 'code'));
    array_push($breadcrumbs, array('link' => array('controller' => 'offerings', 'action' => 'view', $exercise['Offering']['id']), 'text' => $course['Course']['code']));
    array_push($breadcrumbs, array('link' => array('controller' => 'exercises', 'action' => 'view', $exercise['Exercise']['id']), 'text' => $exercise['Exercise']['title']));
    array_push($breadcrumbs, array('link' => '#', 'text' => __("Add Cases in Batch")));
    $this->set(compact('exercise', 'timeoptions', 'memsizeoptions', 'stacksizeoptions', 'blocksizeoptions', 'breadcrumbs'));
  }

  /**
   * edit method
   *
   * @throws NotFoundException
   * @param string $id
   * @return void
   */
  public function edit($id = null)
  {
    $this->layout = "template2015";
    $this->loadModel('Archive');
    if (!$this->ExerciseCase->exists($id)) {
      throw new NotFoundException(__('Invalid exercise case'));
    }
    $memoryStart = memory_get_usage();
    $this->ExerciseCase->id = $id;
    $exercise_id = $this->ExerciseCase->getExerciseId();
    if (is_null($exercise_id)) {
      throw new NotFoundException(__('Invalid exercise'));
    }
    $this->loadModel('Exercise');
    $this->loadModel('ExerciseCaseFile');
    $this->loadModel('Offering');
    $this->ExerciseCase->recursive = -1;
    $this->ExerciseCaseFile->recursive = -1;
    $this->Exercise->recursive = -1;
    $exercise = $this->Exercise->find('first', array('conditions' => array('Exercise.id' => $exercise_id)));
    $offering = $this->Offering->findById($exercise['Exercise']['offering_id']);
    $exercise['Offering'] = $offering['Offering'];

    $this->loadModel('Enrollment');
    //Verifica se participa desta turma
    if ($this->currentUser['type'] < 3) {
      if (count($this->Enrollment->findByUserEmailAndOfferingId($this->currentUser['email'], $exercise['Exercise']['offering_id'])) == 0) {
        throw new NotFoundException(__('The selected exercise does not exists'));
      }
    }

    if ($this->request->is('post') || $this->request->is('put')) {
      $this->loadModel("ExerciseCaseFile");
      if (isset($this->request->data['RemoveExerciseCaseFile'])) {
        foreach ($this->request->data['RemoveExerciseCaseFile'] as $idf => $remove) {
          if (filter_var($remove, FILTER_VALIDATE_BOOLEAN)) {
            $this->ExerciseCaseFile->id = $idf;
            $this->ExerciseCaseFile->removeFile();
          }
        }
        unset($this->request->data['RemoveExerciseCaseFile']);
      }
      $this->request->data['ExerciseCase']['id'] = $id;
      $this->request->data['ExerciseCase']['input_type'] = 1;
      if ($this->request->data['ExerciseCase']['output_type'] == 1) {
        $this->request->data['ExerciseCase']['output'] = $this->request->data['ExerciseCase']['outputText'];
      } elseif ($this->request->data['ExerciseCase']['output_type'] == 2) {
        $this->request->data['ExerciseCase']['output'] = $this->request->data['ExerciseCase']['outputNumber'];
        $this->request->data['ExerciseCase']['abs_error'] = $this->request->data['ExerciseCase']['outputError'];
      } elseif ($this->request->data['ExerciseCase']['output_type'] == 3) {
        $this->request->data['ExerciseCase']['output'] = $this->request->data['ExerciseCase']['outputBinary'];
      }
      $inputContent = false;
      $outputContent = false;

      $this->request->data['ExerciseCase']['input_md5'] = md5($this->request->data['ExerciseCase']['input']);
      $inputContent = $this->request->data['ExerciseCase']['input'];

      if (md5($this->request->data['ExerciseCase']['output']) !== $this->Archive->getExerciseCaseOutputMD5FromAwsS3($id)) {
        $this->request->data['ExerciseCase']['output_md5'] = md5($this->request->data['ExerciseCase']['output']);
        $outputContent = $this->request->data['ExerciseCase']['output'];
      }

      $this->ExerciseCase->validator()->getField('output_type')->setRule('inList', array(
        'rule' => array('inList', array('1', '2', '3')),
        'message' => __("Invalid output type"),
        'required' => true
      ));
      $this->ExerciseCase->begin();
      if ($this->ExerciseCase->saveAssociated($this->request->data)) {
        $rb = false;
        if ($inputContent !== false) {
          if (!$this->Archive->saveExerciseCaseInputToAwsS3($id, $inputContent, array("md5" => md5($inputContent)))) {
            $rb = true;
          }
        }
        if ($outputContent !== false) {
          if (!$this->Archive->saveExerciseCaseOutputToAwsS3($id, $outputContent, array("md5" => md5($outputContent)))) {
            $rb = true;
          }
        }

        if ($rb) {
          $this->ExerciseCase->rollback();
        } else {
          $this->ExerciseCase->commit();
        }

        $this->Session->setFlash(__('The exercise case has been saved'), 'default', array(), 'success');
        $this->ExerciseCase->recursive = -1;
        if (isset($this->request->data['ExerciseCaseFile'])) {
          $options = array('conditions' => array('ExerciseCase.' . $this->ExerciseCase->primaryKey => $this->ExerciseCase->id));
          $exerciseCase = $this->ExerciseCase->find('first', $options);
          $exercise = $this->Exercise->findById($exerciseCase['ExerciseCase']['exercise_id'], array('id', 'offering_id'));
          $files = $this->request->data['ExerciseCaseFile'];
          foreach ($files as $file) {
            if ($file['hash'] != "0") {
              $tmpKey = "tmp/inputfiles/" . $file['hash'] . "/" . $file['path'];
              if (!$this->Archive->copyExerciseCaseFilesToAwsS3($exerciseCase['ExerciseCase']['id'], $tmpKey, $file['path'])) {
                $this->Session->setFlash(__('The exercise case file could not be saved'));
              }
            }
          }
        }
        Log::register("Edited the Exercise Case #" . $id, $this->currentUser);
        $this->redirect(array('controller' => 'Exercises', 'action' => 'viewProfessor', $this->ExerciseCase->field('exercise_id')));
      } else {
        $this->Session->setFlash(__('The exercise case could not be saved. Please, try again.'));
      }
    } else {
      $options = array('conditions' => array('ExerciseCase.' . $this->ExerciseCase->primaryKey => $id));
      $exerciseCase = $this->ExerciseCase->find('first', $options);

      $exerciseCase['ExerciseCase']['input'] = $this->Archive->getExerciseCaseInputFromAwsS3($id);
      $exerciseCase['ExerciseCase']['output'] = $this->Archive->getExerciseCaseOutputFromAwsS3($id);

      if ($exerciseCase['ExerciseCase']['output_type'] == 1) {
        $exerciseCase['ExerciseCase']['outputText'] = $exerciseCase['ExerciseCase']['output'];
      } elseif ($exerciseCase['ExerciseCase']['output_type'] == 2) {
        $exerciseCase['ExerciseCase']['outputNumber'] = $exerciseCase['ExerciseCase']['output'];
        $exerciseCase['ExerciseCase']['outputError'] = $exerciseCase['ExerciseCase']['abs_error'];
      } elseif ($exerciseCase['ExerciseCase']['output_type'] == 3) {
        $exerciseCase['ExerciseCase']['outputBinary'] = $exerciseCase['ExerciseCase']['output'];
      }
      $exerciseCaseFiles = $this->ExerciseCaseFile->find('all', array('conditions' => array('exercise_case_id' => $id)));
      $exerciseCase['ExerciseCaseFile'] = array();
      foreach ($exerciseCaseFiles as $file) {
        array_push($exerciseCase['ExerciseCaseFile'], $file['ExerciseCaseFile']);
      }
      $this->request->data = $exerciseCase;
    }

    $memoryEnd = memory_get_usage();
    $memoryUsage = $memoryEnd - $memoryStart;
    if ($memoryUsage > 3000000) {
      unset($this->request->data);
      unset($exerciseCase);
      $this->Session->setFlash(__('This case uses a lot of memory and can not be edited in browser'));
      $this->redirect(array('controller' => 'Exercises', 'action' => 'viewProfessor', $exercise['Exercise']['id']));
    }
    if ($id == 626) {
      $timeoptions = array(
        '1' => __('1sec'), '2' => __('2sec'), '3' => __('3sec'), '4' => __('4sec'), '5' => __('5sec'), '10' => __('10sec'),
        '15' => __('15sec'), '20' => __('20sec'), '30' => __('30sec'), '40' => __('40sec'), '50' => __('50sec'), '60' => __('1min'),
      );
    } else {
      $timeoptions = array('1' => __('1sec'), '2' => __('2sec'), '3' => __('3sec'), '4' => __('4sec'), '5' => __('5sec'), '10' => __('10sec'));
    }

    $breadcrumbs = array();
    $this->loadModel('Course');
    $this->Course->recursive = -1;
    $course = $this->Course->findById($exercise['Offering']['course_id'], array('fields' => 'code'));
    array_push($breadcrumbs, array('link' => array('controller' => 'offerings', 'action' => 'view', $exercise['Offering']['id']), 'text' => $course['Course']['code']));
    array_push($breadcrumbs, array('link' => array('controller' => 'exercises', 'action' => 'view', $exercise['Exercise']['id']), 'text' => $exercise['Exercise']['title']));
    array_push($breadcrumbs, array('link' => '#', 'text' => __("Edit Exercise Case")));

    $this->set(compact('exercise', 'timeoptions', 'breadcrumbs'));
    $this->set("isEdit", true);
    $this->render("form");
  }

  /**
   * delete method
   *
   * @throws NotFoundException
   * @param string $id
   * @return void
   */
  public function delete($id = null)
  {
    $this->request->onlyAllow('post', 'delete');
    $this->ExerciseCase->id = $id;
    $this->loadModel('Archive');
    if (!$this->ExerciseCase->exists()) {
      throw new NotFoundException(__('Invalid exercise case'));
    }

    $this->ExerciseCase->id = $id;
    $exercise_id = $this->ExerciseCase->getExerciseId();
    if (is_null($exercise_id)) {
      throw new NotFoundException(__('Invalid exercise'));
    }
    $this->loadModel('Exercise');
    $this->Exercise->recursive = 1;
    $exercise = $this->Exercise->find('first', array('conditions' => array('Exercise.id' => $exercise_id)));
    $this->loadModel('Enrollment');
    //Verifica se participa desta turma
    if ($this->currentUser['type'] < 3) {
      if (count($this->Enrollment->findByUserEmailAndOfferingId($this->currentUser['email'], $exercise['Exercise']['offering_id'])) == 0) {
        throw new NotFoundException(__('The selected exercise does not exists'));
      }
    }

    $this->loadModel('ExerciseCaseFile');
    $this->loadModel('Offering');
    $exercise_id = null;
    $exercise_case = $this->ExerciseCase->findById($id);
    $exercise_id = $exercise_case['ExerciseCase']['exercise_id'];
    $this->ExerciseCase->id = $id;

    if ($this->ExerciseCase->delete()) {
      $this->Archive->deleteExerciseCaseInputFromAwsS3($id);
      $this->Archive->deleteExerciseCaseOutputFromAwsS3($id);
      $this->Session->setFlash(__('Exercise case deleted'), 'default', array(), 'success');
      Log::register("Removed the Exercise Case #" . $id, $this->currentUser);
      if (is_null($exercise_id)) {
        $this->redirect(array('controller' => 'exercises', 'action' => 'index'));
      } else {
        $this->redirect(array('controller' => 'exercises', 'action' => 'viewProfessor', $exercise_id));
      }
    }
    $this->Session->setFlash(__('Exercise case was not deleted'));
    $this->redirect(array('action' => 'index'));
  }

  public function toggleShowInput($id = null)
  {
    $this->ExerciseCase->id = $id;
    if (!$this->ExerciseCase->exists()) {
      throw new NotFoundException(__('Invalid exercise case'));
    }
    $this->request->onlyAllow('post', 'put');
    $valueNow = $this->ExerciseCase->field('show_input');
    if ($valueNow == true) {
      $this->ExerciseCase->saveField('show_input', false);
    } else {
      $this->ExerciseCase->saveField('show_input', true);
    }
    $this->redirect(array('controller' => 'exercises', 'action' => 'viewProfessor', $valueNow = $this->ExerciseCase->field('exercise_id')));
  }

  public function toggleShowExpectedOutput($id = null)
  {
    $this->ExerciseCase->id = $id;
    if (!$this->ExerciseCase->exists()) {
      throw new NotFoundException(__('Invalid exercise case'));
    }
    $this->request->onlyAllow('post', 'put');
    $valueNow = $this->ExerciseCase->field('show_expected_output');
    if ($valueNow == true) {
      $this->ExerciseCase->saveField('show_expected_output', false);
    } else {
      $this->ExerciseCase->saveField('show_expected_output', true);
    }
    $this->redirect(array('controller' => 'exercises', 'action' => 'viewProfessor', $valueNow = $this->ExerciseCase->field('exercise_id')));
  }

  public function toggleShowUserOutput($id = null)
  {
    $this->ExerciseCase->id = $id;
    if (!$this->ExerciseCase->exists()) {
      throw new NotFoundException(__('Invalid exercise case'));
    }
    $this->request->onlyAllow('post', 'put');
    $valueNow = $this->ExerciseCase->field('show_user_output');
    if ($valueNow == true) {
      $this->ExerciseCase->saveField('show_user_output', false);
    } else {
      $this->ExerciseCase->saveField('show_user_output', true);
    }
    $this->redirect(array('controller' => 'exercises', 'action' => 'viewProfessor', $valueNow = $this->ExerciseCase->field('exercise_id')));
  }
}
