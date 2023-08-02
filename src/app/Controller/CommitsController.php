<?php
App::uses('AppController', 'Controller');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
/**
 * Commits Controller
 *
 * @property Commit $Commit
 * @property PaginatorComponent $Paginator
 */
class CommitsController extends AppController
{

  /**
   * Components
   *
   * @var array
   */
  public $components = array('Paginator');
  //Verificar se aluno esta acessando a dele
  private $studentAuthorized = array('download', 'details', 'commitinfo', 'casesinfo');
  private $demoPostNotAuthorized = array('editscore', 'recompile', 'details', 'download', 'viewfile', 'getfilefromzip', 'recompileall');
  private $professorAuthorized = array('editscore', 'recompile', 'details', 'download', 'viewfile', 'getfilefromzip', 'recompileall');


  public function isAuthorized($user = null)
  {
    $this->loadModel('User');
    $this->loadModel('Enrollment');
    $this->loadModel('Exercise');
    $this->Commit->recursive = -1;
    $this->Exercise->recursive = -1;
    if ($user['type'] >= $this->User->getAdminIndex()) {
      return true;
    }
    if (!Configure::read('Config.allowDemoPostRequest') && $this->currentUser['domain'] == "demo.run.codes" && !$this->request->is("get") && in_array(strtolower($this->request->params['action']), $this->demoPostNotAuthorized)) {
      $this->Session->setFlash(__("Sorry! This action is not allowed in demo mode"));
      $this->redirect('/home');
    }

    if (strtolower($this->request->params['action']) == "recompileall") {
      $exercise_id = $this->request->params['pass'][0];
      $this->Exercise->id = $exercise_id;
      $offering = $this->Exercise->getOfferingId();
      if ($this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $offering)) {
        return true;
      }
      return false;
    }
    $commit = $this->Commit->findById($this->request->params['pass'][0], array('Commit.user_email'));
    $this->Commit->id = $this->request->params['pass'][0];
    $exercise_id = $this->Commit->getExerciseId();
    $this->Exercise->id = $exercise_id;
    $offering = $this->Exercise->getOfferingId();
    if (in_array(strtolower($this->request->params['action']), $this->professorAuthorized)) {
      if ($this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $offering)) {
        return true;
      }
    }
    if (in_array(strtolower($this->request->params['action']), $this->studentAuthorized)) {
      if ($this->Enrollment->isEnrolled($this->currentUser['email'], $offering) && $commit['Commit']['user_email'] == $this->currentUser['email']) {
        return true;
      }
    } else {
      return false;
    }

    return false;
  }

  public function beforeRender()
  {
    parent::beforeRender();
    if ($this->request->params['action'] != "index") {
      $this->loadModel('Enrollment');
      $this->loadModel('Exercise');
      $commit = $this->Commit->findById($this->request->params['pass'][0], array('Commit.exercise_id'));
      $this->Exercise->id = $commit['Commit']['exercise_id'];
      $offering = $this->Exercise->getOfferingId();
      if (isset($this->currentUser['onlyStudent']) && $this->currentUser['onlyStudent']) {
        $assistantOrProfessor =  false;
      } else {
        $assistantOrProfessor = ($this->currentUser['type'] >= $this->User->getAdminIndex()) ? true : $this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $offering);
      }
      $this->set(compact('assistantOrProfessor'));
    }
  }

  public function index()
  {
    $this->layout = "template2015";
    if ($this->request->is('post')) {
      $this->redirect(array(
        'controller' => 'Commits',
        'action' => 'index',
        'exercise' => $this->request->data['Commit']['exercise'],
        'status' => $this->request->data['Commit']['status']
      ));
    }
    $this->paginate = array(
      'recursive' => -1,
      'limit' => 50,
      'order' => array('commit_time' => 'DESC')
    );
    $cond = array();
    if (isset($this->request->params['named']['exercise'])) {
      $cond['Commit.exercise_id'] = $this->request->params['named']['exercise'];
    }
    if (isset($this->request->params['named']['user_email'])) {
      $cond['Commit.user_email'] = $this->request->params['named']['user_email'];
    }
    if (isset($this->request->params['named']['status'])) {
      $cond['Commit.status'] = $this->request->params['named']['status'];
    }


    $commits = $this->Paginator->paginate($cond);
    $this->loadModel("User");
    $this->loadModel("Exercise");
    $this->User->recursive = -1;
    $this->Exercise->recursive = -1;
    foreach ($commits as $k => $c) {
      $user = $this->User->findByEmail($c['Commit']['user_email'], array('name'));
      $exercise = $this->Exercise->findById($c['Commit']['exercise_id'], array('id', 'title'));
      $commits[$k]['User'] = $user['User'];
      $commits[$k]['Exercise'] = $exercise['Exercise'];
    }
    $this->set('status', $this->Commit->getStatusList());
    $this->set('commits', $commits);
  }

  public function download($commit_id = null)
  {
    //        throw new NotFoundException();
    if (!$this->Commit->exists($commit_id)) {
      throw new NotFoundException(__('Invalid commit'));
    }
    $this->loadModel("Archive");
    $this->loadModel("User");

    $this->Commit->recursive = -1;
    $commit = $this->Commit->findById($commit_id, array("exercise_id", "user_email", "aws_key"));
    $user = $this->User->findByEmail($commit["Commit"]["user_email"], array("identifier"));
    $file = $this->Archive->getCommitFileDownloadFromAwsS3($commit['Commit']['aws_key']);
    if ($file !== false) {
      switch ($file["ext"]) {
        case "exe":
          throw new NotFoundException();
        case "php":
          throw new NotFoundException();
        case "htm":
          throw new NotFoundException();
        case "html":
          throw new NotFoundException();
      }
      Log::register("Downloaded the Commit #" . $commit['Commit']['exercise_id'] . " from AWS S3", $this->currentUser);
      header("Content-Type: " . $file["result"]['ContentType']);
      header("Content-Length: " . $file["result"]['ContentLength']);
      header("Content-Disposition: inline; filename=" . $user["User"]["identifier"] . "." . $file["ext"]);
      echo $file["result"]['Body'];
      exit;
    }
    //
    //        $this->Commit->recursive=2;
    //        $commit=$this->Commit->findById($commit_id);
    //        $folder = Configure::read('Upload.dir').'/'.$commit['Exercise']['Offering']['course_id'].'/'.$commit['Exercise']['offering_id'].'/'.$commit['Exercise']['id'].'/'.$commit['Commit']['user_email'].'/'.$commit['Commit']['commit_time'].'/';
    //        $dir = new Folder($folder,false,0777);
    //        $files=$dir->find();
    //        $tipo = "plain/text";
    //        if(count($files) > 0) {
    //            $file=$folder.$files[0];
    //            if(file_exists($file)) {
    //                switch(strtolower(substr(strrchr(basename($file),"."),1))){
    //                    case "pdf": $tipo="application/pdf"; break;
    //                    case "exe": $tipo="application/octet-stream"; break;
    //                    case "zip": $tipo="application/zip"; break;
    //                    case "doc": $tipo="application/msword"; break;
    //                    case "xls": $tipo="application/vnd.ms-excel"; break;
    //                    case "ppt": $tipo="application/vnd.ms-powerpoint"; break;
    //                    case "gif": $tipo="image/gif"; break;
    //                    case "png": $tipo="image/png"; break;
    //                    case "jpg": $tipo="image/jpg"; break;
    //                    case "mp3": $tipo="audio/mpeg"; break;
    //                    case "php": throw new NotFoundException();
    //                    case "htm": throw new NotFoundException();
    //                    case "html":  throw new NotFoundException();
    //                }
    //                $filename = basename($file);
    //                if(isset($tipo)) {
    //                    header("Content-Type: ".$tipo);
    //                    $elements = explode(".", $filename);
    //                    $filename = $commit['User']['identifier'].".".$elements[count($elements) - 1];
    //                }
    //                header("Content-Length: ".filesize($file));
    //                header("Content-Disposition: inline; filename=".$filename);
    //                readfile($file); // lê o arquivo
    //                exit; // aborta pós-ações   }
    //            }
    //        } else {
    Log::slackNotification("Integridade do Sistema", "O commit " . $commit_id . " não foi encontrado no S3", array("https://run.codes/Commits/details/" . $commit_id));
    throw new NotFoundException(__('This commit does not have any file on directory. Please verify the system integrity'));
    //        }
  }

  public function commitInfo($commit_id = null)
  {
    $this->autoRender = false;
    if (!$this->Commit->exists($commit_id)) {
      throw new NotFoundException(__('Invalid commit'));
    }
    if ($this->request->is('post')) {

      $info = new stdClass();
      $info->commit = new stdClass();
      $info->commit->status = new stdClass();
      $info->commit->compiled = new stdClass();
      $info->commit->cases = new stdClass();
      $info->commit->score = new stdClass();
      $this->loadModel('Exercise');
      $this->Commit->recursive = -1;
      $this->Exercise->recursive = -1;
      $commit = $this->Commit->findById($commit_id, array('exercise_id', 'status', 'corrects', 'compiled', 'score', 'compiled_message', 'id'));
      $exercise = $this->Exercise->findById($commit['Commit']['exercise_id'], array('id'));
      $num_cases = $exercise['Exercise']['num_cases'];

      $info->commit->status->color = $commit['Commit']['status_color'];
      $info->commit->status->name = $commit['Commit']['name_status'];
      $info->commit->status->value = $commit['Commit']['status'];
      $info->commit->compiled->status = $commit['Commit']['compiled'];
      $info->commit->compiled->color = $commit['Commit']['compiled_color'];
      $info->commit->compiled->message = $commit['Commit']['compiled_message'];
      $info->commit->cases->color = $commit['Commit']['correct_color'];
      $info->commit->cases->correct = $commit['Commit']['corrects'];
      $info->commit->cases->total = $num_cases;
      $info->commit->score->color = $commit['Commit']['score_color'];
      $info->commit->score->value = $commit['Commit']['score'];
      $info->commit->id = $commit['Commit']['id'];

      echo json_encode($info);
    } else {
      throw new NotFoundException();
    }
  }

  public function casesInfo($commit_id = null)
  {
    $this->layout = "ajax";
    if (!$this->Commit->exists($commit_id)) {
      throw new NotFoundException(__('Invalid commit'));
    }
    if ($this->request->is('post')) {
      $this->loadModel("Exercise");
      $this->loadModel("Offering");
      $this->Commit->recursive = -1;
      $commit = array();
      $this->Commit->id = $commit_id;
      $exercise_id = $this->Commit->getExerciseId();
      $this->loadModel("ExerciseCase");
      $this->loadModel("CommitsExerciseCase");
      $this->ExerciseCase->recursive = -1;
      $this->CommitsExerciseCase->recursive = -1;
      $exerciseCase = $this->ExerciseCase->findAllByExerciseId($exercise_id, array('id'), array('id'));
      $commit['ExerciseCase'] = array();
      foreach ($exerciseCase as $key => $item) {
        $commitExerciseCase = $this->CommitsExerciseCase->findByExerciseCaseIdAndCommitId($item['ExerciseCase']['id'], $commit_id, array('id', 'exercise_case_id', 'cputime', 'memused', 'status', 'status_message'));
        if (count($commitExerciseCase) > 0) {
          $exerciseCase[$key]['ExerciseCase']['CommitsExerciseCase'] = $commitExerciseCase['CommitsExerciseCase'];
          array_push($commit['ExerciseCase'], $exerciseCase[$key]['ExerciseCase']);
        }
      }
      $this->set('commit', $commit);
    } else {
      die();
    }
  }

  public function details($commit_id = null)
  {
    $this->layout = "template2015";
    if (!$this->Commit->exists($commit_id)) {
      throw new NotFoundException(__('Invalid commit'));
    }
    $this->Commit->recursive = -1;
    $commit = $this->Commit->findById($commit_id);
    $this->Commit->id = $commit_id;
    $exercise_id = $this->Commit->getExerciseId();
    $this->loadModel('Exercise');
    $this->Exercise->id = $exercise_id;
    $offering_id = $this->Exercise->getOfferingId();
    $this->loadModel('Offering');
    $this->Offering->id = $offering_id;
    $course_id = $this->Offering->getCourseId();
    $this->loadModel('User');
    $this->User->recursive = 0;
    $user = $this->User->findByEmail($commit['Commit']['user_email']);
    $commit['User'] = $user['User'];
    $commit['User']['University'] = $user['University'];
    //        debug($commit);
    //        $file=false;
    //        debug(Configure::read('Upload.dir').'/'.$commit['Exercise']['Offering']['course_id'].'/'.$commit['Exercise']['offering_id'].'/'.$commit['Exercise']['id'].'/'.$commit['Commit']['user_email'].'/');
    //        $dir = new Folder(Configure::read('Upload.dir').'/'.$course_id.'/'.$offering_id.'/'.$exercise_id.'/'.$commit['Commit']['user_email'].'/'.$commit['Commit']['commit_time'].'/',false,0777);
    //        $dirname = Configure::read('Upload.dir').'/'.$course_id.'/'.$offering_id.'/'.$exercise_id.'/'.$commit['Commit']['user_email'].'/'.$commit['Commit']['commit_time'].'/';
    //        $files=$dir->find();
    //        $file = array();
    //        if(count($files) > 0) {
    //            foreach ($files as $f) {
    //                $ext = explode('.', $f);
    //                $ext = $ext[count($ext)-1];
    //                $file_item = array('name' => $f, 'extension' => $ext, 'size' => filesize($dirname.$f));
    //                array_push($file,$file_item);
    //            }
    //        }
    //        debug($commit);
    $this->loadModel('Course');
    $this->loadModel('ExerciseCase');
    $this->loadModel('CommitsExerciseCase');
    $this->Course->recursive = -1;
    $this->Exercise->recursive = -1;
    $this->ExerciseCase->recursive = -1;
    $this->CommitsExerciseCase->recursive = -1;
    $course = $this->Course->findById($course_id, array('fields' => 'code'));
    $exercise = $this->Exercise->findById($exercise_id, array('title', 'id', 'ghost', 'real_id'));
    if ($exercise['Exercise']['ghost']) {
      $exerciseCase = $this->ExerciseCase->findAllByExerciseId($exercise['Exercise']['real_id'], array('id'), array('id'));
    } else {
      $exerciseCase = $this->ExerciseCase->findAllByExerciseId($exercise_id, array('id'), array('id'));
    }
    $commit['ExerciseCase'] = array();
    foreach ($exerciseCase as $key => $item) {
      $commitExerciseCase = $this->CommitsExerciseCase->findByExerciseCaseIdAndCommitId($item['ExerciseCase']['id'], $commit_id, array('id', 'commit_id', 'exercise_case_id', 'cputime', 'memused', 'status', 'status_message'));
      if (count($commitExerciseCase) > 0) {
        $exerciseCase[$key]['ExerciseCase']['CommitsExerciseCase'] = $commitExerciseCase['CommitsExerciseCase'];
        array_push($commit['ExerciseCase'], $exerciseCase[$key]['ExerciseCase']);
      }
    }
    $commit['Exercise'] = $exercise['Exercise'];
    $breadcrumbs = array();
    array_push($breadcrumbs, array('link' => array('controller' => 'offerings', 'action' => 'view', $offering_id), 'text' => $course['Course']['code']));
    array_push($breadcrumbs, array('link' => array('controller' => 'exercises', 'action' => 'view', $exercise_id), 'text' => $exercise['Exercise']['title']));
    array_push($breadcrumbs, array('link' => '#', 'text' => __("Commit") . " " . date('d/m/Y H:i:s', strtotime($commit['Commit']['commit_time']))));
    $statusList = $this->Commit->getOptionsStatusList();
    $this->set('commit', $commit);
    $this->set(compact('breadcrumbs', 'statusList'));
  }

  public function getFileFromZip($commit_id = null, $zipFile = null)
  {
    if (!$this->Commit->exists($commit_id)) {
      throw new NotFoundException(__('Invalid commit'));
    }
    $this->loadModel("Archive");
    $this->autoRender = false;
    $this->Commit->recursive = 2;
    $commit = $this->Commit->findById($commit_id);

    if (strlen($commit['Commit']['aws_key']) > 0) {
      $fileFromAws = $this->Archive->getCommitFileContentFromAwsS3($commit['Commit']['aws_key']);
    }
    if ($fileFromAws === false) {
      Log::slackNotification("Integridade do Sistema", "O commit " . $commit_id . " não foi encontrado no S3 com chave `" . $commit['Commit']['aws_key'] . "`", array("https://run.codes/Commits/details/" . $commit_id));
      throw new NotFoundException(__('Invalid file'));
      //            $dirname = Configure::read('Upload.dir') . '/' . $commit['Exercise']['Offering']['course_id'] . '/' . $commit['Exercise']['offering_id'] . '/' . $commit['Exercise']['id'] . '/' . $commit['Commit']['user_email'] . '/' . $commit['Commit']['commit_time'] . '/';
      //            $dir = new Folder($dirname,false,0777);
      //            $files=$dir->find();
      //            $file = null;
      //            if(count($files) > 0) {
      //                $file = $files[0];
      //            }
      //
      //            if (!file_exists($dirname . $file) || is_dir($dirname . $file)) {
      //                throw new NotFoundException(__('Invalid file'));
      //            } else {
      //                $ext = explode('.', $file);
      //                $ext = $ext[count($ext) - 1];
      //                if ($ext == "zip") {
      //                    $zipFiles = array();
      //                    $zip = zip_open($dirname . $file);
      //                    while ($zip_content = zip_read($zip)) {
      //                        $contents = zip_entry_read($zip_content, zip_entry_filesize($zip_content));
      //                        $file_info = new finfo(FILEINFO_MIME);
      //                        $mime_type = $file_info->buffer($contents);
      //                        $zip_item = array('name' => zip_entry_name($zip_content), 'content' => $contents);
      //                        if (strlen($contents) > 0 && sha1($zip_item['name']) == $zipFile) {
      //                            //                        if(strpos($mime_type,'text') !== false) {
      //                            //                            array_push($zipFiles,$zip_item);
      //                            //                        }
      //                            if (strpos($mime_type, 'pdf') !== false) {
      //                                $zip_item['name'];
      //                                $tipo = "application/pdf";
      //                                //                            header("Content-Length: ".filesize($file));
      //                                header("Content-Type: " . $tipo);
      //                                header("Content-Disposition: inline; filename=" . $zip_item['name']);
      //                                echo $zip_item['content'];
      //                                exit;
      //                            }
      //                        }
      //                    }
      //                } else {
      //                    throw new NotFoundException(__('Invalid file'));
      //                }
      //            }
    } else {
      if ($fileFromAws["ext"] == "zip") {
        $zip = zip_open($fileFromAws["body"]);
        while ($zip_content = zip_read($zip)) {
          $contents = zip_entry_read($zip_content, zip_entry_filesize($zip_content));
          $file_info = new finfo(FILEINFO_MIME);
          $mime_type = $file_info->buffer($contents);
          $zip_item = array('name' => zip_entry_name($zip_content), 'content' => $contents);
          if (strlen($contents) > 0 && sha1($zip_item['name']) == $zipFile) {
            if (strpos($mime_type, 'pdf') !== false) {
              $tipo = "application/pdf";
              header("Content-Type: " . $tipo);
              header("Content-Disposition: inline; filename=" . $zip_item['name']);
              echo $zip_item['content'];
              exit;
            }
          }
        }
      } else {
        throw new NotFoundException(__('Invalid file'));
      }
    }
    throw new NotFoundException(__('Not Found'));
  }

  public function viewFile($commit_id = null)
  {
    $this->layout = "template2015";
    $this->loadModel("Archive");
    if (!$this->Commit->exists($commit_id)) {
      throw new NotFoundException(__('Invalid commit'));
    }
    $this->loadModel("Exercise");
    $this->loadModel("Offering");
    $this->Commit->recursive = -1;
    $commit = $this->Commit->findById($commit_id);

    $this->Commit->id = $commit_id;
    $commit['Exercise']['id'] = $this->Commit->getExerciseId();
    $this->Exercise->id = $commit['Exercise']['id'];
    $exercise = $this->Exercise->findById($commit['Exercise']['id'], array('id', 'title', 'offering_id'));
    $commit['Exercise'] = $exercise['Exercise'];
    $this->Offering->id = $commit['Exercise']['offering_id'];
    $commit['Exercise']['Offering']['course_id'] = $this->Offering->getCourseId();
    $this->loadModel('User');
    $this->User->recursive = 0;
    $user = $this->User->findByEmail($commit['Commit']['user_email']);
    $commit['User'] = $user['User'];
    $commit['User']['University'] = $user['University'];
    $statusList = $this->Commit->getOptionsStatusList();

    if (strlen($commit['Commit']['aws_key']) > 0) {
      $fileFromAws = $this->Archive->getCommitFileContentFromAwsS3($commit['Commit']['aws_key']);
    }

    if ($fileFromAws === false) {
      Log::slackNotification("Integridade do Sistema", "O commit " . $commit_id . " não foi encontrado no S3 com chave `" . $commit['Commit']['aws_key'] . "`", array("https://run.codes/Commits/details/" . $commit_id));
      throw new NotFoundException(__('Invalid file'));
      //            $dirname = Configure::read('Upload.dir').'/'.$commit['Exercise']['Offering']['course_id'].'/'.$commit['Exercise']['offering_id'].'/'.$commit['Exercise']['id'].'/'.$commit['Commit']['user_email'].'/'.$commit['Commit']['commit_time'].'/';
      //            $dir = new Folder($dirname,false,0777);
      //            $files=$dir->find();
      //            $file = null;
      //            if(count($files) > 0) {
      //                $file = $files[0];
      //            }
      //
      //            $file_content = '';
      //            if(!is_file($dirname.$file)) {
      //                throw new NotFoundException(__('Invalid file'));
      //            } else {
      //                $ext = explode('.', $file);
      //                $ext = $ext[count($ext)-1];
      //                if ($ext != "zip" && $ext != "exe" && $ext != "pdf") {
      //                    $file_content = file_get_contents($dirname.$file);
      //                    if(!mb_detect_encoding($file_content, 'UTF-8', true)) {
      //                        $file_content = utf8_encode($file_content);
      //                    }
      //                } else if ($ext == "pdf") {
      //                    $file_content = "/Commits/download/" . $commit_id;
      //                } else if($ext == "zip") {
      //                    $zipFiles = array();
      //                    $zip = zip_open($dirname.$file);
      //
      //                    while($zip_content = zip_read($zip)) {
      //                        $contents = zip_entry_read($zip_content,zip_entry_filesize($zip_content));
      //                        if(!mb_detect_encoding($contents, 'UTF-8', true)) {
      //                            $contents = utf8_encode($contents);
      //                        }
      //                        $file_info = new finfo(FILEINFO_MIME);
      //                        $mime_type = $file_info->buffer($contents);
      //    //                    debug($mime_type);
      //                        $zip_item = array('name' => zip_entry_name($zip_content), 'content' => $contents);
      //                        if(strlen($contents) > 0 && strpos($mime_type,'text') !== false) {
      //                            $zip_item['type'] = 'text';
      //                            array_push($zipFiles,$zip_item);
      //                        }elseif (strpos($mime_type,'pdf') !== false) {
      //                            $zip_item['type'] = 'pdf';
      //                            $zip_item['content'] = '/Commits/getFileFromZip/'.$commit_id.'/'.sha1($zip_item['name']);
      //                            array_push($zipFiles,$zip_item);
      //                        }
      //                    }
      //                    $this->set(compact('zipFiles'));
      //                }
      //            }
    } else {
      Log::register("Opened the file of Commit #" . $commit['Commit']['exercise_id'] . " from AWS S3", $this->currentUser);
      $ext = $fileFromAws["ext"];
      if ($ext == "pdf") {
        $file_content = "/Commits/download/" . $commit_id;
      } elseif ($ext == "zip") {
        $zipFiles = array();
        $zip = zip_open($fileFromAws["body"]);

        while ($zip_content = zip_read($zip)) {
          $contents = zip_entry_read($zip_content, zip_entry_filesize($zip_content));
          if (!mb_detect_encoding($contents, 'UTF-8', true)) {
            $contents = utf8_encode($contents);
          }
          $file_info = new finfo(FILEINFO_MIME);
          $mime_type = $file_info->buffer($contents);
          $zip_item = array('name' => zip_entry_name($zip_content), 'content' => $contents);
          if (strlen($contents) > 0 && strpos($mime_type, 'text') !== false) {
            $zip_item['type'] = 'text';
            array_push($zipFiles, $zip_item);
          } elseif (strpos($mime_type, 'pdf') !== false) {
            $zip_item['type'] = 'pdf';
            $zip_item['content'] = '/Commits/getFileFromZip/' . $commit_id . '/' . sha1($zip_item['name']);
            array_push($zipFiles, $zip_item);
          }
        }
        $this->set(compact('zipFiles'));
      } else {
        $file_content = $fileFromAws["body"];
        if (!mb_detect_encoding($file_content, 'UTF-8', true)) {
          $file_content = utf8_encode($file_content);
        }
      }
    }
    $this->loadModel('Course');
    $course = $this->Course->findById($commit['Exercise']['Offering']['course_id'], array('fields' => 'code'));
    $breadcrumbs = array();
    array_push($breadcrumbs, array('link' => array('controller' => 'offerings', 'action' => 'view', $commit['Exercise']['offering_id']), 'text' => $course['Course']['code']));
    array_push($breadcrumbs, array('link' => array('controller' => 'exercises', 'action' => 'view', $commit['Exercise']['id']), 'text' => $exercise['Exercise']['title']));
    array_push($breadcrumbs, array('link' => array('controller' => 'commits', 'action' => 'details', $commit['Commit']['id']), 'text' => __("Commit") . " " . date('d/m/Y H:i:s', strtotime($commit['Commit']['commit_time']))));
    array_push($breadcrumbs, array('link' => '#', 'text' => __("View File")));

    $this->set(compact('commit', 'file', 'file_content', 'ext', 'breadcrumbs', 'statusList'));
  }


  public function recompile($commit_id = null)
  {
    if (!$this->Commit->exists($commit_id)) {
      throw new NotFoundException(__('Invalid commit'));
    }
    $this->loadModel('Exercise');
    $this->Commit->id = $commit_id;
    $this->Exercise->id = $this->Commit->getExerciseId();
    if ($this->Exercise->field('type') == 1) {
      $this->Session->setFlash(__('This exercise does not allow the compilation of commits'), 'default', array(), 'flash');
      $this->redirect(array('controller' => 'Exercises', 'action' => 'viewProfessor', $this->Exercise->id));
    }
    if ($this->currentUser['type'] < 3) {
      if ($this->Commit->field('status') < $this->Commit->getUncompletedStatusValue()) {
        $this->Session->setFlash(__('This commit are being processed now'), 'default', array(), 'flash');
        $this->redirect(array('controller' => 'Exercises', 'action' => 'viewProfessor', $this->Exercise->id));
      }
    }
    $this->loadModel('CommitsExerciseCase');
    $this->CommitsExerciseCase->deleteAll(array('commit_id' => $commit_id));
    $this->Commit->saveField('status', $this->Commit->getInQueueStatusValue());
    $this->Commit->saveField('compiled_message', "");
    $this->Commit->saveField('compiled', false);
    $this->Commit->saveField('corrects', 0);
    $this->Commit->saveField('score', 0);
    Log::register("Recompiled Commit #" . $commit_id, $this->currentUser);
    $this->Session->setFlash(__('The exercise will be recompiled soon'), 'default', array(), 'success');
    $commit = $this->Commit->findById($commit_id);
    $this->redirect(array('controller' => 'Exercises', 'action' => 'viewProfessor', $commit['Commit']['exercise_id']));
  }

  public function recompileAll($exercise_id = null)
  {
    $this->loadModel('Exercise');
    if (!$this->Exercise->exists($exercise_id)) {
      throw new NotFoundException(__('Invalid exercise'));
    }
    $this->Exercise->id = $exercise_id;
    if ($this->Exercise->field('type') == 1) {
      $this->Session->setFlash(__('This exercise does not allow the compilation of commits'), 'default', array(), 'flash');
      $this->redirect(array('controller' => 'Exercises', 'action' => 'viewProfessor', $exercise_id));
    }

    $this->Commit->recursive = -1;
    $scores = $this->Commit->find('all', array('fields' => array('Commit.id', 'Commit.status'), 'conditions' => array('Commit.exercise_id' => $exercise_id, " Commit.commit_time = (SELECT MAX(commit_time) FROM commits c2 WHERE Commit.user_email = c2.user_email AND Commit.exercise_id=c2.exercise_id GROUP BY user_email, exercise_id)"), 'order' => 'score DESC, commit_time ASC'));
    $this->loadModel('CommitsExerciseCase');
    if ($this->currentUser['type'] < 3) {
      foreach ($scores as $s) {
        if ($s['Commit']['status'] < $this->Commit->getUncompletedStatusValue()) {
          $this->Session->setFlash(__('Some commits are being processed now, please wait all results to recompile all again'));
          $this->redirect(array('controller' => 'Exercises', 'action' => 'viewProfessor', $exercise_id));
        }
      }
    }
    foreach ($scores as $s) {
      $this->Commit->id = $s['Commit']['id'];
      $this->CommitsExerciseCase->deleteAll(array('commit_id' => $s['Commit']['id']));
      $this->Commit->saveField('status', $this->Commit->getInQueueStatusValue());
      $this->Commit->saveField('compiled_message', "");
      $this->Commit->saveField('compiled', false);
      $this->Commit->saveField('corrects', 0);
      $this->Commit->saveField('score', 0);
    }
    Log::register("Recompiled All from Exercise #" . $exercise_id, $this->currentUser);
    $this->Session->setFlash(__('The exercises will be recompiled soon'), 'default', array(), 'success');
    $this->redirect(array('controller' => 'Exercises', 'action' => 'viewProfessor', $exercise_id));
  }

  public function editScore($commit_id = null)
  {
    if (!$this->Commit->exists($commit_id)) {
      throw new NotFoundException(__('Invalid commit'));
    }
    $this->layout = "ajax";
    if ($this->request->is('post') || $this->request->is('put')) {
      //Validar se usuario ter permissao para alterar este commit
      $this->Commit->id = $commit_id;
      $this->Commit->saveField('score', $this->request->data['Commit']['score']);
      $this->Commit->saveField('status', $this->request->data['Commit']['status']);
      $commit = $this->Commit->findById($commit_id);
      Log::register("Changed score of Commit #" . $commit_id . " (Score: " . $this->request->data['Commit']['score'] . " Status: " . $this->request->data['Commit']['status'] . ")", $this->currentUser);
      $this->redirect(array('controller' => 'Exercises', 'action' => 'viewProfessor', $commit['Commit']['exercise_id']));
    }
    $commit = $this->Commit->findById($commit_id);
    $this->Commit->id = $commit_id;
    $this->loadModel('User');
    $this->User->recursive = -1;
    $user_email = $this->Commit->getUserEmail();
    $user = $this->User->findByEmail($user_email);
    $commit['User'] = $user['User'];
    $statusList = $this->Commit->getOptionsStatusList();
    $this->request->data = $commit;
    $this->set(compact('commit', 'statusList'));
  }
}
