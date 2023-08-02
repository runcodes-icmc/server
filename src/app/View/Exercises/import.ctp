<?php echo $this->Form->create('Exercise');  ?>
<?php if ($publicExercise) : ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo __("Import Public Exercise"); ?></h3>
                        <div class="panel-heading-buttons">
                            <?php echo $this->Html->link('<i class="fa fa-plus"></i> '.__('View Description'),"#",array('escape' => false,'class' => 'btn btn-sm btn-color-one','ng-hide' => 'show'.$publicExercise['PublicExercise']['id'],'ng-click' => 'show'.$publicExercise['PublicExercise']['id'].'=true;$event.preventDefault()')); ?>
                            <?php echo $this->Html->link('<i class="fa fa-minus"></i> '.__('Hide Description'),"#",array('escape' => false,'class' => 'btn btn-sm btn-color-one','ng-show' => 'show'.$publicExercise['PublicExercise']['id'],'ng-click' => 'show'.$publicExercise['PublicExercise']['id'].'=false;$event.preventDefault()')); ?>
                        </div>
                    </div>
                    <div class="panel-body">
                        <?php echo $this->element(Configure::read("Config.language") . DS . "importExerciseInfo"); ?>
                        <?php echo __("Exercise").": ".$publicExercise['Exercise']['title']; ?><br>
                        <?php echo __("Level").": ".$publicExercise['PublicExercise']['level_name']; ?><br>
                        <?php echo __("Number of Cases").": ".$publicExercise['Exercise']['num_cases']; ?><br>
                        <?php
                        echo $this->Form->input('exercise_id', array('label' => false,'type' => 'hidden','value'=>$publicExercise['PublicExercise']['exercise_id']));
                        ?>
                        <div ng-show="show<?php echo $publicExercise['PublicExercise']['id']; ?>">
                            <?php if (!$publicExercise['Exercise']['markdown']) : echo "<strong>".__("Description").": </strong><br>". $publicExercise['Exercise']['description']; else : ?>
                                <div ng-controller="ExerciseDescriptionController">
                                    <textarea class="markdown" style="display:none;" ng-init="loadMarkdownDescription()"><?php echo $publicExercise['Exercise']['description']; ?></textarea><div class="insertMarkdown" ng-bind-html="html"></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else : ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo __("Import Exercise"); ?></h3>
                        <?php if ($logged_user["type"] > 2): ?>
                        <div class="panel-heading-buttons" ng-init="normalImport=true">
                            <?php echo $this->Html->link('<i class="fa fa-plus"></i> '.__('Type Exercise Id'),"#",array('escape' => false,'class' => 'btn btn-sm btn-color-one','ng-show' => 'normalImport','ng-click' => 'normalImport=false;$event.preventDefault()')); ?>
                            <?php echo $this->Html->link('<i class="fa fa-minus"></i> '.__('Exercise Selector'),"#",array('escape' => false,'class' => 'btn btn-sm btn-color-one','ng-hide' => 'normalImport','ng-click' => 'normalImport=true;$event.preventDefault()')); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($logged_user["type"] > 2) { ?>
                    <div class="panel-body"  ng-hide="normalImport">
                        <?php
                        echo $this->Form->input('exercise_id', array('label' => array('text' => __('Exercise Id'),'class' => 'control-label'),'type' => 'text','class' => 'form-control','div' => array('class' => 'form-group')));
                        ?>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<div class="container-fluid" ng-controller="FormExerciseController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("Deadline"); ?></h3>
                    <div class="panel-heading-buttons">
                    </div>
                </div>
                <div class="panel-body">
                    <fieldset>
                        <?php
                        echo $this->Form->input('offering_id',array('class' => 'span4', 'type' => 'hidden', 'value' => $offering['Offering']['id']));
                        echo $this->Form->input('open_date', array('label' => array('text' => __('Open Date'),'class' => 'control-label'),'type' => 'text','data-mask' => '99/99/9999 99:99:99','class' => 'form-control datetimepicker','div' => array('class' => 'form-group')));
                        echo $this->Form->input('deadline', array('label' => array('text' => __('Deadline'),'class' => 'control-label'),'type' => 'text','data-mask' => '99/99/9999 99:99:99','class' => 'form-control datetimepicker','div' => array('class' => 'form-group'))); ?>
                        <div class="checkbox">
                            <label>
                                <?php echo $this->Form->input('show_before_opening', array('label' => false, 'type' => 'checkbox','class' => 'checkbox'));
                                echo __('Show exercise before open date to participants'); ?>
                            </label>
                        </div>
                    </fieldset>
                    <?php
                    echo $this->Form->input('type', array('label' => array('text' => __('Exercise Type'),'class' => 'control-label'),'type' => 'select','ng-init'=>'filesType='.((isset($this->request->data["Exercise"]["type"])) ? $this->request->data["Exercise"]["type"] : 0).';reloadFileTypes();','ng-model' => 'filesType','ng-change' => 'reloadFileTypes();updateChosen;','options' => $types,'class' => 'form-control','div' => array('class' => 'form-group')));
                    ?>
                    <div class="form-group required <?php if (isset($this->validationErrors['Exercise']['AllowedFile'])) echo "error" ?>" ng-show="filesType!=2">
                        <label for="ExerciseAllowedFile" class="control-label"><?php echo __('Select All Allowed File Types'); ?> <i class="fa fa-refresh fa-spin" ng-show="loading"></i></label>
                        <select name="data[Exercise][AllowedFile][]" multiple data-placeholder="<?php echo __('Select the allowed languages'); ?>" class="form-control chosen-select">
                            <option ng-repeat="file in allowedFiles" value="{{ file.id }}">{{ file.name }}</option>
                        </select>
                        <?php if (isset($this->validationErrors['Exercise']['AllowedFile'])) : ?>
                            <div class="error-message"><?php echo implode("<br>",$this->validationErrors['Exercise']['AllowedFile']); ?></div>
                        <?php endif; ?>
                    </div>
                    <p><?php echo __("Python 2 e 3 não são compatíveis mutualmente para entregas de arquivos .py. Se você deseja aceitar códigos em ambas versões, ao menos para uma delas deverá ser indica explicitamente via entrega com Zip/Makefile") ; ?></p>

                    <?php echo $this->Form->end(array('label' => __('Submit'), 'class' => 'btn btn-color-one ')); ?>
                </div>
            </div>
        </div>
    </div>
</div>

