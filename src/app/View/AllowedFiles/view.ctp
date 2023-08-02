<div class="allowedFiles view">
<h2><?php echo __('Allowed File'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($allowedFile['AllowedFile']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($allowedFile['AllowedFile']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Extension'); ?></dt>
		<dd>
			<?php echo h($allowedFile['AllowedFile']['extension']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Compilable'); ?></dt>
		<dd>
			<?php echo h($allowedFile['AllowedFile']['compilable']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Compile Command'); ?></dt>
		<dd>
			<?php echo h($allowedFile['AllowedFile']['compile_command']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Run Command'); ?></dt>
		<dd>
			<?php echo h($allowedFile['AllowedFile']['run_command']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Allowed File'), array('action' => 'edit', $allowedFile['AllowedFile']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Allowed File'), array('action' => 'delete', $allowedFile['AllowedFile']['id']), null, __('Are you sure you want to delete # %s?', $allowedFile['AllowedFile']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Allowed Files'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Allowed File'), array('action' => 'add')); ?> </li>
	</ul>
</div>
