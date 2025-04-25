<!DOCTYPE html>
<html>
<!--
<style>
p {
  font-size: 24px;
}
</style>
-->

<head>
  <?php echo $this->Html->charset(); ?>
  <title>
    <?php echo $title_for_layout; ?>
  </title>
  <link href='//fonts.googleapis.com/css?family=Raleway:300,700' rel='stylesheet' type='text/css'>
  <?php

  echo $this->Html->meta('icon', $this->Html->url('/img/icon.png'));

  echo $this->Html->css('libs/bootstrap.min');
  echo $this->Html->css('libs/bootstrap-theme.min');
  echo $this->Html->css('libs/font-awesome.min');
  echo $this->Html->css('login2');

  echo $this->fetch('meta');
  echo $this->fetch('css');
  echo $this->fetch('script');

  echo $this->Html->script('jquery-2.0.3.min');
  echo $this->Html->script('libs/bootstrap.min');
  echo $this->Html->script('public.js');
  ?>

</head>

<body>
  <div class="welcome-area">
    <div class="welcome-area-content">
      <?php echo $this->element(Configure::read('Config.language') . DS . "homeRightBar"); ?>
      <?php echo "<p class=\"rc-description\">" . __("By browsing in %s you agree with the", __("run.codes")) . " ";
       echo $this->Html->link(__("Terms of Use"), '#', array('class' => 'modalTerms terms', 'data-toggle' => 'modal', 'data-target' => '#modalTerms')) . "</p>"; ?>
      <?php echo "<p class=\"rc-description\">Note que o runcodes não é um serviço mantido pelo STI, logo, em caso de problemas com a plataforma, entre em contato com <a href=\"mailto:runcodes@icmc.usp.br\">runcodes@icmc.usp.br</a></p>"; ?>
    </div>
  </div>
  <div class="login-area">
    <?php echo $this->fetch('content'); ?>
  </div>


  <div id="modalTerms" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="<?php echo __("Terms of Use"); ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel"><?php echo __("Terms of Use"); ?></h4>
        </div>
        <div class="modal-body">
          <?php echo $this->element(Configure::read('Config.language') . DS . "terms"); ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-dismiss="modal">Fechar</button>
        </div>
      </div>
    </div>
  </div>
</body>

</html>
