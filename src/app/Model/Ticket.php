<?php
App::uses('AppModel', 'Model');
/**
 * Ticket Model
 *
 * @property User $User
 * @property User $User
 */
class Ticket extends AppModel {

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'message';

/**
 * Validation rules
 *
 * @var array
 */
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
		),
		'type' => array(
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
		'solved' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'priority' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'message' => array(
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
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'users_email',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
        
        public function getTypeList(){
            return array(__('Bug'),__('Issue'),__('Suggestion'),__('Request'));
        }
        private function getTypeByListIndex($index){
            $list = $this->getTypeList();
            return $list[$index];
        }

        public function getDefaultStatusValue(){
            return 0;
        }
        public function getClosedStatusValue(){
            return 1;
        }
        public function getStatusList(){
            return array(__('Open'),__('Closed'));
        }
        private function getStatusByListIndex($index){
            $list = $this->getStatusList();
            return $list[$index];
        }
        
        public function getPriorityList(){
            return array(__('Very High'),__('High'),__('Normal'),__('Low'),__('Very Low'));
        }
        private function getPriorityByListIndex($index){
            $list = $this->getPriorityList();
            return $list[$index];
        }
        
        public function afterFind($results, $primary = false) {
            parent::afterFind($results, $primary);
            if(isset($results[0]['Ticket'])){
                foreach ($results as $key => $ticket){
                    $results[$key]['Ticket']['type_name'] = $this->getTypeByListIndex($results[$key]['Ticket']['type']);
                    $results[$key]['Ticket']['status_name'] = $this->getStatusByListIndex($results[$key]['Ticket']['status']);
                    $results[$key]['Ticket']['priority_name'] = $this->getPriorityByListIndex($results[$key]['Ticket']['priority']);
                }
            }
            return $results;
        }
        
}
