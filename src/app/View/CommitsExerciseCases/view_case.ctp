<?php
//debug($exerciseCase);
?>
<div id="commitTabs">
    <ul class="nav nav-tabs" id="myTab">
      <li class="active"><a href="#input"><?php echo __("Input"); ?></a></li>
      <li><a href="#output"><?php echo __("Output"); ?></a></li>
      <li><a href="#error"><?php echo __("Output Error"); ?></a></li>
    </ul>
    <div class="tab-content exercise-case-content">
        <div class="tab-pane active" id="input">
            <div class="row">
                <div class="col-md-12">
                    <pre id="caseInput"><?php echo $exerciseCase['ExerciseCase']['input']; ?></pre>
                    <button class="btn btn-info" id="btnCopyClipboardInput" data-clipboard-target="inputField"><?php echo __("Copy to Clipboard"); ?></button>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="output">
            <div class="row">
                <div class="col-md-6">
                    <p><?php echo __("Expected Output"); ?></p>
                    <pre><?php echo $exerciseCase['ExerciseCase']['output']; ?></pre>
                    <button class="btn btn-info" id="btnCopyClipboardOutput" data-clipboard-target="expectedOutputField"><?php echo __("Copy to Clipboard"); ?></button>
                </div>
                <div class="col-md-6">
                    <p><?php echo __("User Output"); ?></p>
                    <pre id="outputDiff"><?php echo $commitsExerciseCase['CommitsExerciseCase']['output']; ?></pre>
                    <button class="btn btn-info" id="btnCopyClipboardUserOutput" data-clipboard-target="userOutputField"><?php echo __("Copy to Clipboard"); ?></button>

                </div>
            </div>
        </div>
        <div class="tab-pane" id="error">
            <div class="row">
                <div class="col-md-12">
                    <pre><?php echo $commitsExerciseCase['CommitsExerciseCase']['error']; ?></pre>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
echo $this->Form->input('input',array('label' => false,'type' => 'hidden', 'id' => 'inputField', 'value' => $exerciseCase['ExerciseCase']['input']));
echo $this->Form->input('expected_output',array('label' => false,'type' => 'hidden', 'id' => 'expectedOutputField', 'value' => $exerciseCase['ExerciseCase']['output']));
echo $this->Form->input('user_output',array('label' => false,'type' => 'hidden', 'id' => 'userOutputField', 'value' => $commitsExerciseCase['CommitsExerciseCase']['output']));
//echo $this->Html->script('libs/jsdiff');
?>
<script>
    $(function () {
        <?php if (1 == 2 && $exerciseCase['ExerciseCase']['show_expected_output'] && $exerciseCase['ExerciseCase']['show_user_output']): ?>
        $("#outputDiff").html(diffString($("#userOutputField").val(),$("#expectedOutputField").val()));
        <?php endif; ?>
        $('#commitTabs a').click(function (e) {
            e.preventDefault()
            $(this).tab('show')
        });

        var clientInput = new ZeroClipboard( document.getElementById("btnCopyClipboardInput") );
        clientInput.on('copy', function(event) {
            var text = document.getElementById('inputField').value;
            var windowsText = text.replace(/\n/g, '\r\n');
            event.clipboardData.setData('text/plain', windowsText);
            $("#btnCopyClipboardInput").html("<?php echo __("Copied"); ?>!");
            setInterval(function () {
                $("#btnCopyClipboardInput").html("<?php echo __("Copy to Clipboard"); ?>");
            }, 1000);
        });

        var clientOutput = new ZeroClipboard( document.getElementById("btnCopyClipboardOutput") );
        clientOutput.on('copy', function(event) {
            var text = document.getElementById('expectedOutputField').value;
            var windowsText = text.replace(/\n/g, '\r\n');
            event.clipboardData.setData('text/plain', windowsText);
            $("#btnCopyClipboardOutput").html("<?php echo __("Copied"); ?>!");
            setInterval(function () {
                $("#btnCopyClipboardOutput").html("<?php echo __("Copy to Clipboard"); ?>");
            }, 1000);
        });

        var clientUserOutput = new ZeroClipboard( document.getElementById("btnCopyClipboardUserOutput") );
        clientUserOutput.on('copy', function(event) {
            var text = document.getElementById('userOutputField').value;
            var windowsText = text.replace(/\n/g, '\r\n');
            event.clipboardData.setData('text/plain', windowsText);
            $("#btnCopyClipboardUserOutput").html("<?php echo __("Copied"); ?>!");
            setInterval(function () {
                $("#btnCopyClipboardUserOutput").html("<?php echo __("Copy to Clipboard"); ?>");
            }, 1000);
        });
    })
</script>