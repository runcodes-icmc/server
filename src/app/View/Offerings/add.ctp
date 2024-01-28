<div ng-controller="FormOfferingController">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo __('Course'); ?></h3>
                    </div>
                    <div class="panel-body">
                        <fieldset ng-init="showCourses=false;">
                            <small><?php echo $this->element(Configure::read('Config.language'). DS . "offeringAgreement"); ?></small>
                            <p><?php echo __("Please, use the form below to filter the list of courses available"); ?></p>
                            <p><?php echo __("There are %d courses from your university in the system",$coursesCount); ?></p>
                            <?php echo $this->Form->create('Course'); ?>
                            <?php echo $this->Form->input('search', array('label' => false, 'type' => 'text','ng-model' => 'searchString','placeholder' => __("Type a part of the")." ".__("Course Code")." ".__("or")." ".__("Course Title"),'class' => 'form-control','required' => false,'div' => array('class' => 'form-group'))); ?>
                            <?php echo $this->Form->end(array('label' => __('Search'), 'class' => 'btn btn-color-one', 'ng-click' => '$event.preventDefault();loadCourses()')); ?>
                            <i class="fa fa-refresh fa-spin" ng-show="loading"></i>
                            <?php echo $this->Form->create('Offering'); ?>
                            <?php
//                            echo $this->Form->input('course_id',array('class' => 'form-control','type', 'options' => null,'div' => array('ng-show' => 'showCourses')));
                            ?>
                            <div class="form-group">
                                <label for="OfferingCourseId" ng-show="showCourses"><?php echo __("Course"); ?></label>
                                <select id="OfferingCourseId" name="data[Offering][course_id]" class="form-control" required ng-show="showCourses">
                                    <option ng-repeat="course in coursesList" value="{{ course.id }}">{{ course.title }}</option>
                                </select>
                                <div class="form-group has-error" ng-show="showCourses">
                                <span id="helpBlock" class="help-block"><?php echo __("If you did not found your subject in the list above, please send an email to runcodes@icmc.usp.br with your university name, subject code and name. We will add it as soon as possible."); ?></span>
                                </div>
                            </div>
                            <?php
                            echo $this->Form->input('classroom', array('label' => array('text' => __('Classroom'),'class' => 'control-label'), 'type' => 'text','class' => 'form-control','div' => array('class' => 'form-group','ng-show' => 'showCourses')));
                            echo $this->Form->input('end_date', array('label' => array('text' => __('Valid until'),'class' => 'control-label'), 'type' => 'text','class' => 'form-control datepicker','data-mask' => '99/99/9999','div' => array('class' => 'form-group','ng-show' => 'showCourses')));
                            if ($university['isPaid']) echo $this->Form->input('code', array('label' => array('text' => __('Code'),'class' => 'control-label'), 'type' => 'text','class' => 'form-control','div' => array('class' => 'form-group','ng-show' => 'showCourses')));
                            ?>
                            <p ng-show="showCourses"><?php echo __("After the end date, the classroom will be finished.")." ".__("We recommend you to set an end date after all classes and exams.")." ".__("Or even just before the beginning of the next term"); ?></p>
                            <?php echo $this->Form->end(array('label' => __('Register'), 'class' => 'btn btn-color-one ','ng-show' => 'showCourses')); ?>
                        </fieldset>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
