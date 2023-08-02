<?php 
if ($exerciseCase['ExerciseCase']['output_type']==2) {
    echo $this->Form->input('output_error',array('label' => array('text' => __('Output Error Accepted')),'type' => 'text','disabled' => 'disabled','style' => 'width: 520px', 'value' => $exerciseCase['ExerciseCase']['abs_error']));
    echo $this->Form->input('output',array('label' => array('text' => __('Expected Output')),'type' => 'textarea', 'id' => 'copyable-field', 'disabled' => 'disabled','style' => 'width: 520px', 'value' => $exerciseCase['ExerciseCase']['output']));
} elseif ($exerciseCase['ExerciseCase']['output_type']==3) {
    echo __("This output is not an text");
} else {
    echo $this->Form->input('output',array('label' => array('text' => __('Expected Output')),'type' => 'textarea', 'id' => 'copyable-field', 'disabled' => 'disabled','style' => 'width: 520px', 'value' => $exerciseCase['ExerciseCase']['output']));
}