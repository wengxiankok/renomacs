# V2 merge review and release notes - 8 November 2026

Proposed title: **Add isolated local WordPress development and harden enquiry handling**

This merge makes the existing theme reproducible with sample content in Docker,
adds tools for reviewing a restored public-site copy, and improves enquiry failure
handling and server validation. It is a development foundation and enquiry-hardening
checkpoint, not a website redesign or production-delivery sign-off.

## Exact comparison

Remote refs were fetched on 8 November 2026. The repository uses `main`, not `master`.

- Base: `main` / `origin/main` at `d2076a3`.
- Reviewed implementation: `renomacs/v2` / `origin/renomacs/v2` at `a0a49b2`.
- Included commits: `be7e9b2` (Docker preview/simulator) and `a0a49b2`
  (enquiry hardening, recovery checks and QA documentation).
- Implementation diff: 26 files, 1,641 insertions and 293 deletions. Only two
  existing theme files change: `contact-form.php` and `functions.php`.
- A follow-up documentation commit adds these notes and corrections; those edits are
  additional to the two pushed commits. No merge or deployment was performed.

## Behaviour before and after

| Area | Main baseline | Proposed branch |
| --- | --- | --- |
| Local setup | Theme source without a repository-managed WordPress/database preview | Docker Compose, Windows startup script, sample content and persistent volumes |
| Local enquiries | No dedicated isolation harness | Simulator intercepts the contact route; WordPress HTTP/mail blocked locally |
| Form failure | Failure could reset entered data without useful feedback | Values retained; focused error and direct-contact alternatives; controls restored |
| Pending requests | No equivalent in-flight protection | Disabled Submit, busy state, duplicate guard and 45-second client abort |
| Browser secret | Template emitted a server secret into browser JavaScript | Browser sends form fields to WordPress; secret stays server-side |
| Server transport | TLS verification disabled; payload and response logging; loosely controlled forwarding | Verified TLS, allowlisted destinations, bounded responses and controlled errors |
| Input validation | Required-field presence checks | Type/length checks, sanitization, valid email/phone/date, allowed services/project type and required consent |
| Delivery result | Upstream JSON passed through | Success requires 2xx plus JSON `result: success`; public response exposes only the agreed result |
| Recovery and QA | No corresponding repository tooling | Site-specific importer, recovery checks, handler/frontend tests and documented findings |

## Included work

### Local infrastructure and safeguards

`compose.yaml`, `.env.example`, `scripts/start-local.ps1` and
`dev/local/bootstrap.sh` provide WordPress 7.1.3/PHP 8.3, WP-CLI 2.12.0 and
MariaDB 11.4. Startup generates ignored development credentials, checks Docker,
starts services, installs WordPress if absent, activates the theme and runs smoke
checks. Existing seeded/restored content is preserved on subsequent startup.

The web port binds to loopback; the database has no published host port. Theme and
local tools are mounted read-only into containers. Named volumes preserve runtime
data. Compiled theme assets allow preview without host PHP or Node; Node is needed
for frontend tests/builds. The existing build does not compile SCSS.

`dev/local/preview.php` requires both local flags before activating. It simulates
success/error, blocks WordPress HTTP/mail, forces local noindex and labels the
preview. Cron is disabled by local configuration. These are API-level guards, not
network isolation: browser/CDN requests and arbitrary socket code are not blocked.
WhatsApp and other external links still have real destinations.

`.gitignore` excludes local credentials, runtime data, plugins, uploads, exports,
logs and dependencies. `.gitattributes` preserves Linux-compatible script endings.

### Enquiry frontend

`wp-content/themes/renomacs/contact-form.php` replaces the previous submission
script, removes obsolete commented code/browser-secret exposure and uses the
WordPress-generated REST URL. Failure retains values, focuses an alert and offers
phone/WhatsApp alternatives. Success focuses the confirmation. Pending state,
spinner and button are restored in `finally`; there is no automatic retry.
A no-JavaScript message provides a phone fallback; there is no server-rendered
form-submission fallback.

The timeout stops the browser waiting; it cannot cancel a request already processed
by the server or Apps Script. The failure wording therefore avoids claiming that
nothing was delivered. In-flight protection is per page, not delivery deduplication.

### Enquiry server

`wp-content/themes/renomacs/functions.php` keeps the public POST route and adds:

- Allowlisted fields with individual limits and a raw-body size check; sanitization
  and validation before forwarding; unknown fields are not forwarded.
- Server-only secret injection and a required HTTPS Apps Script deployment URL.
- Best-effort per-address throttling: five forwarding attempts, with the transient
  expiry renewed on each allowed attempt. It is not a fixed-window atomic quota.
- Safe WordPress HTTP requests, TLS verification, 20-second timeout per request,
  response-size limit, and no automatic redirects.
- At most one 302 redirect to `script.googleusercontent.com`, followed by GET without
  the enquiry body or secret.
- Controlled 400/413/429/503/502 errors and a minimal 200 success response; removed
  application payload/secret/upstream-response logging.

The stricter contract can reject traffic accepted by main. Deployment must supply
valid server configuration, verify the existing Apps Script field/response contract,
and confirm host TLS/proxy behaviour. Removing logging does not erase historical
logs or rotate a previously exposed secret.

