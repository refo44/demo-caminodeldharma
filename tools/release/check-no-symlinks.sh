#!/usr/bin/env bash
# Refuse a release whose component holds a Git symlink (ADR 0046).
#
# usage: check-no-symlinks.sh <commit> <component-dir>   (run inside the repository)
#
# Reads Git tree metadata of the exact commit, never the working tree, so a link
# is never followed. Only the selected component is inspected; a symlink elsewhere
# in the repository is irrelevant to this release. Fails closed: an unknown commit,
# an absent or empty component, or any mode 120000 entry exits non-zero.
set -euo pipefail
export LC_ALL=C

fail() {
  echo "check-no-symlinks: $*" >&2
  exit 1
}

[ "$#" -eq 2 ] && [ -n "$1" ] && [ -n "$2" ] || fail "usage: check-no-symlinks.sh <commit> <component-dir>"
commit=$1
source_dir=${2%/}

git rev-parse --verify --quiet "${commit}^{commit}" >/dev/null || fail "${commit} is not a commit"

entries=$(git ls-tree -r "${commit}" -- "${source_dir}")
[ -n "${entries}" ] || fail "${source_dir} has no files at ${commit}"

links=$(awk -F'\t' '{ split($1, meta, " "); if (meta[1] == "120000") print $2 }' <<< "${entries}")
if [ -n "${links}" ]; then
  echo "check-no-symlinks: ${source_dir} contains Git symlinks at ${commit}:" >&2
  printf '  %s\n' "${links}" >&2
  exit 1
fi
