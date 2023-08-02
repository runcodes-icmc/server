<table class="table table-hover table-striped table-bordered">
    <thead>
        <tr>
            <th><?php echo __('Name'); ?></th>
            <th class="text-center hidden-md"><?php echo __('Date'); ?></th>
            <th class="text-center"><?php echo __('Status'); ?></th>
            <th class="text-center"><?php echo __('Corrects'); ?></th>
            <th class="text-center"><?php echo __('Score'); ?></th>
            <th class="text-center"><?php echo __('Actions'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($commits as $key => $commit): ?>
            <tr>
                <td><?php echo $commit["User"]['name']; ?></td> 
                <td class="text-center hidden-md"><?php echo $this->Time->format('d/m/Y H:i:s',$commit['Commit']["commit_time"]); ?></td>
                <td class="text-center"><?php echo $commit["Commit"]["name_status"]; ?></td>
                <td class="text-center"><?php echo $commit["Commit"]["corrects"]; ?></td>
                <td class="text-center"><?php echo $commit["Commit"]["score"]; ?></td>
                <td class="text-center">
                    <?php echo $this->Html->link(__('Details'), array('controller' => 'commits', 'action' => 'details', $commit["Commit"]["id"]), array('class' => 'btn btn-color-one btn-sm')); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table> 