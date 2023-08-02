<?php
$this->layout = 'login2';
?>
<div class="login-box">
    <div class="row-fluid">
        <div class="col-md-12">
            <div class="page-error">
                <div class="row">
                    <div class="col-xs-2"><i class="fa fa-5x fa-times"></i> </div>
                    <div class="col-xs-10"><h1 class="error-code">404</h1></div>
                </div>
                <h1>Segmentation Fault</h1>
                <p>
                    <strong><?php echo __d('cake', 'Error'); ?></strong><br>
                    <?php printf( __d('cake', 'The requested address %s was not found on this server.'),"<strong>'{$url}'</strong>"); ?>
                </p>
                <a class="btn btn-lg text-center btn-linkedin" href="/"><?php echo __("Back to Home")."!"; ?></a>
                <?php
                if (Configure::read('debug') > 0 || $logged_user['type'] > 3):
                        echo $this->element('exception_stack_trace');
                endif;
                ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var _paq = [];
    _paq.push(['setDocumentTitle','404 ERROR']);
</script>