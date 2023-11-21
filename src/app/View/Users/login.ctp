<div class="login-box">
  <div class="row-fluid">
    <div class="col-md-12">
      <?php echo $this->Form->create('User', array('class' => 'form-signin')); ?>
      <div class="sign-up-block">
        <?php echo $this->Html->link(__('Sign Up Now!'), array('controller' => 'Users', 'action' => 'add'), array('class' => 'btn btn-lg btn-white-outline btn-block')); ?>
      </div>

      <?php
      $msg = $this->Session->flash('success');
      if (strlen(trim($msg)) > 0) {
      ?>
        <div class="alert alert-success">
          <?php echo $msg; ?>
        </div>
      <?php } else { ?>
        <h3><?php echo __('I\'m already registered!'); ?></h3>
      <?php
      }
      $msg = $this->Session->flash('flash');
      if (strlen(trim($msg)) > 0) {
      ?>
        <div class="alert alert-danger">
          <?php echo $msg; ?>
        </div>
      <?php } ?>


      <?php $msg = $this->Session->flash('auth');
      if (strlen(trim($msg)) > 0) {
      ?>
        <div class="alert alert-danger">
          <?php echo $msg; ?>
        </div>
      <?php } ?>
      <div class="form-group">
        <?php echo $this->Form->input('email', array('label' => false, 'class' => 'form-control', 'placeholder' => __('Email'))); ?>
      </div>
      <div class="form-group">
        <?php echo $this->Form->input('password', array('label' => false, 'class' => 'form-control', 'placeholder' => __('Password'))); ?>
      </div>
      <?php echo $this->Form->end(array('label' => __('Login'), 'class' => 'btn btn-lg btn-white-outline btn-block')); ?>
      <?php if (isset($confirmLink) && $confirmLink) : ?>
        <br>
        <div class="alert alert-info">
          Se você não recebeu seu email para confirmação do cadastro, <?php echo $this->Form->postLink("clique aqui", "/Users/sendConfirmationEmail/" . $this->request->data["User"]["email"], array("style" => "color: black")); ?> que enviaremos novamente. Caso você continue sem receber, envie um email para runcodes@icmc.usp.br
        </div>
      <?php endif; ?>
      <?php echo $this->Html->link(__('Forgot your password?'), '#', array('class' => 'modalRecoverPassword pull-right login-box-link', 'data-toggle' => 'modal', 'data-target' => '#modalRecoverPassword')); ?>
    </div>
  </div>
</div>

<div id="modalRecoverPassword" class="modal fade" role="dialog" aria-labelledby="<?php echo __("Forgot your password?"); ?>">
  <div class="modal-dialog">
    <div class="modal-content">
      <?php echo $this->Form->create('User', array('action' => 'recoveryPassword', 'class' => 'modal-form')); ?>
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo __("Forgot your password?"); ?></h4>
      </div>
      <div class="modal-body">
        <p>
          <?php echo __("We will send another password for you"); ?><br>
        </p>
        <div class="form-group">
          <?php echo $this->Form->input('email', array('label' => __('Email'), 'class' => 'form-control', 'placeholder' => __('Email'))); ?>
        </div>
      </div>
      <div class="modal-footer">
        <?php echo $this->Form->end(array('label' => __('Confirm'), 'class' => 'btn btn-primary')); ?>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
