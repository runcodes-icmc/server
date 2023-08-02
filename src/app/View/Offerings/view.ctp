<div class="container-fluid" <?php if ($assistantOrProfessor) { ?> ng-controller="OfferingController" ng-init="offeringId='<?php echo $offering['Offering']['id']; ?>';participant=-1;loadProfessorsAndAssistantsTable();" <?php } ?>>
    <?php if ($assistantOrProfessor) : ?>
    <div class="modal fade" id="offeringManagementModal" tabindex="-1" role="dialog" aria-labelledby="offeringManagementModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-close"></i></span></button>
                    <h4 class="modal-title" id="myModalLabel"><?php echo __("Participants Management"); ?></h4>
                </div>
                <div class="modal-body">
                    <div class="container-fluid spaced-rows" ng-click="$event.preventDefault()" aria-label="roleOptions">
                        <div class="row">
                            <div class="col-md-12">
                                <?php echo $this->Form->input('participantsList',array('label' => false,'class' => 'form-control', 'type' => 'select', 'options' => $studentsList,'ng-model' => 'participant')) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <button href="#" class="btn btn-color-one btn-block" ng-click="setProfessor();"><?php echo __("Set as Professor"); ?></button>
                            </div>
                            <div class="col-md-6">
                                <button href="#" class="btn btn-color-two btn-block" ng-click="setAssistant();"><?php echo __("Set as Assistant"); ?></button>
                            </div>
                        </div>
                        <div class="row" ng-show="professorsAndAssistants.length > 0">
                            <div class="col-md-12">
                                <div ng-show="loading"><i class="fa fa-refresh fa-spin"></i> <?php echo __("Loading")."..."; ?></div>
                                <table class="table table-hover table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th><?php echo __("Name"); ?></th>
                                            <th class="text-center"><?php echo __("Role"); ?></th>
                                            <th class="text-center"><?php echo __("Action"); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr ng-repeat="p in professorsAndAssistants">
                                            <td>{{ p.name }}</td>
                                            <td class="text-center">{{ p.role }}</td>
                                            <td class="text-center"><button href="#" class="btn btn-color-three btn-sm btn-block" ng-click="setStudent(p.email);"><?php echo __("Set as Student"); ?></button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-color-one" data-dismiss="modal"><?php echo __("Close"); ?></button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="row">
        <div class="<?php if($assistantOrProfessor) : ?>col-lg-7 col-md-6 col-sm-6 <?php else: ?>col-md-12<?php endif; ?>">
            <div id="secInformation" class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo $offering['Course']['code']." - ".$offering['Course']['title']; ?></h3>
                    <?php if($assistantOrProfessor) : ?>
                    <div class="panel-heading-buttons" ng-click="$event.preventDefault()">
                    <?php echo $this->Html->link('<i class="fa fa-group"></i> '.__('Professors and Assistants'),"#",array('escape' => false,'class' => 'btn btn-sm btn-color-one','data-toggle' => 'modal','data-target' => '#offeringManagementModal')); ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-8">
                            <?php if(count($professors)>0): ?>
                            <p><?php echo __("Professors: ");
                                if ($logged_user['type'] >= 3) {
                                    $professors_arr = array();
                                    foreach ($professors as $professor):
                                        echo $this->Html->link($professor['User']['name'],array('controller' => 'Users', 'action' => 'view',$professor['User']['email']))." ";
                                    endforeach;
                                } else {
                                    $professors_arr = array();
                                    foreach ($professors as $professor):
                                        array_push($professors_arr, $professor['User']['name']);
                                    endforeach;
                                    echo implode(", ",$professors_arr);
                                }
                            endif;
                            if(count($assistants)>0): ?>
                                <br><?php echo __("Assistant Professors: ")." ";
                                if ($logged_user['type'] >= 3) {
                                    $assistant_arr = array();
                                    foreach ($assistants as $assistant):
                                        echo $this->Html->link($assistant['User']['name'],array('controller' => 'Users', 'action' => 'view',$assistant['User']['email']))." ";
                                    endforeach;
                                } else {
                                    $assistant_arr = array();
                                    foreach ($assistants as $assistant):
                                        array_push($assistant_arr, $assistant['User']['name']);
                                    endforeach;
                                    echo implode(", ", $assistant_arr);
                                }
                            endif; ?>
                            <br>
                            <?php echo __("Classroom").": ".$offering['Offering']['classroom']; ?><br>
                            <?php echo __("University").": ".$offering['Course']['University']['abbreviation']; ?><br>
                            </p>
                            <p>
                                <?php if ($assistantOrProfessor) : ?>
                                    <?php echo __("Active until").": "; ?><strong><?php echo $this->Time->format('d/m/Y',$offering['Offering']['end_date']); ?></strong></br>
                                 <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <?php if (isset($offering['Offering']['enrollment_code'])) : ?>
                                <div class="box infographic color-three">
                                    <i class="fa fa-sign-in"></i>
                                    <span class="headline"><?php echo __("Enrollment Code"); ?></span>
                                    <span class="value"><?php echo $offering['Offering']['enrollment_code']; ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <?php if($assistantOrProfessor): ?>
        <div class="col-lg-5 col-md-6 col-sm-6">
            <div class="row">
                <div class="col-md-6 col-xs-6">
                    <?php echo $this->Html->link('<i class="fa fa-plus"></i> '.__('New Exercise'), array('controller' => 'exercises','action' => 'add',$offering['Offering']['id']),array('escape' => false,'class' => 'box btn btn-block btn-color-one')); ?>
                </div>
                <div class="col-md-6 col-xs-6">
                    <?php echo $this->Html->link('<i class="fa fa-envelope"></i> '.__('Send E-mail'), array('controller' => 'Offerings','action' => 'email',$offering['Offering']['id']),array('escape' => false,'class' => 'box btn btn-block btn-color-one')); ?>
                </div>
                <div class="col-md-6 col-xs-6">
                    <?php echo $this->Html->link('<i class="fa fa-table"></i> '.__('View Grades'), array('controller' => 'Offerings','action' => 'grades',$offering['Offering']['id']),array('escape' => false,'class' => 'box btn btn-block btn-color-three')); ?>
                </div>
                <div class="col-md-6 col-xs-6">
                    <?php echo $this->Html->link('<i class="fa fa-download"></i> '.__('Export Grades Table'), array('controller' => 'Offerings','action' => 'exportToCsv',$offering['Offering']['id']),array('escape' => false,'class' => 'box btn btn-block btn-color-three')); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $paginationLimit = 20; ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div id="secExercises" class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("Exercises"); ?></h3>
                    <?php if (count($exercises) > $paginationLimit) : ?>
                        <span class="pull-right">
                            <ul class="nav panel-tabs nav-pills">
                                <?php
                                $init = 1;
                                $end = $paginationLimit;
                                $pages = intval(ceil(count($exercises)/$paginationLimit));
                                $page = 1;
                                while ($page <= $pages) :
                                ?>
                                <li class="<?php echo ($page==1) ? "active" : ""; ?>"><a href="#tab<?php echo $page; ?>" data-toggle="tab"><?php echo __("Ex")." ".$init." - ".$end; ?></a></li>
                                <?php
                                $init = $end + 1;
                                $end+= $paginationLimit;
                                $end = ($end > count($exercises)) ? count($exercises) : $end;
                                $page++;
                                endwhile; ?>
                            </ul>
                        </span>
                    <?php endif; ?>
                </div>
                <?php if (count($exercises) == 0) : ?>
                    <div class="panel-body">
                        <p><?php echo __("There are no exercises available"); ?></p>
                    </div>
                <?php else: ?>
                    <?php if (count($exercises) > $paginationLimit) : ?>
                    <div class="panel-body">
                        <div class="tab-content">
                            <?php
                            $init = 1;
                            $end = $paginationLimit;
                            $pages = intval(ceil(count($exercises)/$paginationLimit));
                            $page = 1;
                            while ($page <= $pages) :
                                ?>

                            <div class="tab-pane <?php echo ($page==1) ? "active" : ""; ?>" id="tab<?php echo $page; ?>">
                                <table class="table table-hover table-striped table-bordered">
                                    <thead>
                                    <tr>
                                        <th><?php echo __('No.'); ?></th>
                                        <th><?php echo __('Exercise'); ?></th>
                                        <th class="text-center"><?php echo __('Status'); ?></th>
                                        <th class="text-center"><?php echo __('Correct Cases'); ?></th>
                                        <th class="text-center"><?php echo __('Grade'); ?></th>
                                        <?php if ($assistantOrProfessor) : ?>
                                            <th class="text-center"><?php echo __('Commits'); ?></th>
                                            <th class="text-center"><?php echo __('Participants'); ?></th>
                                        <?php endif; ?>
                                        <th class="text-center" style="min-width: 170px"><?php echo __('Deadline'); ?></th>
                                        <th class="text-center" style="min-width: 295px"><?php echo __('Actions'); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $k=$init-1;
                                    while ($k < $end):
                                        $exercise = $exercises[$k];
                                        $k++;
                                        ?>
                                        <tr>
                                            <td><?php echo $k; ?></td>
                                            <td><?php echo h($exercise['Exercise']['title']); ?></td>
                                            <td class="text-center"><span class="label label-<?php echo $exercise['MyCommit']['status_color']; ?>"><?php echo $exercise['MyCommit']['name_status']; ?></span></td>
                                            <td class="text-center"><span class="label label-<?php echo $exercise['MyCommit']['correct_color']; ?>"><?php echo $exercise['MyCommit']['corrects']; ?>/<?php echo $exercise['Exercise']['num_cases']; ?></span></td>
                                            <td class="text-center"><span class="label label-<?php echo $exercise['MyCommit']['score_color']; ?>"><?php echo $exercise['MyCommit']['score']; ?></span></td>
                                            <?php if ($assistantOrProfessor) : ?>
                                                <td class="text-center"><?php echo h($exercise['Exercise']['num_commits']); ?></td>
                                                <td class="text-center"><?php echo h($exercise['Exercise']['num_participants']."/".$offering['Offering']['num_participants']); ?></td>
                                            <?php endif; ?>
                                            <td class="text-center"><?php echo $this->Time->format('d/m/Y H:i:s',$exercise['Exercise']['deadline']); ?>
                                                <?php if($exercise['Exercise']['isOpen']): ?>
                                                    <span class="label label-success"><i class="fa fa-unlock"></i></span>
                                                <?php else: ?>
                                                    <span class="label label-important"><i class="fa fa-lock"></i></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if($assistantOrProfessor): ?>
                                                    <?php echo $this->Html->link(__('View Details'), array('controller' => 'exercises', 'action' => 'viewProfessor', h($exercise['Exercise']['id'])),array('class' => 'btn btn-color-one btn-sm')); ?>
                                                    <?php echo $this->Form->postLink(__('Remove Exercise'), array('controller' => 'Exercises','action' => 'delete', $exercise['Exercise']['id']), array('class' => 'btn btn-danger btn-sm'), __('Are you sure you want to delete %s?', $exercise['Exercise']['title'])); ?>
                                                <?php else: ?>
                                                    <?php echo $this->Html->link(__('View Details'), array('controller' => 'exercises', 'action' => 'view', h($exercise['Exercise']['id'])),array('class' => 'btn btn-color-one btn-sm')); ?>
                                                <?php endif; ?>
                                                <div class="dropdown">
                                                    <button class="btn btn-color-two dropdown-toggle" type="button" id="dropdownAlert" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                        <i class="fa fa-bell"></i>
                                                        <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu" aria-labelledby="dropdownAlert">
                                                        <li><?php echo $this->Html->link('Google Calendar', array('controller' => 'exercises', 'action' => 'exportExerciseToGoogleCalendar', h($exercise['Exercise']['id'])),array('target' => '_blank','escape' => false,'class' => 'btn btn-color-one btn-sm')); ?>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php
                                $init = $end + 1;
                                $end+= $paginationLimit;
                                $end = ($end > count($exercises)) ? count($exercises) : $end;
                                $page++;
                            endwhile; ?>
                        </div>
                    </div>
                    <?php else : ?>
                        <table class="table table-hover table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th><?php echo __('No.'); ?></th>
                                    <th><?php echo __('Exercise'); ?></th>
                                    <th class="text-center"><?php echo __('Status'); ?></th>
                                    <th class="text-center"><?php echo __('Correct Cases'); ?></th>
                                    <th class="text-center"><?php echo __('Grade'); ?></th>
                                    <?php if ($assistantOrProfessor) : ?>
                                    <th class="text-center"><?php echo __('Commits'); ?></th>
                                    <th class="text-center"><?php echo __('Participants'); ?></th>
                                    <?php endif; ?>
                                    <th class="text-center"><?php echo __('Deadline'); ?></th>
                                    <th class="text-center"><?php echo __('Actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $k=0;
                                foreach ($exercises as $exercise): ?>
                                <tr>
                                    <td><?php echo ++$k; ?></td>
                                    <td><?php echo h($exercise['Exercise']['title']); ?></td>
                                    <td class="text-center"><span class="label label-<?php echo $exercise['MyCommit']['status_color']; ?>"><?php echo $exercise['MyCommit']['name_status']; ?></span></td>
                                    <td class="text-center"><span class="label label-<?php echo $exercise['MyCommit']['correct_color']; ?>"><?php echo $exercise['MyCommit']['corrects']; ?>/<?php echo $exercise['Exercise']['num_cases']; ?></span></td>
                                    <td class="text-center"><span class="label label-<?php echo $exercise['MyCommit']['score_color']; ?>"><?php echo $exercise['MyCommit']['score']; ?></span></td>
                                    <?php if ($assistantOrProfessor) : ?>
                                    <td class="text-center"><?php echo h($exercise['Exercise']['num_commits']); ?></td>
                                    <td class="text-center"><?php echo h($exercise['Exercise']['num_participants']."/".$offering['Offering']['num_participants']); ?></td>
                                    <?php endif; ?>
                                    <td class="text-center"><?php echo $this->Time->format('d/m/Y H:i:s',$exercise['Exercise']['deadline']); ?>
                                        <?php if($exercise['Exercise']['isOpen']): ?>
                                            <span class="label label-success"><i class="fa fa-unlock"></i></span>
                                        <?php else: ?>
                                            <span class="label label-important"><i class="fa fa-lock"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if($assistantOrProfessor): ?>
                                            <?php echo $this->Html->link(__('View Details'), array('controller' => 'exercises', 'action' => 'viewProfessor', h($exercise['Exercise']['id'])),array('class' => 'btn btn-color-one btn-sm')); ?>
                                            <?php echo $this->Form->postLink(__('Remove Exercise'), array('controller' => 'Exercises','action' => 'delete', $exercise['Exercise']['id']), array('class' => 'btn btn-danger btn-sm'), __('Are you sure you want to delete %s?', $exercise['Exercise']['title'])); ?>
                                        <?php else: ?>
                                            <?php echo $this->Html->link(__('View Details'), array('controller' => 'exercises', 'action' => 'view', h($exercise['Exercise']['id'])),array('class' => 'btn btn-color-one btn-sm')); ?>
                                        <?php endif; ?>
                                        <?php echo $this->Html->link('<i class="fa fa-google"></i> <i class="fa fa-calendar"></i>', array('controller' => 'exercises', 'action' => 'exportExerciseToGoogleCalendar', h($exercise['Exercise']['id'])),array('escape' => false,'target'=>'_blank','class' => 'btn btn-color-two btn-sm')); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($assistantOrProfessor) : ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("Statistics"); ?></h3>
                </div>
                <div class="panel-body" ng-controller="OfferingStatsController" ng-init="exerciseTitle='<?php echo __("Exercise"); ?>';loadOfferingInfographicData(<?php echo $offering['Offering']['id']; ?>);loadOfferingStats(<?php echo $offering['Offering']['id']; ?>);">
                    <div class="row" id="stats-row">
                        <div class="col-md-2 col-sm-3">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <?php echo $this->Form->input('studentsList',array('label' => false,'class' => 'form-control', 'type' => 'select', 'options' => $studentsList,'ng-model' => 'student', 'ng-change' => 'loadOfferingStats('.$offering['Offering']['id'].')')) ?>
                                        </div>
                                    <div class="box infographic color-one">
                                        <i class="fa fa-users"></i>
                                        <span class="headline"><?php echo __("Students"); ?></span>
                                        <span class="value">{{ offeringInfographic.students }}</span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="box infographic color-two">
                                        <i class="fa fa-pie-chart"></i>
                                        <span class="headline"><?php echo __("Grades >= 5.0 Percentage"); ?></span>
                                        <span class="value">{{ offeringInfographic.gradespect }}%</span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="box infographic color-three">
                                        <i class="fa fa-star"></i>
                                        <span class="headline"><?php echo __("Global Grades Average"); ?></span>
                                        <span class="value">{{ offeringInfographic.gradesavg }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-10 col-sm-9">
                            <div id="legend" class="flot-legend"></div>
                            <flot dataset="dataset" options="options" height="290px"></flot>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
