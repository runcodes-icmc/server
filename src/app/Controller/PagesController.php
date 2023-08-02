<?php
App::uses('AppController', 'Controller');

class PagesController extends AppController
{

  public $name = 'Pages';

  public $uses = array();
  public $components = array('Cookie');

  public function beforeFilter()
  {
    parent::beforeFilter();
    $this->Auth->allow("slack");
    $this->set('hide_panels', $this->Cookie->read('hide_panels'));
  }

  public function display()
  {
    $path = func_get_args();

    $count = count($path);
    if (!$count) {
      $this->redirect('/');
    }
    $page = $subpage = $title_for_layout = null;

    if (!empty($path[0])) {
      $page = $path[0];
    }
    if (!empty($path[1])) {
      $subpage = $path[1];
    }
    if (!empty($path[$count - 1])) {
      $title_for_layout = Inflector::humanize($path[$count - 1]);
    }
    $this->set(compact('page', 'subpage', 'title_for_layout'));
    $this->render(implode('/', $path));
  }

  public function home_student()
  {
    $this->layout = "template2015";
    $this->loadModel('User');
    $logged = $this->currentUser;

    $user['User'] = $logged;
    $user['University'] = $logged['University'];

    unset($user['User']['University']);
    unset($logged);
    if (!is_numeric($user['User']['identifier'])) {
      $this->Session->setFlash(__('Please, complete your register with your university'), 'default', array(), 'success');
      $this->redirect(array('controller' => 'users', 'action' => 'profile'));
    }
    $title_for_layout = "Home";
    $this->set('controller', 'Dashboard');

    $this->loadModel('Offering');
    $this->loadModel('Enrollment');
    $this->loadModel('Course');
    $this->loadModel('Exercise');


    $this->Enrollment->recursive = 0;
    $my_enrollments = $this->Enrollment->find('all', array('fields' => array('id', 'offering_id'), 'conditions' => array('user_email' => $user['User']['email'], 'Offering.end_date > NOW()', 'role IN (1,2)')));



    $this->Offering->recursive = -1;
    $this->Course->recursive = -1;
    foreach ($my_enrollments as $en) {
      $offering = $this->Offering->findById($en['Enrollment']['offering_id'], array('Offering.classroom', 'Offering.course_id'));
      $course = $this->Course->findById($offering['Offering']['course_id'], array('title'));
      $my_offerings[$en['Enrollment']['offering_id']] = $course['Course']['title'] . " " . $offering['Offering']['classroom'];
    }
    unset($offering);
    unset($course);
    unset($my_enrollments);

    if (isset($my_offerings)) {
      $this->set('my_offerings', $my_offerings);
    }
    unset($my_offerings);

    //        $this->Offering->recursive = 0;
    $this->Enrollment->recursive = 0;
    $enrollments = $this->Enrollment->find('all', array('fields' => array('offering_id'), 'conditions' => array('Enrollment.user_email' => $user['User']['email'], 'Offering.end_date > NOW()')));
    foreach ($enrollments as $k => $e) {
      $offering2 = $this->Offering->findById($e['Enrollment']['offering_id'], array('course_id', 'classroom', 'id'));
      $course = $this->Course->findById($offering2['Offering']['course_id'], array('code', 'title'));
      $enrollments[$k]['Offering'] = $offering2['Offering'];
      $enrollments[$k]['Offering']['Course'] = $course['Course'];
    }
    $this->Exercise->recursive = -1;
    $exercises = $this->Exercise->find('all', array(
      'conditions' => array('OR' => array('isOpen' => 'true', '(show_before_opening = true AND open_date >= NOW() AND deadline >= NOW())'), "EXISTS (SELECT id FROM enrollments WHERE user_email='" . $user['User']['email'] . "' AND enrollments.offering_id=Exercise.offering_id AND banned = FALSE)"),
      'limit' => 5,
      'order' => 'deadline',
      'fields' => array('id', 'title', 'offering_id', 'deadline')
    ));

    $this->loadModel('Commit');
    $this->Commit->recursive = -1;
    foreach ($exercises as $k => $ex) {
      $offering2 = $this->Offering->findById($ex['Exercise']['offering_id'], array('course_id'));
      $course = $this->Course->findById($offering2['Offering']['course_id'], array('code', 'title'));
      $exercises[$k]['Offering'] = $offering2['Offering'];
      $exercises[$k]['Offering']['Course'] = $course['Course'];
      $enrollment = $this->Enrollment->findByUserEmailAndOfferingId($user['User']['email'], $ex['Exercise']['offering_id'], array('role'));
      $exercises[$k]['Offering']['myRole'] = $enrollment['Enrollment']['role'];
      $commit = $this->Commit->findByExerciseIdAndUserEmail($ex['Exercise']['id'], $user['User']['email'], array('status', 'corrects', 'score'), array('commit_time' => 'desc'));
      if (count($commit) > 0) {
        $exercises[$k]['MyCommit'] = $commit['Commit'];
      }

      if (!isset($exercises[$k]['MyCommit'])) {
        $exercises[$k]['MyCommit']['name_status'] = $this->Exercise->getNotDeliveredNameStatus();
        $exercises[$k]['MyCommit']['status_color'] = $this->Exercise->getNotDeliveredStatusColor();
        $exercises[$k]['MyCommit']['corrects'] = 0;
        $exercises[$k]['MyCommit']['correct_color'] = "danger";
        $exercises[$k]['MyCommit']['score'] = '0.00';
        $exercises[$k]['MyCommit']['score_color'] = "danger";
      }
    }
    unset($commit);
    unset($offering2);
    unset($course);
    unset($enrollment);

    if ($this->Session->check('homeEnrollmentValidationErrors')) {
      $this->Enrollment->validationErrors = $this->Session->read('homeEnrollmentValidationErrors');
      $this->Session->delete('homeEnrollmentValidationErrors');
    }

    $this->set(compact('title_for_layout', 'enrollments', 'exercises'));
  }

