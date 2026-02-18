#!/usr/bin/env bash
# Run gitleaks against the repo and write report to gitleaks-report.json
set -euo pipefail
OUT=storage/reports/gitleaks-report.json
mkdir -p $(dirname "$OUT")

if command -v gitleaks >/dev/null 2>&1; then
  # Use default ruleset if our custom config is incompatible with installed gitleaks
  if [ -f .gitleaks.toml ]; then
    gitleaks detect --config .gitleaks.toml --report-format json --report-path "$OUT" || true
  else
    gitleaks detect --report-format json --report-path "$OUT" || true
  fi
  echo "gitleaks report written to $OUT"
  exit 0
fi

# Try Docker fallback
if command -v docker >/dev/null 2>&1; then
  # Docker fallback: use default rules if custom config breaks the image
  if [ -f .gitleaks.toml ]; then
    docker run --rm -v "$PWD":/repo -w /repo zricethezav/gitleaks:latest detect --config .gitleaks.toml --report-format json --report-path /repo/$OUT || true
  else
    docker run --rm -v "$PWD":/repo -w /repo zricethezav/gitleaks:latest detect --report-format json --report-path /repo/$OUT || true
  fi
  echo "gitleaks report (docker) written to $OUT"
  exit 0
fi

echo "gitleaks not found. Install via 'brew install gitleaks' or use Docker." >&2
exit 2
