# FTMBALL fix and security verification report

Date: 14 November 2026. Branch: `fix/ftmball-audit`.

**All 20 original audit checks pass. The application test suite passes, including the added regression coverage. All authorization defects identified during this review were repaired.** Production asset compilation remains unverified because this environment cannot fetch the npm dependencies.

## Actual verification

| Check | Result |
| --- | --- |
| `php artisan test --compact` | **42 passed, 219 assertions**, no failures |
| `audit/run.ps1` | **20 passed, 55 assertions**; original expectations unchanged |
| Combined tests | **62 passed, 274 assertions** |
| `php artisan route:list --except-vendor` | **55 routes**, no discovery errors |
| PHP syntax | **84 files, zero failures**, including application, config, migrations, tests, and audit PHP |
| `php artisan view:cache` | All Blade templates compiled successfully |
| Local server | Started on `127.0.0.1:8765`, stopped after verification |
| HTTP smoke | `/`, `/login`, `/register`, `/up`: **200**; unauthenticated `/dashboard` redirects to login |
| Real HTTP CSRF check | POST `/login` without token: **419** |
| `git diff --check` | No whitespace errors |
| Composer config | Valid JSON; TLS enabled and secure HTTP enabled |
| npm installation/build | Online install blocked with registry `EACCES`; offline attempt returned `ENOTCACHED`; build could not run |

Runtime: PHP 8.5.12 with SQLite. All automated database tests used isolated in-memory SQLite. The local database received only `2026_11_14_000000_add_notes_to_team_user`, an additive nullable-column migration. No reset, fresh migration, development reseeding, or production database command was run.

Local evidence: `audit/suite-results.xml`, `audit/suite-output.txt`, `audit/probe-results.xml`, `audit/probe-output.txt`, `audit/routes-output.txt`. Generated output is ignored by Git.

## Issue-by-issue resolution

