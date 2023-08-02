<?php

App::uses('AppModel', 'Model');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');


/**
 * Was AWS :p
 * Now it's Redis
 */
class AwsCache extends AppModel
{

  public $useTable = false; // This model does not use a database table
  public $cacheConfig = 'aws';

  public function __construct($id = false, $table = null, $ds = null)
  {
    parent::__construct($id, $table, $ds);
  }

  public function getItem($key, $consistentRead = false)
  {
    return Cache::read($key, $this->cacheConfig);
  }

  public function saveItem($key, $data)
  {
    return Cache::write($key, $data, $this->cacheConfig);
  }

  public function removeItem($key)
  {
    return Cache::delete($key, $this->cacheConfig);
  }
}
