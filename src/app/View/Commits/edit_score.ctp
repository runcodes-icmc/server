<?php echo $this->Form->create('Commit',array('controller' => 'Commit', 'action' => 'editScore')); ?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel"><?php echo __("Edit Score"); ?></h3>
</div>
<div class="modal-body">
    <p>
    <?php echo __("Submitted by: %s (%s)", $commit['User']['name'], $commit['User']['email']); ?><br>
    <?php echo __("Date: %s", $this->Time->format('d/m/Y H:i:s',$commit['Commit']['commit_time'])); ?><br>
    <?php echo __("Correct Cases: %s", $commit['Commit']['corrects']); ?><br>
    <?php echo $this->Form->input('id'); ?>
    <?php echo $this->Form->input('score', array('label' => array('text' => __('New Score'),'class' => 'control-label'), 'type' => 'number','step' => '0.01','class' => 'span4','div' => array('class' => 'control-group'))); ?>
    <?php echo $this->Form->input('status', array('label' => array('text' => __('Status'),'class' => 'control-label'), 'type' => 'select', 'options' => $statusList,'class' => 'span4','div' => array('class' => 'control-group'))); ?>
    
</div>
<div class="modal-footer">
    <?php echo $this->Form->end(array('label' => __('Confirm Change'), 'class' => 'btn btn-yellow ')); ?>
</div>