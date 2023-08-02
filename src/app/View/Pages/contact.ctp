<?php 
if (strlen(trim($msgSuccess)) > 0) {
    ?>
    <div class="alert alert-success">
    <?php echo $msgSuccess; ?>
    </div>
<?php } ?>

<?php 
if (strlen(trim($msgError)) > 0) {
    ?>
    <div class="alert alert-danger">
    <?php echo $msgError; ?>
    </div>
<?php } ?>