<div class="container-fluid">
    <div class="row-fluid">
        <div class="users index widget span12">
            <div class="widget-header">
                <h3><?php echo __('Question'); ?></h3>
            </div>
            <div class="widget-body">
                <?php echo $this->Form->create('Question'); ?>
                <fieldset>
                    <?php
                    echo $this->Form->input('id'); 
                    echo $this->Form->input('title', array('label' => array('text' => __('Title')."/".__("Question"),'class' => 'control-label'), 'type' => 'text','class' => 'span8','div' => array('class' => 'control-group'))); 
                    echo $this->Form->input('tags', array('label' => array('text' => __('Tags'),'class' => 'control-label'), 'type' => 'text','class' => 'span8','div' => array('class' => 'control-group'))); ?>
                    <p>As Tags ajudam na busca por perguntas. Digite todas as Tags separadas por espaços, Exemplo: linguagens exercícios</p>
                    <?php echo $this->Form->input('text', array('label' => array('text' => __('Text'),'class' => 'control-label'), 'type' => 'textarea','class' => 'span8','div' => array('class' => 'control-group'))); 
                    ?>
                </fieldset>
                <?php echo $this->Form->end(array('label' => __('Submit'), 'class' => 'btn btn-yellow ')); ?>
            </div>
        </div>
    </div>
</div>