#!/usr/bin/env bash
# Configure the WordPress site identity on the Camino del Dharma staging site.
#
# Run this yourself, over SSH, with the working directory set to the WordPress
# document root. This file stays outside public_html (docs/operations:
# scripts/ is never deployed onto a WordPress document root).
#
#   cd /path/to/wordpress-document-root
#   bash /path/outside/public_html/configure-staging-site-identity.sh
#
# The only writes are:
#   wp option update blogname
#   wp option update blogdescription
#   wp theme mod set custom_logo
#   wp option update site_icon
#
# siteurl, home, the domain, .htaccess, WP_ENVIRONMENT_TYPE, and blog_public
# are read and then checked again. They are never written. No migration, plugin
# install, or content delete runs here, and --confirm-production is never passed.

set -euo pipefail

readonly EXPECTED_SITEURL='https://teal-woodpecker-284165.hostingersite.com'
readonly EXPECTED_ENVIRONMENT='staging'
readonly EXPECTED_BLOG_PUBLIC='0'
readonly EXPECTED_BLOGNAME='Camino del Dharma'
readonly EXPECTED_BLOGDESCRIPTION='Comunidad Buddhista Chan y Tierra Pura en Colombia'

readonly LOGO_BASENAME='logo.png'
readonly ICON_BASENAME='logo-docx-cover.png'
readonly LOGO_DIMENSIONS='240x240'
readonly ICON_DIMENSIONS='1000x1000'
readonly EXPECTED_MIME='image/png'

APPLIED=0
VERIFIED=0
PREV_BLOGNAME_B64=''
PREV_BLOGDESCRIPTION_B64=''
PREV_CUSTOM_LOGO_B64=''
PREV_SITE_ICON_B64=''

LOGO_ID=''
LOGO_FILE=''
LOGO_URL=''
LOGO_MIME=''
LOGO_DIMENSIONS_ACTUAL=''
ICON_ID=''
ICON_FILE=''
ICON_URL=''
ICON_MIME=''
ICON_DIMENSIONS_ACTUAL=''

abort() {
	printf 'ABORT: %s\n' "$*" >&2
	exit 1
}

# A non-zero exit after the four identity writes restores only those four values.
# Environment checks abort before APPLIED=1, so they restore nothing.
on_exit() {
	local status=$?
	if [[ "$status" -ne 0 && "$APPLIED" == 1 && "$VERIFIED" != 1 ]]; then
		restore_previous || true
	fi
	# exit inside an EXIT trap is not recursive, and it keeps the original status.
	# Without it, a successful restore would make the script exit 0.
	exit "$status"
}
trap on_exit EXIT

wp_cmd() {
	wp --path="$PWD" --no-color "$@"
}

require_exact() {
	local label="$1"
	local actual="$2"
	local expected="$3"

	printf '%s=%s\n' "$label" "$actual"
	if [[ "$actual" != "$expected" ]]; then
		abort "${label} is '${actual}', expected '${expected}'. Nothing was modified."
	fi
}

# Previous identity is captured only after every pre-write check has passed.
# A failed write or a failed final check restores these four values and nothing else.
capture_previous() {
	local line key value raw
	local seen_name=0 seen_description=0 seen_logo=0 seen_icon=0

	raw="$(wp_cmd eval '
		$logo = get_theme_mod( "custom_logo" );
		$icon = get_option( "site_icon" );
		$fields = array(
			"PREV_BLOGNAME"        => (string) get_option( "blogname" ),
			"PREV_BLOGDESCRIPTION" => (string) get_option( "blogdescription" ),
			"PREV_CUSTOM_LOGO"     => ( false === $logo || null === $logo ) ? "" : (string) $logo,
			"PREV_SITE_ICON"       => ( false === $icon || null === $icon ) ? "" : (string) $icon,
		);
		foreach ( $fields as $key => $value ) {
			echo $key, "=", base64_encode( $value ), "\n";
		}
	')"

	while IFS= read -r line || [[ -n "$line" ]]; do
		[[ -z "$line" ]] && continue
		[[ "$line" == *=* ]] || abort "Unexpected capture output: ${line}"
		key="${line%%=*}"
		value="${line#*=}"
		case "$key" in
			PREV_BLOGNAME) PREV_BLOGNAME_B64="$value"; seen_name=1 ;;
			PREV_BLOGDESCRIPTION) PREV_BLOGDESCRIPTION_B64="$value"; seen_description=1 ;;
			PREV_CUSTOM_LOGO) PREV_CUSTOM_LOGO_B64="$value"; seen_logo=1 ;;
			PREV_SITE_ICON) PREV_SITE_ICON_B64="$value"; seen_icon=1 ;;
			*) abort "Unexpected capture field: ${key}" ;;
		esac
	done <<< "$raw"

	# Empty base64 is valid: blogdescription, custom_logo, or site_icon may be unset.
	[[ "$seen_name" == 1 && "$seen_description" == 1 && "$seen_logo" == 1 && "$seen_icon" == 1 ]] || abort "Could not capture the previous site identity."
}

