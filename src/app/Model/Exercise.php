<?php
App::uses('AppModel', 'Model');
/**
 * Exercise Model
 *
 * @property Offering $Offering
 * @property ExerciseFile $ExerciseFile
 * @property ExerciseCase $ExerciseCase
 * @property Commit $Commit
 * @property AllowedFile $AllowedFile
 */
class Exercise extends AppModel
{

  /**
   * Display field
   *
   * @var string
   */
  public $recursive = -1;
  public $displayField = 'title';

  public $virtualFields = array(
    'isOpen' => 'CASE WHEN (deadline > NOW() AND open_date < NOW() AND removed = false) THEN true ELSE false END',
    'isFinished' => 'CASE WHEN deadline > NOW() THEN false ELSE true END',
    'public' => 'CASE WHEN (SELECT COUNT(*) FROM public_exercises pe WHERE pe.exercise_id = Exercise.id) > 0 THEN true ELSE false END'
  );

  /**
   * Validation rules
   *
   * @var array
   */
  public $validate = array(
    'offering_id' => array(
      'numeric' => array(
        'rule' => array('numeric'),
        //'message' => 'Your custom message here',
        //'allowEmpty' => false,
        //'required' => false,
        //'last' => false, // Stop validation after this rule
        //'on' => 'create', // Limit validation to 'create' or 'update' operations
      ),
    ),
    'title' => array(
      'notempty' => array(
        'rule' => array('notempty'),
        //'message' => 'Your custom message here',
        //'allowEmpty' => false,
        //'required' => false,
        //'last' => false, // Stop validation after this rule
        //'on' => 'create', // Limit validation to 'create' or 'update' operations
      ),
      'maxlength' => array(
        'rule' => array('maxlength', 255),
        //'message' => 'Your custom message here',
        //'allowEmpty' => false,
        //'required' => false,
        //'last' => false, // Stop validation after this rule
        //'on' => 'create', // Limit validation to 'create' or 'update' operations
      ),
      'minlength' => array(
        'rule' => array('minlength', 5),
        //'message' => 'Your custom message here',
        //'allowEmpty' => false,
        //'required' => false,
        //'last' => false, // Stop validation after this rule
        //'on' => 'create', // Limit validation to 'create' or 'update' operations
      ),
    ),
    'deadline' => array(
      'datetime' => array(
        'rule' => array('datetime'),
        //'message' => 'Your custom message here',
        //'allowEmpty' => false,
        //'required' => false,
        //'last' => false, // Stop validation after this rule
        //'on' => 'create', // Limit validation to 'create' or 'update' operations
      ),
    ),
    'open_date' => array(
      'datetime' => array(
        'rule' => array('datetime'),
        //'message' => 'Your custom message here',
        //'allowEmpty' => false,
        //'required' => false,
        //'last' => false, // Stop validation after this rule
        //'on' => 'create', // Limit validation to 'create' or 'update' operations
      ),
      'beforeDeadline' => array(
        'rule' => array('beforeDeadline'),
        'message' => 'Please select a open date before the exercise deadline',
        //'allowEmpty' => false,
        //'required' => false,
        //'last' => false, // Stop validation after this rule
        //'on' => 'create', // Limit validation to 'create' or 'update' operations
      ),
    ),
    'type' => array(
      'validateTypeInList' => array(
        'rule' => array('validateTypeInList'),
        'message' => 'Please select a valid exercise type',
        //'allowEmpty' => false,
        //'required' => false,
        //'last' => false, // Stop validation after this rule
        //'on' => 'create', // Limit validation to 'create' or 'update' operations
      ),
    ),
    'AllowedFile' => array(
      'multiple' => array(
        'rule' => array('multiple', array('min' => 1)),
        //                        'required' => true,
        'message' => 'Please select at least one language'
      ),
      'validateAllowedFiles' => array(
        'rule' => array('validateAllowedFiles'),
        //                        'required' => true,
        'message' => 'Please select only valid languages'
      ),
    )
  );

  public function validateTypeInList($check)
  {
    if (in_array($check['type'], $this->getAllowedTypes())) {
      return true;
    }
    return false;
  }

