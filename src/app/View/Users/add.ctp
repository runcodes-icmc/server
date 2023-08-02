<div class="login-box">
    <div class="row-fluid">
        <div class="col-md-12">
            <?php echo $this->Form->create('User', array('class' => 'form form-signin')); ?>
            <h3><?php echo __('Sign Up!'); ?></h3>
            <?php $msg = $this->Session->flash();
            if (strlen(trim($msg)) > 0) {
                ?>
                <div class="alert alert-danger">
                    <?php echo $msg; ?>
                </div>
            <?php } ?>
            <?php if (1 == 2) : ?>
            <div class="alert alert-danger">
                <?php echo __('Aviso aos usuários da USP: Estamos com problemas de envio de emails para o domínio usp.br. Se possível, utilize um email alternativo para o cadastro'); ?>
            </div>
            <?php endif; ?>
            <div class="form-group">
                <?php echo $this->Form->input('email', array('label' => false, 'class' => 'form-control','value' => $invite, 'placeholder' => __('Email'))); ?>
            </div>
            <div class="form-group">
            <?php echo $this->Form->input('name', array('label' => false, 'class' => 'form-control', 'placeholder' => __('Name'))); ?>
            </div>
            <div class="form-group">
                <?php echo $this->Form->input('password', array('label' => false, 'class' => 'form-control', 'placeholder' => __('Password'))); ?>
            </div>
            <div class="form-group">
                <?php echo $this->Form->input('confirm_password', array('label' => false, 'type' => 'password', 'class' => 'form-control', 'placeholder' => __('Confirm Password'))); ?>
            </div>
            <div class="form-group">
                <?php echo $this->Form->end(array('label' => __('Sign Up'), 'class' => 'btn btn-lg btn-white-outline btn-block')); ?>
            <?php echo __("By clicking in %s you agree with the", __("Sign Up"))." "; echo $this->Html->link(__("Terms of Use"),'#', array('class' => 'modalTerms terms','data-toggle' => 'modal', 'data-target' => '#modalTerms')); ?>
            </div>
        </div>
    </div>
</div>