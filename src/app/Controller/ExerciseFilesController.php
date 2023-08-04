<?php
App::uses('AppController', 'Controller');
App::uses('Folder', 'Utility');
/**
 * ExerciseFiles Controller
 *
 * @property ExerciseFile $ExerciseFile
 * @property PaginatorComponent $Paginator
 */
class ExerciseFilesController extends AppController
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
        //temporario
        return true;
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
    $this->loadModel("Archive");
    $this->layout = 'ajax';
    $this->autoRender = false;
    $this->response->type('json');
    $upload = isset($_FILES["files"]) ? $_FILES["files"] : null;
    $result = $this->Archive->handleFileUpload($upload, "tmp/exercisefiles");
    echo json_encode($result);
    return false;
  }

  public function fileDownload($id = null)
  {
    if (!$this->ExerciseFile->exists($id)) {
      throw new NotFoundException(__('Invalid exercise file'));
    }
    $this->loadModel("Archive");
    $this->ExerciseFile->recursive = -1;
    $exerciseFile = $this->ExerciseFile->findById($id);
    $this->redirect($this->Archive->getFileDownloadLinkFromAwsS3("exercisefiles" . DS . $exerciseFile["ExerciseFile"]["exercise_id"], $exerciseFile["ExerciseFile"]["path"]));


    $awsFile = $this->Archive->getFileFromAwsS3("exercisefiles" . DS . $exerciseFile["ExerciseFile"]["exercise_id"], $exerciseFile["ExerciseFile"]["path"]);
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
      Log::register("Downloaded the ExerciseFile " . $exerciseFile["ExerciseFile"]["path"] . "(Exercise: " . $exerciseFile["ExerciseFile"]["exercise_id"] . ") from AWS S3", $this->currentUser);
      header("Content-Type: " . $awsFile["result"]['ContentType']);
      header("Content-Length: " . $awsFile["result"]['ContentLength']);
      header("Content-Disposition: inline; filename=" . $exerciseFile["ExerciseFile"]["path"]);
      echo $awsFile["result"]['Body'];
      exit;
    }
    $this->autoRender = false;
  }
}
