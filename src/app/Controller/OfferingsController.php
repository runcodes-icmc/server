<?php
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');
App::uses('Folder', 'Utility');
/**
 * Offerings Controller
 *
 * @property Offering $Offering
 * @property PaginatorComponent $Paginator
 */
class OfferingsController extends AppController
{

  /**
   * Components
   *
   * @var array
   */
  public $components = array('Paginator');
  protected $error_messages = array(
    1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
    2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
    3 => 'The uploaded file was only partially uploaded',
    4 => 'No file was uploaded',
    6 => 'Missing a temporary folder',
    7 => 'Failed to write file to disk',
    8 => 'A PHP extension stopped the file upload',
    'post_max_size' => 'The uploaded file exceeds the post_max_size directive in php.ini',
    'max_file_size' => 'File is too big',
    'min_file_size' => 'File is too small',
    'accept_file_types' => 'Filetype not allowed',
    'max_number_of_files' => 'Maximum number of files exceeded',
    'max_width' => 'Image exceeds maximum width',
    'min_width' => 'Image requires a minimum width',
    'max_height' => 'Image exceeds maximum height',
    'min_height' => 'Image requires a minimum height'
  );

  private $studentAuthorized = array('view', 'my');
  private $demoPostNotAuthorized = array('edit', 'add', 'view', 'setprofessor', 'setassistant', 'setstudent', 'mail', 'mailfileupload', 'grades', 'email', 'getprofessorsandassistantslist');
  private $professorAuthorized = array('edit', 'add', 'view', 'setprofessor', 'setassistant', 'setstudent', 'mail', 'mailfileupload', 'exporttocsv', 'stats', 'infographic', 'grades', 'email', 'getprofessorsandassistantslist', 'listexercises');

