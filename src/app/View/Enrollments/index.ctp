<div class="container-fluid">
    <div class="row-fluid">
        <div class="myenrollments index widget span12">
            <div class="widget-header">
                <h3><?php echo __('My Enrollments'); ?></h3>                
            </div>
            <div class="widget-body">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#my-enrollments-active" data-toggle="tab">Active</a></li>
                    <li><a href="#my-enrollments-closed" data-toggle="tab">Closed</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="my-enrollments-active">
                        <?php if(count($enrollments)>0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th><?php echo __('Subject Code'); ?></th>
                                    <th><?php echo __('Subject'); ?></th>
                                    <th><?php echo __('Status'); ?></th>
                                    <th><?php echo __('Actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enrollments as $enrollment): ?>
                                <tr>
                                    <td class="text-center"><?php echo h($enrollment['Offering']['Course']['code']); ?></td>
                                    <td><?php echo h($enrollment['Offering']['Course']['title']); ?> - <?php echo h($enrollment['Offering']['classroom']); ?></td>
                                    <td class="center"><span class="badge badge-success"><?php echo __('Active'); ?></span></td>
                                    <td class="center">
                                        <?php echo $this->Html->link(__('New Exercise'), array('controller' => 'exercises', 'action' => 'add', h($enrollment['Offering']['id'])),array('class' => 'btn btn-primary btn-small')); ?>
                                        <?php echo $this->Html->link(__('View Subject Page'), array('controller' => 'offerings', 'action' => 'view', h($enrollment['Offering']['id'])),array('class' => 'btn btn-yellow btn-small')); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p><?php echo __('You do not have any active subject'); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="tab-pane" id="my-enrollments-closed">
                        <?php if(count($enrollments_closed)>0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th><?php echo __('Subject Code'); ?></th>
                                    <th><?php echo __('Subject'); ?></th>
                                    <th><?php echo __('Status'); ?></th>
                                    <th><?php echo __('Actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enrollments_closed as $enrollment): ?>
                                <tr>
                                    <td class="text-center"><?php echo h($enrollment['Offering']['Course']['code']); ?></td>
                                    <td><?php echo h($enrollment['Offering']['Course']['title']); ?> - <?php echo h($enrollment['Offering']['classroom']); ?></td>
                                    <td class="center"><span class="badge badge-important"><?php echo __('Closed'); ?></span></td>
                                    <td class="center">
                                        <?php echo $this->Html->link(__('View Exercises'), array('controller' => 'exercises', 'action' => 'index', h($enrollment['Offering']['id'])),array('class' => 'btn btn-yellow btn-small')); ?>
                                        <?php echo $this->Html->link(__('View Grades'), array('controller' => 'exercises', 'action' => 'grades', h($enrollment['Offering']['id'])),array('class' => 'btn btn-yellow btn-small')); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p><?php echo __('You do not have any closed subject'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




<div class="container-fluid">
    <div class="row-fluid">
        <div class="myenrollments index widget span12">
            <div class="widget-header"> 
                <h3><?php echo __('New Enrollment'); ?></h3>
            </div>
            <div class="widget-body">
                <?php if(count($offering)): ?>
                <?php echo $this->Form->create('Enrollment', array('action'=> 'add')); ?>
                <fieldset>
                <?php
                        echo $this->Form->input('offering_id',array('class' => 'span12', 'type'=>'select', 'label'=>__('Offering'),'options'=>$offering));
                ?>
                </fieldset>
                <?php echo $this->Form->end(array('label' => __('Enrollment'), 'class' => 'btn btn-yellow ')); ?>
                <?php else: ?>
                <p><?php echo __("You do not have available subjects to enroll"); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>