| Audit issue | Implemented fix |
| --- | --- |
| Unauthorized private chats | Require current membership; coaches select roster players, players can initiate only their own conversation with the actual coach. Validate before transactional creation. |
| Chat access after leaving | Remove conversation participation and obsolete match lineup entries on leaving. Read/send/list/poll also require current membership, so stale participant rows cannot restore access. Applies to all conversations belonging to the team. |
| Announcement authorization | Only coaches create/post announcements; current player participants can read. |
| Broken team authorization | Use `AuthorizesRequests` and bridge custom session middleware to Laravel's authenticated user resolver. Existing session architecture is retained. |
| Cross-team notes | Require ownership and roster membership; store notes on `team_user`, display only to the coach, remove global notes from mass assignment/serialization. Preserve legacy global data without copying it across teams. |
| Inconsistent membership | Shared `TeamMembership` enforces one active team per user for both creation routes and invitation acceptance. Joining requires a matching invitation and emailed token. Existing multi-team records are not destructively rewritten. New players join existing group/announcement chats. |
| Additional invitation-token bypass | Dashboard/API listings no longer disclose tokens merely because an account claims an email. Direct joining requires the token. This closes the unverified-email shortcut discovered during implementation. |
| Missing throttling | Add login email/IP and IP limits; registration, both invitation-send routes, chat creation/messages, and invitation acceptance are limited. |
| Chat DOM injection | Use DOM `textContent` for messages/names, safe JavaScript serialization, and literal formation-marker text. |
| Session lifecycle | Logout invalidates the session and rotates CSRF; invitation automatic login regenerates the session ID; normal login retains session regeneration. |
| Sensitive match diagnostics | Remove full-session logging and raw database exception messages returned to users. Use Laravel exception handling; document production `APP_DEBUG=false`. |
| Unsafe test database/credentials | Remove PostgreSQL credentials; force SQLite `:memory:`; refuse unsafe/cached database configuration before database-reset test traits run. |
| Profile password/null failure | Exclude blank optional passwords; require current password for email/password changes. |
| Profile relationship/response failure | Reload with `fresh()` and use appended `avatar_url`; browser redirects with feedback, JSON requests receive JSON. |
| Avatar consistency | Validate uploads, use the public disk for storage and URL generation, and delete the old avatar only after persisting the replacement. |
| Formation page crashes | Remove nonexistent header component, correct component-layout use and string truncation. Create/list/detail/edit pages render. |
| Formation payload mismatch | Decode JSON-string form payloads; also accept native arrays. Require coordinates, roster-scoped distinct player IDs, supported roles, and at most 11 positions. Apply to create/update. |
| Formation edit/delete incomplete | Add authorized edit/delete routes/actions, preload the same formation for updates, and redirect browser actions correctly. Retain drag-and-drop; add button and coordinate controls. |
| Missing invitation registration page | Render the existing invitation page with its registration form open. Keep the inline registration flow. |
| Incorrect invitation states | Separate pending/accepted/rejected/expired presentation; old expiry dates do not relabel accepted invitations. Reject expired/processed reuse. |
| Lost invitation after login | Preserve the invitation destination and return there after login for confirmation. |
| Existing-account registration errors | Redirect existing accounts to login without replacing credentials. Disallow registering a different identity while signed in. |
| Partial/racing invitation changes | Transactional account creation, membership, and acceptance; lock/recheck acceptance/rejection state. Membership failures leave invitation and roster unchanged. |
| Duplicate invitation sending logic | Dashboard delegates to the shared send action; validation errors work for browser forms and limits are shared. |
| Undiscovered/stale tests | Convert match/training methods to `test_...`, correct model/factory references and date payloads, restore invitation factory support, and use valid formation/invitation requests. |
| Unbounded chat refresh | Paginate lists/history and fetch only messages after the last ID, at most 100 per poll. Show network/validation errors and stop polling on access loss. |
| Missing/inconsistent theme | Shared asset partial and `resources/theme.json`, consumed by Vite through `tailwind.config.cjs`. Production requires a build; local development retains a CDN fallback. Avoided unnecessary full-layout rewrites. |
| Empty reminder command | Remove the stub and document that automatic reminders are not an implemented feature. No delivery is claimed. |
| Non-repeatable/insecure demo seed | Use `firstOrCreate`, preserve existing passwords, and refuse non-local/non-testing environments. Production requires separately provisioned unique credentials. |
| Unsafe/incomplete setup docs | Replace destructive quick-start migrations; document SQLite, incremental notes migration, email/log behavior, reachable URLs, tests, asset builds, and production configuration. |
| Invalid/insecure tracked Composer file | Preserve the user's pre-existing correction exactly in its own commit: remove invalid trailing JSON text and enable TLS/secure HTTP. |

## Tests added and updated

- `audit/ProjectAuditTest.php`: all **20 original checks** retained without weakening expectations; previously untracked baseline committed.
- `tests/Feature/SecurityRegressionTest.php`: **13 tests** for outsider/player authorization, announcements, revocation through all endpoints, stale participants, joining/group enrollment, membership restrictions, scoped notes, resource mutation authorization, throttling/recovery, logout, token disclosure, bounded polling, and escaped message rendering.
- `tests/Feature/WorkflowRegressionTest.php`: **13 tests** for formation lifecycle/invalid payloads/cross-team updates, sensitive profile changes, uploads, invitation expiry/reuse/wrong accounts/login continuation/session rotation/status display, repeated seeding, and coach match/training updates/deletes.
- Updated `FormationTest.php`, `InvitationTest.php`, `MatchTest.php`, and `TrainingTest.php`. All original suites pass; the five previously undiscovered match/training tests now execute.
- `tests/TestCase.php` fails closed on unsafe database settings. `audit/run.ps1` retains explicit process-local in-memory configuration.

## Remaining actions and limits

