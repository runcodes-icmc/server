# Configuration

## Environment Variables

There are lots of environment variables to control the configurations of the service. To take a look at the defaults, check out the `/src/app/Config/loader.php` file.

### `RUNCODES_EMAIL_PROFILE`

Defines the currently active email profile.

Currently accepted values are:

- `env` (default)
  - Use environment variables to configure the e-mail SMTP settings.
- `debug`
  - Don't send e-mails, logging them instead.

### `RUNCODES_EMAIL_SMTP_HOST`

Defines the SMTP host for e-mail sending.

### `RUNCODES_EMAIL_SMTP_PORT`

Defines the SMTP port for e-mail sending.

### `RUNCODES_EMAIL_SMTP_USER`

Defines the SMTP user for e-mail sending.

### `RUNCODES_EMAIL_SMTP_PASSWORD`

Defines the SMTP password for e-mail sending.

### `RUNCODES_EMAIL_SENDER_ADDRESS`

Defines the e-mail address for sending e-mails.

### `RUNCODES_EMAIL_SENDER_NAME`

Defines the e-mail name for sending e-mails.

### `RUNCODES_S3_ENDPOINT`

Defines the S3 endpoint to be used for file storage.

### `RUNCODES_S3_REGION`

Defines the S3 region to be used for the file storage.

### `RUNCODES_S3_CREDENTIALS_KEY`

Defines the S3 credentials key field used for file storage.

### `RUNCODES_S3_CREDENTIALS_SECRET`

Defines the S3 credentials secret field used for file storage.

### `RUNCODES_S3_BUCKET_PREFIX`

Defines the prefixes to be used when creating S3 buckets.


### `RUNCODES_DB_HOST`

Defines the host of the PostgreSQL database to be used.

### `RUNCODES_DB_PORT`

Defines the port of the PostgreSQL database to be used.

### `RUNCODES_DB_USERNAME`

Defines the user login of the PostgreSQL database to be used.

### `RUNCODES_DB_PASSWORD`

Defines the user password of the PostgreSQL database to be used.

### `RUNCODES_DB_DATABASE`

Defines the database name of the PostgreSQL database to be used.

### `RUNCODES_DB_SCHEMA`

Defines the database schema of the PostgreSQL database to be used.

### `RUNCODES_REDIS_HOST`

Defines the Redis host to be used for cache.

### `RUNCODES_REDIS_PORT`

Defines the Redis port to be used for cache.

### `RUNCODES_CONTACT_EMAIL`

Defines the contact e-mail for run.codes.

### `RUNCODES_SECURITY_SALT`

Defines the password salt for hashing (shouldn't be fixed, but it's that way by design on CakePHP 2).


### `RUNCODES_SECURITY_CIPHER_SEED`

Configures the seed used for encrypting/decrypting strings.
