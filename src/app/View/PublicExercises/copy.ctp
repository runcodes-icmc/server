<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __('Copy Exercise to Public Database'); ?></h3>
                </div>
                <div class="panel-body">
                    <?php echo __("Title").": ".$exercise['Exercise']['title']; ?><br>
                    <?php echo __("Number of Cases").": ".$exercise['Exercise']['num_cases']; ?>
                    <?php echo $this->Form->create('PublicExercise'); ?>
                    <?php echo $this->Form->input('exercise_id',array("type" => "hidden","value" => $exercise['Exercise']['id'])); ?>
                    <?php echo $this->Form->input('level', array('label' => array('text' => __('Level'),'class' => 'control-label'), 'type' => 'select', 'options' => $levels,'class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                    <?php echo $this->Form->input('keywords', array('label' => array('text' => __('Keywords'),'class' => 'control-label'), 'type' => 'text','class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                    <?php echo $this->Form->input('obs', array('label' => array('text' => __('Obs'),'class' => 'control-label'), 'type' => 'textarea','class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                    <?php echo $this->Form->end(array('label' => __('Confirm'), 'class' => 'btn btn-color-one ')); ?>
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
                    <h3 class="panel-title"><?php echo $exercise['Exercise']['title']; ?></h3>
                </div>
                <div class="panel-body">
                    <?php if (!$exercise['Exercise']['markdown']) : echo $exercise['Exercise']['description']; else : ?>
                        <div ng-controller="ExerciseDescriptionController">
                            <textarea class="markdown" style="display:none;" ng-init="loadMarkdownDescription()"><?php echo $exercise['Exercise']['description']; ?></textarea><div class="insertMarkdown" ng-bind-html="html"></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>