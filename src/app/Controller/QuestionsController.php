<?php
App::uses('AppController', 'Controller');

class QuestionsController extends AppController {

    public $components = array('Paginator');
    private $studentAuthorized = array('modal','view');
    
    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('modal');
    }

    public function isAuthorized($user = null) {
        $this->loadModel('User');
        
        if($user['type'] >= $this->User->getAdminIndex() || strtolower($this->request->params['action']) == "modal") return true;
        else {
            if (in_array(strtolower($this->request->params['action']), $this->studentAuthorized)) {
                return true;
            }
            return false;
        };
    }
    
    public function modal($id) {
        if (!$this->Question->exists($id)) {
                throw new NotFoundException(__('Invalid question'));
        }
        $this->layout = "ajax";
        $this->Question->recursive = -1;
        $this->set("question",$this->Question->findById($id));
    }
    
    public function view() {
        $this->Question->recursive = -1;
        $this->set('questions',$this->Question->find('all'));
    }
    
    public function index() {
        $this->Question->recursive = -1;
        $this->set('questions',$this->Paginator->paginate());
    }
    
    public function add() {
        if ($this->request->is('post')) {
            $this->Question->create();
            if ($this->Question->save($this->request->data)) {
                $this->Session->setFlash(__('The question has been saved'), 'default', array(), 'success');
                return $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The question could not be saved. Please, try again.'), 'default', array(), 'flash');
            }
        }
    }
    
    public function edit($id = null) {
        if (!$this->Question->exists($id)) {
            $this->Session->setFlash(__('Invalid question'), 'default', array(), 'flash');
            return $this->redirect(array('action' => 'index'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->Question->save($this->request->data)) {
                $this->Session->setFlash(__('The question has been saved'), 'default', array(), 'success');
                return $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The question could not be saved. Please, try again.'), 'default', array(), 'flash');
            }
        } else {
            $this->request->data = $this->Question->findById($id);
        }
    }
    
    public function delete($id = null) {
        $this->Question->id = $id;
        if (!$this->Question->exists()) {
                throw new NotFoundException(__('Invalid question'));
        }
        $this->request->onlyAllow('post', 'delete');
        if ($this->Question->delete()) {
                $this->Session->setFlash(__('The question was deleted with success'),'default',array(),'success');
                return $this->redirect(array('action' => 'index'));
        }
        $this->Session->setFlash(__('Question was not deleted'));
        return $this->redirect(array('action' => 'index'));
    }
}