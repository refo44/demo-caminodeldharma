#!/usr/bin/env bash
# Accept only the canonical production WordPress root (ADR 0048).
#
# usage: check-production-target.sh <configured-wp-root> <component-target-rel>
#
# The configured root must equal the production document root byte for byte.
# The retired temporary-domain root, a parent directory, and any other path
# fail closed. The component path must be one of the two first-party
# directories, so an rsync --delete can never reach the WordPress root,
# wp-content or uploads. On success prints TARGET_DIR and
# REQUIRED_REAL_ROOT. Callers must require that pwd -P of the remote root
# equals REQUIRED_REAL_ROOT, so a symlink to another directory cannot pass.
# Prints nothing on stdout otherwise.
set -euo pipefail
export LC_ALL=C

readonly PRODUCTION_WP_ROOT='/home/u548735796/domains/caminodeldharma.org/public_html'

fail() {
  echo "check-production-target: $*" >&2
  exit 1
}

[ "$#" -eq 2 ] || fail "usage: check-production-target.sh <configured-wp-root> <component-target-rel>"
root=$1
target=$2

[ -n "$root" ] || fail "production WordPress root is empty"
[ "$root" = "$PRODUCTION_WP_ROOT" ] || fail "configured root is not the canonical production WordPress root"

case "$target" in
  wp-content/themes/camino-del-dharma | wp-content/plugins/camino-del-dharma-core) ;;
  *) fail "component target is not an allowed first-party directory" ;;
esac

printf 'TARGET_DIR=%s/%s\n' "$root" "$target"
printf 'REQUIRED_REAL_ROOT=%s\n' "$PRODUCTION_WP_ROOT"
