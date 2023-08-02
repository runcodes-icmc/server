
<div class="container-fluid">
    <div class="row-fluid">
        <div class="mail index widget span12">
            <div class="widget-header"> 
                <h3><?php echo __('Talk with the Users'); ?></h3>
            </div>
            <div class="widget-body">
                <?php 
                $msg = $this->Session->flash('success');
                if (strlen(trim($msg)) > 0) {
                    ?>
                    <div class="alert alert-success">
                    <?php echo $msg; ?>
                    </div>
                <?php } ?>
                <?php 
                $msg = $this->Session->flash('flash');
                if (strlen(trim($msg)) > 0) {
                    ?>
                    <div class="alert alert-error">
                    <?php echo $msg; ?>
                    </div>
                <?php } ?>
                <?php 
                echo $this->Form->create('Message', array('url' => array('action'=> 'sendMail'))); ?>
                <fieldset>
                <?php
                    echo $this->Form->input('to',array('label' => array('text' => __('To'),'class' => 'control-label'),'class' => 'span12','type' => 'select', 'options' => $recipesList));
                    echo $this->Form->input('subject',array('label' => array('text' => __('Email Subject'),'class' => 'control-label'),'class' => 'span12','type' => 'text'));
                    echo $this->Form->input('message', array('label' => array('text' => __('Message'),'class' => 'control-label'), 'type' => 'textarea','class' => 'span12','div' => array('class' => 'control-group'))); 
                ?>
                    <div class="">
                        <p>
                            <?php echo __("Attachments"); ?>
                        </p>
                        <div class="mail-files-list"></div>
                        <input id="fileupload" type="file" name="files[]" data-url="/Messages/mailFileUpload/" multiple class="btn btn-info" style="display: none">
                        <div id="mail-files-drop-zone" class="drop-zone">
                            <p style="font-size: 36px"><?php echo __("You can drag and drop your files here"); ?></p>
                        </div>
                    </div>
                </fieldset>
                <?php echo $this->Form->end(array('label' => __('Send'), 'class' => 'btn btn-yellow ')); ?>
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
        dropZone: $('#mail-files-drop-zone'), //Implementar progressbar
        done: function (e, data) {
            $.each(data.result.files, function (index, file) {
                $('.mail-files-list').append(file.realname + "<br><input type='hidden' name='data[Files][" + count_files + "][path]' value='" + file.name + "' /><input type='hidden' name='data[Files][" + count_files + "][hash]' value='" + file.hash_time + "' />");
                count_files++;
            });
        }
    });
});
</script>