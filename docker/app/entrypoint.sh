#!/usr/bin/env bash
# Entrypoint shared by dev and prod stages.
# Responsibilities:
#   1. Fix permissions on storage/ and bootstrap/cache/ (Laravel writes here).
#   2. Wait for Oracle to accept connections (skipped if DB_CONNECTION != oracle).
#   3. In prod, run migrations once before php-fpm starts.
#   4. Hand control to the original CMD via exec.

set -euo pipefail

APP_PATH="${APP_PATH:-/var/www/html}"
cd "$APP_PATH"

###
### 1. Permissions — only fix if the dirs exist (early bootstrap may not have them).
###
for dir in storage bootstrap/cache; do
    if [ -d "$APP_PATH/$dir" ]; then
        # Best-effort: container runs as non-root after the chown in Dockerfile.
        # If the bind-mounted host dir has a different owner, this is a no-op.
        chmod -R ug+rwX "$APP_PATH/$dir" 2>/dev/null || true
    fi
done

###
### 2. Wait for the DB host to accept TCP connections (best effort).
###    Reads WAIT_DB_HOST/WAIT_DB_PORT (set by the compose service) — does
###    NOT use DB_* so Laravel/PHPUnit env can stay isolated.
###
WAIT_DB_HOST="${WAIT_DB_HOST:-}"
WAIT_DB_PORT="${WAIT_DB_PORT:-1521}"

if [ -n "$WAIT_DB_HOST" ]; then
    echo "[entrypoint] waiting for db at ${WAIT_DB_HOST}:${WAIT_DB_PORT}..."
    timeout=180
    until php -r "exit(@fsockopen('${WAIT_DB_HOST}', (int)'${WAIT_DB_PORT}') ? 0 : 1);"; do
        timeout=$((timeout - 2))
        if [ $timeout -le 0 ]; then
            echo "[entrypoint] timed out waiting for db. continuing anyway."
            break
        fi
        sleep 2
    done
    echo "[entrypoint] db reachable (or timed out)."
fi

###
### 3. Prod-only: run migrations on boot.
###    In dev, devs run `make migrate` themselves to keep boot fast.
###
if [ "${APP_ENV:-local}" = "production" ] && [ -f "$APP_PATH/artisan" ]; then
    echo "[entrypoint] running migrations (prod)..."
    php artisan migrate --force --no-interaction || {
        echo "[entrypoint] migrate failed — refusing to start."
        exit 1
    }
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

###
### 4. Hand off.
###
exec "$@"