  public function beforeDeadline($check)
  {
    if (strtotime($this->data['Exercise']['deadline']) > strtotime($check['open_date'])) {
      return true;
    }
    return false;
  }

  public function validateAllowedFiles($check)
  {
    App::uses('AllowedFile', 'Model');
    $allowedFiles = new AllowedFile();
    $allowed = $allowedFiles->getAllowedFilesList($this->data[$this->alias]['type']);
    foreach ($check['AllowedFile'] as $selected) {
      foreach ($allowed  as $al) {
        if ($al['AllowedFile']['id'] == $selected) {
          return true;
        }
      }
    }
    return false;
  }

  //        public function validateMultiple($data, $min){
  //            pr($data);
  //            return false;
  //        }

  //The Associations below have been created with all possible keys, those that are not needed can be removed

  /**
   * belongsTo associations
   *
   * @var array
   */
  public $belongsTo = array(
    'Offering' => array(
      'className' => 'Offering',
      'foreignKey' => 'offering_id',
      'conditions' => '',
      'fields' => '',
      'order' => ''
    )
  );

  /**
   * hasMany associations
   *
   * @var array
   */
  public $hasMany = array(
    'ExerciseFile' => array(
      'className' => 'ExerciseFile',
      'foreignKey' => 'exercise_id',
      'dependent' => false,
      'conditions' => '',
      'fields' => '',
      'order' => '',
      'limit' => '',
      'offset' => '',
      'exclusive' => '',
      'finderQuery' => '',
      'counterQuery' => ''
    ),
    'CompilationFile' => array(
      'className' => 'CompilationFile',
      'foreignKey' => 'exercise_id',
      'dependent' => false,
      'conditions' => '',
      'fields' => '',
      'order' => '',
      'limit' => '',
      'offset' => '',
      'exclusive' => '',
      'finderQuery' => '',
      'counterQuery' => ''
    ),
    'ExerciseCase' => array(
      'className' => 'ExerciseCase',
      'foreignKey' => 'exercise_id',
      'dependent' => false,
      'conditions' => '',
      'fields' => '',
      'order' => 'id',
      'limit' => '',
      'offset' => '',
      'exclusive' => '',
      'finderQuery' => '',
      'counterQuery' => ''
    ),
    'Commit' => array(
      'className' => 'Commit',
      'foreignKey' => 'exercise_id',
      'dependent' => false,
      'conditions' => '',
      'fields' => '',
      'order' => 'id DESC',
      'limit' => '',
      'offset' => '',
      'exclusive' => '',
      'finderQuery' => '',
      'counterQuery' => ''
    )
  );


  /**
   * hasAndBelongsToMany associations
   *
   * @var array
   */
  public $hasAndBelongsToMany = array(
    'AllowedFile' => array(
      'className' => 'AllowedFile',
      'joinTable' => 'allowed_files_exercises',
      'foreignKey' => 'exercise_id',
      'associationForeignKey' => 'allowed_file_id',
      'unique' => 'keepExisting',
      'conditions' => '',
      'fields' => '',
      'order' => 'name ASC',
      'limit' => '',
      'offset' => '',
      'finderQuery' => '',
      'deleteQuery' => '',
      'insertQuery' => ''
    )
  );

  public function isOpen($now)
  {
    //'isOpen' => 'CASE WHEN (deadline > NOW() AND open_date < NOW() AND removed = false) THEN true ELSE false END',
    if (isset($this->id)) {
      return strtotime($this->field('deadline')) > $now && $this->field('open_date') < $now && !$this->field('removed');
    }
    return false;
  }

  public function isFinished($now)
  {
    //''CASE WHEN deadline > NOW() THEN false ELSE true END'
    if (isset($this->id)) {
      return strtotime($this->field('deadline')) < $now;
    }
    return false;
  }

