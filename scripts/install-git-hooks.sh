#!/usr/bin/env bash
set -euo pipefail

# Install git hooks by setting the repository hooks path to .githooks
# Safe to run multiple times.

if ! command -v git >/dev/null 2>&1; then
  echo "git not found; please install git and re-run this script." >&2
  exit 1
fi

echo "Configuring git hooks path to .githooks"
git config core.hooksPath .githooks || true

if [ -d ".githooks" ]; then
  echo "Ensuring hooks are executable"
  chmod -R +x .githooks || true
fi

echo "Git hooks configured. You can run 'git status' to verify hooks are active."

exit 0
#!/usr/bin/env bash
set -euo pipefail

# Install git hooks from .githooks
git config core.hooksPath .githooks
chmod +x .githooks/* || true
echo "Git hooks installed (core.hooksPath set to .githooks)"
