# RenoMacs local development

This repository contains the custom WordPress theme, not the full live website.
Work on `renomacs/v2` is local until explicitly committed and pushed. A Git push
does not itself establish a deployment process; no deployment workflow is configured here.

## Start the local preview on Windows

1. Install [Docker Desktop for Windows](https://docs.docker.com/desktop/setup/install/windows-install/)
   with its WSL 2 prerequisites. Restart Windows if the installer requests it.
   Start Docker Desktop using Linux containers, then reopen PowerShell.
2. From the repository root, run:

   ```powershell
   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\start-local.ps1
   ```

   The execution-policy override applies only to that PowerShell process. Review the
   tracked script before running it; no machine-wide policy is changed.
3. Open **http://localhost:8080** or **http://localhost:8080/contact-us/**.
   Admin is at **http://localhost:8080/wp-admin/**. The user is `renomacs-local`;
   its password is `RM_LOCAL_ADMIN_PASSWORD` in the generated, Git-ignored `.env`.
   Do not paste passwords into chat or commit `.env`.

The script creates local-only passwords, checks Compose, starts WordPress and MariaDB,
installs WordPress into Docker volumes, activates this theme, seeds sample pages/menus,
and runs smoke checks. Later runs preserve the existing sample content and passwords.
Initial image downloads require internet access. No host PHP, database, Node, or npm
installation is needed for this initial preview.

Port 8080 binds to **127.0.0.1 only**; MariaDB has no host port. To use a different
port, edit `RM_LOCAL_PORT` in `.env` and rerun the script. Use `localhost` in browser
URLs because that is the configured WordPress URL. The theme is mounted read-only
inside containers: edit it in your IDE, then refresh the browser.

## What this preview includes

- WordPress 7.1.3 with PHP 8.3/Apache, WP-CLI 2.12.0, and MariaDB 11.4.
- Existing theme PHP, compiled CSS/JS, fonts, logos, and favicon assets.
- Clearly labelled sample home/contact/policy pages and menus.
- A local-only enquiry simulator, with no external delivery or enquiry storage.

The sample homepage uses the existing text-page template. It is **not a redesign or
a reconstruction of the live homepage**. A faithful copy still requires a sanitized
database export, uploaded photos, the required plugins, and Lazy Blocks field/block
definitions. These are not in Git. Do not import production credentials or personal
enquiry data. The seed command refuses to populate an unrecognized existing site.

WordPress and WP-CLI image versions are explicit. The MariaDB 11.4 tag follows that
maintenance series; this is a development baseline, not a proven production match.

## Enquiry testing boundaries

Compose mounts `dev/local/preview.php` as a must-use plugin, enabled only by the
local environment flags. It intercepts POST `/wp-json/custom/v1/contact`, blocks
outbound WordPress HTTP and `wp_mail`, and displays a local-preview notice.
The production handler in `functions.php` is **not exercised by this simulator**.
No `RM_SECRET` or `APPS_SCRIPT_URL` is needed for preview.

Use invented details. To simulate an upstream failure, set
`RM_LOCAL_CONTACT_RESULT=error` in `.env`, then run:

```powershell
docker compose up -d --wait wordpress
```

Set it back to `success` to restore simulated success. The existing form's silent
failure/reset bug is not fixed by this setup; the error mode lets us reproduce it
locally in the next work item. Smoke checks test both simulator outcomes and the
HTTP/mail guards, not browser behavior or real enquiry delivery.

The guards cover WordPress HTTP/mail APIs, not arbitrary custom network code.
Browser requests are not intercepted: Choices.js still loads from its CDN, and
WhatsApp/phone/email/social links still point to real destinations. Do not use those
links for test enquiries. Plugin/update downloads via WordPress are deliberately
blocked; recover plugins separately when we prepare a faithful site copy.

## Useful commands

```powershell
# Check status or logs (review logs before sharing).
docker compose ps
docker compose logs --tail 50 wordpress db

# Rerun the local integration checks.
docker compose run --rm cli eval-file /opt/renomacs/tests/smoke.php

# Check simulator activation guards in isolated PHP processes.
docker compose run --rm --entrypoint php cli /opt/renomacs/tests/guards.php local
docker compose run --rm --entrypoint php cli /opt/renomacs/tests/guards.php disabled
docker compose run --rm --entrypoint php cli /opt/renomacs/tests/guards.php production

# Lint the changed PHP without sending enquiries.
docker compose run --rm --entrypoint php cli -l /opt/renomacs/preview.php
docker compose run --rm --entrypoint php cli -l /opt/renomacs/seed.php
docker compose run --rm --entrypoint php cli -l /opt/renomacs/tests/smoke.php
docker compose run --rm --entrypoint php cli -l /var/www/html/wp-content/themes/renomacs/contact-form.php

# Stop containers while retaining the database and WordPress files.
docker compose down
```

Do not add `--volumes`/`-v` to the stop command unless deliberately discarding the
local database and uploaded files. Keep `.env` while retaining the database volume:
changing its passwords does not change accounts in an already initialized database.
If startup fails, already-started containers may remain running; use `docker compose
ps` to inspect them and `docker compose down` to stop them without removing data.

## Frontend builds and next work

Committed compiled assets support the initial preview. JavaScript rebuilds later
require Node/npm and `npm ci` followed by `npx gulp build` inside the theme directory.
There is no Sass compiler/task yet; `gulp build` does **not** compile SCSS.

Next: fix server-side secret logging/TLS/validation and enquiry response handling,
then form recovery and measurement, followed by mobile UI/accessibility. Production
secret rotation, cached-copy cleanup, and deployment require a coordinated later release.
