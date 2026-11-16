# FTMBALL Git Release Plan

Audit date: 2026-11-16
Current branch: `fix/ftmball-audit`
Target branch: `main` through a reviewed Pull Request
Repository action taken: analysis only; no staging, commit, push, merge, tag, or deployment

## Repository state

- The branch is `fix/ftmball-audit`, based on the published `main` history. Do not rewrite it.
- The worktree contains the accumulated chat, formation, invitation, UI, Docker, dependency, test, and documentation work.
- No unmerged index entries or conflict markers were found.
- `package-lock.json` was already staged before this audit. It should be unstaged before organizing commits, then included with frontend/dependency work.
- `results.xml` is a tracked generated test artifact. Remove it from tracking with `git rm --cached -- results.xml` while preserving the local file; `.gitignore` already excludes it.
- `git diff --check` initially found an extra trailing blank line in two formation Blade files. Both were corrected; the final check is clean apart from harmless CRLF conversion warnings.

## Include in the release

### 1. Security and authorization fixes

- `app/Http/Controllers/ChatController.php`
- `app/Models/Conversation.php`
- `app/Policies/ConversationPolicy.php`
- `app/Services/TeamMembership.php`
- `app/Services/TeamChat.php`
- security-related portions of `app/Http/Controllers/DashboardController.php`, `app/Models/Team.php`, `routes/web.php`, and `routes/console.php`
- chat integrity/backfill migrations under `database/migrations/2026_11_15_*`
- `tests/Feature/ChatImprovementTest.php`, `tests/Feature/ChatIntegrityTest.php`, and security regression updates

### 2. Application features and UI corrections

- formation controller/model, `config/tactics.php`, formation migration, formation JavaScript/views/tests
- reusable view components and layout/navigation changes
- chat panels/rows and chat views
- profile/avatar, dashboard, team, match, training, authentication, invitation, and welcome view updates
- `resources/css/app.css`, `resources/js/app.js`, and `vite.config.js`

### 3. Invitation and email improvements

- `app/Http/Controllers/InvitationController.php`
- `config/mail.php`
- invitation route/view changes
- `tests/Feature/InvitationDeliveryTest.php` and `tests/Feature/InvitationResendTest.php`
- invitation/Gmail reports

### 4. Docker and database infrastructure

- `Dockerfile`, `compose.yaml`, `.dockerignore`
- `.env.docker.example`, `.env.production.example`
- all reviewed files under `docker/`
- `bootstrap/app.php` trusted-proxy support
- `DOCKER_DEPLOYMENT.md` and `FTMBALL_DOCKER_REPORT.md`

### 5. Production hardening and dependencies

- `.gitignore`
- `composer.lock`
- `package-lock.json`
- production/session/proxy/migration controls
- security remediation and production readiness reports

### 6. Tests and documentation

- `audit/ProjectAuditTest.php`
- all new and modified tests under `tests/Feature/`
- FTMBALL reports and `RELEASE_VERIFICATION.md`
- this release plan

Some files contain changes belonging to more than one group. Use `git add -p` and review each staged diff. If splitting a file would produce a nonfunctional intermediate commit, keep the related implementation and tests together rather than forcing the six conceptual groups into broken commits.

## Exclude from the release

- `.env`, `.env.docker`, `.env.production`, and all real secret values
- `database/*.sqlite*`, including current databases and local pre-change backups
- `backups/` and its verified SQLite snapshot
- `storage/logs/**` and uploaded files under `storage/app/public/**` (retain only the tracked `.gitignore` placeholder)
- `public/build`, `public/hot`, and `public/storage`
- `vendor`, `node_modules`, local caches, and Docker volumes/images
- `smtp-laravel-test.php`
- `results.xml` after its tracked index entry is removed

Observed local excluded artifacts include `database/database.sqlite`, several dated SQLite backups, `storage/logs/laravel.log`, the ignored `backups/` directory, and generated Vite assets. They must remain local and untracked.

## Security findings

- `.env`, `.env.docker`, `.env.production`, SQLite files, backups, public uploads, logs, and generated assets are covered by Git/Docker ignore rules.
- Tracked sensitive-path review found only the template `.env.example` and the intentional `storage/app/public/.gitignore` placeholder.
- `.env.docker.example` and `.env.production.example` use placeholders for `APP_KEY` and `MAIL_PASSWORD`; no real values were printed or found.
- `.dockerignore` excludes every `.env*` file except explicit example/testing templates, plus SQLite data, backups, uploads, logs, and Git metadata. Production secrets therefore do not enter the Docker build context.
- Reachable Git history contained no committed `.env`, SQLite, or database files in the path audit.
- A redacted high-confidence history scan found no app keys, Gmail passwords, common cloud access keys, or private-key signatures.
- `gitleaks` was unavailable, so the history scan is strong heuristic evidence, not a substitute for a dedicated secret scanner. Run Gitleaks or an equivalent tool before making the repository public.
- Composer production audit reports no advisories. No auditing or TLS controls are disabled.

## Final verification results

