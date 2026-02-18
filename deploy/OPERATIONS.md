Operational Runbook

This file documents recommended operational procedures for monitoring, key rotation, and backups.

1) Monitoring & Alerting

- Sentry (Errors):
  - Add `SENTRY_DSN` to your production environment (set as secret in your provider/CI).
  - Install `sentry/sentry-laravel` in production to capture exceptions and context automatically.
  - Configure release/version in Sentry to track deployments.
  - Example (deploy): set `SENTRY_ENVIRONMENT=production` and `SENTRY_RELEASE`.
  - CI release tagging: add `SENTRY_AUTH_TOKEN` to your repo secrets and enable the CI `sentry-release` workflow to create/releases and set `SENTRY_RELEASE` during deployment. See `/.github/workflows/sentry-release.yml`.

- Papertrail (Logs):
  - Configure `PAPERTRAIL_URL` and `PAPERTRAIL_PORT` in environment variables.
  - Set `LOG_CHANNEL=papertrail` (or include papertrail in `LOG_STACK`) to stream logs.
  - Ensure UDP/TLS access from your app hosts to Papertrail is allowed.

- Queue/Job Alerting:
  - The app registers a listener to log job failures and will forward to Sentry when `SENTRY_DSN` and the Sentry SDK are present.
  - Configure alerting rules in Sentry for queued job exceptions (e.g., alert when > X failures in 1 hour).

  - Example Sentry alert rules:
    - "Notify on job failures": trigger when the event count for `error` with tag `logger:queue` exceeds 5 in 1 hour.
    - "High failure rate": trigger when the ratio of failed jobs to successful jobs exceeds 10% over a 30-minute window.
    - Add a recovery action to auto-resolve incidents when failure rate drops below threshold.

  - Avoid logging secrets: add `SENTRY_TRACES_SAMPLE_RATE` and other Sentry options via environment variables and ensure logs do not contain raw secrets. The app registers a log tap to redact common sensitive keys from records; review `app/Logging/RedactSensitiveData.php` for the redaction list and adjust as needed.

2) Key Rotation

- Principle: rotate secrets immediately when suspected leaked and on a regular cadence (e.g., quarterly).

- AWS keys:
  - Create a new IAM user or temporary credentials with least privilege.
  - Update application environment `AWS_ACCESS_KEY_ID` and `AWS_SECRET_ACCESS_KEY` in your secret store.
  - Deploy/configure hosts with new credentials; once validated, deactivate the old keys in AWS IAM and then delete them.

- Other providers (Pusher, Mail, etc.):
  - Use provider consoles to rotate API keys or create new credentials.
  - Update secret store and deploy.

- Rolling key rotation procedure:
  - Add new key to secrets manager as `SERVICE_KEY_NEW`.
  - Deploy application code/config to read `SERVICE_KEY_NEW` if present.
  - Verify functionality.
  - Replace `SERVICE_KEY` with `SERVICE_KEY_NEW` in secrets manager.
  - Remove `SERVICE_KEY_NEW`.

Rolling rotation examples

- AWS Secrets Manager (example):
  1. Create new secret value with a temporary name: `AWS_CREDENTIALS_NEW` (containing `AWS_ACCESS_KEY_ID` and `AWS_SECRET_ACCESS_KEY`).
  2. Deploy or update your config to prefer `AWS_CREDENTIALS_NEW` when present (feature-flag or runtime precedence).
  3. Verify the app can access S3 and perform operations.
  4. Promote `AWS_CREDENTIALS_NEW` to `AWS_CREDENTIALS` (update the primary secret name) and remove the `_NEW` secret.

- GitHub Actions (example using `gh` CLI):
  1. Generate new IAM key manually or with `aws iam create-access-key --user-name <user>`.
  2. Use the helper script included in `scripts/rotate-aws-iam-key-and-update-gh.sh` to set the repo secret (it can update either the access key or secret depending on the secret name you provide):

     ```bash
     chmod +x scripts/rotate-aws-iam-key-and-update-gh.sh
     ./scripts/rotate-aws-iam-key-and-update-gh.sh deploy-bot owner/repo AWS_ACCESS_KEY_ID
     ./scripts/rotate-aws-iam-key-and-update-gh.sh deploy-bot owner/repo AWS_SECRET_ACCESS_KEY
     ```

  3. Deploy and verify functionality.
  4. After verification, delete the old access key via `aws iam delete-access-key --user-name <user> --access-key-id <OLD_KEY_ID>`.

Notes

- Always rotate keys in a staged manner; do not delete old keys until the new keys are verified.
- Use least-privilege IAM policies for any rotation user/account.
- Store audit logs of key creation/deletion in a secure log channel and restrict access to the rotation scripts.

3) Backups

