#!/usr/bin/env bash
set -euo pipefail

# Restore a DB backup previously uploaded to S3.
# Usage: ./scripts/restore-db.sh s3://bucket/path/to/backup.sql.gz [local-temp-file]
# If the backup is GPG-encrypted, ensure private key is available in gpg keyring.

src=${1:-}
local=${2:-/tmp/restore-$(date -u +"%Y%m%dT%H%M%SZ").sql.gz}

if [ -z "$src" ]; then
  echo "Usage: $0 s3://bucket/path/to/backup.sql.gz [local-temp-file]" >&2
  exit 2
fi

echo "Downloading $src to $local"
aws s3 cp "$src" "$local"

# If file is GPG-encrypted, detect and decrypt
if file "$local" | grep -qi "gpg"; then
  echo "GPG-encrypted backup detected; decrypting"
  gpg --batch --yes --output "${local%.gpg}" --decrypt "$local"
  local="${local%.gpg}"
fi

echo "Restoring using DB connection ${DB_CONNECTION:-sqlite}"
case "${DB_CONNECTION:-sqlite}" in
  mysql)
    export MYSQL_PWD="${DB_PASSWORD:-}"
    gunzip -c "$local" | mysql -h "${DB_HOST:-127.0.0.1}" -P "${DB_PORT:-3306}" -u "${DB_USERNAME:-root}" "${DB_DATABASE:-}"
    ;;
  pgsql|postgres|postgresql)
    export PGPASSWORD="${DB_PASSWORD:-}"
    gunzip -c "$local" | psql -h "${DB_HOST:-127.0.0.1}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-postgres}" -d "${DB_DATABASE:-}"
    ;;
  sqlite|sqlite3)
    dest_db="${DB_DATABASE:-database/database.sqlite}"
    gunzip -c "$local" > /tmp/restore.sql
    sqlite3 "$dest_db" < /tmp/restore.sql
    rm -f /tmp/restore.sql
    ;;
  *)
    echo "Unsupported DB_CONNECTION: ${DB_CONNECTION}" >&2
    exit 3
    ;;
esac

echo "Restore complete."
