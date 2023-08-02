<?php

$cakeDescription = __d('cake_dev', 'CakePHP: the rapid development php framework');
?>
<!DOCTYPE html>
<html>

<head>
  <?php echo $this->Html->charset(); ?>
  <title>
    <?php echo 'run.codes ' ?> -
    <?php echo __($title_for_layout); ?>
  </title>
  <?php
  echo $this->Html->meta('icon', $this->Html->url('/favicon.png'));

  echo $this->Html->css('bootstrap');
  echo $this->Html->css('bootstrap-responsive');
  echo $this->Html->css('general');
  echo $this->Html->css('colors');
  echo $this->Html->css('xcharts');

  echo $this->Html->script('jquery-2.1.0.min');
  echo $this->Html->script('bootstrap.min');
  echo $this->Html->script('libs/angular.min');
  //                echo $this->Html->script('public');


  echo $this->fetch('meta');
  echo $this->fetch('css');
  echo $this->fetch('script');
  ?>
  <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.min.css" />
  <script src="/js/public.js?ver=20140913"></script>
  <link rel="stylesheet" href="https://yandex.st/highlightjs/8.2/styles/default.min.css">
  <script src="https://yandex.st/highlightjs/8.2/highlight.min.js"></script>
  <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,700' rel='stylesheet' type='text/css'>
</head>

