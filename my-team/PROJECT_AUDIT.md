# FTMBALL project audit

Date: 14 November 2026. Scope: local Laravel application, routes, controllers, models, migrations, views, and automated tests.

**Verdict: the application starts, but several core workflows fail and chat authorization needs correction before a public release.** PHP/SQLite installation is now working. The remaining failures are application issues.

## Verification performed

| Check | Result |
| --- | --- |
| Runtime | PHP 8.5.12; `pdo_sqlite` and `sqlite3` available |
| Local server | Started successfully on `127.0.0.1:8765` |
| HTTP smoke checks | `/`, `/login`, `/register`, `/up`: HTTP 200; unauthenticated `/dashboard` redirects to login |
| Database | All 14 application migrations reported as applied; test migrations also ran on in-memory SQLite |
| Route discovery | 52 application routes registered |
| PHP syntax | 79 PHP files checked in app/routes/database/tests/config/bootstrap; zero syntax errors |
| Existing tests | 11 executed: 9 passed, 1 failed, 1 errored; 2 test-class discovery warnings |
| Additional audit tests | 20 executed, 52 assertions: 8 passed, 12 failed |

The audit tests assert intended behavior, so failures are evidence of defects rather than unexpected audit-tool errors. Several failures share one cause; failure count is not a count of unique bugs.

Database writes from automated tests used `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:`. The application's database was not migrated, seeded, or reset during this audit. HTTP smoke requests can create ordinary session records. Production application code was not changed. The pre-existing modification to `composer.json` was left intact.

Working flows exercised: registration/login/logout, authenticated dashboard and index pages, team detail access, invitation acceptance by an existing account, inline invitation registration, seeded admin access and ordinary-user denial, match creation/detail/lineup submission, training creation/detail/edit-page rendering, and outsider denial for team/match/training details. Match route binding worked despite the different controller parameter name, because the explicit binding in `AppServiceProvider` supplies the model.

## Fix first: reproduced defects

### 1. High — private chat creation lacks authorization

Location: `app/Http/Controllers/ChatController.php:84`.

An authenticated outsider can submit a different team's ID, a player from that team, and `type=coach_player_private`. The controller checks coach ownership for group/announcement chats only. It then creates a private conversation and attaches the outsider and target player. The audit expected 403 and received a redirect to the created conversation.

Fix: define permitted coach/player relationships, authorize the requester before creating any record, and ensure the actual coach is included when a player initiates a coach/player chat. Validate type, team, player, and subject. Create the conversation and participants in one transaction. Invalid player requests currently create the conversation before aborting.

### 2. High — leaving a team retains access to team chat

Locations: `app/Http/Controllers/TeamController.php:118`; `app/Http/Controllers/ChatController.php:40` and `:57`.

Reproduction: add a player to a team and its group conversation; leave the team; request the conversation. The response remains HTTP 200. Leaving deletes only `team_user`; chat authorization checks only `conversation_participants`. The same participant check governs sending messages.

Fix: enforce current team membership for team conversations and reconcile participants when membership changes. Decide separately whether historical private conversations should remain accessible. New team members also need a defined way to join existing group conversations.

### 3. High — profile updates return server errors

Location: `app/Http/Controllers/ProfileController.php:45-67`.

Two independently reproduced problems:

- Submitting the browser form with an empty optional password tries to write NULL into `users.password`, causing a NOT NULL constraint error.
- Updating only a name reaches `$user->fresh(['avatar_url'])`, which tries to eager-load an accessor as a relationship and throws `RelationNotFoundException`. The database update occurs before this second error.

Fix: remove the password key when it is empty, reload with `fresh()` without that relationship argument, and return a redirect with success feedback for browser forms. Keep JSON responses for JSON requests. Add a current-password check for sensitive account changes and test uploads separately.

### 4. High — team creation/join/notes authorization calls are broken

Locations: `app/Http/Controllers/Controller.php:5`; `app/Http/Controllers/TeamController.php:40`, `:58`, `:81`; `app/Http/Middleware/AuthMiddleware.php:17`.

