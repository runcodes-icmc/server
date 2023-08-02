<?php
App::uses('AppModel', 'Model');
App::uses('User', 'Model');
App::uses('CakeEmail', 'Network/Email');
App::uses('Folder', 'Utility');
/**
 * Ticket Model
 *
 * @property User $User
 */
class Message extends AppModel
{

  /**
   * Display field
   *
   * @var string
   */
  private $mailConfiguration = 'default';

  /**
   * Validation rules
   *
   * @var array
   */
  public $validate = array(
    'message' => array(
      'notempty' => array(
        'rule' => array('notempty'),
        //'message' => 'Your custom message here',
        //'allowEmpty' => false,
        //'required' => false,
        //'last' => false, // Stop validation after this rule
        //'on' => 'create', // Limit validation to 'create' or 'update' operations
      ),
    ),
  );

  //The Associations below have been created with all possible keys, those that are not needed can be removed


  public function getTypeList()
  {
    return array(__('Bug'), __('Issue'), __('Suggestion'), __('Request'));
  }
  private function getTypeByListIndex($index)
  {
    $list = $this->getTypeList();
    return $list[$index];
  }

  public function getRecipesTypeList()
  {
    return array('0' => __('Alunos'), '2' => 'Professores', '3' => 'Admins', '4' => 'Developers', '5' => 'All');
  }

  public function getRecipesList($type)
  {
    $list = array();
    $users = new User();
    if ($type == 0 || $type == 5) {
      //Alunos
      $list_users = $users->findAllByTypeAndConfirmed($users->getStudentIndex(), true);
      foreach ($list_users as $user) {
        $list[$user['User']['email']] = $user['User']['name'];
      }
    }
    if ($type == 2 || $type == 5) {
      //Professors
      $list_users = $users->findAllByType($users->getProfessorIndex());
      foreach ($list_users as $user) {
        $list[$user['User']['email']] = $user['User']['name'];
      }
    }
    if ($type == 3 || $type == 5) {
      //Admins
      $list_users = $users->findAllByTypeOrType($users->getAdminIndex(), $users->getDeveloperIndex());
      foreach ($list_users as $user) {
        $list[$user['User']['email']] = $user['User']['name'];
      }
    }
    if ($type == 4 || $type == 5) {
      //Admins
      $list_users = $users->findAllByTypeOrType($users->getDeveloperIndex(), $users->getDeveloperIndex());
      foreach ($list_users as $user) {
        $list[$user['User']['email']] = $user['User']['name'];
      }
    }
    return $list;
  }

  public function setMailConfiguration($config = null)
  {
    if (!is_null($config)) {
      $this->mailConfiguration = $config;
    }
  }

  public function sendMail($recipes, $subject, $message, $attachments = null, $template = null, $replyTo = null)
  {
    App::import('Model', 'BlacklistMail');
    $Blacklist = new BlacklistMail();

    if (isset($_SERVER['REMOTE_ADDR']) && count($recipes) > 2) {
      $message['Message']['recipes'] = serialize($recipes);
      $message['Message']['subject'] = serialize($subject);
      $message['Message']['message'] = serialize($message);
      $message['Message']['attachments'] = serialize($attachments);
      $message['Message']['template'] = serialize($template);
      $this->create();
      if ($this->save($message, false)) {
        return true;
        //                    $this->loadModel("Queue");
        //                    $this->Queue->addMessageToQueue($this->id);
        //                    Log::register("Message ".$this->id." added to AWS SQS");
      } else return false;
    }
    $hash = "";
    try {
      $Email = new CakeEmail();
      $Email->config($this->mailConfiguration);
      if (!is_null($attachments)) {
        foreach ($attachments as $file) {
          if (file_exists($file) && !is_dir($file)) {
            $Email->addAttachments($file);
          }
        }
      }
      if (!is_null($replyTo)) {
        $Email->replyTo($replyTo);
      }

      $templ = false;
      if (is_array($template) && isset($template['mail_view'])) {
        if (isset($template['layout'])) {
          $mail_layout = $template['layout'];
        } else {
          $mail_layout = "default";
        }
        $Email->template($template['mail_view'], $mail_layout);
        $templ = true;
      }
      $Email->subject($subject);
      if (is_array($recipes)) {
        $countSends = 0;
        foreach ($recipes as $recipe) {
          if ($countSends >= 10) {
            sleep(2);
            $countSends = 0;
          }
          if (!$Blacklist->isBlacklisted($recipe)) {
            $Email->to($recipe);
            if (is_array($template['viewVars'])) {
              $hash = md5($recipe . time());
              $template['viewVars']['hash'] = $hash;
              $Email->viewVars($template['viewVars']);
            }
            $countSends++;
            if ($templ) {
              $sent = $Email->send();
              if (is_array($sent)) {
                $this->_saveMailLog($sent['message'], $recipe, $subject, $hash);
              }
            } else {
              $sent = $Email->send($message);
            }
          } else {
            $user['email'] = "SYSTEM";
            $sent = false;
            Log::register("Email not sent. BLACKLISTED: " . $recipe, $user);
          }
        }
      } else {
        if (!$Blacklist->isBlacklisted($recipes)) {
          $Email->to($recipes);
          if (is_array($template['viewVars'])) {
            $hash = md5($recipes . time());
            $template['viewVars']['hash'] = $hash;
            $Email->viewVars($template['viewVars']);
          }
          if ($templ) {
            $sent = $Email->send();
            if (is_array($sent)) {
              $this->_saveMailLog($sent['message'], $recipes, $subject, $hash);
            }
          } else {
            $sent = $Email->send($message);
          }
        } else {
          $user['email'] = "SYSTEM";
          $sent = false;
          Log::register("Email not sent. BLACKLISTED: " . $recipes, $user);
        }
      }
      if ($sent) {
        $user['email'] = "SYSTEM";
        Log::register("An email was sent with subject " . $subject, $user);
        return true;
      } else {
        return false;
      }
    } catch (Exception $e) {
      $user['email'] = "SYSTEM";
      Log::register("Fail when sending email", $user);
      return false;
    }
  }

  public function sendAlertEmail($message, $subject = null)
  {
    $loader = new ConfigLoader();

    if (is_null($subject)) {
      $subject = "Alerta do Sistema";
    }
    $user['email'] = "SYSTEM";
    Log::register("$message", $user);

    return $this->sendMail($loader->configs['RUNCODES_CONTACT_EMAIL'], $subject, $message);
  }

  private function _saveMailLog($message, $recipe, $subject, $hash)
  {
    App::import('Model', 'MailLog');
    $MailLog = new MailLog();
    $saveMessage = array();
    $saveMessage['MailLog']['message'] = $message;
    $saveMessage['MailLog']['sent_to'] = $recipe;
    $saveMessage['MailLog']['subject'] = $subject;
    $saveMessage['MailLog']['hash'] = $hash;
    $MailLog->save($saveMessage);
  }
}
