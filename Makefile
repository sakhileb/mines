## Development helper targets

.PHONY: dev-setup install-githooks

dev-setup: install-githooks
	@echo "Running composer install..."
	composer install --no-interaction

install-githooks:
	@echo "Installing git hooks via composer script..."
	composer run-script install-githooks || true
	@echo "If hooks are not active, run: git config core.hooksPath .githooks"
