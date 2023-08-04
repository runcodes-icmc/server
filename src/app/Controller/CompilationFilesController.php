<?php
App::uses('AppController', 'Controller');
App::uses('Folder', 'Utility');
/**
 * ExerciseFiles Controller
 *
 * @property ExerciseFile $ExerciseFile
 * @property PaginatorComponent $Paginator
 */
class CompilationFilesController extends AppController
{

  /**
   * Components
   *
   * @var array
   */
  public $components = array('Paginator');

  protected $error_messages = array(
    1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
    2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
    3 => 'The uploaded file was only partially uploaded',
    4 => 'No file was uploaded',
    6 => 'Missing a temporary folder',
    7 => 'Failed to write file to disk',
    8 => 'A PHP extension stopped the file upload',
    'post_max_size' => 'The uploaded file exceeds the post_max_size directive in php.ini',
    'max_file_size' => 'File is too big',
    'min_file_size' => 'File is too small',
    'accept_file_types' => 'Filetype not allowed',
    'max_number_of_files' => 'Maximum number of files exceeded',
    'max_width' => 'Image exceeds maximum width',
    'min_width' => 'Image requires a minimum width',
    'max_height' => 'Image exceeds maximum height',
    'min_height' => 'Image requires a minimum height'
  );
  private $studentAuthorized = array('filedownload');
  private $professorAuthorized = array('delete', 'filedownload');

