# Recovering the real site for local development

The theme repository is not a complete WordPress site backup. The current local
homepage and policy pages are fixtures. Import is pending a backup from the
existing developer; no production data has been imported.

## Request to the existing developer

Please provide a dated backup of renomacs.my for an isolated local development
copy. We do not need a deployment or any changes to the live site.

- A sanitized WordPress database export containing all public pages and posts,
  page/block content, menus, template assignments, homepage settings, theme
  settings, media metadata, and Lazy Blocks definitions. Preserve serialized data
  correctly. Remove enquiry/customer records, unrelated private content, active
  sessions, production credentials and integration tokens. Replace user account
  details/passwords with development-only equivalents, preserving author IDs where
  needed. Do not send live login credentials.
- The matching `wp-content/uploads` files needed by those pages, including project
  photography and generated image sizes. Exclude private uploads and backups.
- The exact required plugins and versions, including Lazy Blocks, plus its JSON
  block export if available. Include any custom plugins, must-use plugins or
  drop-ins separately for review; identify license requirements without sending
  production license keys.
- A copy of the currently deployed RenoMacs theme so we can compare it with our
  repository. Identify any server-only modifications and the deployed commit if
  known. We will preserve our v2 changes rather than overwrite the working tree.
- WordPress, PHP and database versions; database table prefix; permalink settings;
  a list of public page URLs; and any redirects or server configuration needed for
  the site. Provide a redacted configuration summary, not a credential-bearing
  `wp-config.php` or `.env`.
- Separately, the Apps Script source/field contract and instructions for creating
  a staging endpoint and destination sheet. No production secret is needed for the
  local simulator.

Preferred format: a SQL database export plus separate ZIP archives of uploads,
plugins and deployed theme, with an inventory and backup date. If only a hosting
backup or migration-plugin archive is available, identify the format and required
restore tool/version first. WordPress Tools > Export XML alone does not recreate
all settings, plugins and media files.

Use a private transfer link with restricted access. Please flag anything that
could not be sanitized before transfer. Keep the live site unchanged.

## Local intake and restore sequence

1. Save the incoming files under `.local/imports/` (ignored by Git). Do not commit
   backups, production configuration or uploaded customer data. Inspect archive
   entries and SQL before extraction or execution; reject paths escaping the
   intended destination. Inventory files without printing secret values.
2. Verify the local rollback snapshot exists and is nonempty. Keep current named
   volumes and the current theme working tree intact. Prefer a separate restore
   database/volume until the imported copy passes checks.
3. Review custom plugins, must-use plugins, drop-ins, stored scripts, analytics and
   external integrations before activating or rendering imported content. Disable
   production email, cron, webhooks, analytics, pixels and live enquiry forwarding.
   WordPress HTTP/mail guards do not intercept direct socket calls or browser
   tracking scripts. Keep imported plugins inactive until reviewed.
4. Restore data with compatible WordPress/PHP/plugin versions. Use serialization-
   aware URL replacement for the verified production URL variants. Keep local
   home/site URLs, noindex and local-only credentials. Do not copy production
   wp-config.php or overwrite the local guard plugin.
5. Preserve the imported homepage, menus and template assignments. Adapt startup
   checks to distinguish imported content from sample fixtures; the current seed
   script intentionally refuses to seed over real pages. Do not bypass that check
   by deleting imported pages or mark fixtures complete to conceal a failed import.
6. Restore reviewed media and Lazy Blocks definitions; activate only required,
   reviewed plugins. Compare the deployed theme with the repository and reconcile
   intentional differences while preserving v2 changes.
7. Inventory all pages and compare with the developer's public URL list. Verify
   homepage sections, service/landing pages, project galleries, policies, menus,
   imagery and internal links on localhost. Record missing content explicitly.
8. Repeat enquiry isolation and regression checks. Test real layouts at desktop,
   tablet and mobile sizes; keyboard and pointer navigation; CTAs; images; browser
   console/network failures; and page titles/metadata. No live forms are submitted.

Completion means a traceable page/media/plugin inventory and tested faithful local
copy, not merely a successful database import. Actual lead delivery remains a
separate staging task.
