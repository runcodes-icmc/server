<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class University extends AppModel {

    public $virtualFields = array(
        'both' => "University.abbreviation || ' - ' || University.name",
        'isPaid' => "CASE WHEN University.type=1 THEN false ELSE true END"
    );

    public $displayField = 'both';
    
    public $validate = array(
            'abbreviation' => array(
                'minlength' => array(
                    'rule' => array('minlength', 2),
                    'message' => 'The abbreviation must have at least 2 characters',
                    //'allowEmpty' => false,
                    //'required' => false,
                    //'last' => false, // Stop validation after this rule
                ),
                'maxlength' => array(
                    'rule' => array('maxlength', 15),
                    'message' => 'The abbreviation must have at most 15 characters',
                    //'allowEmpty' => false,
                    //'required' => false,
                    //'last' => false, // Stop validation after this rule
                ),
                'isUnique' => array(
                    'rule' => array('isUnique'),
                    'message' => 'The system already has an university registered with this abbreviation',
                    //'allowEmpty' => false,
                    //'required' => false,
                    //'last' => false, // Stop validation after this rule
                ),
            ),
            'name' => array(
                'minlength' => array(
                    'rule' => array('minlength', 5),
                    'message' => 'The university name must have at least 5 characters',
                    //'allowEmpty' => false,
                    //'required' => false,
                    //'last' => false, // Stop validation after this rule
                ),
                'maxlength' => array(
                    'rule' => array('maxlength', 200),
                    'message' => 'The university name must have at most 200 characters',
                    //'allowEmpty' => false,
                    //'required' => false,
                    //'last' => false, // Stop validation after this rule
                ),
                'isUnique' => array(
                    'rule' => array('isUnique'),
                    'message' => 'The system already has an university registered with this name',
                    //'allowEmpty' => false,
                    //'required' => false,
                    //'last' => false, // Stop validation after this rule
                ),
            ),
            'student_identifier_text' => array(
                'minlength' => array(
                    'rule' => array('minlength', 1),
                    'message' => 'The student identifier text must have at least 1 character',
                    //'allowEmpty' => false,
                    //'required' => false,
                    //'last' => false, // Stop validation after this rule
                ),
                'maxlength' => array(
                    'rule' => array('maxlength', 35),
                    'message' => 'The student identifier text must have at most 35 characters',
                    //'allowEmpty' => false,
                    //'required' => false,
                    //'last' => false, // Stop validation after this rule
                ),
            ),
        'type' => array(
            'inList' => array(
                'rule' => array('inList', array('1','2','3','50')),
                'message' => 'Invalid university type',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
            )
        )
        );

    public function getTypesList () {
        return array(1 => __("Public University"),2 => __("Private University"), 3 => __("High School"));
    }
    public function getAdminTypesList () {
        return array(1 => __("Public University"),2 => __("Private University"), 3 => __("High School"), 50 => __("Reserved run.codes"));
    }

    public function getTypesKeys () {
        return array_keys($this->getTypesList());
    }
}
?>