### Public-site recovery (local data, not merge contents)

The current developer volumes contain four public pages (home, contact, privacy,
terms), eight menu items, 11 published Lazy Blocks definitions and 74 attachment
records. Lazy Blocks 4.2.0 was installed locally. The supplied dated uploads contain
482 files; all 434 registered variant references were found. Services and coverage
are homepage sections, not newly created landing pages.

`dev/local/restore-content.php` imports a reviewed manifest, remaps local URLs and
attachment IDs, assigns menus/homepage and marks imported records for repeat runs.
`quarantine-invalid-media.php` is a manual local recovery utility for HTML responses
mistaken for images; it is not part of startup or production execution.

The WXR/manifest, plugin installation, database and media are not in Git. A fresh
clone creates sample pages; it does not automatically recreate this restored copy.
This is not a full production clone: production options, Yoast settings, integrations
and exact deployed-theme parity remain unverified.

### Tests and documentation

The branch adds WordPress integration/guard checks, intercepted real-handler tests,
frontend source tests using DOM/network doubles, local-only HTTP smoke tests,
restored-content/media checks and a parsed-page audit. Reports distinguish past
baseline failures, recovery evidence and outstanding website defects.

## Self-review: redundancy, accuracy and remaining weaknesses

1. **Documentation drift corrected in this review.** README still described the
   form-reset bug as unfixed; the recovery handoff said no content was imported;
   release readiness said the work was uncommitted. Current guidance now points to
   this comparison and a shorter release gate list. Dated QA reports remain as
   historical evidence rather than being rewritten as new test runs.
2. **Removed dead code is useful cleanup.** The replaced theme code contained
   obsolete commented handler/submission implementations. Their removal reduces
   ambiguity. No unrelated theme redesign or dependency upgrade is included.
3. **Tests overlap intentionally, but are not independent proof of delivery.**
   Simulator checks exercise local isolation, handler checks exercise callback logic
   with intercepted HTTP, and frontend doubles exercise script state transitions.
   Counts must not be combined into a claim of full end-to-end coverage.
4. **Recovery is site-specific and partly manual.** The importer assumes source
   home/privacy IDs 2/3, a pre-reviewed manifest and the known flat menus. It does
   not preserve nested menu parent relationships, provide a generic WXR converter,
   or perform a transactional rollback. Re-running it can overwrite imported local
   edits. Review and back up before reuse; do not treat its regex checks as a complete
   content sanitizer. Full fresh-machine restoration is not yet automated.
5. **Test coverage gaps remain.** Current handler assertions do not directly exercise
   the raw-body 413 branch, every field boundary, multipart serialization end to end,
   or concurrent quota behaviour. The body-size check is not an infrastructure-level
   upload limit. Frontend doubles do not test real FormData or Choices behaviour.
   Recovered-media checks verify existence/headers, not every rendered image or all
   production records. The page audit reports defects without failing its exit code.
6. **Abuse and duplicate controls are limited.** Shared proxy/NAT addresses can share
   a quota; concurrent transient updates can race. No downstream idempotency,
   delivery reconciliation or production monitoring is added.
7. **Existing UI/content defects remain.** The inert mobile quote CTA, inaccessible
   mobile controls, accumulating scroll listeners, hover-only descriptions, missing
   local titles/accessibility names and unfinished policy/service copy are documented
   in the website evaluation. They are not fixes supplied by this merge.

## Validation evidence and limits

Checkpoint validation at `a0a49b2`, recorded on 8 November:

| Check | Result | Boundary |
| --- | --- | --- |
| Restored content/media | 16 passed | Current local volumes only |
| Real contact callback | 36 assertions passed | All upstream HTTP intercepted |
| WordPress smoke/isolation | 7 passed | Local configuration and simulated outcomes |
| Local HTTP | 22 passed | Default success mode; includes 20 sequential contact-page loads |
| Frontend source behaviour | 11 scenarios passed | DOM/network doubles, not a browser |
| PHP syntax | 21 files passed | Syntax, not runtime compatibility across hosting versions |

Earlier dated reports include error-mode HTTP checks and preview activation checks.
These are separate historical runs. This documentation review does not relabel them
as freshly executed tests. Fresh restored-page mobile/desktop interaction, screen
reader, browser console, performance/load and real staging delivery remain open.
No live enquiry was submitted. Passing the listed checks is not a whole-site sign-off.

## Merge and release recommendation

Keep this as a reviewed branch checkpoint until the agreed acceptance work is
complete. At minimum, close the high-priority website defects, complete real-browser
coverage, and verify staging delivery before treating the branch as release-ready.
If the team deliberately merges a development foundation earlier, it must explicitly
remain non-deployable; a Git merge alone is not production approval.

No deployment workflow is configured in this repository, but external hosting hooks
have not been ruled out. Confirm what a push/merge to main triggers before merging.
For deployment, verify secret rotation, production/plugin compatibility, SEO settings,
artifact contents, backup/restore, rollback and monitoring. Never deploy the local
Compose configuration, guard plugin, sample content or ignored production exports.

Use [release readiness](release-readiness.md) for the remaining gates,
[website evaluation](website-evaluation-2026-11-08.md) for specific page defects, and
[recovery status](content-recovery-status.md) for the imported-data inventory.
