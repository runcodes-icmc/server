<?php

App::uses('AppController', 'Controller');
App::uses('Folder', 'Utility');

/**
 * Exercises Controller
 *
 * @property Exercise $Exercise
 * @property PaginatorComponent $Paginator
 */
class ExercisesController extends AppController
{

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

  /**
   * Components
   *
   * @var array
   */
  public $components = array('Paginator');

  private $studentAuthorized = array('view', 'commit', 'downloadcases', 'exportexercisetogooglecalendar');
  private $demoPostNotAuthorized = array('add', 'edit', 'view', 'viewprofessor', 'import', 'casestable', 'commit', 'edit', 'delete', 'removeallcases');
  private $professorAuthorized = array('showuseroutput', 'showexpectedoutput', 'showinput', 'import', 'add', 'view', 'viewprofessor', 'casestable', 'commit', 'edit', 'delete', 'getallscoreszipped', 'participantcommits', 'stats', 'removeallcases');

  public function isAuthorized($user = null)
  {
    $this->loadModel('User');
    $this->loadModel('Enrollment');
    $this->Exercise->recursive = -1;

    if (!Configure::read('Config.allowDemoPostRequest') && $this->currentUser['domain'] == "demo.run.codes" && !$this->request->is("get") && in_array(strtolower($this->request->params['action']), $this->demoPostNotAuthorized)) {
      $this->Session->setFlash(__("Sorry! This action is not allowed in demo mode"));
      $this->redirect('/home');
    }
    if ($user['type'] >= $this->User->getAdminIndex()) {
      return true;
    }
    if (strtolower($this->request->params['action']) == "add" || strtolower($this->request->params['action']) == "import") {
      if ($this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $this->request->params['pass'][0])) {
        return true;
      }
      return false;
    }
    $exercise = $this->Exercise->findById($this->request->params['pass'][0]);
    $offering = $exercise['Exercise']['offering_id'];
    if (in_array(strtolower($this->request->params['action']), $this->professorAuthorized)) {
      if ($this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $offering)) {
        return true;
      }
    }
    if (in_array(strtolower($this->request->params['action']), $this->studentAuthorized)) {
      //            if ($exercise['Exercise']['isOpen'] == false && $exercise['Exercise']['show_before_opening'] != true && $exercise['Exercise']['isFinished'] == false) {
      //                return false;
      //            }
      if ($this->Enrollment->isEnrolled($this->currentUser['email'], $offering)) {
        return true;
      }
    } else {
      return false;
    }
  }

  public function beforeRender()
  {
    parent::beforeRender();
    if (strtolower($this->request->params['action']) != 'add' && strtolower($this->request->params['action']) != 'import' && strtolower($this->request->params['action']) != 'index') {
      $this->loadModel('Enrollment');
      $exercise = $this->Exercise->findById($this->request->params['pass'][0], array('Exercise.offering_id'));
      $offering = $exercise['Exercise']['offering_id'];
      if (isset($this->currentUser['onlyStudent']) && $this->currentUser['onlyStudent']) {
        $assistantOrProfessor =  false;
      } else {
        $assistantOrProfessor = ($this->currentUser['type'] >= $this->User->getAdminIndex()) ? true : $this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $offering);
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
    if ($this->request->is('post')) {
      if (isset($this->request->data['User']['type']) && $this->request->data['User']['type'] == -1) {
        $this->request->data['User']['type'] = null;
      }
      if (isset($this->request->data['Exercise']['university_id']) && $this->request->data['Exercise']['university_id'] == -1) {
        $this->request->data['Exercise']['university_id'] = null;
      }
      $this->redirect(array(
        'controller' => 'Exercises',
        'action' => 'index',
        'university' => $this->request->data['Exercise']['university_id'],
        'type' => $this->request->data['Exercise']['type'],
        'title' => $this->request->data['Exercise']['title']
      ));
    }
    $cond = array();

    if (isset($this->request->params['named']['university'])) {
      $cond[2] = "Exercise.offering_id IN (SELECT id FROM offerings WHERE course_id IN (SELECT id FROM courses WHERE university_id = " . intval($this->request->params['named']['university']) . "))";
    }
    if (isset($this->request->params['named']['title']) && strlen($this->request->params['named']['title']) > 0) {
      $cond['Exercise.title ILIKE'] = '%' . $this->request->params['named']['title'] . '%';
    }
    if (isset($this->request->params['named']['type'])) {
      if ($this->request->params['named']['type'] == 1) {
        //Closed
        $cond[0] = 'Exercise.deadline <= NOW()';
      } elseif ($this->request->params['named']['type'] == 2) {
        //Open
        $cond[0] = 'Exercise.open_date <= NOW()';
        $cond[1] = 'Exercise.deadline > NOW()';
      } elseif ($this->request->params['named']['type'] == 3) {
        //Future
        $cond[0] = 'Exercise.open_date > NOW()';
        $cond[1] = 'Exercise.deadline > NOW()';
      }
    } else {
      $cond[0] = 'Exercise.open_date <= NOW()';
      $cond[1] = 'Exercise.deadline > NOW()';
    }

    $this->loadModel("Offering");
    $this->loadModel("Course");
    $this->loadModel("University");
    $this->loadModel("Enrollment");
    $this->Offering->recursive = -1;
    $this->Course->recursive = -1;
    $this->University->recursive = -1;
    $this->Enrollment->recursive = -1;
    $this->Exercise->recursive = -1;
    $this->Paginator->settings = array('order' => array('Exercise.deadline' => 'ASC'), 'conditions' => $cond, 'fields' => array('id', 'title', 'open_date', 'deadline', 'offering_id', 'removed'));
    $exercises = $this->Paginator->paginate();
    foreach ($exercises as $k => $e) {
      $off = $this->Offering->findById($e['Exercise']['offering_id'], array('id', 'classroom', 'course_id'));
      $prof = $this->Enrollment->findAllByOfferingIdAndRole($off['Offering']['id'], $this->Enrollment->getProfessorRole());
      $off['Offering']['Professor'] = array();
      foreach ($prof as $u) {
        $user = $this->User->findByEmail($u['Enrollment']['user_email'], array('name'));
        $off['Offering']['Professor'][$u['Enrollment']['user_email']] = $user['User']['name'];
      }
      uasort($off['Offering']['Professor'], "strnatcmp");
      $exercises[$k]['Offering'] = $off['Offering'];
      $cou = $this->Course->findById($off['Offering']['course_id'], array('name', 'university_id'));
      $exercises[$k]['Course'] = $cou['Course'];
      $uni = $this->University->findById($cou['Course']['university_id'], array('name'));
      $exercises[$k]['University'] = $uni['University'];
    }

    $this->set('universities', array('-1' => __("All")) + $this->University->find('list', array('order' => array("name" => "ASC"))));
    $this->set('types', array(__("All"), __("Closed"), __("Open"), __("Future")));
    $this->set('exercises', $exercises);
  }

  public function raw($section = 'open', $page = 1)
  {
    $this->autoRender = false;
    $this->Exercise->recursive = -1;
    $this->loadModel('Offering');
    $this->loadModel('Course');
    $this->loadModel('University');
    $this->Offering->recursive = -1;
    $this->Course->recursive = -1;
    $this->University->recursive = -1;
    if ($section == 'open') {
      $cond = array('removed' => false, 'deadline >=' => date('Y-m-d H:i:s'), 'open_date <' => date('Y-m-d H:i:s'));
      $order = array('deadline' => 'ASC');
    } else if ($section == 'closed') {
      $cond = array('removed' => false, 'deadline <' => date('Y-m-d H:i:s'), 'open_date <' => date('Y-m-d H:i:s'));
      $order = array('deadline' => 'DESC');
    } else {
      $cond = array('removed' => false, 'deadline >=' => date('Y-m-d H:i:s'), 'open_date >=' => date('Y-m-d H:i:s'));
      $order = array('deadline' => 'ASC');
    }
    $exercises = $this->Exercise->find('all', array('conditions' => $cond, 'limit' => 20, 'page' => $page, 'order' => $order, 'fields' => array('id', 'title', 'deadline', 'open_date', 'offering_id')));
    foreach ($exercises as $k => $exercise) {
      $offering = $this->Offering->findById($exercise['Exercise']['offering_id'], array('course_id', 'classroom'));
      $course = $this->Course->findById($offering['Offering']['course_id'], array('code', 'name', 'university_id'));
      $university = $this->University->findById($course['Course']['university_id'], array('name'));
      $exercises[$k]['Offering'] = $offering['Offering'];
      $exercises[$k]['Course'] = $course['Course'];
      $exercises[$k]['University'] = $university['University'];
    }
    echo json_encode($exercises);
    exit();
  }

  /**
   * view method
   *
   * @throws NotFoundException
   * @param string $id
   * @return void
   */
  public function view($id = null)
  {
    $this->layout = "template2015";
    $this->Exercise->recursive = -1;
    if (!$this->Exercise->exists($id)) {
      throw new NotFoundException(__('Invalid exercise'));
    }

    $exercise = $this->Exercise->findById($this->request->params['pass'][0], array('Exercise.offering_id'));
    $offering = $exercise['Exercise']['offering_id'];
    if (isset($this->currentUser['onlyStudent']) && $this->currentUser['onlyStudent']) {
      $assistantOrProfessor =  false;
    } else {
      $assistantOrProfessor = ($this->currentUser['type'] >= $this->User->getAdminIndex()) ? true : $this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $offering);
    }
    if ($assistantOrProfessor) {
      $this->redirect(array('controller' => 'Exercises', 'action' => 'viewProfessor', $id));
    }

    $this->loadModel("AwsCache");
    $exercise = $this->AwsCache->getItem("exercise-" . $id);
    if (!$exercise) {
      $exercise = $this->Exercise->getExercise($id);
      $this->AwsCache->saveItem("exercise-" . $id, $exercise);
    }
    $this->Exercise->id = $id;
    $exercise['Exercise']['isOpen'] = $this->Exercise->isOpen($this->now);
    $exercise['Exercise']['isFinished'] = $this->Exercise->isFinished($this->now);
    if (!$exercise['Exercise']['isOpen'] && !$exercise['Exercise']['show_before_opening'] && strtotime($exercise['Exercise']['open_date']) > strtotime(date('Y-m-d H:i:s'))) {
      throw new NotFoundException(__('Invalid exercise'));
    }
    $this->loadModel('Commit');
    $this->Commit->recursive = -1;
    $commits = $this->Commit->find('all', array('fields' => array('commit_time', 'id', 'status', 'corrects', 'score', 'exercise_id', 'compiled_message'), 'conditions' => array('Commit.user_email' => $this->Auth->user('email'), 'Commit.exercise_id' => $id), 'order' => 'commit_time DESC'));
    foreach ($commits as $k => $commit) {
      //            $ex = $this->Exercise->findById($commit['Commit']['exercise_id'],array('id'));
      $commits[$k]['Exercise'] = $exercise['Exercise'];
    }
    //        unset($ex);

    $lastCommit = $this->Commit->findByExerciseIdAndUserEmail($id, $this->Auth->user('email'), array('id', 'commit_time', 'corrects', 'score', 'compiled', 'status', 'compiled_message', 'compiled_signal', 'compiled_error'), array('commit_time' => 'DESC'));
    if (count($lastCommit) > 0) {
      $this->loadModel('CommitsExerciseCase');
      $this->CommitsExerciseCase->recursive = -1;

      $lastCommit['ExerciseCase'] = array();
      foreach ($exercise['ExerciseCase'] as $key => $item) {
        $commitExerciseCase = $this->CommitsExerciseCase->findByExerciseCaseIdAndCommitId($item['ExerciseCase']['id'], $lastCommit["Commit"]["id"], array('id', 'commit_id', 'exercise_case_id', 'cputime', 'memused', 'status', 'status_message'));
        if (count($commitExerciseCase) > 0) {
          $exercise['ExerciseCase'][$key]['ExerciseCase']['CommitsExerciseCase'] = $commitExerciseCase['CommitsExerciseCase'];
          array_push($lastCommit['ExerciseCase'], $exercise['ExerciseCase'][$key]['ExerciseCase']);
        }
      }
    }
    $this->loadModel('ExerciseCase');
    $this->ExerciseCase->recursive = -1;
    $countOpenCases = $this->ExerciseCase->find('count', array('conditions' => array('exercise_id' => $id, 'OR' => array('show_input' => true, 'show_expected_output' => true))));
    $this->set('hasOpenCases', ($countOpenCases > 0));
    $breadcrumbs = array();
    array_push($breadcrumbs, array('link' => array('controller' => 'offerings', 'action' => 'view', $exercise['Exercise']['offering_id']), 'text' => $exercise['Offering']['Course']['code']));
    array_push($breadcrumbs, array('link' => '#', 'text' => $exercise['Exercise']['title']));
    $this->set(compact('exercise', 'commits', 'lastCommit', 'breadcrumbs'));
    $this->set('page', 'exercises');
    //        debug(memory_get_usage());
  }

  public function viewProfessor($id = null)
  {
    $this->layout = "template2015";
    $this->Exercise->recursive = -1;
    if (!$this->Exercise->exists($id)) {
      throw new NotFoundException(__('Invalid exercise'));
    }
    //        $this->Exercise->recursive = 2;
    $this->set('page', 'exercises');
    $options = array('conditions' => array('Exercise.' . $this->Exercise->primaryKey => $id));
    $exercise = $this->Exercise->find('first', $options);
    //        $exercise['ExerciseCase'];
    $this->loadModel('ExerciseCase');
    $this->ExerciseCase->recursive = -1;
    if ($exercise['Exercise']['ghost']) {
      $cases = $this->ExerciseCase->findAllByExerciseId($exercise['Exercise']['real_id'], array('id', 'show_input', 'show_expected_output', 'maxmemsize', 'cputime', 'stacksize', 'show_user_output', 'file_size'), array('id' => 'ASC'));
    } else {
      $cases = $this->ExerciseCase->findAllByExerciseId($exercise['Exercise']['id'], array('id', 'show_input', 'show_expected_output', 'maxmemsize', 'cputime', 'stacksize', 'show_user_output', 'file_size'), array('id' => 'ASC'));
    }
    $exercise['ExerciseCase'] = $cases;
    //        debug($exercise);


    $this->loadModel('Course');
    $this->loadModel('Offering');
    $this->Course->recursive = -1;
    $this->Offering->recursive = -1;
    $offering = $this->Offering->findById($exercise['Exercise']['offering_id']);
    $course = $this->Course->findById($offering['Offering']['course_id']);
    $this->loadModel('University');
    $university = $this->University->findById($course['Course']['university_id'], array('student_identifier_text'));
    $course['University'] = $university['University'];
    $offering['Offering']['Course'] = $course['Course'];
    $exercise['Offering'] = $offering['Offering'];

    $this->Exercise->ExerciseFile->recursive = -1;
    if ($exercise['Exercise']['ghost']) {
      $files = $this->Exercise->ExerciseFile->findAllByExerciseId($exercise['Exercise']['real_id'], array('id', 'path'));
    } else {
      $files = $this->Exercise->ExerciseFile->findAllByExerciseId($exercise['Exercise']['id'], array('id', 'path'));
    }
    $exercise['ExerciseFile'] = $files;
    unset($files);

    $this->Exercise->AllowedFilesExercise->recursive = -1;
    if ($exercise['Exercise']['ghost']) {
      $afiles = $this->Exercise->AllowedFilesExercise->findAllByExerciseId($exercise['Exercise']['real_id']);
    } else {
      $afiles = $this->Exercise->AllowedFilesExercise->findAllByExerciseId($exercise['Exercise']['id']);
    }
    $this->loadModel('AllowedFile');
    foreach ($afiles as $k => $allowed) {
      $afiles[$k] = $this->AllowedFile->findById($allowed['AllowedFilesExercise']['allowed_file_id'], array('name'));
    }
    $exercise['AllowedFile'] = $afiles;
    unset($afiles);

    $this->loadModel('Commit');
    $this->loadModel('User');
    $this->Commit->recursive = -1;
    $scores = $this->Commit->find('all', array('fields' => array('ip', 'commit_time', 'compilation_started', 'user_email', 'status', 'corrects', 'score', 'id'), 'conditions' => array('Commit.exercise_id' => $id, " Commit.commit_time = (SELECT MAX(commit_time) FROM commits c2 WHERE Commit.user_email = c2.user_email AND Commit.exercise_id=c2.exercise_id GROUP BY user_email, exercise_id)"), 'order' => 'score DESC, commit_time ASC'));
    $plus5 = 0;
    $sum = 0;

    $unsafeCommits = false;
    foreach ($scores as $k => $student) {
      $user = $this->User->findByEmail($student['Commit']['user_email'], array('name', 'identifier'));
      $scores[$k]['User'] = $user['User'];
      if ($student['Commit']['score'] > 5) $plus5++;
      $sum += $student['Commit']['score'];
      if (!is_null($exercise['Exercise']['cases_change']) && strtotime($exercise['Exercise']['cases_change']) > strtotime($student['Commit']['compilation_started']) && strtotime($student['Commit']['compilation_started']) > 1) {
        $unsafeCommits = true;
      }
    }
    $total = count($scores);
    if (count($scores) > 0) {
      $plus5pct = number_format($plus5 / $total * 100.0, 1);
      $avg = number_format($sum / $total, 2);
    } else {
      $plus5pct = '0.0';
      $avg = '--';
    }

    uasort($scores, array($this->User, 'compare'));
    unset($user);


    $breadcrumbs = array();
    array_push($breadcrumbs, array('link' => array('controller' => 'offerings', 'action' => 'view', $exercise['Exercise']['offering_id']), 'text' => $course['Course']['code']));
    array_push($breadcrumbs, array('link' => '#', 'text' => $exercise['Exercise']['title']));
    $this->set(compact('scores', 'course', 'exercise', 'breadcrumbs', 'plus5pct', 'avg', 'unsafeCommits'));
  }

  public function casesTable($exercise_id)
  {
    $this->layout = "ajax";
    if (!$this->request->is('post')) {
      $this->redirect('/home');
    }
    if (!$this->Exercise->exists($exercise_id)) {
      throw new NotFoundException(__('Invalid exercise'));
    }
    $this->Exercise->id = $exercise_id;

    $this->loadModel('Course');
    $this->loadModel('Offering');
    $this->Course->recursive = -1;
    $this->Offering->recursive = -1;
    $offering = $this->Offering->findById($this->Exercise->field('offering_id'));
    $course = $this->Course->findById($offering['Offering']['course_id']);
    $this->loadModel('University');
    $university = $this->University->findById($course['Course']['university_id'], array('student_identifier_text'));
    $course['University'] = $university['University'];
    $offering['Offering']['Course'] = $course['Course'];

    $this->loadModel("ExerciseCase");
    $this->loadModel("CommitsExerciseCase");
    $this->ExerciseCase->recursive = -1;
    $this->CommitsExerciseCase->recursive = -1;
    if (!$this->Exercise->field("ghost")) {
      $cases = $this->ExerciseCase->findAllByExerciseId($exercise_id, array("id"), array("ExerciseCase.id"));
    } else {
      $cases = $this->ExerciseCase->findAllByExerciseId($this->Exercise->field("real_id"), array("id"), array("ExerciseCase.id"));
    }
    $casesList = array();
    foreach ($cases as $c) array_push($casesList, $c['ExerciseCase']['id']);
    if (count($casesList) <= 25) {
      $this->loadModel('Commit');
      $this->loadModel('User');
      $this->Commit->recursive = -1;
      $scores = $this->Commit->find('all', array('fields' => array('user_email', 'score', 'id'), 'conditions' => array('Commit.exercise_id' => $exercise_id, " Commit.commit_time = (SELECT MAX(commit_time) FROM commits c2 WHERE Commit.user_email = c2.user_email AND Commit.exercise_id=c2.exercise_id GROUP BY user_email, exercise_id)"), 'order' => 'score DESC, commit_time ASC'));
      foreach ($scores as $k => $student) {
        $user = $this->User->findByEmail($student['Commit']['user_email'], array('name', 'identifier'));
        $scores[$k]['User'] = $user['User'];
      }
      uasort($scores, array($this->User, 'compare'));
      unset($user);

      foreach ($scores as $k => $student) {
        $commExerCases = $this->CommitsExerciseCase->findAllByCommitId($student['Commit']['id'], array('id', 'status', 'status_message', 'exercise_case_id'));
        foreach ($commExerCases as $commitExerciseCase) {
          if (in_array(intval($commitExerciseCase['CommitsExerciseCase']['exercise_case_id']), $casesList)) {
            $scores[$k]['CommitsExerciseCase'][$commitExerciseCase['CommitsExerciseCase']['exercise_case_id'] . ''] = $commitExerciseCase['CommitsExerciseCase']['status'];
            $scores[$k]['CommitsExerciseCaseMessage'][$commitExerciseCase['CommitsExerciseCase']['exercise_case_id'] . ''] = $commitExerciseCase['CommitsExerciseCase']['status_message'];
          }
        }
      }
    }
    $this->set(compact('casesList', 'scores', 'course'));
  }

  public function stats($exercise_id)
  {
    if (!$this->request->is('post')) {
      $this->redirect('/home');
    }
    $this->autoRender = false;
    if (!$this->Exercise->exists($exercise_id)) {
      throw new NotFoundException(__('Invalid exercise'));
    }
    $this->Exercise->id = $exercise_id;
    $chart = new stdClass();
    $chart->colors = array('#c0392b', '#1E88BD');
    $chart->labelsX = array();
    $chart->series[0] = new stdClass();
    $chart->series[0]->name = __("Case Corrects Percentage");
    $chart->series[0]->data = array();
    $this->loadModel('Commit');
    $this->loadModel('CommitsExerciseCase');
    $this->loadModel('ExerciseCase');
    $this->ExerciseCase->recursive = -1;
    if ($this->Exercise->field('ghost')) {
      $cases = $this->ExerciseCase->findAllByExerciseId($this->Exercise->field('real_id'), array('id'), array('id' => 'ASC'));
    } else {
      $cases = $this->ExerciseCase->findAllByExerciseId($exercise_id, array('id'), array('id' => 'ASC'));
    }
    $i = 0;
    $casesCorrect = array();
    $casesIdentifier = array();
    foreach ($cases as $case) {
      $caseNum = ++$i;
      array_push($chart->labelsX, array($caseNum, __("Case") . " " . $caseNum));
      $casesCorrect[$case['ExerciseCase']['id']] = 0;
      $casesIdentifier[$case['ExerciseCase']['id']] = $caseNum;
    }

    $this->Commit->recursive = -1;
    $this->CommitsExerciseCase->recursive = -1;
    $scores = $this->Commit->find('all', array('fields' => array('corrects', 'score', 'id'), 'conditions' => array('Commit.exercise_id' => $exercise_id, " Commit.commit_time = (SELECT MAX(commit_time) FROM commits c2 WHERE Commit.user_email = c2.user_email AND Commit.exercise_id=c2.exercise_id GROUP BY user_email, exercise_id)"), 'order' => 'score DESC, commit_time ASC'));

    foreach ($scores as $score) {
      $commitCases = $this->CommitsExerciseCase->findAllByCommitId($score['Commit']['id'], array('status', 'exercise_case_id'));
      foreach ($commitCases as $commitCase) {
        if ($commitCase['CommitsExerciseCase']['status']) {
          $casesCorrect[$commitCase['CommitsExerciseCase']['exercise_case_id']]++;
        }
      }
    }
    $total = count($scores);

    foreach ($casesCorrect as $k => $corrects) {
      $pct = ($total > 0) ? $corrects / $total * 100.0 : 0.0;
      array_push($chart->series[0]->data, array($casesIdentifier[$k], $pct));
    }
    echo json_encode($chart);
  }

  public function participantCommits($exercise_id, $user_email)
  {
    if (!$this->request->is('post')) {
      $this->redirect('/home');
    }
    $this->layout = 'ajax';
    $this->loadModel('Commit');
    $this->loadModel('User');
    $this->Commit->recursive = -1;
    $commits = $this->Commit->find('all', array('fields' => array('commit_time', 'user_email', 'status', 'corrects', 'score', 'id'), 'conditions' => array('Commit.exercise_id' => $exercise_id, 'Commit.user_email' => $user_email), 'order' => 'commit_time DESC'));
    foreach ($commits as $k => $student) {
      $user = $this->User->findByEmail($student['Commit']['user_email'], array('name', 'identifier'));
      $commits[$k]['User'] = $user['User'];
    }
    $this->set(compact('commits', 'course'));
  }

  public function commit($id = null)
  {
    ob_start();
    if (!$this->Exercise->exists($id)) {
      throw new NotFoundException(__('Invalid exercise'));
    }
    $this->setUploadErrorMessages();
    $this->loadModel("Archive");
    $this->loadModel('Commit');
    $this->loadModel('Offering');
    $this->loadModel('AllowedFile');
    $this->layout = 'ajax';
    $this->autoRender = false;

    $this->response->type('json');

    //Verificar se tem oferecimento
    //se exercicio existe e esta aberto
    //tipo de arquivo
    $this->Exercise->recursive = -1;
    $exercise = $this->Exercise->find('first', array('conditions' => array('Exercise.' . $this->Exercise->primaryKey => $id)));
    $this->Exercise->AllowedFilesExercise->recursive = -1;
    if ($exercise['Exercise']['ghost']) {
      $afiles = $this->Exercise->AllowedFilesExercise->findAllByExerciseId($exercise['Exercise']['real_id']);
    } else {
      $afiles = $this->Exercise->AllowedFilesExercise->findAllByExerciseId($exercise['Exercise']['id']);
    }
    $this->loadModel('AllowedFile');
    foreach ($afiles as $k => $allowed) {
      $afiles[$k] = $this->AllowedFile->findById($allowed['AllowedFilesExercise']['allowed_file_id'], array('name', 'compilable', 'extension'));
    }
    $exercise['AllowedFile'] = $afiles;
    unset($afiles);

    $isCompilable = false;
    if (in_array($exercise['Exercise']['type'], $this->Exercise->getCompilableIndex())) {
      $isCompilable = true;
    }
    $allowed = array();
    foreach ($exercise['AllowedFile'] as $filetype) {
      array_push($allowed, str_replace('*.', '', $filetype['AllowedFile']['extension']));
    }
    //        $allowed = implode('|', $allowed);
    $offering = $this->Offering->find('first', array('conditions' => array('Offering.id' => $exercise['Exercise']['offering_id'])));
    $this->loadModel("Enrollment");

    if (!$exercise['Exercise']['isOpen'] && !$this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $exercise['Exercise']['offering_id'])) {
      $this->Session->setFlash(__('The selected exercise is closed to new commits'), 'default', array(), 'flash');
      Log::register("Tried to commit in the exercise #" . $id . ', but the exercise is closed', $this->currentUser);
      $data = array('type' => 'error', 'message' => __('The selected exercise is closed to new commits'), 'file' => $_POST['files']);
      echo json_encode($data);
      die();
    } else {
      if (!$exercise['Exercise']['isOpen']) {
        Log::register("Professor or assistant committed in the closed exercise #" . $id, $this->currentUser);
      }
      $commit = array();
      $commit['Commit']['ip'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
      $commit['Commit']['user_email'] = $this->Auth->user('email');
      $commit['Commit']['exercise_id'] = $id;
      $commit['Commit']['status'] = $this->Commit->getDefaultStatusValue();
      $this->Commit->begin();
      $this->Commit->create();
      $this->Commit->save($commit, false);
      $commit_data = $this->Commit->findById($this->Commit->id);
      $time = $commit_data['Commit']['commit_time'];
      $this->Commit->id = $commit_data['Commit']['id'];

      //$upload,$exerciseId,$commitId,$meta = array(),$allowed_types = null,$max_size_limit = 2000000
      $upload = isset($_FILES["files"]) ? $_FILES["files"] : null;
      $max_size_limit = $isCompilable ? 1000000 : 5000000;
      $result = $this->Archive->handleCommitFileUpload($upload, $commit_data['Commit']['exercise_id'], $commit_data['Commit']['id'], array(), $allowed, $max_size_limit);

      if (!isset($result['files'][0]->error)) {
        $this->Commit->saveField("aws_key", $result['files'][0]->aws);
        $this->Commit->saveField('hash', $result['files'][0]->hash_time);
        if ($isCompilable) {
          $this->Commit->saveField('status', $this->Commit->getInQueueStatusValue());
        } else {
          $this->Commit->saveField('status', $this->Commit->getNonCompilableDefaultStatusValue());
        }
        Log::register("Committed in the exercise #" . $commit['Commit']['exercise_id'], $this->Auth->user());
        $this->Commit->commit();
      } else {
        $this->Commit->rollback();
        Log::register("Error on commit in the exercise #" . $commit['Commit']['exercise_id'] . " (" . $result['files'][0]->error . ")", $this->Auth->user());
      }

      echo json_encode($result);
    }
    return false;
  }

  private function setUploadErrorMessages()
  {
    $this->error_messages[1] = __('The uploaded file exceeds the upload_max_filesize directive in php.ini');
    $this->error_messages[2] = __('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form');
    $this->error_messages[3] = __('The uploaded file was only partially uploaded');
    $this->error_messages[4] = __('No file was uploaded');
    $this->error_messages[6] = __('Missing a temporary folder');
    $this->error_messages[7] = __('Failed to write file to disk');
    $this->error_messages[8] = __('A PHP extension stopped the file upload');
    $this->error_messages['accept_file_types'] = __('The uploaded file have a filetype not allowed to this exercise');
  }

  public function copy($exercise_id)
  {
    $this->Exercise->copy($exercise_id, 37, '2015-07-17 00:00:00', '2015-08-17 23:00:00', 'fabio.sikansi@gmail.com', true);
    die();
  }

  public function ghost($exercise_id)
  {
    debug($this->Exercise->createGhostExercise($exercise_id, 37, $this->currentUser['email']));
    die();
  }

  /**
   * add method
   *
   * @return void
   */
  public function add($id_offering = null)
  {
    $this->layout = "template2015";
    $this->loadModel('Archive');
    $this->loadModel('Offering');
    $this->loadModel('AllowedFile');
    $this->Exercise->recursive = -1;
    if (!$this->Offering->exists($id_offering)) {
      throw new NotFoundException(__('The selected course offering does not exists'));
    }

    if ($this->request->is('post')) {
      $this->loadModel('AllowedFilesExercise');
      $this->Exercise->create();
      $this->request->data['Exercise']['user_email'] = $this->Auth->user('email');
      $this->request->data['Exercise']['open_date'] = DateTime::createFromFormat('d/m/Y H:i:s', $this->request->data['Exercise']['open_date'])->format('Y-m-d H:i:s');
      $this->request->data['Exercise']['deadline'] = DateTime::createFromFormat('d/m/Y H:i:s', $this->request->data['Exercise']['deadline'])->format('Y-m-d H:i:s');
      if (intval($this->request->data['Exercise']['type']) == 2) {
        $this->request->data['AllowedFile'] = array('9');
      }

      if ($this->Exercise->saveAssociated($this->request->data, array('deep' => true))) {
        $newExerciseId = $this->Exercise->id;
        $this->Session->setFlash(__('The exercise has been saved'), 'default', array(), 'success');
        if (isset($this->request->data['ExerciseFile'])) {
          $options = array('conditions' => array('Exercise.' . $this->Exercise->primaryKey => $this->Exercise->id));
          $exercise = $this->Exercise->find('first', $options);
          //                    $this->Offering->id = $exercise['Exercise']['offering_id'];
          //                    $courseId = $this->Offering->getCourseId();
          //                    $path = Configure::read('Upload.dir')."/exercisefiles/".$courseId."/".$exercise['Exercise']['offering_id']."/".$exercise['Exercise']['id']."/";
          //                    $upload_dir = new Folder($path, true, 0777);
          $files = $this->request->data['ExerciseFile'];
          foreach ($files as $file) {
            if ($file['hash'] != "0") {
              $tmpKey = "/tmp/exercisefiles/" . $file['hash'] . "/" . $file['path'];
              $targetKey = "/exercisefiles/" . $exercise['Exercise']['id'] . "/" . $file['path'];
              if (!$this->Archive->copyAwsFiles($tmpKey, $targetKey)) {
                $this->Session->setFlash(__('The exercise file was not moved'));
              }
            }
            //                        DEPRECATED NO FUTURO
            //                        if ($file['hash'] != "0") {
            //                            $updir = Configure::read('Upload.dir').'/exercisefiles/tmp/'.$file['hash'].'/';
            //                            if (is_file($updir.$file['path'])) {
            //                                if (!copy($updir.$file['path'],$path.$file['path'])) {
            //                                    $this->Session->setFlash(__('The exercise file was not moved'));
            //                                } else {
            //                                    unlink($updir.$file['path']);
            //                                }
            //                            }
            //                        }
          }
        }
        if (isset($this->request->data['CompilationFile'])) {
          $options = array('conditions' => array('Exercise.' . $this->Exercise->primaryKey => $this->Exercise->id));
          $exercise = $this->Exercise->find('first', $options);
          //                    $this->Offering->id = $exercise['Exercise']['offering_id'];
          //                    $courseId = $this->Offering->getCourseId();
          //                    $path = Configure::read('Upload.dir')."/compilationfiles/".$courseId."/".$exercise['Exercise']['offering_id']."/".$exercise['Exercise']['id']."/";
          //                    $upload_dir = new Folder($path, true, 0777);
          $files = $this->request->data['CompilationFile'];
          foreach ($files as $file) {
            if ($file['hash'] != "0") {
              $tmpKey = "/tmp/compilationfiles/" . $file['hash'] . "/" . $file['path'];
              $targetKey = "/compilationfiles/" . $exercise['Exercise']['id'] . "/" . $file['path'];
              if (!$this->Archive->copyAwsFiles($tmpKey, $targetKey)) {
                $this->Session->setFlash(__('The exercise file was not moved'));
              }
            }
            //DEPRECATED NO FUTURO
            //                        if ($file['hash'] != "0") {
            //                            $updir = Configure::read('Upload.dir').'/compilationfiles/tmp/'.$file['hash'].'/';
            //                            if (file_exists($updir.$file['path'])) {
            //                                if (!copy($updir.$file['path'],$path.$file['path'])) {
            //                                    $this->Session->setFlash(__('The exercise file was not moved'));
            //                                } else {
            //                                    unlink($updir.$file['path']);
            //                                }
            //                            }
            //                        }
          }
        }
        Log::register("Added an Exercise to the Offering #" . $id_offering, $this->currentUser);
        if (isset($this->request->data['share'])) {
          foreach ($this->request->data['share'] as $off => $share) {
            if (boolval($share)) {
              if ($this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $off)) {
                $this->Exercise->createGhostExercise($newExerciseId, $off, $this->currentUser['email']);
                Log::register("Shared the Exercise " . $newExerciseId . " to the Offering #" . $off, $this->currentUser);
              }
            }
          }
        }

        return $this->redirect(array('action' => 'viewProfessor', $newExerciseId));
      } else {
        $this->Session->setFlash(__('The exercise could not be saved. Please, try again.'));
      }
    }
    if (isset($this->request->data['Exercise']['deadline']))
      $this->request->data['Exercise']['deadline'] = DateTime::createFromFormat('Y-m-d H:i:s', $this->request->data['Exercise']['deadline'])->format('d/m/Y H:i:s');
    if (isset($this->request->data['Exercise']['open_date']))
      $this->request->data['Exercise']['open_date'] = DateTime::createFromFormat('Y-m-d H:i:s', $this->request->data['Exercise']['open_date'])->format('d/m/Y H:i:s');

    $offering = $this->Offering->find('first', array('conditions' => array('Offering.id' => $id_offering)));
    $allowedTypes = $this->AllowedFile->find('list');
    //---------------------------------------------
    $this->loadModel('Enrollment');
    $this->loadModel('Course');
    $this->Course->recursive = 1;
    $this->Enrollment->recursive = 1;
    $others_offerings = $this->Enrollment->find('all', array('fields' => array('Enrollment.offering_id', 'Offering.classroom', 'Offering.course_id'), 'conditions' => array('Offering.end_date >' => date('Y-m-d H:i:s'), 'role >' => 0, 'offering_id <>' => $id_offering, 'user_email' => $this->currentUser['email'])));
    $others_offerings_list = array();
    foreach ($others_offerings as $koo => $oo) {
      $course = $this->Course->findById($oo['Offering']['course_id'], array('name'));
      $others_offerings[$koo]['Course'] = $course['Course'];
      $others_offerings_list[$oo['Enrollment']['offering_id']] =  $course['Course']['name'] . " (" . $oo['Offering']['classroom'] . ")";
    }
    //---------------------------------------------
    if (isset($this->request->params["named"]["markdown"])) {
      if ($this->request->params["named"]["markdown"] == "0") {
        $this->request->data["Exercise"]["markdown"] = "0";
      } else {
        $this->request->data["Exercise"]["markdown"] = "1";
      }
    }

    $breadcrumbs = array();
    array_push($breadcrumbs, array('link' => array('controller' => 'offerings', 'action' => 'view', $offering['Offering']['id']), 'text' => $offering['Course']['code']));
    array_push($breadcrumbs, array('link' => '#', 'text' => __("New Exercise")));
    $types = $this->Exercise->getTypesList();
    $this->set(compact('offering', 'allowedTypes', 'breadcrumbs', 'types', 'others_offerings_list'));
    $this->render("form");
  }

  public function import($offering_id = null, $public_exercise_id = null)
  {
    $this->layout = "template2015";
    $this->loadModel('Course');
    $this->loadModel('Offering');
    $this->loadModel('Enrollment');
    $this->loadModel('AllowedFile');
    $this->loadModel('PublicExercise');
    $this->Course->recursive = -1;
    $this->Exercise->recursive = -1;
    if (!$this->Offering->exists($offering_id)) {
      throw new NotFoundException(__('The selected course offering does not exists'));
    }
    $publicExercise = false;
    if (!is_null($public_exercise_id) && $this->PublicExercise->exists($public_exercise_id)) {
      $publicExercise = $this->PublicExercise->findById($public_exercise_id);
      $ex = $this->Exercise->findById($publicExercise['PublicExercise']['exercise_id'], array("title", "description", "markdown", "public"));
      $this->Exercise->id = $publicExercise['PublicExercise']['exercise_id'];
      $ex['Exercise']['num_cases'] = $this->Exercise->getNumberOfCases();
      $publicExercise['Exercise'] = $ex['Exercise'];
      $publicExercise['PublicExercise']['level_name'] = $this->PublicExercise->getLevelByIndex($publicExercise['PublicExercise']['level']);
    }
    if ($this->request->is('post')) {
      $this->Exercise->create();
      $this->request->data['Exercise']['user_email'] = $this->Auth->user('email');
      $this->request->data['Exercise']['open_date'] = DateTime::createFromFormat('d/m/Y H:i:s', $this->request->data['Exercise']['open_date'])->format('Y-m-d H:i:s');
      $this->request->data['Exercise']['deadline'] = DateTime::createFromFormat('d/m/Y H:i:s', $this->request->data['Exercise']['deadline'])->format('Y-m-d H:i:s');

      $err = false;
      $originalExercise = $this->Exercise->findById(intval($this->request->data['Exercise']['exercise_id']), array('public', 'offering_id'));
      if (!isset($this->request->data['Exercise']['AllowedFile'])) $this->request->data['Exercise']['AllowedFile'] = array();
      $this->Exercise->set($this->request->data);
      if (!$this->Exercise->validates(array('fieldList' => array('open_date', 'deadline', 'AllowedFile')))) {
        $err = true;
      }
      if (count($originalExercise) == 0 || (!boolval($originalExercise['Exercise']['public']) && $this->currentUser['type'] < 3 && !$this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $originalExercise['Exercise']['offering_id']))) {
        //Invalid Exercise
        $this->Exercise->validationErrors['exercise_id'] = array(__('Invalid exercise'));
        $this->Session->setFlash(__('Invalid exercise'), 'default', array(), 'error');
        $err = true;
      }

      if (!$err) {
        $allowedFiles = array();
        foreach ($this->request->data['Exercise']['AllowedFile'] as $aF) {
          array_push($allowedFiles, array('AllowedFilesExercise' => array('allowed_file_id' => $aF)));
        }
        if (count($allowedFiles) == 0) $allowedFiles = null;
        if ($this->Exercise->copy($this->request->data['Exercise']['exercise_id'], $offering_id, $this->request->data['Exercise']['open_date'], $this->request->data['Exercise']['deadline'], $this->request->data['Exercise']['user_email'], $this->request->data['Exercise']['show_before_opening'], $allowedFiles)) {
          $this->Session->setFlash(__('The exercise has been saved'), 'default', array(), 'success');
          $this->redirect(array('controller' => 'Offerings', 'action' => 'view', $offering_id));
        } else {
          $this->Session->setFlash(__('The exercise could not be saved. Please, try again.'));
        }
      }
    }

    $offering = $this->Offering->find('first', array('conditions' => array('Offering.id' => $offering_id)));

    $this->Offering->recursive = -1;
    $this->Enrollment->recursive = -1;
    $enrolls = $this->Enrollment->find('list', array('conditions' => array('user_email' => $this->currentUser['email'], 'role >=' => 1), 'fields' => array('offering_id')));
    $list = array();
    foreach ($enrolls as $en) {
      $off = $this->Offering->findById($en, array('classroom', 'course_id'));
      $course = $this->Course->findById($off['Offering']['course_id'], array('name'));
      $list[$en] = $course['Course']['name'] . " (" . $off['Offering']['classroom'] . ")";
    }
    $this->set('offeringsList', $list);
    //---------------------------------------------
    $breadcrumbs = array();
    array_push($breadcrumbs, array('link' => array('controller' => 'offerings', 'action' => 'view', $offering['Offering']['id']), 'text' => $offering['Course']['code']));
    array_push($breadcrumbs, array('link' => '#', 'text' => __("Import Exercise")));
    $types = $this->Exercise->getTypesList();
    $this->set(compact('offering', 'allowedTypes', 'breadcrumbs', 'types', 'others_offerings_list', 'publicExercise'));
  }

  public function edit($id = null)
  {
    $this->layout = "template2015";
    $this->Exercise->recursive = -1;
    if (!$this->Exercise->exists($id)) {
      throw new NotFoundException(__('Invalid exercise'));
    }
    $options = array('conditions' => array('Exercise.' . $this->Exercise->primaryKey => $id));
    $exercise = $this->Exercise->find('first', $options);
    if ($exercise['Exercise']['ghost']) {
      return $this->redirect(array('controller' => 'Exercises', 'action' => 'edit', $exercise['Exercise']['real_id']));
    }

    if ($this->request->is('post') || $this->request->is('put')) {
      //            debug($this->request->data);
      //            die();
      $this->loadModel("Archive");
      $this->loadModel("ExerciseFile");
      $this->loadModel("CompilationFile");

      if (isset($this->request->data['RemoveExerciseFile'])) {
        foreach ($this->request->data['RemoveExerciseFile'] as $idf => $remove) {
          if (filter_var($remove, FILTER_VALIDATE_BOOLEAN)) {
            $this->ExerciseFile->id = $idf;
            $this->ExerciseFile->removeFile();
          }
        }
        unset($this->request->data['RemoveExerciseFile']);
      }
      if (isset($this->request->data['RemoveCompilationFile'])) {
        foreach ($this->request->data['RemoveCompilationFile'] as $idf => $remove) {
          if (filter_var($remove, FILTER_VALIDATE_BOOLEAN)) {
            $this->CompilationFile->id = $idf;
            $this->CompilationFile->removeFile();
          }
        }
        unset($this->request->data['RemoveCompilationFile']);
      }
      $this->loadModel('AllowedFilesExercise');
      if ($this->request->data['Exercise']['type'] == 2) {
        $this->request->data['AllowedFile'] = array(9);
      }
      $this->request->data['Exercise']['user_email'] = $this->Auth->user('email');
      $this->request->data['Exercise']['open_date'] = DateTime::createFromFormat('d/m/Y H:i:s', $this->request->data['Exercise']['open_date'])->format('Y-m-d H:i:s');
      $this->request->data['Exercise']['deadline'] = DateTime::createFromFormat('d/m/Y H:i:s', $this->request->data['Exercise']['deadline'])->format('Y-m-d H:i:s');
      if ($this->Exercise->saveAssociated($this->request->data, array('deep' => true))) {
        $this->Session->setFlash(__('The exercise has been saved'), 'default', array(), 'success');
        $newExerciseId = $id;
        if (isset($this->request->data['share'])) {
          foreach ($this->request->data['share'] as $off => $share) {
            if (boolval($share)) {
              if ($this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $off)) {
                $this->Exercise->createGhostExercise($newExerciseId, $off, $this->currentUser['email']);
                Log::register("Shared the Exercise " . $newExerciseId . " to the Offering #" . $off, $this->currentUser);
              }
            }
          }
        }
        if (isset($this->request->data['ExerciseFile'])) {
          $options = array('conditions' => array('Exercise.' . $this->Exercise->primaryKey => $id));
          $exercise = $this->Exercise->find('first', $options);
          //                    $this->loadModel('Offering');
          //                    $this->Offering->id = $exercise['Exercise']['offering_id'];
          //                    $courseId = $this->Offering->getCourseId();
          //                    $path = Configure::read('Upload.dir')."/exercisefiles/".$courseId."/".$exercise['Exercise']['offering_id']."/".$exercise['Exercise']['id']."/";
          //                    $upload_dir = new Folder($path, true, 0777);
          $files = $this->request->data['ExerciseFile'];
          foreach ($files as $file) {
            if ($file['hash'] != "0") {
              $tmpKey = "/tmp/exercisefiles/" . $file['hash'] . "/" . $file['path'];
              $targetKey = "/exercisefiles/" . $exercise['Exercise']['id'] . "/" . $file['path'];
              if (!$this->Archive->copyAwsFiles($tmpKey, $targetKey)) {
                $this->Session->setFlash(__('The exercise file was not moved'));
              }
            }
            //DEPRECATED NO FUTURO
            //                        if ($file['hash'] != "0") {
            //                            $updir = Configure::read('Upload.dir').'/exercisefiles/tmp/'.$file['hash'].'/';
            //                            if (file_exists($updir.$file['path'])) {
            //                                if (!copy($updir.$file['path'],$path.$file['path'])) {
            //                                    $this->Session->setFlash(__('The exercise file was not moved'),'default',array(), 'error');
            //                                    Log::register("Error on move exercise files Exercise #".$id, $this->currentUser);
            //                                } else {
            //                                    unlink($updir.$file['path']);
            //                                }
            //                            }
            //                        }
            //
          }
        }
        if (isset($this->request->data['CompilationFile'])) {
          $options = array('conditions' => array('Exercise.' . $this->Exercise->primaryKey => $id));
          $exercise = $this->Exercise->find('first', $options);
          //                    $this->loadModel('Offering');
          //                    $this->Offering->id = $exercise['Exercise']['offering_id'];
          //                    $courseId = $this->Offering->getCourseId();
          //                    $path = Configure::read('Upload.dir')."/compilationfiles/".$courseId."/".$exercise['Exercise']['offering_id']."/".$exercise['Exercise']['id']."/";
          //                    $upload_dir = new Folder($path, true, 0777);
          $files = $this->request->data['CompilationFile'];
          foreach ($files as $file) {
            if ($file['hash'] != "0") {
              $tmpKey = "/tmp/compilationfiles/" . $file['hash'] . "/" . $file['path'];
              $targetKey = "/compilationfiles/" . $exercise['Exercise']['id'] . "/" . $file['path'];
              if (!$this->Archive->copyAwsFiles($tmpKey, $targetKey)) {
                $this->Session->setFlash(__('The exercise file was not moved'));
              }
            }
            //DEPRECATED NO FUTURO
            //                        if ($file['hash'] != "0") {
            //                            $updir = Configure::read('Upload.dir').'/compilationfiles/tmp/'.$file['hash'].'/';
            //                            if (file_exists($updir.$file['path'])) {
            //                                if (!copy($updir.$file['path'],$path.$file['path'])) {
            //                                    $this->Session->setFlash(__('The compilation file was not moved'),'default',array(), 'error');
            //                                    Log::register("Error on move exercise compilation files Exercise #".$id, $this->currentUser);
            //                                } else {
            //                                    unlink($updir.$file['path']);
            //                                }
            //                            }
            //                        }
            //
          }
        }
        Log::register("Edited the Exercise #" . $id, $this->currentUser);
        return $this->redirect(array('action' => 'viewProfessor', $this->Exercise->id));
      } else {
        $this->Session->setFlash(__('The exercise could not be saved. Please, try again.'));
      }
    } else {
      $options = array('conditions' => array('Exercise.' . $this->Exercise->primaryKey => $id));
      $this->Exercise->recursive = -1;
      $exercise = $this->Exercise->find('first', $options);
      $this->Exercise->AllowedFilesExercise->recursive = -1;
      $afiles = $this->Exercise->AllowedFilesExercise->findAllByExerciseId($exercise['Exercise']['id']);
      $this->loadModel('AllowedFile');
      foreach ($afiles as $k => $allowed) {
        $afiles[$k] = $this->AllowedFile->findById($allowed['AllowedFilesExercise']['allowed_file_id'], array('name', 'id'));
        $afiles[$k] = $afiles[$k]['AllowedFile'];
      }
      $exercise['AllowedFile'] = $afiles;
      unset($afiles);
      $this->request->data = $exercise;
    }
    //		$offerings = $this->Exercise->Offering->find('list');

    $this->loadModel('Offering');
    $this->loadModel('AllowedFile');
    $allowedTypes = $this->AllowedFile->find('list', array('conditions' => array('compilable' => ($this->request->data['Exercise']['type'] == 0) ? true : false)));
    $offering = $this->Offering->find('first', array('conditions' => array('Offering.id' => $exercise['Exercise']['offering_id'])));

    $this->loadModel('ExerciseFile');
    $this->ExerciseFile->recursive = -1;
    $exerciseFiles = $this->ExerciseFile->findAllByExerciseId($id);
    $this->request->data['ExerciseFile'] = $exerciseFiles;

    $this->loadModel('CompilationFile');
    $this->CompilationFile->recursive = -1;
    $compilationFiles = $this->CompilationFile->findAllByExerciseId($id);
    $this->request->data['CompilationFile'] = $compilationFiles;
    //---------------------------------------------
    $this->loadModel('Enrollment');
    $this->loadModel('Course');
    $this->Course->recursive = 1;
    $this->Enrollment->recursive = 1;
    $others_offerings = $this->Enrollment->find('all', array('fields' => array('Enrollment.offering_id', 'Offering.classroom', 'Offering.course_id'), 'conditions' => array('Offering.end_date >' => date('Y-m-d H:i:s'), 'role >' => 0, 'offering_id <>' => $exercise['Exercise']['offering_id'], 'user_email' => $this->currentUser['email'])));
    $ghost_exercises = $this->Exercise->find('list', array('conditions' => array('ghost' => true, 'real_id' => $id, 'removed' => false), 'fields' => array('offering_id')));
    $others_offerings_list = array();
    $others_offerings_ghost = array($offering['Offering']['id'] => $offering['Course']['name'] . " (" . $offering['Offering']['classroom'] . ")");
    foreach ($others_offerings as $koo => $oo) {
      $course = $this->Course->findById($oo['Offering']['course_id'], array('name'));
      $others_offerings[$koo]['Course'] = $course['Course'];
      if (in_array(intval($oo['Enrollment']['offering_id']), $ghost_exercises)) {
        $others_offerings_ghost[$oo['Enrollment']['offering_id']] =  $course['Course']['name'] . " (" . $oo['Offering']['classroom'] . ")";
      } else {
        $others_offerings_list[$oo['Enrollment']['offering_id']] =  $course['Course']['name'] . " (" . $oo['Offering']['classroom'] . ")";
      }
    }
    //---------------------------------------------
    if (isset($this->request->params["named"]["markdown"])) {
      if ($this->request->params["named"]["markdown"] == "0") {
        $this->request->data["Exercise"]["markdown"] = "0";
      } else {
        $this->request->data["Exercise"]["markdown"] = "1";
      }
    }

    $breadcrumbs = array();
    array_push($breadcrumbs, array('link' => array('controller' => 'offerings', 'action' => 'view', $offering['Offering']['id']), 'text' => $offering['Course']['code']));
    array_push($breadcrumbs, array('link' => array('controller' => 'exercises', 'action' => 'view', $exercise['Exercise']['id']), 'text' => $exercise['Exercise']['title']));
    array_push($breadcrumbs, array('link' => '#', 'text' => __("Edit Exercise")));
    $types = $this->Exercise->getTypesList();

    $this->request->data['Exercise']['open_date'] = DateTime::createFromFormat('Y-m-d H:i:s', $this->request->data['Exercise']['open_date'])->format('d/m/Y H:i:s');
    $this->request->data['Exercise']['deadline'] = DateTime::createFromFormat('Y-m-d H:i:s', $this->request->data['Exercise']['deadline'])->format('d/m/Y H:i:s');
    //        $this->request->data['Exercise']['description'] = addslashes($this->request->data['Exercise']['description']);
    $this->set(compact('offering', 'allowedTypes', 'breadcrumbs', 'types', 'others_offerings_ghost', 'others_offerings_list'));
    $this->set('isEdit', true);
    $this->render("form");
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
    $this->Exercise->id = $id;
    if (!$this->Exercise->exists()) {
      throw new NotFoundException(__('Invalid exercise'));
    }
    $this->request->onlyAllow('post', 'delete');
    $exercise = $this->Exercise->findById($id);
    if ($exercise['Exercise']['ghost']) {
      $this->Exercise->id = $exercise['Exercise']['real_id'];
      $id = $exercise['Exercise']['real_id'];
    }
    if ($this->Exercise->saveField('removed', true)) {
      $this->Exercise->updateAll(array('removed' => true), array('real_id' => $id));
      Log::register("Removed the Exercise #" . $id, $this->currentUser);
      $this->Session->setFlash(__('Exercise deleted'), 'default', array(), 'success');
      $this->redirect(array('controller' => 'Offerings', 'action' => 'view', $exercise['Exercise']['offering_id']));
    }
    $this->Session->setFlash(__('Exercise was not deleted'));
    $this->redirect(array('controller' => 'Offerings', 'action' => 'view', $exercise['Exercise']['offering_id']));
  }

  public function restore($id = null)
  {
    $this->Exercise->id = $id;
    if (!$this->Exercise->exists()) {
      throw new NotFoundException(__('Invalid exercise'));
    }
    $this->request->onlyAllow('post', 'delete');
    $exercise = $this->Exercise->findById($id);
    if ($exercise['Exercise']['ghost']) {
      $this->Exercise->id = $exercise['Exercise']['real_id'];
      $id = $exercise['Exercise']['real_id'];
    }
    if ($this->Exercise->saveField('removed', false)) {
      $this->Exercise->updateAll(array('removed' => false), array('real_id' => $id));
      Log::register("Restored the Exercise #" . $id, $this->currentUser);
      $this->Session->setFlash(__('Exercise restored'), 'default', array(), 'success');
      $this->redirect(array('controller' => 'Exercises', 'action' => 'index'));
    }
    $this->Session->setFlash(__('Exercise was not restored'));
    $this->redirect(array('controller' => 'Exercises', 'action' => 'index'));
  }

  public function removeAllCases($id = null)
  {
    $this->Exercise->id = $id;
    if ($this->Exercise->field('ghost')) {
      $this->Exercise->id = $this->Exercise->field('real_id');
    }
    if (!$this->Exercise->exists()) {
      throw new NotFoundException(__('Invalid exercise'));
    }
    $this->request->onlyAllow('post', 'delete');
    //        $this->Exercise->saveField();
    $this->loadModel("ExerciseCase");

    $err = false;
    $cases = $this->ExerciseCase->findAllByExerciseId($id, array("id"));
    foreach ($cases as $case) {
      $delete = $this->ExerciseCase->delete($case["ExerciseCase"]["id"]);
      $err = ($err || !$delete);
    }

    if (!$err) {
      $this->Exercise->updateExerciseCasesChange();
      Log::register("Removed All Cases the Exercise #" . $id, $this->currentUser);
      $this->Session->setFlash(__('Exercise Cases deleted'), 'default', array(), 'success');
      return $this->redirect(array('controller' => 'Exercises', 'action' => 'viewProfessor', $id));
    }
    $this->Session->setFlash(__('Exercise Cases was not deleted'));
    return $this->redirect(array('controller' => 'Exercises', 'action' => 'viewProfessor', $id));
  }

  public function getAllScoresZipped($id = null)
  {
    $this->Exercise->id = $id;
    $this->Exercise->recursive = -1;
    if (!$this->Exercise->exists()) {
      throw new NotFoundException(__('Invalid exercise'));
    }
    $zipfile = tempnam("tmp", "zip");
    $zip = new ZipArchive();
    if ($zip->open($zipfile, ZIPARCHIVE::OVERWRITE) !== TRUE) {
      throw new NotFoundException(__('Invalid exercise'));
    }
    $exercise = $this->Exercise->findById($id);
    $info = "Zip file generated at: " . date('d/m/Y H:i:s') . " \nThis zip file contains the last submitted file for each student so far \nExercise: " . $exercise['Exercise']['title'];
    $info = "Arquivo zip gerado em: " . date('d/m/Y H:i:s') . " \nEste arquivo contem a última submissão de cada aluno até o momento.  \nExercício: " . $exercise['Exercise']['title'];
    $filename = $this->_removeAccents($exercise['Exercise']['title']) . ".zip";
    $zip->addFromString('README.txt', $info);

    $this->loadModel('Commit');
    $this->loadModel('Offering');
    $this->loadModel('User');
    $this->loadModel('Archive');
    $this->Commit->recursive = -1;
    $this->Offering->recursive = -1;
    $this->User->recursive = -1;
    $scores = $this->Commit->find('all', array('fields' => array('id', 'user_email', 'commit_time', 'hash', 'aws_key'), 'conditions' => array('Commit.exercise_id' => $id, " Commit.commit_time = (SELECT MAX(commit_time) FROM commits c2 WHERE Commit.user_email = c2.user_email AND Commit.exercise_id=c2.exercise_id GROUP BY user_email, exercise_id)"), 'order' => 'score DESC, commit_time ASC'));

    //        $offering = $this->Offering->findById($exercise['Exercise']['offering_id']);
    $requestHash = md5($info);
    $tmpDir = "/tmp/" . $requestHash . "/";
    $dir = new Folder($tmpDir, true, 0777);
    foreach ($scores as $commit) {
      $user = $this->User->findByEmail($commit['Commit']['user_email'], array("identifier", "name"));
      $ext = explode('.', $commit['Commit']['aws_key']);
      $ext = count($ext) > 0 ? $ext[count($ext) - 1] : "";
      $tmpFile = $tmpDir . md5($commit['Commit']['id'] . $commit['Commit']['commit_time']) . $commit['Commit']['hash'] . "." . $ext;
      $this->Archive->getCommitFileSavedFromAwsS3($commit['Commit']['aws_key'], $tmpFile);
      if (file_exists($tmpFile)) {
        $zip->addFile(
          $tmpFile,
          $user['User']['identifier'] . '-' . $this->_removeAccents($user['User']['name']) . '/' . $user['User']['identifier'] . '-' . $this->_removeAccents($user['User']['name']) . '.' . $ext
        );
      }
      //            $dir = new Folder(Configure::read('Upload.dir').'/'.$offering['Offering']['course_id'].'/'.$exercise['Exercise']['offering_id'].'/'.$exercise['Exercise']['id'].'/'.$commit['Commit']['user_email'].'/'.$commit['Commit']['commit_time'].'/',false,0777);
      //            $dirname = Configure::read('Upload.dir').'/'.$offering['Offering']['course_id'].'/'.$exercise['Exercise']['offering_id'].'/'.$exercise['Exercise']['id'].'/'.$commit['Commit']['user_email'].'/'.$commit['Commit']['commit_time'].'/';
      //            $files=$dir->find();
      //            $file = array();
      //            if(count($files) > 0) {
      //                $user = $this->User->findByEmail($commit['Commit']['user_email']);
      //                foreach ($files as $f) {
      //                    if (file_exists($dirname.$f) && !is_dir($dirname.$f)) {
      //                        $ext = explode('.', $f);
      //                        $ext = $ext[count($ext)-1];
      //                        $zip->addFile(
      //                            $dirname.$f,
      //                            $user['User']['identifier'].'-'.$this->_removeAccents($user['User']['name']).'/'.$user['User']['identifier'].'-'.$this->_removeAccents($user['User']['name']).'.'.$ext
      //                        );
      //                    }
      //                }
      //            }
    }

    Log::register("The user has dowloaded all files of Exercise #" . $id, $this->currentUser);
    $zip->close();
    $url = $this->Archive->copyDownloadableFileToAwsS3($requestHash . DS . $filename, $zipfile, $filename);
    if ($url !== false) {
      //            header('Content-Type: application/zip');
      //            header('Content-Length: ' . filesize($zipfile));
      //            header('Content-Disposition: attachment; filename='.$filename);
      //          readfile($zipfile);
      //          unlink($zipfile);
      header("Location: " . $url);
    } else {
      $this->Session->setFlash(__("Sorry! We could not generate the zip file now. Please try again!"));
      Log::register("Error on getting S3 downloadable pre-signed url for zipped commits Exercise: #" . $id, $this->currentUser);
      $this->redirect(array('controller' => 'Exercises', 'action' => 'viewProfessor', $id));
    }
    exit();
  }

  public function downloadCases($id = null)
  {
    $this->Exercise->id = $id;
    $this->Exercise->recursive = -1;
    $this->loadModel("ExerciseCase");
    $this->loadModel("Archive");
    $this->ExerciseCase->recursive = -1;
    if (!$this->Exercise->exists()) {
      throw new NotFoundException(__('Invalid exercise'));
    }
    $zipfile = tempnam("tmp", "zip");
    $zip = new ZipArchive();
    if ($zip->open($zipfile, ZIPARCHIVE::OVERWRITE) !== TRUE) {
      throw new NotFoundException(__('Invalid exercise'));
    }

    $exercise = $this->Exercise->findById($id, array('Exercise.offering_id', 'Exercise.title', 'Exercise.ghost', 'Exercise.real_id'));
    $offering_id = $exercise['Exercise']['offering_id'];
    if (isset($this->currentUser['onlyStudent']) && $this->currentUser['onlyStudent']) {
      $assistantOrProfessor =  false;
    } else {
      $assistantOrProfessor = ($this->currentUser['type'] >= $this->User->getAdminIndex()) ? true : $this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $offering_id);
    }
    if ($assistantOrProfessor) {
      $info = "Arquivo zip gerado em: " . date('d/m/Y H:i:s') . " \nEste arquivo contém todos os casos de teste cadastrados até o momento, disponível apenas para professores/monitores. \nPara alterar um caso de teste acesse o sistema. \nExercício: " . $exercise['Exercise']['title'];
    } else {
      $info = "Arquivo zip gerado em: " . date('d/m/Y H:i:s') . " \nEste arquivo contém os casos de teste cadastrados até o momento, disponibilizado pelo professor aos alunos.\nExercício: " . $exercise['Exercise']['title'];
    }
    if ($exercise['Exercise']['ghost']) {
      $id = $exercise['Exercise']['real_id'];
    }
    $cases = $this->ExerciseCase->findAllByExerciseId($id, array("id", "input", "output", "show_input", "show_expected_output", "input_md5", "output_md5"), array("ExerciseCase.id"));
    $filename = "Casos de Teste - " . $this->_removeAccents($exercise['Exercise']['title']) . ".zip";
    $countCases = 1;
    foreach ($cases as $case) {
      if ($assistantOrProfessor || $case['ExerciseCase']['show_input']) {
        if ($case['ExerciseCase']['input_md5'] !== $this->Archive->getExerciseCaseInputMD5FromAwsS3($case['ExerciseCase']['id'])) {
          $zip->addFromString($countCases . '.in', $case['ExerciseCase']['input']);
        } else {
          $zip->addFromString($countCases . '.in', $this->Archive->getExerciseCaseInputFromAwsS3($case['ExerciseCase']['id']));
        }
      }
      if ($assistantOrProfessor || $case['ExerciseCase']['show_expected_output']) {
        if ($case['ExerciseCase']['output_md5'] !== $this->Archive->getExerciseCaseOutputMD5FromAwsS3($case['ExerciseCase']['id'])) {
          $zip->addFromString($countCases . '.out', $case['ExerciseCase']['output']);
        } else {
          $zip->addFromString($countCases . '.out', $this->Archive->getExerciseCaseOutputFromAwsS3($case['ExerciseCase']['id']));
        }
      }
      $countCases++;
    }

    $zip->addFromString('README.txt', $info);
    Log::register("The user has dowloaded all cases of Exercise #" . $id, $this->currentUser);
    $zip->close();
    header('Content-Type: application/zip');
    header('Content-Length: ' . filesize($zipfile));
    header('Content-Disposition: attachment; filename=' . $filename);
    readfile($zipfile);
    unlink($zipfile);
    exit();
  }

  public function showInput($id, $show = false)
  {
    $show = $show ? true : false;
    $this->Exercise->id = $id;
    if (!$this->Exercise->exists()) {
      throw new NotFoundException(__('Invalid exercise'));
    }
    $this->Exercise->recursive = -1;
    $this->loadModel("ExerciseCase");
    $this->ExerciseCase->updateAll(array("ExerciseCase.show_input" => $show), array("ExerciseCase.exercise_id" => $id));
    $this->Session->setFlash(__('All cases input visibility have been updated'), 'default', array(), 'success');
    $this->redirect(array('controller' => 'Exercises', 'action' => 'viewProfessor', $id));
  }

  public function showExpectedOutput($id, $show = false)
  {
    $show = $show ? true : false;
    $this->Exercise->id = $id;
    if (!$this->Exercise->exists()) {
      throw new NotFoundException(__('Invalid exercise'));
    }
    $this->Exercise->recursive = -1;
    $this->loadModel("ExerciseCase");
    $this->ExerciseCase->updateAll(array("ExerciseCase.show_expected_output" => $show), array("ExerciseCase.exercise_id" => $id));
    $this->Session->setFlash(__('All cases expected output visibility have been updated'), 'default', array(), 'success');
    $this->redirect(array('controller' => 'Exercises', 'action' => 'viewProfessor', $id));
  }

  public function showUserOutput($id, $show = false)
  {
    $show = $show ? true : false;
    $this->Exercise->id = $id;
    if (!$this->Exercise->exists()) {
      throw new NotFoundException(__('Invalid exercise'));
    }
    $this->Exercise->recursive = -1;
    $this->loadModel("ExerciseCase");
    $this->ExerciseCase->updateAll(array("ExerciseCase.show_user_output" => $show), array("ExerciseCase.exercise_id" => $id));
    $this->Session->setFlash(__('All cases user output visibility have been updated'), 'default', array(), 'success');
    $this->redirect(array('controller' => 'Exercises', 'action' => 'viewProfessor', $id));
  }

  public function exportExerciseToGoogleCalendar($exerciseId)
  {
    if (!$this->Exercise->exists($exerciseId)) {
      throw new NotFoundException(__('Invalid exercise'));
    }
    $googleUrl = $this->Exercise->getGoogleCalendarLink($exerciseId);
    Log::register("Exported the Exercise #" . $exerciseId . ' to Google Calendar', $this->currentUser);
    $this->redirect($googleUrl);
    die();
  }
}