| Check | Result |
|---|---|
| Conflict/index inspection | PASS - no unmerged entries or conflict markers |
| `git diff --check` | PASS after removing two extra EOF blank lines; CRLF notices only |
| `composer validate` | PASS |
| `composer audit --no-dev` | PASS - no advisories |
| `php artisan test` | PASS - 56 tests, 337 assertions |
| `audit/run.ps1` | PASS - 20 tests, 55 assertions |
| `npm run build` | PASS outside managed filesystem - 62 modules |
| `docker compose config --quiet` | PASS |
| `docker compose build` | PASS, including Vite production build |

## Recommended commit sequence

1. `fix(security): enforce team chat and membership authorization`
2. `feat(app): complete formations chat UI and shared interface`
3. `fix(invitations): make email delivery and resend resilient`
4. `build(docker): add persistent Laravel runtime and database compatibility`
5. `chore(security): update dependencies and production hardening`
6. `test(docs): add regression coverage and release evidence`

Every commit should pass relevant targeted tests. The final commit must pass the complete suite. Preserve the existing published history; use ordinary new commits, not rebase/force-push, unless the repository owner explicitly approves a private-branch cleanup.

## Approval-ready Git commands

Run these only after reviewing the plan. They intentionally begin by clearing the pre-existing staged lockfile so each group can be reviewed independently:

```powershell
git restore --staged -- package-lock.json
git rm --cached -- results.xml
git status --short
git diff --check
```

Then stage each conceptual commit using explicit paths and patch mode where files overlap:

```powershell
# Group 1: security/chat integrity
git add -p app/Http/Controllers/ChatController.php app/Http/Controllers/DashboardController.php app/Models/Conversation.php app/Models/Team.php app/Policies/ConversationPolicy.php app/Services/TeamMembership.php routes/web.php routes/console.php
git add app/Services/TeamChat.php database/migrations/2026_11_15_000000_backfill_team_chats.php database/migrations/2026_11_15_010000_enforce_chat_uniqueness.php tests/Feature/ChatImprovementTest.php tests/Feature/ChatIntegrityTest.php
git diff --cached --check
git diff --cached
git commit -m "fix(security): enforce team chat and membership authorization"

# Group 2: formations and UI
git add -p app/Http/Controllers/FormationController.php app/Models/Formation.php app/Http/Controllers/ProfileController.php resources routes/web.php tests/Feature/WorkflowRegressionTest.php
git add config/tactics.php database/migrations/2026_11_15_020000_add_formation_preset.php tests/Feature/FormationSquadTest.php tests/Feature/FormationTacticsTest.php tests/Feature/NavigationTest.php vite.config.js package-lock.json
git diff --cached --check
git diff --cached
git commit -m "feat(app): complete formations chat UI and shared interface"

# Group 3: invitations and mail
git add app/Http/Controllers/InvitationController.php config/mail.php tests/Feature/InvitationDeliveryTest.php tests/Feature/InvitationResendTest.php FTMBALL_GMAIL_SMTP_FIX_REPORT.md FTMBALL_INVITATION_FIX_REPORT.md
git diff --cached --check
git diff --cached
git commit -m "fix(invitations): make email delivery and resend resilient"

# Group 4: Docker/database infrastructure
git add Dockerfile compose.yaml .dockerignore .env.docker.example .env.production.example bootstrap/app.php docker DOCKER_DEPLOYMENT.md FTMBALL_DOCKER_REPORT.md
git diff --cached --check
git diff --cached
git commit -m "build(docker): add persistent Laravel runtime and database compatibility"

# Group 5: dependency and production hardening
git add .gitignore composer.lock package-lock.json FTMBALL_SECURITY_REMEDIATION.md FTMBALL_PRODUCTION_READINESS.md
git diff --cached --check
git diff --cached
git commit -m "chore(security): update dependencies and production hardening"

# Group 6: remaining tests and reports
git add audit tests FTMBALL_CHAT_IMPROVEMENT_REPORT.md FTMBALL_FORMATION_BUILDER_REPORT.md FTMBALL_UI_UX_REPORT.md RELEASE_VERIFICATION.md FTMBALL_GIT_RELEASE_PLAN.md
git diff --cached --check
git diff --cached
git commit -m "test(docs): add regression coverage and release evidence"
```

Because patch-mode grouping is interactive, verify after every commit that no intended hunks were stranded or duplicated:

```powershell
git status --short
git show --stat --oneline HEAD
php artisan test
powershell -NoProfile -ExecutionPolicy Bypass -File audit/run.ps1
```

Do not push yet. After all commits and a clean final verification, the recommended publication flow is:

```powershell
git log --oneline main..fix/ftmball-audit
git diff --stat main...fix/ftmball-audit
git push -u origin fix/ftmball-audit
```

Then open a Pull Request from `fix/ftmball-audit` into `main`, require CI and human review, and merge without force-pushing or rewriting `main`.

## Remaining production deployment requirements

- Configure the real HTTPS endpoint and exact trusted proxy addresses.
- Inject stable secrets through the hosting secret manager.
- Schedule encrypted off-host database/upload backups with monitoring and retention.
- Verify restoration in the selected hosting environment.
- Send one invitation only to an explicitly authorized address and confirm SMTP acceptance, inbox receipt, HTTPS link, and acceptance.
- Configure logs, health/uptime monitoring, storage alerts, and rollback procedures.
- Decide between single-instance SQLite and managed PostgreSQL before provisioning production data.
