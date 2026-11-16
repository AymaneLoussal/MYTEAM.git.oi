# FTMBALL UI/UX implementation

Branch: `fix/ftmball-audit`. Changes are uncommitted. Existing staged lockfile and release notes were preserved. Welcome changes were edited in place, retaining the existing hero, sections, imagery and footer rather than replacing the file.

## Implemented

- Shared charcoal/emerald visual layer with professional typography, dark controls, consistent borders, focus outlines, card surfaces and reduced-motion support.
- Shared navigation on standalone pages and the application layout: overview, teams, matches, training, formations, chat and account; route-based active states and POST/CSRF sign-out.
- Responsive native-details menu with Escape handling, close-on-navigation and keyboard-accessible controls; skip-to-content links and scroll offsets.
- Removed conflicting fixed dashboard sidebar and its content offset. Mobile users now have the same navigation destinations as desktop users.
- Welcome header no longer contains fake notification/settings buttons or a misleading registration link labeled Dashboard. CTAs use named routes and send signed-in users to their dashboard. Added section IDs, working footer links and a three-step product explanation. Reduced oversized hero height/type for smaller screens and replaced unsupported analytics/scouting claims in the hero copy.
- Applied shared dark surfaces and text to match, lineup, training, formation and chat views. Existing team, invitation, dashboard, account and authentication pages now share navigation. Backend actions and policies were not changed.
- Form label associations and scrollable table regions added through the existing Vite JavaScript entry. Existing clickable dashboard/formation cards receive keyboard link semantics.
- New Blade components: navigation, card, badge, button and empty-state. Navigation and cards are integrated; badge/button/empty-state are available for further page-by-page adoption. Existing feedback component remains in use.

This is a shared-design-system and navigation pass, not a completed bespoke redesign of every screen. Forms, page content and existing functional actions were retained.

## Verification

- `npm run build` initially failed because esbuild could not read a parent directory in this restricted environment. `npm run build -- --configLoader native` succeeded with Vite 7.3.8: 61 modules, fresh manifest, CSS and JavaScript assets.
- Blade compilation passed.
- Route discovery: 55 routes.
- JavaScript syntax and `routes/web.php` PHP syntax checks passed.
- Audit suite: 20 passed, 55 assertions.
- Application suite: 42 passed, 219 assertions; isolated SQLite test data.

## Remaining verification and limitations

Browser automation previously failed in this environment for both Chrome and Edge. No screenshots or measured responsive-layout results were obtained in this run. Backend feature-test success is not browser verification. SMTP delivery remains a separate release task documented in RELEASE_VERIFICATION.md.

At 375px, 768px and 1440px, verify in a normal browser:

1. Guest: welcome section anchors, menu open/close/Escape, CTAs, registration, valid/invalid login and protected redirects.
2. Coach: create team, invite through a local mail catcher, match and training create/edit, lineup save, formation drag-and-drop/coordinate controls/edit/delete, chat and profile updates.
3. Player: accept invitation, view assigned resources, chat with coach, attempt forbidden modifications, leave and confirm chat access is revoked.
4. Keyboard: tab through navigation/forms, inspect focus, activate card links with Enter, escape mobile navigation; inspect validation and success feedback.
5. Layout: inspect horizontal overflow, menu overlap, long names, tables, empty states and form widths. Test with reduced motion enabled.

Outstanding UI work: full per-page accessibility audit, native static label associations instead of JS enhancement, chat-dialog focus trapping, adoption of reusable fields/empty states on all pages, visual review of remaining marketing claims/imagery, and browser verification of the dark palette. Invitations index and admin users remain existing JSON endpoints, not newly invented administrative screens. No authorization checks were weakened and no actual database was reset.

## Final polish follow-up — 2026-11-14

Continued the existing implementation on `fix/ftmball-audit`; no new redesign, commits, deployment or database reset. The staged package-lock.json, existing welcome edits and RELEASE_VERIFICATION.md were preserved.

### Changes in this follow-up

- Navigation starts open as a no-JavaScript fallback and synchronizes with the desktop breakpoint; mobile links and Escape close the menu. Named-route families now select the correct section on nested team schedules rather than highlighting Teams incorrectly.
- Dashboard and formation cards use actual anchors, supporting keyboard activation, copy-link and opening new tabs.
- Team details now expose matches, training and formations to authorized players as well as coaches. The coach Create Formation link now opens the editor directly.
- Dark hover surfaces replace pale hover colors across chat, match, training and formation pages. Headers wrap at mobile widths; long headings wrap and dialog contents scroll within the viewport.
- Chat uses a native modal dialog with a labelled heading, labelled close control, Escape handling and native modal focus containment. Added a useful empty conversation state and static field labels.
- Match/training forms, invitation registration and lineup controls now have static label associations. Remaining legacy fields retain the JS label enhancement.
- Profile avatar selection uses a visible labelled file input and saves with the profile form, avoiding unexpected immediate submission.
- Removed the unsupported 30-day remember-me checkbox; authentication labels now say email/password instead of manager/access key.
- Shared status/error feedback is included in the layout, dashboard, team and profile screens. Existing page-level validation remains.
- Added tests/Feature/NavigationTest.php: player resource discovery, coach-action visibility, nested active navigation, chat validation response, feedback rendering and empty dialog state.

