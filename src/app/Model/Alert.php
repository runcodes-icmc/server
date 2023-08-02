<?php
App::uses('AppModel', 'Model');
/**
 * Alert Model
 *
 * @property Offering $Offering
 * @property User $User
 */
class Alert extends AppModel {

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'title';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'type' => array(
			'checkType' => array(
				'rule' => array('checkType'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'recipients' => array(
			'checkRecipients' => array(
				'rule' => array('checkRecipients'),
				//'message' => 'Your custom message here',
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
		'valid' => array(
			'date' => array(
				'rule' => array('date'),
				'message' => 'The informed date is not valid',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'title' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
			'maxlength' => array(
				'rule' => array('maxlength',95),
				'message' => 'The title can not have more than 95 characters',
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
			'maxlength' => array(
				'rule' => array('maxlength',255),
				'message' => 'The message can not have more than 250 characters',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

        function checkType($value){
            if($value['type']>=0 && $value['type']<=3) return true;
            else return false;
        }
        function checkRecipients($value){
            if($value['recipients']>=0 && $value['recipients']<=2) return true;
            else return false;
        }
	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
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
        
        function beforeSave($options = array()){
            if($this->data[$this->alias]['offering_id']=="-1"){
        	$this->data[$this->alias]['offering_id'] = null;
            }
            return true;
        }
        
        function getAlertTypesList(){
            return array('0' => 'Warning (Yellow)', '1' => 'Danger (Red)', '2' => 'Info (Blue)', '3' => 'Success (Green)');
        }
}
