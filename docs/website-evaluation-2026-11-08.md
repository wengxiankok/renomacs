# Restored website evaluation — 8 November 2026

Verdict: restored local content and enquiry logic pass the exercised checks, but
the site is not ready for release. This pass covers all four public pages through
HTTP, parsed HTML, source inspection and isolated functional tests. Browser access
was unavailable; fresh visual/mobile, real pointer, screen-reader and browser-console
checks remain unperformed. Earlier browser results do not substitute for this pass.

Docker was initially stopped. Docker Desktop and the existing containers were
started without replacing volumes or reseeding content. Existing uncommitted work
was preserved. Only audit tooling/documentation was added in this evaluation;
production, theme application code and imported page copy were not changed.

## Page-by-page results

| Page | Confirmed working | Findings |
| --- | --- | --- |
| Home `/` | HTTP 200; real sections render; menu anchor destinations exist; media references are local | Two H1s (hero and Service areas); Painting/Water Proofing repeat tiling copy; “ho hidden costs” typo; service descriptions depend on hover; title missing |
| Contact `/contact-us/` | HTTP 200; template and form render; simulated endpoint and isolated real-handler cases pass | Real delivery unverified; no title; lengthy form and lack of inline length guidance need usability review; advertised 24-hour response needs business confirmation |
| Privacy `/privacy-policy/` | HTTP 200; imported content and canonical link present | Default WordPress “Suggested text” remains, with comments/accounts material; copy needs to describe actual enquiry processing, Google/WhatsApp destinations and retention; title missing |
| Terms `/terms-condition/` | HTTP 200; business identity, warranty and contact text present | Mentions Google Forms while the repo uses Apps Script; confirm actual workflow and align wording; title missing; footer misspells the brand |

Privacy/terms observations are content/workflow discrepancies, not a legal compliance
assessment. The business should approve final policy wording.

## Confirmed defects and priorities

1. **High — mobile quote CTA is inert.** `footer.php:9` uses a plain DIV for “Get a
   Quote”, with no link or event handler. Change it to a link to the local-aware
   contact URL; verify keyboard, pointer and touch activation on every page.
2. **High — mobile navigation lacks accessible operation/state.** `header.php:40`
   hides the checkbox from accessibility, while labels act as open/close controls.
   `aria-expanded` is hardcoded false, and the overlay contains literal “Logo”.
   Source has no Escape, focus management or anchor-click close behaviour. Replace
   with real buttons and managed state; test tab order, Escape and same-page anchors.
   These are source findings; current touch behaviour is not browser-verified.
3. **High — scroll listeners accumulate.** `assets/js/app.js:16` calls
   `shrinkNavigation()` on every scroll; that function registers another listener at
   line 54. The actual source in an isolated event harness grew from 1 listener to
   101 after 100 events. Register once and update state directly. Rebuild the committed
   bundle and assert listener count remains constant.
4. **High before release — privacy copy is unfinished.** Imported Privacy Policy
   still includes repeated “Suggested text”. This is database content, rendered by
   `tnc-policy.php`, not a missing theme source file. Review against actual lead flow.
5. **Medium — service descriptions require hover.** `_lazyblocks.scss:145` hides
   `.card-text`; `lazyblock-renomax-full-cards/block.php:73` exposes it using
   mouseenter/mouseleave only. Cards are not keyboard controls. Keep essential
   descriptions visible or provide an accessible disclosure; test touch and keyboard.
6. **Medium — all four local document titles are absent.** `header.php:7` calls
   `wp_head()`, but the theme does not enable title-tag support in `functions.php`.
   Add theme support and verify meaningful titles. Meta descriptions are also absent
   locally; production has Yoast and its options were not cloned, so do not infer
   production SEO metadata from this local result. Canonical links exist on all four.
7. **Medium — shared accessible names and home navigation.** Header/footer logos
   have no alt attribute (`header.php:24`, `footer.php:7`); header logo is not a home
   link. The floating WhatsApp anchor has no accessible name on every page
   (`index.php`, `contact-form.php`, `tnc-policy.php`). Back-to-top is a DIV, not a
   keyboard-operable button. Add suitable names, semantics and focus styles.
8. **Medium — external Choices dependency is unpinned.** `header.php:15` and
   `footer.php:53` load unversioned CDN URLs. Select enhancement therefore depends
   on the network and a moving version. Pin/bundle it and verify native fallback.
9. **Content cleanup.** Correct Painting/Waterproofing descriptions and “ho hidden
   costs” in homepage block content; use RenoMacs consistently (`footer.php:45`
   currently says Renomax); make Service areas an H2. Confirm evidence for “500
   completed projects”, “13+ years”, certification claims and response promises.

## Functional evidence

- 16 recovered-content checks passed: four pages, homepage assignment, two menus,
  section anchors, local media URLs, 74 attachment files and 434 image variants.
- 36 real-handler assertions passed with all upstream HTTP intercepted. Covers
  field validation/consent, configuration, redirects, upstream failure, TLS and
  basic rate limiting. No live Apps Script call was made.
- Seven WordPress smoke/isolation checks passed, including both simulator outcomes,
  blocked outbound WordPress HTTP/mail and local noindex.
- 22 local HTTP checks passed in default success mode, including four page responses,
  static assets, expected 404, simulated enquiry and 20 sequential page requests.
- 11 frontend source scenarios passed with DOM/network doubles: invalid-form guard,
  pending duplicates, success, 400/429/503, misleading success status/body combinations,
  invalid/null JSON, offline and timeout. These are not full browser tests.
- Twelve distinct local src/href asset URLs extracted across the four pages returned
  HTTP 200. Registered variants were checked on disk. This is not a complete CSS
  dependency crawl or real-browser image decoding test.
- Parsed menu links have no missing target anchors or unexpected local page paths
  among these four pages. External phone, WhatsApp, social links were inspected only;
  no messages/calls/live enquiries were sent.

Audit evidence: `.local/qa-2026-11-08/` contains ignored HTTP snapshots and
`page-audit.json`. `dev/local/tests/page-markup.php` can repeat the markup analysis
against snapshots mounted read-only at `/qa`. Its report intentionally lists defects;
an exit code of zero is not an accessibility pass.

## Next acceptance checks

First fix mobile navigation/CTA, listener accumulation and accessible controls.
Then correct content and titles without starting a wholesale redesign. Once browser
access works, review all four pages at 320/390/768/1440 pixels, 200% zoom, keyboard
and touch; exercise service cards, anchor scrolling, menu closure, quote links and
both enquiry outcomes. Verify focus, clipping, contrast and real image loading.

Before publishing, separately verify staging lead receipt/deduplication, accurate
analytics events, production plugin/SEO configuration, dependency/security review,
performance budgets, backup restore and rollback. Local noindex and simulated
delivery are intentional safeguards, not production settings to copy.
