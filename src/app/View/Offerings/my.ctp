<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __('Active Offerings'); ?></h3>
                </div>
                <table class="table table-hover table-striped table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 10%"><?php echo __('University'); ?></th>
                            <th class="text-center" style="width: 50%"><?php echo __('Course'); ?></th>
                            <th class="text-center" style="width: 10%"><?php echo __('Classroom'); ?></th>
                            <th class="text-center" style="width: 10%"><?php echo __('Role'); ?></th>
                            <th class="text-center" style="width: 10%"><?php echo __('Participants'); ?></th>
                            <th class="text-center actions" style="width: 10%"><?php echo __('Actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($my_offerings as $offering): ?>
                            <tr>
                                <td class="text-center">
                                    <?php echo h($offering['University']['abbreviation']); ?>
                                </td>
                                <td>
                                    <?php echo h($offering['Course']['title']); ?>
                                </td>
                                <td class="text-center"><?php echo h($offering['Offering']['classroom']); ?>&nbsp;</td>
                                <td class="text-center"><?php echo h($offering['Enrollment']['role_name']); ?>&nbsp;</td>
                                <td class="text-center"><?php echo h($offering['Offering']['num_participants']); ?>&nbsp;</td>
                                <td class="actions text-center">
                                    <?php echo $this->Html->link(__('View'), array('action' => 'view', $offering['Offering']['id']), array('class' => 'btn btn-info btn-sm')); ?>
                                    <?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $offering['Offering']['id']), array('class' => 'btn btn-danger btn-sm')); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __('Past Offerings')." (".__("Closed").")"; ?></h3>
                </div>
                <?php if (count($my_closed_offerings) > 0) : ?>
                <table class="table table-hover table-striped table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 10%"><?php echo __('University'); ?></th>
                            <th class="text-center" style="width: 40%"><?php echo __('Course'); ?></th>
                            <th class="text-center" style="width: 10%"><?php echo __('Classroom'); ?></th>
                            <th class="text-center" style="width: 10%"><?php echo __('Role'); ?></th>
                            <th class="text-center" style="width: 10%"><?php echo __('Participants'); ?></th>
                            <th class="text-center" style="width: 10%"><?php echo __('Finished'); ?></th>
                            <th class="text-center actions" style="width: 10%"><?php echo __('Actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($my_closed_offerings as $offering): ?>
                            <tr>
                                <td class="text-center">
                                    <?php echo h($offering['University']['abbreviation']); ?>
                                </td>
                                <td>
                                    <?php echo h($offering['Course']['title']); ?>
                                </td>
                                <td class="text-center"><?php echo h($offering['Offering']['classroom']); ?>&nbsp;</td>
                                <td class="text-center"><?php echo h($offering['Enrollment']['role_name']); ?>&nbsp;</td>
                                <td class="text-center"><?php echo h($offering['Offering']['num_participants']); ?>&nbsp;</td>
                                <td class="text-center"><?php echo $this->Time->format('d/m/Y',$offering['Offering']['end_date']); ?>&nbsp;</td>
                                <td class="actions text-center">
                                    <?php echo $this->Html->link(__('View'), array('action' => 'view', $offering['Offering']['id']), array('class' => 'btn btn-info btn-sm')); ?>
                                    <?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $offering['Offering']['id']), array('class' => 'btn btn-danger btn-sm')); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="panel-body">
                    <p>
                        <?php echo __("You do not have any closed offering"); ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>




