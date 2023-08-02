<?php
$domain = (new ConfigLoader())->configs['RUNCODES_DOMAIN'];
?>
<p>
  Hello, <?= $user_name ?>, <br>
  Thanks for registering on <a href="<?= $domain ?>/">run.codes</a> <br><br>
  Please, confirm your register in the following link: <br>
  <a href='<?= $domain ?>/Users/confirm/<?= $user_email ?>/<?= $user_hash ?>'><?= $domain ?>/Users/confirm/<?= $user_email ?>/<?= $user_hash ?></a>
</p>
