#!/usr/bin/env bash
# Level 2 (wp-phpunit) in an EPHEMERAL Docker project: cdd-wp-phpunit.
# Own volumes, own port, wptests_ tables, `down -v` on exit. Never the
# developer volume (project camino-del-dharma) and never production
# (docs/guia-pruebas-plugin-theme-fse.md §2, ADR 0023/0033).
set -euo pipefail
cd "$(dirname "$0")/.."

compose=(docker compose --env-file tools/wp-tests.env
  -f docker-compose.yml -f docker-compose.wp-tests.yml -p cdd-wp-phpunit)

cleanup() { "${compose[@]}" down -v --remove-orphans >/dev/null 2>&1 || true; }
trap cleanup EXIT

if [ ! -x vendor/bin/phpunit ]; then
  echo "vendor/bin/phpunit missing — run tools/run-phpunit.sh (or composer install) first" >&2
  exit 1
fi

"${compose[@]}" up -d --wait db
"${compose[@]}" up -d wordpress

# Wait until the wordpress service has seeded core into the ephemeral volume.
core_ready=""
for _ in $(seq 1 30); do
  if "${compose[@]}" run --rm wpcli wp --path=/var/www/html core version >/dev/null 2>&1; then
    core_ready="yes"
    break
  fi
  sleep 2
done
if [ -z "$core_ready" ]; then
  echo "WordPress core never became available in the ephemeral harness" >&2
  exit 1
fi

"${compose[@]}" run --rm wpcli php vendor/bin/phpunit -c phpunit-wp.xml.dist
