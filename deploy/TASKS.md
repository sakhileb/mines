# Tasks — GitHub & Production Steps

This checklist covers the manual steps to perform in GitHub and on production/staging servers to complete the security hardening and operational tasks implemented on branch `feat/static-analysis`.

## Urgent (Do before any destructive history rewrite)
- Rotate all compromised credentials discovered by gitleaks (AWS, Sentry, API keys, deploy keys).
  - Use short-lived tokens where possible (IAM STS). Enforce MFA on admin accounts.
  - Use `scripts/rotate-aws-iam-key-and-update-gh.sh` to update GitHub secrets for AWS keys.
  - After rotation and validation, revoke old keys in provider consoles.

## After rotation — purge history (destructive)
- Re-run the scans to ensure rotated keys are not still present:
  - `./scripts/run-gitleaks.sh`
  - `./scripts/generate-secrets-to-redact.sh`
  - `./scripts/prepare-purge-commands.sh scripts/secrets-to-redact.txt`
- Create a mirror backup: `git clone --mirror $(pwd) ../repo-backup.git`.
- Preview rewrite (non-destructive) on a machine with `git-filter-repo` installed:
  - `python3 -m pip install --user git-filter-repo`
  - `git filter-repo --replace-text secrets-to-redact.replace.txt --analyze-only`
- If preview is OK, run rewrite (team coordination required):
  - `git filter-repo --replace-text secrets-to-redact.replace.txt`
  - `git push --force --all && git push --force --tags`
- After purge: rotate keys again, invalidate caches, update CI secrets, and notify team.

## GitHub repository configuration (admin)
- Provide an admin `GH_TOKEN` (repo:admin) and run `scripts/enable-branch-protection.sh <owner> <repo> main` to require checks:
  - `Composer Security & Validate`
  - `CI - Security & Static Analysis`
  - `Secret scan (gitleaks)`
  - `Composer audit`
  - `phpcs` / `PHPCS`
  - `Scan Blade templates for unescaped output` / `scan:blade-unescaped`
- Configure required GitHub Environments and secrets:
  - `staging` environment (for ZAP): add `ZAP_TARGET_URL`, `ZAP_AUTH_*` secrets as needed.
  - `backups` environment: add `BACKUP_AWS_ACCESS_KEY_ID`, `BACKUP_AWS_SECRET_ACCESS_KEY`, `BACKUP_AWS_REGION`, `BACKUP_S3_BUCKET`, `S3_KMS_KEY_ID`.
  - Add `SENTRY_AUTH_TOKEN`, `SENTRY_ORG`, `SENTRY_PROJECT`, and `SENTRY_DSN` to repo secrets.
- Provision a self-hosted runner inside staging network for authenticated ZAP scans and scheduled restore smoke tests. Register runner and add to repository.

## CI / Static analysis
- Ensure `ci-security.yml` and `composer-security.yml` are present and passing.
- Generate and commit `psalm-baseline.xml` on a PHP 8.3.16+ machine (CI or developer machine):
  - On a machine running PHP >= 8.3.16: `vendor/bin/psalm --set-baseline=psalm-baseline.xml` then commit.
- Confirm `phpstan-baseline.neon` is committed (already included) and tune rules as necessary.
- Run full CI and fix any blocking issues, ensure branch protection blocks merges until CI is green.

## OWASP ZAP (staging)
- Set `ZAP_TARGET_URL` secret to your staging URL and set `ZAP_AUTH_*` if using auth.
- Provision self-hosted runner in staging network and add to repository runner pool.
- Trigger `owasp-zap.yml` via workflow_dispatch for an authenticated initial run, triage issues created (labels: `security,zap,triage`).

## Backups & KMS
- Apply `deploy/s3-bucket-policy-enforce-kms.json` to your backup bucket (update bucket name and KMS key ARN).
- Create dedicated backup IAM role using `deploy/backup-role-policy.json` and attach to backup runner (use instance profile where possible).
- Add required secrets to `backups` environment and run `backup-restore-smoke.yml` once to validate.

## Sentry / Monitoring
- Add `SENTRY_DSN`, `SENTRY_AUTH_TOKEN`, `SENTRY_ORG`, `SENTRY_PROJECT` to repo secrets or deployment secret store.
- Trigger the `sentry-release.yml` workflow (push to `main` or manual dispatch) to verify releases are created.
- Configure Sentry alert rules for job failures and high error rates.

## CSP & App changes
- Update any inline scripts/styles to use the CSP nonce provided by middleware:
  - In Blade templates: `<script nonce="{{ request()->attributes->get('csp_nonce') }}">` or use hashes/SRI for static assets.
- Deploy to staging with `Content-Security-Policy-Report-Only` enabled, review reports, then flip to enforcement in production once stable.

## File upload & storage
- Verify `FileUploadService` behavior in staging with representative ZIPs and large archives.
- Confirm ClamAV scanning is available on the upload runner if `VIRUS_SCAN_ENABLED` is true and configure `CLAMD_SOCKET` or `CLAMD_HOST`/`CLAMD_PORT`.

## Tests & release
- Run full test suite in CI; fix failing tests.
- Run static analysis and only merge after CI passes.
- Tag and deploy after final verification. Use canary/staging rollout first.

## Post-deploy verification
- Verify Sentry errors are reported and triaged.
- Confirm backup smoke workflow ran successfully and that restored DB passes smoke checks.
- Monitor logs for redaction leakage; audit log channel for any unauthorised accesses.

## Communication
- Notify the team of rotation/purge, include list of rotated secrets, timeline, and verification steps.
- Keep an incident ticket with remediation steps and closure criteria.

---
For any step that requires credentials or admin tokens, perform the action manually or provide secure credentials to an operator with appropriate privileges.
