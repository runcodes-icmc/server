<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo $offering['Course']['code']." - ".$offering['Course']['title']; ?></h3>
                </div>
                <div class="panel-body">
                    <p>
                        <?php echo __("Classroom").": ".$offering['Offering']['classroom']; ?></br>
                        <?php echo __("Active until").": "; ?><strong><?php echo $this->Time->format('d/m/Y',$offering['Offering']['end_date']); ?></strong>
                    </p>
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
                    <h3 class="panel-title"><?php echo __("Send Message"); ?></h3>
                </div>
                <div class="panel-body">
                    <p><?php echo __("All participants enrolled at this classroom will receive this message"); ?></p>
                    <?php echo $this->Form->create(null, array('url' => array('controller' => 'offerings','action'=> 'email', $offering['Offering']['id']))); ?>
                    <fieldset>
                        <?php
                        echo $this->Form->input('subject',array('label' => array('text' => __('Email Subject'),'class' => 'control-label'),'class' => 'form-control','type' => 'text','div' => array('class' => 'form-group')));
                        ?>
                        <?php echo $this->Form->input('message', array('label' => array('text' => __('Message'),'class' => 'control-label'), 'type' => 'textarea','class' => 'form-control summernote','div' => array('class' => 'form-group'))); ?>
                        <?php if (1==2): //desabilitado ?>
                        <div ng-controller="MultipleUploadController" ng-init="loadFileUpload('#fileupload');uploadText='<?php echo __("You can drag and drop your files here")." ".__("or"); ?>';uploadingText='<?php echo __("Uploading..."); ?>';uploadMessage=uploadText">
                            <p>
                                <?php echo __("Attachments"); ?>
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
                                            <td>{{ file.size }} bytes</td>
                                            <td>
                                                <a class="btn btn-sm btn-color-three" ng-click="removeFile($index)"><?php echo __("Remove"); ?></a>
                                                <input type='hidden' name='data[Files][{{ $index }}][path]' value='{{ file.name }}' /><input type='hidden' name='data[Files][{{ $index }}][hash]' value='{{ file.hash_time }}' />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div id="files-drop-zone" class="drop-zone">
                                <div class="files-progress-bar" style="width: {{ progress }}%"></div>
                                <p><span class="file-progress-text">{{ uploadMessage }}</span> <span class="btn btn-info" style="position: relative; overflow: hidden"><?php echo __("Select Files"); ?><input id="fileupload" type="file" name="files[]" data-url="/Offerings/mailFileUpload/<?php echo $offering['Offering']['id']; ?>" multiple  style="opacity: 0; position: absolute; top: 0; right: 0; height: 100px; width: 300px; margin: 0; cursor: pointer"></span></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </fieldset>
                    <?php echo $this->Form->end(array('label' => __('Send'), 'class' => 'btn btn-color-one ')); ?>
                </div>
            </div>
        </div>
    </div>
</div>