restore_previous() {
	printf 'Restoring the previous blogname, blogdescription, custom_logo, and site_icon.\n' >&2
	if ! CDD_PREV_BLOGNAME_B64="$PREV_BLOGNAME_B64" \
		CDD_PREV_BLOGDESCRIPTION_B64="$PREV_BLOGDESCRIPTION_B64" \
		CDD_PREV_CUSTOM_LOGO_B64="$PREV_CUSTOM_LOGO_B64" \
		CDD_PREV_SITE_ICON_B64="$PREV_SITE_ICON_B64" \
		wp_cmd eval '
			$decode = static function ( $name ) {
				$raw = getenv( $name );
				if ( ! is_string( $raw ) ) {
					WP_CLI::error( "Missing {$name} while restoring site identity." );
				}
				$value = base64_decode( $raw, true );
				if ( false === $value ) {
					WP_CLI::error( "Could not decode {$name} while restoring site identity." );
				}
				return $value;
			};

			update_option( "blogname", $decode( "CDD_PREV_BLOGNAME_B64" ) );
			update_option( "blogdescription", $decode( "CDD_PREV_BLOGDESCRIPTION_B64" ) );

			$logo = $decode( "CDD_PREV_CUSTOM_LOGO_B64" );
			if ( "" === $logo ) {
				remove_theme_mod( "custom_logo" );
			} else {
				set_theme_mod( "custom_logo", (int) $logo );
			}

			$icon = $decode( "CDD_PREV_SITE_ICON_B64" );
			if ( "" === $icon ) {
				delete_option( "site_icon" );
			} else {
				update_option( "site_icon", (int) $icon );
			}
		'; then
		printf 'ROLLBACK FAILED. Check blogname, blogdescription, custom_logo, and site_icon by hand.\n' >&2
		return 1
	fi
}

run_write() {
	if ! wp_cmd "$@"; then
		abort "Write failed: wp $*"
	fi
}

assign_lookup_field() {
	local key="$1"
	local value="$2"

	case "$key" in
		LOGO_ID) LOGO_ID="$value" ;;
		LOGO_FILE) LOGO_FILE="$value" ;;
		LOGO_URL) LOGO_URL="$value" ;;
		LOGO_MIME) LOGO_MIME="$value" ;;
		LOGO_DIMENSIONS) LOGO_DIMENSIONS_ACTUAL="$value" ;;
		ICON_ID) ICON_ID="$value" ;;
		ICON_FILE) ICON_FILE="$value" ;;
		ICON_URL) ICON_URL="$value" ;;
		ICON_MIME) ICON_MIME="$value" ;;
		ICON_DIMENSIONS) ICON_DIMENSIONS_ACTUAL="$value" ;;
		*) abort "Unexpected lookup field: ${key}" ;;
	esac
}