### Executed checks

- Standard `npm run build`: sandbox esbuild error `Cannot read directory "../../../..": Access is denied` and `Could not resolve ...vite.config.js`. This is not a frontend production-build blocker: the user's normal PowerShell build already succeeded, and `npm run build -- --configLoader native` succeeded again here.
- Native-loader production build: Vite 7.3.8, 61 modules. Manifest references verified existing `app-DISztomx.css` (65.26 kB) and `app-CnIkppU8.js` (60.36 kB).
- `php artisan route:list --json`: 59 routes in this environment, including framework routes. Static scan of literal named route references in every Blade file: zero missing names. This does not prove browser interaction behavior.
- `php artisan view:cache`: successful.
- `node --check resources/js/app.js` and PHP syntax check on NavigationTest.php: successful.
- Audit script: direct invocation was blocked by local PowerShell script execution policy. `powershell -NoProfile -ExecutionPolicy Bypass -File audit/run.ps1 -Php <PHP executable>` ran successfully: 20 tests, 55 assertions. No system policy was changed.
- SQLite in-memory test configuration retained; no application data reset or seeded for browser checks.

### Browser attempt and remaining verification

Started `php artisan serve --host=127.0.0.1 --port=8765`, then attempted:

```powershell
& 'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe' --headless=new --disable-gpu --disable-extensions --no-first-run --user-data-dir="$PWD\storage\app\ui-browser-check" --dump-dom http://127.0.0.1:8765/login
```

Edge repeatedly returned `GPU process exited unexpectedly: exit_code=-1073741790`, then `GPU process isn't usable. Goodbye.` It also reported encryption unavailable, registry access failures and `Access is denied. (0x80070005)`. No DOM or screenshot was obtained. The application server and browser attempt were stopped. Prior Chrome failure is recorded in RELEASE_VERIFICATION.md. Browser isolation was not disabled.

No desktop/mobile visual checks passed in this environment. Native dialog focus, menu breakpoint behavior, scrolling and colors require manual verification. One new in-process POST-then-GET test did not retain flashed validation errors; validation/session assertions and feedback-component rendering pass separately. Confirm actual redirect error display in a browser before release; do not treat that flow as verified. SMTP transport remains unverified and separate from array mail tests. No real invitation email was sent.

### Precise manual checklist (not executed)

Use a separate disposable local database and a local mail catcher, never reset the existing database. Start the local server on port 8765. Every URL below is relative to `http://127.0.0.1:8765`; substitute IDs from links created in that test database. Use separate coach, invited-player and outsider browser profiles. Repeat visual checks at 375, 768 and 1440 CSS pixels.

| URL / role | Action | Expected result |
| --- | --- | --- |
| `/`, guest | Follow Features, How it works and footer anchors; activate each CTA | Correct section visible below sticky header; CTAs reach registration; no dead links |
| `/`, all widths | Open/close Menu; press Escape; resize across 1100px | Mobile menu usable, Escape restores focus, desktop links remain visible |
| `/register`, guest | Submit invalid then valid details with a unique test email | Useful field errors; valid registration reaches authenticated application |
| `/login`, guest | Submit incorrect then correct password | Error visible after redirect; successful login reaches dashboard |
| `/dashboard`, guest | Open in a fresh browser profile | Redirect to login |
| `/dashboard`, coach | Create one team, try invalid/duplicate creation | Team created once; errors/status visible; dashboard cards work with Enter and Ctrl+click |
| `/teams`, `/teams/{team}`, members | Open roster, schedules and formations; coach saves a player note | Correct team and active navigation; player has no coach-only modification controls |
| `/teams/{team}`, coach | Invite a `.test` address with local mail catcher | Local message only; open token URL from catcher, not from public listings |
| `/invitations/{token}`, invited player | Accept/register; revisit accepted token; test expired/declined invitation | Correct distinct states; no duplicate membership; wrong account cannot accept |
| `/teams/{team}/matches/create`, coach | Create invalid then valid match; edit `/matches/{match}/edit` | Errors visible, valid fields persist, cancel/back return to intended pages |
| `/matches/{match}/lineup`, coach | Assign starters/substitutes and save; attempt over 11 starters | Labelled selectors usable on mobile; valid lineup persists, invalid one rejected |
| `/teams/{team}/trainings/create`, coach | Create then edit `/trainings/{training}/edit` | Date/time validation works; schedule/detail links and cancel work |
| `/teams/{team}/formations/create`, coach | Add roster players using keyboard controls and drag; edit then delete disposable formation | Coordinates persist, outsiders excluded, deletion returns to list |
| `/chat`, member | Open New Chat, Tab/Shift+Tab, Escape, reopen, create invalid/valid conversation | Focus stays in modal, Escape closes and restores trigger, validation errors visible after redirect |
| `/chat/{conversation}`, members | Send normal text and HTML-like text; wait for polling | Message renders as text; no duplicate messages; announcement posting restricted |
| `/profile`, member | Save name with blank password; choose avatar; test email/password change | Blank password preserves login; avatar saves only on Save; sensitive changes require current password |
| Other team's resource URLs, outsider | Attempt detail/edit URLs and mutation requests | Denied without data disclosure or unauthorized writes |
| `/teams/{team}`, player | Leave disposable team, revisit old chat/show/poll | Former-member access denied; conversation absent from list |
| Any authenticated page | Sign out, revisit protected pages | POST logout ends session; protected routes redirect |
| All above pages, each viewport | Tab through controls; inspect long names, forms, tables, modal, empty states; enable reduced motion | Visible focus, readable contrast, no overlapping controls or unwanted page overflow; table scroll is local |

