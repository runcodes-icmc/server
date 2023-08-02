<div class="container-fluid">
    <div class="row">
        <div class="col-md-10">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("Commits"); ?></h3>
                    <div class="panel-heading-buttons">
                        <?php echo $this->Html->link('<i class="fa fa-search"></i> '.__('Reset')." ", array('controller' => 'Commits'),array('escape' => false,'class' => 'btn btn-info btn-sm')); ?>
                    </div>
                </div>
                <table class="table table-hover table-striped">
                    <thead>
                    <tr>
                        <th class="text-center"><?php echo $this->Paginator->sort('id'); ?></th>
                        <th><?php echo __("Name"); ?></th>
                        <th><?php echo __("Exercise"); ?></th>
                        <th class="text-center"><?php echo $this->Paginator->sort('status'); ?></th>
                        <th class="text-center"><?php echo $this->Paginator->sort('score'); ?></th>
                        <th class="text-center"><?php echo $this->Paginator->sort('corrects'); ?></th>
                        <th class="text-center"><?php echo $this->Paginator->sort('commit_time'); ?></th>
                        <th class="text-center"><?php echo $this->Paginator->sort('compilation_finished'); ?></th>
                        <th class="text-center"><?php echo __('Actions'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($commits as $commit): ?>
                        <tr>
                            <td class="text-center"><?php echo h($commit['Commit']['id']); ?>&nbsp;</td>
                            <td>
                                <?php echo $this->Html->link('<i class="fa fa-filter"></i>', array('controller' => 'Commits', 'action' => 'index','user_email' => $commit['Commit']['user_email']),array('escape' => false)); ?>
                                <?php echo $this->Html->link(h($commit['User']['name']),array("controller" => "Users", "action" => "view",$commit['Commit']['user_email'])); ?>
                            </td>
                            <td>
                                <?php echo $this->Html->link('<i class="fa fa-filter"></i>', array('controller' => 'Commits', 'action' => 'index','exercise' => $commit['Commit']['exercise_id']),array('escape' => false)); ?>
                                <?php echo $this->Html->link($commit['Exercise']['title'], array('controller' => 'Exercises', 'action' => 'viewProfessor', $commit['Commit']['exercise_id'])); ?>
                            </td>
                            <td class="text-center"><span class="label label-<?php echo $commit['Commit']['status_color']; ?>"><?php echo $commit['Commit']['name_status']; ?></span></td>
                            <td class="text-center"><span class="label label-<?php echo $commit['Commit']['score_color']; ?>"><?php echo $commit['Commit']['score']; ?></span></td>
                            <td class="text-center"><span class="label label-<?php echo $commit['Commit']['correct_color']; ?>"><?php echo $commit['Commit']['corrects']; ?>/<?php echo $commit['Exercise']['num_cases']; ?></td>
                            <td class="text-center"><?php echo $this->Time->format('d/m/Y H:i:s',$commit['Commit']["commit_time"]); ?></td>
                            <td class="text-center"><?php if (!is_null($commit['Commit']["compilation_finished"])) echo $this->Time->format('d/m/Y H:i:s',$commit['Commit']["compilation_finished"]); ?></td>
                            <td class="actions text-center">
                                <?php echo $this->Html->link(__('Details'), array('action' => 'details', $commit['Commit']['id']), array('class' => 'btn btn-info btn-sm')); ?>
                                <?php echo $this->Html->link(__('Recompile'), array('action' => 'recompile', $commit['Commit']['id']), array('class' => 'btn btn-danger btn-sm')); ?>
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
        <div class="col-md-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("Filter"); ?></h3>
                </div>
                <div class="panel-body">
                    <?php echo $this->Form->create('Commit',array('novalidate' => true)); ?>
                    <fieldset>
                        <?php echo $this->Form->input('status', array('label' => array('text' => __('Status'),'class' => 'control-label'), 'options' => $status,'class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                        <?php //echo $this->Form->input('name', array('label' => array('text' => __('Name'),'class' => 'control-label'), 'type' => 'text','allowEmpty' => true,'class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                    </fieldset>
                    <?php echo $this->Form->end(array('label' => __('Filter'), 'class' => 'btn btn-color-one ')); ?>
                </div>
            </div>
        </div>
    </div>
</div>