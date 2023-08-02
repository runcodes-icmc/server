<?php
App::uses('AppModel', 'Model');
/**
 * Offering Model
 *
 * @property Course $Course
 */
class Offering extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'course_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Please, select a valid course',
				'allowEmpty' => false,
				'required' => true,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
        'end_date' => array(
			'maxlength' => array(
				'rule' => array('date'),
				'message' => 'Insert a valid date',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);
        
        public function afterFind($results, $primary = false) {
            parent::afterFind($results);
            foreach($results as $key => $val){
                if(is_numeric($key)){
                    if(isset($results[$key]['Offering']['id']))
                        $results[$key]['Offering']['num_participants']=($c = $this->Enrollment->find('count',array('conditions'=>array('offering_id' => $results[$key]['Offering']['id'])))) ? $c : 0;
                }
            }
            return $results;
        }

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Course' => array(
			'className' => 'Course',
			'foreignKey' => 'course_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
        public $hasMany = array(
		'Exercise' => array(
			'className' => 'Exercise',
			'foreignKey' => 'offering_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => 'Exercise.deadline ASC',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
                'Enrollment' => array(
			'className' => 'Enrollment',
			'foreignKey' => 'offering_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => 'role DESC',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
        );
        
        public function getCourseId() {
            if (isset($this->id)) {
                $offering_data = $this->findById($this->id,array('course_id'));
                return $offering_data['Offering']['course_id'];
            } else {
                return null;
            }
        }
        
        public function isEnrollmentCodeUsed ($code) {
            return ($this->find('count',array('conditions' => array('enrollment_code' => $code))) > 0);
        }
}