  public function afterFind($results, $primary = false)
  {
    parent::afterFind($results);
    foreach ($results as $key => $val) {
      if (is_numeric($key)) {
        //                $results[$key]['Exercise']['num_cases']=0;
        if (isset($results[$key]['Exercise']['id'])) {
          if (!isset($results[$key]['Exercise']['ghost'])) {
            $Exercise = new Exercise();
            $Exercise->id = $results[$key]['Exercise']['id'];
            $results[$key]['Exercise']['ghost'] = $Exercise->field('ghost');
            $results[$key]['Exercise']['real_id'] = $Exercise->field('real_id');
          }
          if ($results[$key]['Exercise']['ghost']) {
            $exId = $results[$key]['Exercise']['real_id'];
          } else {
            $exId = $results[$key]['Exercise']['id'];
          }
          $results[$key]['Exercise']['num_cases'] = $this->ExerciseCase->find('count', array('conditions' => array('exercise_id' => $exId)));
          $results[$key]['Exercise']['num_commits'] = ($c = $this->Commit->find('count', array('conditions' => array('exercise_id' => $results[$key]['Exercise']['id'])))) ? $c : 0;
          $results[$key]['Exercise']['num_participants'] = ($c = $this->Commit->find('count', array('conditions' => array('exercise_id' => $results[$key]['Exercise']['id']), 'group' => 'Commit.user_email'))) ? $c : 0;
        }
      }
    }
    return $results;
  }

  public function getNumberOfCases()
  {
    return ($c = $this->ExerciseCase->find('count', array('conditions' => array('exercise_id' => $this->id)))) ? $c : 0;
  }

  public function beforeSave($options = array())
  {
    foreach (array_keys($this->hasAndBelongsToMany) as $model) {
      if (isset($this->data[$this->name][$model])) {
        $this->data[$model][$model] = $this->data[$this->name][$model];
        unset($this->data[$this->name][$model]);
      }
    }
    return true;
  }

  public function afterSave($created, $options = array())
  {
    App::uses('AwsCache', 'Model');
    $Cache = new AwsCache();
    $Cache->removeItem("exercise-" . $this->data['Exercise']['id']);
    if (!$created && !$this->field('ghost') && isset($this->data['Exercise']['title'])) {
      $exercise['Exercise'] = $this->data['Exercise'];
      $ghosts = $this->findAllByRealIdAndRemoved($this->data['Exercise']['id'], false, array('id'));
      foreach ($ghosts as $ghost) {
        $updateGhost = array('Exercise' => array('id' => $ghost['Exercise']['id'], 'title' => $exercise['Exercise']['title'], 'description' => $exercise['Exercise']['description'], 'deadline' => $exercise['Exercise']['deadline'], 'open_date' => $exercise['Exercise']['open_date'], 'show_before_opening' => $exercise['Exercise']['show_before_opening'], 'type' => $exercise['Exercise']['type'], 'markdown' => $exercise['Exercise']['markdown']));
        //                debug($updateGhost);
        $Exercise = new Exercise();
        $Exercise->id = $ghost['Exercise']['id'];
        $Exercise->save($updateGhost, false, array('title', 'description', 'deadline', 'open_date', 'show_before_opening', 'type', 'markdown'));
      }
    }
  }

  public function getNotDeliveredNameStatus()
  {
    return __("Not Delivered");
  }

  public function getNotDeliveredStatusColor()
  {
    return "warning";
  }

  public function getTypesList()
  {
    return array(__("Compilable or Interpretable File"), __("Simple File"));
  }

  public function getAllowedTypes()
  {
    return array(0, 1, 2);
  }

  public function getCompilableIndex()
  {
    return array(0, 2);
  }

  public function getNotCompilableIndex()
  {
    return 1;
  }

  public function getOfferingId()
  {
    if (is_numeric($this->id)) {
      $ex_data = $this->findById($this->id, array('offering_id'));
      return $ex_data['Exercise']['offering_id'];
    } else {
      return null;
    }
  }

  public function updateExerciseCasesChange()
  {
    if (isset($this->id)) {
      $this->saveField('cases_change', date("Y-m-d H:i:s"));
    }
  }

