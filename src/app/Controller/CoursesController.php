<?php
App::uses('AppController', 'Controller');
/**
 * Courses Controller
 *
 * @property Course $Course
 * @property PaginatorComponent $Paginator
 */
class CoursesController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator');
        
    public function isAuthorized($user = null) {
        $this->loadModel('User');
        if($user['type'] >= $this->User->getAdminIndex()) return true;
        else {
            if (strtolower($this->request->params['action']) == "courseslist" && $user['type'] >= $this->User->getProfessorIndex()) {
                return true;
            }
            return false;
        }
    }

    public function add () {
        if ($this->request->is('post')) {
            if(isset($this->request->data['Course']['batch'])){
                $cAdd = 0;
                $cExists = 0;
                $cError = 0;
                $courses = explode(PHP_EOL,$this->request->data['Course']['batch']);
                foreach ($courses as $course) {
                    $courseData = explode(' ',$course);
                    if (count($courseData) >= 2) {
                        $courseCode = $courseData[0];
                        unset ($courseData[0]);
                        $courseTitle = trim(implode(' ',$courseData));
                        if ($this->Course->find('count', array('conditions' => array('code' => $courseCode,'university_id' => $this->request->data['Course']['university_id']))) > 0) {
                            $cExists++;
                        }else {
                            $this->Course->create();
                            if ($this->Course->save(array('Course' => array('code' => $courseCode,'title' => $courseTitle,'university_id' => $this->request->data['Course']['university_id'])))) {
                                $cAdd++;
                            } else $cError++;
                        }
                    } else {
                        $cError;
                    }
                }
                if ($cError + $cExists > 0) {
                    $this->Session->setFlash(__('%d courses have been saved, %d courses have been registered before, %d courses could not be saved',$cAdd,$cExists,$cError));
                } else {
                    $this->Session->setFlash(__('All %d courses have been saved',$cAdd), 'default', array(), 'success');
                }
            }else{
                if ($this->Course->find('count', array('conditions' => array('code' => $this->request->data['Course']['code']))) > 0) {
                    $this->Session->setFlash(__('We already have a course with this code'));
                }else{
                    $this->Course->create();
                    if ($this->Course->save($this->request->data)) {
                        Log::register("Added the course with code '".$this->request->data['Course']['code']."'", $this->currentUser);
                        $this->Session->setFlash(__('The course has been saved'), 'default', array(), 'success');
                        unset($this->request->data);
                    } else {
                        $this->Session->setFlash(__('The course could not be saved. Please, try again.'));
                    }
                }
            }
        }
        $this->redirect(array('controller' => "Courses"));
    }

	public function index($startswith = null) {
        $this->layout = "template2015";
        $this->Course->recursive = 0;
        $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $this->set('letters',$letters);
        $cond=array();
        if ($this->request->is('post')) {
            if (isset($this->request->data['Course']['university_id']) && $this->request->data['Course']['university_id'] == -1) {
                $this->request->data['Course']['university_id'] = null;
            }
            $this->redirect(array('controller' => 'Courses',
                'action' => 'index',
                'university' => $this->request->data['Course']['university_id'],
                'title' => $this->request->data['Course']['title'],
                'code' => $this->request->data['Course']['code']));
        }

        if (isset($this->request->params['named']['university'])) {
            $cond['Course.university_id'] = $this->request->params['named']['university'];
        }
        if (isset($this->request->params['named']['startswith'])) {
            $cond['Course.title ILIKE'] = $this->request->params['named']['startswith'].'%';
            $startswith = strtoupper($this->request->params['named']['startswith']);
        }
        if (isset($this->request->params['named']['code']) && strlen($this->request->params['named']['code']) > 0) {
            $cond['Course.code ILIKE'] = '%'.$this->request->params['named']['code'].'%';
        }
        if (isset($this->request->params['named']['title']) && strlen($this->request->params['named']['title']) > 0) {
            $cond['Course.title ILIKE'] = '%'.$this->request->params['named']['title'].'%';
        }

        $this->paginate = array(
            'conditions' => $cond,
            'order' => 'university_id'
        );
        $this->set('startswith',$startswith);
        $this->set('universities', array('-1' => __("All")) + $this->Course->University->find('list',array('order' => array("name" => "ASC"))));
        $this->set('courses', $this->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->Course->exists($id)) {
			throw new NotFoundException(__('Invalid course'));
		}
		$options = array('conditions' => array('Course.' . $this->Course->primaryKey => $id));
		$this->set('course', $this->Course->find('first', $options));
	}

	public function edit($id = null) {
		if (!$this->Course->exists($id)) {
			throw new NotFoundException(__('Invalid course'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Course->save($this->request->data)) {
				$this->Session->setFlash(__('The course has been saved'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The course could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Course.' . $this->Course->primaryKey => $id));
			$this->request->data = $this->Course->find('first', $options);
		}
	}

	public function delete($id = null) {
		$this->Course->id = $id;
		if (!$this->Course->exists()) {
			throw new NotFoundException(__('Invalid course'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->Course->delete()) {
			$this->Session->setFlash(__('The course was deleted with success'),'default',array(),'success');
			return $this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Course was not deleted'));
		return $this->redirect(array('action' => 'index'));
	}
        
        public function coursesList($search = null) {
            $this->autoRender = false;
            $title = null;

            if (!isset($search) || strlen($search) <= 0) {
                $search = null;
            }
            $conditions = array ('OR' => array('title ILIKE' => '%'.$search.'%','code ILIKE' => '%'.$search.'%'),'university_id' => $this->currentUser['university_id']);
            
            $courses = $this->Course->find('list', array('conditions' => $conditions,'order' => array('title')));
            $coursesList = array();
            foreach ($courses as $k => $course) {
                if($course != "RUN0001 - Run Codes Test") {
                    $item = new stdClass();
                    $item->id = $k;
                    $item->title = $course;
                    array_push($coursesList, $item);
                }
            }
            echo json_encode($coursesList);
        }
}
