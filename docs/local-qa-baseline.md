# Local QA baseline — 5 November 2026

## Decision

The Docker setup is usable for controlled development and regression testing.
The current theme is **not ready for a production v2 release**. In particular, the
form loses entered details on a server error and gives no useful feedback.

Baseline: `renomacs/v2`, commit `be7e9b2`, clean at the beginning of this review.
Local Git tracks `origin/renomacs/v2` at the same recorded commit. This review did
not fetch GitHub or deploy anything. All submissions used invented data on localhost.

## Executed checks

| Area | Result | Evidence / limits |
|---|---|---|
| Docker services | PASS | WordPress and MariaDB healthy; web port bound to 127.0.0.1:8080; database port not published to host |
| WordPress smoke suite | 7 PASS | Active theme, contact template, noindex setting, mock success/error, HTTP/mail blocking |
| Guard unit suite | 11 PASS | Success/error/unknown/missing mode; unrelated route and method passthrough; HTTP/mail/indexing guards; disabled and production environments register no simulator hooks |
| HTTP regression suite | 22 PASS in each mode | Executed separately in success and error modes: four pages, simulator notices, absence of old browser secret assignment, form rendering, six assets, missing-page 404, simulated POST, 20 sequential contact-page requests per run |
| Repeated startup | PASS | Rerunning start-local.ps1 preserves page IDs, slugs, statuses, content; seed reports existing data preserved |
| Container restart | PASS | Restarted this project's database and WordPress; identical page snapshot afterward; health recovered |
| PHP syntax | PASS | All 16 PHP files in the custom theme and local development tools linted inside the PHP 8.3 container |
| PHP diagnostics | PASS within observation window | No /tmp/renomacs-debug.log existed when inspected; this is not a production log audit |
| Browser required fields | PASS | Empty submission remains on form; name, phone, email and consent are invalid |
| Browser simulated success | PASS | Form hides and explicit local simulation completion appears |
| Browser simulated HTTP 503 | FAIL — existing theme defect | Inputs reset, no inline error/alert appears, form stays visible, spinner clears |
| Responsive form width | PASS for overflow only | No document horizontal overflow at 320, 390, 768 and 1440px; single column through 768px, three columns at 1440px |
| Mobile menu semantics | FAIL | Toggle opens, but aria-expanded remains false; placeholder Logo remains |
| Footer quote action | FAIL | Mobile Get a Quote control is a DIV without a link |
| Local page title | FAIL | Contact document.title is empty; theme relies on capabilities not supplied by this minimal installation |
| Image alternatives | FAIL | Both header/footer logos lack alt attributes on the inspected contact page |

The responsive checks are a Chromium sample, not a complete device matrix or a
WCAG audit. The floating WhatsApp control occupies the lower form area on mobile;
the development banner also takes screen space. Review overlap and keyboard focus
at the top of the page, after scrolling, and with the virtual keyboard during UX work.

## Reproduce

From the repository root with Docker Desktop running:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\start-local.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\test-local.ps1
docker compose run --rm --no-deps cli eval-file /opt/renomacs/tests/smoke.php
```

The HTTP test fixes the hostname to localhost. Use `-Port` if the configured port
differs from 8080. It does not follow a live-site testing workflow.

To reproduce the failure in the browser, temporarily set the local simulator:

```powershell
$env:RM_LOCAL_CONTACT_RESULT = 'error'
docker compose up -d --wait wordpress
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\test-local.ps1 -ExpectedMode error
```

Open `/contact-us/`, enter invented valid details, consent and submit. Expected:
details retained and an understandable error. Actual baseline: fields clear silently.
Restore the default afterward:

```powershell
Remove-Item Env:RM_LOCAL_CONTACT_RESULT
docker compose up -d --wait wordpress
```

During this assessment error mode was exercised in both the browser and the HTTP
suite. Success mode was then restored and confirmed by a local HTTP POST returning
mock success. The viewport override was also reset.

## Boundaries and remaining risks

- The simulator bypasses the real PHP enquiry handler. Passing mock checks does not
  establish server validation, Apps Script compatibility, email delivery or lead storage.
- The unchanged production handler still disables TLS verification, logs payloads
  and secrets, performs weak validation, and lacks robust upstream error handling.
  These remain high-priority code findings from the initial assessment.
- The old browser secret reference was removed from the local theme. This does not
  rotate the exposed production secret or remove cached production copies.
- Sample pages are not the live homepage. Project photographs, actual content,
  Lazy Blocks definitions and production plugins are missing. Homepage visual and
  performance comparisons would be misleading until those are recovered.
- Third-party Choices.js still loads in the browser. The WordPress HTTP guard does
  not intercept browser requests or arbitrary custom socket/cURL code.
- External WhatsApp, phone, email and social links remain real; none were used for testing.
- No real upstream contract tests, load/soak tests, Core Web Vitals measurement,
  backup restoration, fresh-volume rebuild, multi-browser or screen-reader audit
  was performed in this pass. Restart persistence is not a backup/restore test.
- Raw logs and credentials were not printed or added to Git. Local .env and runtime
  data remain excluded. This report does not certify plugin security or production stability.

## Next delivery slices

1. **Reliable enquiry feedback:** preserve data on every failure, useful accessible
   messages, correct HTTP handling, prevent duplicate requests, focus success/errors.
   Retest success, 503, invalid JSON, network failure and rapid repeated activation.
2. **Reliable server processing:** validation/consent, safe configuration failure,
   TLS, redacted diagnostics and abuse controls. Test the actual handler against
   intercepted upstream responses before any real delivery test.
3. **Faithful content baseline:** recover sanitized content/media/block/plugin
   configuration; establish an explicit import and backup process.
4. **Credibility and lead-generation UX:** real project evidence, service/coverage
   clarity, clearer calls to action, shorter mobile journey and accessible navigation.
   Treat design changes as hypotheses measured against quote completion and contact
   intent, not aesthetic changes alone. Do not invent testimonials or project metrics.
5. **Measurement and release:** verify analytics events without personal data;
   distinguish WhatsApp clicks from actual enquiries. Establish performance,
   accessibility and delivery acceptance criteria before staging/release.

Future functional and UX changes should add focused regression coverage to this
baseline. Fixing the current failures is required before calling v2 production-ready.
