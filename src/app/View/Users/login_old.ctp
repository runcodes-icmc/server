<div class="login-box">
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span6">
                <?php echo $this->element(Configure::read('Config.language'). DS . "homeRightBar"); ?>
                <?php echo __("By browsing in %s you agree with the", __("run.codes"))." "; echo $this->Html->link(__("Terms of Use"),'#', array('class' => 'modalTerms terms')); ?>
            </div>
            <div class="span6">
                <?php echo $this->Form->create('User', array('class' => 'form-signin')); ?>
                <div class="sign-up-block">
                    <?php echo $this->Html->link(__('Sign Up Now!'), array('controller' => 'Users', 'action' => 'add'), array('class' => 'btn btn-large btn-inverse btn-block')); ?>
                    <?php echo $this->Html->link(__('Sign In with Linkedin')."!", array('controller' => 'Users', 'action' => 'linkedin'), array('class' => 'btn btn-large btn-primary btn-block')); ?>
                </div>
                
                <?php 
                $msg = $this->Session->flash('success');
                if (strlen(trim($msg)) > 0) {
                    ?>
                    <div class="alert alert-success">
                    <?php echo $msg; ?>
                    </div>
                <?php }else{ ?>
                        <h3><?php echo __('I\'m already registered!'); ?></h3>
                <?php 
                }
                $msg = $this->Session->flash('flash');
                if (strlen(trim($msg)) > 0) {
                    ?>
                    <div class="alert alert-error">
                    <?php echo $msg; ?>
                    </div>
                <?php } ?>
                
                
                <?php $msg = $this->Session->flash('auth');
                if (strlen(trim($msg)) > 0) {
                    ?>
                    <div class="alert alert-error">
                    <?php echo $msg; ?>
                    </div>
                <?php } ?>

                <?php echo $this->Form->input('email', array('label' => __('Email'), 'class' => 'input-block-level', 'placeholder' => __('Email'))); ?>
            <?php echo $this->Form->input('password', array('label' => __('Password'), 'class' => 'input-block-level', 'placeholder' => __('Password'))); ?>
            <?php echo $this->Form->end(array('label' => __('Login'), 'class' => 'btn btn-large btn-inverse btn-block')); ?>
            <?php echo $this->Html->link(__('Forgot your password?'), '#', array('class' => 'modalRecoverPassword')); ?>
            </div>
        </div>
    </div>
</div>
<div id="modalRecoverPassword" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <?php echo $this->Form->create('User',array('action' => 'recoveryPassword', 'class' => 'modal-form')); ?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel"><?php echo __("Forgot your password?"); ?></h3>
    </div>
    <div class="modal-body">
        <p>
        <?php echo __("We will send another password for you"); ?><br>
        </p>
        <?php echo $this->Form->input('email', array('label' => __('Email'), 'class' => 'input-block-level', 'placeholder' => __('Email'))); ?>
    </div>
    <div class="modal-footer">
        <?php echo $this->Form->end(array('label' => __('Confirm'), 'class' => 'btn btn-yellow')); ?>
    </div>
</div>
<div id="modalTerms" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel"><?php echo __("Terms of Use"); ?></h3>
    </div>
    <div class="modal-body">
        <?php echo $this->element(Configure::read('Config.language'). DS . "terms"); ?>
    </div>
    <div class="modal-footer">
    </div>
</div>
<script>
    $(document).ready(function() {
        $(".modalTerms").on('click', function(e) {
            e.preventDefault();
            $('#modalTerms').modal('show');
            
        });
    });
</script>
<script>
    $(document).ready(function() {
        $(".modalRecoverPassword").on('click', function(e) {
            e.preventDefault();
            $('#modalRecoverPassword').modal('show');
            $('#modalRecoverPassword #UserEmail').focus();
            $('#modalRecoverPassword #UserEmail').val("");
            
        });
    });
</script>