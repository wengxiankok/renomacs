#!/bin/sh
set -eu

if ! wp core is-installed; then
    wp core install \
        --url="$RM_LOCAL_URL" \
        --title="RenoMacs local preview" \
        --admin_user="renomacs-local" \
        --admin_password="$RM_LOCAL_ADMIN_PASSWORD" \
        --admin_email="developer@example.invalid" \
        --skip-email
fi

wp theme activate renomacs
wp eval-file /opt/renomacs/seed.php
printf 'Preview: %s\nAdmin: %s/wp-admin/\n' "$RM_LOCAL_URL" "$RM_LOCAL_URL"