  public function isAuthorized($user = null)
  {
    $this->loadModel('User');
    $this->loadModel('Enrollment');
    $this->loadModel('Exercise');

    if ($user['type'] >= $this->User->getAdminIndex()) {
      return true;
    }
    if (strtolower($this->request->params['action']) == "fileupload" || strtolower($this->request->params['action']) == "delete") {
      return true;
    }
    $this->ExerciseFile->recursive = -1;
    $this->Exercise->recursive = -1;
    $this->ExerciseFile->id = $this->request->params['pass'][0];
    $exercise_id = $this->ExerciseFile->getExerciseId();
    $this->Exercise->id = $exercise_id;
    $offering = $this->Exercise->getOfferingId();

    if ($user['type'] >= $this->User->getStudentIndex()) {
      if (in_array(strtolower($this->request->params['action']), $this->professorAuthorized)) {
        if ($this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $offering)) {
          return true;
        }
      }
      if (in_array(strtolower($this->request->params['action']), $this->studentAuthorized)) {
        if ($this->Enrollment->isEnrolled($this->currentUser['email'], $offering)) {
          return true;
        }
      } else {
        return false;
      }
    }
    return false;
  }

  public function fileUpload()
  {
    $this->layout = 'ajax';
    $this->autoRender = false;
    $this->response->type('json');
    $this->loadModel("Archive");
    $upload = isset($_FILES["files"]) ? $_FILES["files"] : null;
    $result = $this->Archive->handleFileUpload($upload, "tmp/compilationfiles");
    echo json_encode($result);
    return false;
  }

  public function fileDownload($id = null)
  {
    if (!$this->CompilationFile->exists($id)) {
      throw new NotFoundException(__('Invalid exercise compilation file'));
    }
    $this->loadModel("Archive");
    $this->CompilationFile->recursive = -1;
    $compilationFile = $this->CompilationFile->findById($id);
    $awsFile = $this->Archive->getFileFromAwsS3("compilationfiles" . DS . $compilationFile["CompilationFile"]["exercise_id"], $compilationFile["CompilationFile"]["path"]);
    if ($awsFile !== false) {
      switch ($awsFile["ext"]) {
        case "exe":
          throw new NotFoundException();
        case "php":
          throw new NotFoundException();
        case "htm":
          throw new NotFoundException();
        case "html":
          throw new NotFoundException();
      }
      Log::register("Downloaded the ExerciseFile " . $compilationFile["CompilationFile"]["path"] . "(Exercise: " . $compilationFile["CompilationFile"]["exercise_id"] . ") from AWS S3", $this->currentUser);
      header("Content-Type: " . $awsFile["result"]['ContentType']);
      header("Content-Length: " . $awsFile["result"]['ContentLength']);
      header("Content-Disposition: inline; filename=" . $compilationFile["CompilationFile"]["path"]);
      echo $awsFile["result"]['Body'];
      exit;
    }
    //            $this->CompilationFile->recursive=2;
    //            $exercise=$this->CompilationFile->findById($id);
    //            $file = Configure::read('Upload.dir').'/compilationfiles/'.$exercise['Exercise']['Offering']['course_id'].'/'.$exercise['Exercise']['offering_id'].'/'.$exercise['Exercise']['id'].'/'.$exercise['CompilationFile']['path'];
    //                if(file_exists($file)) {
    //                    $elements = explode(".",basename($file));
    //                    $ext = $elements[count($elements) - 1];
    //                    switch(strtolower($ext)){
    //                        case "pdf": $tipo="application/pdf"; break;
    //                        case "exe": $tipo="application/octet-stream"; break;
    //                        case "zip": $tipo="application/zip"; break;
    //                        case "doc": $tipo="application/msword"; break;
    //                        case "xls": $tipo="application/vnd.ms-excel"; break;
    //                        case "ppt": $tipo="application/vnd.ms-powerpoint"; break;
    //                        case "gif": $tipo="image/gif"; break;
    //                        case "png": $tipo="image/png"; break;
    //                        case "jpg": $tipo="image/jpg"; break;
    //                        case "mp3": $tipo="audio/mpeg"; break;
    //                        case "Makefile": $tipo="text/plain"; break;
    //                        case "php":
    //                        case "htm":
    //                        case "html":
    //                    }
    //                    if (isset($tipo)) header("Content-Type: ".$tipo);
    //                    header("Content-Length: ".filesize($file));
    //                    header("Content-Disposition: attachment; filename=".basename($file));
    //                    readfile($file);
    //                    exit;
    //                } else {
    //                    throw new NotFoundException(__('This file does not exist. Please verify the system integrity'));
    //                }
    $this->autoRender = false;
  }

  //        public function delete() {
  //            if (!$this->request->is('post')) {
  //                throw new NotFoundException();
  //            }
  //            $this->autoRender = false;
  //            $this->response = 'json';
  //            if (isset($_POST['dataExerciseFileId'])) {
  //                $id = $_POST['dataExerciseFileId'];
  //                $this->CompilationFile->recursive = -1;
  //                $this->CompilationFile->id = $id;
  //
  //                $exerciseId = $this->CompilationFile->getExerciseId();
  //                $this->loadModel('Exercise');
  //                $this->loadModel('Enrollment');
  //                $this->Exercise->id = $exerciseId;
  //                $offeringId = $this->Exercise->getOfferingId();
  //                $this->loadModel('Offering');
  //                $this->Offering->id = $offeringId;
  //                $courseId = $this->Offering->getCourseId();
  //                $dir = Configure::read('Upload.dir'). DS . "compilationfiles" . DS . $courseId . DS . $offeringId . DS . $exerciseId . DS;
  //                if (!$this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $offeringId) && $this->currentUser['type'] <= 2) {
  //                    throw new NotFoundException();
  //                }
  //                $file = $this->CompilationFile->findById($id,array('path'));
  //                if ($this->CompilationFile->delete()) {
  //                    if (file_exists($dir.$file['CompilationFile']['path']) && !is_dir($dir.$file['CompilationFile']['path'])) {
  //                        unlink($dir.$file['CompilationFile']['path']);
  //                        Log::register("Removed the Compilation File ".$file['CompilationFile']['path']." from exercise #".$exerciseId, $this->currentUser);
  //                    }
  //                    echo "1";
  //                } else {
  //                    echo "0";
  //                }
  //            }
  //        }

}
