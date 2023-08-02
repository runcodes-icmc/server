<?php
App::uses('Controller', 'Controller');

class AppController extends Controller
{

  public $components = array(
    'Session',
    'Auth' => array(
      'loginRedirect' => '/home',
      'logoutRedirect' => '/',
      'authorize' => array('Controller'),
      'authenticate' => array(
        'Form' => array(
          'fields' => array('username' => 'email'),
          'scope' => array('User.confirmed' => true)
        )
      ),
    )
  );

  public $currentUser = null;
  public $commitsMessages = null;

  private function getDateTime()
  {
    return date('d/m/Y H:i:s');
  }

  private function getDateTimeJson()
  {
    $now = getdate();
    unset($now['yday']);
    unset($now['weekday']);
    unset($now['month']);
    unset($now['wday']);
    unset($now[0]);
    foreach ($now as $k => $i) {
      if (strlen(strval($i)) == 1) {
        $now[$k] = "0" . $i;
      } else {
        $now[$k] = strval($i);
      }
    }
    return json_encode($now);
  }


  public function beforeFilter()
  {
    Security::setHash('sha1');
    Configure::write('Config.language', 'por');
    $this->loadModel('User');
    $result = $this->User->query("SELECT NOW();");
    $this->now = strtotime($result[0][0]['now']);

    $enrollment_role = array('0' => __('Students'), '1' => __('Assistants'), '2' => __('Professor'));

    $this->set('controller', $this->request->params['controller']);
    $this->set('page', 'home');
    $logged = $this->Auth->user();
    if (!is_null($logged) && isset($logged['email'])) {
      $this->User->recursive = 0;
      $user = $this->User->findByEmail($logged['email']);
      $this->currentUser = $user['User'];
      $splitEmail = explode("@", $logged['email']);
      $this->currentUser['domain'] = $splitEmail[1];
      $this->currentUser['University'] = $user['University'];
      $this->currentUser['onlyStudent'] = false;
      if ($this->Session->check('simulateUserType')) {
        $this->currentUser['real_type'] = $this->currentUser['type'];
        $this->currentUser['type'] = $this->Session->read('simulateUserType');
        if ($this->currentUser['type'] == -1) {
          $this->currentUser['type'] = 0;
          $this->currentUser['onlyStudent'] = true;
        }
      } else {
        $this->currentUser['real_type'] = $this->currentUser['type'];
      }
      if ($this->currentUser['type'] == 0 && $this->currentUser['real_type'] > 0) {
        $this->currentUser['onlyStudent'] = true;
      }
    }
    if ($this->currentUser['real_type'] < $this->currentUser['type']) {
      $this->currentUser['type'] = $this->currentUser['real_type'];
    }
    if ($this->currentUser['domain'] == "demo.run.codes") {
      $this->Session->setFlash(__("Welcome to run.codes! Using a demo account you can see how run.codes works. However, some action has been disabled for security reasons. If you want complete access, please create an account"), 'default', array(), 'success');
    }

    if (Configure::check('Config.maintenanceMode')) {
      if (Configure::read('Config.maintenanceMode')) {
        if ($this->currentUser['real_type'] < 3) {
          $this->Auth->logout();
        }
        if (!isset($this->currentUser['real_type']) || $this->currentUser['real_type'] < 3) {
          if (strtolower($this->request->params['controller']) != "users" && strtolower($this->request->params['action']) != "login") {
            $this->Session->setFlash(__("Sorry! The run.codes is down for maintenance tasks"), null, array(), 'auth');
            $this->redirect('/');
          }
        }
      }
    }
    if ($this->currentUser['type'] > 3) {
      // Configure::write('debug', 2);
      $this->set('database_name', $this->User->getDataSource()->config['database']);
    }
    $this->set('logged_user', $this->currentUser);
    //        $this->set('user_types',$user_types);
    $this->set('enrollment_role', $enrollment_role);
    $this->set('title_for_layout', 'run.codes');
    //        $this->set('alert_types',$alert_types);
  }

