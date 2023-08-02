<div class="alerts view">
<h2><?php echo __('Alert'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($alert['Alert']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Type'); ?></dt>
		<dd>
			<?php echo h($alert['Alert']['type']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Offering'); ?></dt>
		<dd>
			<?php echo $this->Html->link($alert['Offering']['id'], array('controller' => 'offerings', 'action' => 'view', $alert['Offering']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Recipients'); ?></dt>
		<dd>
			<?php echo h($alert['Alert']['recipients']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('User'); ?></dt>
		<dd>
			<?php echo $this->Html->link($alert['User']['name'], array('controller' => 'users', 'action' => 'view', $alert['User']['email'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Valid'); ?></dt>
		<dd>
			<?php echo h($alert['Alert']['valid']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Title'); ?></dt>
		<dd>
			<?php echo h($alert['Alert']['title']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Message'); ?></dt>
		<dd>
			<?php echo h($alert['Alert']['message']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Alert'), array('action' => 'edit', $alert['Alert']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Alert'), array('action' => 'delete', $alert['Alert']['id']), null, __('Are you sure you want to delete # %s?', $alert['Alert']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Alerts'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Alert'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Offerings'), array('controller' => 'offerings', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Offering'), array('controller' => 'offerings', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
