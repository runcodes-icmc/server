<div class="grades" ng-controller="GradesController">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo $offering['Course']['code']." - ".$offering['Course']['title']; ?></h3>
                    </div>
                    <div class="panel-body">
                        <p>
                            <?php echo __("Classroom").": ".$offering['Offering']['classroom']; ?></br>
                            <?php echo __("Active until").": "; ?><strong><?php echo $this->Time->format('d/m/Y',$offering['Offering']['end_date']); ?></strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo __("Grades"); ?></h3>
                        <div class="panel-heading-buttons">
                            <?php echo __("Show not delivered exercises as").": "; ?>
                            <div class="btn-group" ng-click="$event.preventDefault()">
                                <a href="#" ng-click="notDelivered='0.0'" ng-class="{active: notDelivered=='0.0'}" class="btn btn-sm btn-info">0.0</a>
                                <a href="#" ng-click="notDelivered='--'" ng-class="{active: notDelivered=='--'}" class="btn btn-sm btn-info">--</a>
                            </div>
                            <?php echo $this->Html->link('<i class="fa fa-download"></i> '.__('Export Grades Table'), array('controller' => 'Offerings','action' => 'exportToCsv',$offering['Offering']['id']),array('escape' => false,'class' => 'btn btn-sm btn-color-one')); ?>
                        </div>
                    </div>
                    <table class="table table-hover table-striped table-bordered">
                        <thead>
                            <tr>
                                <th><?php echo __('Name'); ?></th>
                                <th class="text-center"><?php echo $offering['Course']['University']['student_identifier_text']; ?></th>
                                <th class="text-center"><?php echo __('Email'); ?></th>
                                <th class="text-center"><?php echo __('Role'); ?></th>
                                <?php foreach ($scores as $k => $ex) : ?>
                                <th class="text-center"><a href="/Exercises/viewProfessor/<?php echo $ex['Exercise']['id'] ?>" class="tooltip-link" data-placement="bottom" data-toggle="tooltip" title="<?php echo $ex['Exercise']['title']; ?>"><?php echo __("Exercise")." ".($k+1); ?></a></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($offering['Enrollment'] as $student): ?>
                            <tr>
                                <td><?php echo h($student['User']['name']); ?></td>
                                <td class="text-center"><?php echo h($student['User']['identifier']); ?></td>
                                <td><?php echo h($student['user_email']); ?></td>
                                <td class="text-center"><?php echo h($student['role_name']); ?></td>
                                <?php foreach ($scores as $k => $ex) : ?>
                                <td class="text-center"><a href="<?php if (isset($ex['Commit'][$student['user_email']])) echo "/Commits/details/".$ex['Commit'][$student['user_email']]; else echo "#" ?>" class="tooltip-link" <?php if (!isset($ex['Commit'][$student['user_email']])) { ?>ng-click="$event.preventDefault()"<?php } ?> data-toggle="tooltip" title="<?php echo $ex['Status'][$student['user_email']]; ?>"><?php if (isset($ex['Commit'][$student['user_email']])) { echo $ex['Grade'][$student['user_email']]; } else { echo "{{ notDelivered }}"; } ?></a></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>


                </div>
            </div>
        </div>
    </div>
</div>