- Database backups:
  - Use managed database snapshots where possible (RDS snapshots, Cloud SQL backups) with point-in-time recovery.
  - For self-managed DB: schedule daily dumps, encrypt backups, and store in a separate S3 bucket with lifecycle rules.
  - Test restores regularly.
    - Use server-side encryption with AWS KMS for all backups. Store the KMS key id (or ARN) in your secret store (do not commit it).
    - Prefer a dedicated KMS key with a tightly scoped key policy allowing only the backup IAM role to use it.
    - Enforce S3 encryption at the bucket level with a policy that rejects PUTs not using `x-amz-server-side-encryption:aws:kms`.

- File storage backups:
  - S3: enable versioning and cross-region replication if required.
  - Periodically export important artifacts and store in an archival bucket.

- Key backups:
  - Do not store secrets in git. Use a secrets manager (AWS Secrets Manager, HashiCorp Vault, platform env vars).
  - Keep an offline, encrypted copy of critical keys for disaster recovery (limited access).

Best-practices for S3 backups and KMS

- Store the S3 bucket name and `S3_KMS_KEY_ID` value in your secrets manager (do not commit them in config files). Use instance profiles or CI secrets for credentials.
- Example S3 bucket policy to require KMS encryption and restrict access to a specific IAM principal (replace values):

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "DenyUnencryptedObjectUploads",
      "Effect": "Deny",
      "Principal": "*",
      "Action": "s3:PutObject",
      "Resource": "arn:aws:s3:::your-backup-bucket/*",
      "Condition": {
        "StringNotEquals": {
          "s3:x-amz-server-side-encryption": "aws:kms"
        }
      }
    },
    {
      "Sid": "AllowBackupRole",
      "Effect": "Allow",
      "Principal": {
        "AWS": "arn:aws:iam::123456789012:role/backup-role"
      },
      "Action": ["s3:PutObject","s3:GetObject","s3:ListBucket"],
      "Resource": [
        "arn:aws:s3:::your-backup-bucket",
        "arn:aws:s3:::your-backup-bucket/*"
      ]
    }
  ]
}
```

- Example least-privilege IAM policy for the backup role (attach to the role used by your backup runner):

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "s3:PutObject",
        "s3:GetObject",
        "s3:ListBucket"
      ],
      "Resource": [
        "arn:aws:s3:::your-backup-bucket",
        "arn:aws:s3:::your-backup-bucket/*"
      ]
    },
    {
      "Effect": "Allow",
      "Action": [
        "kms:Encrypt",
        "kms:Decrypt",
        "kms:GenerateDataKey"
      ],
      "Resource": "arn:aws:kms:us-east-1:123456789012:key/your-kms-key-id"
    }
  ]
}
```

- Key rotation and auditing:
  - Rotate IAM credentials for the backup runner on a schedule and after any suspected compromise.
  - Rotate KMS keys according to your org policy (you can schedule key rotation or create new CMKs and re-encrypt data if needed).
  - Send backup/restore audit logs to a secure monitoring channel (CloudTrail, S3 access logs) and restrict access to logs.

- Restore testing:
  - Automate and schedule periodic restore tests to a staging environment (weekly or monthly depending on RTO/RPO requirements).
  - Example: restore the latest backup to a temporary instance and run integrity checks and smoke tests.
  - Keep a documented checklist for restores and require at least one successful restore per quarter.

Automated backups (example scripts)

We include basic scripts to perform database backups and restores. They are intended as examples to adapt to your environment and must be reviewed before use.

