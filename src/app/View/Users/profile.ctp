<div class="container-fluid" ng-controller="FormProfileController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __('Profile'); ?></h3>
                </div>
                <div class="panel-body" ng-init="university='<?php echo $user['User']['university_id']; ?>'">
                    <?php echo $this->Form->create('User', array('action'=> 'profile')); ?>
                    <fieldset>
                        <?php echo $this->Form->input('name', array('label' => array('text' => __('Name'),'class' => 'control-label'), 'type' => 'text', 'required', 'value' => $user['User']['name'], 'class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                        <?php echo $this->Form->input('university_id', array('label' => array('text' => __('University'),'class' => 'control-label'), 'required','ng-model' => 'university', 'ng-change' => 'loadUniversityInfo();', 'class' => 'form-control chosen-unique-select', 'placeholder' => __('New Password'),'div' => array('class' => 'form-group'))); ?>
                        <?php echo $this->Form->input('identifier', array('label' => array('text' => "{{ identifierText }}",'class' => 'control-label', 'id' => 'UserIdentifierLabel'),'value' => $user['User']['identifier'], 'type' => 'text', 'required', 'ng-init' => "identifierText='".__('Student ID')."';loadUniversityInfo();", 'class' => 'form-control','div' => array('class' => 'form-group'))); ?>

                    </fieldset>
                    <?php echo $this->Form->end(array('label' => __('Confirm Changes'), 'class' => 'btn btn-color-one ')); ?>
                </div>
            </div>
        </div>
    </div>
    <?php if ($user['User']['source'] == 0) : ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __('Change Password'); ?></h3>
                </div>
                <div class="panel-body">
                    <?php echo $this->Form->create('User', array('action'=> 'profile')); ?>
                    <fieldset>
                        <?php echo $this->Form->input('old_password', array('label' => array('text' => __('Current Password'),'class' => 'control-label'), 'type' => 'password', 'required', 'class' => 'form-control', 'placeholder' => __('Current Password'),'div' => array('class' => 'form-group'))); ?>
                        <?php echo $this->Form->input('password', array('label' => array('text' => __('New Password'),'class' => 'control-label'), 'type' => 'password', 'required', 'class' => 'form-control', 'placeholder' => __('New Password'),'div' => array('class' => 'form-group'))); ?>
                        <?php echo $this->Form->input('confirm_password', array('label' => array('text' => __('Confirm Password'),'class' => 'control-label'), 'type' => 'password', 'required', 'class' => 'form-control', 'placeholder' => __('Confirm Password'),'div' => array('class' => 'form-group'))); ?>

                    </fieldset>
                    <?php echo $this->Form->end(array('label' => __('Confirm Changes'), 'class' => 'btn btn-color-one ')); ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>