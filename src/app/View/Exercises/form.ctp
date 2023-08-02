<?php echo $this->Form->create('Exercise');  ?>
<?php if (isset($isEdit) && $isEdit) {
    $initSelec = array();
    if (isset($this->request->data['Exercise']['AllowedFile']))
    foreach ($this->request->data['Exercise']['AllowedFile'] as $f) array_push($initSelec,$f);
    else if (isset($this->request->data['AllowedFile']))
    foreach ($this->request->data['AllowedFile'] as $f) array_push($initSelec,$f['id']);
}
?>
<div ng-controller="FormExerciseController" <?php if (isset($isEdit) && $isEdit) { ?> ng-init="initSelection=[<?php echo implode(',',$initSelec); ?>]" <?php } ?> >
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <?php if (isset($isEdit) && $isEdit) {
                                echo __('Edit Exercise');
                            } else {
                                echo __('New Exercise');
                            } ?>
                        </h3>
                        <?php if (!isset($isEdit) || !$isEdit) : ?>
                        <div class="panel-heading-buttons">
                            <?php echo $this->Html->link('<i class="fa fa-files-ogt"></i> '.__('Import Exercise'), array('controller' => 'Exercises','action' => 'import', $offering['Offering']['id']), array('class' => 'btn btn-color-one btn-sm','escape'=>false)); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="panel-body">
                        <fieldset>
                            <?php
                            if (isset($isEdit) && $isEdit) {
                                echo $this->Form->input('id');
                            }
                            echo $this->Form->input('offering_id',array('class' => 'span4', 'type' => 'hidden', 'value' => $offering['Offering']['id']));
                            echo $this->Form->input('title', array('label' => array('text' => __('Title'),'class' => 'control-label'), 'type' => 'text','class' => 'form-control','div' => array('class' => 'form-group')));
                            if (isset($this->request->data['Exercise']['markdown']) && !$this->request->data['Exercise']['markdown']) {
                                $class = "form-control summernote";
                                echo $this->Form->input('markdown',array("type" => "hidden","value" => "0"));
                            } else {
                                $class = "form-control";
                                echo $this->Form->input('markdown',array("type" => "hidden","value" => "1"));
                            }
                            echo $this->Form->input('description', array('label' => array('text' => __('Description'),'class' => 'control-label'), 'type' => 'textarea','class' => $class,'div' => array('class' => 'form-group')));
                            if (!isset($this->request->data['Exercise']['markdown']) || $this->request->data['Exercise']['markdown']) :
                            ?>
                            <p class="text-info">O run.codes utiliza a estrutura markdown nos textos de descrição, como sugestão, você pode utilizar um editor online, como o <a href="http://showdownjs.github.io/demo/" target="_blank">Showdown</a> ou o <a href="http://dillinger.io/" target="_blank">Dillinger</a> para preparar seu enunciado, e depois apenas colar o texto no campo acima</p>
                            <p class="text-info">Dica: Se você precisa incluir alguma equação no enunciado, insira uma imagem a partir da API latex do codecogs. Exemplo <a href="http://latex.codecogs.com/png.latex?1+sin(x)" target="_blank">http://latex.codecogs.com/png.latex?1+sin(x)</a></p>
                            <?php
                            endif;
                            echo $this->Form->input('open_date', array('label' => array('text' => __('Open Date'),'class' => 'control-label'),'type' => 'text','data-mask' => '99/99/9999 99:99:99','class' => 'form-control datetimepicker','div' => array('class' => 'form-group')));
                            echo $this->Form->input('deadline', array('label' => array('text' => __('Deadline'),'class' => 'control-label'),'type' => 'text','data-mask' => '99/99/9999 99:99:99','class' => 'form-control datetimepicker','div' => array('class' => 'form-group'))); ?>
                            <div class="checkbox">
                                <label>
                                    <?php echo $this->Form->input('show_before_opening', array('label' => false, 'type' => 'checkbox','class' => 'checkbox'));
                                    echo __('Show exercise before open date to participants'); ?>
                                </label>
                            </div>
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
                        <h3 class="panel-title"><?php echo __('Exercise Files'); ?></h3>
                    </div>
                    <div class="panel-body">
                        <div ng-controller="MultipleUploadController"
                             ng-init="loadFileUpload('#fileupload','#files-drop-zone1');
                             uploadText='<?php echo __("You can drag and drop your files here")." ".__("or"); ?>';
                             uploadingText='<?php echo __("Uploading..."); ?>';
                             uploadMessage=uploadText;
                             <?php if (isset($isEdit) && $isEdit && count($this->request->data['ExerciseFile']) > 0) {
                               foreach  ($this->request->data['ExerciseFile'] as $file) {
                                   echo "addExistentFile('{$file['ExerciseFile']['path']}',{$file['ExerciseFile']['id']},0);";
                               }
                             }
                             ?>
                             ">
                            <p>
                                <?php echo __("You can attach any file to your exercise, for example, a pdf with detailed instructions or additional libraries. All participants will have access to these files"); ?>
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
                                            <input type='hidden' name='data[ExerciseFile][{{ $index }}][path]' value='{{ file.name }}' /><input type='hidden' name='data[ExerciseFile][{{ $index }}][hash]' value='{{ file.hash_time }}' />
                                        </td>
                                        <td ng-if="file.existent === true">
                                            <a class="btn btn-sm btn-color-three" ng-if="!file.remove" ng-click="removeExistentFile($index)"><?php echo __("Remove"); ?></a>
                                            <a class="btn btn-sm btn-color-one" ng-if="file.remove" ng-click="undoRemoveExistentFile($index)"><?php echo __("Undo"); ?></a>
                                            <input type='hidden' name='data[RemoveExerciseFile][{{ file.id }}]' value='{{ file.remove }}' />
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div id="files-drop-zone1" class="drop-zone">
                                <div class="files-progress-bar" style="width: {{ progress }}%"></div>
                                <p><span class="file-progress-text">{{ uploadMessage }}</span> <span class="btn btn-info" style="position: relative; overflow: hidden"><?php echo __("Select Files"); ?><input id="fileupload" type="file" name="files[]" data-url="/ExerciseFiles/fileUpload/" multiple  style="opacity: 0; position: absolute; top: 0; right: 0; height: 100px; width: 300px; margin: 0; cursor: pointer"></span></p>
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
                        <h3 class="panel-title"><?php echo __('Assessment'); ?></h3>
                    </div>
                    <div class="panel-body">
                        <?php
                        echo $this->Form->input('type', array('label' => array('text' => __('Exercise Type'),'class' => 'control-label'),'type' => 'select','ng-init'=>'filesType='.((isset($this->request->data["Exercise"]["type"])) ? $this->request->data["Exercise"]["type"] : 0).';reloadFileTypes();','ng-model' => 'filesType','ng-change' => 'reloadFileTypes();updateChosen;','options' => $types,'class' => 'form-control','div' => array('class' => 'form-group')));
                        ?>
                        <div class="form-group required" ng-show="filesType!=2">
                            <label for="ExerciseAllowedFile" class="control-label"><?php echo __('Select All Allowed File Types'); ?> <i class="fa fa-refresh fa-spin" ng-show="loading"></i></label>
                            <select name="data[Exercise][AllowedFile][]" multiple data-placeholder="<?php echo __('Select the allowed languages'); ?>" class="form-control chosen-select">
                                <option ng-repeat="file in allowedFiles" value="{{ file.id }}">{{ file.name }}</option>
                            </select>
                        </div>
                        <p><?php echo __("Python 2 e 3 não são compatíveis mutuamente para entregas de arquivos .py. Se você deseja aceitar códigos em ambas versões, ao menos para uma delas deverá ser indicada explicitamente via entrega com Zip/Makefile") ; ?></p>
                        <div ng-controller="MultipleUploadController" ng-init="loadFileUpload('#compilationfileupload','#files-drop-zone2');uploadText='<?php echo __("You can drag and drop your files here")." ".__("or"); ?>';uploadingText='<?php echo __("Uploading..."); ?>';uploadMessage=uploadText;
                             <?php if (isset($isEdit) && $isEdit && count($this->request->data['CompilationFile']) > 0) {
                            foreach  ($this->request->data['CompilationFile'] as $file) {
                                echo "addExistentFile('{$file['CompilationFile']['path']}',{$file['CompilationFile']['id']},0);";
                                }
                            }
                            ?>
                             ">
                            <p>
                                <?php echo __("You can attach any file to your compilation process, for example, a code file with the main function. All files listed above will be copied to the folder of the source code of each submission at the time of compilation").". ".__("However, they will not be available at the time of execution")."."; ?>
                            </p>
                            <div class="mail-files-list">
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
                                            <input type='hidden' name='data[CompilationFile][{{ $index }}][path]' value='{{ file.name }}' /><input type='hidden' name='data[CompilationFile][{{ $index }}][hash]' value='{{ file.hash_time }}' />
                                        </td>
                                        <td ng-if="file.existent === true">
                                            <a class="btn btn-sm btn-color-three" ng-if="!file.remove" ng-click="removeExistentFile($index)"><?php echo __("Remove"); ?></a>
                                            <a class="btn btn-sm btn-color-one" ng-if="file.remove" ng-click="undoRemoveExistentFile($index)"><?php echo __("Undo"); ?></a>
                                            <input type='hidden' name='data[RemoveCompilationFile][{{ file.id }}]' value='{{ file.remove }}' />
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div id="files-drop-zone2" class="drop-zone">
                                <div class="files-progress-bar" style="width: {{ progress }}%"></div>
                                <p><span class="file-progress-text">{{ uploadMessage }}</span> <span class="btn btn-info" style="position: relative; overflow: hidden"><?php echo __("Select Files"); ?><input id="compilationfileupload" type="file" name="files[]" data-url="/CompilationFiles/fileUpload/" multiple  style="opacity: 0; position: absolute; top: 0; right: 0; height: 100px; width: 300px; margin: 0; cursor: pointer"></span></p>
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
                        <h3 class="panel-title"><?php echo __('Share Exercise'); ?></h3>
                    </div>
                    <div class="panel-body">
                        <p>
                            <?php echo __("Share exercise allow you to have this exercise in more than one active classroom. All classrooms will have the same exercise, including de deadline, allowed files and test cases. Any change in any exercise configuration will be propagated to all classrooms it is shared", __('Submit')); ?>
                        </p>
                        <?php foreach ($others_offerings_list as $other_offering_id => $other_offering_name) : ?>
                            <div class="checkbox">
                                <label>
                                    <?php echo $this->Form->input('share.'.$other_offering_id, array('label' => false, 'type' => 'checkbox','class' => 'checkbox'));
                                    echo h($other_offering_name); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        <?php if (isset($others_offerings_ghost) && is_array($others_offerings_ghost) && count($others_offerings_ghost) > 0) {
                          echo __("This exercise is being shared among the following classrooms").": <br>".implode(", ",$others_offerings_ghost);
                        }
                        ?>
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
                        <h3 class="panel-title"><?php echo __('Are you sure that all exercise information is correct?'); ?></h3>
                    </div>
                    <div class="panel-body">
                        <p>
                            <?php echo __("By clicking in %s you will register the exercise, in the next page you will can add the test cases", __('Submit')); ?>
                        </p>
                        <?php echo $this->Form->end(array('label' => __('Submit'), 'class' => 'btn btn-color-one ')); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
