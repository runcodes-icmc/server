<?php
App::uses('AppModel', 'Model');

class CompilationFile extends AppModel {


	public $displayField = 'path';

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
			return $ex_data['CompilationFile']['exercise_id'];
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

		$dir = Configure::read('Upload.dir'). DS . "compilationfiles" . DS . $courseId . DS . $offeringId . DS . $exerciseId . DS;
		$path = $dir.$this->field('path');
		if (file_exists($path)) {
			unlink($path);
		}
		return $this->delete();
	}
}