  public function beforeRender()
  {
    $this->loadModel('Enrollment');
    $this->loadModel('Offering');
    $this->loadModel('Course');
    $this->Offering->recursive = -1;
    $this->Course->recursive = -1;
    if (!$this->currentUser['onlyStudent']) {
      $userOfferings = $this->Enrollment->getOfferingsByUser($this->currentUser['email'], true);
      $userOfferingsMenu = array();
      foreach ($userOfferings as $k => $o) {
        $off = $this->Offering->findById($o, array('classroom', 'course_id'));
        $cou = $this->Course->findById($off['Offering']['course_id'], array('name'));
        $userOfferingsMenu[$o] = $cou['Course']['name'] . " (" . $off['Offering']['classroom'] . ")";
      }
    }

    $this->setCommitsMessages();
    $this->set('commitsMessages', $this->commitsMessages);
    $datetime = $this->getDateTime();
    $datetimeJson = $this->getDateTimeJson();
    $this->set(compact('userOfferingsMenu', 'datetime', 'datetimeJson'));
  }

  public function isAuthorized($user = null)
  {
    return true;
  }

  protected function _removeAccents($string)
  {
    $tr = strtr(
      trim($string),
      array(

        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
        'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ð' => 'D', 'Ñ' => 'N',
        'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O',
        'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Ŕ' => 'R',
        'Þ' => 's', 'ß' => 'B', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a',
        'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e',
        'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
        'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y',
        'þ' => 'b', 'ÿ' => 'y', 'ŕ' => 'r'
      )
    );
    return $tr;
  }

