<div class="container-fluid">
    <div class="row">
        <div class="col-md-9">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("Exercises"); ?></h3>
                    <div class="panel-heading-buttons">
                        <?php echo $this->Html->link('<i class="fa fa-search"></i> '.__('Reset')." ", array('controller' => 'Exercises'),array('escape' => false,'class' => 'btn btn-info btn-sm')); ?>
                    </div>
                </div>
                <table class="table table-hover table-striped">
                    <thead>
                    <tr>
                        <th class="text-center"><?php echo $this->Paginator->sort('id'); ?></th>
                        <th class="text-center"><?php echo __('Subject'); ?></th>
                        <th class="text-center"><?php echo __('Professors'); ?></th>
                        <th class="text-center"><?php echo $this->Paginator->sort('title',__('Exercise')); ?></th>
                        <th class="text-center"><?php echo __('Participants'); ?></th>
                        <th class="text-center"><?php echo __('Commits'); ?></th>
                        <th class="text-center"><?php echo $this->Paginator->sort('open_date'); ?></th>
                        <th class="text-center"><?php echo $this->Paginator->sort('deadline'); ?></th>
                        <th class="text-center"><?php echo __('Actions'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($exercises as $exercise): ?>
                        <tr>
                            <td class="text-center"><?php echo h($exercise['Exercise']['id']); ?>&nbsp;</td>
                            <td><?php echo h($exercise['Course']['name'])." (".h($exercise['Offering']['classroom']).") <br>".h($exercise['University']['name']); ?>&nbsp;</td>
                            <td>
                                <?php foreach ($exercise['Offering']['Professor'] as $emailProf => $prof) {
                                    echo $this->Html->link($prof,array('controller' => 'Users', 'action' => 'view',$emailProf))."<br>";
                                }
                                ?>
                            </td>
                            <td><?php echo h($exercise['Exercise']['title']); ?>&nbsp;</td>
                            <td class="text-center"><?php echo h($exercise['Exercise']['num_participants']); ?>&nbsp;</td>
                            <td class="text-center"><?php echo h($exercise['Exercise']['num_commits']); ?>&nbsp;</td>
                            <td class="text-center"><?php echo $this->Time->format('d/m/Y H:i',h($exercise['Exercise']['open_date'])); ?>&nbsp;</td>
                            <td class="text-center"><?php echo $this->Time->format('d/m/Y H:i',h($exercise['Exercise']['deadline'])); ?>&nbsp;</td>
                            <td class="text-center">
                                <?php echo $this->Html->link(__('View'), array('action' => 'viewProfessor', $exercise['Exercise']['id']),array('class' => 'btn btn-primary btn-sm')); ?>
                                <?php if($exercise['Exercise']['removed'])echo $this->Form->postLink(__('Restore'), array('action' => 'restore', $exercise['Exercise']['id']), array('class' => 'btn btn-danger btn-sm'), __('Are you sure you want to restore # %s?', $exercise['Exercise']['title'])); ?>
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
                    <?php echo $this->Form->create('Exercise',array('novalidate' => true)); ?>
                    <fieldset>
                        <?php echo $this->Form->input('university_id', array('label' => array('text' => __('University'),'class' => 'control-label'), 'required', 'options' => $universities,'class' => 'form-control chosen-unique-select','div' => array('class' => 'form-group'))); ?>
                        <?php echo $this->Form->input('type', array('label' => array('text' => __('Type'),'class' => 'control-label'), 'required', 'options' => $types,'class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                        <?php echo $this->Form->input('title', array('label' => array('text' => __('Title'),'class' => 'control-label'), 'type' => 'text','allowEmpty' => true,'class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                    </fieldset>
                    <?php echo $this->Form->end(array('label' => __('Filter'), 'class' => 'btn btn-color-one ')); ?>
                </div>
            </div>
        </div>
    </div>
</div>