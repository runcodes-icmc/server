<?php echo $this->Form->create('ExerciseCase'); ?>
<div class="container-fluid">
    <div class="row-fluid">
        <div class="exercises index widget span12">
            <div class="widget-header">
                <h3><?php echo __('Exercise Cases: ').$exercise['Exercise']['title']; ?></h3>
            </div>
            <div class="widget-body">
                <fieldset>
                    <legend><?php echo __('New Exercise Case'); ?></legend>
                    <label class="checkbox">
                        <?php 
                        echo $this->Form->input('show_input', array('label' => false, 'type' => 'checkbox','class' => 'checkbox','div' => false,'hiddenField' => false));  
                        echo __('Show input to participants'); ?>
                    </label>
                    <label class="checkbox">
                        <?php 
                        echo $this->Form->input('show_expected_output', array('label' => false, 'type' => 'checkbox','class' => 'checkbox','div' => false,'hiddenField' => false));  
                        echo __('Show expected output to participants'); ?>
                    </label>
                    <label class="checkbox">
                        <?php 
                        echo $this->Form->input('show_user_output', array('label' => false, 'type' => 'checkbox','class' => 'checkbox','div' => false,'hiddenField' => false));  
                        echo __('Show user output'); ?>
                    </label>
                    <?php
                    echo $this->Form->input('exercise_id',array('class' => 'span4', 'type' => 'hidden', 'value' => $exercise['Exercise']['id']));
                    echo $this->Form->input('input', array('label' => array('text' => __('Input'),'class' => 'control-label'), 'type' => 'textarea','class' => 'span8','div' => array('class' => 'control-group'))); 
                    echo $this->Form->input('maxmemsize', array('label' => array('text' => __('Max. Memory Size'),'class' => 'control-label'), 'type' => 'select','options'=>$memsizeoptions,'class' => 'span8','div' => array('class' => 'control-group'))); 
                    echo $this->Form->input('stacksize', array('label' => array('text' => __('Max. Stack Size'),'class' => 'control-label'), 'type' => 'select','options'=>$stacksizeoptions,'class' => 'span8','div' => array('class' => 'control-group'))); 
                    echo $this->Form->input('file_size', array('label' => array('text' => __('Max. Blocks Use'),'class' => 'control-label'), 'type' => 'select','options'=>$blocksizeoptions,'class' => 'span8','div' => array('class' => 'control-group'))); 
                    echo $this->Form->input('cputime', array('label' => array('text' => __('Max. CPU Time'),'class' => 'control-label'), 'type' => 'select','options'=>$timeoptions, 'value' => '3','class' => 'span8','div' => array('class' => 'control-group'))); 
                    ?>
                    
                </fieldset>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="row-fluid">
        <div class="exercises index widget span12">
            <div class="widget-header">
                <h3><?php echo __('Output'); ?></h3>
            </div>
            <div class="widget-body">
                <?php echo $this->Form->input('output_type', array('label' => array('text' => __('Output Type'),'class' => 'control-label'), 'type' => 'select','options'=>array("1" => __("Text"),"2" => __("Numbers with Error"),"3" => __("Binary")),'class' => 'span8','div' => array('class' => 'control-group')));  ?>
                <div class="tab-content">
                    <div class="tab-pane active" id="output-text">
                        <p class="text-info"><?php echo __("For the textual output the system will compare the student output with the case output. The user output must be identical with the case output to be correct"); ?></p>
                        <?php echo $this->Form->input('outputText', array('label' => array('text' => __('Output'),'class' => 'control-label'), 'type' => 'textarea','class' => 'span8','div' => array('class' => 'control-group'))); ?>
                    </div>
                    <div class="tab-pane" id="output-numbers">
                        <p class="text-info"><?php echo __("For the numeric output the system will compare the student output numbers with the case output numbers considering the maximum absolute error allowed. You must inform the maximum absolute error allowed for the output numbers"); ?></p>
                        <?php echo $this->Form->input('outputError', array('label' => array('text' => __('Output Error Accepted'),'class' => 'control-label'), 'type' => 'number', 'step' => 'any', 'min' => '0','class' => 'span8','div' => array('class' => 'control-group'))); ?>
                        <?php echo $this->Form->input('outputNumber', array('label' => array('text' => __('Output'),'class' => 'control-label'), 'type' => 'textarea','class' => 'span8','div' => array('class' => 'control-group'))); ?>
                    </div>
                    <div class="tab-pane" id="output-binary">
                        <p class="text-info"><?php echo __("For the binary output the system will consider the user output as a binary (The student must write the binary data in the stardard output). You must inform the file name for comparison and upload the file in the box below"); ?></p>
                        <?php echo $this->Form->input('outputBinary', array('label' => array('text' => __('File name'),'class' => 'control-label'), 'type' => 'text','class' => 'span8','div' => array('class' => 'control-group'))); ?>
                    </div>
                </div>
                <script>
                $(function () {
                  changeOutputTab($("#ExerciseCaseOutputType").val());
                  $("#ExerciseCaseOutputType").on('change', function() {
                      changeOutputTab($(this).val());
                  });
                  
                  function changeOutputTab(selected) {
                      if(selected == '1') {
                          $(".tab-pane").removeClass('active');
                          $('#output-text').addClass('active');
                      } else if(selected == '2') {
                          $(".tab-pane").removeClass('active');
                          $('#output-numbers').addClass('active');
                      } else if(selected == '3') {
                          $(".tab-pane").removeClass('active');
                          $('#output-binary').addClass('active');
                      }
                  }
                })
              </script>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="row-fluid">
        <div class="exercises index widget span12">
            <div class="widget-header">
                <h3><?php echo __('Exercise Case Files'); ?></h3>
            </div>
            <div class="widget-body exercise-files-inputs">
                <p>
                    <?php echo __("You can attach files to your exercise case to be used in the execution, like an image for processing. All files attached here will be moved to the same directory in each execution to test this case. The students will NOT have access to these files"); ?>
                </p>
                <div class="exercise-files-list"></div>
                <div id="exercise-case-files-drop-zone" class="drop-zone">
                    <div class="files-progress-bar"></div>
                    <p><span class="file-progress-text"><?php echo __("You can drag and drop your files here"); ?> <?php echo __("or"); ?></span> <span class="btn btn-info" style="position: relative; overflow: hidden"><?php echo __("Select Files"); ?><input id="fileupload" type="file" name="files[]" data-url="/ExerciseCaseFiles/fileUpload/" multiple  style="opacity: 0; position: absolute; top: 0; right: 0; margin: 0; cursor: pointer"></span></p>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="row-fluid">
        <div class="exercises index widget span12">
            <div class="widget-header">
                <h3><?php echo __('The End'); ?></h3>
            </div>
            <div class="widget-body">
                <p>
                    <?php echo __("By clicking in %s you will register the exercise case", __('Submit')); ?>
                </p>
                <?php echo $this->Form->end(array('label' => __('Submit'), 'class' => 'btn btn-yellow ')); ?>
            </div>
        </div>
    </div>
