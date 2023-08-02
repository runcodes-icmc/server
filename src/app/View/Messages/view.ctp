<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("View Message"); ?></h3>
                </div>
                <div class="panel-body">
                    <p>
                        <?php echo __("Sent to").": ".$mail['MailLog']['sent_to']; ?><br>
                        <?php echo __("Opened").": ".$mail['MailLog']['opened']; ?><br>
                        <?php if ($mail['MailLog']['opened'] > 0) echo __("First Time Opened").": ".$this->Time->format('d/m/Y H:i:s',h($mail['MailLog']['first_opened_time'])); ?><br>
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
                    <h3 class="panel-title"><?php echo __("Message"); ?></h3>
                </div>
                <div class="panel-body">
                    <p>
                        <?php echo strip_tags($mail['MailLog']['message'],"<table><tr><td><p><strong><a><tbody>"); ?><br>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>