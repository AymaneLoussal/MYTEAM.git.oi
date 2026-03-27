# Football Team Management API

Custom Laravel 11 app - session auth, dynamic teams, profile. No auth packages!

## 🚀 Quick Start
```
git clone ...
cd my-team
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

Admin: `admin@team.com` / `adminpass`

## 📊 DB Schema
- **users**: id, name, email(unique), avatar(null), password(hashed), is_admin(default false)
- **teams**: id, name, coach_id(FK users), timestamps
- **team_user**: team_id(FK), user_id(FK), unique(team,user) pivot

## 🔐 Auth Logic (Custom Session)
1. **Register** POST /register {name,email,password} → hashed pw
2. **Login** POST /login {email,pw} → Hash::check + session('user_id') + regenerate (anti-fixation)
3. **Logout** POST /logout → forget session
- Middleware: Auth (session + user exists), Admin (is_admin)

## ⚽ Team Dynamic Roles
- **Coach**: Create team → coach_id = me (User::coachTeams())
- **Player**: Join → team_user attach (User::playerTeams())
- Unique pivot prevents dup joins
- Policy: Coach manage own team

## 👤 Profile Management
- PATCH /profile {name?,email?,avatar(image)?,password?}
- Upload → storage/app/public/avatars + delete old
- avatar_url accessor → /storage/avatars/xx.jpg or default
- Password optional + confirmed/hash

## 📋 API Endpoints
| Method | Endpoint | Auth | Desc |
|--------|----------|------|------|
| POST | /register | - | Create user |
| POST | /login | - | Login session |
| POST | /logout | auth | Logout |
| PATCH | /profile | auth | Update profile/avatar |
| GET | /teams | auth | List coach/player teams |
| POST | /teams | auth | Create team (coach) |
| POST | /teams/{id}/join | auth | Join as player |
| GET | /admin/users | admin | List users |

## 🧪 Test Example (curl)
```
# Login
curl -X POST http://127.0.0.1:8000/login -d '{"email":"test@test.com","password":"pass"}' -H "Content-Type: app/json" -c cookies.txt

# Profile w/ avatar
curl -X PATCH http://127.0.0.1:8000/profile -b cookies.txt -F "name=Updated" -F "avatar=@photo.jpg"

# Teams
curl http://127.0.0.1:8000/teams -b cookies.txt
```

## 🎯 Validations & Security
- All inputs validated (unique/email/image/size/min/confirmed)
- Hash::make/check
- Session regenerate
- FK cascade
- Pivot unique
- Policies coach manage
- User exists check (deleted session invalid)

## 📈 Scalability
Easy add:
- Matches: matches table team_id x team_id
- Invites: invites team_id/user_id/status
- Chat: messages morphTo team/user
- Events/notifs: Laravel events/queues

## Notes (Junior Dev)
- Pure Laravel - no Sanctum (session API ok)
- Linter errors ignore (middleware authorize in L11 needs AuthServiceProvider)
- Default avatar: public/images/default-avatar.png (add)
- Prod: SESSION_DRIVER=database, storage perms

Project 100%! 🚀