  public function getGoogleCalendarLink($exerciseId)
  {
    $this->id = $exerciseId;
    $ex_data = $this->findById($this->id, array('title', 'offering_id', 'deadline'));
    App::uses('Offering', 'Model');
    App::uses('Course', 'Model');
    $Offering = new Offering();
    $Course = new Course();
    $Offering->id = $ex_data["Exercise"]["offering_id"];
    $Course->id = $Offering->getCourseId();
    $course = $Course->field("name");
    $eventTitle = $ex_data["Exercise"]["title"] . " [" . $course . "]";
    //2016 09 20 T235900 &ctz=America/Sao_Paulo
    $startDate = date('Ymd\THis', strtotime($ex_data["Exercise"]["deadline"]) - (30 * 60));
    $endDate = date('Ymd\THis', strtotime($ex_data["Exercise"]["deadline"]));
    $url = "https://run.codes/exercises/view/" . $exerciseId;
    $googleUrl = "https://calendar.google.com/calendar/render?action=TEMPLATE";
    $googleUrl .= "&text=" . str_replace(" ", "+", $eventTitle);
    $googleUrl .= "&dates=" . $startDate . "/" . $endDate . "&ctz=America/Sao_Paulo";
    $googleUrl .= "&details=" . str_replace(" ", "+", __("Exercise URL: ")) . $url;
    $googleUrl .= "&location=run.codes";
    $googleUrl .= "&sprop=website:" . $url;
    $googleUrl .= "&sf=true&output=xml";

    return $googleUrl;
  }

  public function createGhostExercise($realId, $offeringId, $userEmail)
  {
    $this->recursive = -1;
    $exercise = $this->findById($realId);
    if (count($exercise) == 1) {
      $newExercise = array('Exercise' => array('ghost' => true, 'real_id' => $realId, 'offering_id' => $offeringId, 'markdown' => $exercise['Exercise']['markdown'], 'title' => $exercise['Exercise']['title'], 'description' => $exercise['Exercise']['description'], 'deadline' => $exercise['Exercise']['deadline'], 'open_date' => $exercise['Exercise']['open_date'], 'show_before_opening' => $exercise['Exercise']['show_before_opening'], 'type' => $exercise['Exercise']['type'], 'user_email' => $userEmail));
      $this->create();
      return $this->save($newExercise, false);
    } else {
      return false;
    }
  }

