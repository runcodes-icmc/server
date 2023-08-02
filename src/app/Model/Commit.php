<?php
App::uses('AppModel', 'Model');
/**
 * Commit Model
 *
 * @property User $User
 * @property ExerciseCase $ExerciseCase
 */
class Commit extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
        public $recursive = -1;
	
        public $validate = array(
		'users_email' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
			'email' => array(
				'rule' => array('email'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'exercises_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'status' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'corrects' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_email',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
                'Exercise' => array(
			'className' => 'Exercise',
			'foreignKey' => 'exercise_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * hasAndBelongsToMany associations
 *
 * @var array
 */
	public $hasAndBelongsToMany = array(
		'ExerciseCase' => array(
			'className' => 'ExerciseCase',
			'joinTable' => 'commits_exercise_cases',
			'foreignKey' => 'commit_id',
			'associationForeignKey' => 'exercise_case_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => 'exercise_case_id',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		)
	);
        
        public function getStatusList(){
            return array(__("In Queue"),__("Compiling"),__("Compiled"),__("Running"),__("Uncompleted"),__("Completed"),__("Error"),__("Submitted"),__("Plagiarism"),__("Server Error"),__("Running"),__("Running"));
        }
        
        public function getDetailedStatusList(){
            return array(__("Your commit is waiting to be automatically processed"),
                         __("Your commit is being compiled"),
                         __("Your commit is waiting to be executed"),
                         __("Your commit is being executed"),
                         __("Your commit has not reached all cases correct"),
                         __("Your commit reached all cases correct"),
                         __("Your commit resulted in an error"),
                         __("Your commit will not be processed automatically, and is currently waiting an manual feedback"),
                         __("Your commit was analyzed as plagiarism"),
                         __("Your commit could not be analyzed by our engine, please contact support@run.codes"),
                         __("Your commit could not be analyzed by our engine, please contact support@run.codes"));
        }
        
        public function getOptionsStatusList(){
            return array(4 => __("Uncompleted"),5 => __("Completed"),6 => __("Error"), 7 => __("Waiting..."), 8 => __("Plagiarism"));
        }
        public function getInQueueStatusValue(){
            return 0;
        }

        public function getCompilingStatusValue(){
            return 1;
        }

        public function getUncompletedStatusValue() {
            return 4;
        }

        public function getCompletedStatusValue() {
            return 5;
        }

        public function getErrorStatusValue() {
            return 6;
        }

        public function getServerErrorStatusValue() {
            return 9;
        }
        
        public function getNonCompilableDefaultStatusValue() {
            return 7;
        }
        
        public function getDefaultStatusValue(){
            return 6;
        }
        public function getStatusByListIndex($index){
            $list = $this->getStatusList();
            return $list[$index];
        }
        public function getDetailedStatusByListIndex($index){
            $list = $this->getDetailedStatusList();
            return $list[$index];
        }
        
        public function getExerciseId() {
            $this->recursive = -1;
            if (is_numeric($this->id)) {
                $ex_data = $this->findById($this->id,array('exercise_id'));
                return $ex_data['Commit']['exercise_id'];
            } else {
                return null;
            }
        }
        
        public function getUserEmail() {
            $this->recursive = -1;
            if (is_numeric($this->id)) {
                $ex_data = $this->findById($this->id,array('user_email'));
                return $ex_data['Commit']['user_email'];
            } else {
                return null;
            }
        }
        
        public function afterFind($results,$primary = false) {
            if(isset($results[0]['Commit']['status'])) {
                foreach ($results as $key => $result) {
                    $results[$key]['Commit']['name_status']=$this->getStatusByListIndex($results[$key]['Commit']['status']);
                    $results[$key]['Commit']['detailed_status'] = $this->getDetailedStatusByListIndex($results[$key]['Commit']['status']);
                    if($results[$key]['Commit']['status']==0) {
                        $results[$key]['Commit']['status_color'] = "warning";
                    }else if($results[$key]['Commit']['status']==1) {
                        $results[$key]['Commit']['status_color'] = "inverse";
                    }else if($results[$key]['Commit']['status']==2) {
                        $results[$key]['Commit']['status_color'] = "inverse";
                    }else if($results[$key]['Commit']['status']==3) {
                        $results[$key]['Commit']['status_color'] = "inverse";
                    }else if($results[$key]['Commit']['status']==4) {
                        $results[$key]['Commit']['status_color'] = "info";
                    }else if($results[$key]['Commit']['status']==5) {
                        $results[$key]['Commit']['status_color'] = "success";
                    }else if($results[$key]['Commit']['status']==6) {
                        $results[$key]['Commit']['status_color'] = "danger";
                    }else if($results[$key]['Commit']['status']==7) {
                        $results[$key]['Commit']['status_color'] = "inverse";
                    }else if($results[$key]['Commit']['status']==8) {
                        $results[$key]['Commit']['status_color'] = "danger";
                    }else if($results[$key]['Commit']['status']==9) {
                        $results[$key]['Commit']['status_color'] = "danger";
                    }else if($results[$key]['Commit']['status']==11) {
                        $results[$key]['Commit']['status_color'] = "inverse";
                    }else if($results[$key]['Commit']['status']==10) {
                        $results[$key]['Commit']['status_color'] = "inverse";
                    }else {
                        $results[$key]['Commit']['status_color'] = "danger";
                    }
                    if (isset($results[$key]['Commit']['compiled'])) {
                        if ($results[$key]['Commit']['compiled']==true) {
                            $results[$key]['Commit']['compiled_color'] = "success";
                        } else {
                            $results[$key]['Commit']['compiled_color'] = "danger";
                        }
                    } else {
                        $results[$key]['Commit']['compiled_color'] = "danger";
                    }
                    
                    if (isset($results[$key]['Commit']['corrects'])) {
                        if ($results[$key]['Commit']['corrects'] == 0) {
                            $results[$key]['Commit']['correct_color'] = "danger";
                        }else if ($results[$key]['Commit']['corrects'] > 0) {
                            $results[$key]['Commit']['correct_color'] = "info";
                        }
                    }
                    if (isset($results[$key]['Commit']['score'])) {
                        if ($results[$key]['Commit']['score'] < 5) {
                            $results[$key]['Commit']['score_color'] = "danger";
                        } else {
                            $results[$key]['Commit']['score_color'] = "success";
                        }
                    }
                    
                    if (isset($results[$key]['Commit']['compiled_signal'])) {
                        if ($results[$key]['Commit']['compiled']) {
                            $results[$key]['Commit']['compiled_message'] = __("Successfully Compiled");
                        } else {
                            $results[$key]['Commit']['compiled_message'] = base64_decode($results[$key]['Commit']['compiled_error']);
                        }
                    }
                }
            }
            if(isset($results['Commit']['status'])) {
                $results['Commit']['name_status']=$this->getStatusByListIndex($results['Commit']['status']);
                if($results['Commit']['status']==0) {
                    $results['Commit']['status_color'] = "warning";
                }else if($results['Commit']['status']==1) {
                    $results['Commit']['status_color'] = "info";
                }else if($results['Commit']['status']==2) {
                    $results['Commit']['status_color'] = "info";
                }else if($results['Commit']['status']==3) {
                    $results['Commit']['status_color'] = "primaty";
                }else if($results['Commit']['status']==4) {
                    $results['Commit']['status_color'] = "success";
                }else if($results['Commit']['status']==5) {
                    $results['Commit']['status_color'] = "danger";
                }else if($results['Commit']['status']==6) {
                    $results['Commit']['status_color'] = "primary";
                }else if($results['Commit']['status']==8) {
                    $results['Commit']['status_color'] = "danger";
                }else {
                    $results['Commit']['status_color'] = "danger";
                }
                if ($results['Commit']['compiled']==true) {
                    $results['Commit']['compiled_color'] = "success";
                } else {
                    $results['Commit']['compiled_color'] = "danger";
                }
                if ($results['Commit']['corrects'] == 0) {
                    $results['Commit']['correct_color'] = "danger";
                }else if ($results['Commit']['corrects'] > 0) {
                    $results['Commit']['correct_color'] = "primary";
                }
                if ($results['Commit']['score'] < 5) {
                    $results['Commit']['score_color'] = "danger";
                } else {
                    $results['Commit']['score_color'] = "success";
                }
            }
            
            if (isset($results['Commit']['compiled_signal'])) {
                if ($results['Commit']['compiled']) {
                    $results['Commit']['compiled_message'] = __("Successfully Compiled");
                } else {
                    $results['Commit']['compiled_message'] = base64_decode($results['Commit']['compiled_error']);
                }
            }
                    
            if(isset($results['status'])) {
                $results['name_status']=$this->getStatusByListIndex($results['status']);
                if($results['status']==0) {
                    $results['status_color'] = "warning";
                }else if($results['status']==1) {
                    $results['status_color'] = "info";
                }else if($results['status']==2) {
                    $results['status_color'] = "info";
                }else if($results['status']==3) {
                    $results['status_color'] = "primaty";
                }else if($results['status']==4) {
                    $results['status_color'] = "success";
                }else if($results['status']==5) {
                    $results['status_color'] = "danger";
                }else if($results['status']==6) {
                    $results['status_color'] = "primary";
                }else if($results['status']==8) {
                    $results['status_color'] = "danger";
                }else {
                    $results['status_color'] = "danger";
                }
                if ($results['compiled']==true) {
                    $results['compiled_color'] = "success";
                } else {
                    $results['compiled_color'] = "danger";
                }
                if ($results['corrects'] == 0) {
                    $results['correct_color'] = "danger";
                }else if ($results['corrects'] > 0) {
                    $results['correct_color'] = "primary";
                }
                if ($results['score'] < 5) {
                    $results['score_color'] = "danger";
                } else {
                    $results['score_color'] = "success";
                }
            }
            
            if (isset($results['compiled_signal'])) {
                if ($results['compiled']) {
                    $results['compiled_message'] = __("Successfully Compiled");
                } else {
                    $results['compiled_message'] = base64_decode($results['compiled_error']);
                }
            }
            
            
//            if (isset($results[])) {
//                
//            }
            return $results;
        }

}
