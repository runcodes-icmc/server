<?php
App::uses('AppModel', 'Model');

class Question extends AppModel {
    public $validate = array(
		'title' => array(
			'maxlength' => array(
				'rule' => array('maxlength',350),
				'message' => 'Required Field Title Max Length: 350 characters',
				//'allowEmpty' => false,
				'required' => true,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'tags' => array(
			'maxlength' => array(
				'rule' => array('maxlength',350),
				'message' => 'Required Field Tags Max Length: 350 characters',
				//'allowEmpty' => false,
				'required' => true,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'text' => array(
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
    
    public function afterFind($results, $primary = false) {
        foreach ($results as $key => $val) {
            if (isset($val['Question']['text'])) {
                $text = $val['Question']['text'];
                $text = str_replace("</code>", "<code>", $text);
                $textArr = explode("<code>", $text);
                $newText = "";
                foreach ($textArr as $k => $textPart) {
                    if ($k % 2 == 0) {
                        $newText .= nl2br($textPart);
                    } else {
                        $newText .= "<pre><code>".h($textPart)."</code></pre>";
                    }
                }
                $results[$key]['Question']['text'] = $newText;
            }
        }
        return $results;
    }
}
