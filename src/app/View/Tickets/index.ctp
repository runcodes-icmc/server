<div class="container-fluid">
    <div class="row-fluid">
        <div class="tickets index widget span12">
            <div class="widget-header">
                <h3><?php echo __('Tickets'); ?></h3>                
            </div>
            <div class="widget-body">
                <table>
                    <thead>
                        <tr>
                            <th><?php echo $this->Paginator->sort('id'); ?></th>
                            <th><?php echo $this->Paginator->sort('users_email',__('Opened by')); ?></th>
                            <th><?php echo $this->Paginator->sort('datetime',__('Date')); ?></th>
                            <th><?php echo $this->Paginator->sort('type'); ?></th>
                            <th><?php echo $this->Paginator->sort('status'); ?></th>
                            <th><?php echo $this->Paginator->sort('solved'); ?></th>
                            <th><?php echo $this->Paginator->sort('priority'); ?></th>
                            <th><?php echo __('Actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        
                        foreach ($tickets as $ticket): ?>
                        <tr>
                            <td class="text-center"><?php echo h($ticket['Ticket']['id']); ?>&nbsp;</td>
                            <td><?php echo h($ticket['User']['name']); ?>&nbsp;</td>
                            <td class="text-center"><?php echo $this->Time->format('d/m/Y H:i:s',$ticket['Ticket']['datetime']); ?>&nbsp;</td>
                            <td class="text-center"><?php echo h($ticket['Ticket']['type_name']); ?>&nbsp;</td>
                            <td class="text-center">
                                <?php if(!$ticket['Ticket']['status']): ?>
                                    <span class="badge badge-success"><?php echo h($ticket['Ticket']['status_name']); ?></span>
                                <?php else: ?>
                                   <span class="badge badge-important"> <?php echo h($ticket['Ticket']['status_name']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if($ticket['Ticket']['solved']): ?>
                                    <span class="badge badge-success"><?php echo __("Yes"); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-important"> <?php echo __("No"); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if($ticket['Ticket']['priority'] <= 1): ?>
                                    <span class="badge badge-important"><?php echo h($ticket['Ticket']['priority_name']); ?></span>
                                <?php elseif($ticket['Ticket']['priority'] == 2): ?>
                                   <span class="badge badge-warning"> <?php echo h($ticket['Ticket']['priority_name']); ?></span>
                                <?php else: ?>
                                   <span class="badge badge-info"> <?php echo h($ticket['Ticket']['priority_name']); ?></span>
                                <?php endif; ?>
                            <td class="text-center">
                                <?php echo $this->Html->link(__('View Message'), '#ModalTicket'.$ticket['Ticket']['id'],array('class' => 'btn btn-yellow btn-small view-message','data-toggle' => 'modal','data-ticket-id'=>h($ticket['Ticket']['id']))); ?>
                                <?php if(!$ticket['Ticket']['solved'] && !$ticket['Ticket']['status']) { echo $this->Html->link(__('Set as Solved'), array('action'=>'setAsSolved',h($ticket['Ticket']['id'])),array('class' => 'btn btn-success btn-small'));} ?>
                                <?php if(!$ticket['Ticket']['status']) { echo $this->Html->link(__('Close'), array('action'=>'close',h($ticket['Ticket']['id'])),array('class' => 'btn btn-danger btn-small')); } ?>
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
<?php foreach ($tickets as $ticket): ?>
<div id="ModalTicket<?php echo $ticket['Ticket']['id']; ?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="labelModalTicket<?php echo $ticket['Ticket']['id']; ?>" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="labelModalTicket<?php echo $ticket['Ticket']['id']; ?>"><?php echo __("View Ticket"); ?></h3>
    </div>
    <div class="modal-body">
        <?php echo h($ticket['Ticket']['message']); ?>
    </div>
    <div class="modal-footer">
        <button class="btn btn-yellow" data-dismiss="modal" aria-hidden="true">Ok</button>
    </div>
</div>
<?php endforeach; ?>
