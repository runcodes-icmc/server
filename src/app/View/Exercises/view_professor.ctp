<?php if ($unsafeCommits) : ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-danger">
                <strong><?php echo __("Attention").": "; ?></strong><?php echo __("There are commits processed before your last cases changes. It strongly recommends that you recompile all commits"); ?>
                <?php echo $this->Form->postLink(__('Recompile All'), array('controller' => 'Commits','action' => 'recompileAll', $exercise['Exercise']['id']), array('class' => 'btn btn-danger btn-sm')); ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container-fluid" ng-controller="ExerciseProfessorViewController">
    <div class="row row-min-height-191">
        <div class="col-md-7">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?php echo $exercise['Exercise']['title']; ?></h3>
                            <?php if ($logged_user["type"] > 3): ?>
                            <div class="panel-heading-buttons">
                                <?php echo $this->Html->link('<i class="fa fa-copy"></i> '.__('Copy to Public Database'), array('controller' => 'PublicExercises','action' => 'copy',$exercise['Exercise']['id']),array('escape' => false,'class' => 'btn btn-danger btn-sm')); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="panel-body" ng-init="descr=0">
                            <p>
                                <?php echo "<strong>".__("Subject").": </strong>".$exercise['Offering']['Course']['code']." - ".$exercise['Offering']['Course']['title']; ?>
                                <br>
                                <?php echo "<strong>".__("Open Date") . ": </strong>" . $this->Time->format('d/m/Y H:i:s',$exercise['Exercise']['open_date']); ?>
                                <br>
                                <?php echo "<strong>".__("Deadline").": </strong>".$this->Time->format('d/m/Y H:i:s',$exercise['Exercise']['deadline']); ?>
                                <?php if($exercise['Exercise']['isOpen']): ?>
                                    <span class="label label-success"><?php echo __('Open'); ?></span>
                                <?php else: ?>
                                    <span class="label label-danger"><?php echo __('Closed'); ?></span>
                                <?php endif; ?>
                                <br>
                                <?php echo $this->Html->link(__('View Description'), '#', array('ng-show' => 'descr==0','ng-click' => 'descr=1;$event.preventDefault();','class' => 'btn btn-color-two btn-sm')); ?>
                            </p>
                            <div ng-show="descr==1">
                                <?php if (!$exercise['Exercise']['markdown']) : echo $exercise['Exercise']['description']; else: ?>
                                    <div ng-controller="ExerciseDescriptionController">
                                        <textarea class="markdown" style="display:none;" ng-init="loadMarkdownDescription()"><?php echo $exercise['Exercise']['description']; ?></textarea><div class="insertMarkdown" ng-bind-html="html"></div>
                                    </div>
                                <?php endif; echo $this->Html->link(__('Hide Description'), '#', array('ng-show' => 'descr==1','ng-click' => 'descr=0;$event.preventDefault();','class' => 'btn btn-color-two btn-sm')); ?>
                            </div>
                            <?php if(count($exercise['ExerciseFile'])): ?>
                                <?php echo "<strong>".__("Files").':</strong>'; ?>
                                <ul class="nav nav-pills">
                                    <?php foreach($exercise['ExerciseFile'] as $key => $file): ?>
                                        <li class="active">
                                            <?php echo $this->Html->link(h($file['ExerciseFile']['path']),array('controller' => 'ExerciseFiles', 'action' => 'fileDownload', h($file['ExerciseFile']['id']))); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <?php if(count($exercise['AllowedFile'])): ?>
                                <?php echo "<strong>".__("This exercise accept the following types").':</strong>'; ?>
                                <ul class="nav nav-pills">
                                    <?php
                                    $java = false;
                                    $makefile = false;
                                    foreach($exercise['AllowedFile'] as $key => $file):
                                        if($file['AllowedFile']['name'] == "Java 8") $java = true;
                                        if(strpos(strtolower($file['AllowedFile']['name']),"makefile") !== false) $makefile = true;
                                        ?>
                                        <li style="margin-right: 3px;">
                                            <span class="label label-info"><?php echo h($file['AllowedFile']['name']); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php if ($java) echo $this->element(Configure::read('Config.language'). DS . "javaAgreement"); ?>
                                <?php if ($makefile) echo $this->element(Configure::read('Config.language'). DS . "makeAgreement"); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="row">
                <div class="col-md-6 col-xs-6">
                    <?php echo $this->Html->link('<i class="fa fa-edit"></i> '.__('Edit Exercise'), array('controller' => 'Exercises','action' => 'edit',$exercise['Exercise']['id']),array('escape' => false,'class' => 'box btn btn-block btn-color-one')); ?>
                </div>
                <div class="col-md-6 col-xs-6">
                    <?php echo $this->Html->link('<i class="fa fa-plus"></i> '.__('New Case'), array('controller' => 'ExerciseCases', 'action' => 'add', $exercise['Exercise']['id']),array('escape' => false,'class' => 'box btn btn-block btn-color-two')); ?>
                </div>
                <div class="col-md-6 col-xs-6">
                    <?php echo $this->Html->link('<i class="fa fa-plus-square"></i> '.__('Add Cases in Batch'), array('controller' => 'ExerciseCases', 'action' => 'addBatch', $exercise['Exercise']['id']),array('escape' => false,'class' => 'box btn btn-block btn-color-two')); ?>
                </div>
                <div class="col-md-6 col-xs-6">
                    <?php echo $this->Html->link('<i class="fa fa-refresh"></i> '.__('Recompile All'), array('controller' => 'Commits', 'action' => 'recompileAll', $exercise['Exercise']['id']),array('escape' => false,'class' => 'box btn btn-block btn-color-three')); ?>
                </div>
                <div class="col-md-6 col-xs-6">
                    <?php echo $this->Html->link('<i class="fa fa-download"></i> '.__('Download All'), array('controller' => 'Exercises', 'action' => 'getAllScoresZipped', $exercise['Exercise']['id']),array('escape' => false,'class' => 'box btn btn-block btn-color-three')); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-7">
            <div class="row">
                <?php if (count($exercise['ExerciseCase']) > 0) : ?>
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?php echo __("Cases"); ?></h3>
                            <div class="panel-heading-buttons">
                                <?php echo $this->Html->link('<i class="fa fa-download"></i> '.__('Download Test Cases'), array('controller' => 'Exercises','action' => 'downloadCases',$exercise['Exercise']['id']),array('escape' => false,'class' => 'btn btn-info btn-sm')); ?>
                                <?php echo $this->Form->postLink('<i class="fa fa-remove"></i> '.__('Remove All Cases'), array('controller' => 'Exercises','action' => 'removeAllCases', $exercise['Exercise']['id']), array('class' => 'btn btn-color-three btn-sm','escape'=>false), __('Are you sure you want to delete all cases?')); ?>
                            </div>
                        </div>
                        <table class="table table-hover table-striped table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center"><?php echo __('Case'); ?></th>
                                <th class="text-center"><?php echo __('Show Input'); ?></th>
                                <th class="text-center"><?php echo __('Show Expected Output'); ?></th>
                                <th class="text-center"><?php echo __('Show User Output'); ?></th>
                                <th class="text-center" style="min-width: 175px"><?php echo __('Actions'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($exercise['ExerciseCase'] as $key => $case): ?>
                                <tr>
                                    <td class="text-center"><?php echo __("Case") ." " . ($key + 1); ?></td>
                                    <td class="text-center">
                                        <?php if ($case['ExerciseCase']['show_input']): ?>
                                            <span class="label label-success"><?php echo $this->Form->postLink(__('Yes'), array('controller' => 'ExerciseCases','action' => 'toggleShowInput', $case['ExerciseCase']['id'])); ?></span>
                                        <?php else: ?>
                                            <span class="label label-important"><?php echo $this->Form->postLink(__('No'), array('controller' => 'ExerciseCases','action' => 'toggleShowInput', $case['ExerciseCase']['id'])); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($case['ExerciseCase']['show_expected_output']): ?>
                                            <span class="label label-success"><?php echo $this->Form->postLink(__('Yes'), array('controller' => 'ExerciseCases','action' => 'toggleShowExpectedOutput', $case['ExerciseCase']['id'])); ?></span>
                                        <?php else: ?>
                                            <span class="label label-important"><?php echo $this->Form->postLink(__('No'), array('controller' => 'ExerciseCases','action' => 'toggleShowExpectedOutput', $case['ExerciseCase']['id'])); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($case['ExerciseCase']['show_user_output']): ?>
                                            <span class="label label-success"><?php echo $this->Form->postLink(__('Yes'), array('controller' => 'ExerciseCases','action' => 'toggleShowUserOutput', $case['ExerciseCase']['id'])); ?></span>
                                        <?php else: ?>
                                            <span class="label label-important"><?php echo $this->Form->postLink(__('No'), array('controller' => 'ExerciseCases','action' => 'toggleShowUserOutput', $case['ExerciseCase']['id'])); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($assistantOrProfessor): ?>
                                            <?php echo $this->Html->link(__('Edit Case'), array('controller' => 'ExerciseCases', 'action' => 'edit', h($case['ExerciseCase']['id'])), array('class' => 'btn btn-color-one btn-sm')); ?>
                                            <?php echo $this->Form->postLink(__('Delete'), array('controller' => 'ExerciseCases','action' => 'delete', $case['ExerciseCase']['id']), array('class' => 'btn btn-danger btn-sm'), __('Are you sure you want to delete # %s?', $case['ExerciseCase']['id'])); ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($exercise['ExerciseCase']) > 2) : ?>
                            <tr>
                                <td class="text-center"><?php echo __("Change All"); ?></td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <?php echo $this->Form->postLink(__('Yes'), array('controller' => 'Exercises','action' => 'showInput', $exercise['Exercise']['id'],1),array('class' => 'btn btn-sm btn-success')); ?>
                                        <?php echo $this->Form->postLink(__('No'), array('controller' => 'Exercises','action' => 'showInput', $exercise['Exercise']['id'],0),array('class' => 'btn btn-sm btn-danger')); ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <?php echo $this->Form->postLink(__('Yes'), array('controller' => 'Exercises','action' => 'showExpectedOutput', $exercise['Exercise']['id'],1),array('class' => 'btn btn-sm btn-success')); ?>
                                        <?php echo $this->Form->postLink(__('No'), array('controller' => 'Exercises','action' => 'showExpectedOutput', $exercise['Exercise']['id'],0),array('class' => 'btn btn-sm btn-danger')); ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <?php echo $this->Form->postLink(__('Yes'), array('controller' => 'Exercises','action' => 'showUserOutput', $exercise['Exercise']['id'],1),array('class' => 'btn btn-sm btn-success')); ?>
                                        <?php echo $this->Form->postLink(__('No'), array('controller' => 'Exercises','action' => 'showUserOutput', $exercise['Exercise']['id'],0),array('class' => 'btn btn-sm btn-danger')); ?>
                                    </div>
                                </td>
                                <td class="text-center"> </td>
                            </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?php echo __("Information"); ?></h3>
                            <span class="pull-right">
                                <ul class="nav panel-tabs nav-pills">
                                    <li class="active"><a href="#scores" data-toggle="tab"><?php echo __("Scores"); ?></a></li>
                                    <li><a href="#commits" data-toggle="tab"><?php echo __("Commits"); ?></a></li>
                                    <li><a href="#cases" data-toggle="tab" ng-click="loadCasesTable();$event.preventDefault();"><?php echo __("Cases"); ?></a></li>
                                </ul>
                            </span>
                        </div>
                        <div class="panel-body">
                            <div class="tab-content">
                                <div class="tab-pane active" id="scores">
                                    <table class="table table-hover table-striped table-bordered">
                                        <thead>
                                        <tr>
                                            <th><?php echo __('Name'); ?></th>
                                            <th class="text-center"><?php echo $course['University']['student_identifier_text']; ?></th>
                                            <th class="text-center hidden-md"><?php echo __('Date'); ?></th>
                                            <th class="text-center"><?php echo __('Status'); ?></th>
                                            <th class="text-center"><?php echo __('Corrects'); ?></th>
                                            <th class="text-center"><?php echo __('Score'); ?></th>
                                            <th class="text-center"><?php echo __('Actions'); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($scores as $key => $score): ?>
                                            <tr>
                                                <td><?php echo $score["User"]['name']; ?><?php if (strlen($score["Commit"]['ip']) > 0) echo '<br>IP: '.$score["Commit"]['ip']; ?></td>
                                                <td class="text-center"><?php echo $score["User"]['identifier']; ?></td>
                                                <td class="text-center hidden-md"><?php echo $this->Time->format('d/m/Y H:i:s',$score['Commit']["commit_time"]); ?></td>
                                                <td class="text-center"><?php echo $score['Commit']["name_status"]; ?></td>
                                                <td class="text-center"><?php echo $score['Commit']["corrects"]; ?></td>
                                                <td class="text-center"><?php echo $score['Commit']["score"]; ?></td>
                                                <td class="text-center">
                                                    <?php echo $this->Html->link(__('Details'), array('controller' => 'commits', 'action' => 'details', $score['Commit']["id"]), array('class' => 'btn btn-color-one btn-sm')); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane" id="commits">
                                    <div class="form-group">
                                        <select ng-init="studentCommits=-1;exercise=<?php echo $exercise['Exercise']['id']; ?>" ng-model="studentCommits" ng-change="loadStudentCommits();" id="selectCommitStudent" class="form-control">
                                            <option value="-1"><?php echo __("Select a participant"); ?></option>
                                            <?php foreach ($scores as $key => $score): ?>
                                                <option value="<?php echo $score['Commit']["user_email"]; ?>"><?php echo $score["User"]['name']." (".$score["User"]['identifier'].")"; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div ng-show="loading"><i class="fa fa-spin fa-refresh"></i> <?php echo __("Loading")."..."; ?></div>
                                    <div id="commitsTable" ng-bind-html="studentCommitsContent">
                                    </div>
                                </div>
                                <div class="tab-pane" id="cases">
                                    <div ng-show="loading"><i class="fa fa-spin fa-refresh"></i> <?php echo __("Loading")."..."; ?></div>
                                    <div id="casesTable" ng-bind-html="casesContent" style="width: 100%; overflow-x: scroll">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?php echo __("New Commit"); ?></h3>
                            <div class="panel-heading-buttons">
                                <?php echo $this->Html->link('<i class="fa fa-google"></i> <i class="fa fa-calendar"></i>', array('controller' => 'Exercises', 'action' => 'exportExerciseToGoogleCalendar', h($exercise['Exercise']['id'])),array('escape' => false,'target'=>'_blank','class' => 'btn btn-color-two btn-sm')); ?>
                            </div>
                        </div>
                        <div class="panel-body submit-commit" ng-controller="CommitController" ng-init="loadFileUpload(<?php echo $exercise['Exercise']['id']; ?>)">
                            <div class="alert alert-danger" ng-show="alertMessage" role="alert">{{ alertMessage }}</div>
                            <p class="deadline-text text-center"><?php echo __("You can submit a file until"); ?><br><span class="deadline text-color-three"><?php echo $this->Time->format('d/m/Y H:i:s',$exercise['Exercise']['deadline']); ?></span> </p>
                            <span class="btn btn-color-three fileinput-button text-center">
                                <i class="fa fa-upload"></i> <span><?php echo __("Select File"); ?></span>
                                <input type="file" name="files[]" id="userFileUpload" />
                            </span>
                            <div class="row" ng-show="showFileInfo">
                                <div class="col-md-12">
                                    <div class="filename text-center">
                                        {{ filename }}
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="progressbar">
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" aria-valuenow="{{ progress }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ progress }}%;">
                                                <span class="sr-only">{{ progress }}% Complete</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="submit">
                                        <input type="submit" ng-click="submitFile()" ng-class="{disable: submitted}" ng-init="submitButton='<?php echo __("Confirm"); ?>';submitButtonDefault='<?php echo __("Confirm"); ?>';submitButtonReloading='<?php echo __("Reloading"); ?>...'" id="submit-confirm-button" class="btn btn-success" value="{{ submitButton }}" />
                                    </div>
                                </div>
                            </div>
                            <small><?php echo $this->element(Configure::read('Config.language'). DS . "commitAgreement"); ?></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?php echo __("Statistics"); ?></h3>
                        </div>
                        <div class="panel-body" ng-init="exerciseInfographic={gradesavg: <?php echo $avg; ?>,gradespect: <?php echo $plus5pct; ?>}">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="box infographic color-one">
                                        <i class="fa fa-star"></i>
                                        <span class="headline"><?php echo __("Exercise Grades Average"); ?></span>
                                        <span class="value">{{ exerciseInfographic.gradesavg }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="box infographic color-two">
                                        <i class="fa fa-pie-chart"></i>
                                        <span class="headline"><?php echo __("Grades >= 5.0 Percentage"); ?></span>
                                        <span class="value">{{ exerciseInfographic.gradespect }}%</span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div id="legend" class="flot-legend" ng-init="loadCasesStats();"></div>
                                    <flot dataset="dataset" options="options" height="290px"></flot>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
