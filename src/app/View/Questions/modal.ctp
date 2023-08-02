<link rel="stylesheet" href="https://yandex.st/highlightjs/8.0/styles/default.min.css">
<script src="https://yandex.st/highlightjs/8.0/highlight.min.js"></script>
<div class="modal-header"  style="min-width: 70%">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="modalQuestionLabel"><?php echo $question['Question']['title']; ?></h3>
</div>
<div class="modal-body">
    <?php echo $question['Question']['text']; ?>
</div>
<div class="modal-footer">
</div>
<script>hljs.initHighlightingOnLoad();</script>