</div>

<?php 
echo $this->Html->script('jquery.ui.widget');
echo $this->Html->script('jquery.iframe-transport');
echo $this->Html->script('jquery.fileupload');
?>
<script>
$(function () {
    var count_files=0;
    $('#fileupload').fileupload({
        dataType: 'json',
        autoUpload: true,
        dropZone: $('#exercise-case-files-drop-zone'), 
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('.files-progress-bar').css(
                'width',
                progress + '%'
            );
                $('.drop-zone .file-progress-text').html("<?php echo __("Uploading"); ?> (" + progress + "%)");
        },
        fail: function(e, data) {
            console.log("Upload Error");
            console.log(data);
            alert('Upload Fail!');
            $('.files-progress-bar').css('width','0%');
        },
        done: function (e, data) {
            $.each(data.result.files, function (index, file) {
                $('.exercise-files-list').append('<div class="exercise-file-item" id="file' + file.hash_time + '"><a href="#" class="exercise-file-item-remove btn btn-danger btn-small" data-file="' + file.hash_time + '"><?php echo __("Remove"); ?></a>' + file.realname + "<br><input type='hidden' name='data[ExerciseCaseFile][" + count_files + "][path]' value='" + file.name + "' /><input type='hidden' name='data[ExerciseCaseFile][" + count_files + "][hash]' value='" + file.hash_time + "' /></div>");
                $('.files-progress-bar').css(
                    'width','0%'
                );
                $('.drop-zone .file-progress-text').html("<?php echo __("You can drag and drop your files here")." ".__("or")." "; ?>");
                count_files++;
                $(".exercise-file-item-remove").off("click");
                $(".exercise-file-item-remove").on("click",function(e) {
                    e.preventDefault();
                    removeFile($(this).attr("data-file"));
                });
            });
        }
    });
    
    $(".exercise-file-item-remove").on("click",function(e) {
        e.preventDefault();
        removeFile($(this).attr("data-file"));
    });
    
    function removeFile(hash) {
        $("#file" + hash).remove();
    }
});
</script>