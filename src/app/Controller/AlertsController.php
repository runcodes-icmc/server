<?php
App::uses('AppController', 'Controller');
/**
 * Alerts Controller
 *
 * @property Alert $Alert
 * @property PaginatorComponent $Paginator
 */
class AlertsController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator');
        
        public function beforeFilter(){
            parent::beforeFilter();
            
        }

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->Alert->recursive = 0;
		$this->set('alerts', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->Alert->exists($id)) {
			throw new NotFoundException(__('Invalid alert'));
		}
		$options = array('conditions' => array('Alert.' . $this->Alert->primaryKey => $id));
		$this->set('alert', $this->Alert->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Alert->create();
                        $this->request->data['Alert']['user_email']=$this->Auth->user('email');
			if ($this->Alert->save($this->request->data)) {
                        Log::register("Publish an Alert '".$this->request->data['Alert']['title']."'", $this->currentUser);
//				$this->Session->setFlash(__('The alert has been saved'));
				return $this->redirect('/home');
			} else {
				$this->Session->setFlash(__('The alert could not be saved. Please, try again.'));
                                $this->Session->write('homeAlertValidationErrors',$this->Alert->validationErrors);
                                $this->Session->write('homeAlertData',$this->request->data);
				return $this->redirect('/home');
			}
		}
		$offerings = $this->Alert->Offering->find('list');
                $offerings['-1']=__("All");
		$users = $this->Alert->User->find('list');
                $this->set('alert_types',$this->Alert->getAlertTypesList());
		$this->set(compact('offerings', 'users'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->Alert->exists($id)) {
			throw new NotFoundException(__('Invalid alert'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Alert->save($this->request->data)) {
				$this->Session->setFlash(__('The alert has been saved'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The alert could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Alert.' . $this->Alert->primaryKey => $id));
			$this->request->data = $this->Alert->find('first', $options);
		}
		$offerings = $this->Alert->Offering->find('list');
		$users = $this->Alert->User->find('list');
		$this->set(compact('offerings', 'users'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Alert->id = $id;
		if (!$this->Alert->exists()) {
			throw new NotFoundException(__('Invalid alert'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->Alert->delete()) {
			$this->Session->setFlash(__('Alert deleted'));
			return $this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Alert was not deleted'));
		return $this->redirect(array('action' => 'index'));
	}
}