1. **Production assets:** run `npm install` and `npm run build` where registry access is available, then verify compiled styling. No successful asset build or dependency vulnerability scan is claimed here.
2. **Old database credential:** rotate it at its actual PostgreSQL database if it was real. It is removed from current configuration but remains in Git history; this work did not connect to or administer that server.
3. **Email:** configure and verify actual SMTP delivery and a reachable production URL. Automated tests used the array mailer; no emails were sent to other people.
4. **Other installations:** apply the additive notes migration before using the updated app. It is already applied to this local SQLite database. Legacy global notes require deliberate assignment to a team if they must remain visible.
5. **Verification scope:** Blade rendering, real HTTP/CSRF, and backend workflows were executed. Interactive browser/mobile, visual accessibility, drag-and-drop execution, multi-process concurrency/load tests, and production deployment were not performed. Passing authorization tests are evidence for the identified fixes, not a guarantee against every possible vulnerability.

No known authorization defect identified in this audit remains open. No push or merge was performed.

## Implementation commits

| Hash | Change |
| --- | --- |
| `5c0cecb336e2053a7f06db92dd68cfcdf9ba843a` | Isolate tests and preserve audit baseline |
| `f19aea2743d13a4848751c89708d0005629c4957` | Shared assets and safe repeatable setup |
| `dd927b640e1f50f32f6f3d33d8e8bad653bcbf03` | Team/chat authorization, membership, invitations, security regressions |
| `bd0a20ced37249d533db5ba180d0e90cdd8966d2` | Profile/formations, workflow regressions, original test repairs |
| `bddd262` | Preserve existing valid Composer/TLS correction |

This report is committed separately after implementation. Its hash is available through `git log -1 -- FTMBALL_FIX_REPORT.md`, avoiding a self-referential hash.

## Complete file inventory

Paths are relative to the project root. The reminder command was removed. The original audit document, test, and runner were added to Git rather than rewritten.

- `.env.example`
- `.gitignore`
- `PROJECT_AUDIT.md`
- `README.md`
- `app/Console/Commands/SendMatchReminders.php`
- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/ChatController.php`
- `app/Http/Controllers/Controller.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/FormationController.php`
- `app/Http/Controllers/InvitationController.php`
- `app/Http/Controllers/MatchController.php`
- `app/Http/Controllers/ProfileController.php`
- `app/Http/Controllers/TeamController.php`
- `app/Http/Middleware/AuthMiddleware.php`
- `app/Models/Invitation.php`
- `app/Models/Team.php`
- `app/Models/User.php`
- `app/Policies/ConversationPolicy.php`
- `app/Policies/TeamPolicy.php`
- `app/Providers/AppServiceProvider.php`
- `app/Services/TeamMembership.php`
- `audit/.gitignore`
- `audit/ProjectAuditTest.php`
- `audit/run.ps1`
- `composer.json`
- `database/factories/InvitationFactory.php`
- `database/migrations/2026_11_14_000000_add_notes_to_team_user.php`
- `database/seeders/AdminSeeder.php`
- `phpunit.xml`
- `resources/css/app.css`
- `resources/theme.json`
- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`
- `resources/views/chat/index.blade.php`
- `resources/views/chat/show.blade.php`
- `resources/views/components/assets.blade.php`
- `resources/views/components/feedback.blade.php`
- `resources/views/components/layouts/app.blade.php`
- `resources/views/dashboard.blade.php`
- `resources/views/formations/create.blade.php`
- `resources/views/formations/index.blade.php`
- `resources/views/formations/show.blade.php`
- `resources/views/formations/team.blade.php`
- `resources/views/invitations/show.blade.php`
- `resources/views/profile/show.blade.php`
- `resources/views/teams/index.blade.php`
- `resources/views/teams/show.blade.php`
- `resources/views/welcome.blade.php`
- `routes/web.php`
- `tailwind.config.cjs`
- `tests/Feature/FormationTest.php`
- `tests/Feature/InvitationTest.php`
- `tests/Feature/MatchTest.php`
- `tests/Feature/SecurityRegressionTest.php`
- `tests/Feature/TrainingTest.php`
- `tests/Feature/WorkflowRegressionTest.php`
- `tests/TestCase.php`
- `FTMBALL_FIX_REPORT.md` (this report)
