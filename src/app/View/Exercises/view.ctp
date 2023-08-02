<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="offerings view panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo $exercise['Exercise']['title']; ?></h3>
                </div>
                <div class="panel-body">
                    <?php echo "<strong>".__("Subject").": </strong>".$exercise['Offering']['Course']['code']." - ".$exercise['Offering']['Course']['title']; ?>
                    <br>
                    <?php echo "<strong>".__("Deadline").": </strong>".$this->Time->format('d/m/Y H:i:s',$exercise['Exercise']['deadline']); ?>
                    <?php if($exercise['Exercise']['isOpen']): ?>
                        <span class="label label-success"><?php echo __('Open'); ?></span>
                    <?php else: ?>
                        <span class="label label-danger"><?php echo __('Closed'); ?></span>
                    <?php endif; ?>
                    <div ng-init="descr=<?php if(count($commits)) echo "0"; else echo "1"; ?>" ng-show="descr==1">
                        <?php if (!$exercise['Exercise']['markdown']) : echo "<strong>".__("Description").": </strong><br>". $exercise['Exercise']['description']; else : ?>
                            <div ng-controller="ExerciseDescriptionController">
                                <textarea class="markdown" style="display:none;" ng-init="loadMarkdownDescription()"><?php echo $exercise['Exercise']['description']; ?></textarea><div class="insertMarkdown" ng-bind-html="html"></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <br>
                    <?php echo $this->Html->link(__('Hide Description'), '#', array('ng-show' => 'descr==1','ng-click' => 'descr=0;$event.preventDefault();','class' => 'btn btn-color-two btn-sm')); ?>
                    <?php echo $this->Html->link(__('View Description'), '#', array('ng-show' => 'descr==0','ng-click' => 'descr=1;$event.preventDefault();','class' => 'btn btn-color-two btn-sm')); ?>
                    <br>
                    <?php if(count($exercise['ExerciseFile'])): ?>
                        <br><?php echo "<strong>".__("Files").':</strong>'; ?>
                        <ul class="nav nav-pills">
                        <?php foreach($exercise['ExerciseFile'] as $key => $file): ?>
                            <li class="active">
                                <?php echo $this->Html->link(h($file['ExerciseFile']['path']),array('controller' => 'ExerciseFiles', 'action' => 'fileDownload', h($file['ExerciseFile']['id']))); ?>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if(count($exercise['AllowedFile'])): ?>
                        <br><?php echo "<strong>".__("This exercise accept the following types").':</strong>'; ?>
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
                    <?php if ($hasOpenCases) : ?>
                        <?php echo $this->Html->link('<i class="fa fa-download"></i> '.__('Download Test Cases'), array('controller' => 'Exercises','action' => 'downloadCases',$exercise['Exercise']['id']),array('escape' => false,'class' => 'btn btn-info btn-sm','style' => 'margin-top: 5px')); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("New Commit"); ?></h3>
                    <div class="panel-heading-buttons">
                        <?php echo $this->Html->link('<i class="fa fa-google"></i> <i class="fa fa-calendar"></i>', array('controller' => 'Exercises', 'action' => 'exportExerciseToGoogleCalendar', h($exercise['Exercise']['id'])),array('escape' => false,'target'=>'_blank','class' => 'btn btn-color-two btn-sm')); ?>
                    </div>
                </div>
                <?php if($exercise['Exercise']['isOpen']): ?>
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
                <?php else: ?>
                <div class="panel-body submit-commit">
                    <p class="deadline-text text-center"><?php echo __("The exercise is closed"); ?><br><span class="deadline text-color-three"><?php echo $this->Time->format('d/m/Y H:i:s',$exercise['Exercise']['deadline']); ?></span> </p>
                    <span class="btn btn-color-three text-center disabled">
                        <i class="fa fa-upload"></i> <span><?php echo __("Closed"); ?></span>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if(count($commits)): ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("My Last Commit"); ?></h3>
                    <div class="panel-heading-buttons">
                        <?php echo $this->Html->link('<i class="fa fa-download"></i> '.__('Download'), array('controller' => 'Commits','action' => 'download',$lastCommit['Commit']['id']),array('escape' => false,'class' => 'btn btn-info btn-sm')); ?>
                    </div>
                </div>
                <div class="panel-body" ng-controller="LastCommitController">
                    <div ng-if="loading">
                        <i class="fa fa-refresh fa-spin"></i> <?php echo __("Wait. Your last commit is being processed. (You do not need to refresh this page)"); ?>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-3">
                            <div class="row">
                                <div class="col-md-12" ng-init="commit.id=<?php echo $lastCommit['Commit']['id']; ?>;commit.status.value=<?php echo $lastCommit['Commit']['status']; ?>;commit.status.color='<?php echo $lastCommit['Commit']['status_color']; ?>';commit.status.name='<?php echo $lastCommit['Commit']['name_status']; ?>';">
                                    <div class="box infographic color-{{commit.status.color}}">
                                        <div class="headline">status</div>
                                        <div class="text-value">{{ commit.status.name }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 col-sm-9">
                            <div class="row">
                                <div class="col-md-4 col-sm-6" ng-init="commit.compiled.status=<?php echo ($lastCommit['Commit']['compiled']) ? "true" : "false"; ?>;commit.compiled.color='<?php echo $lastCommit['Commit']['compiled_color']; ?>';">
                                    <div class="box infographic color-{{commit.compiled.color}}">
                                        <div class="headline"><?php echo __("compiled"); ?></div>
                                        <div class="value">
                                            <span ng-show="commit.compiled.status"><?php echo __("Yes"); ?></span>
                                            <span ng-show="!commit.compiled.status"><?php echo __("No"); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 hidden-sm" ng-init="commit.cases.correct=<?php echo $lastCommit['Commit']['corrects']; ?>;commit.cases.total=<?php echo $exercise['Exercise']['num_cases']; ?>;commit.cases.color='<?php echo $lastCommit['Commit']['correct_color']; ?>';">
                                    <div class="box infographic color-{{commit.cases.color}}">
                                        <div class="headline"><?php echo __("correct cases"); ?></div>
                                        <div class="value">{{commit.cases.correct}}/{{commit.cases.total}}</div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6" ng-init="commit.score.value='<?php echo $lastCommit['Commit']['score']; ?>';commit.score.color='<?php echo $lastCommit['Commit']['score_color']; ?>';startRefreshing();">
                                    <div class="box infographic color-{{commit.score.color}}">
                                        <div class="headline"><?php echo __("score"); ?></div>
                                        <div class="value">{{commit.score.value}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if (isset($lastCommit['ExerciseCase']) && count($lastCommit['ExerciseCase']) > 0) : ?>
                    <table class="table table-hover table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center"><?php echo __('Case'); ?></th>
                                <th class="text-center"><?php echo __('Status'); ?></th>
                                <th class="text-center"><?php echo __('CPU Time Used'); ?></th>
                                <th class="text-center"><?php echo __('Mem Size Used'); ?></th>
                                <th class="text-center"><?php echo __('Message'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $casesList = array(-1 => __("Select an exercise case")."...");
                            foreach ($lastCommit['ExerciseCase'] as $key => $case):
                                $text = __("Case") . " " . ($key + 1);
                                $text.= ($case['CommitsExerciseCase']['status'] == 1) ? " (".__('Accepted').")" : " (".__('Rejected').")";
                                $casesList[$case['CommitsExerciseCase']['id']] = $text;
                            ?>
                                <tr>
                                    <td class="text-center"><?php echo __("Case") . " " . ($key + 1); ?></td>
                                    <td class="text-center">
                                        <?php if ($case['CommitsExerciseCase']['status'] == 1) :  ?>
                                        <span class="label label-success"><?php echo __('Accepted'); ?></span>
                                        <?php else: ?>
                                        <span class="label label-danger"><?php echo __('Rejected'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?php echo h($case['CommitsExerciseCase']['cputime'])." ".__("sec"); ?></td>
                                    <td class="text-center"><?php echo h($case['CommitsExerciseCase']['memused'])." Kb"; ?> </td>
                                    <td class="text-center"><?php echo h($case['CommitsExerciseCase']['status_message']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div id="casesDetails" ng-bind-html="commitsExerciseCaseDetails">

                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (isset($casesList) && count($casesList) > 0) : ?>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("Cases Details"); ?></h3>
                </div>
                <div class="panel-body" ng-controller="ExerciseCasesController">
                    <?php echo $this->Form->input('case', array('label' => false, 'type' => 'select','options' => $casesList,'class' => 'form-control','ng-model' => 'caseId','ng-change' => 'loadCase()','div' => array('class' => 'form-group')));  ?>
                    <div class="commits-exercise-case-page" ng-show="caseId != -1" ng-bind-html="commitsExerciseCaseView">

                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="col-lg-12 col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("Commits History"); ?></h3>
                </div>
                <table class="table table-hover table-striped table-bordered">
                    <thead>
                    <tr>
                        <th><?php echo __('Date'); ?></th>
                        <th class="text-center"><?php echo __('Status'); ?></th>
                        <th class="text-center"><?php echo __('Corrects'); ?></th>
                        <th class="text-center"><?php echo __('Score'); ?></th>
                        <th class="text-center"><?php echo __('Actions'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($commits as $key => $commit): ?>
                        <tr>
                            <td class="text-center"><?php echo $this->Time->format('d/m/Y H:i:s',$commit['Commit']["commit_time"]); ?></td>
                            <td class="text-center"><span class="label label-<?php echo $commit['Commit']['status_color']; ?>"><?php echo $commit['Commit']['name_status']; ?></span></td>
                            <td class="text-center"><span class="label label-<?php echo $commit['Commit']['correct_color']; ?>"><?php echo $commit['Commit']['corrects']; ?>/<?php echo $commit['Exercise']['num_cases']; ?></td>
                            <td class="text-center"><span class="label label-<?php echo $commit['Commit']['score_color']; ?>"><?php echo $commit['Commit']['score']; ?></span></td>
                            <td class="text-center">
                                <?php echo $this->Html->link('<i class="fa fa-download"></i> '.__('Download'), array('controller' => 'Commits','action' => 'download',$commit['Commit']['id']),array('escape' => false,'class' => 'btn btn-info btn-sm')); ?>
                                <?php echo $this->Html->link(__('Details'), array('controller' => 'commits', 'action' => 'details', $commit['Commit']["id"]), array('class' => 'btn btn-color-one btn-sm')); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>
