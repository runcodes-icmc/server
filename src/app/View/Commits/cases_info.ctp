<table class="table table-hover table-striped table-bordered">
    <thead>
    <tr>
        <th class="text-center"><?php echo __('Case'); ?></th>
        <th class="text-center"><?php echo __('Status'); ?></th>
        <th class="text-center"><?php echo __('CPU Time Used'); ?></th>
        <th class="text-center"><?php echo __('Mem Size Used'); ?></th>
        <th class="text-center"><?php echo __('Message'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($commit['ExerciseCase'] as $key => $case):
        ?>
        <tr>
            <td class="text-center"><?php echo __("Case ") . ($key + 1); ?></td>
            <td class="text-center">
                <?php if ($case['CommitsExerciseCase']['status'] == 1) :  ?>
                    <span class="label label-success"><?php echo __('Accepted'); ?></span>
                <?php else: ?>
                    <span class="label label-danger"><?php echo __('Rejected'); ?></span>
                <?php endif; ?>
            </td>
            <td class="text-center"><?php echo h($case['CommitsExerciseCase']['cputime'])." ".__("sec"); ?></td>
            <td class="text-center"><?php echo h($case['CommitsExerciseCase']['memused'])." Kb"; ?> </td>
            <td class="text-center"><?php echo __($case['CommitsExerciseCase']['status_message']); ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<p><?php echo __("Refresh this page to see the details of each case correction"); ?></p>