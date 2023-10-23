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
echo $this->Form->input('input', array('label' => false, 'type' => 'hidden', 'id' => 'inputField', 'value' => $exerciseCase['ExerciseCase']['input']));
echo $this->Form->input('expected_output', array('label' => false, 'type' => 'hidden', 'id' => 'expectedOutputField', 'value' => $exerciseCase['ExerciseCase']['output']));
echo $this->Form->input('user_output', array('label' => false, 'type' => 'hidden', 'id' => 'userOutputField', 'value' => $commitsExerciseCase['CommitsExerciseCase']['output']));
?>
<script>
    const copiedStr = "<?php echo __("Copied"); ?>";
    const copyToClipboardStr = "<?php echo __("Copy to Clipboard"); ?>";

    function copyButtonRoutine(buttonId, copyFieldId) {
        const buttonEl = document.getElementById(buttonId);
        const hiddenInputEl = document.getElementById(copyFieldId);

        buttonEl.addEventListener('click', function(ev) {
            ev.preventDefault()

            navigator.clipboard.writeText(hiddenInputEl.value)
                .then(() => {
                    buttonEl.textContent = copiedStr;
                    setTimeout(() => {
                        buttonEl.textContent = copyToClipboardStr;
                    }, 1000);
                }).catch((e) => {
                    console.error("Failed to copy text to clipboard: ", e);
                });

        });
    }

    $(function() {
        <?php if (1 == 2 && $exerciseCase['ExerciseCase']['show_expected_output'] && $exerciseCase['ExerciseCase']['show_user_output']) : ?>
            $("#outputDiff").html(diffString($("#userOutputField").val(), $("#expectedOutputField").val()));
        <?php endif; ?>
        $('#commitTabs a').click(function(e) {
            e.preventDefault()
            $(this).tab('show')
        });

        // Copy text from user input field when copy button is clicked
        copyButtonRoutine("btnCopyClipboardInput", 'inputField');

        // Copy text from expected output field when copy button is clicked
        copyButtonRoutine("btnCopyClipboardOutput", 'expectedOutputField');

        // Copy text from user output field when copy button is clicked
        copyButtonRoutine("btnCopyClipboardUserOutput", 'userOutputField');
    })
</script>