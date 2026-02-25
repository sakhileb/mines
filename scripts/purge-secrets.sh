#!/usr/bin/env bash
set -euo pipefail
echo "Purge secrets helper. This script only prints guidance; it does NOT modify history."
echo
echo "Steps:"
echo "1) Rotate any exposed credentials (AWS, Sentry, etc.)."
echo "2) Create a secrets file from scripts/secrets-to-redact.example and edit with your secrets to redact."
echo "3) Follow the git-filter-repo or BFG instructions in this file to rewrite history on a mirrored clone."
echo
echo "For a safe dry-run, create a mirror clone and test the rewrite locally before pushing force-updates."
echo
echo "See scripts/secrets-to-redact.example for format."
exit 0