  protected function setCommitsMessages()
  {
    $messages = array();
    $messages['UNKONWN'] = __('An unlisted event has been received');
    $messages['SIGHUP'] = __('This signal usually means that the controlling pseudo or virtual terminal has been closed');
    $messages['SIGINT'] = __('This signal means a user wishes to interrupt the process. This is typically initiated by pressing Control-C');
    $messages['SIGQUIT'] = __('This signal means the user requests that the process quit and perform a core dump');
    $messages['SIGILL'] = __('This signal means your process has attempted to execute an illegal, malformed, unknown, or privileged instruction');
    $messages['SIGTRAP'] = __('This signal is sent to a process when an exception (or trap) occurs: a condition that a debugger has requested to be informed of — for example, when a particular function is executed, or when a particular variable changes value');
    $messages['SIGABRT'] = __('This signal is sent to a process to tell it to abort, i.e. to terminate. The signal is usually initiated by the process itself when it calls abort function of the C Standard Library, but it can be sent to the process from outside as well as any other signal');
    $messages['SIGBUS'] = __('This signal is sent to a process when it causes a bus error. The conditions that lead to the signal being raised are, for example, incorrect memory access alignment or non-existent physical address');
    $messages['SIGFPE'] = __('This signal is sent to a process when it executes an erroneous arithmetic operation, such as division by zero (the name FPE, standing for floating-point exception, is a misnomer as the signal covers integer-arithmetic errors as well)');
    $messages['SIGKILL'] = __('This signal is sent to a process to cause it to terminate immediately (kill). In contrast to SIGTERM and SIGINT, this signal cannot be caught or ignored, and the receiving process cannot perform any clean-up upon receiving this signal');
    $messages['SIGUSR1'] = __('This signal is sent to a process to indicate user-defined conditions');
    $messages['SIGSEGV'] = __('This signal is sent to a process when it makes an invalid virtual memory reference, or segmentation fault, i.e. when it performs a segmentation violation');
    $messages['SIGUSR2'] = __('This signal is sent to a process to indicate user-defined conditions');
    $messages['SIGPIPE'] = __('This signal is sent to a process when it attempts to write to a pipe without a process connected to the other end');
    $messages['SIGALRM'] = __('The SIGALRM, SIGVTALRM and SIGPROF signal is sent to a process when the time limit specified in a call to a preceding alarm setting function (such as setitimer) elapses. SIGALRM is sent when real or clock time elapses. SIGVTALRM is sent when CPU time used by the process elapses. SIGPROF is sent when CPU time used by the process and by the system on behalf of the process elapses');
    $messages['SIGTERM'] = __('The SIGTERM signal is sent to a process to request its termination. Unlike the SIGKILL signal, it can be caught and interpreted or ignored by the process. This allows the process to perform nice termination releasing resources and saving state if appropriate. It should be noted that SIGINT is nearly identical to SIGTERM');
    $messages['SIGSTKFLT'] = __('SIGSTKFLT');
    $messages['SIGCHLD'] = __('The SIGCHLD signal is sent to a process when a child process terminates, is interrupted, or resumes after being interrupted. One common usage of the signal is to instruct the operating system to clean up the resources used by a child process after its termination without an explicit call to the wait system call');
    $messages['SIGCONT'] = __('The SIGCONT signal instructs the operating system to continue (restart) a process previously paused by the SIGSTOP or SIGTSTP signal. One important use of this signal is in job control in the Unix shell');
    $messages['SIGSTOP'] = __('The SIGSTOP signal instructs the operating system to stop a process for later resumption');
    $messages['SIGTSTP'] = __('The SIGTSTP signal is sent to a process by its controlling terminal to request it to stop temporarily. It is commonly initiated by the user pressing Control-Z. Unlike SIGSTOP, the process can register a signal handler for or ignore the signal');
    $messages['SIGTTIN'] = __('The SIGTTIN and SIGTTOU signals are sent to a process when it attempts to read in or write out respectively from the tty while in the background. Typically, this signal can be received only by processes under job control; daemons do not have controlling terminals and should never receive this signal');
    $messages['SIGTTOU'] = __('The SIGTTIN and SIGTTOU signals are sent to a process when it attempts to read in or write out respectively from the tty while in the background. Typically, this signal can be received only by processes under job control; daemons do not have controlling terminals and should never receive this signal');
    $messages['SIGURG'] = __('The SIGURG signal is sent to a process when a socket has urgent or out-of-band data available to read');
    $messages['SIGXCPU'] = __('The SIGXCPU signal is sent to a process when it has used up the CPU for a duration that exceeds a certain predetermined user-settable value. The arrival of a SIGXCPU signal provides the receiving process a chance to quickly save any intermediate results and to exit gracefully, before it is terminated by the operating system using the SIGKILL signal');
    $messages['SIGXFSZ'] = __('The SIGXFSZ signal is sent to a process when it grows a file larger than the maximum allowed size');
    $messages['SIGVTALRM'] = __('The SIGALRM, SIGVTALRM and SIGPROF signal is sent to a process when the time limit specified in a call to a preceding alarm setting function (such as setitimer) elapses. SIGALRM is sent when real or clock time elapses. SIGVTALRM is sent when CPU time used by the process elapses. SIGPROF is sent when CPU time used by the process and by the system on behalf of the process elapses');
    $messages['SIGPROF'] = __('The SIGALRM, SIGVTALRM and SIGPROF signal is sent to a process when the time limit specified in a call to a preceding alarm setting function (such as setitimer) elapses. SIGALRM is sent when real or clock time elapses. SIGVTALRM is sent when CPU time used by the process elapses. SIGPROF is sent when CPU time used by the process and by the system on behalf of the process elapses');
    $messages['SIGWINCH'] = __('The SIGWINCH signal is sent to a process when its controlling terminal changes its size (a window change)');
    $messages['SIGIO'] = __('SIGIO');
    $messages['SIGPWR'] = __('The SIGPWR signal is sent to a process when the system experiences a power failure');
    $messages['SIGSYS'] = __('The SIGSYS signal is sent to a process when it passes a bad argument to a system call');
    $messages['COREDUMP'] = __('Your process terminated and produced a core dump file');
    $messages['NONZERO'] = __('Your process produced a nonzero exit status');
    $messages['LIMITSERROR'] = __('Problems while setting limits. You may not be root');
    $messages['COULDNOTEXECUTECOMMAND'] = __('Your process tried to execute a unrecognized command');
    $this->commitsMessages = $messages;
  }

  protected function generateRandomString($size)
  {
    if ($size > 30) $size = 30;
    $arr = str_split('ABCDEFGHJKLMNPQRSTUVXWYZ123456789');
    shuffle($arr);
    $arr = array_slice($arr, 0, $size); // get the first six (random) characters out
    $str = implode('', $arr); // smush them back into a string
    return $str;
  }

  protected function generateRandomNumber($size)
  {
    if ($size > 30) $size = 30;
    $arr = str_split('012345678901234567890123456789');
    shuffle($arr);
    $arr = array_slice($arr, 0, $size); // get the first six (random) characters out
    $str = implode('', $arr); // smush them back into a string
    return $str;
  }
}
