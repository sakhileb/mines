# Secret Rotation Playbook — "Secret Found"

This playbook documents the automated and manual steps to follow when a secret is discovered in the repository or leaked.

Severity: HIGH — treat immediately.

Immediate steps (automated where possible)

1. Identify the leaked secret(s) and the provider (AWS, Sentry, Stripe, etc.). Use `gitleaks-report.json` or CI report to list findings.
2. Trigger rotation for the affected provider:
   - AWS access keys: use `scripts/rotate-aws-iam-key-and-update-gh.sh` to create new keys and update repo secrets.
   - Sentry tokens: generate a new `SENTRY_AUTH_TOKEN` and update secrets in your secret store.
   - Other API keys: rotate using provider console or API and update secret store.
3. Update application secrets in your secrets manager / CI (`AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `SENTRY_DSN`, etc.).
4. Deploy to a small canary or staging environment and run smoke tests to validate.
5. After validation, revoke the old credential(s) via the provider console or API.

Repository hygiene (history rewrite)

- After rotation and revocation, perform a repository history rewrite to remove the secret from commits. Use `scripts/purge-secrets.sh` as a non-destructive guide, or run `git-filter-repo` with the list of secrets in `scripts/secrets-to-redact.example`.
- Notify the team and post a remediation summary: rotated credential, snippets removed, PR/commit refs, and verification steps.

Notifications

- Alert the on-call rotation via Slack/email/pager as configured in your incident response playbooks.
- Create a Sentry incident (if Sentry is enabled) with the rotation details and remediation status.

Post-incident review

- Record the root cause and preventive actions (e.g., add secrets to vault, improve pre-commit/gitleaks block, add stricter CI gating).
- Add lessons learned to the incident tracker and update `deploy/OPERATIONS.md` to reflect any procedural changes.
