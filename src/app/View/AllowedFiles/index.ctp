<div class="container-fluid">
    <div class="row-fluid">
        <div class="alerts index widget span12">
            <div class="widget-header">
                <h3><?php echo __('Allowed Files'); ?></h3>
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
                            <th><?php echo $this->Paginator->sort('name'); ?></th>
                            <th><?php echo $this->Paginator->sort('extension'); ?></th>
                            <th><?php echo $this->Paginator->sort('compilable'); ?></th>
                            <th><?php echo $this->Paginator->sort('available'); ?></th>
                            <th class="actions"><?php echo __('Actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allowedFiles as $allowedFile): ?>
                        <tr>
                            <td class="text-center"><?php echo h($allowedFile['AllowedFile']['id']); ?>&nbsp;</td>
                            <td class="text-center"><?php echo h($allowedFile['AllowedFile']['name']); ?>&nbsp;</td>
                            <td class="text-center"><?php echo h($allowedFile['AllowedFile']['extension']); ?></td>
                            <td class="text-center">
                                <?php if($allowedFile['AllowedFile']['compilable']): ?>
                                    <span class="badge badge-success"><?php echo __('Yes'); ?></span>
                                <?php else: ?>
                                   <span class="badge badge-important"><?php echo __('No'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if($allowedFile['AllowedFile']['available']): ?>
                                    <span class="badge badge-success"><?php echo __('Yes'); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-important"><?php echo __('No'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="actions text-center">
                                    <?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $allowedFile['AllowedFile']['id']),array('class' => 'btn btn-yellow btn-small')); ?>
                                    <?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $allowedFile['AllowedFile']['id']), array('class' => 'btn btn-danger btn-small'), __('Are you sure you want to delete # %s?', $allowedFile['AllowedFile']['id'])); ?>
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