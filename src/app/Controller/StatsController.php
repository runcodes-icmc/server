<?php
App::uses('AppController', 'Controller');

class StatsController extends AppController
{

  public $helpers = array('Cache');

  public $name = 'Stats';
  public $uses = array();

  private $professorAuthorized = array('homestats', 'exercise');

  public function beforeFilter()
  {
    parent::beforeFilter();
    $this->Auth->allow('basicCount');
  }

  public function isAuthorized($user = null)
  {
    $this->loadModel('User');
    $this->loadModel('Enrollment');
    $this->loadModel('Exercise');
    if ($user['type'] >= $this->User->getAdminIndex()) {
      return true;
    }

    if (in_array(strtolower($this->request->params['action']), $this->professorAuthorized)) {
      $this->Exercise->id = $this->request->params['pass'][0];
      $offering = $this->Exercise->getOfferingId();
      if ($this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $offering)) {
        return true;
      }
    }
    return false;
  }

  public function basicCount()
  {
    $this->autoRender = false;
    $this->response->header('Access-Control-Allow-Origin', 'https://we.run.codes');
    $count = Cache::read('basic_count', '_cake_stats_');
    if (!$count) {
      $this->loadModel("Exercise");
      $this->loadModel("Commit");
      $this->loadModel("University");
      $this->loadModel("User");
      $count = array(
        'users' => $this->User->find('count'),
        'exercises' => $this->Exercise->find('count'),
        'commits' => $this->Commit->find('count'),
        'universities' => $this->University->find('count'),
      );
      Cache::write('basic_count', $count, '_cake_stats_');
    }
    echo json_encode($count);
  }

  public function homeInfographic()
  {
    if (!$this->request->is('post')) {
      $this->redirect('/home');
    }
    $this->autoRender = false;
    $this->loadModel("Commit");
    $this->loadModel("ServerWatch");
    if ($this->Session->check("ServerWatchCPUUtilization") && $this->Session->check("ServerWatchCPUUtilizationExpireAt")) {
      if ($this->Session->read("ServerWatchCPUUtilizationExpireAt") < strtotime('now')) {
        $this->Session->write("ServerWatchCPUUtilization", $this->ServerWatch->getCPUUtilization());
        $this->Session->write("ServerWatchCPUUtilizationExpireAt", strtotime('+2 minutes'));
      }
    } else {
      $this->Session->write("ServerWatchCPUUtilization", $this->ServerWatch->getCPUUtilization());
      $this->Session->write("ServerWatchCPUUtilizationExpireAt", strtotime('+2 minutes'));
    }
    $cloud = $this->Session->read("ServerWatchCPUUtilization");
    $this->Commit->recursive = -1;

    $info['queue'] = $this->Commit->find('count', array('conditions' => array('status' => $this->Commit->getInQueueStatusValue())));
    $info['execution'] = $this->Commit->find('count', array('conditions' => array('status' => $this->Commit->getCompilingStatusValue())));
    $info['users'] = 0;
    $info['servers'] = $cloud;

    $info['users'] = ":)";
    echo (json_encode($info));
  }

  public function homeServers()
  {
    if (!$this->request->is('post')) {
      $this->redirect('/home');
    }
    $this->autoRender = false;
    $this->loadModel("Ganglia");
    $ganglia = $this->Ganglia->find('all', array('fields' => array('name', 'diskfree', 'disktotal', 'memtotal', 'memcached', 'memfree', 'memtotal', 'cpuidle', 'ip', 'isjail', 'reported', 'boottime'), 'order' => array('name')));
    $servers = array();
    $now = new DateTime(); // string date
    foreach ($ganglia as $g) {
      $server = array();
      $server['disk'] = number_format(($g['Ganglia']['disktotal'] - $g['Ganglia']['diskfree']) / $g['Ganglia']['disktotal'] * 100, 1);
      $server['ram'] = number_format(($g['Ganglia']['memtotal'] - $g['Ganglia']['memcached'] - $g['Ganglia']['memfree']) * 100 / $g['Ganglia']['memtotal'], 1);
      $server['cpu'] = number_format(100.0 - $g['Ganglia']['cpuidle'], 1);
      $server['name'] = $g['Ganglia']['name'];
      $server['ip'] = $g['Ganglia']['ip'];
      $server['isJail'] = $g['Ganglia']['isjail'];
      $reported = new DateTime(date("Y-m-d H:i:s", $g['Ganglia']["reported"]));
      $server['boot'] = date("d/m/Y H:i:s", $g['Ganglia']["boottime"]);
      $server['lastreport'] = date("d/m/Y H:i:s", $g['Ganglia']["reported"]);
      $warn = array();
      if (floatval($server['disk']) >= 90) {
        array_push($warn, "DISK");
      }
      if (floatval($server['ram']) >= 90) {
        array_push($warn, "RAM");
      }
      if (floatval($server['cpu']) >= 90) {
        array_push($warn, "CPU");
      }
      if (intval($reported->diff($now)->format("%H")) > 1) {
        array_push($warn, "GANGLIA");
      }
      if (count($warn) > 0) {
        $server['warning'] = implode(' ', $warn);
      } else {
        $server['warning'] = false;
      }
      array_push($servers, $server);
    }
    echo (json_encode($servers));
  }

  public function commitsInfographic()
  {
    if (!$this->request->is('post')) {
      $this->redirect('/home');
    }
    $this->autoRender = false;
    $this->loadModel("Commit");
    $this->Commit->recursive = -1;
    $today = new DateTime();
    $todayDate = new DateTime($today->format("Y-m-d"));
    $yesterday = clone $todayDate;
    $last7 = clone $todayDate;
    $yesterday = $yesterday->sub(new DateInterval('P1D'));
    $last7 = $last7->sub(new DateInterval('P7D'));

    $info['today'] = $this->Commit->find('count', array('conditions' => array("commit_time BETWEEN '" . $todayDate->format("Y-m-d 00:00:00") . "' AND '" . $todayDate->format("Y-m-d 23:59:59") . "'")));
    $info['yesterday'] = $this->Commit->find('count', array('conditions' => array("commit_time BETWEEN '" . $yesterday->format("Y-m-d 00:00:00") . "' AND '" . $yesterday->format("Y-m-d 23:59:59") . "'")));
    $info['last7'] = $this->Commit->find('count', array('conditions' => array("commit_time BETWEEN '" . $last7->format("Y-m-d 00:00:00") . "' AND '" . $todayDate->format("Y-m-d 23:59:59") . "'")));

    $stats = new stdClass();
    $stats->box1 = new stdClass();
    $stats->box2 = new stdClass();
    $stats->box3 = new stdClass();
    $stats->box1->icon = "fa-clock-o";
    $stats->box1->title = __("Today");
    $stats->box1->value = $info['today'];
    $stats->box2->icon = "fa-bookmark";
    $stats->box2->title = __("Yesterday");
    $stats->box2->value = $info['yesterday'];
    $stats->box3->icon = "fa-calendar";
    $stats->box3->title = __("Last 7 days");
    $stats->box3->value = $info['last7'];

    echo (json_encode($stats));
  }

  public function commitsChart()
  {
    if (!$this->request->is('post')) {
      $this->redirect('/home');
    }
    $this->autoRender = false;
    $this->loadModel("Commit");
    $this->Commit->recursive = -1;
    $today = new DateTime();
    $todayDate = new DateTime($today->format("Y-m-d"));
    $diff1Day = new DateInterval('P1D');
    $count = 28;
    $currentDate = $todayDate->sub(new DateInterval('P28D'));
    $serieError = array();
    $serieCompleted = array();
    $serieUncompleted = array();
    $serieSubmitted = array();
    while ($count >= 0) {
      $chartItem = new stdClass();
      $chartItem->x = $currentDate->getTimestamp();
      $chartItem->y = $this->Commit->find('count', array('conditions' => array('status' => $this->Commit->getErrorStatusValue(), "commit_time BETWEEN '" . $currentDate->format("Y-m-d 00:00:00") . "' AND '" . $currentDate->format("Y-m-d 23:59:59") . "'")));
      array_push($serieError, $chartItem);

      $chartItem = new stdClass();
      $chartItem->x = $currentDate->getTimestamp();
      $chartItem->y = $this->Commit->find('count', array('conditions' => array('status' => $this->Commit->getCompletedStatusValue(), "commit_time BETWEEN '" . $currentDate->format("Y-m-d 00:00:00") . "' AND '" . $currentDate->format("Y-m-d 23:59:59") . "'")));
      array_push($serieCompleted, $chartItem);

      $chartItem = new stdClass();
      $chartItem->x = $currentDate->getTimestamp();
      $chartItem->y = $this->Commit->find('count', array('conditions' => array('status' => $this->Commit->getUncompletedStatusValue(), "commit_time BETWEEN '" . $currentDate->format("Y-m-d 00:00:00") . "' AND '" . $currentDate->format("Y-m-d 23:59:59") . "'")));
      array_push($serieUncompleted, $chartItem);

      $chartItem = new stdClass();
      $chartItem->x = $currentDate->getTimestamp();
      $chartItem->y = $this->Commit->find('count', array('conditions' => array('status' => $this->Commit->getNonCompilableDefaultStatusValue(), "commit_time BETWEEN '" . $currentDate->format("Y-m-d 00:00:00") . "' AND '" . $currentDate->format("Y-m-d 23:59:59") . "'")));
      array_push($serieSubmitted, $chartItem);

      $currentDate = $currentDate->add($diff1Day);
      $count--;
    }
    $chart = array();
    $chart[0] = new stdClass();
    $chart[1] = new stdClass();
    $chart[2] = new stdClass();
    $chart[3] = new stdClass();
    $chart[0]->name = __("Error");
    $chart[0]->color = '#c0392b';
    $chart[0]->data = $serieError;
    $chart[1]->name = __("Completed");
    $chart[1]->color = '#16a085';
    $chart[1]->data = $serieCompleted;
    $chart[2]->name = __("Uncompleted");
    $chart[2]->color = '#f39c12';
    $chart[2]->data = $serieUncompleted;
    $chart[3]->name = __("Submitted");
    $chart[3]->color = '#2c3e50';
    $chart[3]->data = $serieSubmitted;
    //        debug($chart);
    echo (json_encode($chart));
  }

  public function visitsChart()
  {
    if (!$this->request->is('post')) {
      $this->redirect('/home');
    }
    $this->autoRender = false;
    $time = 60 * 24; //last 24 hours
    $today = new DateTime();
    $todayDate = new DateTime($today->format("Y-m-d"));
    $initDate = (clone $todayDate);
    $initDate->sub(new DateInterval('P30D'));

    $visits = json_decode("{}");

    $chart = array();
    $chart[0] = new stdClass();
    $chart[1] = new stdClass();
    $chart[0]->name = __("Unique Visitors");
    $chart[0]->color = '#c0392b';
    $chart[1]->name = __("Visits");
    $chart[1]->color = '#16a085';
    $chart[0]->data = array();
    $chart[1]->data = array();

    foreach ($visits as $date => $visitsDate) {
      $chartVisitsItem = new stdClass();
      $chartUniqueItem = new stdClass();
      $currentDate = new DateTime($date);
      $chartUniqueItem->x = $currentDate->getTimestamp();
      $chartUniqueItem->y = isset($visitsDate->nb_uniq_visitors) ? $visitsDate->nb_uniq_visitors : 0;
      $chartVisitsItem->x = $currentDate->getTimestamp();
      $chartVisitsItem->y = isset($visitsDate->nb_visits) ? $visitsDate->nb_visits : 0;
      array_push($chart[0]->data, $chartUniqueItem);
      array_push($chart[1]->data, $chartVisitsItem);
    }

    $staticData = json_decode("{}");
    if (isset($staticData->avg_time_on_site)) {
      $minutes = floor($staticData->avg_time_on_site / 60);
      $seconds = $staticData->avg_time_on_site % 60;
      $staticData->avg_time_on_site = $minutes . "'" . $seconds . '"';
    }
    $infographic = new stdClass();
    $infographic->box1 = new stdClass();
    $infographic->box2 = new stdClass();
    $infographic->box3 = new stdClass();
    $infographic->box1->icon = "fa-sign-out";
    $infographic->box1->title = __("Bounce Rate");
    $infographic->box1->value = (isset($staticData->bounce_rate)) ? $staticData->bounce_rate : ":)";
    $infographic->box2->icon = "fa-clock-o";
    $infographic->box2->title = __("Avg. Time on Site");
    $infographic->box2->value = (isset($staticData->avg_time_on_site)) ? $staticData->avg_time_on_site : ":)";
    $infographic->box3->icon = "fa-download";
    $infographic->box3->title = __("Downloads");
    $infographic->box3->value = (isset($staticData->nb_downloads)) ? $staticData->nb_downloads : ":)";

    $stats = new stdClass();
    $stats->infographic = $infographic;
    $stats->chart = $chart;
    echo json_encode($stats);
  }

  public function diskChart()
  {
    if (!$this->request->is('post')) {
      $this->redirect('/home');
    }
    $this->autoRender = false;
    $time = 60 * 24; //last 24 hours
    $today = new DateTime();
    $todayDate = new DateTime($today->format("Y-m-d h:i:s e"));
    $initDate = (clone $todayDate);
    $initDate->sub(new DateInterval('P180D'));

    $this->loadModel("DiskReport");
    $this->DiskReport->virtualFields['free_percent'] = 'DiskReport.free*100/(DiskReport.size+0.0001)';
    $serverData = $this->DiskReport->find('list', array('fields' => array('DiskReport.datetime', 'free_percent'), 'conditions' => array('disk' => 'xvda1', 'datetime >' => $initDate->format("Y-m-d h:i:s e"))));
    $archiveData = $this->DiskReport->find('list', array('fields' => array('DiskReport.datetime', 'free_percent'), 'conditions' => array('disk' => 'archive', 'datetime >' => $initDate->format("Y-m-d h:i:s e"))));
    $databaseData = $this->DiskReport->find('list', array('fields' => array('DiskReport.datetime', 'free_percent'), 'conditions' => array('disk' => 'database', 'datetime >' => $initDate->format("Y-m-d h:i:s e"))));

    $chart = array();
    $chart[0] = new stdClass();
    $chart[1] = new stdClass();
    $chart[2] = new stdClass();
    $chart[0]->name = __("Server Disk Free");
    $chart[0]->color = '#c0392b';
    $chart[1]->name = __("/database Disk Free");
    $chart[1]->color = '#16a085';
    $chart[2]->name = __("/archive Disk Free");
    $chart[2]->color = '#87169e';
    $chart[0]->data = array();
    $chart[1]->data = array();
    $chart[2]->data = array();

    foreach ($serverData as $date => $freePercent) {
      $item = new stdClass();
      $currentDate = new DateTime($date);
      $item->x = $currentDate->getTimestamp();
      $item->y = isset($freePercent) ? floatval($freePercent) : 0.00;
      array_push($chart[0]->data, $item);
    }
    foreach ($databaseData as $date => $freePercent) {
      $item = new stdClass();
      $currentDate = new DateTime($date);
      $item->x = $currentDate->getTimestamp();
      $item->y = isset($freePercent) ? floatval($freePercent) : 0.00;
      array_push($chart[1]->data, $item);
    }
    foreach ($archiveData as $date => $freePercent) {
      $item = new stdClass();
      $currentDate = new DateTime($date);
      $item->x = $currentDate->getTimestamp();
      $item->y = isset($freePercent) ? floatval($freePercent) : 0.00;
      array_push($chart[2]->data, $item);
    }

    $xvda1 = $this->DiskReport->findByDisk('xvda1', array('used'), array('id' => 'DESC'));
    $arch = $this->DiskReport->findByDisk('archive', array('used'), array('id' => 'DESC'));
    $db = $this->DiskReport->findByDisk('database', array('used'), array('id' => 'DESC'));
    $infographic = new stdClass();
    $infographic->box1 = new stdClass();
    $infographic->box2 = new stdClass();
    $infographic->box3 = new stdClass();
    $infographic->box1->icon = "fa-database";
    $infographic->box1->title = __("Server Disk in Use");
    $infographic->box1->value = number_format($xvda1['DiskReport']['used'], 1) . "G";
    $infographic->box2->icon = "fa-database";
    $infographic->box2->title = __("/database Disk in Use");
    $infographic->box2->value = number_format($db['DiskReport']['used'], 1) . "G";
    $infographic->box3->icon = "fa-database";
    $infographic->box3->title = __("/archive Disk in Use");
    $infographic->box3->value = number_format($arch['DiskReport']['used'], 1) . "G";

    $stats = new stdClass();
    $stats->infographic = $infographic;
    $stats->chart = $chart;
    echo json_encode($stats);
  }
}
