# FTMBALL Docker Build Repair Report

## Build errors repaired

- `Dockerfile:4` installs Composer production dependencies with `--no-scripts` while only `composer.json` and `composer.lock` are present. This keeps the dependency layer cacheable and prevents `artisan package:discover` from running before `artisan` exists.
- `Dockerfile:5-6` copies the full Laravel application, then rebuilds the authoritative production autoloader. Composer's normal `post-autoload-dump` scripts run at this point, and the verified build completed Laravel package discovery successfully.
- `Dockerfile:17-18` installs `libsqlite3-dev` before compiling `pdo_sqlite`; `libsqlite3-0` remains present at runtime. The verified build compiled both `pdo_sqlite` and OPcache.

## Supporting runtime checks

- `Dockerfile:26` installs `docker/php/www.conf`. PHP-FPM workers run as the non-root `app` user and listen only on `127.0.0.1:9000` for the co-located Nginx process.
- The host `public/storage` path is a Windows junction with an absolute Windows target. The earlier image copied it as a Linux symlink to `C:/Users/.../storage/app/public`, where it was broken. POSIX `test -e` consequently returned false even though the link pathname existed, and `ln` terminated startup with `File exists`.
- `.dockerignore` now excludes `public/storage`, so host links and junctions never enter Linux images.
- `docker/entrypoint.sh` now treats links and real directories separately. It leaves either supported correct symlink target unchanged, replaces only a broken/incorrect symlink, creates a missing link, and only warns when a real file or directory exists. Replacing a link never touches the persistent upload target.
- `Dockerfile:32` and `compose.yaml:12` use a simple HTTP request to Laravel's `/up` route. The Compose command contains no shell variable requiring Compose interpolation.
- `.dockerignore:21` excludes the local SMTP diagnostic script. `.gitignore:6` excludes `.env.docker`.

## Persistent data and secrets

Compose uses two named volumes:

- `ftmball_sqlite` at `/app/database`
- `ftmball_uploads` at `/app/storage/app/public`

The host SQLite database, `.env`, `.env.docker`, backups, user uploads, credentials, and existing compiled output are excluded from the build context. Startup only runs normal `php artisan migrate --force` against the isolated Docker SQLite file; it does not use `migrate:fresh`, `db:wipe`, seeders, or volume deletion.

## Verification performed on 2026-11-16

- `docker compose config --quiet`: passed.
- `docker compose build`: passed; image `my-team-app:latest` was created.
- Image extension check: passed for `pdo_sqlite` and OPcache.
- Image artifact check: passed for `/app/artisan`, `/app/vendor/autoload.php`, and `/app/public/build/manifest.json`.
- `composer validate`: passed.
- PHP syntax checks for modified and untracked PHP files: passed.
- `php artisan test`: **56 passed, 329 assertions**.
- `powershell -ExecutionPolicy Bypass -File audit/run.ps1`: **20 passed, 55 assertions**.
- `php artisan view:cache`: passed.
- `npm.cmd run build`: passed; 62 modules transformed and production assets generated.
- Rebuild after storage-link repair: passed.
- Container startup and isolated SQLite migrations: passed.
- Docker health after startup: **healthy**.
- `GET http://localhost:8080/up`: HTTP 200.
- Storage link inside the container: `../storage/app/public` (verified).
- Restart persistence: passed. The SQLite inode and size were unchanged across `docker compose restart app` (`106770:200704` before and after), all migrations remained applied, and health returned to **healthy**.

The isolated Docker database was initialized through normal migrations. The host SQLite database was not mounted or modified. The existing named volumes were preserved; no volume deletion or database reset command was run. Browser workflows such as login, teams, chat, formations, invitations, and uploads still require manual functional verification.

## PowerShell runtime verification

Run from the project directory in normal Windows PowerShell. These commands use the actual Compose service name, `app`, and the configured default host port, `8080`.

```powershell
if (-not (Test-Path .env.docker)) { Copy-Item .env.docker.example .env.docker -ErrorAction Stop }
$dockerAppKey = php artisan key:generate --show
(Get-Content .env.docker) -replace '^APP_KEY=.*$', "APP_KEY=$dockerAppKey" | Set-Content .env.docker
Remove-Variable dockerAppKey

docker compose config --quiet
docker compose build
docker compose up -d
docker compose ps
docker compose exec app php artisan migrate:status
Invoke-WebRequest http://localhost:8080/up -UseBasicParsing

docker compose restart app
docker compose ps
docker compose exec app php artisan migrate:status
Invoke-WebRequest http://localhost:8080/up -UseBasicParsing

docker compose logs --tail 100 app
docker compose down
```

`docker compose down` preserves both named volumes. Do not add `-v` unless intentionally deleting the isolated Docker database and uploads.

## Remaining production work

- Complete the runtime verification above and manually test authentication, teams, invitations with a non-real mailer, chat, formations, uploads, and persistence.
- Keep `MAIL_MAILER=log` during verification. Configure Gmail values only through deployment secrets when real delivery is explicitly authorized.
- Public hosting still requires HTTPS termination, trusted proxy configuration, backups and restore testing, secure session cookies, production secret management, and a reviewed database strategy.

## Avatar upload repair (2026-11-16)

The Docker upload volume was not a permissions failure: `/app/storage/app/public` was owned by `app:app`, mode `0755`, writable by the PHP-FPM `app` user, and `/tmp` was mode `1777`. The public disk targeted that directory and `public/storage` resolved correctly. The failure text came from PHP marking the upload invalid before Laravel's validator could apply its intended 2 MB limit. Docker had an 8 MB PHP upload ceiling and an equal 8 MB Nginx request ceiling, creating a configuration-layer failure for larger browser images instead of Laravel's clear validation response.

Changes:

- PHP parsing headroom is now 12 MB per uploaded file and 14 MB per request.
- Nginx accepts request bodies up to 16 MB.
- Laravel continues to enforce the security/business limit of 2 MB and JPEG, PNG, or GIF content.
- Upload parsing and size failures now return clear avatar-specific messages.
- The profile form documents the 2 MB/type policy and renders avatar validation errors next to the input.
- Regression coverage now checks valid upload, persisted model path, public URL path, invalid file rejection, oversized file rejection, and preservation of the last valid avatar after rejected replacements.

Verification:

- Compose validation and Docker build passed.
- Targeted avatar test passed: 1 test, 13 assertions.
- Full Laravel suite passed: 56 tests, 337 assertions.
- Effective container limits were verified as `upload_max_filesize=12M` and `post_max_size=14M`.
- Laravel's public disk wrote a PNG as the non-root `app` user; Nginx served it as `image/png` with HTTP 200.
- The same uploaded verification file remained available with HTTP 200 after `docker compose restart app`.
- The temporary verification files were removed afterward; existing uploads were untouched.
- Docker returned to `healthy` after restart.

A final manual Account-page upload of a JPEG, PNG, or GIF no larger than 2 MB is still recommended to verify the browser interaction and preview with the user's chosen file.
