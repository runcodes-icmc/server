<div class="container-fluid">
	<div class="row  row-min-height-191">
		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><?php echo __("User Info"); ?>:</h3>
					<div class="panel-heading-buttons">
<!--						--><?php //if($file) :
//							echo $this->Html->link('<i class="fa fa-download"></i> '.__('Download File'),array('controller' => 'Commits', 'action' => 'download', $commit['Commit']['id']),array('escape' => false,'class' => 'btn btn-sm btn-color-one'));
//						endif; ?>
					</div>
				</div>
				<div class="panel-body">
					<p>
						<?php echo __('Name').": ".h($user['User']['name']); ?><br>
						<?php echo __('Email').": ".h($user['User']['email']); ?><br>
						<?php echo __('University').": ".h($user['University']['name']); ?><br>
						<?php echo h($user['University']['student_identifier_text']).": ".h($user['User']['identifier']); ?><br>
						<?php echo __('Type').": ".h($user['User']['type_name']); ?>
						<?php if ($user['User']['type'] >= 2) : ?>
						(<a href="/Users/sendProfessorEmail/<?php echo $user['User']['email']; ?>"><?php echo __("Send role confirmation email"); ?></a>)
						<?php endif; ?>
						<br>
						<?php echo __('Registered since').": ".$this->Time->format('d/m/Y H:i:s',$user['User']['creation']); ?><br>
						<?php echo __('Confirmed').": ".(($user['User']['confirmed']) ? __("Yes") : __("No")); ?><br>
					</p>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><?php echo __("Actions"); ?>:</h3>
				</div>
				<div class="panel-body">
					<div class="row"><?php if (!$user['User']['confirmed']) : ?>
						<div class="col-sm-6">
							<?php echo $this->Html->link(__('Resend Confirmation Email'), array('action' => 'resendConfirmation', $user['User']['email']),array('class' => 'btn btn-primary btn-block btn-sm')); ?>
							</div>
						<div class="col-sm-6">
							<?php echo $this->Html->link(__('Confirm Register'), array('action' => 'confirmRegister', $user['User']['email']),array('class' => 'btn btn-success btn-block btn-sm')); ?>
						</div>
						<?php endif; ?>
					</div>

					<?php echo $this->Form->create('User',array('url' => array('controller' => 'Users', 'action' => 'edit',$user['User']['email']))); ?>
					<?php echo $this->Form->input('email',array('type' => 'hidden', 'value' => $user['User']['email'])); ?>
					<?php echo $this->Form->input('type', array('label' => array('text' => __('Type'),'class' => 'control-label'), 'type' => 'select', 'options' => $user_types,'value' => $user['User']['type'],'class' => 'form-control','div' => array('class' => 'form-group'))); ?>
					<?php echo $this->Form->end(array('label' => __('Confirm Change'), 'class' => 'btn btn-color-one ')); ?>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><?php echo __("Enrollments"); ?>:</h3>
				</div>
				<table class="table table-hover">
					<tbody>
					<?php foreach ($enrollments as $enrollment): ?>
						<tr>
							<td style="width: 10%" class="text-center"><?php echo h($enrollment['Offering']['Course']['code']); ?></td>
							<td><?php echo h($enrollment['Offering']['Course']['title']); ?> - <?php echo h($enrollment['Offering']['classroom']); ?></td>
							<td class="text-right"><?php echo $this->Form->postLink(__('Remove'), array('controller' => 'offerings', 'action' => 'ban', h($enrollment['Offering']['id']),h($user['User']['email'])),array('class' => 'btn btn-color-three btn-sm'),__("Are you sure you want to remove the user %s from that offering?",h($user['User']['email']))); ?>
							<?php echo $this->Html->link(__('View Subject Page'), array('controller' => 'offerings', 'action' => 'view', h($enrollment['Offering']['id'])),array('class' => 'btn btn-color-one btn-sm')); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<div class="panel-body" style="min-height: 100px">
					<?php echo $this->Form->create('Enrollment', array('action'=> 'enroll')); ?>
					<div class="row">
						<div class="col-md-9">
							<fieldset>
								<?php
								echo $this->Form->input('user_email',array('type'=>'hidden', 'label'=>false,'value' => $user['User']['email']));
								echo $this->Form->input('enrollment_code',array('type'=>'text', 'label'=>false,'class' => 'form-control input-enrollment-code','data-mask' => 'wwww','placeholder' => "Matricular em turma (Código da turma)",'div' => array('class' => 'form-group'),'error' => array('attributes' => array('class' => 'has-error help-block'))));
								?>
							</fieldset>
						</div>
						<div class="col-md-3">
							<?php echo $this->Form->submit(__('Enrollment'),array('class' => 'btn btn-color-one btn-block')); ?>
						</div>
					</div>
					<?php echo $this->Form->end(); ?>
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
					<h3 class="panel-title"><?php echo __("Last Commits"); ?>:</h3>
				</div>
				<table class="table table-hover">
					<thead>
						<tr>
							<th><?php echo __('Id'); ?></th>
							<th><?php echo __("Exercise"); ?></th>
							<th class="text-center"><?php echo __('Status'); ?></th>
							<th class="text-center"><?php echo __('Score'); ?></th>
							<th class="text-center"><?php echo __('Corrects'); ?></th>
							<th class="text-center"><?php echo __('Time'); ?></th>
							<th class="actions text-center"><?php echo __('Actions'); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($commits as $commit): ?>
						<tr>
							<td class="text-center"><?php echo h($commit['Commit']['id']); ?>&nbsp;</td>
							<td>
								<?php echo $this->Html->link($commit['Exercise']['title'], array('controller' => 'Exercises', 'action' => 'viewProfessor', $commit['Commit']['exercise_id'])); ?>
							</td>
							<td class="text-center"><span class="label label-<?php echo $commit['Commit']['status_color']; ?>"><?php echo $commit['Commit']['name_status']; ?></span></td>
							<td class="text-center"><span class="label label-<?php echo $commit['Commit']['score_color']; ?>"><?php echo $commit['Commit']['score']; ?></span></td>
							<td class="text-center"><span class="label label-<?php echo $commit['Commit']['correct_color']; ?>"><?php echo $commit['Commit']['corrects']; ?>/<?php echo $commit['Exercise']['num_cases']; ?></td>
							<td class="text-center"><?php echo $this->Time->format('d/m/Y H:i:s',$commit['Commit']["commit_time"]); ?></td>
							<td class="actions text-center">
								<?php echo $this->Html->link(__('Details'), array('controller' => 'Commits','action' => 'details', $commit['Commit']['id']), array('class' => 'btn btn-primary btn-sm')); ?>
								<?php echo $this->Html->link(__('Recompile'), array('controller' => 'Commits','action' => 'recompile', $commit['Commit']['id']), array('class' => 'btn btn-danger btn-sm')); ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
