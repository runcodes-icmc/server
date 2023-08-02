<div class="container-fluid">
    <div class="row row-min-height-230">
        <div class="<?php if($assistantOrProfessor): ?> col-md-8 <?php else: ?> col-md-12 <?php endif; ?>">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("General Info"); ?>:</h3>
                    <div class="panel-heading-buttons">
                        <?php  echo $this->Html->link('<i class="fa fa-download"></i> '.__('Download File'),array('controller' => 'Commits', 'action' => 'download', $commit['Commit']['id']),array('escape' => false,'class' => 'btn btn-sm btn-color-one')); ?>
                    </div>
                </div>
                <div class="panel-body">
                    <p>
                        <?php echo __("Submitted by: %s (%s)", $commit['User']['name'], $commit['User']['email']); ?><br>
                        <?php echo $commit['User']['University']['student_identifier_text'].": ".$commit['User']['identifier']; ?><br>
                        <?php echo __("Date: %s", $this->Time->format('d/m/Y H:i:s',$commit['Commit']['commit_time'])); ?><br>
                    </p>

                    <div class="row">
                        <div class="col-md-4 col-sm-3">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="box infographic color-<?php echo $commit['Commit']['status_color']; ?>">
                                        <div class="headline">status</div>
                                        <div class="text-value"><?php echo $commit['Commit']['name_status']; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 col-sm-9">
                            <div class="row">
                                <div class="col-md-4 col-sm-6">
                                    <div class="box infographic color-<?php echo $commit['Commit']['compiled_color']; ?>">
                                        <div class="headline"><?php echo __("compiled"); ?></div>
                                        <div class="value"><?php echo ($commit['Commit']['compiled']) ? __("Yes") : __("No"); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4 hidden-sm">
                                    <div class="box infographic color-<?php echo $commit['Commit']['correct_color']; ?>">
                                        <div class="headline"><?php echo __("correct cases"); ?></div>
                                        <div class="value"><?php echo $commit['Commit']['corrects']; ?>/<?php echo $commit['Exercise']['num_cases']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="box infographic color-<?php echo $commit['Commit']['score_color']; ?>">
                                        <div class="headline"><?php echo __("score"); ?></div>
                                        <div class="value"><?php echo $commit['Commit']['score']; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <?php if(isset($commit['Commit']['detailed_status'])) : ?>
                                <p>
                                    <span class="label label-<?php echo $commit['Commit']['status_color']; ?>"><?php echo $commit['Commit']['name_status']; ?></span>: <?php echo $commit['Commit']['detailed_status']; ?>
                                </p>
                            <?php endif; ?>
                            <p>
                                <?php if (strlen($commit['Commit']['compiled_message']) > 0) echo __("Compiled Message").": <br>".  nl2br($commit['Commit']['compiled_message']); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php if($assistantOrProfessor): ?>
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo __("Edit Score"); ?>:</h3>
                        <div class="panel-heading-buttons">
                            <?php if($assistantOrProfessor): ?>
                                <?php echo $this->Html->link('<i class="fa fa-refresh"></i> '.__('Recompile'), array('controller' => 'Commits', 'action' => 'recompile',$commit['Commit']['id']),array('escape' => false,'class' => 'btn btn-danger btn-sm')); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="panel-body">
                        <?php echo $this->Form->create('Commit',array('url' => array('controller' => 'Commits', 'action' => 'editScore',$commit['Commit']['id']))); ?>
                        <?php echo $this->Form->input('id'); ?>
                        <?php echo $this->Form->input('score', array('label' => array('text' => __('New Score'),'class' => 'control-label'), 'type' => 'number','step' => '0.01','value' => $commit['Commit']['score'],'class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                        <?php echo $this->Form->input('status', array('label' => array('text' => __('Status'),'class' => 'control-label'), 'type' => 'select', 'options' => $statusList,'value' => $commit['Commit']['status'],'class' => 'form-control','div' => array('class' => 'form-group'))); ?>
                        <?php echo $this->Form->end(array('label' => __('Confirm Change'), 'class' => 'btn btn-color-one ')); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("File");": "; ?></h3>
                </div>
                <div class="panel-body">
                    <?php if ($ext != "zip" && $ext != "exe" && $ext != "pdf") : ?>
                        <pre><code><?php echo h($file_content); ?></code></pre>
                    <?php else:
                        if ($ext == "pdf") : ?>
                        <object data="<?php echo $file_content; ?>" type="application/pdf" style="width: 100%; min-height: 800px;">alt:<a href="<?php echo $file_content; ?>">Download</a>
                        </object>
                        <?php else : ?>
                            <ul class="nav nav-tabs">
                                <?php foreach($zipFiles as $k => $zipFile) : ?>
                                    <li  <?php if($k==0) : ?> class="active" <?php endif; ?>><a href="#file-tab-<?php echo $k; ?>" data-toggle="tab"><?php echo $zipFile['name'] ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="tab-content">
                                <?php foreach($zipFiles as $k => $zipFile) : ?>
                                    <div class="tab-pane <?php if($k==0) : ?>active<?php endif; ?>" id="file-tab-<?php echo $k; ?>">
                                        <?php if($zipFile['type'] == 'text') : ?>
                                            <pre><code><?php echo h($zipFile['content']); ?></code></pre>
                                        <?php else: ?>
                                            <object data="<?php echo $zipFile['content'] ?>" type="application/pdf" style="width: 100%; min-height: 800px;">alt:<a href="<?php echo $zipFile['content'] ?>">Download</a>
                                            </object>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>