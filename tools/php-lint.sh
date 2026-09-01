#!/usr/bin/env bash
# QA 1: php -l over first-party PHP (plugin, theme, tests). Excludes vendor/.
# Portable without native PHP: falls back to wordpress:cli-php8.3 (ADR 0023).
set -euo pipefail
cd "$(dirname "$0")/.."

FIND_ARGS=(wordpress/wp-content tests -name '*.php' -not -path '*/vendor/*')

if [ -z "$(find "${FIND_ARGS[@]}" -print -quit)" ]; then
  echo "php-lint: no first-party PHP files found — refusing to pass vacuously" >&2
  exit 1
fi

if command -v php >/dev/null 2>&1; then
  find "${FIND_ARGS[@]}" -print0 | xargs -0 -n1 php -l >/dev/null
else
  docker run --rm -v "$PWD":/repo -w /repo wordpress:cli-php8.3 \
    sh -c "find wordpress/wp-content tests -name '*.php' -not -path '*/vendor/*' -print0 | xargs -0 -n1 php -l >/dev/null"
fi

echo "php-lint: OK"
