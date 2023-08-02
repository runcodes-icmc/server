<?php if (count($casesList) > 25) : ?>
<div class="alert alert-danger">
    <?php echo __("This exercise has too many cases, there is not possible to build this report"); ?>
</div>
<?php else : ?>
<table class="table table-hover table-striped table-bordered">
    <thead>
    <tr>
        <th style="width: 25%"><?php echo __('Name'); ?></th>
        <?php if (count($casesList) < 20) : ?>
        <th style="width: 15%" class="text-center"><?php echo $course['University']['student_identifier_text']; ?></th>
        <?php endif; ?>
        <?php foreach ($casesList as $k => $case) : ?>
        <th class="text-center" style="min-width: 52px"><?php echo __('Case')." ".($k+1); ?></th>
        <?php endforeach; ?>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($scores as $key => $score): ?>
        <tr>
            <td><?php echo $score["User"]['name']; ?></td>
            <?php if (count($casesList) < 20) : ?>
            <td class="text-center"><?php echo $score["User"]['identifier']; ?></td>
            <?php endif; ?>
            <?php foreach ($casesList as $k => $case) : ?>
                <th class="text-center">
                    <?php if (isset($score["CommitsExerciseCase"][$case.''])) : ?>
                        <?php if ($score["CommitsExerciseCase"][$case.'']) : ?>
                            <span class="label label-success" class="tooltip-link" data-placement="top" data-toggle="tooltip" title="<?php echo $score["CommitsExerciseCaseMessage"][$case.'']; ?>"><i class="fa fa-check"></i></span>
                        <?php else : ?>
                            <span class="label label-important" class="tooltip-link" data-placement="top" data-toggle="tooltip" title="<?php echo $score["CommitsExerciseCaseMessage"][$case.'']; ?>"><i class="fa fa-remove"></i></span>
                        <?php endif; ?>
                    <?php else : ?>
                        <span class="label label-warning"><i class="fa fa-minus"></i></span>
                    <?php endif; ?>
                </th>
            <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>
