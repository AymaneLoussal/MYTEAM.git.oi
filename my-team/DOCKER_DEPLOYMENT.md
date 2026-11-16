# FTMBALL Docker deployment

## Local startup

1. Copy `.env.docker.example` to the ignored `.env.docker` file.
2. Generate a local-only `APP_KEY`; never reuse a production key.
3. Run `docker compose config --quiet`, `docker compose build`, and `docker compose up -d`.
4. Check `docker compose ps` and `http://localhost:${APP_PORT:-8080}/up`.

Nginx and PHP-FPM serve the application. The `ftmball_sqlite` and `ftmball_uploads` named volumes persist the isolated Docker database and public uploads. The host `.env`, local SQLite files, backups, and uploads are excluded from the image. `docker compose down` preserves the volumes. Never use `docker compose down -v` on data that must be retained.

Local automatic migrations are opt-in with `RUN_MIGRATIONS=true`. Production must keep `RUN_MIGRATIONS=false` and run migrations explicitly after a verified backup:

```powershell
docker compose exec -T app php artisan migrate --force
```

## Production environment and HTTPS

Inject secrets through the hosting platform. Required settings include the stable existing `APP_KEY`, `APP_ENV=production`, `APP_DEBUG=false`, exact HTTPS `APP_URL`, database configuration, and SMTP credentials. Use `.env.production.example` only as a checklist.

Set `TRUSTED_PROXIES` to the documented IP addresses or CIDRs of the hosting reverse proxy. Do not use `*` unless network controls guarantee that requests can arrive only through that proxy. Forward `X-Forwarded-For`, `Host`, `Port`, and `Proto`; Laravel then generates HTTPS URLs and secure cookies correctly. Keep `SESSION_SECURE_COOKIE=true`. HSTS is deliberately not configured until the real domain is confirmed HTTPS-only.

Use database sessions for a single instance and a shared managed session/cache backend for multiple instances. Startup never rotates `APP_KEY` and production never migrates automatically.

## SQLite backup and isolated restore check

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File docker/backup-sqlite.ps1
```

The script uses SQLite `VACUUM INTO` for a consistent online snapshot, copies it to the ignored host `backups/` directory outside the application volume, computes SHA-256, and validates an isolated copied restore with `PRAGMA integrity_check`, expected tables, and migration records. It never replaces or writes to the live database. Move successful backups to encrypted off-host storage with retention and monitoring. Back up `ftmball_uploads` at the same recovery point.

## SMTP transport probe

This uses no credentials and sends no message:

```powershell
docker compose exec -T app php /app/docker/smtp-probe.php smtp.gmail.com 587
```

It checks DNS, TCP 587, SMTP greeting, EHLO, STARTTLS, and certificate validation. It does not prove SMTP authentication, provider acceptance, inbox delivery, or invitation-link correctness.

## Database strategy

SQLite is acceptable only for one application instance on reliable persistent block storage. Writes are serialized; chat growth and multiple replicas favor managed PostgreSQL. The runtime includes `pdo_pgsql`; the full migration history and chat partial unique indexes/triggers have been verified on PostgreSQL 17. Do not switch the current database or transfer user data without a separately approved and rehearsed migration plan.

## Operations

```powershell
docker compose logs --tail=200 app
docker compose restart app
docker compose ps
curl.exe -fsS http://localhost:8080/up
```

Public hosting still requires managed HTTPS, secret storage, automated off-host backups, monitoring/log aggregation, disk alerts, and a rollback plan.
