<div class="alerts form">
<?php echo $this->Form->create('Alert'); ?>
	<fieldset>
		<legend><?php echo __('Edit Alert'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('type');
		echo $this->Form->input('offering_id');
		echo $this->Form->input('recipients');
		echo $this->Form->input('user_email');
		echo $this->Form->input('valid');
		echo $this->Form->input('title');
		echo $this->Form->input('message');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Alert.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('Alert.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Alerts'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Offerings'), array('controller' => 'offerings', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Offering'), array('controller' => 'offerings', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