`POST /teams` returns HTTP 500: `TeamController::authorize does not exist`. The base controller imports `Authorizable`, the entity ability-checking trait, instead of `AuthorizesRequests`. Join and notes actions use the same missing method. Dashboard team creation uses a separate code path and is not affected by this specific error.

Fix both layers: use `AuthorizesRequests`, then integrate custom session authentication with Laravel's auth user/Gate resolver or explicitly authorize for the current user. Currently middleware validates `session('user_id')` but does not authenticate that user with Laravel's guard. Replacing the trait alone is insufficient for policy authorization.

### 5. High — formation pages and browser submission are broken

Locations: `resources/views/formations/{create,team,show}.blade.php`; `app/Http/Controllers/FormationController.php:91`.

Creation, team listing, and detail pages all returned HTTP 500 because `<x-header>` does not exist. Browser form submission sends `positions` as a JSON string; the controller requires an array, so the submitted payload is rejected with “The positions field must be an array.”

Further source-confirmed defects will remain after the first exception is fixed:

- These views use `@extends` with a component layout that expects `$slot`.
- The detail page calls `slice()` on a normal name string.
- The delete form references the nonexistent `formations.destroy` route; no destroy action is implemented.
- Edit redirects to the creation page with `?edit=...`, but the create action does not load or update the existing formation.

Fix the layout/component contract, name truncation, payload serialization, and complete edit/delete routes and controller behavior. Test create → view → edit → delete through the actual form contract.

### 6. Medium — formations accept players outside the team

Location: `app/Http/Controllers/FormationController.php:91-98`, `:147-153`.

Submitting another user's ID succeeds with HTTP 201. Validation checks only whether the user exists. Nested fields are also optional, so malformed position entries may be stored and later fail during rendering.

Fix: scope players to the team's roster; require player ID and coordinates; reject duplicates; define allowed roles and appropriate roster limits. Apply identical rules to creation and updates.

### 7. Medium — invitation branches are inconsistent

Locations: `app/Http/Controllers/InvitationController.php:106-195`; `app/Http/Controllers/AuthController.php:70`; `resources/views/invitations/show.blade.php:29`.

Reproduced:

- Accepting an invitation for an email without an account through `/accept` renders missing `invitations.register` and returns HTTP 500. The separate inline `register-and-accept` flow does work, so not all new-account invitations are broken.
- An accepted invitation displays “Invitation Expired.” The first condition catches every non-pending status before the accepted branch can run.

Additional source findings: login stores but does not resume `invitation_token`; registration-and-accept does not handle an already-existing email gracefully; account creation, membership attachment, and invitation update are not transactional; its automatic login does not regenerate the session ID.

Fix: unify the invitation journey, preserve its destination through login, display distinct accepted/rejected/expired states, handle existing accounts, use a transaction, and regenerate the session after automatic login.

## Other issues found by source review

These findings are distinguished from the runtime reproductions above.

