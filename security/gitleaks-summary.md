Gitleaks scan summary

Date: 2026-02-18

Findings: 3 potential leaks (all curl example headers in documentation)

- File: resources/views/livewire/documentation.blade.php
  - Rule: curl-auth-header
  - Lines: 631, 757-758, 769-770
  - Match: Authorization: Bearer YOUR_TOKEN
  - Note: These are example curl snippets containing the placeholder string `YOUR_TOKEN`. They are not actual secrets, but they trigger scanners. Replace with a clearly non-sensitive placeholder (e.g. `<REDACTED_TOKEN>`) or remove examples including real tokens.

Recommendations:
- If any real tokens were committed, rotate them immediately.
- Replace `YOUR_TOKEN` in documentation with a placeholder value and commit.
- Run `gitleaks detect` again to verify no other secrets are present.
- Consider removing any accidental secrets from history using `git filter-repo` or BFG and rotate keys.

Report file: gitleaks-report.json
