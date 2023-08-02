<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("Offerings"); ?></h3>
                    <div class="panel-heading-buttons">
                        <?php echo $this->Html->link('<i class="fa fa-search"></i> '.__('Reset'), array('controller' => 'Offerings'),array('escape' => false,'class' => 'btn btn-info btn-sm')); ?>
                    </div>
                </div>
                <table class="table table-hover table-striped">
                    <thead>
                    <tr>
                        <th class="text-center"><?php echo $this->Paginator->sort('id'); ?></th>
                        <th class="text-center"><?php echo __("University"); ?></th>
                        <th class="text-center"><?php echo __("Course"); ?></th>
                        <th class="text-center"><?php echo $this->Paginator->sort('classroom'); ?></th>
                        <th class="text-center"><?php echo $this->Paginator->sort('end_date',__("Active until")); ?></th>
                        <th class="text-center"><?php echo __('Participants'); ?></th>
                        <th class="text-center"><?php echo __('Professors'); ?></th>
                        <th class="text-center"><?php echo __('Actions'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($offerings as $offering): ?>
                        <tr>
                            <td class="text-center"><?php echo h($offering['Offering']['id']); ?></td>
                            <td class="text-center">
                                <?php echo $this->Html->link($offering['University']['abbreviation'],array('university' => $offering['University']['id'])); ?>
                            </td>
                            <td>
                                <?php echo h($offering['Course']['title']); ?>
                            </td>
                            <td class="text-center"><?php echo h($offering['Offering']['classroom']); ?></td>
                            <td class="text-center"><?php echo $this->Time->format('d/m/Y',$offering['Offering']['end_date']); ?></td>
                            <td class="text-center"><?php echo h($offering['Offering']['num_participants']); ?></td>
                            <td class="text-center">
                                <?php foreach ($offering['Professor'] as $emailProf => $prof) {
                                    echo $this->Html->link($prof,array('controller' => 'Users', 'action' => 'view',$emailProf))."<br>";
                                }
                                ?>
                            </td>
                            <td class="actions text-center">
                                <?php echo $this->Html->link(__('View'), array('action' => 'view', $offering['Offering']['id']), array('class' => 'btn btn-info btn-small')); ?>
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
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("Filter"); ?></h3>
                </div>
                <div class="panel-body">
                    <?php echo $this->Form->create('Offering'); ?>
                    <fieldset>
                        <?php echo $this->Form->input('university_id', array('label' => array('text' => __('University'),'class' => 'control-label'), 'required', 'options' => $universities,'class' => 'form-control chosen-unique-select','div' => array('class' => 'form-group'))); ?>
                        <div class="checkbox">
                            <label>
                                <?php echo $this->Form->input('finished', array('label' => false, 'type' => 'checkbox','class' => 'checkbox'));
                                echo __('Include finished offerings'); ?>
                            </label>
                        </div>
                    </fieldset>
                    <?php echo $this->Form->end(array('label' => __('Filter'), 'class' => 'btn btn-color-one ')); ?>
                </div>
            </div>
        </div>
    </div>
</div>
