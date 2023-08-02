<?php
App::uses('AppModel', 'Model');

class Enrollment extends AppModel {

	public $validate = array(
		'offering_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
			'offeringIdAndUserEmailUnique' => array(
				'rule' => array('offeringIdAndUserEmailUnique'),
				'message' => "Your already is enrolled in this offering",
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
			'offeringExists' => array(
				'rule' => array('offeringExists'),
				'message' => "This offering is not available for new enrollments",
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'user_email' => array(
			'email' => array(
				'rule' => array('email'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'role' => array(
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
        
    public function offeringIdAndUserEmailUnique($offering_id) {
        $count = $this->find('count',array('conditions' => array('user_email' => $this->data['Enrollment']['user_email'],'offering_id' => $this->data['Enrollment']['offering_id'])));
        return ($count == 0) ? true : false;
    }

    public function offeringExists($offering_id) {
        App::uses('Offering', 'Model');
        $offeringModel = new Offering();
        $offeringModel->recursive = -1;
        $count = $offeringModel->find('count',array('conditions' => array('id' => $offering_id)));
        return ($count == 1) ? true : false;
    }

	public $belongsTo = array(
		'Offering' => array(
			'className' => 'Offering',
			'foreignKey' => 'offering_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_email',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

    public function beforeFind ($query) {
        parent::beforeFind($query);
        if (!isset($query['conditions']['banned'])) {
            $query['conditions']['banned'] = false;
        }
        return $query;
    }
        
    public function afterFind($results, $primary = false) {
        parent::afterFind($results);
        $enrollment_role = array('0' => 'Aluno', '1' => 'Monitor', '2' => 'Professor');
        foreach($results as $key => $val){
            if (isset($val['Enrollment']['role'])){
                $results[$key]['Enrollment']['role_name']=$enrollment_role[$val['Enrollment']['role']];
            }
        }
        return $results;
    }

    public function getStudentRole () {
        return 0;
    }

    public function getAssistantRole () {
        return 1;
    }

    public function getProfessorRole () {
        return 2;
    }

    public function isEnrolled($user_email,$offering_id) {
        if (count($this->findByUserEmailAndOfferingId($user_email,$offering_id)) == 0) {
            return false;
        }
        return true;
    }

    public function isEnrolledAsProfessorOrAssistant($user_email,$offering_id) {
        if (count($this->findByUserEmailAndOfferingIdAndRole($user_email,$offering_id,2)) > 0) {
            return true;
        }
        if (count($this->findByUserEmailAndOfferingIdAndRole($user_email,$offering_id,1)) > 0) {
            return true;
        }
        return false;
    }

    public function isEnrolledAsProfessor($user_email,$offering_id) {
        if (count($this->findByUserEmailAndOfferingIdAndRole($user_email,$offering_id,2)) == 0) {
            return false;
        }
        return true;
    }

    public function getOfferingId() {
        if (is_numeric($this->id)) {
            $this->recursive = -1;
            return $this->field('offering_id');
        }
        return null;
    }

    public function getOfferingsByUser($user_email,$onlyProfessorOrAssistant = false) {
        $this->recursive = 0;
        $conditions = array('Offering.end_date > NOW()','user_email' => $user_email);
        if ($onlyProfessorOrAssistant) {
            $conditions = array_merge($conditions, array('role >' => 0));
        }
        $list = array();
        $enrollments = $this->find('all',array('conditions' => $conditions, 'fields' => array('offering_id')));
        foreach ($enrollments as $enroll) {
            array_push($list, $enroll['Enrollment']['offering_id']);
        }
        return $list;
    }
        
        
}
