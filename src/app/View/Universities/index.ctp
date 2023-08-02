<div class="container-fluid">
    <div class="row">
        <div class="col-md-9">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("Universities"); ?></h3>
                    <div class="panel-heading-buttons">
                        <?php echo $this->Html->link('<i class="fa fa-search"></i> '.__('Reset')." ", array('controller' => 'Universities'),array('escape' => false,'class' => 'btn btn-info btn-sm')); ?>
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
                        <th class="text-center"><?php echo $this->Paginator->sort('id'); ?></th>
                        <th class="text-center"><?php echo $this->Paginator->sort('abbreviation'); ?></th>
                        <th><?php echo $this->Paginator->sort('name'); ?></th>
                        <th class="text-center"><?php echo $this->Paginator->sort('type'); ?></th>
                        <th class="text-center"><?php echo $this->Paginator->sort('student_identifier_text'); ?></th>
                        <th class="text-center"><?php echo __('Users'); ?></th>
                        <th class="text-center"><?php echo __('Actions'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($universities as $university): ?>
                        <tr>
                            <td class="text-center"><?php echo h($university['University']['id']); ?>&nbsp;</td>
                            <td class="text-center"><?php echo h($university['University']['abbreviation']); ?>&nbsp;</td>
                            <td><?php echo h($university['University']['name']); ?>&nbsp;</td>
                            <td class="text-center"><?php echo h($universitiesType[$university['University']['type']]); ?>&nbsp;</td>
                            <td class="text-center"><?php echo h($university['University']['student_identifier_text']); ?>&nbsp;</td>
                            <td class="text-center"><?php echo h($university['University']['num_users']); ?>&nbsp;</td>
                            <td class="actions text-center">
                                <?php echo $this->Html->link(__('Edit'), array('controller' => 'Universities','action' => 'index', 'edit' => $university['University']['id']),array('class' => 'btn btn-color-three btn-sm')); ?>
                                <?php echo $this->Html->link(__('Courses'), array('controller' => 'Courses','action' => 'index', 'university' => $university['University']['id']),array('class' => 'btn btn-color-one btn-sm')); ?>
                                <?php echo $this->Html->link(__('Offerings'), array('controller' => 'Offerings','action' => 'index', 'university' => $university['University']['id']),array('class' => 'btn btn-color-two btn-sm')); ?>
                                <?php echo $this->Html->link(__('Users'), array('controller' => 'Users','action' => 'index', 'university' => $university['University']['id']),array('class' => 'btn btn-color-three btn-sm')); ?>
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
                    <h3 class="panel-title"><?php if ($edit) echo __("Edit University"); else echo __("Add University"); ?></h3>
                </div>
                <div class="panel-body">
                    <?php if ($edit) echo $this->Form->create('University',array('action' => 'edit')); else echo $this->Form->create('University',array('action' => 'add')); ?>
                    <fieldset>
                        <?php if ($edit) echo $this->Form->input('id'); ?>
                        <?php echo $this->Form->input('abbreviation', array('label' => array('text' => __('Abbreviation'),'class' => 'control-label'), 'required','type' => 'text','class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                        <?php echo $this->Form->input('name', array('label' => array('text' => __('Name'),'class' => 'control-label'), 'required', 'type' => 'text','class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                        <?php echo $this->Form->input('type', array('label' => array('text' => __('Type'),'class' => 'control-label'), 'required', 'options' => $universitiesType,'class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                        <?php echo $this->Form->input('student_identifier_text', array('label' => array('text' => __('Student Identifier Text'),'placeholder' => __('Student Identifier text (e.g.: Número USP, RA)'),'class' => 'control-label'), 'required', 'type' => 'text','class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                    </fieldset>
                    <?php echo $this->Form->end(array('label' => __('Submit'), 'class' => 'btn btn-color-one ')); ?>
                </div>
            </div>
        </div>
    </div>
</div>
