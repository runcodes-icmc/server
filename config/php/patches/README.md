# PHP 5.6 — php2shell mitigation patch

Vendored copy of the **minimal** community backport for
[CVE-2026-109618](https://github.com/php/php-src/security/advisories/GHSA-w8hw-w9fx-j79w)
(RCE via OOM corruption) and
[CVE-2026-109619](https://github.com/php/php-src/security/advisories/GHSA-6wmf-4rv5-p3rp)
(RCE via stale global state).

| | |
|---|---|
| File | `php-5.6-community-backport.patch` |
| SHA-256 | `abcb401aef115604f5ccedce32581872e6a993069ae74eae255f0706faea026c` |
| Upstream repo | https://github.com/Anton-Barinov/php2shell-patches |
| Pinned commit | `41fa62fcc3a2890cbeba973b71cb7dc5d9b170b7` (2026-10-23) |
| Applies to | vanilla `php-5.6.40` (official php.net tarball, sha256 `1369a51eee3995d7fbd1c5342e5cc917760e276d561595b6052b21ace2656d1c`) |
| Licence | PHP License 3.01 (derived from php-src) |

## Why this flavour

The official PHP fix ships for 7.4/8.0/8.1 only (branches
`PHP-*-CVE-2026-109618-109619`); PHP 5.6 is EOL. The official EOL mitigation
is small and is what this backport mirrors:

1. **`Zend/zend_alloc.c`** — on out-of-memory, print the fatal error and
   `_exit(1)` instead of `zend_error_noreturn()`/`zend_bailout()`. The worker
   dies before partially-initialised request state can be reused
   (CVE-2026-109618).
2. **`ext/session/session.c`** — force-disable session upload progress
   (`if (!PS(rfc1867_enabled) || 1)`), closing the `upload_progress` UAF
   (CVE-2026-109619). This is the same blunt disable the official 7.4/8.0/8.1
   backport uses.
3. **`main/rfc1867.c`** — retain only `Content-Disposition` / `Content-Type`
   multipart headers, so header flooding cannot exhaust `memory_limit`.
4. **`main/main.c`** — clear `PG(http_globals)` entries that may be read
   before `php_request_startup()`.

We chose this over the repo's `php-5.6-enhanced-universal.patch` because the
application does not use `session.upload_progress` (no reference in `src/` or
`config/`), and the minimal patch is a 5-file change instead of a 14-file
change to serializer internals. Fewer moving parts on an EOL runtime.

`config/php/fpm-settings.conf` also sets
`php_admin_flag[session.upload_progress.enabled] = off` as defence in depth:
the build forces it off, the INI keeps the intent visible and still disables
it if the binary is ever replaced by an unpatched one.

## Residual risk (read before relying on this)

* **Not full parity with the official EOL commit.** The official 7.4/8.0/8.1
  fix also hardens `zend_vm_*.h`, `zend_hash.c`, `zend_execute_API.c`,
  `main/SAPI.c`, `main/php_variables.c` and `zend_llist.c` against other
  bailout paths. Those hunks are not portable to 5.6 as-is, so they are not
  included here. The main disclosed chains are closed, alternate ones may not
  be.
* **PHP 5.6 is EOL.** This buys time; it is not a supported configuration.
  Migrate to a current PHP release.
* Upload progress is unavailable after this change. Nothing in this
  application uses it.

## Rebuilding / updating

The patch is applied in the `php:5.6-fpm` build stage, which recompiles
5.6.40 with the same `configure` flags as the official image (see
`Dockerfile`, "php2shell" step). To update the patch:

```bash
git clone https://github.com/Anton-Barinov/php2shell-patches
git -C php2shell-patches checkout <new-commit>
cp php2shell-patches/patches/php-5.6-community-backport.patch .
sha256sum php-5.6-community-backport.patch   # update the table above
```

Then rebuild and re-run the verification in
[docs/php2shell.md](../../../docs/php2shell.md).
