<?php

/*********************
 * Composer Autoload *
 *********************/

require ROOT . DS . 'vendor/autoload.php';
spl_autoload_unregister(array('App', 'load'));
spl_autoload_register(array('App', 'load'), true, true);

/************
 * Timezone *
 ************/
if (!ini_get('date.timezone')) {
  date_default_timezone_set('UTC');
}

/*******************
 * Dynamic Configs *
 *******************/
// Environment loader (with default values)
$loader = new ConfigLoader();

// Contact Info
Configure::write('Config.contactEmail', $loader->configs['RUNCODES_CONTACT_EMAIL']);

/**************
 * S3 Configs *
 **************/

Configure::write('Aws', new Aws\Sdk([
  'version' => 'latest',
  'use_path_style_endpoint' => true,

  'region' => $loader->configs['RUNCODES_S3_REGION'],
  'endpoint' => $loader->configs['RUNCODES_S3_ENDPOINT'],
  'credentials' => [
    'key'    => $loader->configs['RUNCODES_S3_CREDENTIALS_KEY'],
    'secret' => $loader->configs['RUNCODES_S3_CREDENTIALS_SECRET'],
  ],
]));

$s3Prefix = $loader->configs['RUNCODES_S3_BUCKET_PREFIX'];
Configure::write('AWS.commits-bucket-name',  $s3Prefix . '-commits');
Configure::write('AWS.outputfiles-bucket-name', $s3Prefix . '-outputfiles');
Configure::write('AWS.files-bucket-name', $s3Prefix . '-files');
Configure::write('AWS.cases-bucket-name', $s3Prefix . '-cases');
Configure::write('AWS.download-bucket-name', $s3Prefix . '-downloads');

/*****************
 * Cache Configs *
 *****************/

$redisHost = $loader->configs['RUNCODES_REDIS_HOST'];
$redisPort = $loader->configs['RUNCODES_REDIS_PORT'];

Cache::config('default', [
  'engine' => 'Redis',
  'duration' => '+7 days',
  'probability' => 100,
  'prefix' => 'runcodes_php_cache_',
  'server' => $redisHost,
  'port' => $redisPort,
  'persistent' => true,
]);

Cache::config('session', [
  'engine' => 'Redis',
  'duration' => '+1 days',
  'probability' => 100,
  'prefix' => 'runcodes_php_session_',
  'server' => $redisHost,
  'port' => $redisPort,
  'persistent' => true,
]);

Cache::config('aws', [
  'engine' => 'Redis',
  'duration' => '+999 days',
  'probability' => 100,
  'prefix' => 'runcodes_php_aws_',
  'server' => $redisHost,
  'port' => $redisPort,
  'persistent' => true,
]);

Cache::config('long', array(
  'engine' => 'Redis',
  'duration' => '+999 days',
  'probability' => 100,
  'prefix' => 'runcodes_php_long_',
  'server' => $redisHost,
  'port' => $redisPort,
  'persistent' => true,
));

$cakeCacheDuration = Configure::read('debug') > 0 ? '+10 seconds' : '+999 days';

Cache::config('_cake_stats_', array(
  'engine' => 'Redis',
  'duration' => '+8 hours',
  'probability' => 100,
  'prefix' =>  'runcodes_php_cake_stats_',
  'server' => $redisHost,
  'port' => $redisPort,
  'persistent' => true,
));

Cache::config('_cake_core_', array(
  'engine' => 'Redis',
  'duration' => '+999 days',
  'probability' => 100,
  'prefix' => 'runcodes_php_cake_core_',
  'server' => $redisHost,
  'port' => $redisPort,
  'persistent' => true,
  'duration' => $cakeCacheDuration
));

Cache::config('_cake_model_', array(
  'engine' => 'Redis',
  'duration' => '+999 days',
  'probability' => 100,
  'prefix' => 'runcodes_php_cake_model_',
  'server' => $redisHost,
  'port' => $redisPort,
  'persistent' => true,
  'duration' => $cakeCacheDuration
));


/***************
 * Log Configs *
 ***************/

App::uses('CakeLog', 'Log');

CakeLog::config('default', [
  'engine' => 'ConsoleLog',
]);

App::uses('Log', 'Model');

/******************
 * Static Configs *
 ******************/

date_default_timezone_set('America/Sao_Paulo');

Configure::write('Error', array(
  'handler' => 'ErrorHandler::handleError',
  'level' => E_ALL & ~E_DEPRECATED,
  'trace' => true
));

Configure::write('Exception', array(
  'handler' => 'ErrorHandler::handleException',
  'renderer' => 'ExceptionRenderer',
  'log' => true
));


Configure::write('Dispatcher.filters', array(
  'AssetDispatcher',
  'CacheDispatcher'
));

Configure::write('Config.allowDemoPostRequest', false);
Configure::write('version', '2');
Configure::write('Config.maintenanceMode', false);
Configure::write('Upload.dir', '/archive');
