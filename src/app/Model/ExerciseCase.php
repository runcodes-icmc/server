<?php
App::uses('AppModel', 'Model');
/**
 * ExerciseCase Model
 *
 * @property Exercise $Exercise
 * @property ExerciseCaseFile $ExerciseCaseFile
 * @property Commit $Commit
 */
class ExerciseCase extends AppModel {

	/**
	 * Validation rules
	 *
	 * @var array
	 */
	public $validate = array(
		'exercise_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'input_type' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'output_type' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'show_input' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'show_expected_output' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
//		'maxmemsize' => array(
//			'numeric' => array(
//				'rule' => array('numeric'),
//				//'message' => 'Your custom message here',
//				//'allowEmpty' => false,
//				//'required' => false,
//				//'last' => false, // Stop validation after this rule
//				//'on' => 'create', // Limit validation to 'create' or 'update' operations
//			),
//		),
		'cputime' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
//		'stacksize' => array(
//			'numeric' => array(
//				'rule' => array('numeric'),
//				//'message' => 'Your custom message here',
//				//'allowEmpty' => false,
//				//'required' => false,
//				//'last' => false, // Stop validation after this rule
//				//'on' => 'create', // Limit validation to 'create' or 'update' operations
//			),
//		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

	/**
	 * belongsTo associations
	 *
	 * @var array
	 */
	public $belongsTo = array(
		'Exercise' => array(
			'className' => 'Exercise',
			'foreignKey' => 'exercise_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	/**
	 * hasMany associations
	 *
	 * @var array
	 */
	public $hasMany = array(
		'ExerciseCaseFile' => array(
			'className' => 'ExerciseCaseFile',
			'foreignKey' => 'exercise_case_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

//	public $hasAndBelongsToMany = array(
//		'Commit' => array(
//			'className' => 'Commit',
//			'joinTable' => 'commits_exercise_cases',
//			'foreignKey' => 'exercise_case_id',
//			'associationForeignKey' => 'commit_id',
//			'unique' => 'keepExisting',
//			'conditions' => '',
//			'fields' => '',
//			'order' => '',
//			'limit' => '',
//			'offset' => '',
//			'finderQuery' => '',
//			'deleteQuery' => '',
//			'insertQuery' => ''
//		)
//	);

	public function getExerciseId() {
		if (is_numeric($this->id)) {
			$case_data = $this->findById($this->id);
			return $case_data['ExerciseCase']['exercise_id'];
		} else {
			return null;
		}
	}

	public function getExercise($id = null,$fields = null) {
		if (!is_null($id)) {
			$this->id = $id;
		}
		if ($fields == "all") {
			$fields = null;
		}else if(is_null($fields)) {
			$fields = array('id');
		}
		if (isset($this->id)) {
			App::uses('Exercise', 'Model');
			$model = new Exercise();
			$model->recursive = -1;
			$return = $model->findById($this->field('exercise_id'),$fields);
			if (count($return) > 0) {
				return $return;
			} else {
				return false;
			}
		}
		return false;
	}

	public function afterFind($results, $primary = false) {
//            foreach ($results as $k => $case) {
//                App::uses('CommitsExerciseCase', 'Model');
//                $commitsExerciseCaseModel = new CommitsExerciseCase();
//                if (isset($case['ExerciseCase']['id']) && isset($case['ExerciseCase']['exercise_id'])) {
//                    $corrects = $commitsExerciseCaseModel->find('all',array('fields' => array('COUNT(*) as count','status'),'group' => array('status'),'conditions' => array('exercise_case_id' => $case['ExerciseCase']['id'], "commit_id IN (SELECT id FROM commits c1 WHERE exercise_id = ".$case['ExerciseCase']['exercise_id']." AND commit_time = (SELECT MAX(commit_time) FROM commits c2 WHERE c1.user_email = c2.user_email AND c1.exercise_id=c2.exercise_id GROUP BY user_email, exercise_id))")));
//                    $corrects = $commitsExerciseCaseModel->find('count',array('conditions' => array('exercise_case_id' => $case['ExerciseCase']['id'], "commit_id IN (SELECT id FROM commits c1 WHERE exercise_id = ".$case['ExerciseCase']['exercise_id']." AND commit_time = (SELECT MAX(commit_time) FROM commits c2 WHERE c1.user_email = c2.user_email AND c1.exercise_id=c2.exercise_id GROUP BY user_email, exercise_id))")));
//                    debug($corrects);
//                    
//                }
//            }
		return $results;
	}

	public function beforeSave($options = array()) {
		$this->data['ExerciseCase']['last_update'] = date('Y-m-d H:i:s');
		if (isset($this->data['ExerciseCase']['input']))
			$this->data['ExerciseCase']['input'] = str_replace("\r", "", $this->data['ExerciseCase']['input']);
		if (isset($this->data['ExerciseCase']['output']))
			$this->data['ExerciseCase']['output'] = str_replace("\r", "", $this->data['ExerciseCase']['output']);
	}

	public function afterSave($created,$options = array()) {
		App::uses('AwsCache', 'Model');
		$Cache = new AwsCache();
		$Cache->removeItem("exercise-" . $this->data['ExerciseCase']['exercise_id']);
		$this->updateExerciseCasesChange();
	}

	public function beforeDelete($cascade = true) {
		$this->updateExerciseCasesChange();
		$ExerciseCaseFile = ClassRegistry::init('ExerciseCaseFile');
		$files = $ExerciseCaseFile->findAllByExerciseCaseId($this->id,array("id"));
		foreach ($files as $eCF) {
			$ExerciseCaseFile->removeExerciseCaseFile($eCF["ExerciseCaseFile"]["id"]);
		}
		return true;
	}

	public function afterDelete() {
		$Archive = ClassRegistry::init('Archive');
		$Archive->deleteExerciseCaseInputFromAwsS3($this->id);
		$Archive->deleteExerciseCaseOutputFromAwsS3($this->id);
	}

	private function updateExerciseCasesChange () {
		App::uses('Exercise', 'Model');
		$exerciseModel = new Exercise();
		$exerciseModel->recursive = -1;
		if (isset($this->data['ExerciseCase']['exercise_id'])) {
			$exerciseModel->id = $this->data['ExerciseCase']['exercise_id'];
			$exerciseModel->updateExerciseCasesChange();
		} else if (isset($this->id)) {
			$exerciseId = $this->field("exercise_id");
			$exerciseModel->id = $exerciseId;
			$exerciseModel->updateExerciseCasesChange();
		}
	}

}
