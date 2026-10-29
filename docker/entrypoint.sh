#!/bin/sh
set -eu

mkdir -p storage/framework/cache/data storage/framework/sessions \
    storage/framework/views storage/logs bootstrap/cache
mkdir -p storage/app/public

# Chown mount points only: recursively touching uploads on every start is slow.
for directory in storage/app/public storage/framework/cache/data \
    storage/framework/sessions storage/framework/views storage/logs bootstrap/cache; do
    chown www-data:www-data "$directory" 2>/dev/null || true
done

exec "$@"
