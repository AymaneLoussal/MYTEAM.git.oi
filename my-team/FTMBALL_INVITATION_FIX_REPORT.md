# FTMBALL invitation delivery and acceptance

## Diagnosis and changes

The local `.env` selects `MAIL_MAILER=log`: Laravel writes message contents to the application log and does not deliver to Gmail. The invitation link contains a bearer token. To prevent further token logging and misleading success messages, sending now refuses the log mailer before creating or updating an invitation, with a validation error explaining that a delivery transport must be configured. Previously logged links may still exist in historical logs and should be treated as sensitive; existing invitations can expire or be reissued by the coach once expired.

Account registration does not verify email ownership. Consequently, a matching dashboard account cannot safely receive tokenless review, accept or decline controls: anyone can register another person's email address. The dashboard displays pending, unexpired invitations, team, coach and expiration, and directs the invitee to the **Review Invitation** link in the email. It does not render tokens. Actual accept/decline controls remain on the token-bearing email page; the token itself proves access to the invitation, and a signed-in account must also match the invited email. The existing transactional membership service retains one-team and duplicate checks. Revoked or otherwise non-pending statuses no longer present actions, and mismatched accounts see an explanation instead of controls. Existing token routes and registration behavior remain unchanged.

This is a secure fallback, not full tokenless dashboard management. Implement verified email ownership (verification delivery, signed/expiring links, verified middleware and re-verification after email changes) before enabling it. No verification flag was artificially set. Dashboard review action remains unavailable without the emailed token. The mail transport exception path and older log entries should be reviewed as part of production hardening; Mailpit and server logs must be restricted because the email itself necessarily contains its bearer link.

Files changed: `app/Http/Controllers/InvitationController.php`, `app/Http/Controllers/DashboardController.php`, `resources/views/dashboard.blade.php`, `resources/views/invitations/show.blade.php`, and `tests/Feature/InvitationDeliveryTest.php`.

## Safe local SMTP test with Mailpit

Run a local Mailpit service bound to loopback (for example, with an already-installed Docker daemon):

```powershell
docker run --rm --name ftmball-mailpit -p 127.0.0.1:1025:1025 -p 127.0.0.1:8025:8025 axllent/mailpit
```

For local development only, update the project's `.env` without sharing its contents:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS=hello@example.test
MAIL_FROM_NAME=FTMBALL
APP_URL=http://127.0.0.1:8000
```

Remove or unset `MAIL_URL` if it overrides host settings. Clear cached config with `php artisan config:clear`, start the application on the configured APP_URL, and invite a disposable `example.test` address. Open `http://127.0.0.1:8025`, inspect the captured message and follow its Review Invitation link in a separate browser session. Confirm matching user acceptance and decline on separate invitations, expiration and wrong-account rejection. Mailpit catches messages locally; it does not deliver to Gmail. Do not expose its ports publicly. Restore local settings afterward. An existing pending invitation cannot be resent until expiration under current rules; use a different test address, or wait for expiry. No real email was sent during this work.

## Verification and limitations

`InvitationDeliveryTest`: 3 tests, 14 assertions: log transport refusal, fake mail recipient, dashboard/JSON token absence, wrong account denial, token-based decline for an unverified matching account and duplicate response. Existing security and workflow suites cover acceptance, expiry, wrong account and membership restrictions. `Mail::fake` verifies application dispatch; it is not an SMTP transport test. Audit: 20 tests, 55 assertions. Blade compilation and Vite native-loader production build passed. Actual SMTP and browser flows were not tested; Docker/Mailpit was not started. No database reset or production configuration change.