  public function isAuthorized($user = null)
  {
    $this->loadModel('User');
    $this->loadModel('Enrollment');
    if (!Configure::read('Config.allowDemoPostRequest') && $this->currentUser['domain'] == "demo.run.codes" && !$this->request->is("get") && in_array(strtolower($this->request->params['action']), $this->demoPostNotAuthorized)) {
      $this->Session->setFlash(__("Sorry! This action is not allowed in demo mode"));
      $this->redirect('/home');
    }
    if ($user['type'] >= $this->User->getAdminIndex()) {
      return true;
    }
    if (strtolower($this->request->params['action']) == "my") {
      return true;
    }
    if ($user['type'] >= $this->User->getProfessorIndex()) {
      if (strtolower($this->request->params['action']) == "add") {
        return true;
      }
    }
    if (in_array(strtolower($this->request->params['action']), $this->professorAuthorized)) {
      if ($this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $this->request->params['pass'][0])) {
        return true;
      }
    }
    if (in_array(strtolower($this->request->params['action']), $this->studentAuthorized)) {
      if ($this->Enrollment->isEnrolled($this->currentUser['email'], $this->request->params['pass'][0])) {
        return true;
      }
      return false;
    }
  }

  public function beforeRender()
  {
    parent::beforeRender();
    if (isset($this->request->params['pass'][0])) {
      $this->loadModel('Enrollment');
      if (isset($this->currentUser['onlyStudent']) && $this->currentUser['onlyStudent']) {
        $assistantOrProfessor =  false;
      } else {
        $assistantOrProfessor = ($this->currentUser['type'] >= $this->User->getAdminIndex()) ? true : $this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $this->request->params['pass'][0]);
      }
      $this->set(compact('assistantOrProfessor'));
    }
  }

  /**
   * index method
   *
   * @return void
   */
  public function index()
  {
    $this->layout = "template2015";
    $this->loadModel('University');
    $this->loadModel('Enrollment');
    $this->loadModel('User');
    $this->University->recursive = -1;
    $this->Enrollment->recursive = -1;
    $this->User->recursive = -1;
    $this->Offering->recursive = 0;
    if ($this->request->is('post')) {
      if ($this->request->data['Offering']['finished'] == '0') {
        $this->redirect(array('controller' => 'Offerings', 'action' => 'index', 'university' => $this->request->data['Offering']['university_id']));
      } else {
        $this->redirect(array('controller' => 'Offerings', 'action' => 'index', 'closed' => 1, 'university' => $this->request->data['Offering']['university_id']));
      }
    }
    if (isset($this->request->params['named']['closed'])) {
      $cond = array('Offering.end_date <= NOW()');
    } else {
      $cond = array('Offering.end_date >= NOW()');
    }
    if (isset($this->request->params['named']['university'])) {
      $cond['Course.university_id'] = $this->request->params['named']['university'];
    }
    $this->paginate = array('limit' => 20, 'conditions' => $cond, 'fields' => array('Offering.id', 'Offering.end_date', 'Offering.classroom', 'Course.title', 'Course.university_id'));
    $offerings = $this->Paginator->paginate();
    foreach ($offerings as $k => $o) {
      $uni = $this->University->findById($o['Course']['university_id'], array('id', 'name', 'abbreviation'));
      $prof = $this->Enrollment->findAllByOfferingIdAndRole($o['Offering']['id'], $this->Enrollment->getProfessorRole());
      $offerings[$k]['Professor'] = array();
      foreach ($prof as $u) {
        $user = $this->User->findByEmail($u['Enrollment']['user_email'], array('name'));
        $offerings[$k]['Professor'][$u['Enrollment']['user_email']] = $user['User']['name'];
      }
      uasort($offerings[$k]['Professor'], "strnatcmp");
      $offerings[$k]['University'] = $uni['University'];
    }
    $this->set('universities', $this->University->find('list', array('order' => array("name" => "ASC"))));
    $this->set('offerings', $offerings);
  }

  public function listExercises($offering_id)
  {
    if (!$this->request->is('post')) {
      $this->redirect('/home');
    }
    $this->loadModel('Offering');
    $this->loadModel('Course');
    $this->loadModel('Exercise');
    $this->Offering->recursive = -1;
    $this->Course->recursive = -1;
    $this->Exercise->recursive = -1;
    $exercises = $this->Exercise->findAllByOfferingIdAndRemoved($offering_id, false, array('id', 'title', 'deadline', 'offering_id'), array('deadline'));
    foreach ($exercises as $k => $exercise) {
      $offering = $this->Offering->findById($exercise['Exercise']['offering_id'], array('course_id', 'classroom'));
      $course = $this->Course->findById($offering['Offering']['course_id'], array('code', 'name', 'university_id'));
      $exercises[$k]['Offering'] = $offering['Offering'];
      $exercises[$k]['Course'] = $course['Course'];
    }
    echo json_encode($exercises);
    exit();
  }

  public function my()
  {
    $this->layout = "template2015";
    $this->loadModel('University');
    $this->loadModel('Enrollment');
    $this->University->recursive = -1;
    $this->Enrollment->recursive = -1;
    $this->Offering->recursive = 0;
    $my_offerings = $this->Offering->find('all', array('conditions' => array("Offering.id IN (SELECT enrollments.offering_id FROM enrollments WHERE user_email='{$this->currentUser['email']}')", 'end_date > NOW()')));
    $my_closed_offerings = $this->Offering->find('all', array('conditions' => array("Offering.id IN (SELECT enrollments.offering_id FROM enrollments WHERE user_email='{$this->currentUser['email']}')", 'end_date < NOW()')));
    foreach ($my_offerings as $k => $o) {
      $uni = $this->University->findById($o['Course']['university_id'], array('name', 'abbreviation'));
      $enroll = $this->Enrollment->findByUserEmailAndOfferingId($this->currentUser['email'], $o['Offering']['id'], array('id', 'role'));
      $my_offerings[$k]['Enrollment'] = $enroll['Enrollment'];
      $my_offerings[$k]['University'] = $uni['University'];
    }
    foreach ($my_closed_offerings as $k => $o) {
      $uni = $this->University->findById($o['Course']['university_id'], array('name', 'abbreviation'));
      $enroll = $this->Enrollment->findByUserEmailAndOfferingId($this->currentUser['email'], $o['Offering']['id'], array('id', 'role'));
      $my_closed_offerings[$k]['Enrollment'] = $enroll['Enrollment'];
      $my_closed_offerings[$k]['University'] = $uni['University'];
    }
    $this->set('my_offerings',  $my_offerings);
    $this->set('my_closed_offerings',  $my_closed_offerings);
  }

  public function infographic($id)
  {
    if (!$this->request->is('post')) {
      $this->redirect('/home');
    }
    $this->autoRender = false;

    $info = new stdClass();
    $this->loadModel('Enrollment');
    $this->Enrollment->recursive = -1;
    $info->students = $this->Enrollment->find('count', array('conditions' => array('offering_id' => $id, 'role' => $this->Enrollment->getStudentRole())));

    $this->loadModel('Exercise');
    $this->Exercise->recursive = -1;
    $exercises = $this->Exercise->findAllByOfferingId($id, null, array('deadline', 'title'));
    $exercisesList = array();
    foreach ($exercises as $ex) {
      array_push($exercisesList, $ex['Exercise']['id']);
    }

    $this->loadModel('Commit');
    $this->Commit->recursive = -1;
    $scores = $this->Commit->find('all', array('fields' => array('id', 'exercise_id', 'user_email', 'score'), 'conditions' => array("exercise_id" => $exercisesList, " Commit.commit_time = (SELECT MAX(commit_time) FROM commits c2 WHERE Commit.user_email = c2.user_email AND Commit.exercise_id=c2.exercise_id GROUP BY user_email, exercise_id)"), 'order' => 'score DESC, commit_time ASC'));
    $total = 0;
    $plus5 = 0;
    $sum = 0;
    foreach ($scores as $score) {
      $sum += $score['Commit']['score'];
      $total++;
      if ($score['Commit']['score'] > 5) {
        $plus5++;
      }
    }
    if ($total == 0) {
      $info->gradespect = 0;
      $info->gradesavg = 0;
    } else {
      $info->gradespect = number_format($plus5 / $total * 100, 1);
      $info->gradesavg = number_format($sum / $total, 2);
    }

    echo json_encode($info);
  }

  public function stats($id, $participant_email = null)
  {
    if (!$this->request->is('post')) {
      $this->redirect('/home');
    }
    $this->autoRender = false;

    $chart = new stdClass();
    $chart->colors = array('#c0392b', '#1E88BD');
    $chart->labelsX = array();
    $chart->series[0] = new stdClass();
    $chart->series[0]->name = __("Classroom Avg.");
    $chart->series[0]->data = array();

    if (!is_null($participant_email)) {
      $chart->series[1] = new stdClass();
      $this->loadModel('User');
      $this->User->recursive = -1;
      $participant = $this->User->findByEmail($participant_email, array('name'));
      if (isset($participant['User']['name'])) {
        $chart->series[1]->name = $participant['User']['name'];
        $chart->series[1]->data = array();
      } else {
        $participant_email = null;
      }
    }

    $this->loadModel('Exercise');
    $this->loadModel('Commit');
    $this->Exercise->recursive = -1;
    $exercises = $this->Exercise->findAllByOfferingIdAndRemoved($id, false, array('id'), array('deadline' => 'ASC', 'title' => 'ASC'));
    $prefix = __("Exercise") . " ";
    if (count($exercises) > 10) {
      $prefix = __("Ex") . ". ";
    } else if (count($exercises) > 20) {
      $prefix = "";
    }
    foreach ($exercises as $k => $exercise) {
      $scores2 = $this->Commit->find('all', array(
        'fields' => 'AVG(score)',
        'recursive' => -1,
        'conditions' => array('Commit.exercise_id' => $exercise['Exercise']['id'], " Commit.commit_time = (SELECT MAX(commit_time) FROM commits c2 WHERE Commit.user_email = c2.user_email AND Commit.exercise_id=c2.exercise_id GROUP BY user_email, exercise_id)")
      ));
      $avg_grade = $scores2[0][0]['avg'];
      $item = array();
      $item[0] = ($k + 1);
      $item[1] = $avg_grade;
      array_push($chart->series[0]->data, $item);
      array_push($chart->labelsX, array($k + 1, $prefix . ($k + 1)));

      if (!is_null($participant_email)) {
        $score = $this->Commit->find('first', array('fields' => array('id', 'score'), 'conditions' => array('Commit.user_email' => $participant_email, 'Commit.exercise_id' => $exercise['Exercise']['id'], " Commit.commit_time = (SELECT MAX(commit_time) FROM commits c2 WHERE Commit.user_email = c2.user_email AND Commit.exercise_id=c2.exercise_id GROUP BY user_email, exercise_id)"), 'order' => 'score DESC, commit_time ASC'));
        $item = array();
        $item[0] = ($k + 1);
        $item[1] = (isset($score['Commit']['score'])) ? $score['Commit']['score'] : 0.0;
        array_push($chart->series[1]->data, $item);
      }
    }


    echo json_encode($chart);
  }

  public function view($offering_id = null)
  {
    $this->layout = "template2015";
    $this->loadModel('Enrollment');
    $this->loadModel('Exercise');
    $this->loadModel('Commit');
    $this->Offering->recursive = -1;
    if (!$this->Offering->exists($offering_id)) {
      throw new NotFoundException(__('Invalid offering'));
    }

    $this->Enrollment->recursive = -1;
    $command = $this->Enrollment->find('all', array('fields' => array('user_email', 'role'), 'conditions' => array('offering_id' => $offering_id, '(role = 2 OR role = 1)')));
    $professors = array();
    $assistants = array();

    $this->loadModel('User');

    if (isset($this->currentUser['onlyStudent']) && $this->currentUser['onlyStudent']) {
      $assistantOrProfessor =  false;
    } else {
      $assistantOrProfessor = ($this->currentUser['type'] >= $this->User->getAdminIndex()) ? true : $this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $this->request->params['pass'][0]);
    }

    foreach ($command as $member) {
      $item = $this->User->findByEmail($member['Enrollment']['user_email'], array('name', 'email'));
      if ($member['Enrollment']['role'] == 2) {
        array_push($professors, $item);
      } else {
        array_push($assistants, $item);
      }
    }
    unset($command);

    $this->Exercise->recursive = -1;
    if ($assistantOrProfessor) {
      $exercises = $this->Exercise->find('all', array('conditions' => array('offering_id' => $offering_id, 'removed' => false), 'order' => array('deadline', 'title')));
    } else {
      $exercises = $this->Exercise->find('all', array('conditions' => array('AND' => array('offering_id' => $offering_id, 'removed' => false, 'OR' => array('open_date < NOW()', 'show_before_opening' => true))), 'order' => array('deadline', 'title')));
    }
    $this->loadModel('Commit');
    $this->Commit->recursive = -1;
    foreach ($exercises as $k => $ex) {
      $commit = $this->Commit->findByExerciseIdAndUserEmail($ex['Exercise']['id'], $this->Auth->user('email'), array('status', 'corrects', 'score'), array('commit_time' => 'desc'));
      if (count($commit) > 0) {
        $exercises[$k]['MyCommit'] = $commit['Commit'];
      } else {
        $exercises[$k]['MyCommit']['name_status'] = $this->Exercise->getNotDeliveredNameStatus();
        $exercises[$k]['MyCommit']['status_color'] = $this->Exercise->getNotDeliveredStatusColor();
        $exercises[$k]['MyCommit']['corrects'] = 0;
        $exercises[$k]['MyCommit']['correct_color'] = "important";
        $exercises[$k]['MyCommit']['score'] = 0.0;
        $exercises[$k]['MyCommit']['score_color'] = "important";
      }
    }

    $this->Offering->recursive = 0;
    $options = array('conditions' => array('Offering.' . $this->Offering->primaryKey => $offering_id));
    $offering = $this->Offering->find('first', $options);

    if ($assistantOrProfessor) {
      $this->loadModel('Enrollment');
      $this->Enrollment->recursive = 0;
      $enrollments = $this->Enrollment->findAllByOfferingIdAndRole($offering['Offering']['id'], $this->Enrollment->getStudentRole(), array('user_email', 'id', 'User.name'), array('User.name'));
      $studentsList = array();
      $studentsList[-1] = __("Select a student...");
      foreach ($enrollments as $enr) {
        $studentsList[$enr['Enrollment']['user_email']] = $enr['User']['name'];
      }
      $this->set(compact('studentsList'));
    }

    $this->loadModel('University');
    $this->University->recursive = -1;
    $university = $this->University->findById($offering['Course']['university_id'], array('abbreviation', 'student_identifier_text'));
    $offering['Course']['University'] = $university['University'];

    //        $this->loadModel('Enrollment');
    //        $this->Enrollment->recursive = 0;
    //        $enrollments = $this->Enrollment->findAllByOfferingId($offering['Offering']['id'],array('user_email','id','role'),array('User.name'));
    //        $offering['Enrollment'] = array();
    //        foreach ($enrollments as $en) {
    //            $user = $this->User->findByEmail($en['Enrollment']['user_email'],array('name','identifier','email','type'));
    //            $user['user_email'] = $en['Enrollment']['user_email'];
    //            $user['role'] = $en['Enrollment']['role'];
    //            $user['role_name'] = $en['Enrollment']['role_name'];
    //            $user['id'] = $en['Enrollment']['id'];
    //            array_push($offering['Enrollment'], $user);
    //        }
    //        unset($user);


    //        $scores = array();
    //        foreach ($exercises as $k => $ex) {
    //            $item = array();
    //            $item['Exercise']=$ex['Exercise'];
    //            foreach ($offering['Enrollment'] as $student) {
    //                $score = $this->Commit->find('first', array('fields' => array('id','score','status'),'conditions' => array('Commit.user_email' => $student['user_email'],'Commit.exercise_id' => $ex['Exercise']['id'], " Commit.commit_time = (SELECT MAX(commit_time) FROM commits c2 WHERE Commit.user_email = c2.user_email AND Commit.exercise_id=c2.exercise_id GROUP BY user_email, exercise_id)"), 'order' => 'score DESC, commit_time ASC'));
    //                if (isset($score['Commit']['score'])) {
    //                    $item['Grade'][$student['user_email']]=$score['Commit']['score'];
    //                    $item['Status'][$student['user_email']]=$score['Commit']['name_status'];
    //                    $item['Commit'][$student['user_email']]=$score['Commit']['id'];
    //                } else {
    //                    $item['Grade'][$student['user_email']]=0.0;
    //                    if (isset($score['Commit']['name_status'])) {
    //                        $item['Status'][$student['user_email']]=$score['Commit']['name_status'];
    //                    } else {
    //                        $item['Status'][$student['user_email']]=$this->Exercise->getNotDeliveredNameStatus();
    //                    }
    //                }
    //
    //            }
    //            array_push($scores, $item);
    //        }
    //        unset($item);
    //        unset($score);

    $breadcrumbs = array();
    array_push($breadcrumbs, array('link' => '#', 'text' => $offering['Course']['code']));
    $this->set(compact('professors', 'assistants', 'exercises', 'offering', 'scores', 'breadcrumbs'));
    $this->loadModel('User');
  }

  public function grades($offering_id)
  {
    $this->layout = "template2015";
    $this->Offering->recursive = -1;
    if (!$this->Offering->exists($offering_id)) {
      throw new NotFoundException(__('Invalid offering'));
    }
    $this->Offering->recursive = 0;
    $options = array('conditions' => array('Offering.' . $this->Offering->primaryKey => $offering_id));
    $offering = $this->Offering->find('first', $options);

    $this->loadModel("Exercise");
    $this->Exercise->recursive = -1;
    $exercises = $this->Exercise->find('all', array('conditions' => array('offering_id' => $offering_id, 'removed' => false), 'order' => 'deadline'));

    $this->loadModel('University');
    $university = $this->University->findById($offering['Course']['university_id'], array('student_identifier_text'));
    $offering['Course']['University'] = $university['University'];

    $this->loadModel('Enrollment');
    $this->Enrollment->recursive = 0;
    $enrollments = $this->Enrollment->findAllByOfferingIdAndRole($offering['Offering']['id'], $this->Enrollment->getStudentRole(), array('user_email', 'id', 'role'), array('User.name'));
    $offering['Enrollment'] = array();
    foreach ($enrollments as $en) {
      $user = $this->User->findByEmail($en['Enrollment']['user_email'], array('name', 'identifier', 'email', 'type'));
      $user['user_email'] = $en['Enrollment']['user_email'];
      $user['role'] = $en['Enrollment']['role'];
      $user['role_name'] = $en['Enrollment']['role_name'];
      $user['id'] = $en['Enrollment']['id'];
      array_push($offering['Enrollment'], $user);
    }
    unset($user);

    $this->loadModel("Commit");
    $this->Commit->recursive = -1;
    $scores = array();
    foreach ($exercises as $k => $ex) {
      $item = array();
      $item['Exercise'] = $ex['Exercise'];
      foreach ($offering['Enrollment'] as $student) {
        $score = $this->Commit->find('first', array('fields' => array('id', 'score', 'status'), 'conditions' => array('Commit.user_email' => $student['user_email'], 'Commit.exercise_id' => $ex['Exercise']['id'], " Commit.commit_time = (SELECT MAX(commit_time) FROM commits c2 WHERE Commit.user_email = c2.user_email AND Commit.exercise_id=c2.exercise_id GROUP BY user_email, exercise_id)"), 'order' => 'score DESC, commit_time ASC'));
        if (isset($score['Commit']['score'])) {
          $item['Grade'][$student['user_email']] = $score['Commit']['score'];
          $item['Status'][$student['user_email']] = $score['Commit']['name_status'];
          $item['Commit'][$student['user_email']] = $score['Commit']['id'];
        } else {
          $item['Grade'][$student['user_email']] = "#";
          if (isset($score['Commit']['name_status'])) {
            $item['Status'][$student['user_email']] = $score['Commit']['name_status'];
          } else {
            $item['Status'][$student['user_email']] = $this->Exercise->getNotDeliveredNameStatus();
          }
        }
      }
      array_push($scores, $item);
    }
    unset($item);
    unset($score);

    $breadcrumbs = array();
    array_push($breadcrumbs, array('link' => array('action' => 'view', $offering['Offering']['id']), 'text' => $offering['Course']['code']));
    array_push($breadcrumbs, array('link' => '#', 'text' => __("Grades")));
    $this->set(compact('offering', 'scores', 'breadcrumbs'));
  }

  public function setProfessor($id = null, $user_email = null)
  {
    if (!$this->Offering->exists($id)) {
      throw new NotFoundException(__('Invalid offering'));
    }
    if ($this->request->is('post')) {
      $this->loadModel('User');
      if (!$this->User->exists($user_email)) {
        throw new NotFoundException(__('Invalid offering'));
      }
      $this->loadModel('Enrollment');

      $enrollment = $this->Enrollment->findByOfferingIdAndUserEmail($id, $user_email);
      if (count($enrollment) == 0) {
        throw new NotFoundException(__('User not enrolled'));
      }
      $this->Enrollment->id = $enrollment['Enrollment']['id'];
      $this->Enrollment->saveField('role', 2);
      Log::register("The user has set the user " . $user_email . " as professor of the Offering #" . $id, $this->currentUser);
      $this->Session->setFlash(__('The user %s is a professor now', array($user_email)), 'default', array(), 'success');
      $this->redirect(array('controller' => 'Offerings', 'action' => 'view', $id));
    } else {
      throw new NotFoundException(__('Invalid offering'));
    }
  }

  public function setAssistant($id = null, $user_email = null)
  {
    if (!$this->Offering->exists($id)) {
      throw new NotFoundException(__('Invalid offering'));
    }
    if ($this->request->is('post')) {
      $this->loadModel('User');
      if (!$this->User->exists($user_email)) {
        throw new NotFoundException(__('Invalid offering'));
      }
      $this->loadModel('Enrollment');
      $enrollment = $this->Enrollment->findByOfferingIdAndUserEmail($id, $user_email);
      if (count($enrollment) == 0) {
        throw new NotFoundException(__('User not enrolled'));
      }
      $this->Enrollment->id = $enrollment['Enrollment']['id'];
      $this->Enrollment->saveField('role', 1);

      Log::register("The user has set the user " . $user_email . " as assistant of the Offering #" . $id, $this->currentUser);
      $this->Session->setFlash(__('The user %s is an assistant professor now', array($user_email)), 'default', array(), 'success');
      $this->redirect(array('controller' => 'Offerings', 'action' => 'view', $id));
    } else {
      throw new NotFoundException(__('Invalid offering'));
    }
  }

  public function setStudent($id = null, $user_email = null)
  {
    if (!$this->Offering->exists($id)) {
      throw new NotFoundException(__('Invalid offering'));
    }

    if ($this->request->is('post')) {
      $this->loadModel('User');
      if (!$this->User->exists($user_email)) {
        throw new NotFoundException(__('Invalid offering'));
      }
      $this->loadModel('Enrollment');

      $enrollment = $this->Enrollment->findByOfferingIdAndUserEmail($id, $user_email);
      if (count($enrollment) == 0) {
        throw new NotFoundException(__('User not enrolled'));
      }
      $this->Enrollment->id = $enrollment['Enrollment']['id'];
      $this->Enrollment->saveField('role', 0);

      Log::register("The user has set the user " . $user_email . " as student of the Offering #" . $id, $this->currentUser);
      $this->Session->setFlash(__('The user %s is a student now', array($user_email)), 'default', array(), 'success');
      $this->redirect(array('controller' => 'Offerings', 'action' => 'view', $id));
    } else {
      throw new NotFoundException(__('Invalid offering'));
    }
  }

  public function getProfessorsAndAssistantsList($id = null)
  {
    $this->autoRender = false;
    if (!$this->Offering->exists($id)) {
      throw new NotFoundException(__('Invalid offering'));
    }
    if ($this->request->is('post')) {
      $this->loadModel('Enrollment');
      $this->loadModel('User');
      $this->Enrollment->recursive = -1;
      $this->User->recursive = -1;
      $profAndAssist = array();
      $enrollment = $this->Enrollment->findAllByOfferingIdAndRole($id, $this->Enrollment->getProfessorRole(), array('user_email'));
      foreach ($enrollment as $en) {
        $p = new stdClass();
        $this->User->id = $en['Enrollment']['user_email'];
        $p->email = $en['Enrollment']['user_email'];
        $p->name = $this->User->field('name');
        $p->role = __("Professor");
        array_push($profAndAssist, $p);
      }
      $enrollment = $this->Enrollment->findAllByOfferingIdAndRole($id, $this->Enrollment->getAssistantRole(), array('id', 'user_email'));
      foreach ($enrollment as $en) {
        $p = new stdClass();
        $this->User->id = $en['Enrollment']['user_email'];
        $p->email = $en['Enrollment']['user_email'];
        $p->name = $this->User->field('name');
        $p->role = __("Assistant");
        array_push($profAndAssist, $p);
      }
      echo json_encode($profAndAssist);
    } else {
      throw new NotFoundException(__('Invalid offering'));
    }
  }

  /**
   * add method
   *
   * @return void
   */

  public function ban($offering_id, $user_email)
  {
    $this->request->onlyAllow('post', 'delete');
    $this->loadModel('Enrollment');
    $this->Enrollment->recursive = -1;
    $en = $this->Enrollment->findByUserEmailAndOfferingId($user_email, $offering_id);
    if (count($en) > 0) {
      $this->Enrollment->id = $en['Enrollment']['id'];
      $this->Enrollment->saveField('banned', true);
      $this->Session->setFlash(__('The user has been banned form this offering'), 'default', array(), 'success');
      $this->redirect(array("controller" => "Offerings", "action" => "view", $offering_id));
    }
  }

  public function add()
  {
    $this->layout = "template2015";
    if ($this->request->is('post')) {
      $this->Offering->create();
      if ($this->currentUser['University']['isPaid']) {
        $code = $this->request->data['Offering']['code'];
        //Validar codigo com pagamento
      }
      do {
        $code = $this->generateRandomString(4);
        $this->request->data['Offering']['enrollment_code'] = $code;
      } while ($this->Offering->isEnrollmentCodeUsed($code));
      $this->request->data['Offering']['year'] = 0;
      $this->request->data['Offering']['term'] = 0;
      $this->request->data['Offering']['end_date'] = DateTime::createFromFormat('d/m/Y', $this->request->data['Offering']['end_date'])->format('Y-m-d');
      if ($this->Offering->save($this->request->data)) {
        Log::register("Created the offering #" . $this->Offering->id, $this->Auth->user());
        $this->Session->setFlash(__('The offering has been saved'), 'default', array(), 'success');
        //Assign user as a professor
        $enrollment = array();
        $this->loadModel('Enrollment');
        $this->Enrollment->create();
        $enrollment['Enrollment']['offering_id'] = $this->Offering->id;
        $enrollment['Enrollment']['role'] = 2;
        $enrollment['Enrollment']['user_email'] = $this->Auth->user('email');
        $this->Enrollment->save($enrollment, false);
        $this->loadModel("Message");
        $this->loadModel("Course");
        $this->Course->id = $this->request->data['Offering']['course_id'];
        $email_vars['user_name'] = $this->currentUser['name'];
        $email_vars['course_code'] = $this->Course->field("code");
        $email_vars['course_title'] = $this->Course->field("title");
        $email_vars['offering_classroom'] = $this->request->data['Offering']['classroom'];
        $email_vars['offering_end_date'] = DateTime::createFromFormat('Y-m-d', $this->request->data['Offering']['end_date'])->format('d/m/Y');
        $email_vars['enrollment_code'] = $this->request->data['Offering']['enrollment_code'];
        $email_vars['offering_id'] = $this->Offering->id;
        $template = array('mail_view' => Configure::read('Config.language') . "_new_offering");
        $template['viewVars'] = $email_vars;
        $this->Message->sendMail($this->Auth->user('email'), __("Your offering was created"), null, null, $template);
        return $this->redirect('/home');
      } else {
        $this->Session->setFlash(__('The offering could not be saved. Please, try again.'));
      }
    }
    if (isset($this->request->data['Offering']['end_date'])) {
      $this->request->data['Offering']['end_date'] = DateTime::createFromFormat('Y-m-d', $this->request->data['Offering']['end_date'])->format('d/m/Y');
    }
    $courses = $this->Offering->Course->find('list', array('conditions' => array('university_id' => $this->currentUser['university_id']), 'order' => 'title'));
    $coursesCount = count($courses);
    $this->set(compact('coursesCount'));
    $this->set('university', $this->currentUser['University']);
  }

  //        public function updateEnrollmentCode () {
  //            $offerings = $this->Offering->find('all');
  //            foreach ($offerings as $offering) {
  //                if (strlen($offering['Offering']['enrollment_code']) == 0) {
  //                    do {
  //                        $code = $this->generateRandomString(4);
  //                    } while ($this->Offering->isEnrollmentCodeUsed($code));
  //                    $this->Offering->id = $offering['Offering']['id'];
  //                    $this->Offering->saveField('enrollment_code',$code);
  //                }
  //            }
  //            echo "Enrollment code updated for each offering";
  //            die();
  //        }

  /**
   * edit method
   *
   * @throws NotFoundException
   * @param string $id
   * @return void
   */
  public function edit($id = null)
  {
    $this->layout = 'template2015';
    if (!$this->Offering->exists($id)) {
      throw new NotFoundException(__('Invalid offering'));
    }
    $this->Offering->id = $id;
    if ($this->request->is('post') || $this->request->is('put')) {
      $this->request->data['Offering']['end_date'] = DateTime::createFromFormat('d/m/Y', $this->request->data['Offering']['end_date'])->format('Y-m-d');
      if ($this->Offering->save($this->request->data, true, array('end_date', 'classroom'))) {
        Log::register("Edited the offering #" . $this->Offering->id, $this->Auth->user());
        $this->Session->setFlash(__('The offering has been saved'), 'default', array(), 'success');
        return $this->redirect(array('action' => 'my'));
      } else {
        $this->Session->setFlash(__('The offering could not be saved. Please, try again.'));
      }
    } else {
      $options = array('conditions' => array('Offering.' . $this->Offering->primaryKey => $id));
      $this->request->data = $this->Offering->find('first', $options);
    }
    $this->loadModel('Course');
    $this->Course->recursive = -1;
    $this->set('course', $this->Course->findById($this->Offering->getCourseId()));
  }

  /**
   * delete method
   *
   * @throws NotFoundException
   * @param string $id
   * @return void
   */
  //	public function delete($id = null) {
  //		$this->Offering->id = $id;
  //		if (!$this->Offering->exists()) {
  //			throw new NotFoundException(__('Invalid offering'));
  //		}
  //		$this->request->onlyAllow('post', 'delete');
  //		if ($this->Offering->delete()) {
  //                        Log::register("Deleted the offering #".$this->Offering->id, $this->Auth->user());
  //			$this->Session->setFlash(__('Offering deleted'));
  //			return $this->redirect(array('action' => 'index'));
  //		}
  //		$this->Session->setFlash(__('Offering was not deleted'));
  //		return $this->redirect(array('action' => 'index'));
  //	}

  //        public function mail($id = null) {
  //		$this->Offering->id = $id;
  //		if (!$this->Offering->exists()) {
  //			throw new NotFoundException(__('Invalid offering'));
  //		}
  //                if(isset($this->request->data['Files'])) {
  //                    $files = $this->request->data['Files'];
  //                }
  //                $this->loadModel('Enrollment');
  //
  //                $this->Enrollment->recursive = 0;
  //                $this->Offering->recursive = 0;
  //                $offering = $this->Offering->findById($id);
  //                $enrolled_list = $this->Enrollment->findAllByOfferingId($id);
  //                $recipes = array();
  //                foreach ($enrolled_list as $participant) {
  //                    array_push($recipes, $participant['Enrollment']['user_email']);
  //                }
  //                //USAR MODEL PARA EMAILS
  //                $this->loadModel('Message');
  //                $subject = "[".__("run.codes")." - ".$offering['Course']['name']."] ".$this->request->data['Offering']['subject'];
  //                $template = array('mail_view' => Configure::read('Config.language')."_offering_mail");
  //                $mailViewVars = array();
  //                $mailViewVars['course_name'] = $offering['Course']['name'];
  //                $mailViewVars['message'] = nl2br($this->request->data['Offering']['message']);
  //                $template['viewVars'] = $mailViewVars;
  //                $message = null; //The message is inside the mail view
  //                $attach = array();
  //                if(is_array($files)) {
  //                    foreach($files as $file) {
  //                        $dir = Configure::read('Upload.dir')."/attachments/tmp/".$file['hash']."/".$file['path'];
  //                        if(file_exists($dir) && !is_dir($dir)) {
  //                            array_push($attach, $dir);
  //                        }
  //
  //                    }
  //                }
  //                try {
  //                    $this->Message->sendMail($recipes,$subject,$message,$attach,$template);
  //                    $this->Session->setFlash(__('The message was sent'),'default',array(), 'success');
  //                } catch (Exception $e) {
  //                    Log::register("Fail when sending confirmation email", $this->request->data['User']);
  //                    $this->Session->setFlash(__("Sorry").", ".__('The message was not sent'));
  //                }
  //
  //                Log::register("Sent mail to the offering #".$id." students", $this->Auth->user());
  //
  //                //USAR MODEL
  //                if(is_array($files)) {
  //                    foreach($files as $file) {
  //                        $dir = Configure::read('Upload.dir')."/attachments/tmp/".$file['hash']."/".$file['path'];
  //                        if(file_exists($dir) && !is_dir($dir)) {
  //                            unlink($dir);
  //                        }
  //                    }
  //                }
  //                $this->redirect(array('controller' => 'offerings', 'action' => 'view', $offering['Offering']['id']));
  //        }

  public function email($offering_id = null)
  {
    $this->layout = "template2015";
    $this->Offering->recursive = -1;
    if (!$this->Offering->exists($offering_id)) {
      throw new NotFoundException(__('Invalid offering'));
    }
    $this->Offering->recursive = 0;
    $offering = $this->Offering->findById($offering_id);
    $this->loadModel("Message");
    if ($this->request->is('post') || $this->request->is('put')) {
      if (isset($this->request->data['Files'])) {
        $files = $this->request->data['Files'];
      }
      $this->loadModel('Enrollment');
      $this->Enrollment->recursive = -1;
      $enrolled_list = $this->Enrollment->findAllByOfferingId($offering_id, array('user_email'));
      $recipes = array();
      foreach ($enrolled_list as $participant) {
        if (strpos($participant['Enrollment']['user_email'], 'test.run.codes') === false) {
          array_push($recipes, $participant['Enrollment']['user_email']);
        }
      }

      $subject = "[" . __("run.codes") . " - " . $offering['Course']['name'] . "] " . $this->request->data['Offering']['subject'];
      $template = array('mail_view' => Configure::read('Config.language') . "_offering_mail");
      $mailViewVars = array();
      $mailViewVars['course_name'] = $offering['Course']['name'];
      $mailViewVars['message'] = nl2br($this->request->data['Offering']['message']);
      $template['viewVars'] = $mailViewVars;
      $message = null; //The message is inside the mail view
      $attach = array();
      //            if(isset($files) && is_array($files)) {
      //                foreach($files as $file) {
      //                    $dir = Configure::read('Upload.dir')."/attachments/tmp/".$file['hash']."/".$file['path'];
      //                    if(file_exists($dir) && !is_dir($dir)) {
      //                        array_push($attach, $dir);
      //                    }
      //
      //                }
      //            }
      try {
        $this->Message->sendMail($recipes, $subject, $message, $attach, $template);
        $this->Session->setFlash(__('The message has been sent'), 'default', array(), 'success');
        $this->redirect(array('action' => 'view', $offering_id));
      } catch (Exception $e) {
        Log::register("Fail when sending the message (Offering: " . $offering_id . ")", $this->request->data['User']);
        $this->Session->setFlash(__("Sorry") . ", " . __('The message has not been sent'));
      }
    }
    $breadcrumbs = array();
    array_push($breadcrumbs, array('link' => array('action' => 'view', $offering['Offering']['id']), 'text' => $offering['Course']['code']));
    array_push($breadcrumbs, array('link' => '#', 'text' => __("Send E-mail")));
    $this->set(compact('offering', 'breadcrumbs'));
  }

  public function mailFileUpload()
  {
    return false;
  }

  public function exportToCsv($id = null)
  {
    $this->loadModel('Enrollment');
    $this->loadModel('Exercise');
    $this->loadModel('Commit');
    $this->loadModel('Course');
    $this->loadModel('University');
    $this->loadModel('Enrollment');
    $this->Offering->recursive = -1;
    $this->Course->recursive = -1;
    $this->Enrollment->recursive = -1;
    $this->Exercise->recursive = -1;
    $this->User->recursive = -1;
    $this->Commit->recursive = -1;
    $this->University->recursive = -1;
    if (!$this->Offering->exists($id)) {
      throw new NotFoundException(__('Invalid offering'));
    }
    $exercises = $this->Exercise->find('all', array('conditions' => array('offering_id' => $id, 'removed' => false), 'order' => 'deadline'));

    $this->loadModel('Commit');

    $options = array('conditions' => array('Offering.' . $this->Offering->primaryKey => $id));
    $offering = $this->Offering->find('first', $options);
    $course = $this->Course->findById($offering['Offering']['course_id']);
    $offering['Course'] = $course['Course'];
    $university = $this->University->findById($offering['Course']['university_id'], array('student_identifier_text'));
    $offering['Course']['University'] = $university['University'];

    $this->Enrollment->recursive = 1;
    $enrollments = $this->Enrollment->findAllByOfferingIdAndRole($offering['Offering']['id'], $this->Enrollment->getStudentRole(), array('user_email', 'id', 'role'), array('User.name'));

    $offering['Enrollment'] = array();
    foreach ($enrollments as $en) {
      $user = $this->User->findByEmail($en['Enrollment']['user_email'], array('name', 'identifier', 'email', 'type'));
      $user['user_email'] = $en['Enrollment']['user_email'];
      $user['role'] = $en['Enrollment']['role'];
      $user['role_name'] = $en['Enrollment']['role_name'];
      $user['id'] = $en['Enrollment']['id'];
      array_push($offering['Enrollment'], $user);
    }
    unset($user);


    $scores = array();
    foreach ($exercises as $k => $ex) {
      $item = array();
      $item['Exercise'] = $ex['Exercise'];
      foreach ($offering['Enrollment'] as $student) {
        $score = $this->Commit->find('first', array('fields' => array('id', 'score', 'status'), 'conditions' => array('Commit.user_email' => $student['user_email'], 'Commit.exercise_id' => $ex['Exercise']['id'], " Commit.commit_time = (SELECT MAX(commit_time) FROM commits c2 WHERE Commit.user_email = c2.user_email AND Commit.exercise_id=c2.exercise_id GROUP BY user_email, exercise_id)"), 'order' => 'score DESC, commit_time ASC'));
        if (isset($score['Commit']['score'])) {
          $item['Grade'][$student['user_email']] = $score['Commit']['score'];
          $item['Status'][$student['user_email']] = $score['Commit']['name_status'];
          $item['Commit'][$student['user_email']] = $score['Commit']['id'];
        } else {
          $item['Grade'][$student['user_email']] = "-";
          if (isset($score['Commit']['name_status'])) {
            $item['Status'][$student['user_email']] = $score['Commit']['name_status'];
          } else {
            $item['Status'][$student['user_email']] = $this->Exercise->getNotDeliveredNameStatus();
          }
        }
      }
      array_push($scores, $item);
    }
    unset($item);
    unset($score);

    Log::register("The user has downloaded the grades (CSV) of the Offering #" . $id, $this->currentUser);
    //            debug($offering);
    //            debug($scores);
    $tipo = "text/csv";
    header("Content-Type: " . $tipo);
    header("Content-Disposition: attachment; filename=\"" . __("Grades Table") . " " . $offering['Course']['name'] . ".csv\"");
    $fp = fopen('php://output', 'w');

    $header = array();
    array_push($header, __("Name"));
    array_push($header, utf8_decode($offering['Course']['University']['student_identifier_text']));
    array_push($header, __("Email"));
    array_push($header, __("Role"));
    foreach ($scores as $k => $exercise) {
      array_push($header, utf8_decode($exercise['Exercise']['title']));
    }
    fputcsv($fp, $header);
    foreach ($offering['Enrollment'] as $k => $enroll) {
      $row = array();
      array_push($row, utf8_decode($enroll['User']['name']));
      array_push($row, utf8_decode(strval($enroll['User']['identifier'])));
      array_push($row, utf8_decode($enroll['user_email']));
      array_push($row, utf8_decode($enroll['role_name']));
      foreach ($scores as $k => $ex) {
        array_push($row, (isset($ex['Grade'][$enroll['user_email']])) ? $ex['Grade'][$enroll['user_email']] : 0.0);
      }
      fputcsv($fp, $row);
    }
    fclose($fp);
    die();
  }
}
