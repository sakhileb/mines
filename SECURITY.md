Immediate Security Remediation Checklist

This document lists outstanding security tasks and recommended next steps for production hardening. Completed items have been removed to keep the checklist focused.

- Rotate any real secrets found by the repository scan (URGENT)
  - Rotate leaked/older keys in provider consoles (AWS, Pusher, mail providers, etc.) and remove revoked keys from all environments and CI.
  - Re-run secret scans after rotation to confirm no lingering exposures.

- Environment & debug
  - Ensure `APP_ENV=production` and `APP_DEBUG=false` are set in production environment variables.
  - Ensure `APP_KEY` is present in production and not stored in git.

- Mail & queue
  - Configure a production mail driver (e.g., `MAIL_MAILER=smtp` or a transactional provider).
  - Provision a worker-backed queue (Redis or SQS), set `QUEUE_CONNECTION` accordingly, and run monitored queue workers (Supervisor/systemd).

- File uploads & storage
  - Configure and verify an S3 bucket (private ACL) and set `AWS_*` environment variables in production.
  - Verify uploads work and that the application can generate and serve signed download URLs.
  - Deploy a virus-scanning service (ClamAV/clamd or a hosted scanning API) for uploaded files and ensure large-file scanning runs asynchronously.

- Cookies & session
  - Set `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`, `SESSION_SAME_SITE=strict` in production.
  - For staging/dev on HTTP, set `SESSION_SECURE_COOKIE=false` to avoid cookie issues.

- HTTPS & security headers
  - Enforce HTTPS (redirects) and HSTS in production. The repository includes `ForceHttps` and `SecurityHeaders` middleware; ensure they are enabled in production.
  - Configure `TrustProxies` when running behind load-balancers or reverse proxies so HTTPS detection (`$request->isSecure()`) works correctly.
  - Confirm the following headers are in place: `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY` (or `SAMEORIGIN`), and a suitable `Content-Security-Policy`.

- Git hygiene & CI
  - Keep secret scanning in CI (gitleaks) and ensure the workflow runs on PRs and pushes.
  - Consider blocking merges when secret-scan fails.

- Dependency & CI hardening
  - Address critical Composer and npm advisories; add static analysis (PHPStan / Psalm) and security scanning (Semgrep) to CI.

- Operational
  - Configure monitoring/alerting (Sentry, Papertrail) for queue and job failures.
  - Establish key rotation and backup procedures.

If you want, I can:
- Add a short `TrustProxies` example for common platforms.
- Add CI checks or branch protection guidance to enforce the session/secret checks.
- Run another repo secret scan and produce a short remediation plan.
