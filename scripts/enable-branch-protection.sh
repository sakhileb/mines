#!/usr/bin/env bash
set -euo pipefail

# Usage: ./scripts/enable-branch-protection.sh owner repo branch
# Example: ./scripts/enable-branch-protection.sh sakhileb mines main

REPO_OWNER=${1:-}
REPO_NAME=${2:-}
BRANCH=${3:-main}

if [ -z "$REPO_OWNER" ] || [ -z "$REPO_NAME" ]; then
  echo "Usage: $0 <owner> <repo> [branch]"
  exit 2
fi

REPO="$REPO_OWNER/$REPO_NAME"

echo "Enabling branch protection for $REPO on branch $BRANCH"

# Required status checks - match workflow names or check-run names. Ensure composer lock sync,
# secret scanning, static analysis and coding standards are required before merging.
REQUIRED_CHECKS=(
  "Composer Security & Validate"
  "CI - Security & Static Analysis"
  "Secret scan (gitleaks)"
  "Composer audit"
  "phpcs"
  "Verify S3 Storage"
)

# Build JSON payload
checks_json=$(printf '"%s",' "${REQUIRED_CHECKS[@]}" | sed 's/,$//')

payload=$(cat <<EOF
{
  "required_status_checks": {
    "strict": true,
    "contexts": [${checks_json}]
  },
  "enforce_admins": false,
  "required_pull_request_reviews": null,
  "restrictions": null
}
EOF
)

gh api --method PUT "/repos/$REPO/branches/$BRANCH/protection" --input - <<EOF
$payload
EOF

echo "Branch protection applied. Confirm via GitHub UI or gh api get."
