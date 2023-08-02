<div class="container-fluid">
    <div class="row row-min-height-230">
        <div class="<?php if($assistantOrProfessor): ?> col-md-8 <?php else: ?> col-md-12 <?php endif; ?>">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("General Info"); ?>:</h3>
                    <div class="panel-heading-buttons">
                        <?php
                        echo $this->Html->link('<i class="fa fa-download"></i> '.__('Download File'),array('controller' => 'Commits', 'action' => 'download', $commit['Commit']['id']),array('escape' => false,'class' => 'btn btn-sm btn-color-one'));
                         ?>
                    </div>
                </div>
                <div class="panel-body">
                    <p>
                        <?php echo __("Submitted by: %s (%s)", $commit['User']['name'], $commit['User']['email']); ?><br>
                        <?php echo $commit['User']['University']['student_identifier_text'].": ".$commit['User']['identifier']; ?><br>
                        <?php echo __("Date: %s", $this->Time->format('d/m/Y H:i:s',$commit['Commit']['commit_time'])); ?><br>
                    </p>

                    <div class="row">
                        <div class="col-md-4 col-sm-3">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="box infographic color-<?php echo $commit['Commit']['status_color']; ?>">
                                        <div class="headline">status</div>
                                        <div class="text-value"><?php echo $commit['Commit']['name_status']; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 col-sm-9">
                            <div class="row">
                                <div class="col-md-4 col-sm-6">
                                    <div class="box infographic color-<?php echo $commit['Commit']['compiled_color']; ?>">
                                        <div class="headline"><?php echo __("compiled"); ?></div>
                                        <div class="value"><?php echo ($commit['Commit']['compiled']) ? __("Yes") : __("No"); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4 hidden-sm">
                                    <div class="box infographic color-<?php echo $commit['Commit']['correct_color']; ?>">
                                        <div class="headline"><?php echo __("correct cases"); ?></div>
                                        <div class="value"><?php echo $commit['Commit']['corrects']; ?>/<?php echo $commit['Exercise']['num_cases']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="box infographic color-<?php echo $commit['Commit']['score_color']; ?>">
                                        <div class="headline"><?php echo __("score"); ?></div>
                                        <div class="value"><?php echo $commit['Commit']['score']; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <?php if(isset($commit['Commit']['detailed_status'])) : ?>
                                <p>
                                    <span class="label label-<?php echo $commit['Commit']['status_color']; ?>"><?php echo $commit['Commit']['name_status']; ?></span>: <?php echo $commit['Commit']['detailed_status']; ?>
                                </p>
                            <?php endif; ?>
                            <p>
                                <?php if (strlen($commit['Commit']['compiled_message']) > 0) echo __("Compiled Message").": <br>".  nl2br($commit['Commit']['compiled_message']); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php if($assistantOrProfessor): ?>
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo __("Edit Score"); ?>:</h3>
                        <div class="panel-heading-buttons">
                            <?php if($assistantOrProfessor): ?>
                                <?php echo $this->Html->link('<i class="fa fa-refresh"></i> '.__('Recompile'), array('controller' => 'Commits', 'action' => 'recompile',$commit['Commit']['id']),array('escape' => false,'class' => 'btn btn-danger btn-sm')); ?>
                                <?php echo $this->Html->link('<i class="fa fa-file"></i> '.__('View File'), array('controller' => 'Commits', 'action' => 'viewFile',$commit['Commit']['id']),array('escape' => false,'class' => 'btn btn-color-two btn-sm')); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="panel-body">
                        <?php echo $this->Form->create('Commit',array('url' => array('controller' => 'Commits', 'action' => 'editScore',$commit['Commit']['id']))); ?>
                        <?php echo $this->Form->input('id'); ?>
                        <?php echo $this->Form->input('score', array('label' => array('text' => __('New Score'),'class' => 'control-label'), 'type' => 'number','step' => '0.01','value' => $commit['Commit']['score'],'class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                        <?php echo $this->Form->input('status', array('label' => array('text' => __('Status'),'class' => 'control-label'), 'type' => 'select', 'options' => $statusList,'value' => $commit['Commit']['status'],'class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                        <?php echo $this->Form->end(array('label' => __('Confirm Change'), 'class' => 'btn btn-color-one ')); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php if (isset($commit['ExerciseCase']) && count($commit['ExerciseCase']) > 0) : ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo __("Cases Result: "); ?></h3>
                    </div>
                    <table class="table table-hover table-striped table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center"><?php echo __('Case'); ?></th>
                            <th class="text-center"><?php echo __('Status'); ?></th>
                            <th class="text-center"><?php echo __('CPU Time Used'); ?></th>
                            <th class="text-center" style="width: 60%"><?php echo __('Message'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($commit['ExerciseCase'] as $key => $case):
                            $text = __("Case") . " " . ($key + 1);
                            $casesList[$case['CommitsExerciseCase']['id']] = array('text' => $text,'correct' => $case['CommitsExerciseCase']['status']);
                            ?>
                            <tr>
                                <td class="text-center"><?php echo __("Case ") . ($key + 1); ?></td>
                                <td class="text-center">
                                    <?php if ($case['CommitsExerciseCase']['status'] == 1) :  ?>
                                        <span class="label label-success"><?php echo __('Accepted'); ?></span>
                                    <?php else: ?>
                                        <span class="label label-danger"><?php echo __('Rejected'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?php echo h($case['CommitsExerciseCase']['cputime'])." ".__("sec"); ?></td>
                                <td class="text-center"><?php echo __($case['CommitsExerciseCase']['status_message']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if (isset($casesList) && count($casesList) > 0) : ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("Cases Details"); ?></h3>
                </div>
                <div class="panel-body" ng-controller="ExerciseCasesController">
                    <ul class="nav nav-pills nav-cases" role="tablist">
                        <?php foreach ($casesList as $caseId => $case) :
                            if ($case['correct'] == 1) :
                        ?>
                        <li role="cases" style="margin-bottom: 5px;"><a href="#" data-toggle="tab" class="correct" ng-click="caseId=<?php echo $caseId; ?>;loadCase();$event.preventDefault();"><i class="fa fa-check"></i> <?php echo $case['text']; ?></a></li>
                            <?php else : ?>
                        <li role="cases" style="margin-bottom: 5px;"><a href="#" data-toggle="tab" class="error" ng-click="caseId=<?php echo $caseId; ?>;loadCase();$event.preventDefault();"><i class="fa fa-remove"></i> <?php echo $case['text']; ?></a></li>
                            <?php endif;
                        endforeach; ?>
                    </ul>
                    <?php // echo $this->Form->input('case', array('label' => false, 'type' => 'select','options' => $casesList,'class' => 'form-control','ng-model' => 'caseId','ng-change' => 'loadCase()','div' => array('class' => 'form-group')));  ?>
                    <div class="commits-exercise-case-page" ng-show="caseId != -1" ng-bind-html="commitsExerciseCaseView">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>