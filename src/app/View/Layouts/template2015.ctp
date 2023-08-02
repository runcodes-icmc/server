<!DOCTYPE html>
<html>

<head>
  <?php echo $this->Html->charset(); ?>
  <title>
    <?php echo $title_for_layout; ?>
  </title>
  <link href='//fonts.googleapis.com/css?family=Raleway:300,700' rel='stylesheet' type='text/css'>
  <link href='//fonts.googleapis.com/css?family=Open+Sans:600,700' rel='stylesheet' type='text/css'>
  <?php

  echo $this->Html->meta('icon', $this->Html->url('//d7ghnchxvmryx.cloudfront.net/img/icon.png'));
  //        echo $this->Html->css('libs/bootstrap.min');
  //        echo $this->Html->css('libs/jasny-bootstrap.min');
  //        echo $this->Html->css('libs/font-awesome.min');
  //        echo $this->Html->css('libs/bootstrap-datetimepicker.min');
  //        echo $this->Html->css('libs/rickshaw.min');
  //        echo $this->Html->css('libs/summernote');
  //        echo $this->Html->css('libs/summernote-bs3');
  //        echo $this->Html->css('libs/chosen.min');
  //        echo $this->Html->css('libs/zenburn');
  ?>
  <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/css/bootstrap.min.css" />
  <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/jasny-bootstrap/3.1.3/css/jasny-bootstrap.min.css" />
  <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css" />
  <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.42/css/bootstrap-datetimepicker.min.css" />
  <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/rickshaw/1.5.1/rickshaw.min.css" />
  <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/summernote/0.6.1/summernote.min.css" />
  <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/summernote/0.6.1/summernote-bs3.min.css" />
  <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.min.css" />
  <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.4/styles/zenburn.min.css" />
  <?php
  echo $this->Html->css('template2015.min');

  echo $this->fetch('meta');
  echo $this->fetch('css');
  echo $this->fetch('script');

  ?>

</head>

