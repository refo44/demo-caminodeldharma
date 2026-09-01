#!/usr/bin/env bash
# Level 1 unit suite. Installs the Composer kit if missing.
# Portable without native PHP/Composer: composer:2 resolves (platform.php is
# pinned to 8.3 in composer.json) and wordpress:cli-php8.3 runs the suite.
set -euo pipefail
cd "$(dirname "$0")/.."

if command -v php >/dev/null 2>&1 && command -v composer >/dev/null 2>&1; then
  [ -x vendor/bin/phpunit ] || composer install --prefer-dist --no-progress
  vendor/bin/phpunit -c phpunit.xml.dist
else
  [ -x vendor/bin/phpunit ] || docker run --rm -v "$PWD":/repo -w /repo \
    composer:2 composer install --prefer-dist --no-progress
  docker run --rm -v "$PWD":/repo -w /repo wordpress:cli-php8.3 \
    php vendor/bin/phpunit -c phpunit.xml.dist
fi
