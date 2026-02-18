#!/usr/bin/env bash
set -euo pipefail
# Prepare git-filter-repo commands for purging secrets after rotation
# This script DOES NOT execute destructive actions; it prints reviewed commands.

SECRETS_FILE=${1:-scripts/secrets-to-redact.txt}

if [ ! -f "$SECRETS_FILE" ]; then
  echo "Secrets file not found: $SECRETS_FILE" >&2
  echo "Run scripts/generate-secrets-to-redact.sh to produce it from gitleaks." >&2
  exit 2
fi

echo "Review the following commands carefully. They are examples to run after you've rotated credentials and coordinated with your team."
echo
echo "# Create a mirror backup before rewriting:"
echo "git clone --mirror "$(pwd)" ../repo-backup.git"
echo
echo "# Example: build a replace-text file for git-filter-repo (literal lines):"
echo "# The file should contain lines of the form: literal:<secret>==>REDACTED"
echo
REPLACE_TXT=secrets-to-redact.replace.txt
echo "# Generated replace file: $REPLACE_TXT"
echo "" > $REPLACE_TXT
while IFS= read -r line; do
  [ -z "$line" ] && continue
  # Escape '==' for safety
  echo "literal:$line==>REDACTED" >> $REPLACE_TXT
done < "$SECRETS_FILE"

echo
echo "# To preview the rewrite locally (non-destructive), use git-filter-repo --analyze-only:" 
echo "python3 -m pip install --user git-filter-repo"
echo "# Then to rewrite (destructive) run (after verification):"
echo "# git filter-repo --replace-text $REPLACE_TXT"
echo "# git push --force --all && git push --force --tags"

echo
echo "# After purge: rotate credentials again, invalidate caches, notify team, and monitor for suspicious activity."

exit 0
