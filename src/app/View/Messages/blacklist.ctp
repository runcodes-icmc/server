<div class="container-fluid">
    <div class="row">
        <div class="col-md-7 col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("Blacklisted Address"); ?></h3>
                </div>
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th class="text-center"><?php echo __("Id"); ?></th>
                        <th><?php echo __("Address"); ?></th>
                        <th class="text-center"><?php echo __("Type"); ?></th>
                        <th class="text-center"><?php echo __("Actions"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($blacklist as $address): ?>
                        <tr>
                            <td style="width: 5%" class="text-center"><?php echo h($address['BlacklistMail']['id']); ?></td>
                            <td><?php echo h($address['BlacklistMail']['address']); ?></td>
                            <td style="width: 15%" class="text-center"><?php echo $addressType[$address['BlacklistMail']['type']]; ?></td>
                            <td style="width: 15%" class="text-center"><?php echo $this->Form->postLink(__('Remove'), array('controller' => 'Messages', 'action' => 'blacklist', h($address['BlacklistMail']['id'])),array('class' => 'btn btn-color-three btn-sm')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-5 col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("Add to Blacklist"); ?></h3>
                </div>
                <div class="panel-body">
                    <?php echo $this->Form->create('BlacklistMail'); ?>
                    <fieldset>
                        <?php echo $this->Form->input('address', array('label' => array('text' => __('Email/Domain'),'class' => 'control-label'), 'type' => 'text', 'required', 'class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                        <?php echo $this->Form->input('type', array('label' => array('text' => __('Type'),'class' => 'control-label'), 'type' => 'select', 'options' => $addressType, 'required', 'class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                    </fieldset>
                    <?php echo $this->Form->end(array('label' => __('Submit'), 'class' => 'btn btn-color-one ')); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">

    </div>
</div>