<?php
App::uses('AppController', 'Controller');
App::uses('Folder', 'Utility');
/**
 * ExerciseCaseFiles Controller
 *
 * @property ExerciseCaseFile $ExerciseCaseFile
 * @property PaginatorComponent $Paginator
 */
class ExerciseCaseFilesController extends AppController
{

  public function isAuthorized($user = null)
  {
    $this->loadModel('User');

    if (strtolower($this->request->params['action']) == "fileupload" || strtolower($this->request->params['action']) == "delete") {
      return true;
    }
    if ($user['type'] >= $this->User->getProfessorAssistantIndex()) return true;
    else return false;
  }
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

  public function fileUpload()
  {
    $this->layout = 'ajax';
    $this->autoRender = false;
    $this->response->type('json');
    $this->loadModel("Archive");
    $upload = isset($_FILES["files"]) ? $_FILES["files"] : null;
    $result = $this->Archive->handleFileUpload($upload, "tmp/inputfiles");
    echo json_encode($result);
    return false;
  }
}
