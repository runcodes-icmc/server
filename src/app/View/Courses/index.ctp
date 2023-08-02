<div class="container-fluid">
  <div class="row">
    <div class="col-md-9">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><?php echo __("Courses"); ?></h3>
          <div class="panel-heading-buttons">
            <?php echo $this->Html->link('<i class="fa fa-search"></i> ' . __('Reset') . " ", array('controller' => 'Courses'), array('escape' => false, 'class' => 'btn btn-info btn-sm')); ?>
          </div>
        </div>
        <div class="panel-body">
          <div class="btn-group">
            <?php foreach ($letters as $l) :
              $class = 'btn btn-sm btn-danger ';
              if ($startswith == $l) :
                $class .= 'active';
              endif;
              echo $this->Html->link(__($l), array('action' => 'index', 'startswith' => $l), array('class' => $class)); ?>
            <?php endforeach; ?>
          </div>
        </div>
        <table class="table table-hover table-striped">
          <thead>
            <tr>
              <th class="text-center"><?php echo $this->Paginator->sort('Id'); ?></th>
              <th class="text-center"><?php echo $this->Paginator->sort('University'); ?></th>
              <th class="text-center"><?php echo $this->Paginator->sort('Subject Code'); ?></th>
              <th><?php echo $this->Paginator->sort('Subject'); ?></th>
              <th class="text-center"><?php echo __('Actions'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($courses as $course) : ?>
              <tr>
                <td class="text-center"><?php echo h($course['Course']['id']); ?>&nbsp;</td>
                <td class="text-center"><?php echo $this->Html->link($course['University']['abbreviation'], array('university' => $course['Course']['university_id'])); ?>&nbsp;</td>
                <td class="text-center"><?php echo h($course['Course']['code']); ?>&nbsp;</td>
                <td><?php echo h($course['Course']['title']); ?>&nbsp;</td>
                <td class="actions text-center">
                  <?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $course['Course']['id']), array('class' => 'btn btn-color-one btn-sm')); ?>
                  <?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $course['Course']['id']), array('class' => 'btn btn-color-three btn-sm'), __('Are you sure you want to delete # %s?', $course['Course']['id'])); ?>
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
              echo $this->Paginator->prev(__('prev'), array('tag' => 'li'), null, array('tag' => 'li', 'class' => 'disabled', 'disabledTag' => 'a'));
              echo $this->Paginator->numbers(array('separator' => '', 'currentTag' => 'a', 'currentClass' => 'active', 'tag' => 'li', 'first' => 1));
              echo $this->Paginator->next(__('next'), array('tag' => 'li', 'currentClass' => 'disabled'), null, array('tag' => 'li', 'class' => 'disabled', 'disabledTag' => 'a'));
              ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><?php echo __("Filter"); ?></h3>
        </div>
        <div class="panel-body">
          <?php echo $this->Form->create('Course', array('novalidate' => true)); ?>
          <fieldset>
            <?php echo $this->Form->input('university_id', array('label' => array('text' => __('University'), 'class' => 'control-label'), 'required', 'options' => $universities, 'class' => 'form-control chosen-unique-select', 'div' => array('class' => 'form-group'))); ?>
            <?php echo $this->Form->input('code', array('label' => array('text' => __('Code'), 'class' => 'control-label'), 'type' => 'text', 'allowEmpty' => true, 'class' => 'form-control', 'div' => array('class' => 'form-group'))); ?>
            <?php echo $this->Form->input('title', array('label' => array('text' => __('Title'), 'class' => 'control-label'), 'type' => 'text', 'allowEmpty' => true, 'class' => 'form-control', 'div' => array('class' => 'form-group'))); ?>
          </fieldset>
          <?php echo $this->Form->end(array('label' => __('Filter'), 'class' => 'btn btn-color-one ')); ?>
        </div>
      </div>

      <div class="panel panel-default" ng-controller="AddCoursesForm" ng-init="batch=false">
        <div class="panel-heading">
          <h3 class="panel-title"><?php echo __("Add Course"); ?></h3>
          <div class="panel-heading-buttons">
            <?php echo $this->Html->link(__("Add in Batch"), '#', array('ng-click' => 'batch=true;$event.preventDefault()', 'ng-show' => '!batch', 'escape' => false, 'class' => 'btn btn-color-two btn-sm')); ?>
            <?php echo $this->Html->link(__("Add Normal"), '#', array('ng-click' => 'batch=false;$event.preventDefault()', 'ng-show' => 'batch', 'class' => 'btn btn-color-two btn-sm')); ?>
          </div>
        </div>
        <div class="panel-body">
          <?php echo $this->Form->create('Course', array('action' => 'add')); ?>
          <fieldset>
            <?php unset($universities['-1']);
            echo $this->Form->input('university_id', array('label' => array('text' => __('University'), 'class' => 'control-label'), 'required', 'options' => $universities, 'class' => 'form-control chosen-unique-select', 'div' => array('class' => 'form-group'))); ?>
            <?php echo $this->Form->input('code', array('label' => array('text' => __('Code'), 'class' => 'control-label'), 'type' => 'text', 'allowEmpty' => true, 'class' => 'form-control', 'div' => array('class' => 'form-group', 'ng-if' => '!batch'))); ?>
            <?php echo $this->Form->input('title', array('label' => array('text' => __('Title'), 'class' => 'control-label'), 'type' => 'text', 'allowEmpty' => true, 'class' => 'form-control', 'div' => array('class' => 'form-group', 'ng-if' => '!batch'))); ?>
            <?php echo $this->Form->input('batch', array('label' => array('text' => __('Courses in Batch'), 'class' => 'control-label'), 'type' => 'textarea', 'allowEmpty' => true, 'class' => 'form-control', 'after' => '<div class="help-block">' . __("Add one course per line in the format [course_code]{espace}[course_name]") . '</div>', 'div' => array('class' => 'form-group', 'ng-if' => 'batch'))); ?>
          </fieldset>
          <?php echo $this->Form->end(array('label' => __('Submit'), 'class' => 'btn btn-color-one ')); ?>
        </div>
      </div>
    </div>
  </div>
</div>
