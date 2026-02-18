#!/usr/bin/env bash
set -euo pipefail

# Automated DB backup script for MySQL/Postgres/SQLite and upload to S3.
# Requires: awscli, gzip, (optional) gpg for client-side encryption.
# Environment variables used:
#  DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
#  AWS_BUCKET, AWS_PREFIX (optional), AWS_REGION
#  GPG_RECIPIENT (optional) - when set, the dump will be GPG-encrypted for this recipient
#  S3_KMS_KEY_ID (optional) - when set, enables server-side KMS encryption on S3 upload
# IMPORTANT: Provide DB and AWS credentials via environment variables or a secrets manager.
# Do NOT hardcode secrets in this script or in system crontab entries; source them from
# a secure store or instance profile.

timestamp=$(date -u +"%Y%m%dT%H%M%SZ")
prefix=${AWS_PREFIX:-backups}
bucket=${AWS_BUCKET:-}

if [ -z "$bucket" ]; then
  echo "AWS_BUCKET must be set to upload backups to S3" >&2
  exit 2
fi

# Prevent obvious placeholder values from being used accidentally.
if [ "$bucket" = "your-backup-bucket" ] || [ "$bucket" = "example-bucket" ]; then
  echo "AWS_BUCKET appears to be a placeholder value ('$bucket'); set the real bucket via secrets manager or env." >&2
  exit 2
fi

# Require server-side KMS by default. To allow unencrypted uploads (not recommended),
# set BACKUP_ALLOW_UNENCRYPTED=true in the environment.
if [ -z "${S3_KMS_KEY_ID:-}" ] && [ "${BACKUP_ALLOW_UNENCRYPTED:-false}" != "true" ]; then
  echo "S3_KMS_KEY_ID is not set. Uploading backups without KMS encryption is disallowed by policy." >&2
  echo "Set S3_KMS_KEY_ID to your KMS key id/arn, or set BACKUP_ALLOW_UNENCRYPTED=true to override (not recommended)." >&2
  exit 3
fi

filename="${prefix}/db-${DB_CONNECTION:-unknown}-${timestamp}.sql.gz"
tmpfile="/tmp/$(basename "$filename")"

echo "Creating backup to $tmpfile"

case "${DB_CONNECTION:-sqlite}" in
  mysql)
    export MYSQL_PWD="${DB_PASSWORD:-}"
    mysqldump --single-transaction --quick --skip-lock-tables -h "${DB_HOST:-127.0.0.1}" -P "${DB_PORT:-3306}" -u "${DB_USERNAME:-root}" "${DB_DATABASE:-}" | gzip > "$tmpfile"
    ;;
  pgsql|pgsql:|postgres|postgresql)
    export PGPASSWORD="${DB_PASSWORD:-}"
    pg_dump --format=plain --no-owner --no-privileges -h "${DB_HOST:-127.0.0.1}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-postgres}" "${DB_DATABASE:-}" | gzip > "$tmpfile"
    ;;
  sqlite|sqlite3)
    dbpath="${DB_DATABASE:-database/database.sqlite}"
    if [ ! -f "$dbpath" ]; then
      echo "SQLite DB file not found: $dbpath" >&2
      exit 3
    fi
    sqlite3 "$dbpath" ".dump" | gzip > "$tmpfile"
    ;;
  *)
    echo "Unsupported DB_CONNECTION: ${DB_CONNECTION}" >&2
    exit 4
    ;;
esac

if [ -n "${GPG_RECIPIENT:-}" ]; then
  echo "Encrypting dump with GPG for recipient: $GPG_RECIPIENT"
  gpg --batch --yes --encrypt -r "$GPG_RECIPIENT" -o "${tmpfile}.gpg" "$tmpfile"
  upload_file="${tmpfile}.gpg"
else
  upload_file="$tmpfile"
fi

echo "Uploading to s3://$bucket/$filename"
# Ensure AWS credentials are provided via environment, profile, or instance role.
# Do not embed plaintext AWS keys in scripts or repo.
if [ -n "${S3_KMS_KEY_ID:-}" ]; then
  aws s3 cp "$upload_file" "s3://$bucket/$filename" --region "${AWS_REGION:-us-east-1}" --sse aws:kms --sse-kms-key-id "${S3_KMS_KEY_ID}" --acl private
else
  aws s3 cp "$upload_file" "s3://$bucket/$filename" --region "${AWS_REGION:-us-east-1}" --acl private
fi

echo "Upload complete. Cleaning up local files."
rm -f "$tmpfile" "${tmpfile}.gpg"

echo "Backup finished: s3://$bucket/$filename"
