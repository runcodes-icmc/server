<div class="container-fluid">
    <div class="row-fluid">
        <div class="alerts index widget span12">
            <div class="widget-header">
                <h3><?php echo __('Alerts'); ?></h3>
                <div class="widget-header-buttons">
                    <?php echo $this->Html->link(__('+ Add'), array('action' => 'add'),array('class' => 'btn btn-info btn-small')); ?>
                </div>
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
                <table>
                    <thead>
                        <tr>
                            <th><?php echo $this->Paginator->sort('id'); ?></th>
                            <th><?php echo $this->Paginator->sort('type'); ?></th>
                            <th><?php echo $this->Paginator->sort('offering_id'); ?></th>
                            <th><?php echo $this->Paginator->sort('recipients'); ?></th>
                            <th><?php echo $this->Paginator->sort('user_email'); ?></th>
                            <th><?php echo $this->Paginator->sort('valid'); ?></th>
                            <th><?php echo $this->Paginator->sort('title'); ?></th>
                            <th class="actions"><?php echo __('Actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alerts as $alert): ?>
                        <tr>
                            <td class="text-center"><?php echo h($alert['Alert']['id']); ?>&nbsp;</td>
                            <td class="text-center"><?php echo h($alert['Alert']['type']); ?>&nbsp;</td>
                            <td>
                                    <?php echo $this->Html->link($alert['Offering']['id'], array('controller' => 'offerings', 'action' => 'view', $alert['Offering']['id'])); ?>
                            </td>
                            <td class="text-center"><?php echo h($alert['Alert']['recipients']); ?>&nbsp;</td>
                            <td>
                                    <?php echo $this->Html->link($alert['User']['name'], array('controller' => 'users', 'action' => 'view', $alert['User']['email'])); ?>
                            </td>
                            <td class="text-center"><?php echo h($alert['Alert']['valid']); ?>&nbsp;</td>
                            <td class="text-center"><?php echo h($alert['Alert']['title']); ?>&nbsp;</td>
                            <td class="actions text-center">
                                    <?php echo $this->Html->link(__('View'), array('action' => 'view', $alert['Alert']['id']),array('class' => 'btn btn-primary btn-small')); ?>
                                    <?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $alert['Alert']['id']),array('class' => 'btn btn-yellow btn-small')); ?>
                                    <?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $alert['Alert']['id']), array('class' => 'btn btn-danger btn-small'), __('Are you sure you want to delete # %s?', $alert['Alert']['id'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p>
                <?php
                echo $this->Paginator->counter(array(
                'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
                ));
                ?>	</p>
                <div class="pagination">
                    <ul>
                        <?php
                        echo $this->Paginator->prev('<<', array('class' => '', 'tag' => 'li'), null, array('class' => 'disabled pagli', 'tag' => 'li'));
                        echo $this->Paginator->numbers(array('tag' => 'li', 'separator' => '', 'currentClass' => 'disabled pagli'));
                        echo $this->Paginator->next('>>', array('class' => '', 'tag' => 'li'), null, array('class' => 'disabled pagli', 'tag' => 'li'));
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>  
</div> 