- `scripts/backup-db.sh` - creates a timestamped DB dump (MySQL/Postgres/SQLite), compresses it, optionally GPG-encrypts for a recipient, and uploads to S3. Environment variables used:
  - `AWS_BUCKET` (required), `AWS_PREFIX` (optional), `AWS_REGION`, `S3_KMS_KEY_ID` (optional)
  - `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
  - `GPG_RECIPIENT` (optional)

  Example (cron): run nightly at 02:00 UTC

  ```cron
  # Use environment or secret manager variables rather than embedding plaintext values.
  0 2 * * * AWS_BUCKET=your-backup-bucket DB_CONNECTION=pgsql DB_HOST=db.prod DB_PORT=5432 DB_DATABASE=prod DB_USERNAME=backup DB_PASSWORD="${PROD_DB_PWD}" /path/to/repo/scripts/backup-db.sh
  # Note: set PROD_DB_PWD in your host/CI secret store; do not commit secrets to git.
  ```

- `scripts/restore-db.sh` - download a backup from S3, decrypt if GPG-encrypted, and restore to the configured DB.

Restore example (download latest and restore):

```bash
# download latest backup listing (example uses aws cli and jq to pick latest key)
LATEST=$(aws s3api list-objects --bucket your-backup-bucket --prefix backups/ --query 'reverse(sort_by(Contents,&LastModified))[0].Key' --output text)
aws s3 cp "s3://your-backup-bucket/$LATEST" /tmp/backup.sql.gz
# Set restore DB credentials via environment or secrets manager (do not insert values into scripts)
export DB_CONNECTION=pgsql DB_HOST=db.prod DB_PORT=5432 DB_DATABASE=prod DB_USERNAME=restore DB_PASSWORD="${RESTORE_PWD}"
chmod +x scripts/restore-db.sh
./scripts/restore-db.sh s3://your-backup-bucket/$LATEST
```

S3 lifecycle recommendation

- Use an S3 lifecycle policy to move backups to cheaper storage and expire old backups. Example policy:

```json
{
  "Rules": [
    {
      "ID": "backup-archive-and-expire",
      "Prefix": "backups/",
      "Status": "Enabled",
      "Transitions": [
        { "Days": 30, "StorageClass": "STANDARD_IA" },
        { "Days": 90, "StorageClass": "GLACIER" }
      ],
      "Expiration": { "Days": 365 }
    }
  ]
}
```

Operational notes

- Encrypt backups at rest: prefer server-side encryption with KMS (`S3_KMS_KEY_ID`) and optionally client-side encryption with `gpg` for an extra layer.
- Test restores regularly — schedule a periodic restore test to a staging environment and validate data integrity.
- Rotate any keys used to access backups (AWS IAM keys or secret manager credentials) on a regular cadence and after any suspected compromise.

4) Runbook examples

- Responding to job failures:
  - Check the failed job in Sentry/logs for exception details.
  - If transient (network/db), retry the job via `php artisan queue:retry`.
  - If persistent, inspect job payload and code; add fix and redeploy.

- Rotating a leaked AWS key:
  - Create new key in IAM.
  - Update `AWS_ACCESS_KEY_ID`/`AWS_SECRET_ACCESS_KEY` in secrets manager.
  - Ensure developer git hooks are enabled to prevent accidental commits of secrets:
    - Run `composer run-script install-githooks` or `make dev-setup` to configure `.githooks` as the repo hooks path.
    - The repository includes a composer script and a `scripts/install-git-hooks.sh` helper that sets `core.hooksPath` and makes hooks executable.
    - To enforce checks server-side, enable branch protection requiring the `ci-security.yml` workflow and the secret-scan step; use `scripts/enable-branch-protection.sh` with an admin token to automate.
  - Deploy and verify the app can access S3.
  - Deactivate old key in IAM.


For any automation or integration (e.g. automatic Sentry release tagging on deploy), I can add CI snippets or example scripts. Let me know which integrations you prefer and I will scaffold them.

5) CI & Static Analysis

- A GitHub Actions workflow `ci-security.yml` was added to run secret scans (gitleaks), `composer audit`, `npm audit`, Semgrep, and optional PHP static analysis (PHPStan/ Psalm when installed).
- To enforce checks before merging, use `scripts/enable-branch-protection.sh` to require the security workflows as branch protection checks.
 - To enforce checks before merging, use `scripts/enable-branch-protection.sh` to require the security workflows as branch protection checks. Ensure the repository secrets `ZAP_TARGET_URL` (for non-prod ZAP scan) and required CI secrets are configured before enabling.

Incident playbooks

- Secret found: see [deploy/INCIDENTS/secret-rotation-playbook.md](deploy/INCIDENTS/secret-rotation-playbook.md) for automated rotation steps, history purge guidance, and notification procedures.
 
- Composer lock hygiene:
  - CI now runs `composer update --lock` and fails the job if `composer.lock` would be modified. This keeps `composer.lock` pinned to `composer.json` and prevents accidental drift.
 - The `composer-security` workflow runs `composer validate` and `composer audit`. Ensure you run `composer update --lock` locally and commit `composer.lock` when you update dependencies.
 - Enforce composer changes via PR and CI:
   - Do not commit manual edits to `composer.lock` or `composer.json` directly to `main`; open a PR and let CI (`Composer Security & Validate`) validate `composer.lock` is in sync.
   - Revert `minimum-stability` to `stable` if it was changed during experimentation. `composer.json` should contain:
     ```json
     "minimum-stability": "stable",
     "prefer-stable": true
     ```
   - Use Dependabot for automated dependency updates and review Dependabot PRs; do not merge dependency changes without CI green and a manual review.
    - Use Dependabot for automated dependency updates and review Dependabot PRs; do not merge dependency changes without CI green and a manual review.

  Composer & supply-chain notes

  - Enable Composer signature verification and consider SLSA provenance for high-risk packages. For composer, prefer installing from dist with secure HTTPS and verify packages where possible. Consider adding dependency provenance checks in CI and require Dependabot PRs to pass `ci-security.yml` before merging.

6) Key rotation and helper scripts

- A helper `scripts/rotate-aws-iam-key.sh` is provided to create a new IAM access key and guide manual rotation steps. Do not store the output keys in plaintext; update your secret manager and deploy.

Additional helper scripts

- `scripts/create-sentry-alerts.sh`: helper to create simple Sentry alert rules via the Sentry API. Requires `SENTRY_AUTH_TOKEN`, `SENTRY_ORG`, and `SENTRY_PROJECT`. Review and adapt the script to match your Sentry integrations and notification channels before running.

