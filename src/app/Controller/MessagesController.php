<?php
App::uses('AppController', 'Controller');
App::uses('Folder', 'Utility');
/**
 * Offerings Controller
 *
 * @property Offering $Offering
 * @property PaginatorComponent $Paginator
 */
class MessagesController extends AppController
{

  public $components = array('Paginator');
  public $paginate = array(
    'limit' => 50,
    'order' => array(
      'id' => 'DESC'
    )
  );
  private $studentAuthorized = array('');

  public function beforeFilter()
  {
    parent::beforeFilter();
    $this->Auth->allow('open');
  }

  public function isAuthorized($user = null)
  {
    $this->loadModel('User');
    if ($user['type'] >= $this->User->getAdminIndex()) {
      return true;
    }
    return false;
  }

  public function index()
  {
    $recipesList = $this->Message->getRecipesTypeList();
    $this->set(compact('recipesList'));
  }

  public function blacklist($remove = null)
  {
    $this->loadModel("BlacklistMail");
    if ($this->request->is('post')) {
      if (is_null($remove)) {
        $this->BlacklistMail->create();
        $this->BlacklistMail->save($this->request->data);
        $this->redirect(array("action" => 'blacklist'));
      } else {
        if ($this->BlacklistMail->exists($remove)) {
          $this->BlacklistMail->id = $remove;
          $this->BlacklistMail->delete();
          $this->redirect(array("action" => 'blacklist'));
        }
      }
    }
    $this->layout = "template2015";
    $blacklist = $this->BlacklistMail->find('all');
    $addressType = $this->BlacklistMail->getTypes();
    $this->set(compact('blacklist', 'addressType'));
  }

  public function sendMail()
  {
    if ($this->request->is('post')) {
      //Recipes
      $recipes = $this->Message->getRecipesList($this->request->data['Message']['to']);
      //Attachments
      $files = $this->request->data['Files'];
      $attachments = array();
      if (count($files) > 0) {
        foreach ($files as $file) {
          $dir = Configure::read('Upload.dir') . "/attachments/tmp/" . $file['hash'] . "/" . $file['path'];
          if (file_exists($dir) && !is_dir($dir)) {
            array_push($attachments, $dir);
          }
        }
      }
      //Subject and Message
      $subject = "[run.codes] " . $this->request->data['Message']['subject'];
      $template = array('mail_view' => Configure::read('Config.language') . "_global_mail");
      $mailViewVars = array();
      $mailViewVars['message'] = nl2br($this->request->data['Message']['message']);
      $template['viewVars'] = $mailViewVars;
      if ($this->Message->sendMail($recipes, $subject, null, $attachments, $template)) {
        $this->Session->setFlash(__('The message has been sent'), 'default', array(), 'success');
      } else {
        $this->Session->setFlash(__('The message has not been sent'), 'default', array(), 'flash');
      }
      $this->redirect(array('action' => 'index'));
    }
  }

  public function mailFileUpload()
  {
    $this->layout = 'ajax';
    $this->autoRender = false;
    $this->response->type('json');
    $hash_time = sha1(time());
    $upload_dir = new Folder(Configure::read('Upload.dir') . '/attachments/tmp/' . $hash_time . '/', true, 0777);
    $upload_handler = new UploadHandler(array(
      //                'accept_file_types' => '/\.(gif|jpe?g|png|pdf)$/i',
      'upload_dir' => Configure::read('Upload.dir') . '/attachments/tmp/' . $hash_time . '/',
      'hash_time' => $hash_time,
      'hash_name' => false
    ), false, $this->error_messages);
    //            pr($upload_handler);
    $result = $upload_handler->post(false);
    echo json_encode($result);
    return false;
  }

  public function maillog($remove = null)
  {
    $this->loadModel("MailLog");
    $cond = array();
    if ($this->request->is('post')) {
      if (is_null($remove)) {
        $this->redirect(array('controller' => 'Messages', 'action' => 'maillog', 'sent_to' => $this->request->data['MailLog']['sent_to'], 'opened' => $this->request->data['MailLog']['opened']));
      } else {
        if ($this->MailLog->exists($remove)) {
          $this->MailLog->id = $remove;
          $this->MailLog->delete();
          $this->redirect(array("action" => 'maillog'));
        }
      }
    }
    if (isset($this->request->params['named']['sent_to']) && strlen($this->request->params['named']['sent_to']) > 0) {
      $cond['MailLog.sent_to ILIKE'] = '%' . $this->request->params['named']['sent_to'] . '%';
    }
    if (isset($this->request->params['named']['opened']) && strlen($this->request->params['named']['opened']) > 0) {
      $cond['MailLog.opened'] = $this->request->params['named']['opened'];
    }
    $this->layout = "template2015";
    $this->paginate['conditions'] = $cond;
    $this->Paginator->settings = $this->paginate;
    $this->set("logs", $this->Paginator->paginate('MailLog'));
  }

  public function clearOldMessages()
  {
    $this->loadModel("MailLog");
    if (!$this->request->is('post')) {
      $this->redirect(array("action" => "maillog"));
    }
    $result = $this->MailLog->updateAll(array("MailLog.message" => null), array("MailLog.message IS NOT NULL", "MailLog.sent_date < (NOW() - INTERVAL '3 days') "));
    if ($result) {
      $this->Session->setFlash(__('All messages older than 3 days have been cleared'), 'default', array(), 'success');
    } else {
      $this->Session->setFlash(__('No messages found to be cleared'));
    }
    $this->redirect(array("action" => "maillog"));
  }

  public function view($mail_log_id)
  {
    $this->layout = "template2015";
    $this->loadModel("MailLog");
    if (!$this->MailLog->exists($mail_log_id)) {
      throw new NotFoundException(__("Invalid mail id"));
    }

    $breadcrumbs = array();
    array_push($breadcrumbs, array('link' => '/Messages/maillog', 'text' => __("Mail Log")));
    array_push($breadcrumbs, array('link' => '#', 'text' => __("View Message")));
    $this->set(compact("breadcrumbs"));
    $this->set("mail", $this->MailLog->findById($mail_log_id));
  }

  public function open($hash, $garbage = null)
  {
    $this->loadModel("MailLog");
    $this->MailLog = new MailLog();
    $log = $this->MailLog->findByHash($hash, array('id', 'opened'));
    if (count($log) > 0) {
      $this->MailLog->id = $log['MailLog']['id'];
      $this->MailLog->saveField('opened', intval($log['MailLog']['opened']) + 1);
      if (intval($log['MailLog']['opened']) == 0) {
        $this->MailLog->saveField('first_opened_time', 'NOW()');
      }
    }

    $this->autoRender = false;
    $this->response->header(array(
      'Cache-Control'  => 'private',
    ));
    $this->response->body(hex2bin('47494638396101000100900000ff000000000021f90405100000002c00000000010001000002020401003b'));
    $this->response->type('gif');
    return $this->response;
    exit;
  }
}