<body ng-app="runcodesApp">
  <nav class="navbar navbar-fixed-top color-one">
    <div class="container-fluid">
      <div class="navbar-header hidden-xs hidden-sm">
        <a class="navbar-brand-img" href="/"><img src="//d7ghnchxvmryx.cloudfront.net/img/logo.png" class="img-responsive"></a>
      </div>
      <div class="collapse navbar-collapse">
        <ul class="nav navbar-nav">
          <?php if ($logged_user['type'] >= 3) : ?>
            <li class="home color-three"><a id="btnAdminMenu" class="hover-text-color-three" href="#"><i class="fa fa-bars"></i></a></li>
          <?php endif; ?>
          <li class="home color-two"><a class="hover-text-color-three" href="/"><i class="fa fa-home"></i></a></li>
          <?php if ((isset($userOfferingsMenu) && is_array($userOfferingsMenu) && count($userOfferingsMenu) > 0) || $logged_user['type'] >= 2) : ?>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle hover-text-color-three" data-toggle="dropdown" role="button" aria-expanded="false"><?php echo __("Professor Menu"); ?> <i class="fa fa-angle-double-down"></i> </a>
              <ul class="dropdown-menu">
                <?php if ($logged_user['type'] >= 3) : ?>
                  <li><?php echo $this->Html->link(__('Exercises Public Database'), array('controller' => 'PublicExercises'), array('class' => '')); ?></li>
                <?php endif; ?>
                <?php if ($logged_user['type'] >= 2) : ?>
                  <li><?php echo $this->Html->link(__('Create New Offering'), array('controller' => 'Offerings', 'action' => 'add'), array('class' => '')); ?></li>
                  <!--                                    <li>--><?php //echo $this->Html->link(__('Exercises Public Database'), array('controller' => 'PublicExercises'), array('class' => ''));
                                                                  ?><!--</li>-->
                  <li class="divider"></li>
                <?php endif;
                if (count($userOfferingsMenu) > 0) :
                ?>
                  <li class="nav-header text-color-two"><?php echo __("Active Offerings"); ?></li>
                  <?php foreach ($userOfferingsMenu as $k => $off) : ?>
                    <li><?php echo $this->Html->link($off, array('controller' => 'Offerings', 'action' => 'view', $k), array('class' => '')); ?></li>
                <?php endforeach;
                endif;
                ?>
              </ul>
            </li>
          <?php endif; ?>
        </ul>
        <ul id="nav-right-menu" class="nav navbar-nav navbar-right hidden">
          <li class="dropdown user-menu"><a href="#" class="dropdown-toggle hover-text-color-three" data-toggle="dropdown"><?php echo $logged_user['email']; ?> <i class="fa fa-angle-double-down"></i></a>
            <ul class="dropdown-menu color-light">
              <li><?php echo $this->Html->link(__('Profile'), array('controller' => 'Users', 'action' => 'profile'), array('class' => 'hover-color-three')); ?></li>
              <?php if ((isset($logged_user['real_type']) && ($logged_user['real_type'] > 1)) || (($logged_user['onlyStudent']) || (isset($userOfferingsMenu) && is_array($userOfferingsMenu) && count($userOfferingsMenu) > 0))) : ?>
                <li class="divider"></li>
                <li class="nav-header text-color-two"><?php echo __("View as"); ?></li>
                <li>
                  <div class="btn-group <?php echo ($logged_user['real_type'] > 2) ? "three-options" : "two-options"; ?>" role="group" aria-label="viewAs">
                    <?php echo $this->Html->link('<i class="fa fa-male"></i><br>' . __('Student'), array('controller' => 'Users', 'action' => 'viewAs', 0), array('escape' => false, 'class' => 'btn btn-default ' . ((($logged_user['type'] == 0 && $logged_user['onlyStudent'])) ? "active" : ""))); ?>

                    <?php if ($logged_user['real_type'] == 0) : ?>
                      <?php echo $this->Html->link('<i class="fa fa-university"></i><br>' . __('Assistant Professor'), array('controller' => 'Users', 'action' => 'viewAs', 2), array('escape' => false, 'class' => 'btn btn-default ' . (((!$logged_user['onlyStudent'])) ? "active" : ""))); ?>
                    <?php endif; ?>

                    <?php if ($logged_user['real_type'] > 1) : ?>
                      <?php echo $this->Html->link('<i class="fa fa-university"></i><br>' . __('Professor'), array('controller' => 'Users', 'action' => 'viewAs', 2), array('escape' => false, 'class' => 'btn btn-default ' . ((($logged_user['type'] == 2)) ? "active" : ""))); ?>
                    <?php endif; ?>
                    <?php if ($logged_user['real_type'] > 2) : ?>
                      <?php echo $this->Html->link('<i class="fa fa-code"></i><br>' . __('Admin'), array('controller' => 'Users', 'action' => 'viewAs', 4), array('escape' => false, 'class' => 'btn btn-default ' . ((($logged_user['type'] >= 3)) ? "active" : ""))); ?>
                    <?php endif; ?>
                  </div>
                </li>
              <?php endif; ?>
              <li class="divider"></li>
              <li>
                <div class="pull-left">
                  <?php //echo $this->Html->link('<i class="fa fa-question"></i> '.__('FAQ'), "/faq/", array('class' => 'btn btn-color-three-outline btn-block','escape' => false));
                  ?>
                </div>
                <div class="pull-right">
                  <?php echo $this->Html->link('<i class="fa fa-sign-out"></i> ' . __('Logout'), array('controller' => 'Users', 'action' => 'logout'), array('class' => 'btn btn-color-three-outline btn-block', 'escape' => false)); ?>
                </div>
              </li>
            </ul>
          </li>
          <li class="color-two hidden-xs hidden-sm">
            <p><?php echo __("Server's Clock"); ?>: </p>
          </li>
          <li class="color-two server-time">
            <?php
            $datetime = explode(" ", $datetime);
            ?>
            <p id="server-time" ng-controller="ServerTimeController" ng-init="initializeServerTime('<?php echo $datetime[0] . "','" . $datetime[1]; ?>')">{{ serverTime.date }}<br>{{ serverTime.time }}</p>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <section class="page-content">
    <?php if ($logged_user['type'] >= 3) : ?>
      <aside id="admin-menu" class="admin-menu color-three">
        <nav>
          <ul class="nav">
            <li><a href="/home" class="text-light hover-color-one <?php if ($controller == "Dashboard") echo "active"; ?>"><i class="fa fa-dashboard"></i> <?php echo __("Dashboard"); ?></a></li>
            <li><a href="/Commits" class="text-light hover-color-one <?php if ($controller == "Commits") echo "active"; ?>"><i class="fa fa-code"></i> <?php echo __("Commits"); ?></a></li>
            <li><a href="/Exercises" class="text-light hover-color-one <?php if ($controller == "Exercises") echo "active"; ?>"><i class="fa fa-folder"></i> <?php echo __("Exercises"); ?></a></li>
            <li><a href="/Offerings" class="text-light hover-color-one <?php if ($controller == "Offerings") echo "active"; ?>"><i class="fa fa-mortar-board"></i> <?php echo __("Offerings"); ?></a></li>
            <li><a href="/Users" class="text-light hover-color-one <?php if ($controller == "Users") echo "active"; ?>"><i class="fa fa-users"></i> <?php echo __("Users"); ?></a></li>
            <li><a href="/Courses" class="text-light hover-color-one <?php if ($controller == "Courses") echo "active"; ?>"><i class="fa fa-university"></i> <?php echo __("Courses"); ?></a></li>
            <li><a href="/Universities" class="text-light hover-color-one <?php if ($controller == "Universities") echo "active"; ?>"><i class="fa fa-university"></i> <?php echo __("Universities"); ?></a></li>
            <li><a href="/AllowedFiles" class="text-light hover-color-one <?php if ($controller == "AllowedFiles") echo "active"; ?>"><i class="fa fa-files-o"></i> <?php echo __("Allowed Files"); ?></a></li>
            <li><a href="/Messages/blacklist" class="text-light hover-color-one <?php if ($controller == "Messages") echo "active"; ?>"><i class="fa fa-remove"></i> <?php echo __("Email Blacklist"); ?></a></li>
            <li><a href="/Logs" class="text-light hover-color-one <?php if ($controller == "Logs") echo "active"; ?>"><i class="fa fa-list"></i> <?php echo __("Log"); ?></a></li>
            <li><a href="/Messages/maillog" class="text-light hover-color-one <?php if ($controller == "Messages") echo "active"; ?>"><i class="fa fa-list"></i> <?php echo __("Mail Logs"); ?></a></li>
          </ul>
        </nav>
      </aside>
    <?php endif; ?>
    <div class="main-loading">
      <i class="fa fa-5x fa-spin fa-gear"></i>
      <h3><?php echo __("Loading") . "..."; ?></h3>
    </div>
    <aside class="main-content hidden">
      <div class="container-fluid">
        <?php if (isset($breadcrumbs)) : ?>
          <div class="row">
            <div class="col-md-12">
              <ol class="breadcrumb">
                <li><a href="/" class="text-color-one"><i class="fa fa-home"></i> Home</a></li>
                <?php foreach ($breadcrumbs as $b) :
                  if ($b['link'] == "#") : ?>
                    <li class="active"><?php echo $b['text']; ?></li>
                  <?php else : ?>
                    <li>
                      <?php if (strlen($b['text']) > 50) $b['text'] = substr($b['text'], 0, 50) . "...";
                      echo $this->Html->link($b['text'], $b['link'], array('class' => 'text-color-one')); ?>
                    </li>
                  <?php endif; ?>
                <?php endforeach; ?>
              </ol>
            </div>
          </div>
        <?php endif; ?>
        <?php
        $msgSuccess = $this->Session->flash('success');
        $msgError = $this->Session->flash('flash');
        if (strlen(trim($msgSuccess)) > 0 || strlen(trim($msgError)) > 0) : ?>
          <div class="row">
            <div class="col-md-12">
              <?php if (strlen(trim($msgSuccess)) > 0) { ?>
                <div class="alert alert-success">
                  <?php echo $msgSuccess; ?>
                </div>
              <?php }
              if (strlen(trim($msgError)) > 0) {  ?>
                <div class="alert alert-danger">
                  <?php echo $msgError; ?>
                </div>
              <?php } ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
      <?php echo $this->fetch('content'); ?>
    </aside>
  </section>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/js/bootstrap.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/rickshaw/1.5.1/rickshaw.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jasny-bootstrap/3.1.3/js/jasny-bootstrap.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/summernote/0.6.1/summernote.min.js"></script>
  <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.3.9/angular.min.js"></script>
  <?php
  //        Old: Local files, now on Amazon S3
  //        echo $this->Html->script('libs/angular.rickshaw');
  //        echo $this->Html->script('libs/angular-flot');
  //        echo $this->Html->script('libs/moment-with-locales.min');
  ?>
  <script src="//d7ghnchxvmryx.cloudfront.net/js/libs/angular.rickshaw.js"></script>
  <script src="//d7ghnchxvmryx.cloudfront.net/js/libs/angular-flot.min.js"></script>
  <script src="//d7ghnchxvmryx.cloudfront.net/js/libs/moment-with-locales.min.js"></script>

  <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.42/js/bootstrap-datetimepicker.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.jquery.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/zeroclipboard/2.2.0/ZeroClipboard.min.js"></script>

  <script src="//d7ghnchxvmryx.cloudfront.net/js/libs/highlight.pack.js"></script>
  <script src="//d7ghnchxvmryx.cloudfront.net/js/jquery.ui.widget.js"></script>
  <script src="//d7ghnchxvmryx.cloudfront.net/js/jquery.iframe-transport.js"></script>
  <script src="//d7ghnchxvmryx.cloudfront.net/js/jquery.fileupload.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/showdown/1.3.0/showdown.min.js"></script>
  <?php
  //        Old: Local files, now on Amazon S3
  //        echo $this->Html->script('libs/highlight.pack.js');
  //        echo $this->Html->script('jquery.ui.widget');
  //        echo $this->Html->script('jquery.iframe-transport');
  //        echo $this->Html->script('jquery.fileupload');
  echo $this->Html->script('public.v2.min.js?v=20160227');
  ?>
  <?php
  //        echo $this->Html->script('libs/jquery-2.1.0.min');
  //        echo $this->Html->script('libs/bootstrap.min');
  //        echo $this->Html->script('libs/d3.v3.min');
  //        echo $this->Html->script('libs/rickshaw.min');
  //        echo $this->Html->script('libs/jquery.flot.min');
  //        echo $this->Html->script('libs/jasny-bootstrap.min');
  //        echo $this->Html->script('libs/summernote.min');
  //        echo $this->Html->script('libs/angular.min');
  //        echo $this->Html->script('libs/angular.rickshaw');
  //        echo $this->Html->script('libs/angular-flot');
  //        echo $this->Html->script('libs/moment');
  //        echo $this->Html->script('libs/moment-with-locales.min');
  //        echo $this->Html->script('libs/bootstrap-datetimepicker.min');
  //        echo $this->Html->script('libs/chosen.min');
  //        echo $this->Html->script('libs/highlight.pack.js');
  //        echo $this->Html->script('jquery.ui.widget');
  //        echo $this->Html->script('jquery.iframe-transport');
  //        echo $this->Html->script('jquery.fileupload');
  //        echo $this->Html->script('../assets/zeroclipboard/ZeroClipboard.min');
  //        echo $this->Html->script('public.v2.min');
  ?>
  <?php if ($logged_user['type'] >= 3) : echo $this->Html->script('extra.js?v=20171917'); ?>
    <script>
      var admin_menu = false;
      $("#admin-menu").css("min-height", $("html").height());
      $("#btnAdminMenu").on("click", function(e) {
        e.preventDefault();
        $(this).blur();
        if (admin_menu) {
          $("#admin-menu").animate({
            width: 0
          }, 600);
          admin_menu = false;
        } else {
          $("#admin-menu").css("min-height", $("html").height());
          admin_menu = true;
          $("#admin-menu").animate({
            width: 229
          }, 600);
        }
      });
    </script>
  <?php endif; ?>
</body>

</html>
