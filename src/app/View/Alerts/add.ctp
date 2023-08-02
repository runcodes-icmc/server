<div class="container-fluid">
    <div class="row-fluid">
        <div class="users index widget span12">
            <div class="widget-header">
                <h3><?php echo __('Alerts'); ?></h3>
            </div>
            <div class="widget-body">
                <?php echo $this->Form->create('Alert'); ?>
                <fieldset>
                    <legend><?php echo __('Add Alert'); ?></legend>
                    <?php
                    echo $this->Form->input('offering_id',array('class' => 'span4'));
                    echo $this->Form->input('type',array('class' => 'span4','type' => 'select', 'options' => $alert_types));
                    echo $this->Form->input('recipients',array('class' => 'span4','type' => 'select', 'options' => $enrollment_role));
                    echo $this->Form->input('title', array('label' => array('text' => __('Title'),'class' => 'control-label'), 'type' => 'text','class' => 'span4','div' => array('class' => 'control-group'))); 
                    echo $this->Form->input('message', array('label' => array('text' => __('Message'),'class' => 'control-label'), 'type' => 'text','class' => 'span4','div' => array('class' => 'control-group'))); 
                    echo $this->Form->input('valid', array('label' => array('text' => __('Valid until'),'class' => 'control-label'),'class' => 'span2','div' => array('class' => 'control-group'))); 
                    ?>
                </fieldset>
                <?php echo $this->Form->end(array('label' => __('Submit'), 'class' => 'btn btn-yellow ')); ?>
            </div>
        </div>
    </div>
</div>