  public function copy($originalExerciseId, $offeringId, $openDate, $deadline, $userEmail, $showBeforeOpening, $allowedFiles = null)
  {
    $exercise = $this->findById($originalExerciseId);
    if ($exercise['Exercise']['ghost']) {
      $originalExerciseId = $exercise['Exercise']['real_id'];
      $exercise = $this->findById($originalExerciseId);
    }
    //		debug($exercise);
    $newExercise = array('Exercise' => array());
    $newExercise['Exercise']['offering_id'] = $offeringId;
    $newExercise['Exercise']['open_date'] = $openDate;
    $newExercise['Exercise']['deadline'] = $deadline;
    $newExercise['Exercise']['user_email'] = $userEmail;
    $newExercise['Exercise']['show_before_opening'] = $showBeforeOpening;

    $newExercise['Exercise']['title'] = $exercise['Exercise']['title'];
    $newExercise['Exercise']['type'] = $exercise['Exercise']['type'];
    $newExercise['Exercise']['description'] = $exercise['Exercise']['description'];
    $newExercise['Exercise']['markdown'] = $exercise['Exercise']['markdown'];
    $newExercise['Exercise']['removed'] = false;
    $newExercise['Exercise']['cases_change'] = null;
    //		debug($newExercise);
    $this->getDataSource()->begin();
    $this->create();
    $this->save($newExercise);
    $newExerciseId = $this->id;

    try {
      //ALLOWED FILES
      App::uses('AllowedFilesExercise', 'Model');
      $AllowedFilesExerciseModel = new AllowedFilesExercise();
      if (is_null($allowedFiles)) {
        $allowedFiles = $AllowedFilesExerciseModel->findAllByExerciseId($originalExerciseId);
      }
      foreach ($allowedFiles as $aF) {
        unset($aF['AllowedFilesExercise']['id']);
        $aF['AllowedFilesExercise']['exercise_id'] = $newExerciseId;
        $AllowedFilesExerciseModel->create();
        $AllowedFilesExerciseModel->save($aF);
      }


      //compilation files
      App::uses('Archive', 'Model');
      App::uses('Folder', 'Utility');
      $Archive = new Archive();
      App::uses('CompilationFile', 'Model');
      $CompilationModel = new CompilationFile();
      $CompilationModel->recursive = -1;
      $compilationFiles = $CompilationModel->findAllByExerciseId($originalExerciseId);
      //		debug($compilationFiles);
      $oldCFPath = $Archive->getCompilationFilesFolder($originalExerciseId);
      //		debug($oldCFPath);
      foreach ($compilationFiles as $cF) {
        $newCF = array('CompilationFile' => array('exercise_id' => $newExerciseId, 'path' => $cF['CompilationFile']['path']));
        //			debug($newCF);
        $CompilationModel->create();
        $CompilationModel->save($newCF);
        $tmpKey = "/compilationfiles/" . $originalExerciseId . "/" . $cF['CompilationFile']['path'];
        $targetKey = "/compilationfiles/" . $newExerciseId . "/" . $cF['CompilationFile']['path'];
        $Archive->copyAwsFiles($tmpKey, $targetKey);
        //DEPRECATED NO FUTURO
        // $newCFPath = $Archive->getCompilationFilesFolder($newExerciseId);
        // $createFolder = new Folder($newCFPath, true, 0777);
        // copy($oldCFPath.$cF['CompilationFile']['path'],$newCFPath.$cF['CompilationFile']['path']);
        //
      }
      //exercise files
      App::uses('ExerciseFile', 'Model');
      $ExerciseFile = new ExerciseFile();
      $ExerciseFile->recursive = -1;
      $exerciseFiles = $ExerciseFile->findAllByExerciseId($originalExerciseId);
      //		debug($exerciseFiles);
      $oldEFPath = $Archive->getExerciseFilesFolder($originalExerciseId);
      //		debug($oldEFPath);
      foreach ($exerciseFiles as $eF) {
        $newEF = array('ExerciseFile' => array('exercise_id' => $newExerciseId, 'path' => $eF['ExerciseFile']['path']));
        //			debug($newEF);
        $ExerciseFile->create();
        $ExerciseFile->save($newEF);
        $tmpKey = "/exercisefiles/" . $originalExerciseId . "/" . $eF['ExerciseFile']['path'];
        $targetKey = "/exercisefiles/" . $newExerciseId . "/" . $eF['ExerciseFile']['path'];
        $Archive->copyAwsFiles($tmpKey, $targetKey);
        //DEPRECATED NO FUTURO
        // $newEFPath = $Archive->getExerciseFilesFolder($newExerciseId);
        // $createFolder = new Folder($newEFPath, true, 0777);
        // copy($oldEFPath.$eF['ExerciseFile']['path'],$newEFPath.$eF['ExerciseFile']['path']);
      }
      //exercise cases
      App::uses('ExerciseCase', 'Model');
      App::uses('ExerciseCaseFile', 'Model');
      App::uses('Archive', 'Model');
      $ExerciseCase = new ExerciseCase();
      $ExerciseCaseFile = new ExerciseCaseFile();
      $Archive = new Archive();
      $ExerciseCase->recursive = -1;
      $ExerciseCaseFile->recursive = -1;
      $exerciseCases = $ExerciseCase->findAllByExerciseId($originalExerciseId);

      foreach ($exerciseCases as $eC) {
        $newEC = $eC;
        $originalECId = $newEC['ExerciseCase']['id'];
        $originalInput = $Archive->getExerciseCaseInputFromAwsS3($originalECId);
        $originalOutput = $Archive->getExerciseCaseOutputFromAwsS3($originalECId);
        $newEC['ExerciseCase']['input'] = "";
        $newEC['ExerciseCase']['output'] = "";
        $newEC['ExerciseCase']['exercise_id'] = $newExerciseId;
        unset($newEC['ExerciseCase']['id']);
        $ExerciseCase->create();
        $ExerciseCase->save($newEC);
        $newECId = $ExerciseCase->id;
        $Archive->saveExerciseCaseInputToAwsS3($newECId, $originalInput, array("md5" => md5($originalInput)));
        $Archive->saveExerciseCaseOutputToAwsS3($newECId, $originalOutput, array("md5" => md5($originalOutput)));

        $exerciseCaseFiles = $ExerciseCaseFile->findAllByExerciseCaseId($eC['ExerciseCase']['id']);
        //			debug($exerciseCaseFiles);
        foreach ($exerciseCaseFiles as $eCF) {
          $newECF = array('ExerciseCaseFile' => array('exercise_case_id' => $newECId, 'path' => $eCF['ExerciseCaseFile']['path']));
          $ExerciseCaseFile->create();
          $ExerciseCaseFile->save($newECF);
          $newECFId = $ExerciseCaseFile->id;
          $Archive->copyExerciseCaseFileFromExerciseCaseFile($originalECId, $newECId, $eCF['ExerciseCaseFile']['path']);
          //                    $oldECFPath = $Archive->getExerciseCaseFilesFolder($eCF['ExerciseCaseFile']['exercise_case_id']);
          //                    $newECFPath = $Archive->getExerciseCaseFilesFolder($newECId);
          //                    $createFolder = new Folder($newECFPath, true, 0777);
          //                    copy($oldECFPath.$eCF['ExerciseCaseFile']['path'],$newECFPath.$eCF['ExerciseCaseFile']['path']);
        }
      }
      $this->getDataSource()->commit();
      return $newExerciseId;
    } catch (Exception $e) {
      $this->getDataSource()->rollback();
      die();
      return false;
    }
  }

