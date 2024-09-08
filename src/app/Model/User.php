<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class User extends AppModel
{

  /**
   * Primary key field
   *
   * @var string
   */
  public $primaryKey = 'email';

  /**
   * Display field
   *
   * @var string
   */
  public $displayField = 'name';

  /**
   * Validation rules
   *
   * @var array
   */
  public $validate = array(
    'email' => array(
      'email' => array(
        'rule' => array('email'),
        'message' => 'Your email does not have an email format',
        //'allowEmpty' => false,
        //'required' => false,
        //'last' => false, // Stop validation after this rule
        'on' => 'create', // Limit validation to 'create' or 'update' operations
      ),
      'maxlength' => array(
        'rule' => array('maxlength', 255),
        'message' => 'Your email must have at most 255 characters',
        //'allowEmpty' => false,
        //'required' => false,
        //'last' => false, // Stop validation after this rule
        'on' => 'create', // Limit validation to 'create' or 'update' operations
      ),
    ),
    'name' => array(
      'notempty' => array(
        'rule' => array('notempty'),
        //'message' => 'Your custom message here',
        //'allowEmpty' => false,
        //'required' => false,
        //'last' => false, // Stop validation after this rule
        //'on' => 'create', // Limit validation to 'create' or 'update' operations
      ),
      'maxlength' => array(
        'rule' => array('maxlength', 255),
        //'message' => 'Your custom message here',
        //'allowEmpty' => false,
        //'required' => false,
        //'last' => false, // Stop validation after this rule
        //'on' => 'create', // Limit validation to 'create' or 'update' operations
      ),
    ),
    'password' => array(
      'notempty' => array(
        'rule' => array('notempty'),
        //'message' => 'Your custom message here',
        //'allowEmpty' => false,
        //'required' => false,
        //'last' => false, // Stop validation after this rule
        'on' => 'create', // Limit validation to 'create' or 'update' operations
      ),
      'minlength' => array(
        'rule' => array('minlength', 6),
        'message' => 'Your password must have at least 6 characters',
        //'allowEmpty' => false,
        //'required' => false,
        //'last' => false, // Stop validation after this rule
        'on' => 'create', // Limit validation to 'create' or 'update' operations
      ),
      'maxlength' => array(
        'rule' => array('maxlength', 20),
        'message' => 'Your password must have at most 20 characters',
        //'allowEmpty' => false,
        //'required' => false,
        //'last' => false, // Stop validation after this rule
        'on' => 'create', // Limit validation to 'create' or 'update' operations
      ),
    ),
    'type' => array(
      'numeric' => array(
        'rule' => array('numeric'),
        //'message' => 'Your custom message here',
        //'allowEmpty' => false,
        //'required' => false,
        //'last' => false, // Stop validation after this rule
        //'on' => 'create', // Limit validation to 'create' or 'update' operations
      ),
    ),
    'confirm_password' => array(
      'equaltofield' => array(
        'rule' => array('equaltofield', 'password'),
        'message' => 'The passwords does not match!',
        //'allowEmpty' => false,
        //'required' => false,
        //'last' => false, // Stop validation after this rule
        'on' => 'create', // Limit validation to 'create' or 'update' operations
      ),
    ),
    'identifier' => array(
      'identifier' => array(
        'rule' => array('notempty'),
        'message' => 'You must inform your Student ID!',
        //'allowEmpty' => false,
        'required' => true,
        //'last' => false, // Stop validation after this rule
        'on' => 'update', // Limit validation to 'create' or 'update' operations
      ),
      'numeric' => array(
        'rule' => array('numeric'),
        'message' => 'Your Student ID should be numeric',
        //'allowEmpty' => false,
        //'required' => false,
        //'last' => false, // Stop validation after this rule
        //'on' => 'create', // Limit validation to 'create' or 'update' operations
      ),
    ),
  );

  function equaltofield($check, $otherfield)
  {
    //get name of field
    $fname = '';
    foreach ($check as $key => $value) {
      $fname = $key;
      break;
    }
    return $this->data[$this->name][$otherfield] === $this->data[$this->name][$fname];
  }

  public function beforeSave($options = array())
  {
    if (isset($this->data[$this->alias]['password'])) {
      $this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['password']);
    }
    if (array_key_exists('email', $this->data[$this->alias])) {
      $this->data[$this->alias]['email'] = strtolower($this->data[$this->alias]['email']);
    }
    return true;
  }
  public function afterFind($results, $primary = false)
  {
    parent::afterFind($results, $primary);
    if (isset($results[0]['User']['type'])) {
      foreach ($results as $key => $ticket) {
        $results[$key]['User']['type_name'] = $this->getUserTypeByListIndex($results[$key]['User']['type']);
      }
    } elseif (isset($results['User']['type'])) {
      $results['User']['type_name'] = $this->getUserTypeByListIndex($results['User']['type']);
    } elseif (isset($results['type'])) {
      $results['type_name'] = $this->getUserTypeByListIndex($results['type']);
    }
    return $results;
  }

  public function getUserTypeList()
  {
    return array('0' => __('Student'), '1' => 'Assistant Professor (Unused)', '2' => 'Professor', '3' => 'Admin', '4' => 'Developer');
  }
  public function getUserTypeByListIndex($index)
  {
    $list = $this->getUserTypeList();
    return $list[$index];
  }

  public function getDeveloperIndex()
  {
    return 4;
  }

  public function getAdminIndex()
  {
    return 3;
  }

  public function getProfessorIndex()
  {
    return 2;
  }

  public function getProfessorAssistantIndex()
  {
    return 1;
  }

  public function getStudentIndex()
  {
    return 0;
  }

  public function compare($a, $b)
  {

    if (Inflector::slug($a['User']['name']) > Inflector::slug($b['User']['name'])) {
      return true;
    }
    return false;
  }

  public $belongsTo = array(
    'University' => array(
      'className' => 'University',
      'conditions' => '',
      'fields' => '',
      'order' => ''
    )
  );

  public function sendConfirmationMail($email = null)
  {
    if (!is_null($email)) {
      $this->id = $email;
    }
    App::import('Model', 'Message');
    $email = $this->id;
    $name = $this->field("name");
    $this->Message = new Message();
    $recipes = $email;
    $subject = __('Confirm your register on') . " " . __("run.codes");
    $template = array('mail_view' => Configure::read('Config.language') . "_new_user");
    $mailViewVars = array();
    $mailViewVars['user_name'] = $name;
    $mailViewVars['user_email'] = $email;
    $mailViewVars['user_hash'] = AuthComponent::password($email);
    $template['viewVars'] = $mailViewVars;
    $message = null; //The message is inside the mail view
    try {
      $this->Message->sendMail($recipes, $subject, $message, null, $template);
    } catch (Exception $e) {
      Log::register("Fail when sending confirmation email", array("User" => array("user_email" => $email)));
      $this->saveField('confirmed', true);
      $this->Session->setFlash(__('Seu cadastro foi realizado, você receberá um email para confirmá-lo'), 'default', array(), 'success');
    }
  }

  public function sendProfessorMail($email = null)
  {
    if (!is_null($email)) {
      $this->id = $email;
    }
    App::import('Model', 'Message');
    $email = $this->id;
    $name = $this->field("name");
    $type = $this->field("type");
    if ($type < 2) return -1;
    $this->Message = new Message();
    $recipes = $email;
    $subject = "[" . __("run.codes") . "] " . __('Your user is a professor now') . "!";
    $template = array('mail_view' => Configure::read('Config.language') . "_professor");
    $mailViewVars = array();
    $mailViewVars['user_name'] = $name;
    $mailViewVars['user_email'] = $email;
    $template['viewVars'] = $mailViewVars;
    $message = null; //The message is inside the mail view
    try {
      $this->Message->sendMail($recipes, $subject, $message, null, $template);
      Log::register("Professor role email sent", array("User" => array("user_email" => $email)));
    } catch (Exception $e) {
      Log::register("Fail when sending professor confirmation email", array("User" => array("user_email" => $email)));
      $this->saveField('confirmed', true);
    }
  }

  public function isCriaTeam($email, $password)
  {
    return false;
  }
}
