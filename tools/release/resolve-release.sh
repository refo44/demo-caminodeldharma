#!/usr/bin/env bash
# Resolve a WordPress release tag to exactly one deployable component (ADR 0046).
#
# usage: resolve-release.sh <tag> <tree-root>
#
# Pure and fail-closed: reads only the checked-out tree, prints KEY=VALUE lines
# on success and nothing on stdout otherwise. No git, no network, no secrets.
#
# Accepted tags are strict SemVer 2.0.0 core versions in the two WordPress
# namespaces: theme-v<X.Y.Z> and plugin-v<X.Y.Z>. Pre-release and build
# metadata are refused (ADR 0046 adopts no pre-releases). The tag SemVer must
# equal the version the selected component declares in the tree.
set -euo pipefail
export LC_ALL=C

fail() {
  echo "resolve-release: $*" >&2
  exit 1
}

[ "$#" -eq 2 ] || fail "usage: resolve-release.sh <tag> <tree-root>"
tag=$1
tree=$2

semver='(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)'
[[ "$tag" =~ ^(theme|plugin)-v(${semver})$ ]] ||
  fail "tag is not theme-v<SemVer> or plugin-v<SemVer> (no pre-release, no build metadata)"
component=${BASH_REMATCH[1]}
version=${BASH_REMATCH[2]}

# Print the single value of a `Version:` header in the first 8 KiB (what
# WordPress reads); zero or several headers are ambiguous.
header_version() {
  local file=$1 lines
  [ -f "$file" ] || fail "missing $file"
  lines=$(head -c 8192 "$file" | grep -Ei '^[[:space:]/*#@]*Version:' || true)
  [ -n "$lines" ] && [ "$(printf '%s\n' "$lines" | wc -l)" -eq 1 ] ||
    fail "$file must declare exactly one Version header"
  printf '%s\n' "$lines" | sed -E 's/^[^:]*:[[:space:]]*//; s/[[:space:]]*(\*\/)?[[:space:]]*$//'
}

# Print the single CDD_CORE_VERSION value defined by the plugin main file.
constant_version() {
  local file=$1 lines
  lines=$(grep -E "define\( *'CDD_CORE_VERSION' *, *'[^']*' *\)" "$file" || true)
  [ -n "$lines" ] && [ "$(printf '%s\n' "$lines" | wc -l)" -eq 1 ] ||
    fail "$file must define CDD_CORE_VERSION exactly once"
  printf '%s\n' "$lines" | sed -E "s/.*'CDD_CORE_VERSION' *, *'([^']*)'.*/\1/"
}

require_semver() {
  [[ "$2" =~ ^${semver}$ ]] || fail "$1 is not a SemVer core version"
}

case "$component" in
  theme)
    source_dir='wordpress/wp-content/themes/camino-del-dharma'
    slug='camino-del-dharma'
    declared=$(header_version "$tree/$source_dir/style.css")
    require_semver 'style.css Version' "$declared"
    ;;
  plugin)
    source_dir='wordpress/wp-content/plugins/camino-del-dharma-core'
    slug='camino-del-dharma-core'
    declared=$(header_version "$tree/$source_dir/camino-del-dharma-core.php")
    constant=$(constant_version "$tree/$source_dir/camino-del-dharma-core.php")
    require_semver 'plugin Version header' "$declared"
    require_semver 'CDD_CORE_VERSION' "$constant"
    [ "$declared" = "$constant" ] ||
      fail "plugin header ($declared) and CDD_CORE_VERSION ($constant) disagree"
    ;;
esac

[ "$declared" = "$version" ] ||
  fail "tag version $version does not match the $component version $declared"

# Component target below the WordPress root: same tree, without the repo prefix.
target_rel=${source_dir#wordpress/}

printf 'COMPONENT=%s\n' "$component"
printf 'VERSION=%s\n' "$version"
printf 'SOURCE_DIR=%s\n' "$source_dir"
printf 'TARGET_REL=%s\n' "$target_rel"
printf 'SLUG=%s\n' "$slug"
