<?php
App::uses('AppController', 'Controller');

class EnrollmentsController extends AppController
{

  public $components = array('Paginator');

  private $studentAuthorized = array('add');
  private $professorAuthorized = array('delete');


  public function isAuthorized($user = null)
  {
    $this->loadModel('User');
    if ($user['type'] >= $this->User->getAdminIndex()) return true;

    if (in_array(strtolower($this->request->params['action']), $this->professorAuthorized)) {
      $this->Enrollment->id = $this->request->params['pass'][0];
      if ($this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $this->Enrollment->getOfferingId())) {
        return true;
      }
    }
    if ($user['type'] <= $this->User->getAdminIndex()) {
      if (in_array(strtolower($this->request->params['action']), $this->studentAuthorized)) return true;
    }
    return false;
  }

  public function index()
  {
    $this->loadModel('Offering');
    $this->Offering->recursive = 1;
    //$offering = $this->Offering->find('list',array('fields' => array('id','list_name')));
    $offering_data = $this->Offering->find('all', array('conditions' => "NOT EXISTS (SELECT * FROM enrollments WHERE user_email='" . $this->Auth->user('email') . "' AND offering_id=Offering.id)"));
    $offering = array();
    foreach ($offering_data as $offering_line) {
      $offering[$offering_line['Offering']['id']] = $offering_line['Course']['code'] . " - " . $offering_line['Course']['title'] . " (" . $offering_line['Offering']['classroom'] . ")";
    }
    $this->set('offering', $offering);

    $this->Enrollment->recursive = 2;
    $enrollments_open = $this->Enrollment->find('all', array('conditions' => array('user_email' => $this->Auth->user('email'), 'Offering.end_date >= NOW()')));
    $this->Paginator->settings = array(
      'conditions' => array('user_email' => $this->Auth->user('email'), 'Offering.end_date < NOW()')
    );
    $this->set('page', 'courses');
    $this->set('enrollments', $enrollments_open);
    $this->set('enrollments_closed', $this->Paginator->paginate());
  }

  public function view($id = null)
  {
    if (!$this->Enrollment->exists($id)) {
      throw new NotFoundException(__('Invalid enrollment'));
    }
    $options = array('conditions' => array('Enrollment.' . $this->Enrollment->primaryKey => $id));
    $this->set('enrollment', $this->Enrollment->find('first', $options));
  }

  /**
   * add method
   *
   * @return void
   */
  public function add()
  {
    if ($this->request->is('post')) {
      $this->loadModel('Offering');
      $this->Offering->recursive = 0;
      $offering = $this->Offering->findByEnrollmentCode(strtoupper($this->request->data['Enrollment']['enrollment_code']), array('id', 'course_id'));
      if (isset($offering['Offering']['id'])) {
        $this->request->data['Enrollment']['offering_id'] = $offering['Offering']['id'];
        unset($this->request->data['Enrollment']['enrollment_code']);
        $this->loadModel('Course');
        $this->Course->recursive = 0;
        $course = $this->Course->findById($offering['Offering']['course_id']);
        if (isset($course['Course']['university_id'])) {
          if ($course['Course']['university_id'] != $this->currentUser['University']['id']) {
            $this->Session->setFlash(__('The selected classroom does not belong to your university'));
            return $this->redirect('/home');
          }
        }
        $exists = $this->Enrollment->find('count', array('conditions' => array('offering_id' => $offering['Offering']['id'], 'user_email' => $this->currentUser['email'])));
        if ($exists > 0) {
          $this->Session->setFlash(__('You are already enrolled in that offering'));
          return $this->redirect('/home');
        }
        $exists = $this->Enrollment->find('count', array('conditions' => array('banned' => true, 'offering_id' => $offering['Offering']['id'], 'user_email' => $this->currentUser['email'])));
        if ($exists > 0) {
          $this->Session->setFlash(__('You have been removed for that offering'));
          return $this->redirect('/home');
        }
      } else {
        $this->Session->setFlash(__('Your enrollment code is not valid'));
        return $this->redirect('/home');
      }

      $this->request->data['Enrollment']['user_email'] = $this->Auth->user('email');
      $this->request->data['Enrollment']['role'] = 0;
      $this->Enrollment->create();
      if ($this->Enrollment->save($this->request->data)) {
        Log::register("Enrolled in the offering #" . $this->request->data['Enrollment']['offering_id'], $this->Auth->user());
        $this->Session->setFlash(__('You was enrolled in that classroom with success'), 'default', array(), 'success');
      } else {
        $this->Session->write('homeEnrollmentValidationErrors', $this->Enrollment->validationErrors);
        $this->Session->setFlash(__('We could not process your enroll. Please, try again.'));
      }
      return $this->redirect('/home');
    }
  }

  public function enroll()
  {
    if ($this->request->is('post')) {
      $this->loadModel('Offering');
      $this->loadModel('User');
      $this->User->recursive = 0;
      $user = $this->User->findByEmail($this->request->data['Enrollment']['user_email'], array("User.email", "University.id"));
      if (!isset($user['User']['email'])) {
        $this->Session->setFlash(__('Invalid user'));
        $this->redirect(array("controller" => "Users", "action" => "view", $user['User']['email']));
      }
      $this->Offering->recursive = 0;
      $offering = $this->Offering->findByEnrollmentCode(strtoupper($this->request->data['Enrollment']['enrollment_code']), array('id', 'course_id'));
      if (isset($offering['Offering']['id'])) {
        $this->request->data['Enrollment']['offering_id'] = $offering['Offering']['id'];
        unset($this->request->data['Enrollment']['enrollment_code']);
        $this->loadModel('Course');
        $this->Course->recursive = 0;
        $course = $this->Course->findById($offering['Offering']['course_id']);
        if (isset($course['Course']['university_id'])) {
          if ($course['Course']['university_id'] != $user['University']['id']) {
            $this->Session->setFlash(__('The selected classroom does not belong to user\'s university'));
            $this->redirect(array("controller" => "Users", "action" => "view", $user['User']['email']));
          }
        }
        $exists = $this->Enrollment->find('count', array('conditions' => array('offering_id' => $offering['Offering']['id'], 'user_email' => $user['User']['email'])));
        if ($exists > 0) {
          $this->Session->setFlash(__('The user are already enrolled in that offering'));
          $this->redirect(array("controller" => "Users", "action" => "view", $user['User']['email']));
        }
        $exists = $this->Enrollment->find('count', array('conditions' => array('banned' => true, 'offering_id' => $offering['Offering']['id'], 'user_email' => $this->currentUser['email'])));
        if ($exists > 0) {
          $this->Session->setFlash(__('The user have been removed for that offering'));
          $this->redirect(array("controller" => "Users", "action" => "view", $user['User']['email']));
        }
      } else {
        $this->Session->setFlash(__('Your enrollment code is not valid'));
        $this->redirect(array("controller" => "Users", "action" => "view", $user['User']['email']));
      }

      $this->request->data['Enrollment']['user_email'] = $user['User']['email'];
      $this->request->data['Enrollment']['role'] = 0;
      $this->Enrollment->create();
      if ($this->Enrollment->save($this->request->data)) {
        Log::register("Admin Enrolled the user " . $user['User']['email'] . " in the offering #" . $this->request->data['Enrollment']['offering_id'], $this->Auth->user());
        $this->Session->setFlash(__('The user has been enrolled in that classroom with success'), 'default', array(), 'success');
      } else {
        $this->Session->setFlash(__('We could not process your enroll. Please, try again.'));
      }
      $this->redirect(array("controller" => "Users", "action" => "view", $user['User']['email']));
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
    if (!$this->Enrollment->exists($id)) {
      throw new NotFoundException(__('Invalid enrollment'));
    }
    if ($this->request->is('post') || $this->request->is('put')) {
      if ($this->Enrollment->save($this->request->data)) {
        $this->Session->setFlash(__('The enrollment has been saved'));
        return $this->redirect(array('action' => 'index'));
      } else {
        $this->Session->setFlash(__('The enrollment could not be saved. Please, try again.'));
      }
    } else {
      $options = array('conditions' => array('Enrollment.' . $this->Enrollment->primaryKey => $id));
      $this->request->data = $this->Enrollment->find('first', $options);
    }
    $offerings = $this->Enrollment->Offering->find('list');
    $users = $this->Enrollment->User->find('list');
    $this->set(compact('offerings', 'users'));
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
    $this->Enrollment->id = $id;
    if (!$this->Enrollment->exists()) {
      throw new NotFoundException(__('Invalid enrollment'));
    }
    $this->request->onlyAllow('post', 'delete');
    $enroll = $this->Enrollment->findById($id);
    if ($this->Enrollment->delete()) {
      Log::register("Removed the user " . $enroll['Enrollment']['user_email'] . " from the offering #" . $enroll['Enrollment']['offering_id'], $this->Auth->user());
      $this->Session->setFlash(__('The user %s was removed from this offering', $enroll['Enrollment']['user_email']), 'default', array(), 'success');
      return $this->redirect(array('controller' => 'Offerings', 'action' => 'view', $enroll['Enrollment']['offering_id']));
    }
    $this->Session->setFlash(__('Enrollment was not deleted'));
    $this->Session->setFlash(__('The user %s was not removed from this offering', $enroll['Enrollment']['user_email']));
    return $this->redirect(array('controller' => 'Offerings', 'action' => 'view', $enroll['Enrollment']['offering_id']));
  }
}
