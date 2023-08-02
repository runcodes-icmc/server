<?php
App::uses('AppModel', 'Model');
/**
 * ExerciseCaseFile Model
 *
 * @property ExerciseCase $ExerciseCase
 */
class ExerciseCaseFile extends AppModel {

    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = array(
        'exercise_case_id' => array(
            'numeric' => array(
                'rule' => array('numeric'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'path' => array(
            'notempty' => array(
                'rule' => array('notempty'),
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
        'ExerciseCase' => array(
            'className' => 'ExerciseCase',
            'foreignKey' => 'exercise_case_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );

    public function getExerciseCaseId() {
        if (is_numeric($this->id)) {
            $case_data = $this->findById($this->id,array('exercise_case_id'));
            return $case_data['ExerciseCaseFile']['exercise_case_id'];
        } else {
            return null;
        }
    }

    public function getExerciseCase($id = null,$fields = null) {
        if (!is_null($id)) {
            $this->id = $id;
        }
        if ($fields == "all") {
            $fields = null;
        }else if(is_null($fields)) {
            $fields = array('id');
        }
        if (isset($this->id)) {
            App::uses('ExerciseCase', 'Model');
            $model = new ExerciseCase();
            $model->recursive = -1;
            $return = $model->findById($this->field('exercise_case_id'),$fields);
            if (count($return) > 0) {
                return $return;
            } else {
                return false;
            }
        }
        return false;
    }

    public function removeExerciseCaseFile ($id = null) {
        if (!is_null($id)) {
            $this->id = $id;
        }
        $this->recursive = -1;
        if (!$this->exists()) {
            return false;
        }

        $this->id = $id;
        $this->removeFile();
        return $this->delete();
    }

    public function removeFile ($id = null) {
        if (!is_null($id)) {
            $this->id = $id;
        }
        $this->recursive = -1;
        if (!$this->exists()) {
            return false;
        }
        $exerciseCaseId = $this->getExerciseCaseId();

//        $exerciseCaseModel = ClassRegistry::init('ExerciseCase');
//        $exerciseModel = ClassRegistry::init('Exercise');
//        $offeringModel = ClassRegistry::init('Offering');
//        $exerciseCaseModel->id = $exerciseCaseId;
//        $exerciseId = $exerciseCaseModel->getExerciseId();
//        $exerciseModel->id = $exerciseId;
//        $offeringId = $exerciseModel->getOfferingId();
//        $offeringModel->id = $offeringId;
//        $courseId = $offeringModel->getCourseId();

        $Archive = ClassRegistry::init('Archive');
        $Archive->deleteExerciseCaseFileFromAwsS3($exerciseCaseId,$this->field('path'));

//            $dir = Configure::read('Upload.dir'). DS . "inputfiles" . DS . $courseId . DS . $offeringId . DS . $exerciseId . DS . $exerciseCaseId . DS;
//            $path = $dir.$this->field('path');
//            if (file_exists($path)) {
//                unlink($path);
//            }
        return $this->delete();
    }
}
