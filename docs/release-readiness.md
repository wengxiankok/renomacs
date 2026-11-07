# Release readiness — 2026-11-05

Status: local hardening and regression work; not approved for production. Branch
`renomacs/v2`. No changes from this batch have been committed, pushed or deployed.

## Changed locally

- Enquiries retain their values after failures, expose an accessible error with
  direct contact links, and focus the success/error result. Submit is disabled
  while a request is pending, with an in-flight guard and a 45-second timeout.
- Removed payload/secret logging and disabled-TLS requests. The real callback now
  validates bounded, allowlisted fields and consent, rejects unexpected upstream
  destinations, checks both HTTP requests, and returns controlled status codes.
- Added best-effort per-address throttling (five upstream attempts per ten minutes
  of inactivity). This is not atomic, distributed abuse prevention or deduplication.

## Evidence from this batch

- 36 real-handler assertions pass in WordPress/PHP 8.3. All upstream requests are
  intercepted; no Google Apps Script requests or emails are sent.
- Seven WordPress integration checks pass; nine standalone preview guard checks
  pass in the enabled local mode.
- 22 HTTP checks pass in each simulator mode, including 20 sequential page loads
  per run. This is a small stability sample, not a load/soak test.
- All 17 PHP files in the theme and local tooling pass syntax checks.
- Chrome: simulated success hides the form and focuses the confirmation. Failure
  keeps values, focuses the error, hides the spinner and restores Submit.
- Pointer clicks were inconsistent during the failure test; keyboard submission
  worked. Reproduce manually on desktop and touch devices before marking interaction
  testing complete. The fixed local notice may interfere with viewport controls.
- The simulator was restored to its default success mode. Existing QA changes
  were preserved. See `local-qa-baseline.md` for the earlier assessment; its form
  failure findings describe the pre-fix state.

Run the new isolated handler test:

```powershell
docker compose run --rm cli eval-file /opt/renomacs/tests/contact-handler.php
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/test-local.ps1
```

The browser simulator deliberately bypasses server validation. The handler test
invokes the real callback with stubbed HTTP. Neither proves actual lead delivery.

## Before pushing this batch for review

- Review the diff and the Apps Script field contract, particularly the services
  array, consent value, and strict JSON `result: success` response.
- Extend browser coverage to delayed responses/double clicks, timeout, offline,
  malformed JSON, field validation and service selections. The in-flight guard is
  implemented but has not been tested under artificial network delay.
- Repeat the final interaction checks after resolving pointer behavior. Check
  keyboard navigation, mobile touch, zoom and screen-reader announcements.
- Run the listed regressions after any fixes, check the diff for secrets and
  accidental files, then commit/push the reviewed batch. Pushing is not publishing.

## Before publishing

1. Obtain a sanitized content/database export, required plugin versions, Lazy
   Blocks definitions and media so staging represents the actual website. The
   sample Docker homepage cannot validate production layouts or plugin compatibility.
2. Review the Apps Script source and deploy a separate staging endpoint/sheet.
   Verify one enquiry creates exactly one record and reaches the designated team;
   verify failure visibility and the services field. Add downstream idempotency
   before promising retry-safe delivery. Browser guards cannot prevent duplicates
   across tabs or retries after uncertain delivery.
3. Coordinate rotation of the previously exposed/logged secret, clean caches and
   review log access/retention without committing or printing secret values.
4. Confirm production PHP/WordPress/plugin compatibility, HTTPS/redirect behavior,
   proxy client-address handling, and hosting-level spam/rate controls. Shared
   proxy IPs can cause legitimate enquiries to share the local throttle.
5. Fix baseline navigation/CTA defects, page titles, image alternatives and mobile
   menu state. Test real content at mobile/tablet/desktop sizes in Chrome, Edge,
   Firefox and Safari, plus keyboard and screen-reader checks. Audit contrast,
   focus, broken links, image loading and performance against agreed budgets.
6. Confirm contact information, service areas, portfolio permissions, privacy copy
   and the advertised 24-hour response promise. Define success as delivered leads;
   add privacy-aware form-success and WhatsApp-click measurement without recording
   enquiry text or personal details in analytics.
7. Verify canonical URLs, sitemap, robots/noindex settings, metadata and redirects
   on staging and production. Keep local guards and sample content out of release.
8. Establish deployment ownership, artifact contents, backup and tested restore,
   rollback steps and post-release monitoring. Schedule an explicitly authorized
   live smoke test with the receiving team; none has been performed here.

Recommended order: close enquiry delivery/interaction gaps, make staging faithful,
then refine credibility and UI/UX. Release only after the staging checks and
rollback rehearsal pass; retain and refactor the current theme incrementally.
