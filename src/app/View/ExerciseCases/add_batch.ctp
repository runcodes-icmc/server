<?php echo $this->Form->create('ExerciseCase'); ?>
<div ng-controller="FormExerciseCaseController">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo __('Exercise Cases: ').$exercise['Exercise']['title']; ?></h3>
                    </div>
                    <div class="panel-body">
                        <fieldset>
                            <legend><?php echo __('Add Exercise Cases in Batch'); ?></legend>
                            <div class="checkbox">
                                <label>
                                    <?php echo $this->Form->input('show_input', array('label' => false, 'type' => 'checkbox','class' => '','div' => false));
                                    echo __('Show input to participants'); ?>
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <?php  echo $this->Form->input('show_expected_output', array('label' => false, 'type' => 'checkbox','class' => '','div' => false));
                                    echo __('Show expected output to participants'); ?>
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <?php  echo $this->Form->input('show_user_output', array('label' => false, 'type' => 'checkbox','class' => '','div' => false));
                                    echo __('Show user output'); ?>
                                </label>
                            </div>
                            <?php
                            echo $this->Form->input('exercise_id',array('class' => 'form-control', 'type' => 'hidden', 'value' => $exercise['Exercise']['id']));
//                            echo $this->Form->input('maxmemsize', array('label' => array('text' => __('Max. Memory Size'),'class' => 'control-label'), 'type' => 'select','options'=>$memsizeoptions,'class' => 'form-control','div' => array('class' => 'form-group')));
//                            echo $this->Form->input('stacksize', array('label' => array('text' => __('Max. Stack Size'),'class' => 'control-label'), 'type' => 'select','options'=>$stacksizeoptions,'class' => 'form-control','div' => array('class' => 'form-group')));
//                            echo $this->Form->input('file_size', array('label' => array('text' => __('Max. Blocks Use'),'class' => 'control-label'), 'type' => 'select','options'=>$blocksizeoptions,'class' => 'form-control','div' => array('class' => 'form-group')));
                            echo $this->Form->input('cputime', array('label' => array('text' => __('Max. CPU Time'),'class' => 'control-label'),'value' => '3', 'type' => 'select','options'=>$timeoptions,'class' => 'form-control','div' => array('class' => 'form-group')));
                            ?>
                            
                        </fieldset>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo __('Exercise Case Files in Batch'); ?></h3>
                    </div>
                    <div class="panel-body" ng-init="outputType='<?php echo (isset($this->request->data['ExerciseCase']['output_type'])) ? $this->request->data['ExerciseCase']['output_type'] : '1'; ?>'">
                        <?php echo $this->Form->input('output_type', array('label' => array('text' => __('Output Type'),'class' => 'control-label'), 'type' => 'select','options'=>array("1" => __("Text"),"2" => __("Numbers with Error"),"3" => __("Binary")),'ng-model' => 'outputType','class' => 'form-control','div' => array('class' => 'form-group')));  ?>
                        <div ng-show="outputType == '1'" id="output-text">
                            <p class="text-info"><?php echo __("For the textual output the system will compare the student output with the case output. The user output must be identical with the case output to be correct"); ?></p>
                        </div>
                        <div ng-show="outputType == '2'" id="output-numbers">
                            <p class="text-info"><?php echo __("For the numeric output the system will compare the student output numbers with the case output numbers considering the maximum absolute error allowed. You must inform the maximum absolute error allowed for the output numbers"); ?></p>
                            <?php echo $this->Form->input('outputError', array('label' => array('text' => __('Output Error Accepted'),'class' => 'control-label'), 'type' => 'number', 'step' => 'any', 'min' => '0','class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                        </div>
                        <div ng-show="outputType == '3'" id="output-binary">
                            <p class="text-info"><?php echo __("For the binary output the system will consider the user output as a binary (The student must write the binary data in the stardard output). You must inform the file name for comparison and upload the file in the box below"); ?></p>
                        </div>
                        <div ng-controller="MultipleUploadController"
                             ng-init="loadFileUpload('#fileupload');
                             uploadText='<?php echo __("You can drag and drop your files here")." ".__("or"); ?>';
                             uploadingText='<?php echo __("Uploading..."); ?>';
                             uploadMessage=uploadText;">
                            <p>
                                <?php echo __("Attach here each case input and output. <br>You must follow this model: <br>Each case must have two files: [case_name].in is the input file and [case_name].out is the output file. <br>Each case name must be different, and can not have spaces or special caracters. <br>Others files with pattern [case_name].ext may be added as case file in the same case.<br>If any [case_name].in does not have an correspondent [case_name].out or vice-versa, it will be disconsidered."); ?>
                            </p>
                            <div class="files-list">
                                <table class="table table-hover table-striped table-bordered" ng-show="files.length > 0">
                                    <thead>
                                    <th><?php echo __("File"); ?></th>
                                    <th><?php echo __("Size"); ?></th>
                                    <th><?php echo __("Actions"); ?></th>
                                    </thead>
                                    <tbody>
                                    <tr ng-repeat="file in files">
                                        <td>{{ file.realname }}</td>
                                        <td ng-if="file.existent !== true">{{ file.size }} bytes</td>
                                        <td ng-if="file.existent === true"><?php echo __("File in disk"); ?></td>
                                        <td ng-if="file.existent !== true">
                                            <a class="btn btn-sm btn-color-three" ng-click="removeFile($index)"><?php echo __("Remove"); ?></a>
                                            <input type='hidden' name='data[ExerciseCaseFile][{{ $index }}][path]' value='{{ file.name }}' /><input type='hidden' name='data[ExerciseCaseFile][{{ $index }}][hash]' value='{{ file.hash_time }}' />
                                        </td>
                                        <td ng-if="file.existent === true">
                                            <a class="btn btn-sm btn-color-three" ng-if="!file.remove" ng-click="removeExistentFile($index)"><?php echo __("Remove"); ?></a>
                                            <a class="btn btn-sm btn-color-one" ng-if="file.remove" ng-click="undoRemoveExistentFile($index)"><?php echo __("Undo"); ?></a>
                                            <input type='hidden' name='data[RemoveExerciseCaseFile][{{ file.id }}]' value='{{ file.remove }}' />
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div id="files-drop-zone" class="drop-zone">
                                <div class="files-progress-bar" style="width: {{ progress }}%"></div>
                                <p><span class="file-progress-text">{{ uploadMessage }}</span> <span class="btn btn-info" style="position: relative; overflow: hidden"><?php echo __("Select Files"); ?><input id="fileupload" type="file" name="files[]" data-url="/ExerciseCaseFiles/fileUpload/" multiple  style="opacity: 0; position: absolute; top: 0; right: 0; height: 100px; width: 300px; margin: 0; cursor: pointer"></span></p>
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
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo __('The End'); ?></h3>
                    </div>
                    <div class="panel-body">
                        <p>
                            <?php echo __("By clicking in %s you will register the exercise case", __('Submit')); ?>
                        </p>
                        <?php echo $this->Form->end(array('label' => __('Submit'), 'class' => 'btn btn-color-one ')); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>