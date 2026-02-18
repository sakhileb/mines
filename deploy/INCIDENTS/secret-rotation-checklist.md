# Secret Rotation & Purge Checklist

Follow this checklist immediately when a secret is confirmed leaked.

1. Rotate credentials (DO THIS FIRST)
   - For each affected provider (AWS, Sentry, Stripe, etc.) create new credentials via provider console or API.
   - Mark the old keys as compromised and revoke them only after new keys are deployed and validated.

2. Update runtime/CI secrets
   - Update secrets in GitHub Actions or your secret manager.
   - Use existing helper script to update repo secrets: `scripts/set-github-secrets.sh` or `scripts/rotate-aws-iam-key-and-update-gh.sh` for AWS keys.

3. Deploy to staging/canary and validate
   - Deploy updated secrets to a canary or staging environment.
   - Run smoke tests and any integration checks.

4. Generate redact lists and prepare purge (NON-DESTRUCTIVE)
   - Generate redact list from the repo gitleaks report (if present):

     ```bash
     jq -r '.[].Secret' gitleaks-report.json | sort -u > scripts/secrets-to-redact.txt
     ```

   - Produce the git-filter-repo replace-text file (prepared by scripted helper):

     ```bash
     bash scripts/prepare-purge-commands.sh scripts/secrets-to-redact.txt
     # This will create secrets-to-redact.replace.txt and print reviewed commands.
     ```

5. Coordinate purge & perform history rewrite (DESTRUCTIVE step)
   - Coordinate with team; create backup mirror:

     ```bash
     git clone --mirror "$(pwd)" ../repo-backup.git
     ```

   - Run git-filter-repo (example):

     ```bash
     # Review replace file before running
     git filter-repo --replace-text secrets-to-redact.replace.txt
     git push --force --all
     git push --force --tags
     ```

   - After purge: inform all contributors to reclone or run `git fetch --all && git reset --hard origin/main` per your policy.

6. Post-purge actions
   - Rotate credentials again to replace any that may have been cached.
   - Invalidate caches/CDN tokens and any API clients that might have stored old creds.
   - Monitor access logs and set short-term alerts for unusual activity using provider alerts or SIEM.
   - Update `deploy/OPERATIONS.md` with remediation notes and timeline.

7. Communicate
   - Send incident notification to the on-call channel with summary, rotated keys, purge PR/commit references, and verification results.

IMPORTANT: Do NOT run the destructive rewrite until rotation is complete and all stakeholders agree. Use `scripts/prepare-purge-commands.sh` to prepare and review commands.
