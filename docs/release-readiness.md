# Release readiness - updated 8 November 2026

Status: development checkpoint pushed on `renomacs/v2` at `a0a49b2`; not merged or
approved for deployment. A follow-up documentation commit records the subsequent self-review corrections. See [merge release notes](v2-merge-release-notes.md) for the complete
comparison with `main` and the recorded validation results.

## Completed

- Isolated Docker preview and enquiry simulator.
- Enquiry frontend/server hardening and repeatable local regression checks.
- Public pages, menus, required Lazy Blocks definitions and media restored in the
  current local volumes. These assets are not included in Git.
- HTTP/source evaluation of all four public pages; defects recorded in the
  [website evaluation](website-evaluation-2026-11-08.md).

## Before release approval

1. Resolve the high-priority navigation, quote CTA and scroll-listener defects;
   address page titles, accessible names, service/policy copy and other findings.
2. Test the restored pages in real browsers at mobile/tablet/desktop sizes, with
   pointer, keyboard, touch and zoom. Verify form validation, selections, delayed
   responses, failure recovery and focus. Source doubles do not replace this.
3. Review Apps Script source and test a separate staging endpoint/destination.
   Verify the strict response/field contract, receipt by the designated team,
   failure visibility and duplicate handling. Local tests have never proved delivery.
4. Coordinate rotation of the previously exposed/logged secret and review historical
   logs/caches. Confirm production WordPress/PHP/plugins, TLS, proxy addresses and
   hosting-level abuse controls. Do not disclose or commit credentials.
5. Obtain business approval for policies, contact details, claims, service coverage
   and response promises. Define privacy-aware lead/WhatsApp measurement without
   recording enquiry contents in analytics.
6. Verify staging/production titles, metadata, canonicals, sitemap, redirects and
   indexability; review dependencies and performance. Local noindex is intentional.
7. Confirm deployment ownership and any external main-branch hooks. Define the
   production artifact, preserve database/media backups, rehearse restore/rollback
   and plan monitoring. Exclude local tooling/configuration from deployment.

A merge is not a deployment sign-off. Keep `main` unchanged until the agreed review
and acceptance gates are satisfied; retain and improve this theme incrementally.

## Evidence map

- [Merge notes](v2-merge-release-notes.md): scope, before/after, self-review and limits.
- [Recovery status](content-recovery-status.md): current public-data inventory.
- [Website evaluation](website-evaluation-2026-11-08.md): page findings and acceptance work.
- [QA baseline](local-qa-baseline.md): historical pre-hardening assessment.
- [6 November sanity tests](sanity-test-2026-11-06.md): historical test execution.

Run commands are maintained in [README](../README.md). The local simulator bypasses
the real callback; handler tests intercept upstream HTTP. Neither sends live leads.
