<?php

require_once __DIR__ . '/loader.php';

$loader = new ConfigLoader();

// Configure debug level based on Application Profile
Configure::write('debug', preg_match('/^.*prod(?:uction|).*$/', $loader->configs['RUNCODES_PROFILE']) ? 0 : 1);

Configure::write('App.encoding', 'UTF-8');
Configure::write('Routing.prefixes', array('admin'));
Configure::write('Cache.check', true);

Configure::write('Acl.classname', 'DbAcl');
Configure::write('Acl.database', 'default');

// Use caching for session control
Configure::write('Session', [
  'defaults' => 'cache',
  'handler' => [
    'config' => 'session',
  ],
]);

// Security keys, please change those in production :)
Configure::write('Security.salt', $loader->configs['RUNCODES_SECURITY_SALT']);
Configure::write('Security.cipherSeed', $loader->configs['RUNCODES_SECURITY_CIPHER_SEED']);
