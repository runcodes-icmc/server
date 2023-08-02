<?php

App::uses('AppModel', 'Model');


class ServerWatch extends AppModel
{

  public $useTable = false; // This model does not use a database table

  public function __construct($id = false, $table = null, $ds = null)
  {
    parent::__construct($id, $table, $ds);
  }

  public function getCPUUtilization()
  {
    $ret = [];
    $ret["web"] = ":)";
    $ret["compiler"] = ":)";
    $ret["db"] = ":)";
    return $ret;
  }
}