  public function getExercise($id)
  {
    App::uses('Course', 'Model');
    App::uses('Offering', 'Model');
    App::uses('ExerciseFile', 'Model');
    App::uses('AllowedFilesExercise', 'Model');
    App::uses('AllowedFile', 'Model');
    App::uses('ExerciseCase', 'Model');
    $Course = new Course();
    $Offering = new Offering();
    $ExerciseFile = new ExerciseFile();
    $AllowedFilesExercise = new AllowedFilesExercise();
    $AllowedFile = new AllowedFile();
    $ExerciseCase = new ExerciseCase();
    $Offering->recursive = -1;
    $Course->recursive = -1;
    $ExerciseFile->recursive = -1;
    $AllowedFilesExercise->recursive = -1;
    $AllowedFile->recursive = -1;
    $ExerciseCase->recursive = -1;

    $options = array('fields' => array(), 'conditions' => array('Exercise.id' => $id));
    $exercise = $this->find('first', $options);
    $this->id = $exercise["Exercise"]["id"];

    $offering = $Offering->findById($exercise['Exercise']['offering_id']);
    $course = $Course->findById($offering['Offering']['course_id'], array('title', 'code', 'name'));
    $offering['Offering']['Course'] = $course['Course'];
    $exercise['Offering'] = $offering['Offering'];

    if ($exercise['Exercise']['ghost']) {
      $files = $ExerciseFile->findAllByExerciseId($exercise['Exercise']['real_id'], array('id', 'path'));
    } else {
      $files = $ExerciseFile->findAllByExerciseId($exercise['Exercise']['id'], array('id', 'path'));
    }
    $exercise['ExerciseFile'] = $files;
    unset($files);

    if ($exercise['Exercise']['ghost']) {
      $afiles = $AllowedFilesExercise->findAllByExerciseId($exercise['Exercise']['real_id']);
    } else {
      $afiles = $AllowedFilesExercise->findAllByExerciseId($exercise['Exercise']['id']);
    }
    foreach ($afiles as $k => $allowed) {
      $afiles[$k] = $AllowedFile->findById($allowed['AllowedFilesExercise']['allowed_file_id'], array('name'));
    }
    $exercise['AllowedFile'] = $afiles;
    unset($afiles);

    if ($exercise['Exercise']['ghost']) {
      $cases = $ExerciseCase->findAllByExerciseId($exercise['Exercise']['real_id'], array('id', 'show_input', 'show_expected_output', 'maxmemsize', 'cputime', 'stacksize', 'show_user_output', 'file_size'), array('id' => 'ASC'));
    } else {
      $cases = $ExerciseCase->findAllByExerciseId($exercise['Exercise']['id'], array('id', 'show_input', 'show_expected_output', 'maxmemsize', 'cputime', 'stacksize', 'show_user_output', 'file_size'), array('id' => 'ASC'));
    }
    $exercise['ExerciseCase'] = $cases;
    return $exercise;
  }
}
