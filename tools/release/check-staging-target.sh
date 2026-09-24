#!/usr/bin/env bash
# Refuse any deployment target that is not the WordPress staging contract (ADR 0046).
#
# usage: check-staging-target.sh <configured-wp-root> <component-target-rel>
#
# The configured root must equal the staging document root byte for byte. The
# production static public_html can therefore never pass, whatever a variable
# holds. The component path must be one of the two first-party directories, so
# an rsync --delete can never reach the WordPress root, wp-content or uploads.
# Prints TARGET_DIR=<root>/<component> on success and nothing on stdout otherwise.
set -euo pipefail
export LC_ALL=C

readonly STAGING_WP_ROOT='/home/u548735796/domains/teal-woodpecker-284165.hostingersite.com/public_html'

fail() {
  echo "check-staging-target: $*" >&2
  exit 1
}

[ "$#" -eq 2 ] || fail "usage: check-staging-target.sh <configured-wp-root> <component-target-rel>"
root=$1
target=$2

[ -n "$root" ] || fail "staging WordPress root is empty"
[ "$root" = "$STAGING_WP_ROOT" ] || fail "configured root is not the staging WordPress root"

case "$target" in
  wp-content/themes/camino-del-dharma | wp-content/plugins/camino-del-dharma-core) ;;
  *) fail "component target is not an allowed first-party directory" ;;
esac

printf 'TARGET_DIR=%s/%s\n' "$root" "$target"
