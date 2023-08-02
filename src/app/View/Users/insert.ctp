<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("Add User"); ?></h3>
                </div>
                <div class="panel-body">
                    <?php echo $this->Form->create('User'); ?>
                    <fieldset>
                        <?php echo $this->Form->input('name', array('label' => array('text' => __('Name'),'class' => 'control-label'), 'type' => 'text','class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                        <?php echo $this->Form->input('email', array('label' => array('text' => __('Email'),'class' => 'control-label'), 'type' => 'text','class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                        <?php echo $this->Form->input('password', array('label' => array('text' => __('Senha'),'class' => 'control-label'), 'type' => 'text','class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                        <?php echo $this->Form->input('type', array('label' => array('text' => __('Type'),'class' => 'control-label'), 'required', 'options' => $types,'class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                    </fieldset>
                    <?php echo $this->Form->end(array('label' => __('Cadastrar'), 'class' => 'btn btn-color-one ')); ?>
                </div>
            </div>
        </div>
    </div>
</div>