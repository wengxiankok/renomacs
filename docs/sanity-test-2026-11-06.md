# Local functional and sanity test — 6 November 2026

Verdict: PASS for the exercised local enquiry and preview scenarios, with known
release gaps. No regression was observed in those scenarios. This is not proof of
production delivery, complete browser compatibility or absence of all defects.

Branch: `renomacs/v2`. Existing uncommitted changes preserved. This pass adds only
a repeatable frontend test and this report; application code was not changed.
No commit, push, deployment or live enquiry submission was performed.

| Area | Result |
| --- | --- |
| Real PHP handler, intercepted upstream | 36 checks passed |
| WordPress integration and isolation | 7 checks passed |
| Local/disabled/production preview guards | 11 checks passed |
| Page, asset, 404 and enquiry HTTP checks | 22 passed in success mode and 22 in error mode |
| Repeated page requests | 20 successful requests per HTTP run; not a load test |
| Frontend behaviour with DOM/network doubles | 11 scenarios passed |
| PHP syntax | 17 files passed |
| Git whitespace check | Passed; line-ending normalization warnings only |
| Docker | Database and WordPress healthy; web port bound to 127.0.0.1:8080; no published DB port |

Frontend scenarios execute the actual inline script extracted from the theme:
invalid-form guard, one pending request despite repeated submit events, success
focus, HTTP 400/429/503 errors, HTTP 500 with a misleading success body, HTTP 200
with an error body, malformed/null JSON, offline rejection, and timeout abort.
Failures preserve the form and restore controls; there is no automatic retry.
These use DOM/network doubles, so they complement actual browser testing.

Chrome observations:

- Empty submission was blocked and focused the name field.
- Desktop pointer clicks checked consent and successfully submitted the simulator.
- At 390 x 844, pointer submission produced the configured simulated error, kept
  entered details, focused the error, and restored Submit. No horizontal overflow
  was observed at that width.
- The previous pointer problem did not reproduce in this run. This does not rule
  out overlap at other scroll positions, viewports, or on physical touch devices.
- The page title remains empty. Existing mobile menu/CTA accessibility work remains
  outstanding; this pass did not change those components.

Isolation: the WordPress HTTP and mail guards passed; real-handler tests substitute
all upstream responses. Tests used invented details. The local simulator was
returned to success mode and the browser viewport override was reset.
Browser CDN requests and links are not blocked by the WordPress server guards;
external contact links were not activated.

Repeat the new checks without installing dependencies (requires Node):

```powershell
node dev/local/tests/contact-form.test.cjs
```

Remaining release gates: staging Apps Script/field-contract verification and actual
receipt, production-compatible content/plugins, mobile navigation and CTA fixes,
SEO title and accessibility work, real-device/multi-browser testing, performance,
backup/restore and coordinated secret rotation. See `release-readiness.md`.
Local simulation bypasses server validation; passing simulation alone never proves
that an invalid enquiry would be accepted by the real handler or that a lead arrived.
