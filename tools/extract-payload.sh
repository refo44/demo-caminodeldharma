#!/usr/bin/env bash
# Deterministic, read-only payload extraction from static/ (ADR 0032 §8.1).
# The source commit recorded in the payload is the last commit touching
# static/ (stable while the static tree is unchanged; OWN-006: the repo
# VERSION is the source, not whatever ZIP Hostinger still serves).
set -euo pipefail
cd "$(dirname "$0")/.."

commit="$(git log -1 --format=%H -- static/)"

if command -v php >/dev/null 2>&1; then
  php tools/extract-payload.php "$commit"
else
  docker run --rm -v "$PWD":/repo -w /repo wordpress:cli-php8.3 \
    php tools/extract-payload.php "$commit"
fi
