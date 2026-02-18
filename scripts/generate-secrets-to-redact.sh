#!/usr/bin/env bash
set -euo pipefail
# Generates scripts/secrets-to-redact.txt from storage/reports/gitleaks-report.json
REPORT=storage/reports/gitleaks-report.json
OUT=scripts/secrets-to-redact.txt

if [ ! -f "$REPORT" ]; then
  echo "No gitleaks report found at $REPORT. Run scripts/run-gitleaks.sh first." >&2
  exit 2
fi

echo "# Generated secrets-to-redact (review before use)" > "$OUT"
echo "# Format: literal string or regex lines to pass to git-filter-repo --replace-text" >> "$OUT"

# Extract likely secrets: prefer offenders[].match, then .Match/.match, then .Secret
# Support both gitleaks versions that use capitalized keys and older/lowercase keys.
jq -r '.[] | ( .offenders[]?.match // .Match // .match // .Secret // "") as $m | select($m!="") | $m' "$REPORT" | sort -u >> "$OUT" || true

echo "Wrote candidates to $OUT; review carefully before using with git-filter-repo or BFG." >&2
echo "Suggested next step: rotate these secrets in provider consoles, then run purge script as documented." >&2

exit 0
