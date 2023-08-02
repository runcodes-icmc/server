<?php
App::uses('AppController', 'Controller');

class PublicExercisesController extends AppController {

    public $components = array('Paginator');

    private $studentAuthorized = array();
    private $professorAuthorized = array('index');

    public function isAuthorized($user = null) {
        $this->loadModel('User');
        $this->loadModel('Enrollment');
        if ($user['type'] >= $this->User->getAdminIndex()) {
            return true;
        }else if (($user['type'] >= $this->User->getProfessorIndex()) && in_array(strtolower($this->request->params['action']), $this->professorAuthorized)) {
            return true;
        } else {
            return false;
        }

    }

    public function beforeRender() {
        parent::beforeRender();
    }

    public function copy ($exercise_id) {
        $this->loadModel('Exercise');
        $this->layout = "template2015";
        $this->Exercise->recursive = -1;
        if (!$this->Exercise->exists($exercise_id)) {
            throw new NotFoundException(__('Invalid exercise'));
        }

        if ($this->request->is('post')) {
            if (!$this->Exercise->exists($this->request->data['PublicExercise']['exercise_id'])) {
                throw new NotFoundException(__('Invalid exercise'));
            }

            if (count($this->PublicExercise->findByExerciseId($this->request->data['PublicExercise']['exercise_id'])) > 0) {
                $this->Session->setFlash(__('This exercise was added to the public list before'));
            } else {
                if ($this->PublicExercise->save($this->request->data)) {
                    Log::register("Added the exercise #".$this->request->data['PublicExercise']['exercise_id']." to the public database", $this->Auth->user());
                    $this->Session->setFlash(__('The exercise has been added to the public database'),'default',array(), 'success');
                    $this->redirect(array("controller" => "Exercises", "action" => "view", $this->request->data['PublicExercise']['exercise_id']));
                } else {
                    $this->Session->setFlash(__('The exercise could not be saved as public. Please, try again.'));
                }
            }
        }

        $this->set('exercise',$this->Exercise->findById($exercise_id));
        $this->set('levels',$this->PublicExercise->getLevels());
    }

    public function index () {
        $this->layout = "template2015";
        $this->loadModel("Exercise");
        $this->Exercise->recursive = -1;
        $this->paginate = array('limit' => 10,'order' => array('level','name'));
        $publicExercises = $this->Paginator->paginate();
        foreach ($publicExercises as $k => $exercise) {
            $ex = $this->Exercise->findById($exercise['PublicExercise']['exercise_id'],array("title","description","markdown"));
            $this->Exercise->id = $exercise['PublicExercise']['exercise_id'];
            $ex['Exercise']['num_cases'] = $this->Exercise->getNumberOfCases();
            $publicExercises[$k]['Exercise'] = $ex['Exercise'];
            $publicExercises[$k]['PublicExercise']['level_name'] = $this->PublicExercise->getLevelByIndex($exercise['PublicExercise']['level']);
        }

        $this->set('publicExercises', $publicExercises);
    }
}
