<?php foreach ($questions as $k => $question) : ?>
<div class="container-fluid">
    <div class="row-fluid">
        <div class="index widget span12" style="margin-bottom: 2px;">
            <div class="widget-header header-faq">
                <a href="#" data-panel="faq-<?php echo $k; ?>" id="btn-toggle-faq-<?php echo $k; ?>" class="btn-toggle-panel faq-link"><?php echo $question['Question']['title']; ?></a>
            </div>
            <div class="widget-body faq-text panel-hide" style="height: 0px; padding: 0" id="panel-faq-<?php echo $k; ?>">
                <?php echo ($question['Question']['text']); ?>
            </div>
        </div>
    </div>  
</div>
<?php endforeach; ?>
<script>hljs.initHighlightingOnLoad();</script>