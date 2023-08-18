<?php


class ConfigLoader
{

  private $defaults = [
    'RUNCODES_PROFILE' => 'development',
    'RUNCODES_EMAIL_PROFILE' => 'env',
    'RUNCODES_EMAIL_SMTP_HOST' => 'smtp4dev',
    'RUNCODES_EMAIL_SMTP_PORT' => '25',
    'RUNCODES_EMAIL_SMTP_USER' => 'user',
    'RUNCODES_EMAIL_SMTP_PASSWORD' => 'password',
    'RUNCODES_EMAIL_SENDER_ADDRESS' => 'no-reply@run.codes',
    'RUNCODES_EMAIL_SENDER_NAME' => 'run.codes',
    'RUNCODES_S3_ENDPOINT' => 'http://seaweed:8333',
    'RUNCODES_S3_REGION' => 'sa-east-1',
    'RUNCODES_S3_CREDENTIALS_KEY' => '',
    'RUNCODES_S3_CREDENTIALS_SECRET' => '',
    'RUNCODES_S3_BUCKET_PREFIX' => 'runcodes',
    'RUNCODES_DB_HOST' => 'database',
    'RUNCODES_DB_PORT' => '5432',
    'RUNCODES_DB_USERNAME' => 'runcodes',
    'RUNCODES_DB_PASSWORD' => 'asdasd33',
    'RUNCODES_DB_DATABASE' => 'runcodes',
    'RUNCODES_DB_SCHEMA' => 'public',
    'RUNCODES_REDIS_HOST' => 'redis',
    'RUNCODES_REDIS_PORT' => '6379',
    'RUNCODES_CONTACT_EMAIL' => 'nowhere@nowhere',
    'RUNCODES_SECURITY_SALT' => '75581170ffc0cc5ae2d7c2823fe21d6a77bbefb90d141c2765d37ab24f7702dc',
    'RUNCODES_SECURITY_CIPHER_SEED' => '15146846548146846454685',
    'RUNCODES_DOMAIN' => 'https://run.codes',
  ];

  public $configs;


  public function get_or_default($key, $default)
  {
    $value = getenv($key);

    if ($value === false) {
      return $default;
    }

    return $value;
  }

  public function __construct()
  {
    foreach ($this->defaults as $key => $default) {
      $this->configs[$key] = $this->get_or_default($key, $default);
    }

    $this->configs["RUNCODES_S3_PUBLIC_ENDPOINT"] = $this->get_or_default(
      "RUNCODES_S3_PUBLIC_ENDPOINT",
      $this->configs["RUNCODES_S3_ENDPOINT"]
    );
  }
}