lookup_attachments() {
	local line key value raw

	# Read-only. Exact basename of get_attached_file(), not a guessed ID and not SQL.
	raw="$(
		CDD_LOGO_BASENAME="$LOGO_BASENAME" \
			CDD_ICON_BASENAME="$ICON_BASENAME" \
			wp_cmd eval '
		$find = static function ( $basename ) {
			$ids = get_posts(
				array(
					"post_type"              => "attachment",
					"post_status"            => "inherit",
					"posts_per_page"         => -1,
					"fields"                 => "ids",
					"orderby"                => "ID",
					"order"                  => "ASC",
					"no_found_rows"          => true,
					"update_post_meta_cache" => false,
					"update_post_term_cache" => false,
				)
			);

			if ( ! is_array( $ids ) ) {
				WP_CLI::error( "get_posts() did not return attachment IDs." );
			}

			$matches = array();
			foreach ( $ids as $id ) {
				$id   = (int) $id;
				$file = get_attached_file( $id );
				if ( ! is_string( $file ) || "" === $file ) {
					continue;
				}
				if ( basename( $file ) === $basename ) {
					$matches[] = $id;
				}
			}

			$count = count( $matches );
			if ( 1 !== $count ) {
				$suffix = "";
				if ( $count > 1 ) {
					$suffix = " IDs: " . implode( ", ", $matches ) . ".";
				}
				WP_CLI::error( "Expected exactly 1 attachment named {$basename}; found {$count}.{$suffix}" );
			}

			return $matches[0];
		};

		$emit = static function ( $prefix, $id ) {
			$id   = (int) $id;
			$file = get_attached_file( $id );
			$url  = wp_get_attachment_url( $id );
			$meta = wp_get_attachment_metadata( $id );
			$mime = (string) get_post_mime_type( $id );

			if ( ! is_string( $file ) ) {
				$file = "";
			}
			if ( ! is_string( $url ) ) {
				$url = "";
			}

			$width  = ( is_array( $meta ) && isset( $meta["width"] ) ) ? (int) $meta["width"] : 0;
			$height = ( is_array( $meta ) && isset( $meta["height"] ) ) ? (int) $meta["height"] : 0;

			echo $prefix, "_ID=", $id, "\n";
			echo $prefix, "_FILE=", $file, "\n";
			echo $prefix, "_URL=", $url, "\n";
			echo $prefix, "_MIME=", $mime, "\n";
			echo $prefix, "_DIMENSIONS=", $width, "x", $height, "\n";
		};

		$logo_name = getenv( "CDD_LOGO_BASENAME" );
		$icon_name = getenv( "CDD_ICON_BASENAME" );
		if ( ! is_string( $logo_name ) || "" === $logo_name || ! is_string( $icon_name ) || "" === $icon_name ) {
			WP_CLI::error( "Attachment basenames were not provided." );
		}

		$emit( "LOGO", $find( $logo_name ) );
		$emit( "ICON", $find( $icon_name ) );
	')"

	while IFS= read -r line || [[ -n "$line" ]]; do
		[[ -z "$line" ]] && continue
		[[ "$line" == *=* ]] || abort "Unexpected lookup output: ${line}"
		key="${line%%=*}"
		value="${line#*=}"
		assign_lookup_field "$key" "$value"
	done <<< "$raw"
}

print_attachment() {
	local prefix="$1"
	local id="$2"
	local file="$3"
	local url="$4"
	local dimensions="$5"

	printf '%s_ID=%s\n' "$prefix" "$id"
	printf '%s_FILE=%s\n' "$prefix" "$file"
	printf '%s_URL=%s\n' "$prefix" "$url"
	printf '%s_DIMENSIONS=%s\n' "$prefix" "$dimensions"
}

