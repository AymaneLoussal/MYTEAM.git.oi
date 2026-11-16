# FTMBALL Production Readiness

Audit date: 2026-11-16
Branch: `fix/ftmball-audit`
Verdict: **CONDITIONALLY READY**

FTMBALL's application, dependency, container, persistence, proxy, backup, SMTP transport, and PostgreSQL migration blockers have been addressed and verified locally. Public release still requires host-specific HTTPS/proxy values, secret injection, scheduled off-host backups, monitoring, and one authorized end-to-end email check. No deployment, live-data migration, secret change, or real email occurred.

## Confirmed working

- Composer production audit: zero advisories after the Laravel 13.42.0 security update.
- Laravel: 56 tests, 337 assertions passed.
- Security audit: 20 tests, 55 assertions passed.
- Blade compilation, Vite production build (62 modules), route discovery, and Compose validation passed.
- Docker image rebuilt successfully with PHP 8.4, Nginx, PHP-FPM, SQLite, and PostgreSQL PDO support.
- Recreated application container is healthy; `/up` returned HTTP 200. Existing named SQLite and upload volumes were retained.
- SQLite backup was made with `VACUUM INTO` outside the application volume. An isolated copied restore passed `PRAGMA integrity_check` with 20 tables and 18 migrations.
- Gmail connectivity from Docker passed DNS, TCP 587, SMTP greeting/EHLO, STARTTLS, and certificate validation. No credentials were used and no mail was sent.
- A fresh PostgreSQL 17 database ran all 18 migrations. Rolled-back integrity probes confirmed one team group per team, one private chat per team/player pair, and rejection of private chats without `private_player_id`.
- A disposable production-mode proxy check honored `X-Forwarded-Proto: https`; session and XSRF cookies were Secure. HSTS remains intentionally disabled.
- Chat, invitation, formation, team membership, CSRF, rate-limiting, authorization, and avatar-upload regression tests pass.

## Changes made in this readiness pass

- Added explicit `TRUSTED_PROXIES` support in `bootstrap/app.php`. Blank configuration preserves local HTTP behavior; production accepts comma-separated proxy IPs/CIDRs.
- Added `TRUSTED_PROXIES` and `RUN_MIGRATIONS` examples. Production defaults automatic migrations off and enables encrypted, database-backed secure sessions.
- Changed the entrypoint so `php artisan migrate --force` runs only when `RUN_MIGRATIONS=true`.
- Added `pdo_pgsql` to the runtime image.
- Added `docker/sqlite-backup.php` and `docker/backup-sqlite.ps1` for consistent backup plus isolated restore verification.
- Added `docker/smtp-probe.php` for credential-free TCP/STARTTLS checks.
- Added `docker/postgres-integrity-check.sql` for transactional PostgreSQL chat constraint verification.
- Excluded `/backups` from Git and documented deployment/recovery procedures.

## Security assessment

### Confirmed controls

- Authentication regenerates sessions; logout invalidates the session and rotates CSRF state.
- Web state changes use CSRF-protected routes. Login, registration, invitation, resend, and chat operations are rate limited.
- Invitation tokens are random, expiring, hidden from serialization, account-bound, and reused safely for resend after transport failure.
- Chat read, send, polling, and incremental endpoints authorize the conversation. Former members lose team-chat/private-chat access.
- Messages use escaped Blade output and bounded validation.
- Formation and team-resource writes enforce coach/team ownership and player membership.
- Avatar uploads enforce image type/size validation and persist outside the image.
- No tracked `.env`, application key, Gmail password, SQLite database, or user upload was found in the earlier tracked-file scan.

### Remaining application improvements

- Password rules still allow six characters; adopt Laravel's reviewed `Password` policy consistently before open public registration.
- Password reset, verified-email enforcement, account recovery, and optional MFA are not complete public-account lifecycle controls.
- Add browser E2E coverage for drag/drop formations, invitation acceptance, chat isolation, avatar upload, and responsive layouts.
- Add CSP after final asset origins are known. Do not add HSTS until the public domain is reliably HTTPS-only.

## HTTPS, proxy, and sessions

