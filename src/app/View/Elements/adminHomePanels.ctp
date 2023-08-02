<?php
$showServers = true;
if (is_array($hide_panels)) {
  if (in_array('home-servers', $hide_panels)) {
    $showServers = false;
  }
}
$showStats = true;
if (is_array($hide_panels)) {
  if (in_array('home-stats', $hide_panels)) {
    $showStats = false;
  }
}
$showAdmin = true;
if (is_array($hide_panels)) {
  if (in_array('home-admin', $hide_panels)) {
    $showAdmin = false;
  }
}
?>
<script>
  var loadHomeCharts = true;
</script>
<div class="container-fluid">
  <div class="row" id="homeAdminInfographics" ng-controller="InfographicsController">
    <div class="col-md-2 col-sm-4 col-xs-6">
      <div class="box infographic color-one">
        <i class="fa fa-code"></i>
        <span class="headline"><?php echo __("Commits in Queue"); ?></span>
        <span class="value">{{ queue }}</span>
      </div>
    </div>
    <div class="col-md-2 col-sm-4 col-xs-6">
      <div class="box infographic color-two">
        <i class="fa fa-terminal"></i>
        <span class="headline"><?php echo __("Commits in Execution"); ?></span>
        <span class="value">{{ execution }}</span>
      </div>
    </div>
    <div class="col-md-2 col-sm-4 col-xs-6">
      <div class="box infographic color-three">
        <i class="fa fa-users"></i>
        <span class="headline"><?php echo __("On-line Users"); ?></span>
        <span class="value">{{ users }}</span>
      </div>
    </div>
    <div class="col-md-2 col-sm-4 col-xs-6">
      <div class="box infographic color-one">
        <i class="fa fa-tachometer"></i>
        <span class="headline"><?php echo __("Web CPU Utilization"); ?></span>
        <span class="value">{{ servers.web }}%</span>
      </div>
    </div>
    <div class="col-md-2 col-sm-4 col-xs-6">
      <div class="box infographic color-two">
        <i class="fa fa-tachometer"></i>
        <span class="headline"><?php echo __("Database CPU Utilization"); ?></span>
        <span class="value">{{ servers.db }}%</span>
      </div>
    </div>
    <div class="col-md-2 col-sm-4 col-xs-6">
      <div class="box infographic color-three">
        <i class="fa fa-tachometer"></i>
        <span class="headline"><?php echo __("Compiler CPU Utilization"); ?></span>
        <span class="value">{{ servers.compiler }}%</span>
      </div>
    </div>
  </div>
  <?php if (1 == 2) : ?>
    <div class="row" ng-controller="ServersController">
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title"><?php echo __('Servers'); ?></h3>
            <div class="panel-heading-buttons">
              <?php
              if ($showServers) {
                $displayShow = "none";
                $displayHide = "block";
              } else {
                $displayHide = "none";
                $displayShow = "block";
              }
              echo $this->Html->link(__('Hide Servers'), '#', array('class' => 'btn btn-info btn-sm btn-hide-panel', 'id' => 'btn-hide-home-servers', 'data-panel' => 'home-servers', 'style' => 'display: ' . $displayHide));
              echo $this->Html->link(__('Show Servers'), '#', array('class' => 'btn btn-success btn-sm btn-show-panel', 'id' => 'btn-show-home-servers', 'data-panel' => 'home-servers', 'style' => 'display: ' . $displayShow));
              ?>
            </div>
          </div>
          <div class="panel-body without-overflow" id="panel-home-servers" <?php if (!$showServers) :  ?>style="height: 0px; padding: 0" <?php endif; ?>>
            <div class="server-box" ng-repeat="server in servers">
              <div class="warning" ng-show="server.warning">
                <p><i class="fa fa-warning"></i> {{ server.warning }}</p>
              </div>
              <div class="title">
                <h3>{{ server.name }}</h3>
                <h5>{{ server.ip }}</h5>
              </div>
              <div class="clock">
                <p>
                  Last Report: <br>{{ server.lastreport }} <br>
                  Boot: <br>{{ server.boot }}
                </p>
              </div>
              <div class="isJail" ng-show="server.isJail">Compiler</div>
              <div class="full-bar disk">
                <div class="bar disk" style="height: {{ server.disk }}%"><span class="text">Disk ({{ server.disk }}%)</span> </div>
              </div>
              <div class="full-bar mem">
                <div class="bar mem" style="height: {{ server.ram }}%"><span class="text">Memory ({{ server.ram }}%)</span> </div>
              </div>
              <div class="full-bar cpu">
                <div class="bar cpu" style="height: {{ server.cpu }}%"><span class="text">CPU ({{ server.cpu }}%)</span> </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><?php echo __('Admin Info'); ?></h3>
          <div class="panel-heading-buttons">
            <?php
            if ($showAdmin) {
              $displayShow = "none";
              $displayHide = "block";
            } else {
              $displayHide = "none";
              $displayShow = "block";
            }
            echo $this->Html->link(__('Hide Admin'), '#', array('class' => 'btn btn-info btn-sm btn-hide-panel', 'id' => 'btn-hide-home-admin', 'data-panel' => 'home-admin', 'style' => 'display: ' . $displayHide));
            echo $this->Html->link(__('Show Admin'), '#', array('class' => 'btn btn-success btn-sm btn-show-panel', 'id' => 'btn-show-home-admin', 'data-panel' => 'home-admin', 'style' => 'display: ' . $displayShow));
            ?>
          </div>
        </div>
        <div class="panel-body without-overflow" id="panel-home-admin" <?php if (!$showAdmin) :  ?>style="height: 0px; padding: 0" <?php endif; ?>>
          <div class="row" id="admin-row">
            <div class="col-md-6">
              <div class="alert <?php echo (Configure::read('Config.maintenanceMode')) ? "alert-danger" : "alert-success"; ?>">
                <strong><?php echo __("Maintenance Mode"); ?>: </strong>
                <?php echo (Configure::read('Config.maintenanceMode')) ? __("ON") . " " . __("The system is in maintenance mode and closed for non-admin users") : __("OFF") . " " . __("The system is open for users"); ?>
              </div>
            </div>
          </div>
          <div class="row" id="admin-row">
            <div class="col-md-6">
              <div class="alert <?php echo (Configure::read('debug') == 2) ? "alert-danger" : "alert-success"; ?>">
                <strong><?php echo __("Debug"); ?>: </strong>
                <?php echo (Configure::read('debug') == 2) ? __("The system is in debug mode") : __("The system is in production mode"); ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="stats index panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><?php echo __('Stats'); ?></h3>
          <div class="panel-heading-buttons">
            <?php
            if ($showStats) {
              $displayShow = "none";
              $displayHide = "block";
            } else {
              $displayHide = "none";
              $displayShow = "block";
            }
            echo $this->Html->link(__('Hide Stats'), '#', array('class' => 'btn btn-info btn-sm btn-hide-panel', 'id' => 'btn-hide-home-stats', 'data-panel' => 'home-stats', 'style' => 'display: ' . $displayHide));
            echo $this->Html->link(__('Show Stats'), '#', array('class' => 'btn btn-success btn-sm btn-show-panel', 'id' => 'btn-show-home-stats', 'data-panel' => 'home-stats', 'style' => 'display: ' . $displayShow));
            ?>
          </div>
        </div>
        <div class="panel-body without-overflow with-loading-mask" ng-controller="StatsController" id="panel-home-stats" <?php if (!$showStats) {  ?>style="height: 0px; padding: 0" <?php } else { ?>style="height: 310px;" <?php }; ?>>
          <div class="loadingMask" ng-show="loading">
            <i class="fa fa-spin fa-refresh"></i>
            <h3><?php echo ("Loading") . "..."; ?></h3>
          </div>
          <div class="row" id="stats-row">
            <div class="col-md-2 col-sm-3">
              <div class="form-group">
                <select class="form-control" ng-model="source" ng-init="source=1;loadStats();" ng-change="loadStats();">
                  <option value="1"><?php echo __("Commits"); ?></option>
                  <option value="2"><?php echo __("Visits"); ?></option>
                  <option value="3"><?php echo __("Disk Usage"); ?></option>
                </select>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="box infographic color-one">
                    <i class="fa {{infographic.box1.icon}}"></i>
                    <span class="headline">{{infographic.box1.title}}</span>
                    <span class="value">{{ infographic.box1.value }}</span>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="box infographic color-two">
                    <i class="fa {{infographic.box2.icon}}"></i>
                    <span class="headline">{{infographic.box2.title}}</span>
                    <span class="value">{{ infographic.box2.value }}</span>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="box infographic color-three">
                    <i class="fa {{infographic.box3.icon}}"></i>
                    <span class="headline">{{infographic.box3.title}}</span>
                    <span class="value">{{ infographic.box3.value }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-10 col-sm-9">
              <rickshaw rickshaw-options="options" rickshaw-features="features" rickshaw-series="series">
              </rickshaw>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
