# FTMBALL (Football Team Manager)

Laravel 13 app (no Spatie/Livewire/Voyager/EasyPanel/DataTables). Custom session auth, teams, profile, admin flag, invitations, trainings, and matches.

## Quick Start
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan serve
```

Admin: `admin@team.com` / `adminpass`

The demo admin is seeded only in local/testing environments. Re-running its seeder preserves the existing password. Do not use the demo credentials in production; provision an administrator with a unique password through your deployment process.

## Local setup and upgrades

PHP must have `pdo_sqlite`, `mbstring`, `fileinfo`, and XML extensions enabled. Use `php --ini` to locate the active configuration. Keep an existing `.env` and `APP_KEY` when upgrading. `php artisan migrate` applies incremental migrations; never use `migrate:fresh` on data you want to keep.

The audit fix adds nullable notes to `team_user`. Run `php artisan migrate` on each installation. Legacy `users.notes` values are retained but no longer shown across teams; review and assign them to the appropriate membership manually if needed.

Each user can coach or play for one active team. Joining requires the token from the invitation email. Dashboard listings intentionally do not reveal tokens because registration alone does not verify ownership of an email address. Players can start private conversations with their coach, not arbitrary team members. Leaving revokes all conversations belonging to that team. Only coaches can post announcements.

## Assets and email

```bash
npm install
npm run build
```

Vite builds the shared theme from `resources/theme.json` through `tailwind.config.cjs`. Local development has a CDN fallback when there is no build; production requires the compiled assets. Use `npm run dev` alongside `php artisan serve` when editing assets.

`MAIL_MAILER=log` writes invitations to `storage/logs/laravel.log`; it does not deliver email. For actual delivery, configure the supported SMTP settings in `.env` and set `APP_URL` to the URL recipients can reach (include the local port, e.g. `http://127.0.0.1:8000`). Run `php artisan config:clear` after changing local configuration. Keep production secrets outside version control and set `APP_DEBUG=false`.

The unfinished match-reminder command was removed. Automatic reminder delivery is not an implemented feature.

## Tests

```bash
php artisan test
```

`phpunit.xml` forces in-memory SQLite. The test base class refuses non-isolated database settings, including an unsafe cached configuration. No development data is needed for the tests. Clear cached configuration if the guard refuses to run.

On Windows, run the original 20 audit checks with `powershell -NoProfile -ExecutionPolicy Bypass -File audit/run.ps1 -Php php` (or pass the full path to your PHP executable). Audit output files are ignored by Git. The PostgreSQL credential removed from the historical test configuration must be rotated if it was a real credential; removing it from the current file does not remove it from Git history.

## Implemented Features
- Custom session authentication (register/login/logout)
- Browser UI pages (login, register, dashboard)
- Team creation and joining (coach and player roles via relations)
- Profile update (name/email/password) + avatar upload
- Admin middleware (boolean `is_admin`) + list users
- Team invitations by email (token link + accept/reject)
- Training scheduling (coach) + training viewing (players)
- Match scheduling (coach) + lineup roles (starter/sub/not selected)

## Main Routes (web.php)
- `GET /login`
- `GET /register`
- `GET /dashboard` (auth)
- `POST /register`
- `POST /login`
- `POST /logout` (auth)
- `GET /teams` (auth)
- `POST /teams` (auth)
- `POST /teams/{team}/join` (auth)
- `PATCH /profile` (auth)
- `POST /teams/{team}/invite` (auth)
- `GET /invitations` (auth)
- `GET /invitations/{token}` (public page)
- `POST|PATCH /invitations/{token}/accept` (auth)
- `POST|PATCH /invitations/{token}/reject` (auth)
- `GET /trainings` (auth)
- `GET /teams/{team}/trainings` (auth)
- `GET /teams/{team}/trainings/create` (auth, coach)
- `POST /teams/{team}/trainings` (auth, coach)
- `GET /trainings/{training}` (auth)
- `GET /trainings/{training}/edit` (auth, coach)
- `PATCH /trainings/{training}` (auth, coach)
- `DELETE /trainings/{training}` (auth, coach)
- `GET /matches` (auth)
- `GET /teams/{team}/matches` (auth)
- `GET /teams/{team}/matches/create` (auth, coach)
- `POST /teams/{team}/matches` (auth, coach)
- `GET /matches/{match}` (auth)
- `GET /matches/{match}/edit` (auth, coach)
- `PATCH /matches/{match}` (auth, coach)
- `GET /matches/{match}/lineup` (auth, coach)
- `PATCH /matches/{match}/lineup` (auth, coach)
- `DELETE /matches/{match}` (auth, coach)
- `GET /admin/users` (admin)

## Notes
- Default avatar asset: `public/images/default-avatar.svg`
- Policies are registered in `app/Providers/AuthServiceProvider.php`



- Appended documentation for Roles 
