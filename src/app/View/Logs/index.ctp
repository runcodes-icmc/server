<div class="container-fluid">
    <div class="row">
        <div class="col-md-9">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("Logs"); ?></h3>
                    <div class="panel-heading-buttons">
                        <?php echo $this->Html->link('<i class="fa fa-search"></i> '.__('Reset')." ", array('controller' => 'Logs'),array('escape' => false,'class' => 'btn btn-info btn-sm')); ?>
                    </div>
                </div>
                <table class="table table-hover table-striped">
                    <thead>
                    <tr>
                        <th class="text-center"><?php echo __('Date'); ?></th>
                        <th class="text-center"><?php echo __('User Email'); ?></th>
                        <th class="text-center"><?php echo __('IP'); ?></th>
                        <th class="text-center"><?php echo __('Actions'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="text-center"><?php echo $this->Time->format('d/m/Y H:i:s',h($log['Log']['datetime'])); ?>&nbsp;</td>
                            <td><?php echo $this->Html->link($log['Log']['user_email'],array('user_email' => $log['Log']['user_email'])); ?>&nbsp;</td>
                            <td class="text-center"><?php echo $this->Html->link($log['Log']['ip'],array('ip' => $log['Log']['ip'])); ?>&nbsp;</td>
                            <td><?php echo h($log['Log']['action']); ?>&nbsp;</td>
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
                    <?php echo $this->Form->create('Log',array('novalidate' => true)); ?>
                    <fieldset>
                        <?php echo $this->Form->input('user_email', array('label' => array('text' => __('User Email'),'class' => 'control-label'), 'type' => 'text','allowEmpty' => true,'class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                        <?php echo $this->Form->input('ip', array('label' => array('text' => __('IP'),'class' => 'control-label'), 'type' => 'text','allowEmpty' => true,'class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                    </fieldset>
                    <?php echo $this->Form->end(array('label' => __('Filter'), 'class' => 'btn btn-color-one ')); ?>
                </div>
            </div>
        </div>
    </div>
</div>