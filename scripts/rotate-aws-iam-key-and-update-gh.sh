#!/usr/bin/env bash
set -euo pipefail

# Rotate an IAM user's access key and optionally update a GitHub Actions secret.
# Requires: awscli configured with permissions to manage IAM, `jq`, and `gh` (optional).
# Usage: ./scripts/rotate-aws-iam-key-and-update-gh.sh <iam-username> [owner/repo] [GITHUB_SECRET_NAME]
# Example: ./scripts/rotate-aws-iam-key-and-update-gh.sh deploy-bot sakhileb/mines AWS_ACCESS_KEY_ID

USER=${1:-}
REPO=${2:-}
GH_SECRET_NAME=${3:-}

if [ -z "$USER" ]; then
  echo "Usage: $0 <iam-username> [owner/repo] [GITHUB_SECRET_NAME]"
  exit 2
fi

echo "Creating new access key for IAM user: $USER"
new=$(aws iam create-access-key --user-name "$USER")
access_key=$(echo "$new" | jq -r '.AccessKey.AccessKeyId')
secret_key=$(echo "$new" | jq -r '.AccessKey.SecretAccessKey')

echo "New Access Key ID: $access_key"
echo "New Secret Access Key: (hidden)"

if [ -n "$REPO" ] && [ -n "$GH_SECRET_NAME" ]; then
  if ! command -v gh >/dev/null 2>&1; then
    echo "gh CLI not installed; skipping GitHub secret update." >&2
  else
    echo "Updating GitHub secret $GH_SECRET_NAME in repo $REPO"
    # For safety, set only the access key or secret depending on secret name
    if [[ "$GH_SECRET_NAME" =~ ACCESS_KEY ]]; then
      gh secret set "$GH_SECRET_NAME" --body "$access_key" --repo "$REPO"
    else
      # If secret looks like a secret value key, set secret key
      gh secret set "$GH_SECRET_NAME" --body "$secret_key" --repo "$REPO"
    fi
    echo "GitHub secret update requested; confirm in repo settings." 
  fi
fi

cat <<EOF
Next steps:
1) Add the new key to your secrets manager (if not using GitHub Actions). Use a temporary name like: SERVICE_KEY_NEW or AWS_ACCESS_KEY_ID_NEW.
2) Deploy using the new credential and verify application functionality.
3) After verification, list old keys:
   aws iam list-access-keys --user-name $USER
4) Delete old keys that are no longer in use:
   aws iam delete-access-key --user-name $USER --access-key-id <OLD_KEY_ID>

IMPORTANT: Do not print secrets to logs or commit them to source control.
EOF