Remaining release gates: complete this browser checklist (including flashed validation feedback), review final marketing copy/images, and test SMTP against a local catcher. These are not claimed complete.

Final full application rerun: **44 tests passed, 233 assertions**, including both new navigation tests (36.55 seconds). Final Blade compilation passed. The follow-up above supersedes earlier outstanding items for static labels already converted and the chat dialog implementation; browser confirmation remains pending.

## Urgent authentication layout repair

Root causes: shared navigation was included inside the max-w-sm authentication form, although its desktop breakpoint measures the viewport. Equal-width columns with large internal padding further constrained it. Unlayered global input padding overrode Tailwind icon padding. Absolutely positioned artwork/footer and fixed minimum heights competed with the card. The navigation component itself was designed for a full-width application header.

Implemented dedicated layouts/auth.blade.php with minimal home header, normal-flow two-column card, single-column mobile form, no floating artwork or icon overlays, readable labels, autocomplete, linked field errors and retained CSRF/routes/old input. Login/register transition stays inside the card. Added layouts/public.blade.php and public-navigation.blade.php; welcome content and inline styles retained while moving its document shell into that layout. Invitation pages use the minimal public header. Existing application pages retain their full-width application navigation. Auth CSS uses dedicated classes rather than positioning overrides to the old card.

Modified: auth/login.blade.php, auth/register.blade.php, welcome.blade.php, invitations/show.blade.php, resources/css/app.css. Added: components/layouts/auth.blade.php, components/layouts/public.blade.php, components/public-navigation.blade.php. No backend/auth policy changes.

Build with native Vite config loader succeeded; audit 20 tests/55 assertions; Blade compilation passed. Browser visual verification remains incomplete (prior Edge GPU launch failure). Verify /login and /register in Chrome at 375, 768 and 1440px: single header outside card, no full application menu, aligned columns, readable headings, no clipping, keyboard focus, invalid submission errors and successful login/registration. Also check welcome and invitations after the public-header separation. Automated passes are not proof of visual correctness.

## Welcome page refinement

Retained charcoal/emerald styling and the existing hero/features/how-it-works/footer structure. Added responsive public Home, Features, How It Works, Sign In and Get Started links; authenticated visitors receive Dashboard. Sign In uses an explicit light-on-dark bordered treatment. Hero typography uses fluid sizing, improved leading and bounded page width. CTAs now describe their actual destination. Replaced the coach illustration and fictional metrics with a static workspace preview explicitly labelled demo data, not an actual dashboard screenshot. Removed unsupported scouting, contract, injury, AI planning, 3D board and prediction claims; features now describe invitations, roster notes, formations, lineups and session scheduling. Removed fictional company attribution.

Modified welcome.blade.php, components/public-navigation.blade.php and resources/css/app.css. Backend and security flows unchanged. Native-loader Vite build and Blade compilation passed. No screenshot was attached to this request; no new browser capture obtained. Visual verification remains pending at 375/768/1440px, including intentionally wrapping mobile header links, CTA contrast, preview proportions, anchor destinations and footer spacing. Public navigation is also used by invitation pages, so include those in the visual check. No database reset, commits, push or deployment.
Welcome refinement application test result: 44 passed, 233 assertions (39.61 seconds).
