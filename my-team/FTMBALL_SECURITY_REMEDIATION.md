# FTMBALL Composer Security Remediation

Date: 2026-11-16
Branch: `fix/ftmball-audit`

## Outcome

All Composer production dependency advisories reported before remediation were resolved. The definitive registry-backed command `composer audit --no-dev --format=json` now returns an empty `advisories` array. No advisory was ignored and Composer security auditing remains enabled.

The update stayed on Laravel major version 13. Only `composer.lock` changed; the constraints in `composer.json` already permitted the patched releases.

## Affected package versions

| Package | Before | After | Resolved advisory IDs |
|---|---:|---:|---|
| `laravel/framework` | 13.0.0 | 13.42.0 | PKSA-yxbx-3c9s-cjt9, PKSA-b41t-wbzk-9q51, PKSA-4hy6-m98j-dz9f, PKSA-d5tc-s1qs-h781, PKSA-m5cs-t1y6-qpcs, PKSA-3r5d-mb8f-1qw9, PKSA-mdq4-51ck-6kdq |
| `guzzlehttp/guzzle` | 7.10.0 | 7.16.1 | PKSA-gcrk-3vtt-1r14, PKSA-cnw1-2ytm-cgr8, PKSA-fy2t-3c5f-827y, PKSA-qxvb-2bpp-dnk6, PKSA-bbs6-q5q9-f3t4, PKSA-bcdd-5xc7-gwfb, PKSA-pwsk-hy21-4gby, PKSA-93qv-9n9h-6k6p, PKSA-k22t-f949-t9g6 |
| `guzzlehttp/psr7` | 2.9.0 | 2.13.3 | PKSA-vznr-tgp9-fd7d, PKSA-7qs6-zvnz-h66r, PKSA-gm5x-j3mz-71n9, PKSA-jj5t-2zs1-dcfm |
| `league/commonmark` | 2.8.1 | 2.10.7 | PKSA-m2dq-1fhr-29b1, PKSA-m4t9-vsgq-8khn, PKSA-zyf5-hrxv-hrd7, PKSA-nv44-1b4d-6gjg, PKSA-kr3s-894t-g5w2, PKSA-9q1p-3s19-bp1q, PKSA-5mzr-szzf-z6cn, PKSA-cqd6-fg4n-nxpf, PKSA-1q6p-sqkj-8mmj, PKSA-mc58-w91n-f5gv, PKSA-t21r-vtr5-3mdz, PKSA-scnn-p8mm-jbft, PKSA-21fb-n1x5-5nf7 |
| `league/flysystem` | 3.32.0 | 3.36.0 | PKSA-w9tt-7782-78jx |
| `symfony/http-foundation` | 7.4.7 | 7.4.23 | PKSA-nxjs-s6hn-vb6m, PKSA-24ph-xkrh-y79j, PKSA-pr6q-2w5s-kzzf, PKSA-b2gg-vk1g-6d5q, PKSA-xmwy-rmx5-m7h4, PKSA-y6py-qpv1-h52p |
| `symfony/http-kernel` | 7.4.7 | 7.4.23 | PKSA-3gxv-7xhq-mgmz, PKSA-dw7n-x7f5-zf63 |
| `symfony/mailer` | 7.4.6 | 7.4.22 | PKSA-28rh-rzzn-djk4 |
| `symfony/mime` | 7.4.7 | 7.4.22 | PKSA-wtxr-p26d-nn42, PKSA-2n2k-66v2-bwg3 |
| `symfony/polyfill-intl-idn` | 1.33.0 | 1.43.0 | PKSA-dwsq-ppd2-mb1x |
| `symfony/routing` | 7.4.6 | 7.4.23 | PKSA-y8c5-1b7f-r1gj, PKSA-bf7t-jnpz-492k, PKSA-yc7t-91v9-99xs |

Direct packages were also updated within existing compatible constraints: `laravel/sanctum` 4.3.1 to 4.4.0 and `laravel/tinker` 3.0.0 to 3.1.0. Composer updated related transitive packages as required.

## Compatibility assessment

- Framework major remained Laravel 13.
- Symfony remains on its patched 7.4 series and Guzzle remains on its patched 7.x series. This avoids unnecessary transitive major upgrades and preserves the PHP `^8.3` compatibility declared in `composer.json`.
- Package discovery completed locally and during the production Docker build.
- Existing application, security, invitation mail-failure, chat, formation, avatar, and workflow tests all passed.
- No application source refactor was required for the dependency update.
- No vulnerability without a compatible fix remained.

## Verification results

| Check | Result |
|---|---|
| `composer validate` | PASS |
| `composer audit --no-dev --format=json` | PASS — zero advisories, zero abandoned packages |
| `php artisan test` | PASS — 56 tests, 337 assertions |
| `audit/run.ps1` | PASS — 20 tests, 55 assertions |
| `php artisan route:list` | PASS — 60 routes |
| `php artisan view:cache` | PASS |
| `npm.cmd run build` | PASS — 62 modules transformed |
| `docker compose config --quiet` | PASS |
| `docker compose build` | PASS |
| Docker package discovery | PASS — 4,814 authoritative classes generated |
| Recreate application container only | PASS |
| Docker health | PASS — healthy |
| Laravel `/up` | PASS — HTTP 200 |
| Laravel runtime version | 13.42.0 |
| Migration status | PASS — all migrations remain applied |
| SQLite volume persistence | PASS — inode and size unchanged (`106770:200704`) |
| Upload volume persistence | PASS — file count unchanged (0 before and after) |

The local Docker environment remains intentionally configured as `APP_ENV=local` with debug enabled. That is not a production configuration result.

## Remaining advisories

None reported by Composer at verification time. Future releases must continue to run registry-backed dependency auditing because advisory data can change without a code change.

## Remaining production blockers

This remediation removes the Composer vulnerability blocker, but does not by itself make the application publicly deployable. Outstanding release gates documented in `FTMBALL_PRODUCTION_READINESS.md` include:

- trusted proxy and HTTPS validation;
- production APP_ENV/APP_DEBUG/APP_URL/secure-cookie configuration;
- verified off-host backup and restore procedures;
- explicit production migration workflow;
- PostgreSQL runtime and migration verification if PostgreSQL is selected;
- Gmail SMTP verification from the eventual host;
- stronger password/account recovery policy and shared sessions for scaling;
- full Git-history secret scanning and removal of tracked generated artifacts.

No database reset, destructive migration, volume deletion, email transmission, secret modification, commit, push, merge, or deployment was performed.
