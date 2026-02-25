#!/usr/bin/env bash
# Guidance script: lists candidates found by gitleaks and prints recommended commands
# DO NOT RUN PURGE AUTOMATICALLY. Review results, rotate keys, then purge history.
set -euo pipefail
REPORT=storage/reports/gitleaks-report.json
if [ ! -f "$REPORT" ]; then
  echo "Run scripts/run-gitleaks.sh first to produce $REPORT" >&2
  exit 2
fi

jq -r '.[] | "[" + (.rule + "] " ) + .path + ":" + (.offenders[0].line) ' "$REPORT" || true

echo "\nIf secrets are confirmed exposed, follow these steps (manual):"
echo "1) Rotate the exposed secrets immediately in the provider (AWS, Sentry, etc.)."
echo "2) Remove the secret from the repo history using 'git filter-repo' or 'bfg'. Example with git filter-repo:" 
cat <<'EOF'
# Install git-filter-repo
python3 -m pip install --user git-filter-repo
# Create a backup clone
git clone --mirror file://$PWD ../repo-backup.git
# Rewrite history to remove file or pattern (example: remove file with name secrets.txt)
git filter-repo --invert-paths --paths secrets.txt
# Force-push rewritten history (requires coordination)
git push --force --all
git push --force --tags
EOF

echo "3) After purge, invalidate caches and notify team. Rotate any credentials again to be safe."
