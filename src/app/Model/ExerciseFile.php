<?php
App::uses('AppModel', 'Model');
/**
 * ExerciseFile Model
 *
 * @property Exercise $Exercise
 */
class ExerciseFile extends AppModel {

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'path';
        

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
        
	public function getExerciseId() {
		if (is_numeric($this->id)) {
			$ex_data = $this->findById($this->id,array('exercise_id'));
			return $ex_data['ExerciseFile']['exercise_id'];
		} else {
			return null;
		}
	}

	public function removeFile ($id = null) {
		if (!is_null($id)) {
			$this->id = id;
		}
		$this->recursive = -1;
		if (!$this->exists()) {
			return false;
		}

		$exerciseId = $this->getExerciseId();

		$exerciseModel = ClassRegistry::init('Exercise');
		$offeringModel = ClassRegistry::init('Offering');
		$exerciseModel->id = $exerciseId;
		$offeringId = $exerciseModel->getOfferingId();
		$offeringModel->id = $offeringId;
		$courseId = $offeringModel->getCourseId();

		$dir = Configure::read('Upload.dir'). DS . "exercisefiles" . DS . $courseId . DS . $offeringId . DS . $exerciseId . DS;
		$path = $dir.$this->field('path');
		if (file_exists($path)) {
			unlink($path);
		}
		return $this->delete();
	}
}
