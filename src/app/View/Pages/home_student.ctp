<?php if(1==2 && $logged_user['type']>0) : ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">
                Atenção: Devido ao procedimento de migração de servidores e atualização do sistema, nosso motor de compilação está apresentando grande instabilidade. Estamos trabalhando para que tudo seja solucionado o mais rápido possível. Estes contratempos não afetam qualquer ação no sistema, de maneira que entregas que não possam ser processadas atualmente poderão ser corrigidas normalmente quando a situação estiver normalizada.
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php if($logged_user['type']>2) echo $this->element("adminHomePanels"); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __('Next Deadlines'); ?></h3>
                </div>
                <?php if(count($exercises)>0): ?>
                    <table class="table table-hover table-striped">
                        <thead>
                        <tr>
                            <th><?php echo __('Exercise'); ?></th>
                            <th class="text-center"><?php echo __('Status'); ?></th>
                            <th class="text-center"><?php echo __('Correct Cases'); ?></th>
                            <th class="text-center"><?php echo __('Grade'); ?></th>
                            <th class="text-center"><?php echo __('Deadline'); ?></th>
                            <th class="text-center"><?php echo __('Actions'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($exercises as $exercise): ?>
                            <tr>
                                <td><?php echo h($exercise['Exercise']['title']); ?><br><small><?php echo h($exercise['Offering']['Course']['code'])." - ".h($exercise['Offering']['Course']['title']); ?></small></td>
                                <td class="text-center"><span class="label label-<?php echo $exercise['MyCommit']['status_color']; ?>"><?php echo $exercise['MyCommit']['name_status']; ?></span></td>
                                <td class="text-center"><span class="label label-<?php echo $exercise['MyCommit']['correct_color']; ?>"><?php echo $exercise['MyCommit']['corrects']; ?>/<?php echo $exercise['Exercise']['num_cases']; ?></span></td>
                                <td class="text-center"><span class="label label-<?php echo $exercise['MyCommit']['score_color']; ?>"><?php echo $exercise['MyCommit']['score']; ?></span></td>
                                <td class="text-center"><?php echo $this->Time->format('d/m/Y H:i:s',$exercise['Exercise']['deadline']); ?></td>
                                <td class="text-center">
                                    <?php
                                    if ($exercise['Offering']['myRole'] > 0 && (isset($logged_user['onlyStudent']) && !$logged_user['onlyStudent'])) echo $this->Html->link(__('View Details as Professor'), array('controller' => 'exercises', 'action' => 'viewProfessor', h($exercise['Exercise']['id'])),array('class' => 'btn btn-color-one btn-sm'));
                                    else echo $this->Html->link(__('View Details'), array('controller' => 'exercises', 'action' => 'view', h($exercise['Exercise']['id'])),array('class' => 'btn btn-color-one btn-sm'));
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="panel-body"><p><?php echo __("You do not have any exercise to submit in the next days"); ?></p></div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>


<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="users index panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __('My Subjects'); ?></h3>
                    <?php if($logged_user['type']>1): ?>
                    <div class="panel-heading-buttons">
                        <?php echo $this->Html->link('<i class="fa fa-plus"></i> '.__('New Offering'), array('controller' => 'Offerings','action' => 'add'),array('escape' => false,'class' => 'btn btn-info btn-sm')); ?>
                    </div>
                    <?php endif; ?>
                </div>
                <table class="table table-hover">
                    <tbody>
                        <?php foreach ($enrollments as $enrollment): ?>
                        <tr>
                            <td class="text-center"><?php echo h($enrollment['Offering']['Course']['code']); ?></td>
                            <td><?php echo h($enrollment['Offering']['Course']['title']); ?> - <?php echo h($enrollment['Offering']['classroom']); ?></td>
                            <td class="text-right"><?php echo $this->Html->link(__('View Subject Page'), array('controller' => 'offerings', 'action' => 'view', h($enrollment['Offering']['id'])),array('class' => 'btn btn-color-one btn-sm')); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="panel-body">
                    <?php echo $this->Html->link(__('View Past Offerings'), array('controller' => 'Offerings','action' => 'my'),array('class' => 'btn btn-color-one')); ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="users index panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __('New Enrollment'); ?></h3>
                </div>
                <div class="panel-body">
                    <?php echo $this->Form->create('Enrollment', array('action'=> 'add')); ?>
                    <fieldset>
                    <?php
                        echo $this->Form->input('enrollment_code',array('type'=>'text', 'label'=>__('Enrollment Code').":",'class' => 'form-control input-lg input-enrollment-code','data-mask' => 'wwww','div' => array('class' => 'form-group'),'error' => array('attributes' => array('class' => 'has-error help-block'))));
                    ?>
                    </fieldset>
                    <p><?php echo __("If you do not know the enrollment code for your classroom, contact your professor"); ?></p>
                    <?php echo $this->Form->end(array('label' => __('Enrollment'), 'class' => 'btn btn-color-one ')); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if($logged_user['type']>0 && 1 == 2): ?>
<div class="container-fluid">
    <div class="row">
        <div class="users index panel col-md-6">
            <div class="panel-heading">
                <h3><?php echo __('Alerts'); ?></h3>
            </div>
            <div class="panel-body">
                <?php if (isset($my_offerings) && count($my_offerings) > 0) : ?>
                    <?php echo $this->Form->create('Alert',array('controller' => 'Alerts', 'action' => 'add')); ?>
                    <?php
                    echo $this->Form->input('offering_id',array('class' => 'col-md-12 form-control', 'options' => $my_offerings,'div' => array('class' => 'form-group')));
                    ?>
                    <div class="row" style="margin-left: 0px !important;">
                        <div class="col-md-6"><?php echo $this->Form->input('type',array('type' => 'select', 'options' => $alert_types,'class' => 'form-control col-md-12','div' => array('class' => 'form-group'),'error' => array('attributes' => array('class' => 'has-error help-block')))); ?></div>
                        <div class="col-md-6"><?php echo $this->Form->input('recipients',array('type' => 'select', 'options' => $enrollment_role,'class' => 'form-control col-md-12','div' => array('class' => 'form-group'),'error' => array('attributes' => array('class' => 'has-error help-block')))); ?></div>
                    </div>
                    <?php
                    echo $this->Form->input('title', array('label' => array('text' => __('Title'),'class' => 'control-label'), 'type' => 'text','class' => 'form-control col-md-12','div' => array('class' => 'form-group'),'error' => array('attributes' => array('class' => 'has-error help-block'))));
                    echo $this->Form->input('message', array('label' => array('text' => __('Message'),'class' => 'control-label'), 'type' => 'text','class' => 'form-control col-md-12','div' => array('class' => 'form-group'),'error' => array('attributes' => array('class' => 'has-error help-block'))));
                    echo $this->Form->input('valid', array('label' => array('text' => __('Valid until'),'class' => 'control-label'),'class' => 'form-control col-md-3','div' => array('class' => 'form-group'),'error' => array('attributes' => array('class' => 'has-error help-block'))));
                    ?>
                    <?php echo $this->Form->end(array('label' => __('Submit'), 'class' => 'btn btn-color-one ')); ?>
                <?php
                else: echo __("You only can publish alerts if you are the professor of some offering");
                endif; ?>
            </div>
        </div>
        <div class="tickets index panel col-md-6">
            <div class="panel-heading">
                <h3><?php echo __('Talk with the Admin'); ?></h3>
            </div>
            <div class="panel-body">
                <?php
                echo $this->Form->create('Ticket', array('action'=> 'add')); ?>
                <fieldset>
                <?php
                    echo $this->Form->input('type',array('class' => 'col-md-12 form-control','type' => 'select', 'options' => $ticketsTypeList,'div' => array('class' => 'form-group')));
                    echo $this->Form->input('priority',array('class' => 'col-md-12 form-control','type' => 'select', 'options' => $ticketsPriorityList,'div' => array('class' => 'form-group')));
                    echo $this->Form->input('message', array('label' => array('text' => __('Message'),'class' => 'control-label'), 'type' => 'textarea','class' => 'col-md-12 form-control','div' => array('class' => 'form-group')));
                ?>
                </fieldset>
                <?php echo $this->Form->end(array('label' => __('Send'), 'class' => 'btn btn-color-one ')); ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
