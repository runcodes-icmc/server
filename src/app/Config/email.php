<?php

class EmailConfig
{

  public $default;

  public $env = [
    'transport' => 'Smtp',
    'timeout' => 120,
    'client' => null,
    'log' => false,
    'emailFormat' => 'html',
  ];

  public $debug = [
    'transport' => 'Debug',
    'from' => 'no-reply@example.com',
    'log' => true,
    'emailFormat' => 'html',
  ];

  public function __construct()
  {
    // Load env configs
    $loader = new ConfigLoader();

    // Build env based config
    $this->env['from'] = [
      $loader->configs['RUNCODES_EMAIL_SENDER_ADDRESS'] => $loader->configs['RUNCODES_EMAIL_SENDER_NAME']
    ];
    $this->env['host'] = $loader->configs['RUNCODES_EMAIL_SMTP_HOST'];
    $this->env['port'] = $loader->configs['RUNCODES_EMAIL_SMTP_PORT'];
    $this->env['username'] = $loader->configs['RUNCODES_EMAIL_SMTP_USER'];
    $this->env['password'] = $loader->configs['RUNCODES_EMAIL_SMTP_PASSWORD'];


    // Select default configs
    $profile = $loader->configs['RUNCODES_EMAIL_PROFILE'];
    $this->default = $this->{$profile};
  }
}
