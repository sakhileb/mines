#!/usr/bin/env bash
set -euo pipefail

# Rotate an IAM user's access key and print the new credentials.
# Requires: awscli configured with permissions to manage IAM and `jq`.
# Usage: ./scripts/rotate-aws-iam-key.sh <iam-username>

USER=${1:-}
if [ -z "$USER" ]; then
  echo "Usage: $0 <iam-username>"
  exit 2
fi

echo "Creating new access key for IAM user: $USER"
new=$(aws iam create-access-key --user-name "$USER")
access_key=$(echo "$new" | jq -r '.AccessKey.AccessKeyId')
secret_key=$(echo "$new" | jq -r '.AccessKey.SecretAccessKey')

echo "New Access Key ID: $access_key"
echo "New Secret Access Key: (hidden)"

cat <<EOF
Next steps:
1) Add the new key to your secrets manager (e.g., update GitHub/CI secret or your hosting platform).
2) Deploy using the new credential and verify application functionality.
3) After verification, list old keys:
   aws iam list-access-keys --user-name $USER
4) Delete old keys that are no longer in use:
   aws iam delete-access-key --user-name $USER --access-key-id <OLD_KEY_ID>

IMPORTANT: Do not print secrets to logs or commit them to source control.
EOF
