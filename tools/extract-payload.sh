#!/usr/bin/env bash
# Deterministic, read-only payload extraction from static/ (ADR 0032 §8.1).
# The source commit recorded in the payload is the last commit touching
# static/ (stable while the static tree is unchanged; OWN-006: the repo
# VERSION is the source, not whatever ZIP Hostinger still serves).
set -euo pipefail
cd "$(dirname "$0")/.."

commit="$(git rev-list -1 HEAD -- static/ 2>/dev/null || true)"
if [ -z "$commit" ]; then
  commit="$(git rev-parse HEAD)"
  echo "extract-payload: no static/ history found (shallow clone?); using HEAD" >&2
fi

if command -v php >/dev/null 2>&1; then
  php tools/extract-payload.php "$commit"
else
  docker run --rm -v "$PWD":/repo -w /repo wordpress:cli-2.12-php8.3 \
    php tools/extract-payload.php "$commit"
fi
