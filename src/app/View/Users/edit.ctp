<div class="container-fluid">
    <div class="row-fluid">
        <div class="users index widget span12">
            <div class="widget-header">
                <h3><?php echo __('Users'); ?></h3>
            </div>
            <div class="widget-body">
                <?php echo $this->Form->create('User'); ?>
                <fieldset>
                    <?php
                    echo $this->Form->input('email', array('disabled' => 'true','class' => 'span4'));
                    echo $this->Form->input('name',array('class' => 'span4'));
                    echo $this->Form->input('type', array('type' => 'select', 'options' => $user_types, 'class' => 'span4'));
                    echo $this->Form->input('confirmed');
                    ?>
                </fieldset>
                <?php echo $this->Form->end(array('label' => __('Submit'), 'class' => 'btn btn-yellow ')); ?>
            </div>
        </div>
    </div>
</div>