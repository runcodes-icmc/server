<div class="container-fluid">
    <div class="row">
        <div class="col-md-9">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("Mail Logs"); ?></h3>
                    <div class="panel-heading-buttons">
                        <?php echo $this->Html->link('<i class="fa fa-search"></i> '.__('Reset')." ", array('controller' => 'Messages','action'=>'maillog'),array('escape' => false,'class' => 'btn btn-info btn-sm')); ?>
                        <?php echo $this->Form->postLink('<i class="fa fa-remove"></i> '.__('Clean Old Messages')." ", array('controller' => 'Messages','action'=>'clearOldMessages'),array('escape' => false,'class' => 'btn btn-color-three btn-sm')); ?>
                    </div>
                </div>
                <table class="table table-hover table-striped">
                    <thead>
                    <tr>
                        <th class="text-center"><?php echo __('Date'); ?></th>
                        <th class="text-center"><?php echo __('Sent to'); ?></th>
                        <th class="text-center"><?php echo __('Message Subject'); ?></th>
                        <th class="text-center"><?php echo __('Opened'); ?></th>
                        <th class="text-center"><?php echo __('First Time Opened'); ?></th>
                        <th class="text-center"><?php echo __('Actions'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="text-center"><?php echo $this->Time->format('d/m/Y H:i:s',h($log['MailLog']['sent_date'])); ?>&nbsp;</td>
                            <td class="text-center"><?php echo $this->Html->link($log['MailLog']['sent_to'],array('sent_to' => $log['MailLog']['sent_to'])); ?>&nbsp;</td>
                            <td><?php echo $log['MailLog']['subject']; ?>&nbsp;</td>
                            <td class="text-center"><?php echo $log['MailLog']['opened']; ?>&nbsp;</td>
                            <td class="text-center"><?php if ($log['MailLog']['opened'] > 0) echo $this->Time->format('d/m/Y H:i:s',h($log['MailLog']['first_opened_time'])); ?>&nbsp;</td>
                            <td class="text-center">
                                <?php echo $this->Html->link(__('View'), array('action' => 'view', $log['MailLog']['id']), array('class' => 'btn btn-info btn-small')); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="panel-body">
                    <p>
                        <?php
                        echo $this->Paginator->counter(array(
                            'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
                        ));
                        ?>
                    </p>
                    <div>
                        <ul class="pagination pagination-large">
                            <?php
                            echo $this->Paginator->prev(__('prev'), array('tag' => 'li'), null, array('tag' => 'li','class' => 'disabled','disabledTag' => 'a'));
                            echo $this->Paginator->numbers(array('separator' => '','currentTag' => 'a', 'currentClass' => 'active','tag' => 'li','first' => 1));
                            echo $this->Paginator->next(__('next'), array('tag' => 'li','currentClass' => 'disabled'), null, array('tag' => 'li','class' => 'disabled','disabledTag' => 'a'));
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("Filter"); ?></h3>
                </div>
                <div class="panel-body">
                    <?php echo $this->Form->create('MailLog',array('novalidate' => true)); ?>
                    <fieldset>
                        <?php echo $this->Form->input('sent_to', array('label' => array('text' => __('Sent to'),'class' => 'control-label'), 'type' => 'text','allowEmpty' => true,'class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                        <?php echo $this->Form->input('opened', array('label' => array('text' => __('Opened'),'class' => 'control-label'), 'type' => 'text','allowEmpty' => true,'class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                    </fieldset>
                    <?php echo $this->Form->end(array('label' => __('Filter'), 'class' => 'btn btn-color-one ')); ?>
                </div>
            </div>
        </div>
    </div>
</div>