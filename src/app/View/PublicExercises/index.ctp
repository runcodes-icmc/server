<?php echo $this->element(Configure::read('Config.language'). DS . "publicDatabaseHeader"); ?>
<div class="container-fluid">
    <div class="row">
        <?php foreach ($publicExercises as $ke => $exercise) : ?>
        <div class="col-md-12" ng-init="show<?php echo $exercise['PublicExercise']['id']; ?>=false">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo $exercise['Exercise']['title']; ?> (<?php echo $exercise['PublicExercise']['level_name']; ?>)</h3>
                    <div class="panel-heading-buttons">
                        <?php echo $this->Html->link('<i class="fa fa-plus"></i> '.__('View Description'),"#",array('escape' => false,'class' => 'btn btn-sm btn-color-one','ng-hide' => 'show'.$exercise['PublicExercise']['id'],'ng-click' => 'show'.$exercise['PublicExercise']['id'].'=true;$event.preventDefault()')); ?>
                        <?php echo $this->Html->link('<i class="fa fa-minus"></i> '.__('Hide Description'),"#",array('escape' => false,'class' => 'btn btn-sm btn-color-one','ng-show' => 'show'.$exercise['PublicExercise']['id'],'ng-click' => 'show'.$exercise['PublicExercise']['id'].'=false;$event.preventDefault()')); ?>
                        <?php if (count($userOfferingsMenu) > 0): ?>
                            <div class="btn-group">
                                <button type="button" class="btn btn-color-two btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <?php echo __("Import"); ?> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <?php foreach ($userOfferingsMenu as $k => $off) : ?>
                                        <li><?php echo $this->Html->link($off, array('controller' => 'Exercises', 'action' => 'import',$k,$exercise['PublicExercise']['id']), array('class' => '')); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="panel-body">
                    <?php echo __("Level").": ".$exercise['PublicExercise']['level_name']; ?><br>
                    <?php echo __("Number of Cases").": ".$exercise['Exercise']['num_cases']; ?><br>
                    <?php echo __("Keywords").": ".$exercise['PublicExercise']['keywords']; ?><br>
                    <?php if (strlen($exercise['PublicExercise']['obs']) > 0) echo __("Obs").": ".$exercise['PublicExercise']['obs']; ?><br>
                    <div ng-show="show<?php echo $exercise['PublicExercise']['id']; ?>">
                        <?php if (!$exercise['Exercise']['markdown']) : echo "<strong>".__("Description").": </strong><br>". $exercise['Exercise']['description']; else : ?>
                            <div ng-controller="ExerciseDescriptionController" ng-init="area=<?php echo $ke; ?>">
                                <textarea class="markdown<?php echo $ke; ?>" style="display:none;" ng-init="loadMarkdownDescription()"><?php echo $exercise['Exercise']['description']; ?></textarea><div class="insertMarkdown" ng-bind-html="html"></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <p>
                        <?php
                        echo $this->Paginator->counter(array(
                            'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
                        ));
                        ?>
                    </p>
                    <div>
                        <ul class="pagination pagination-large" style="margin: 0">
                            <?php
                            echo $this->Paginator->prev(__('prev'), array('tag' => 'li'), null, array('tag' => 'li','class' => 'disabled','disabledTag' => 'a'));
                            echo $this->Paginator->numbers(array('separator' => '','currentTag' => 'a', 'currentClass' => 'active','tag' => 'li','first' => 1));
                            echo $this->Paginator->next(__('next'), array('tag' => 'li','currentClass' => 'disabled'), null, array('tag' => 'li','class' => 'disabled','disabledTag' => 'a'));
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>