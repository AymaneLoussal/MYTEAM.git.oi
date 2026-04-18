# FTMBALL (Football Team Manager)

Laravel 13 app (no Spatie/Livewire/Voyager/EasyPanel/DataTables). Custom session auth, teams, profile, admin flag, invitations, trainings, and matches.

## Quick Start
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

Admin: `admin@team.com` / `adminpass`

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