<body class="admin <?php echo (isset($secondMenu)) ? "fixed-top-offering" : "fixed-top"; ?>">
  <header class="admin-top fixed-top box">
    <div class="navbar navbar-inverse navbar-static-top">
      <div class="navbar-inner">
        <a class="brand hidden-small hidden-phone top-name" href="/home">run.codes <?php // echo Configure::read('version');
                                                                                    ?></a>
        <ol class="pull-left breadcrumb">
          <li><?php echo $this->Html->link(__('Home'), array('controller' => 'pages', 'action' => 'home_student'), array('class' => 'btn btn-yellow')); ?></li>
        </ol>
        <?php if ((isset($userOfferingsMenu) && is_array($userOfferingsMenu) && count($userOfferingsMenu) > 0) || $logged_user['type'] >= 2) : ?>
          <ul class="nav pull-left">
            <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo __("Professor Menu"); ?><b class="caret"></b></a>
              <ul class="dropdown-menu" style="opacity: 1">
                <?php if ($logged_user['type'] >= 2) : ?>
                  <li><?php echo $this->Html->link(__('Create New Offering'), array('controller' => 'Offerings', 'action' => 'add'), array('class' => '')); ?></li>
                  <li class="divider"></li>
                <?php endif;
                if (count($userOfferingsMenu) > 0) :
                ?>
                  <li class="nav-header"><?php echo __("Active Offerings"); ?></li>
                  <?php foreach ($userOfferingsMenu as $k => $off) : ?>
                    <li><?php echo $this->Html->link($off, array('controller' => 'Offerings', 'action' => 'view', $k), array('class' => '')); ?></li>
                <?php endforeach;
                endif;
                ?>
              </ul>
            </li>
          </ul>
        <?php endif; ?>
        <ol class="pull-left breadcrumb">
          <?php // if($logged_user['type']>=3):
          if (isset($breadcrumbs)) :
            foreach ($breadcrumbs as $b) :
              if ($b['link'] == "#") :
                //                                    if (strlen($b['text']) > 18) $b['text'] = substr($b['text'], 0, 18)."...";
          ?>
                <li class="active breadcrumb-normal"><span class="divider">»</span><?php echo $b['text']; ?></li>
              <?php else : ?>
                <li class="breadcrumb-normal"><span class="divider">»</span>
                  <?php
                  if (strlen($b['text']) > 10) $b['text'] = substr($b['text'], 0, 10) . "...";
                  echo $this->Html->link($b['text'], $b['link'], array('class' => '')); ?>
                </li>
              <?php endif; ?>
            <?php endforeach; ?>
          <?php endif; ?>
          <?php // endif;
          ?>
        </ol>
        <div class="pull-right header-buttons">
          <?php // echo $this->Html->link(__('Profile'), array('controller' => 'Users', 'action' => 'profile'), array('class' => 'btn btn-yellow hidden'));
          ?>
          <?php // echo $this->Html->link(__('Profile'), array('controller' => 'Users', 'action' => 'profile'), array('class' => 'btn btn-yellow'));
          ?>
          <?php echo $this->Html->link(__('FAQ'), '/faq/', array('class' => 'btn btn-yellow')); ?>
          <?php // echo $this->Html->link(__('Logout'), array('controller' => 'Users', 'action' => 'logout'), array('class' => 'btn btn-yellow'));
          ?>
        </div>
        <ul class="nav pull-right">
          <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $logged_user['email']; ?><b class="caret"></b></a>
            <ul class="dropdown-menu" style="opacity: 1">
              <li><?php echo $this->Html->link(__('Profile'), array('controller' => 'Users', 'action' => 'profile'), array('class' => '')); ?></li>
              <li><?php echo $this->Html->link(__('Logout'), array('controller' => 'Users', 'action' => 'logout'), array('class' => '')); ?></li>
              <?php if ((isset($logged_user['real_type']) && ($logged_user['real_type'] > 1)) || (($logged_user['onlyStudent']) || (isset($userOfferingsMenu) && is_array($userOfferingsMenu) && count($userOfferingsMenu) > 0))) : ?>
                <li class="divider"></li>
                <li class="nav-header"><?php echo __("View as"); ?></li>
                <li <?php if ($logged_user['type'] == 0 && $logged_user['onlyStudent']) : ?> class="active" <?php endif; ?>><?php echo $this->Html->link(__('Student'), array('controller' => 'Users', 'action' => 'viewAs', 0), array('class' => '')); ?></li>
                <?php if ($logged_user['real_type'] == 0) : ?>
                  <li <?php if ($logged_user['type'] == 0 && !$logged_user['onlyStudent']) : ?> class="active" <?php endif; ?>><?php echo $this->Html->link(__('Assistant Professor'), array('controller' => 'Users', 'action' => 'viewAs', 2), array('class' => '')); ?></li>
                <?php endif; ?>
                <?php if ($logged_user['real_type'] > 1) : ?>
                  <li <?php if ($logged_user['type'] == 2) : ?> class="active" <?php endif; ?>><?php echo $this->Html->link(__('Professor'), array('controller' => 'Users', 'action' => 'viewAs', 2), array('class' => '')); ?></li>
                <?php endif; ?>
                <?php if ($logged_user['real_type'] > 2) : ?>
                  <li <?php if ($logged_user['type'] >= 3) : ?> class="active" <?php endif; ?>><?php echo $this->Html->link(__('Admin'), array('controller' => 'Users', 'action' => 'viewAs', 4), array('class' => '')); ?></li>
                <?php endif; ?>
              <?php endif; ?>
            </ul>
          </li>
        </ul>
        <div class="pull-right header-text" style="height: 35px; line-height: 35px; color: white; padding-right: 10px">
          <p><?php echo __("Server's Clock"); ?>: <span id="server-time" style="font-weight: bold"><?php echo $datetime; ?></span></p>
        </div>
      </div>
    </div>
    <?php if (isset($secondMenu)) : ?>
      <div class="navbar second-nav">
        <div class="navbar-inner">
          <ul class="pull-left nav">
            <a class="brand" href="/home"><?php echo $secondMenuTitle; ?></a>
            <?php foreach ($secondMenu as $link => $item) : ?>
              <li><?php echo $this->Html->link($item, '#' . $link); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    <?php endif; ?>
  </header>
  <div class="main-container container-fluid" style="min-width: 1240px">
    <?php
    if ($logged_user['type'] >= 3) : ?>
      <aside class="sidebar hidden-phone">
        <nav class="admin-menu">
          <ul class="nav admin-nav-list">
            <li><a href="/home" <?php if ($controller == "Dashboard") : ?> class="active" <?php endif; ?>><i class="new-icon-graph"></i><?php echo __("Dashboard"); ?></a><?php if ($controller == "Dashboard") : ?><div class="arrow-left"></div> <?php endif; ?></li>
            <?php if ($logged_user['type'] > 2) : ?><li><a href="/Tickets" <?php if ($controller == "Tickets") : ?> class="active" <?php endif; ?>><i class="new-icon-tag"></i><?php echo __("Tickets"); ?></a><?php if ($controller == "Tickets") : ?><div class="arrow-left"></div> <?php endif; ?></li><?php endif; ?>
            <li><a href="/Commits" <?php if ($controller == "Commits") : ?> class="active" <?php endif; ?>><i class="new-icon-text_document"></i><?php echo __("Commits"); ?></a><?php if ($controller == "Commits") : ?><div class="arrow-left"></div> <?php endif; ?></li>
            <li><a href="/Exercises" <?php if ($controller == "Exercises") : ?> class="active" <?php endif; ?>><i class="new-icon-empty_document"></i><?php echo __("Exercises"); ?></a><?php if ($controller == "Exercises") : ?><div class="arrow-left"></div> <?php endif; ?></li>
            <li><a href="/Offerings" <?php if ($controller == "Offerings") : ?> class="active" <?php endif; ?>><i class="new-icon-edit"></i><?php echo __("Offerings"); ?></a><?php if ($controller == "Offerings") : ?><div class="arrow-left"></div> <?php endif; ?></li>
            <li><a href="/Users" <?php if ($controller == "Users") : ?> class="active" <?php endif; ?>><i class="new-icon-users"></i><?php echo __("Users"); ?></a><?php if ($controller == "Users") : ?><div class="arrow-left"></div> <?php endif; ?></li>
            <li><a href="/Courses" <?php if ($controller == "Courses") : ?> class="active" <?php endif; ?>><i class="new-icon-calendar"></i><?php echo __("Courses"); ?></a><?php if ($controller == "Courses") : ?><div class="arrow-left"></div> <?php endif; ?></li>
            <li><a href="/Universities" <?php if ($controller == "Universities") : ?> class="active" <?php endif; ?>><i class="new-icon-target"></i><?php echo __("Universities"); ?></a><?php if ($controller == "Universities") : ?><div class="arrow-left"></div> <?php endif; ?></li>
            <li><a href="/Alerts" <?php if ($controller == "Alerts") : ?> class="active" <?php endif; ?>><i class="new-icon-bookmark"></i><?php echo __("Alerts"); ?></a><?php if ($controller == "Alerts") : ?><div class="arrow-left"></div> <?php endif; ?></li>
            <li><a href="/AllowedFiles" <?php if ($controller == "AllowedFiles") : ?> class="active" <?php endif; ?>><i class="new-icon-text_document"></i><?php echo __("Allowed Files"); ?></a><?php if ($controller == "AllowedFiles") : ?><div class="arrow-left"></div> <?php endif; ?></li>
            <li><a href="/Messages" <?php if ($controller == "Messages") : ?> class="active" <?php endif; ?>><i class="new-icon-mail"></i><?php echo __("Messages"); ?></a><?php if ($controller == "Messages") : ?><div class="arrow-left"></div> <?php endif; ?></li>
            <li><a href="/Questions" <?php if ($controller == "Questions") : ?> class="active" <?php endif; ?>><i class="new-icon-documents"></i><?php echo __("FAQ"); ?></a><?php if ($controller == "Questions") : ?><div class="arrow-left"></div> <?php endif; ?></li>
            <li><a href="/Logs" <?php if ($controller == "Logs") : ?> class="active" <?php endif; ?>><i class="new-icon-list"></i><?php echo __("Log"); ?></a><?php if ($controller == "Logs") : ?><div class="arrow-left"></div> <?php endif; ?></li>
            <li><a href="https://ganglia.run.codes/" target="_blank"><i class="new-icon-graph"></i><?php echo __("Ganglia"); ?></a></li>
          </ul>
        </nav>
      </aside>
    <?php endif; ?>
    <div <?php if ($logged_user['type'] >= 3) : ?> class="content" <?php else : ?> class="content content-student" <?php endif; ?>>
      <?php echo $this->fetch('content'); ?>
    </div>
  </div>
  <footer>
    <?php // if($logged_user['type']>3):  echo $this->element('sql_dump'); endif;
    ?>
  </footer>
  <!--<script type="text/javascript">
//    $(document).ready(function(){
        //

//    });
    </script>-->
  <script src="//cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.jquery.min.js"></script>
  <?php if ($logged_user['type'] < 3) : ?>
  <?php endif; ?>
  <script>
    hljs.initHighlightingOnLoad();
    $(function() {
      var datetimeJson = $.parseJSON('<?php echo $datetimeJson; ?>');
      updateServerDateTimeClient(datetimeJson);
    });
    $('.chosen-unique-select').chosen({
      width: '100%'
    });
  </script>



</body>

</html>
