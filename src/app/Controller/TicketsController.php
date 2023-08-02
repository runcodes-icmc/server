<?php
App::uses('AppController', 'Controller');
/**
 * Tickets Controller
 *
 * @property Ticket $Ticket
 * @property PaginatorComponent $Paginator
 */
class TicketsController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator');
        
    private $studentAuthorized = array('add');

    public function isAuthorized($user = null) {
        $this->loadModel('User');
        if($user['type'] >= $this->User->getProfessorAssistantIndex()) return true;
        else{
            if(in_array(strtolower($this->request->params['action']), $this->studentAuthorized)) return true;
            else return false;
        }
    }

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->Ticket->recursive = 0;
                $tickets = $this->Ticket->find('all',array('order'=>'Ticket.status, Ticket.priority'));
                $this->Paginator->settings = array('order'=>'Ticket.status, Ticket.priority');
                $tickets = $this->Paginator->paginate();
		$this->set('tickets', $tickets);
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->Ticket->exists($id)) {
			throw new NotFoundException(__('Invalid ticket'));
		}
		$options = array('conditions' => array('Ticket.' . $this->Ticket->primaryKey => $id));
		$this->set('ticket', $this->Ticket->find('first', $options));
	}
        
        public function setAsSolved($id = null) {
		if (!$this->Ticket->exists($id)) {
			throw new NotFoundException(__('Invalid ticket'));
                        return $this->redirect(array('action'=>'index'));
		}
                $this->autoRender = false;
                $this->Ticket->id=$id;
		$this->Ticket->saveField('solved', true);
                return $this->redirect(array('action'=>'index'));
	}
        public function close($id = null) {
		if (!$this->Ticket->exists($id)) {
			throw new NotFoundException(__('Invalid ticket'));
                        return $this->redirect(array('action'=>'index'));
		}
                $this->autoRender = false;
                $this->Ticket->id=$id;
		$this->Ticket->saveField('status', $this->Ticket->getClosedStatusValue());
                return $this->redirect(array('action'=>'index'));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Ticket->create();
                        $this->request->data['Ticket']['users_email']=$this->Auth->user('email');
                        $this->request->data['Ticket']['solved']=false;
                        unset($this->request->data['Ticket']['datetime']);
                        $this->request->data['Ticket']['status']=$this->Ticket->getDefaultStatusValue();
                        pr($this->request->data);
			if ($this->Ticket->save($this->request->data)) {
                            Log::register("The user has sent a ticket", $this->currentUser);
                            $this->Session->setFlash(__('The ticket has been saved'),'default',array(), 'success');
                            return $this->redirect('/home');
			} else {
                            $this->Session->setFlash(__('The ticket could not be saved. Please, try again.'));
                            return $this->redirect('/home');
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
	public function edit($id = null) {
		if (!$this->Ticket->exists($id)) {
			throw new NotFoundException(__('Invalid ticket'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Ticket->save($this->request->data)) {
				$this->Session->setFlash(__('The ticket has been saved'),'default',array(), 'success');
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The ticket could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Ticket.' . $this->Ticket->primaryKey => $id));
			$this->request->data = $this->Ticket->find('first', $options);
		}
		$users = $this->Ticket->User->find('list');
		$this->set(compact('users'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Ticket->id = $id;
		if (!$this->Ticket->exists()) {
			throw new NotFoundException(__('Invalid ticket'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->Ticket->delete()) {
			$this->Session->setFlash(__('Ticket deleted'));
			return $this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Ticket was not deleted'));
		return $this->redirect(array('action' => 'index'));
	}
}
