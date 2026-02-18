#!/usr/bin/env bash
set -euo pipefail

# Helper to set required session env secrets in a GitHub repo using the gh CLI.
# Usage: ./scripts/set-github-secrets.sh [owner/repo]
# Example: ./scripts/set-github-secrets.sh sakhileb/mines

REPO=${1:-sakhileb/mines}

if ! command -v gh >/dev/null 2>&1; then
  echo "gh CLI not found. Install and authenticate: https://cli.github.com/"
  exit 2
fi

echo "Setting session secrets for repo: $REPO"

read -rp "SESSION_SECURE_COOKIE (true/false) [true]: " SESSION_SECURE_COOKIE
SESSION_SECURE_COOKIE=${SESSION_SECURE_COOKIE:-true}

read -rp "SESSION_HTTP_ONLY (true/false) [true]: " SESSION_HTTP_ONLY
SESSION_HTTP_ONLY=${SESSION_HTTP_ONLY:-true}

read -rp "SESSION_SAME_SITE (lax/strict/none) [strict]: " SESSION_SAME_SITE
SESSION_SAME_SITE=${SESSION_SAME_SITE:-strict}

echo "Setting secrets via gh..."
gh secret set SESSION_SECURE_COOKIE --body "$SESSION_SECURE_COOKIE" --repo "$REPO"
gh secret set SESSION_HTTP_ONLY --body "$SESSION_HTTP_ONLY" --repo "$REPO"
gh secret set SESSION_SAME_SITE --body "$SESSION_SAME_SITE" --repo "$REPO"

echo "Done. Secrets set for $REPO"
