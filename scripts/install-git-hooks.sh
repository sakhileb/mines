#!/usr/bin/env bash
set -euo pipefail

# Install git hooks from .githooks
git config core.hooksPath .githooks
chmod +x .githooks/* || true
echo "Git hooks installed (core.hooksPath set to .githooks)"