  public function getServerDateTime()
  {
    echo date('d/m/Y H:i:s');
    $this->autoRender = false;
  }

  public function getServerDateTimeJson()
  {
    //            echo date('d/m/Y H:i:s');
    if (!$this->request->is('post')) {
      $this->redirect('/home');
    }
    //        $now = getdate();
    $this->loadModel("User");
    //        $result = $this->User->query("SELECT NOW();");
    $now = getdate($this->now);
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
    echo json_encode($now);
    $this->response = 'json';
    $this->autoRender = false;
  }

  public function slack()
  {
    $actions = array();
    $actions["help"] = "Lista as ações disponíveis";
    $actions["user [email]"] = "Retorna os dados do usuário [email] no sistema";
    $actions["professor [email]"] = "Transforma o usuário [email] em professor";
    $actions["queue"] = "Lista as entregas em fila";
    $actions["closing"] = "Lista os exercíos com deadline nas próximas 48 horas";
    $actions["stats [option]"] = "Retorna o gráfico da estatística requerida. Opções aceitas: [`visits`]";
    $this->autoRender = false;
    $this->response->type('json');
    header("Content-Type: application/json");
    $data = array();
    $data["mrkdwn_in"] = array("text", "pretext");
    $payload = array();
    if (isset($this->request->data["token"]) && $this->request->data["token"] == "tf2m9wtlEBewcjio30OjnH3U") {
      $command = $this->request->data["text"];
      $user = $this->request->data["user_name"];
      $command = explode(" ", $command);

      if (!in_array($user, array("fabiosikansi", "fadel", "felipelageduarte"))) {
        $data["pretext"] = "Desculpe " . $user . ", apenas respondo a comandos dos meus chefes.";
        $data["fallback"] = "Desculpe " . $user . ", apenas respondo a comandos dos meus chefes.";
      } else if ($command[0] == "help") {
        $data["pretext"] = "Olá " . $user . ", aqui estão as tarefas em que posso te ajudar. Você pode me chamar dizendo `/runcodes [tarefa] [opcoes]`\n";
        foreach ($actions as $ac => $text) {
          $data["pretext"] .= "`" . $ac . "`: " . $text . "\n";
        }
      } else if ($command[0] == "user") {
        if (isset($command[1])) {
          $this->loadModel("User");
          $list = $this->User->findByEmail($command[1]);
          if (count($list) > 1) {
            $data["color"] = "good";
            $data["pretext"] = "Olá " . $user . ", aqui está o usuário que você me requisitou";
            $data["title"] = $list["User"]["name"];
            $data["title_link"] = "https://run.codes/Users/view/" . $list["User"]["email"];
            $data["fallback"] = $data["pretext"] . "<" . $data["title_link"] . ">";
            $data["text"] = "Tipo: " . $list["User"]["type_name"] . "\nConfirmado: " . ($list["User"]["confirmed"] ? "Sim" : "Não");
          } else {
            $data["pretext"] = "Desculpe " . $user . ", não encontrei nenhum usuário no run.codes com email " . $command[1];
            $data["fallback"] = "Desculpe " . $user . ", não encontrei nenhum usuário no run.codes com email " . $command[1];
          }
        } else {
          $data["pretext"] = $user . ", você precisa me mandar o email do usuário";
        }
      } else if ($command[0] == "professor") {
        if (isset($command[1])) {
          $this->loadModel("User");
          $list = $this->User->findByEmail($command[1]);
          if (count($list) > 1) {
            if ($list["User"]["type"] == 0) {
              $this->User->id = $command[1];
              $this->User->saveField('type', 2);
              $ret = $this->User->sendProfessorMail($command[1]);
              $list = $this->User->findByEmail($command[1]);
              $data["color"] = "good";
              $data["pretext"] = "Olá " . $user . ", o usuário com email " . $command[1] . " foi transformado em professor";
              $data["title"] = $list["User"]["name"];
              $data["title_link"] = "https://run.codes/Users/view/" . $list["User"]["email"];
              $data["fallback"] = $data["pretext"] . "<" . $data["title_link"] . ">";
              $data["text"] = "Tipo: " . $list["User"]["type_name"] . "\nConfirmado: " . ($list["User"]["confirmed"] ? "Sim" : "Não");
              if ($ret == -1) {
                $data["text"] .= "\n O usuário não tem papel de professor";
              } else if (!ret) {
                $data["text"] .= "\n Email não enviado";
              } else {
                $data["text"] .= "\n Email de confirmação enviado ao usuário";
              }
            } else {
              $data["color"] = "warning";
              $data["pretext"] = "Olá " . $user . ", já possuia um outro papel de usuário";
              $data["title"] = $list["User"]["name"];
              $data["title_link"] = "https://run.codes/Users/view/" . $list["User"]["email"];
              $data["fallback"] = $data["pretext"] . "<" . $data["title_link"] . ">";
              $data["text"] = "Tipo: " . $list["User"]["type_name"] . "\nConfirmado: " . ($list["User"]["confirmed"] ? "Sim" : "Não");
            }
          } else {
            $data["pretext"] = "Desculpe " . $user . ", não encontrei nenhum usuário no run.codes com email " . $command[1];
            $data["fallback"] = "Desculpe " . $user . ", não encontrei nenhum usuário no run.codes com email " . $command[1];
          }
        } else {
          $data["pretext"] = $user . ", você precisa me mandar o email do usuário";
        }
      } else if ($command[0] == "queue") {
        $this->loadModel("Commit");
        $this->Commit->recursive = -1;
        $queue = $this->Commit->findAllByStatus($this->Commit->getInQueueStatusValue(), array("id", "user_email", "commit_time"));
        if (count($queue) == 0) {
          $data["color"] = "good";
          $data["pretext"] = "Olá " . $user . ", aqui estão as entregas em fila";
          $data["title"] = "Não existem entregas em fila";
          $data["title_link"] = "https://run.codes/Commits/";
          $data["fallback"] = $data["pretext"] . "<" . $data["title_link"] . ">";
        } else if (count($queue) <= 10) {
          $data["color"] = "warning";
          $data["pretext"] = "Olá " . $user . ", aqui estão as entregas em fila";
          $data["title"] = "Existe(m) " . count($queue) . " entrega(s) em fila";
          $data["title_link"] = "https://run.codes/Commits/";
          $data["text"] = "";
          foreach ($queue as $c) {
            $data["text"] .= "Commit: " . $c["Commit"]["id"] . ", User: " . $c["Commit"]["user_email"] . ", Commit Time: " . date("d/m/Y H:i:s", strtotime($c["Commit"]["commit_time"])) . "\n";
          }
        } else {
          $data["color"] = "danger";
          $data["pretext"] = "Olá " . $user . ", aqui estão as entregas em fila";
          $data["title"] = "Possivelmente um erro aconteceu, existem " . count($queue) . " entregas em fila no sistema";
          $data["title_link"] = "https://run.codes/Commits/";
        }
      } else if ($command[0] == "closing") {
        $data["pretext"] = "Olá " . $user . ", aqui estão os exercícios previstos para os próximos 2 dias";
        $this->loadModel("Exercise");
        $this->Exercise->recursive = -1;
        $exercises = $this->Exercise->find(
          "all",
          array(
            "conditions" => array("deadline >= NOW()", "deadline <= (NOW() + INTERVAL '2 days')"),
            "fields" => array("Exercise.id", "Exercise.offering_id", "Exercise.deadline", "Exercise.title"),
            "order" => array("Exercise.deadline", "Exercise.title")
          )
        );
        if (count($exercises) == 0) {
          $data["color"] = "good";
          $data["title"] = "Não existem exercícios previstos";
          $data["title_link"] = "https://run.codes/Exercises/";
          $data["fallback"] = $data["pretext"] . "<" . $data["title_link"] . ">";
        } else if (count($exercises) <= 20) {
          $data["color"] = "warning";
          $data["title"] = "Existe(m) " . count($exercises) . " exercícios previstos para os próximos dias";
          $data["title_link"] = "https://run.codes/Exercises/";
          $data["text"] = "";
          foreach ($exercises as $c) {
            $data["text"] .= "*Exercise: " . $c["Exercise"]["title"] . "*\n";
            $data["text"] .= "Deadline: " . date("d/m/Y H:i:s", strtotime($c["Exercise"]["deadline"])) . "\n";
            $data["text"] .= "<https://run.codes/Exercises/viewProfessor/" . $c["Exercise"]["id"] . "|Acessar Exercício> | <https://run.codes/Offering/view/" . $c["Exercise"]["offering_id"] . "|Acessar Oferecimento>\n";
          }
        } else {
          $data["color"] = "danger";
          $data["title"] = "Existem " . count($exercises) . " exercícios previstos para os próximos dias";
          $data["title_link"] = "https://run.codes/Commits/";
        }
      } else if ($command[0] == "stats") {
        if ($command[1] == "visits") {
          $data["color"] = "good";
          $data["title"] = "Visitas nos últimos 30 dias";
          $data["image_url"] = "";
        } else {
          $data["title"] = "Opção não encontrada";
          $data["pretext"] = "Desculpe " . $user . ", não reconheço a estatística " . $command[1] . "\nDigite o comando `/runcodes help` para saber em que posso te ajudar";
          $data["fallback"] = "Desculpe " . $user . ", não reconheço a estatística " . $command[1] . "\nDigite o comando `/runcodes help` para saber em que posso te ajudar";
          $data["color"] = "danger";
        }
      } else {
        $data["pretext"] = "Desculpe " . $user . ", não reconheço a ordem " . $command[0] . "\nDigite o comando `/runcodes help` para saber em que posso te ajudar";
        $data["fallback"] = "Desculpe " . $user . ", não reconheço a ordem " . $command[0] . "\nDigite o comando `/runcodes help` para saber em que posso te ajudar";
        $data["color"] = "danger";
      }
      if ($this->request->data["channel_name"] == "privategroup") {
        $payload["response_type"] = "in_channel";
      } else {
        $payload["response_type"] = "ephemeral";
      }
      $payload["attachments"][0] = $data;
      $this->response->body(json_encode($payload));
    } else {
      throw new NotFoundException();
    }
  }
}
