<div class="container-fluid">
    <div class="row-fluid">
        <div class="users index widget span12">
            <div class="widget-header">
                <h3><?php echo __('Allowed Files'); ?></h3>
            </div>
            <div class="widget-body">
                <?php echo $this->Form->create('AllowedFile'); ?>
                <fieldset>
                    <legend><?php echo __('Edit Allowed File'); ?></legend>
                    <?php
                    echo $this->Form->input('name', array('label' => array('text' => __('Name'),'class' => 'control-label'), 'type' => 'text','class' => 'span4','div' => array('class' => 'control-group'))); 
                    echo $this->Form->input('extension', array('label' => array('text' => __('Extension'),'class' => 'control-label'), 'type' => 'text','class' => 'span4','div' => array('class' => 'control-group'))); 
                    echo $this->Form->input('compilable', array('label' => array('text' => __('Compilable'),'class' => 'control-label'), 'type' => 'select','options'=>array(0=>__('No'),1=>__('Yes')),'class' => 'span4','div' => array('class' => 'control-group'))); 
                    echo $this->Form->input('compile_command', array('label' => array('text' => __('Compile Command'),'class' => 'control-label'), 'type' => 'text','class' => 'span4','div' => array('class' => 'control-group'))); 
                    echo $this->Form->input('run_command', array('label' => array('text' => __('Run Command'),'class' => 'control-label'), 'type' => 'text','class' => 'span4','div' => array('class' => 'control-group'))); 
                    
                    ?>
                </fieldset>
                <?php echo $this->Form->end(array('label' => __('Submit'), 'class' => 'btn btn-yellow ')); ?>
            </div>
        </div>
    </div>
</div>