Production must set the exact HTTPS `APP_URL`, `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, and the actual trusted proxy IP/CIDR list. Wildcard proxy trust is safe only when the application cannot be reached except through the platform proxy. The local HTTP environment leaves `TRUSTED_PROXIES` blank.

The disposable proxy test returned HTTP 200 with Secure session/XSRF cookies when forwarded as HTTPS. HSTS was absent as required. The final host must still be checked for correct forwarded client IP, scheme, host, invitation URL, and redirect behavior.

## Database and recovery recommendation

### SQLite

SQLite is acceptable for an initial **single-instance** deployment with reliable persistent block storage and modest write concurrency. It must not be shared by multiple application replicas. Writes serialize and may become a constraint as chat activity grows.

The verified backup is `backups/ftmball-20261116-121116.sqlite`, SHA-256 `9563D28CC6312C00BB647A65260422D2AEF5BC9C67340BA67258EABE93C2E98E`. It is ignored by Git and outside the application volume. This local artifact is not a production retention solution: schedule encrypted off-host copies, monitor failures/capacity, back up uploads at the same recovery point, and rehearse complete application restoration.

### PostgreSQL

Managed PostgreSQL is recommended for multiple replicas, higher write concurrency, or stronger managed backup/high-availability requirements. The runtime driver, fresh migration history, partial indexes, and trigger constraints are now verified against PostgreSQL 17. Existing SQLite data has not been migrated. Any conversion requires an approved mapping, rehearsal, reconciliation report, cutover window, and rollback plan.

## SMTP status

Docker can reach Gmail on port 587 and validate STARTTLS. This proves network transport only. It does not prove authentication, SMTP acceptance, inbox placement, or invitation-link correctness. Production must source Gmail values from the host secret manager and later send one invitation only to an explicitly authorized recipient. A queued mail worker and retry/monitoring policy are recommended before meaningful traffic.

## Verification evidence

| Check | Result |
|---|---|
| `composer validate` | PASS |
| `composer audit --no-dev` | PASS - zero advisories |
| `php artisan test` | PASS - 56 tests, 337 assertions |
| `audit/run.ps1` | PASS - 20 tests, 55 assertions |
| `php artisan view:cache` | PASS |
| `npm run build` | PASS - 62 modules |
| `docker compose config --quiet` | PASS |
| `docker compose build` | PASS |
| Docker runtime | PASS - healthy, `/up` HTTP 200 |
| SQLite backup/isolated restore | PASS - integrity OK, 20 tables, 18 migrations |
| Gmail TCP/STARTTLS from Docker | PASS - no auth/message attempted |
| PostgreSQL 17 fresh migrations | PASS - 18 migrations |
| PostgreSQL chat integrity | PASS - uniqueness and trigger checks, test transaction rolled back |
| HTTPS forwarded scheme/secure cookies | PASS in disposable proxy simulation |
| Real host HTTPS and authorized email | NOT RUN - deployment-specific |

## Git and release readiness

The branch has a large dirty worktree spanning prior chat, formation, invitation, UI, dependency, Docker, test, migration, and report work. Preserve it and review files deliberately; do not stage with `git add .` blindly. Before publication:

1. Remove the tracked generated `results.xml` from the repository index if still present (without deleting any needed local evidence).
2. Run a full Git-history secret scan; rotate any credential ever committed.
3. Confirm `.env`, `.env.docker`, databases, backups, uploads, logs, and SMTP diagnostics are untracked/ignored.
4. Review the staged diff and use coherent commits only after explicit approval.

## Hosting requirements

- Docker-compatible Linux hosting with managed TLS termination and documented proxy IP ranges/headers.
- Secret manager for stable `APP_KEY`, SMTP credentials, and database credentials.
- One instance plus persistent SQLite block storage, or managed PostgreSQL for scaling.
- Persistent uploads, automated encrypted off-host backups, retention, and restore alerts.
- Central logs, health/uptime monitoring, disk/capacity alerts, and rollback capability.
- Outbound Gmail SMTP 587 or an approved HTTPS mail provider if the host blocks SMTP.
- A supervised queue worker if mail is moved off synchronous requests.

## Deployment checklist

1. Select the host/database architecture and provision persistent storage.
2. Inject production environment values; never generate a new key for existing encrypted data.
3. Configure exact trusted proxy addresses and HTTPS domain; leave HSTS off initially.
4. Take and verify a pre-release database/upload backup.
5. Run `php artisan migrate --force` as an explicit release step; do not enable startup migrations.
6. Start the application and verify health, logs, secure cookies, client IP, URL generation, and no debug output.
7. Smoke-test authentication, teams, invitations, chat authorization, formations, matches, training, and avatar persistence.
8. Send one explicitly authorized invitation; confirm SMTP acceptance, inbox receipt, HTTPS link, and acceptance.
9. Verify monitoring, backup schedule, isolated restore, and rollback procedure.
10. Only after stable HTTPS verification, decide whether to enable HSTS.

## Final verdict

**CONDITIONALLY READY.** The repository-level production blockers are closed with evidence. Deployment is now gated by configuration and operational controls that can only be validated on the selected host: real proxy addresses and HTTPS endpoint, secret injection, scheduled off-host backups/monitoring, and one authorized end-to-end email workflow. FTMBALL is not yet declared fully READY because those deployment-specific checks have not occurred.
