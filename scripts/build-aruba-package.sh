#!/usr/bin/env bash

set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BUILD_DIR="$(mktemp -d "${TMPDIR:-/tmp}/wayout-aruba.XXXXXX")"
PUBLIC_DIR="$BUILD_DIR/package"
PRIVATE_DIR="$PUBLIC_DIR/_wayout"
DIST_DIR="$PROJECT_DIR/dist"
RELEASE_ID="$(date -u +%Y%m%dT%H%M%SZ)-$(git -C "$PROJECT_DIR" rev-parse --short HEAD 2>/dev/null || echo local)"

cleanup() {
    rm -rf "$BUILD_DIR"
}
trap cleanup EXIT

for command in composer npm php rsync zip; do
    command -v "$command" >/dev/null 2>&1 || { echo "Comando mancante: $command" >&2; exit 1; }
done

cd "$PROJECT_DIR"
if [[ "${SKIP_FRONTEND_BUILD:-0}" != "1" ]]; then
    npm ci
    npm run build
fi

mkdir -p "$PRIVATE_DIR" "$DIST_DIR"

for directory in app bootstrap config database lang resources routes; do
    rsync -a "$PROJECT_DIR/$directory/" "$PRIVATE_DIR/$directory/"
done

cp "$PROJECT_DIR/artisan" "$PROJECT_DIR/composer.json" "$PROJECT_DIR/composer.lock" "$PRIVATE_DIR/"
cp "$PROJECT_DIR/deploy/aruba/private.htaccess" "$PRIVATE_DIR/.htaccess"
cp "$PROJECT_DIR/deploy/aruba/run-deploy.php" "$PRIVATE_DIR/run-deploy.php"
cp "$PROJECT_DIR/deploy/aruba/run-schedule.php" "$PRIVATE_DIR/run-schedule.php"
printf '%s\n' "$RELEASE_ID" > "$PRIVATE_DIR/RELEASE_ID"

mkdir -p \
    "$PRIVATE_DIR/bootstrap/cache" \
    "$PRIVATE_DIR/storage/app/private/deployments" \
    "$PRIVATE_DIR/storage/app/public" \
    "$PRIVATE_DIR/storage/framework/cache/data" \
    "$PRIVATE_DIR/storage/framework/sessions" \
    "$PRIVATE_DIR/storage/framework/testing" \
    "$PRIVATE_DIR/storage/framework/views" \
    "$PRIVATE_DIR/storage/logs/application" \
    "$PRIVATE_DIR/storage/logs/email" \
    "$PRIVATE_DIR/storage/logs/schedule" \
    "$PRIVATE_DIR/storage/logs/testing"

composer install \
    --working-dir="$PRIVATE_DIR" \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction

rsync -a \
    --exclude='.htaccess' \
    --exclude='index.php' \
    --exclude='hot' \
    --exclude='fonts-manifest.dev.json' \
    --exclude='storage' \
    "$PROJECT_DIR/public/" "$PUBLIC_DIR/"

cp "$PROJECT_DIR/deploy/aruba/index.php" "$PUBLIC_DIR/index.php"
cp "$PROJECT_DIR/deploy/aruba/public.htaccess" "$PUBLIC_DIR/.htaccess"
cp "$PROJECT_DIR/deploy/aruba/maintenance.php" "$PUBLIC_DIR/maintenance.php"

chmod 0644 "$PUBLIC_DIR/index.php" "$PUBLIC_DIR/maintenance.php"
chmod 0755 "$PRIVATE_DIR/artisan" "$PRIVATE_DIR/run-deploy.php" "$PRIVATE_DIR/run-schedule.php"
chmod -R u+rwX,go-rwx "$PRIVATE_DIR/storage" "$PRIVATE_DIR/bootstrap/cache"
chmod 0644 "$PRIVATE_DIR/RELEASE_ID"

if find "$PUBLIC_DIR" -type f \( -name '.env' -o -name '.env.*' -o -name 'hot' -o -name 'fonts-manifest.dev.json' \) -print -quit | grep -q .; then
    echo "Pacchetto rifiutato: contiene configurazione o artefatti di sviluppo." >&2
    exit 1
fi

ARCHIVE="$DIST_DIR/wayout-aruba-$RELEASE_ID.zip"
(cd "$PUBLIC_DIR" && zip -qr "$ARCHIVE" .)
shasum -a 256 "$ARCHIVE" > "$ARCHIVE.sha256"
EXTRACTOR="$DIST_DIR/extract-wayout-$RELEASE_ID.php"
php "$PROJECT_DIR/scripts/build-aruba-extractor.php" "$ARCHIVE" "$EXTRACTOR"

echo "Pacchetto creato: $ARCHIVE"
echo "Checksum creato: $ARCHIVE.sha256"
echo "Estrattore creato: $EXTRACTOR"
echo "Release ID: $RELEASE_ID"
echo "Il pacchetto non contiene il file .env né credenziali."