| Priority | Finding and evidence | Required action |
| --- | --- | --- |
| High | `phpunit.xml:26-32` contains a named PostgreSQL database, superuser login, and plaintext password; the file is tracked. Tests use `RefreshDatabase`. | Use a dedicated disposable database or in-memory SQLite; remove the credential and rotate it if it is real. A normal test run must never target a database holding useful data. |
| High | No login, registration, invitation-send, or chat-message throttling appears in the registered route/controller setup. | Add rate limits appropriate to each action and test lockout/recovery behavior. |
| Medium | `resources/views/chat/show.blade.php:76` inserts `data.message` into `innerHTML`. | Build DOM nodes with `textContent`; serialize JavaScript values safely. The observed unsafe insertion is on the sender's immediate message display; cross-user script execution was not established. Server-rendered message text is Blade-escaped. |
| Medium | Player notes are saved on global `users.notes`; `TeamController::updatePlayerNotes` does not check that the selected player belongs to the team. This action is currently blocked by issue 4. | Scope notes to team membership and require roster membership. Fix this together with authorization so repairing the 500 does not expose a cross-team write. |
| Medium | Dashboard creation restricts users to one team, while other create/join/invitation paths do not consistently enforce that rule. `TeamPolicy::join` allows any authenticated user. | Decide whether teams are public or invitation-only and whether multi-team membership is allowed; enforce the chosen rule centrally. |
| Medium | `MatchController::store` logs the full session and returns raw exception messages to the user. | Log only the diagnostic fields needed; return a generic failure message with a correlation identifier. |
| Medium | Logout only forgets `user_id`; invitation auto-login lacks session regeneration. | Invalidate the session and regenerate the CSRF token on logout; regenerate the session at every login entry point. |
| Medium | Chat loads all messages and polls the entire rendered page every three seconds. No pagination or incremental fetch exists. | Page messages, fetch only messages newer than the last ID, and handle network/validation errors visibly. |
| Medium | Profile, formation, and chat views use semantic Tailwind colors such as `bg-primary` without the shared configuration defining them. Several pages duplicate their document/layout. | Consolidate layout and theme configuration, compile assets consistently, and review mobile layout, focus states, labels, and contrast in a browser. This audit did not measure rendered accessibility or visual quality. |
| Low | `SendMatchReminders::handle()` is empty and no reminder schedule is configured. | Implement and schedule reminders if they are a required feature; otherwise remove the unfinished feature/stub from expectations. |
| Low | Admin seeding always creates the same email with a fixed documented password. Repeated seeding will collide with the unique email. | Make local seeding repeatable; provision production admins securely without the demo password. |
| Low | `.env.example` uses the log mailer and localhost URL. README suggests `migrate:fresh --seed`. | Explain that log mail does not deliver invitations; document real mail/URL setup and ordinary non-destructive migrations. Reserve `migrate:fresh` for disposable development data. |

## Test suite repairs

The existing suite does not currently provide reliable feature coverage:

- `FormationTest::test_coach_can_create_formation` omits the required positions payload, so validation prevents creation.
- `InvitationTest::test_user_can_accept_invitation` calls `Invitation::factory()` without `HasFactory` on that model. Its payload also uses `coach_id` instead of `invited_by` and assumes a different acceptance flow.
- Match and training methods use docblock `@test` annotations that the installed PHPUnit does not discover. Convert to `test_...` methods or supported test attributes. Those tests also contain stale factory/model references and date payloads that need repair.
- Several named feature suites only check whether `/` returns HTTP 200; the unit example merely asserts true.

Retain regression coverage for each confirmed bug, then add CSRF-inclusive browser journeys, file uploads, invite expiry/wrong-account/repeated acceptance, participant changes, and match/training updates and deletions.

## Recommended implementation order

1. Isolate test configuration; correct chat authorization and membership revocation.
2. Fix controller/Gate integration and scoped player notes together.
3. Fix profile updates and invitation states/session handling.
4. Complete formation pages, validation, editing, and deletion.
5. Repair the existing suite, add throttling, remove unsafe DOM/session logging, and consolidate membership rules.
6. Finish theme/build setup, email delivery, reminders if required, and browser/mobile acceptance testing.

Release gate: all intended workflow/regression tests pass; outsiders cannot read or create unauthorized team conversations; a clean install can complete registration → create team → invite → accept → create training/match → set lineup → manage formation → update profile without server errors.

## Reproduce the audit

From the project directory:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File audit/run.ps1 -Php 'C:\Users\user\Downloads\php-8.5.12-1-nts-Win32-vs17-x64\php.exe'
```

The runner sets process-local test environment variables, bypasses the repository's PostgreSQL PHPUnit configuration, and uses in-memory SQLite. Failure exit status is expected until the defects are fixed. It writes `audit/probe-results.xml` and `audit/probe-output.txt`. Existing-suite results are in `audit/existing-results.xml`.

Limits: no interactive browser automation or mobile/visual review was available; JavaScript execution, drag-and-drop, live email delivery, upload rendering, load behavior, dependency vulnerability status, and production hosting were not verified. The HTTP server smoke test and Laravel feature tests were real executions. This is a local functional/security code review, not an exhaustive penetration test or production certification.
