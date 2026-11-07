# Content recovery status — updated 7 November 2026

The four public pages, menus, block definitions and required uploaded media are
restored at http://localhost:8080/. Fresh visual/mobile interaction review is still
pending; this is not a production-release sign-off.

## Successful media import — 7 November

The user supplied the uploads directory. Its dated folders contain 482 files
(467 JPEG, 9 SVG, 6 AVIF), totaling 177,095,558 bytes. The initial scan found no
HTML challenge responses or PHP payloads; SVG scans found no script/event-handler
or external href/src matches. Only the dated folders were copied into Docker;
the supplied ithemes-security folder was excluded.

- All 74 imported attachment files exist; raster headers/dimensions validate.
- All 434 registered image variants exist (some records may reference the same file).
- All nine unique uploaded-media URLs rendered on the homepage exist and return
  image content types over localhost HTTP.
- All 16 recovered-content checks, seven WordPress/isolation checks, and 22 local
  HTTP checks pass. The latter includes 20 sequential contact-page requests.
- Existing theme changes remain intact. No production action, commit, push or
  deployment was performed. Browser automation was unavailable, so no fresh
  rendered visual or mobile review is claimed for this pass.

The sections below record the original recovery and the now-resolved media blocker.

## Recovered

Authenticated WordPress Tools > Export supplied the WXR content. Production has
four published pages: Home Page, Contact Us, Privacy Policy, Terms & Condition.
Services and coverage are homepage sections, not separate published landing pages.

The reviewed local import contains four pages, 74 attachment records, 11 published
Lazy Blocks definitions (including existing duplicate block slugs), and eight menu
items. Primary Navigation and Footer are assigned to their corresponding theme
locations. Lazy Blocks 4.2.0 matches the observed production plugin version.

Current repository theme changes are retained. Local URLs and image attachment IDs
are remapped. The stock Sample Page is a draft; the real home replaces the fixture.
Production analytics, security/cache integrations and user credentials were not
imported. WXR exports and import data remain under ignored `.local/imports/`.
The pre-import local SQL snapshot remains under ignored `.local/backups/`.

## Earlier validation — 6 November (superseded by successful import above)

The recovered-content test passes 13 page, menu, section, plugin and URL checks.
Its media check deliberately fails: 74 attachment files are missing. Rendering
warnings caused by production image IDs were resolved by remapping those IDs.
Seven WordPress/enquiry isolation checks also passed after import.

Direct media requests returned HTML challenge documents despite HTTP 200. All
98 downloaded files were invalid. The invalid host downloads were moved into
`.local/imports/rejected-media-responses/`; both mistakenly nested and correctly
placed container copies were quarantined outside the served uploads directory.
The current check reports zero HTML impostors. Earlier download-count messages
must not be interpreted as successful image recovery.

The downloader now checks response type/content and stops on a non-image response.
It has not been rerun against production after this change. The browser automation
connection remains unavailable even though the user can open the Media Library.
No hosting controls, production settings, plugins or live content were changed.

## Earlier media requirement (now supplied)

Restore browser automation access to recover media through authenticated browser
access, or obtain a ZIP of the site's `wp-content/uploads` from the hosting account
owner. Preserve its year/month directory structure and include resized images.
A full production database export is no longer required for these public pages.
Place the archive under `.local/imports/` for inspection before extraction.

After genuine media is available, copy its contents into the local uploads volume,
verify file types and referenced originals/sizes, then run:

```powershell
docker compose run --rm cli eval-file /opt/renomacs/tests/recovered-content.php
docker compose run --rm cli eval-file /opt/renomacs/tests/smoke.php
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/test-local.ps1
```

Complete desktop/mobile visual and interaction checks only after images load.
WXR does not include every production option or Yoast configuration; exact SEO,
hosting compatibility and actual enquiry delivery remain separate release checks.
