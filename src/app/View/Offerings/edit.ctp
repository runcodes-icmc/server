<div>
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title"><?php echo __('Course'); ?></h3>
					</div>
					<div class="panel-body">
						<fieldset>
							<p><?php echo __("Course").": ".$course['Course']['title']; ?></p>
                            <?php
                            echo $this->Form->create('Offering');
                            echo $this->Form->input('id');
							echo $this->Form->input('classroom', array('label' => array('text' => __('Classroom'),'class' => 'control-label'), 'type' => 'text','class' => 'form-control','div' => array('class' => 'form-group')));
							echo $this->Form->input('end_date', array('label' => array('text' => __('Valid until'),'class' => 'control-label'),'value' => $this->Time->format('d/m/Y',$this->request->data['Offering']['end_date']), 'type' => 'text','class' => 'form-control datepicker','data-mask' => '99/99/9999','div' => array('class' => 'form-group')));
							?>
							<p><?php echo __("After the end date, the classroom will be finished.")." ".__("We recommend you to set an end date after all classes and exams.")." ".__("Or even just before the beginning of the next term"); ?></p>
							<?php echo $this->Form->end(array('label' => __('Submit'), 'class' => 'btn btn-color-one ')); ?>
						</fieldset>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

