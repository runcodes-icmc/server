<?php

class DATABASE_CONFIG
{
  public $default;

	public function __construct()
	{
    $loader = new ConfigLoader();

		$this->default = [
			'datasource' => 'Database/Postgres',
			'persistent' => true,

			'host' => $loader->configs['RUNCODES_DB_HOST'],
			'port' => $loader->configs['RUNCODES_DB_PORT'],
			'login' => $loader->configs['RUNCODES_DB_USERNAME'],
			'password' => $loader->configs['RUNCODES_DB_PASSWORD'],
			'database' => $loader->configs['RUNCODES_DB_DATABASE'],
			'schema' => $loader->configs['RUNCODES_DB_SCHEMA'],
    ];
	}
}
