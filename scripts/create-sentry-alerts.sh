#!/usr/bin/env bash
# Create basic Sentry alert rules for job failures.
# Requires: SENTRY_AUTH_TOKEN, SENTRY_ORG, SENTRY_PROJECT env vars.
# This is a helper script — review and adapt before running in production.

set -euo pipefail

if [ -z "${SENTRY_AUTH_TOKEN:-}" ] || [ -z "${SENTRY_ORG:-}" ] || [ -z "${SENTRY_PROJECT:-}" ]; then
  echo "SENTRY_AUTH_TOKEN, SENTRY_ORG and SENTRY_PROJECT must be set"
  exit 1
fi

API="https://sentry.io/api/0/projects/${SENTRY_ORG}/${SENTRY_PROJECT}/rules/"

# Example rule: notify when >5 errors with logger:queue in 1 hour
read -r -d '' PAYLOAD <<'JSON'
{
  "name": "Notify on job failures",
  "action_match": "any",
  "conditions": [
    {"id": "sentry.rules.conditions.event_frequency.EventFrequencyCondition", "value": {"count": 5, "interval": 60, "window": 60}}
  ],
  "actions": [
    {"id": "sentry.integrations.slack.notify_action.SlackNotifyAction", "settings": {"channel": "#alerts"}}
  ],
  "environment": "production",
  "frequency": 1
}
JSON

# Send payload (note: Slack integration id/settings will likely need adjustment)
curl -sSf -X POST "$API" \
  -H "Authorization: Bearer ${SENTRY_AUTH_TOKEN}" \
  -H "Content-Type: application/json" \
  -d "$PAYLOAD" || {
    echo "Failed to create Sentry rule. Check token, org, project, and integration settings." >&2
    exit 2
  }

echo "Sentry alert rule request submitted. Verify in Sentry UI."
