# php2shell mitigation for PHP 5.6

**Status: applied 2026-10-23.** The `app` image was rebuilt with a patched
PHP 5.6.40 and the running container was recreated on it. This is a
stopgap for an EOL runtime, not a supported end state.

## Why

[CVE-2026-109618](https://github.com/php/php-src/security/advisories/GHSA-w8hw-w9fx-j79w)
(critical, "RCE via OOM corruption") and
[CVE-2026-109619](https://github.com/php/php-src/security/advisories/GHSA-6wmf-4rv5-p3rp)
(critical, "RCE via stale global state") were fixed by the PHP project on
2026-10-21 for 8.2–8.5 and backported to 7.4/8.0/8.1 only. PHP 5.6 is EOL and
will not receive a fix.

The application ran `php:5.6-fpm` (PHP 5.6.40) with
`session.upload_progress.enabled = On` (the default), which is the consumer
side of CVE-2026-109619. Nothing in this codebase uses upload progress.

## What changed

| File | Change |
|---|---|
| `config/php/patches/php-5.6-community-backport.patch` | Vendored minimal backport, pinned to `Anton-Barinov/php2shell-patches@41fa62f` |
| `Dockerfile` | Installs build deps and recompiles the bundled 5.6.40 source with the patch, using the same `configure` flags as the official image |
| `config/php/fpm-settings.conf` | `php_admin_flag[session.upload_progress.enabled] = off` (defence in depth; the patch already forces it off in code) |
| `.dockerignore` | Keeps `.git`, `.scratch`, `exec` and `src/vendor` out of the build context |

The patch mirrors the official EOL mitigation:

1. OOM now prints the fatal error and `_exit(1)`s instead of
   `longjmp()`ing through partially-initialised request state.
2. `php_session_rfc1867_callback()` returns early, so upload progress is
   never recorded.
3. Only `Content-Disposition`/`Content-Type` multipart headers are retained.
4. `PG(http_globals)` entries are cleared before request startup.

See [`config/php/patches/README.md`](../config/php/patches/README.md) for
provenance and the residual-risk discussion.

## Verification

All of this was run against the built image and the live container:

| Check | Before (unpatched) | After (patched) |
|---|---|---|
| `php -v` | 5.6.40, built 2019-01-23 | 5.6.40, built 2026-10-23 |
| `php -m` | — | byte-identical module list (redis 4.3.0, timezonedb, gd, intl, mcrypt, pdo_pgsql, zip, curl, openssl) |
| `php-fpm -t` | — | configuration test successful |
| OOM (`memory_limit=16M`, 64 MB alloc) | exit **255** after bailout | exit **1** (hard `_exit`) |
| Upload progress, forced `enabled=1` | `$_SESSION` = `upload_progress_probe,marker` | `$_SESSION` = `marker` only |
| 4 MB multipart upload | parsed | parsed (`size=4194304`, `is_uploaded_file()=yes`) |
| Normal form POST / sessions | works | works |
| Live `GET /` | 200, 8918 bytes | 200, 8918 bytes |
| Live `GET /login` | 200 | 200 |
| FPM logs | — | no warnings, no unloadable modules |

The upload-progress test explicitly set `session.upload_progress.enabled=1`
on the command line, so the missing `upload_progress_probe` key proves the
code-level disable, not just the INI flag.

## Rollback

Images kept on the host:

| Tag | Image | Meaning |
|---|---|---|
| `server-app:latest`, `server-app:php2shell-20261023` | `4109a9e10446` | patched |
| `server-app:pre-php2shell-20261023` | `8f28948ae8ab` | pre-patch |

To roll back:

```bash
docker tag server-app:pre-php2shell-20261023 server-app:latest
docker compose -f docker-compose.full.yml up -d --no-deps app
```

To re-apply:

```bash
docker tag server-app:php2shell-20261023 server-app:latest
docker compose -f docker-compose.full.yml up -d --no-deps app
```

Or rebuild from source: `docker compose -f docker-compose.full.yml build app`.
The build fails closed if the patch does not apply or the patched symbol is
missing from the installed headers.

## Residual risk

* **Partial parity.** The official 7.4/8.0/8.1 commit also hardens
  `zend_vm_*.h`, `zend_hash.c`, `zend_execute_API.c`, `main/SAPI.c`,
  `main/php_variables.c` and `zend_llist.c` against other bailout paths.
  Those hunks do not map onto 5.6 and are not included. The two published
  chains are closed; alternate ones may not be. Do not treat this as
  equivalent to a supported PHP release.
* **Upload progress is unavailable.** Nothing here uses it.
* **PHP 5.6 remains EOL.** No future security fixes. Migration is the actual
  remediation; this only buys time.

## If compromise is suspected

The advisory recommends checking for shells and unexpected child processes:

```bash
# web workers spawning shells / recon tools
ps -eo pid,ppid,user,args | grep -E 'php-fpm|www-data' | grep -E '/bin/(ba)?sh|python|curl|wget|nc '
# recently dropped PHP files
find /var/www /tmp /var/tmp /dev/shm -type f \( -name '*.php' -o -name '*.phtml' \) \
  -newer /etc/hostname -size -200k 2>/dev/null
```

Then preserve logs, rotate DB/panel/SMTP credentials and rebuild from a
known-good image. Note that the app container runs as UID 1000 (`www-data`)
and has network access to the database, Redis and SeaweedFS.