require_attachment() {
	local label="$1"
	local basename="$2"
	local id="$3"
	local file="$4"
	local url="$5"
	local mime="$6"
	local dimensions="$7"
	local expected_dimensions="$8"

	[[ "$id" =~ ^[1-9][0-9]*$ ]] || abort "${label} id '${id}' is not a positive integer."
	[[ -n "$file" && -n "$url" && -n "$mime" && -n "$dimensions" ]] || abort "${label} lookup is incomplete."
	[[ "$(basename "$file")" == "$basename" ]] || abort "${label} file basename is '$(basename "$file")', expected '${basename}'."
	[[ -f "$file" && -r "$file" ]] || abort "${label} file is missing or unreadable: ${file}"
	[[ "$url" == "${EXPECTED_SITEURL}"/* ]] || abort "${label} URL is not on staging: ${url}"
	[[ "$mime" == "$EXPECTED_MIME" ]] || abort "${label} mime is '${mime}', expected '${EXPECTED_MIME}'."
	[[ "$dimensions" == "$expected_dimensions" ]] || abort "${label} dimensions are '${dimensions}', expected '${expected_dimensions}'."
}

require_environment() {
	local siteurl environment blog_public

	command -v wp >/dev/null 2>&1 || abort "wp (WP-CLI) was not found in PATH."
	[[ -f "$PWD/wp-load.php" ]] || abort "Current directory is not a WordPress document root (wp-load.php is missing): ${PWD}"

	printf '== Environment ==\n'
	printf 'pwd=%s\n' "$PWD"

	siteurl="$(wp_cmd option get siteurl)"
	environment="$(wp_cmd eval 'echo wp_get_environment_type(), PHP_EOL;')"
	blog_public="$(wp_cmd option get blog_public)"

	siteurl="${siteurl//$'\r'/}"
	environment="${environment//$'\r'/}"
	blog_public="${blog_public//$'\r'/}"

	require_exact 'siteurl' "$siteurl" "$EXPECTED_SITEURL"
	require_exact 'environment_type' "$environment" "$EXPECTED_ENVIRONMENT"
	require_exact 'blog_public' "$blog_public" "$EXPECTED_BLOG_PUBLIC"
}

verify_final() {
	local siteurl environment blog_public blogname blogdescription custom_logo site_icon
	local line key value raw
	local read_logo_id='' read_logo_file='' read_logo_url='' read_logo_mime='' read_logo_dimensions=''
	local read_icon_id='' read_icon_file='' read_icon_url='' read_icon_mime='' read_icon_dimensions=''
	local failed=0

	siteurl="$(wp_cmd option get siteurl)"
	environment="$(wp_cmd eval 'echo wp_get_environment_type(), PHP_EOL;')"
	blog_public="$(wp_cmd option get blog_public)"
	blogname="$(wp_cmd option get blogname)"
	blogdescription="$(wp_cmd option get blogdescription)"
	custom_logo="$(wp_cmd theme mod get custom_logo)"
	site_icon="$(wp_cmd option get site_icon)"

	siteurl="${siteurl//$'\r'/}"
	environment="${environment//$'\r'/}"
	blog_public="${blog_public//$'\r'/}"
	blogname="${blogname//$'\r'/}"
	blogdescription="${blogdescription//$'\r'/}"
	custom_logo="${custom_logo//$'\r'/}"
	site_icon="${site_icon//$'\r'/}"

	raw="$(wp_cmd eval '
		$emit = static function ( $prefix, $id ) {
			$id   = (int) $id;
			$file = get_attached_file( $id );
			$url  = wp_get_attachment_url( $id );
			$meta = wp_get_attachment_metadata( $id );
			$mime = (string) get_post_mime_type( $id );

			if ( ! is_string( $file ) ) {
				$file = "";
			}
			if ( ! is_string( $url ) ) {
				$url = "";
			}

			$width  = ( is_array( $meta ) && isset( $meta["width"] ) ) ? (int) $meta["width"] : 0;
			$height = ( is_array( $meta ) && isset( $meta["height"] ) ) ? (int) $meta["height"] : 0;

			echo $prefix, "_ID=", $id, "\n";
			echo $prefix, "_FILE=", $file, "\n";
			echo $prefix, "_URL=", $url, "\n";
			echo $prefix, "_MIME=", $mime, "\n";
			echo $prefix, "_DIMENSIONS=", $width, "x", $height, "\n";
		};

		$emit( "LOGO", (int) get_theme_mod( "custom_logo" ) );
		$emit( "ICON", (int) get_option( "site_icon" ) );
	')"

	while IFS= read -r line || [[ -n "$line" ]]; do
		[[ -z "$line" ]] && continue
		[[ "$line" == *=* ]] || abort "Unexpected read-back output: ${line}"
		key="${line%%=*}"
		value="${line#*=}"
		case "$key" in
			LOGO_ID) read_logo_id="$value" ;;
			LOGO_FILE) read_logo_file="$value" ;;
			LOGO_URL) read_logo_url="$value" ;;
			LOGO_MIME) read_logo_mime="$value" ;;
			LOGO_DIMENSIONS) read_logo_dimensions="$value" ;;
			ICON_ID) read_icon_id="$value" ;;
			ICON_FILE) read_icon_file="$value" ;;
			ICON_URL) read_icon_url="$value" ;;
			ICON_MIME) read_icon_mime="$value" ;;
			ICON_DIMENSIONS) read_icon_dimensions="$value" ;;
			*) abort "Unexpected read-back field: ${key}" ;;
		esac
	done <<< "$raw"

	printf '\n== Verification ==\n'
	printf 'siteurl=%s\n' "$siteurl"
	printf 'environment_type=%s\n' "$environment"
	printf 'blog_public=%s\n' "$blog_public"
	printf 'blogname=%s\n' "$blogname"
	printf 'blogdescription=%s\n' "$blogdescription"
	printf 'custom_logo=%s\n' "$custom_logo"
	printf 'site_icon=%s\n' "$site_icon"
	printf '\n'
	print_attachment 'LOGO' "$read_logo_id" "$read_logo_file" "$read_logo_url" "$read_logo_dimensions"
	printf '\n'
	print_attachment 'ICON' "$read_icon_id" "$read_icon_file" "$read_icon_url" "$read_icon_dimensions"

	[[ "$siteurl" == "$EXPECTED_SITEURL" ]] || failed=1
	[[ "$environment" == "$EXPECTED_ENVIRONMENT" ]] || failed=1
	[[ "$blog_public" == "$EXPECTED_BLOG_PUBLIC" ]] || failed=1
	[[ "$blogname" == "$EXPECTED_BLOGNAME" ]] || failed=1
	[[ "$blogdescription" == "$EXPECTED_BLOGDESCRIPTION" ]] || failed=1
	[[ "$custom_logo" == "$LOGO_ID" ]] || failed=1
	[[ "$site_icon" == "$ICON_ID" ]] || failed=1
	[[ "$read_logo_id" == "$LOGO_ID" ]] || failed=1
	[[ "$read_icon_id" == "$ICON_ID" ]] || failed=1
	[[ "$(basename "$read_logo_file")" == "$LOGO_BASENAME" ]] || failed=1
	[[ "$(basename "$read_icon_file")" == "$ICON_BASENAME" ]] || failed=1
	[[ "$read_logo_url" == "${EXPECTED_SITEURL}"/* ]] || failed=1
	[[ "$read_icon_url" == "${EXPECTED_SITEURL}"/* ]] || failed=1
	[[ "$read_logo_mime" == "$EXPECTED_MIME" ]] || failed=1
	[[ "$read_icon_mime" == "$EXPECTED_MIME" ]] || failed=1
	[[ "$read_logo_dimensions" == "$LOGO_DIMENSIONS" ]] || failed=1
	[[ "$read_icon_dimensions" == "$ICON_DIMENSIONS" ]] || failed=1

	return "$failed"
}

main() {
	require_environment

	printf '\n== Attachments ==\n'
	lookup_attachments

	print_attachment 'LOGO' "$LOGO_ID" "$LOGO_FILE" "$LOGO_URL" "$LOGO_DIMENSIONS_ACTUAL"
	printf '\n'
	print_attachment 'ICON' "$ICON_ID" "$ICON_FILE" "$ICON_URL" "$ICON_DIMENSIONS_ACTUAL"

	require_attachment 'logo' "$LOGO_BASENAME" "$LOGO_ID" "$LOGO_FILE" "$LOGO_URL" "$LOGO_MIME" "$LOGO_DIMENSIONS_ACTUAL" "$LOGO_DIMENSIONS"
	require_attachment 'icon' "$ICON_BASENAME" "$ICON_ID" "$ICON_FILE" "$ICON_URL" "$ICON_MIME" "$ICON_DIMENSIONS_ACTUAL" "$ICON_DIMENSIONS"
	[[ "$LOGO_ID" != "$ICON_ID" ]] || abort "Logo and icon resolved to the same attachment id ${LOGO_ID}."

	capture_previous

	printf '\n== Update ==\n'
	APPLIED=1
	run_write option update blogname "$EXPECTED_BLOGNAME"
	run_write option update blogdescription "$EXPECTED_BLOGDESCRIPTION"
	run_write theme mod set custom_logo "$LOGO_ID"
	run_write option update site_icon "$ICON_ID"

	# set -e would exit before the abort message when verify_final returns 1.
	# The EXIT trap restores the four identity values if this check fails.
	set +e
	verify_final
	local status=$?
	set -e
	if [[ "$status" -ne 0 ]]; then
		abort 'Final verification failed. The previous identity values will be restored.'
	fi
	VERIFIED=1

	printf '\nSite identity updated on staging.\n'
}

main "$@"
