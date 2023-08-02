<?php
App::uses('AppController', 'Controller');
/**
 * AllowedFiles Controller
 *
 * @property AllowedFile $AllowedFile
 * @property PaginatorComponent $Paginator
 */
class AllowedFilesController extends AppController
{

  /**
   * Components
   *
   * @var array
   */
  public $components = array('Paginator');

  public function isAuthorized($user = null)
  {
    $this->loadModel('User');
    if ($user['type'] >= $this->User->getAdminIndex()) return true;
    if (strtolower($this->request->params['action']) == "getallowedfileslist") {
      return true;
    } else return false;
  }

  public function getAllowedFilesList($type = null)
  {
    if (!$this->request->is('post')) {
      $this->redirect('/home');
    }
    $this->autoRender = false;
    $allowed = $this->AllowedFile->getAllowedFilesList($type);
    $filesList = array();
    foreach ($allowed as $al) {
      $file = new stdClass();
      $file->id = $al['AllowedFile']['id'];
      $file->name = $al['AllowedFile']['name'] . " (" . $al['AllowedFile']['extension'] . ")";
      array_push($filesList, $file);
    }
    echo json_encode($filesList);
  }

  /**
   * index method
   *
   * @return void
   */
  public function index()
  {
    $this->AllowedFile->recursive = 0;
    $this->set('allowedFiles', $this->Paginator->paginate());
  }

  /**
   * view method
   *
   * @throws NotFoundException
   * @param string $id
   * @return void
   */
  public function view($id = null)
  {
    if (!$this->AllowedFile->exists($id)) {
      throw new NotFoundException(__('Invalid allowed file'));
    }
    $options = array('conditions' => array('AllowedFile.' . $this->AllowedFile->primaryKey => $id));
    $this->set('allowedFile', $this->AllowedFile->find('first', $options));
  }

  /**
   * add method
   *
   * @return void
   */
  public function add()
  {
    if ($this->request->is('post')) {
      $this->AllowedFile->create();
      if ($this->AllowedFile->save($this->request->data)) {
        $this->Session->setFlash(__('The allowed file has been saved'));
        return $this->redirect(array('action' => 'index'));
      } else {
        $this->Session->setFlash(__('The allowed file could not be saved. Please, try again.'));
      }
    }
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
    if (!$this->AllowedFile->exists($id)) {
      throw new NotFoundException(__('Invalid allowed file'));
    }
    if ($this->request->is('post') || $this->request->is('put')) {
      if ($this->AllowedFile->save($this->request->data)) {
        $this->Session->setFlash(__('The allowed file has been saved'));
        return $this->redirect(array('action' => 'index'));
      } else {
        $this->Session->setFlash(__('The allowed file could not be saved. Please, try again.'));
      }
    } else {
      $options = array('conditions' => array('AllowedFile.' . $this->AllowedFile->primaryKey => $id));
      $this->request->data = $this->AllowedFile->find('first', $options);
    }
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
    $this->AllowedFile->id = $id;
    if (!$this->AllowedFile->exists()) {
      throw new NotFoundException(__('Invalid allowed file'));
    }
    $this->request->onlyAllow('post', 'delete');
    if ($this->AllowedFile->delete()) {
      $this->Session->setFlash(__('Allowed file deleted'));
      return $this->redirect(array('action' => 'index'));
    }
    $this->Session->setFlash(__('Allowed file was not deleted'));
    return $this->redirect(array('action' => 'index'));
  }
}
