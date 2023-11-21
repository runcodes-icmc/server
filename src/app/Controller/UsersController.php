<?php
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class UsersController extends AppController
{

  public $components = array('Paginator', 'Linkedin');
  private $studentAuthorized = array('logout', 'confirm', 'changepassword', 'profile');

  public function beforeFilter()
  {
    parent::beforeFilter();
    $this->Auth->allow('prospect', 'login', 'login2', 'add', 'logout', 'confirm', 'googleCallback', 'recoveryPassword', 'sendConfirmationEmail');
  }

  public function isAuthorized($user = null)
  {
    $this->loadModel('User');
    if ($user['type'] >= $this->User->getAdminIndex()) return true;

    if (strtolower($this->request->params['action']) == "viewas") {
      return true;
    }
    if ($user['type'] <= $this->User->getAdminIndex()) {
      if (in_array(strtolower($this->request->params['action']), $this->studentAuthorized)) return true;
      else return false;
    }
  }


  public function googleCallback()
  {
    $this->autoRender = false;
  }

  public function login()
  {
    $this->layout = "login2";
    if ($this->Auth->loggedIn()) {
      $this->redirect($this->Auth->redirectUrl());
    }

    if (isset($this->request->data["User"]["email"])) {
      $this->User->id = $this->request->data["User"]["email"];
      if (!$this->User->field("confirmed")) {
        $this->set("confirmLink", true);
        return $this->Session->setFlash(__('You have not confirmed your registration by email'));
      }
    }

    if ($this->request->is('post') || $this->request->is('put')) {
      $this->Session->destroy();
      if ($this->Auth->login()) {
        Log::register("Logged", $this->Auth->user());
        return $this->redirect($this->Auth->redirectUrl());
      } else {
        $this->Session->setFlash(__('Incorrect email and/or password or You have not confirmed your registration by email'));
      }
    }
  }

  public function sendConfirmationEmail($email = null)
  {
    if (!is_null($email)) {
      if ($this->request->is('post') || $this->request->is('put')) {
        $user = $this->User->findByEmail($email, array('email', 'confirmed'));
        if (count($user) > 0) {
          if ($user['User']['confirmed']) {
            $this->Session->setFlash(__('Seu cadastro já foi confirmado'));
          } else {
            $this->User->sendConfirmationMail($email);
            $this->Session->setFlash(__('Enviamos um novo email para confirmação do seu cadastro'), 'default', array(), 'success');
            //                        Log::slackNotification('Email de confirmação',"O usuário ".$email." requereu o email de confirmação de cadastro novamente",array(),"warning");
          }
        } else {
          $this->Session->setFlash(__('Usuário não encontrado'));
        }
      }
    }
    $this->redirect('/');
  }

  public function sendProfessorEmail($email = null)
  {
    if (!$this->User->exists($email)) {
      $this->Session->setFlash(__('Usuário não encontrado'));
      $this->redirect(array("action" => "index"));
    }
    $ret = $this->User->sendProfessorMail($email);
    if ($ret == -1) {
      $this->Session->setFlash(__('This user does not have the professor role'));
    } else if (!ret) {
      $this->Session->setFlash(__('E-mail not sent'));
    } else {
      $this->Session->setFlash(__('The user has received an email confirm the role change'), 'default', array(), 'success');
    }
    $this->redirect(array("action" => "view", $email));
  }


  public function logout()
  {
    Log::register("Logout", $this->Auth->user());
    $this->redirect($this->Auth->logout());
  }
  public function confirm()
  {
    $this->layout = "login";
    $path = func_get_args();
    pr($path);
    if (count($path) < 2) {
      $this->Session->setFlash(__('Invalid URL'));
      return $this->redirect(array('action' => 'login'));
    }
    $user = $this->User->find('first', array(
      'conditions' => array('email' => $path[0])
    ));
    pr($user);
    if (count($user) == 0) {
      $this->Session->setFlash(__('Este usuário não está cadastrado no sistema'));
      return $this->redirect(array('action' => 'login'));
    }
    if ($user['User']['confirmed'] == true) {
      $this->Session->setFlash(__('Seu cadastro já foi confirmado, você pode entrar no sistema'), 'default', array(), 'success');
      return $this->redirect(array('action' => 'login'));
    }
    if (AuthComponent::password($user['User']['email']) == $path[1]) {
      if (!$this->User->exists($path[0])) {
        $this->Session->setFlash(__('Invalid user'));
        return $this->redirect(array('action' => 'login'));
      } else {
        $user['User']['confirmed'] = true;
        if ($this->User->save($user, false, array('confirmed'))) {
          Log::register("Confirm register", $user['User']);
          $this->Session->setFlash(__('Seu cadastro foi confirmado, você já pode entrar no sistema'), 'default', array(), 'success');
          return $this->redirect(array('action' => 'login'));
        } else {
          $this->Session->setFlash(__('Ocorreu um erro e o cadastro não foi confirmado'));
          return $this->redirect(array('action' => 'login'));
        }
      }
    } else {
      $this->Session->setFlash(__('Invalid check code'));
      return $this->redirect(array('action' => 'login'));
    }
    die();
  }

  /**
   * index method
   *
   * @return void
   */
  public function index($startswith = null)
  {
    $this->layout = "template2015";
    $this->loadModel('University');
    $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
    $this->set('letters', $letters);
    $this->paginate = array(
      'limit' => 20,
      'order' => 'User.name'
    );
    if ($this->request->is('post')) {
      if (isset($this->request->data['User']['type']) && $this->request->data['User']['type'] == -1) {
        $this->request->data['User']['type'] = null;
      }
      if (isset($this->request->data['User']['university_id']) && $this->request->data['User']['university_id'] == -1) {
        $this->request->data['User']['university_id'] = null;
      }
      $this->redirect(array(
        'controller' => 'Users',
        'action' => 'index',
        'university' => $this->request->data['User']['university_id'],
        'type' => $this->request->data['User']['type'],
        'email' => trim($this->request->data['User']['email']),
        'name' => trim($this->request->data['User']['name']),
        'confirmed' => $this->request->data['User']['confirmed']
      ));
    }
    $cond = array();
    if (isset($this->request->params['named']['university'])) {
      if ($this->request->params['named']['university'] == 'null') {
        $cond['User.university_id IS'] = null;
      } else {
        $cond['User.university_id'] = $this->request->params['named']['university'];
      }
    }
    if (isset($this->request->params['named']['startswith'])) {
      $cond['User.name ILIKE'] = $this->request->params['named']['startswith'] . '%';
      $startswith = strtoupper($this->request->params['named']['startswith']);
    }
    if (isset($this->request->params['named']['name']) && strlen($this->request->params['named']['name']) > 0) {
      $cond['User.name ILIKE'] = '%' . $this->request->params['named']['name'] . '%';
    }
    if (isset($this->request->params['named']['email']) && strlen($this->request->params['named']['email']) > 0) {
      $cond['User.email ILIKE'] = '%' . $this->request->params['named']['email'] . '%';
    }
    if (isset($this->request->params['named']['type'])) {
      $cond['User.type'] = $this->request->params['named']['type'];
    }

    if (isset($this->request->params['named']['confirmed']) && in_array($this->request->params['named']['confirmed'], array("0", "1"))) {
      $cond['User.confirmed'] = boolval($this->request->params['named']['confirmed']);
    } else if (!isset($this->request->params['named']['confirmed'])) {
      $cond['User.confirmed'] = true;
    }
    $this->set('universities', array('-1' => __("All"), 'null' => __("Empty")) + $this->University->find('list', array('order' => array("name" => "ASC"))));
    $this->set('types', array(-1 => __("All")) + $this->User->getUserTypeList());
    $this->set('users', $this->Paginator->paginate($cond));
    $this->set('startswith', $startswith);
  }

  public function confirmRegister($id = null)
  {
    if (!$this->User->exists($id)) {
      throw new NotFoundException(__('Invalid user'));
    }
    $this->User->id = $id;
    $this->User->saveField("confirmed", true);
    $this->Session->setFlash(__('The user register has been confirmed'), 'default', array(), 'success');
    $this->redirect(array('action' => 'view', $id));
  }

  public function resendConfirmation($id = null)
  {
    if (!$this->User->exists($id)) {
      throw new NotFoundException(__('Invalid user'));
    }
    $this->User->id = $id;
    $this->User->sendConfirmationMail();
    $this->Session->setFlash(__('The confirmation email was send again'), 'default', array(), 'success');
    $this->redirect(array('action' => 'view', $id));
  }

  public function view($id = null)
  {
    $this->layout = "template2015";
    if (!$this->User->exists($id)) {
      throw new NotFoundException(__('Invalid user'));
    }
    $options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
    $user = $this->User->find('first', $options);

    $this->loadModel("Offering");
    $this->loadModel("Enrollment");
    $this->loadModel("Course");
    $this->Offering->recursive = 0;
    $this->Enrollment->recursive = 0;
    $enrollments = $this->Enrollment->find('all', array('fields' => array('offering_id'), 'conditions' => array('Enrollment.user_email' => $user['User']['email'], 'Offering.end_date > NOW()')));
    foreach ($enrollments as $k => $e) {
      $offering2 = $this->Offering->findById($e['Enrollment']['offering_id'], array('course_id', 'classroom', 'id'));
      $course = $this->Course->findById($offering2['Offering']['course_id'], array('code', 'title'));
      $enrollments[$k]['Offering'] = $offering2['Offering'];
      $enrollments[$k]['Offering']['Course'] = $course['Course'];
    }

    $this->loadModel("Commit");
    $this->loadModel("Exercise");
    $this->Commit->recursive = -1;
    $this->Exercise->recursive = -1;
    $commits = $this->Commit->findAllByUserEmail($id, array('id', 'commit_time', 'exercise_id', 'status', 'score', 'corrects'), array("commit_time" => "DESC"), 15);
    foreach ($commits as $k => $c) {
      $exercise = $this->Exercise->findById($c['Commit']['exercise_id'], array('id', 'title'));
      $commits[$k]['Exercise'] = $exercise['Exercise'];
    }
    $breadcrumbs = array();
    array_push($breadcrumbs, array('link' => array('controller' => 'Users', 'action' => 'index'), 'text' => __("Users")));
    array_push($breadcrumbs, array('link' => '#', 'text' => $user['User']['email']));

    $this->set('user_types', $this->User->getUserTypeList());
    $this->set(compact("user", "enrollments", 'commits', 'breadcrumbs'));
  }

  /**
   * add method
   *
   * @return void
   */
  public function add($invite = null)
  {
    $this->layout = "login2";
    if ($this->request->is('post') || $this->request->is('put')) {
      $invite = null;
      if ($this->User->exists($this->request->data['User']['email'])) {
        $this->Session->setFlash(__('We already have an user with this email'));
      } else {
        $this->User->create();
        $this->request->data['User']['type'] = 0;
        $this->request->data['User']['source'] = 0;
        if ($this->User->save($this->request->data)) {
          $this->User->sendConfirmationMail();
          $this->Session->setFlash(__('Seu cadastro foi realizado, você receberá um email para confirmá-lo'), 'default', array(), 'success');
          Log::register("Registered", $this->request->data['User']);
          return $this->redirect(array('action' => 'login'));
        } else {
          $this->Session->setFlash(__('The user could not be saved. Please, try again.'));
        }
      }
    }
    $this->set(compact('invite'));
  }

  public function insert()
  {
    $this->layout = "template2015";
    if ($this->request->is('post') || $this->request->is('put')) {
      if ($this->User->exists($this->request->data['User']['email'])) {
        $this->Session->setFlash(__('We already have an user with this email'));
      } else {
        $this->User->create();
        $this->request->data['User']['confirmed'] = true;
        $this->request->data['User']['source'] = 0;
        if ($this->User->save($this->request->data)) {
          $this->Session->setFlash(__('O usuário foi cadastrado com sucesso!'), 'default', array(), 'success');
          Log::register("Admin Added User ", $this->request->data['User']);
          $this->redirect(array('action' => 'index'));
        } else {
          $this->Session->setFlash(__('The user could not be saved. Please, try again.'));
          $this->redirect(array('action' => 'index'));
        }
      }
    }
    $this->set('types', $this->User->getUserTypeList());
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
    if (!$this->User->exists($id)) {
      throw new NotFoundException(__('Invalid user'));
    }
    $this->request->data['User']['id'] = $id;
    if ($this->request->is('post') || $this->request->is('put')) {
      if ($this->User->save($this->request->data, true, array('name', 'type', 'confirmed'))) {
        Log::register("Edited register User #" . $id, $this->Auth->user());
        $this->Session->setFlash(__('The user has been saved'), 'default', array(), 'success');
      } else {
        $this->Session->setFlash(__('The user could not be saved. Please, try again.'));
      }
    }
    return $this->redirect(array('action' => 'view', $id));
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
    $this->User->id = $id;
    if (!$this->User->exists()) {
      throw new NotFoundException(__('Invalid user'));
    }

    $this->request->onlyAllow('post', 'delete');
    if ($this->User->delete()) {
      Log::register("Deleted register User #" . $id, $this->Auth->user());
      $this->Session->setFlash(__('User deleted'));
      return $this->redirect(array('action' => 'index'));
    }
    $this->Session->setFlash(__('User was not deleted'));
    return $this->redirect(array('action' => 'index'));
  }


  public function recoveryPassword()
  {
    if ($this->request->is('post') || $this->request->is('put')) {
      $email = $this->request->data['User']['email'];
      $this->User->recursive = -1;
      $user = $this->User->findByEmail($email);
      if (count($user) > 0) {
        Log::register("Recovery Password", $user['User']);
        if ($user['User']['source'] == 1) {
          $this->Session->setFlash(__('This account is registered with LinkedIn'));
        } elseif ($user['User']['confirmed']) {
          $hash = sha1(time() . Configure::read('Security.Salt'));
          $newPass = substr($hash, 0, 10);
          $user['User']['password'] = $newPass;
          $this->User->id = $email;
          if ($this->User->saveField('password', $newPass)) {
            $this->Session->setFlash(__('Your password has been changed, we will send you the new password soon'), 'default', array(), 'success');
            $this->loadModel('Message');
            $recipes = $email;
            $subject = "[" . __('run.codes') . "] " . __('Your new account password') . "!";
            $template = array('mail_view' => Configure::read('Config.language') . "_recovery_password");
            $mailViewVars = array();
            $mailViewVars['user_name'] = $user['User']['name'];
            $mailViewVars['password'] = $newPass;
            $template['viewVars'] = $mailViewVars;
            $message = null; //The message is inside the mail view
            try {
              $this->Message->sendMail($recipes, $subject, $message, null, $template);
            } catch (Exception $e) {
              Log::register("Fail when sending recovery password email", $this->request->data['User']);
              $this->saveField('confirmed', true);
              $this->Session->setFlash(__('The system can not send your password, please try later or contact the run.codes support'));
            }
          } else {
            $this->Session->setFlash(__('The system can not change your password, please try later or contact the run.codes admin'));
          }
        } else {
          $this->Session->setFlash(__('Your account is not confirmed, you have received an email with the link to confirm the account'));
        }
      } else {
        $this->Session->setFlash(__('This email is not registered in the system'));
      }
    }
    return $this->redirect(array('action' => 'login'));
  }

  public function profile()
  {
    $this->layout = "template2015";
    $u = $this->Auth->user();
    if ($this->request->is('post') || $this->request->is('put')) {

      Log::register("Change profile information", $this->Auth->user());
      if (isset($this->request->data['User']['old_password'])) {
        //Atualizar senha
        $user = $this->User->findByEmailAndPassword($this->Auth->user('email'), AuthComponent::password($this->request->data['User']['old_password']));
        if (count($user) > 0) {
          $this->User->set($this->request->data);
          if (!$this->User->validates(array('fieldList' => array('password', 'confirm_password')))) {
            $this->Session->setFlash(__('The new password and the confirmation do not match'));
          } else {
            $this->User->id = $this->Auth->user('email');
            if ($this->User->saveField('password', $this->request->data['User']['password'])) {
              $this->Session->setFlash(__('Your password has been changed'), 'default', array(), 'success');
            } else {
              $this->Session->setFlash(__('The system can not change your password, please try later or contact runcodes@icmc.usp.br'));
            }
          }
        } else {
          $this->Session->setFlash(__('The current password is incorrect'));
        }
      } else {
        //Atualizar Dados Pessoais
        $this->User->set($this->request->data);
        $this->User->id = $u['email'];
        if (!$this->User->save(array('fieldList' => array('name', 'university_id', 'identifier')))) {
          $this->Session->setFlash(__('Your profile has not been updated'));
        } else {
          $this->Session->setFlash(__('Your profile has been updated'), 'default', array(), 'success');
          return $this->redirect('/home');
        }
      }
    }
    unset($this->request->data);
    $user = $this->User->findByEmail($u['email']);
    $this->loadModel('University');
    $this->set('universities', $this->University->find('list', array('conditions' => array('type <=' => 3), 'order' => array("name" => "ASC"))));
    $this->set('user', $user);
  }

  public function linkedin()
  {
    $this->Linkedin->redirectToLoginUrl();
  }

  public function linkedinCallback()
  {
    $this->User->recursive = 0;
    if (isset($_GET['error'])) {
      $this->Session->setFlash(__('Incorrect email and/or password or You have not confirmed your registration by email'));
    } else {
      $code = $_GET['code'];
      $state = $_GET['state'];
      $this->Linkedin->getAccessToken($code);
      $user = $this->Linkedin->fetch('GET', '/v2/emailAddress?q=members&projection=(elements*(handle~))');
      $registeredUser = $this->User->findByEmail($user->elements[0]->{"handle~"}->emailAddress);
      if (count($registeredUser) > 0) {
        $registeredUser['User']['University'] = $registeredUser['University'];
        unset($registeredUser['University']);
        if ($registeredUser['User']['source'] == 1) {
          if ($registeredUser['User']['confirmed'] && $this->Auth->login($registeredUser['User'])) {
            Log::register("Logged with Linkedin", $this->Auth->user());
            return $this->redirect($this->Auth->redirectUrl());
          } else {
            $this->Session->setFlash(__('Maybe you have not confirmed your registration by email'));
          }
        } else {
          $this->Session->setFlash(__('This email is used in an account without Linkedin connection'));
        }
      } else {
        $userProfile = $this->Linkedin->fetch('GET', '/v2/me?projection=(localizedFirstName,localizedLastName)');
        $newUser = array();
        $newUser['User']['name'] = $userProfile->localizedFirstName . " " . $userProfile->localizedLastName;
        $newUser['User']['email'] = $user->elements[0]->{"handle~"}->emailAddress;
        $newUser['User']['type'] = 0;
        $newUser['User']['source'] = 1;
        $newUser['User']['password'] = AuthComponent::password(uniqid(md5(mt_rand()))); //Set a random password
        //                    debug($newUser);
        if ($this->User->save($newUser, false)) {
          $this->Session->setFlash(__('Seu cadastro foi realizado, você receberá um email para confirmá-lo'), 'default', array(), 'success');
          Log::register("Registered", $newUser['User']);
          $this->loadModel('Message');
          $recipes = $newUser['User']['email'];
          $subject = __('Confirm your register on') . " " . __("run.codes");
          $template = array('mail_view' => Configure::read('Config.language') . "_new_user");
          $mailViewVars = array();
          $mailViewVars['user_name'] = $newUser['User']['name'];
          $mailViewVars['user_email'] = $newUser['User']['email'];
          $mailViewVars['user_hash'] = AuthComponent::password($newUser['User']['email']);
          $template['viewVars'] = $mailViewVars;
          $message = null; //The message is inside the mail view
          $this->Message->sendMail($recipes, $subject, $message, null, $template);
          return $this->redirect(array('action' => 'login'));
        } else {
          Log::register("Error on trying to register with Linkedin Account", $newUser['User']);
          $this->Session->setFlash(__('Your register could not be saved. Please, try again.'));
        }
      }
    }
    return $this->redirect('/');
  }

  public function viewAs($type = 0)
  {
    if ($type >= 0 && $type <= 4) {
      if ($this->currentUser['type'] == 0 && $type == 0) {
        $type = -1;
      }
      $this->Session->write('simulateUserType', $type);
    }
    if ($type == $this->currentUser['type']) {
      $this->Session->delete('simulateUserType');
    }
    Log::register("Changed view to role " . $type, $this->currentUser);
    return $this->redirect('/home');
  }

  public function prospect($token, $name, $email, $university = null)
  {
    $this->autoRender = false;
    if ($token == md5("vnoirghsiopdngapvfgn" . date('Y-m-d') . "vibwv[pafg34309tn")) {
      $this->loadModel("Message");
      $recipes = $email;
      $subject = __('Conheça o') . " " . __("run.codes");
      $template = array('mail_view' => "por_prospect_user");
      $mailViewVars = array();
      $mailViewVars['user_name'] = $name;
      $mailViewVars['user_email'] = $email;
      $template['viewVars'] = $mailViewVars;
      $message = null;
      $response = $this->Message->sendMail($recipes, $subject, $message, null, $template, "runcodes@icmc.usp.br");
      echo json_encode($response);
    } else {
      throw new NotFoundException();
    }
    die();
  }
}
