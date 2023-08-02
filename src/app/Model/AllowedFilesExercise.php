<?php
App::uses('AppModel', 'Model');
/**
 * ExercisesAllowedFile Model
 *
 * @property Exercise $Exercise
 * @property AllowedFile $AllowedFile
 */
class AllowedFilesExercise extends AppModel {
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
		),
		'AllowedFile' => array(
			'className' => 'AllowedFile',
			'foreignKey' => 'allowed_file_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	public function afterSave($created,$options = array()) {
		if (isset($this->data['AllowedFilesExercise']['exercise_id'])) {
			App::uses('AwsCache', 'Model');
			$Cache = new AwsCache();
			$Cache->removeItem("exercise-" . $this->data['AllowedFilesExercise']['exercise_id']);
			//$this->updateExerciseCasesChange();
		}
	}
}
