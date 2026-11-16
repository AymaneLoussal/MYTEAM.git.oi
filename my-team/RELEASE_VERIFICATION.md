# FTMBALL release verification

Branch: `fix/ftmball-audit`. The production asset build succeeded; browser workflows and SMTP transport remain to be verified. No deployment was performed.

## npm and Vite

The user reports successful `npm ping`, registry port 443 connectivity, `npm install` (0 vulnerabilities), and `npm run build` with Vite 7.3.8. The generated `package-lock.json` has lockfile version 3, its root dependencies match `package.json`, and it records Vite 7.3.8. The generated `public/build/manifest.json` exists; both referenced CSS and JavaScript files were confirmed on disk. The lockfile is staged for version control; build output is ignored by Git.

An earlier agent execution encountered `EACCES` contacting the registry. The successful user-run installation and build resolve that blocker. A fresh `npm ci` has not been independently run after lockfile generation; it can be verified later in a network-enabled terminal using `npm ci` followed by `npm run build`.

## Browser verification

The Laravel server started at `http://127.0.0.1:8765`. Both Chrome and Edge were launched separately with `--headless=new --disable-gpu --use-gl=angle --use-angle=swiftshader --disable-extensions --no-first-run`. Both repeatedly reported GPU process exit code `-1073741790` and ended with `FATAL ... GPU process isn't usable. Goodbye.` Access-denied and persistent-cache messages also appeared. The precise environmental cause is not established. No DOM was rendered and neither desktop nor mobile workflow verification passed.

Reproduce with a fresh, writable profile directory:

```powershell
php artisan serve --host=127.0.0.1 --port=8765
& 'C:\Program Files\Google\Chrome\Application\chrome.exe' --headless=new --disable-gpu --use-gl=angle --use-angle=swiftshader --disable-extensions --no-first-run --user-data-dir="$env:TEMP\ftmb-chrome-check" --dump-dom http://127.0.0.1:8765/login
```

Use a normal browser and a **disposable local database**, not production data. At desktop (1440×900) and mobile (390×844) widths:

1. Register a test coach, log in, and check dashboard navigation and sign-out.
2. Create a team; check team detail and a second test player's access restrictions.
3. Create, view, edit and delete a training and a match; assign and save a lineup.
4. Invite a test player using the local catcher below; follow the captured link, register or sign in and accept; confirm team membership and rejection/expired states.
5. As coach, create, view, edit and delete a formation with a roster player; exercise drag-and-drop and coordinate controls.
6. Create group and coach/player chats, send messages, confirm a player cannot post announcements, and confirm leaving revokes chat access.
7. Check profile edits and avatar upload. Inspect keyboard focus, readable text, clipped content, horizontal overflow, and navigation at both viewport widths. Record screenshots, browser console errors, and failing steps.

These steps are instructions, not completed results. Laravel feature tests exercise backend routes but do not establish browser rendering or responsiveness.

## Safe SMTP procedure

`config/mail.php` supports SMTP, log and array transports. The current local `.env` uses `MAIL_MAILER=log`, host `127.0.0.1`, port `2525`, and `MAIL_SCHEME=null`. Existing automated tests use the **array mailer**; they do not open an SMTP connection or prove delivery. No real SMTP transport test was completed here. Mailpit/MailHog binaries were not found; Docker reported that its engine pipe was unavailable.

For a local-only test with Docker and the trusted Mailpit image available, run:

```powershell
docker run --rm --name ftmb-mailpit -p 127.0.0.1:1025:1025 -p 127.0.0.1:8025:8025 axllent/mailpit
```

In a separate PowerShell window, after confirming no configuration cache and using a disposable local database, set process-local configuration before starting Laravel:

```powershell
$env:MAIL_MAILER='smtp'
$env:MAIL_HOST='127.0.0.1'
$env:MAIL_PORT='1025'
$env:MAIL_SCHEME='smtp'
$env:MAIL_URL=''
$env:MAIL_USERNAME=''
$env:MAIL_PASSWORD=''
$env:MAIL_FROM_ADDRESS='noreply@example.test'
$env:APP_URL='http://127.0.0.1:8000'
php artisan serve --host=127.0.0.1 --port=8000
```

Ensure the `smtp` mailer resolves to `127.0.0.1:1025` before sending; stop if it does not. Send an invitation to a reserved `example.test` address, then inspect `http://127.0.0.1:8025`: recipient, subject, team, expiry, link host/token and acceptance flow. The catcher must have no forwarding configured. Stop both processes afterward; process environment settings disappear when the terminal closes. Never use production credentials or actual recipients for this check.

## Automated checks

- `php artisan test --compact`: **42 passed, 219 assertions**.
- `powershell -NoProfile -ExecutionPolicy Bypass -File audit/run.ps1 -Php '<path-to-php.exe>'`: **20 passed, 55 assertions**.
- Both used the test suite's in-memory SQLite guard. No database reset was run.

The historical PostgreSQL credential should be rotated if real. Verify production secrets, HTTPS, `APP_DEBUG=false`, backups and deployment-specific mail configuration before release. Existing `resources/views/welcome.blade.php` changes were preserved. No push, merge or deployment was performed.
