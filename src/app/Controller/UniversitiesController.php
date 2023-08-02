<?php
App::uses('AppController', 'Controller');
/**
 * Tickets Controller
 *
 * @property Ticket $Ticket
 * @property PaginatorComponent $Paginator
 */
class UniversitiesController extends AppController {
    
    public $components = array('Paginator');
    private $studentAuthorized = array('getidentifiertext','getuniversitieslist');

    public function isAuthorized($user = null) {
        $this->loadModel('User');
        if ($user['type'] >= $this->User->getAdminIndex()) {
            return true;
        }
        if($user['type'] <= $this->User->getAdminIndex()) {
            if(in_array(strtolower($this->request->params['action']), $this->studentAuthorized)) return true;
            else return false;
        }
        
        return false;
    }

    public function add () {
        if ($this->request->is('post')) {
            if($this->University->save($this->request->data)) {
                $this->Session->setFlash(__('The university has been saved'), 'default', array(), 'success');
                Log::register("Added the university '".$this->request->data['University']['abbreviation']."'", $this->currentUser);
            } else {
                $this->Session->setFlash(__('The university could not be saved. Please, try again.'));
            }
        }
        $this->redirect(array('controller' => "Universities"));
    }

    public function edit ($university_id) {
        if ($this->request->is('post')) {
            $this->University->id = $university_id;
            if($this->University->save($this->request->data)) {
                $this->Session->setFlash(__('The university has been saved'), 'default', array(), 'success');
                Log::register("Edited the university '".$this->request->data['University']['abbreviation']."'", $this->currentUser);
            } else {
                $this->Session->setFlash(__('The university could not be saved. Please, try again.'));
                debug($this->University->validationErrors);
            }
        }
        $this->redirect(array('controller' => "Universities"));
    }
    
    public function index($startswith = null) {
        $this->layout = "template2015";
        $this->University->recursive = 0;
        $cond = array();
        if (isset($this->request->params['named']['startswith'])) {
            $cond['University.name ILIKE'] = $this->request->params['named']['startswith'].'%';
            $startswith = strtoupper($this->request->params['named']['startswith']);
        }
        $universities = $this->Paginator->paginate($cond);
        foreach ($universities as $k => $uni) {
            $universities[$k]['University']['num_users'] = $this->User->find('count',array('conditions' => array('university_id' => $uni['University']['id'])));
        }
        $edit = false;
        if (isset($this->request->params['named']['edit'])) {
            $this->request->data = $this->University->findById($this->request->params['named']['edit']);
            $edit = true;
        }
        $this->set('edit',$edit);

        $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'); 
        $this->set('letters',$letters);
        $this->set('startswith',$startswith);
        $this->set('universities', $universities);
        $this->set('universitiesType',$this->University->getAdminTypesList());
    }
    
    public function getIdentifierText($id = null) {
        if (!$this->request->is('post')) {
            $this->redirect('/home');
        }
        $this->layout = 'ajax';
        $this->autoRender = false;
        $this->University->id = $id;
        if (!$this->University->exists()) {
            throw new NotFoundException("University not found");
        } 
        echo $this->University->field('student_identifier_text');
        exit();
    }

    public function getUniversitiesList ($type = null) {
        if (!$this->request->is('post')) {
            $this->redirect('/home');
        }
        if (is_null($type)) {
            echo json_encode($this->University->find('all',array('fields' => array('id','both'),'order' => array("name" => "ASC"))));
        } else {
            echo json_encode($this->University->find('all',array('fields' => array('id','both'),'conditions' => array('type' => $type),'order' => array("name" => "ASC"))));
        }
        exit();
    }
}

?>
