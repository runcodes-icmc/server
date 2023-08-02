<?php
App::uses('AppController', 'Controller');

class LogsController extends AppController {
    
    public $components = array('Paginator');
    public $paginate = array(
        'limit' => 50,
        'order' => array(
            'datetime' => 'DESC'
        )
    );
        
    public function isAuthorized($user = null) {
        $this->loadModel('User');
        if($user['type'] >= $this->User->getAdminIndex()) return true;
        else return false;
    }
    
    public function index() {
        $this->layout = "template2015";
        $cond = array();

        if ($this->request->is('post')) {
            $this->redirect(array('controller' => 'Logs',
                'action' => 'index',
                'user_email' => $this->request->data['Log']['user_email'],
                'ip' => $this->request->data['Log']['ip']));
        }

        if (isset($this->request->params['named']['user_email']) && strlen($this->request->params['named']['user_email']) > 0) {
            $cond['Log.user_email ILIKE'] = '%'.$this->request->params['named']['user_email'].'%';
        }
        if (isset($this->request->params['named']['ip']) && strlen($this->request->params['named']['ip']) > 0) {
            $cond['Log.ip ILIKE'] = '%'.$this->request->params['named']['ip'].'%';
        }

        $this->Paginator->settings = $this->paginate;
        $this->set('logs', $this->Paginator->paginate($cond));
    }
}