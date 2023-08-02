<div class="container-fluid">
    <div class="row">
        <div class="col-md-9">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("Users"); ?></h3>
                    <div class="panel-heading-buttons">
                        <?php echo $this->Html->link('<i class="fa fa-search"></i> '.__('Reset')." ", array('controller' => 'Users'),array('escape' => false,'class' => 'btn btn-color-one btn-sm')); ?>
                        <?php echo $this->Html->link('<i class="fa fa-plus"></i> '.__('Add User')." ", array('controller' => 'Users','action' => 'insert'),array('escape' => false,'class' => 'btn btn-color-two btn-sm')); ?>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="btn-group">
                    <?php foreach ($letters as $l):
                        $class = 'btn btn-sm btn-danger ';
                        if ($startswith==$l):
                            $class.='active';
                        endif;
                        echo $this->Html->link(__($l), array('action' => 'index', 'startswith' => $l),array('class' => $class)); ?>
                    <?php endforeach; ?>
                    </div>
                </div>
                <table class="table table-hover table-striped">
                    <thead>
                    <tr>
                        <th><?php echo $this->Paginator->sort('email'); ?></th>
                        <th><?php echo $this->Paginator->sort('name'); ?></th>
                        <th class="text-center"><?php echo $this->Paginator->sort('type'); ?></th>
                        <th class="text-center"><?php echo __('University'); ?></th>
                        <th class="text-center"><?php echo $this->Paginator->sort('creation'); ?></th>
                        <th class="text-center"><?php echo $this->Paginator->sort('confirmed'); ?></th>
                        <th class="text-center"><?php echo __('Actions'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo h($user['User']['email']); ?>&nbsp;</td>
                            <td><?php echo h($user['User']['name']); ?>&nbsp;</td>
                            <td class="text-center"><?php echo $this->Html->link($user['User']['type_name'],array('type' => $user['User']['type'])); ?>&nbsp;</td>
                            <td class="text-center"><?php echo (isset($universities[$user['User']['university_id']])) ?  $this->Html->link($universities[$user['User']['university_id']],array('university' => $user['User']['university_id'])) : $this->Html->link(__("Empty"),array('university' => 'null')); ?>&nbsp;</td>
                            <td class="text-center"><?php echo $this->Time->format('d/m/Y H:i',h($user['User']['creation'])); ?>&nbsp;</td>
                            <td class="text-center">
                                <?php if($user['User']['confirmed']): ?>
                                    <span class="label  label-success"><?php echo __('Yes'); ?></span>
                                <?php else: ?>
                                    <span class="label label-danger"><?php echo __('No'); ?></span>
                                <?php endif; ?>&nbsp;</td>
                            <td class="text-center">
                                <?php echo $this->Html->link(__('View'), array('action' => 'view', $user['User']['email']),array('class' => 'btn btn-primary btn-small')); ?>
                                <?php //echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $user['User']['email']), array('class' => 'btn btn-danger btn-small'), __('Are you sure you want to delete # %s?', $user['User']['email'])); ?>
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
                    <?php echo $this->Form->create('User',array('novalidate' => true)); ?>
                    <fieldset>
                        <?php echo $this->Form->input('university_id', array('label' => array('text' => __('University'),'class' => 'control-label'), 'required', 'options' => $universities,'class' => 'form-control chosen-unique-select','div' => array('class' => 'form-group'))); ?>
                        <?php echo $this->Form->input('type', array('label' => array('text' => __('Type'),'class' => 'control-label'), 'required', 'options' => $types,'class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                        <?php echo $this->Form->input('name', array('label' => array('text' => __('Name'),'class' => 'control-label'), 'type' => 'text','allowEmpty' => true,'class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                        <?php echo $this->Form->input('email', array('label' => array('text' => __('Email'),'class' => 'control-label'), 'type' => 'text','allowEmpty' => true,'class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                        <div class="checkbox">
                            <label>
                                <?php echo $this->Form->input('confirmed', array('label' => false, 'type' => 'checkbox','class' => 'checkbox','checked' => 'checked'));
                                echo __('Confirmed'); ?>
                            </label>
                        </div>
                    </fieldset>
                    <?php echo $this->Form->end(array('label' => __('Filter'), 'class' => 'btn btn-color-one ')); ?>
                </div>
            </div>
        </div>
    </div>
</div>