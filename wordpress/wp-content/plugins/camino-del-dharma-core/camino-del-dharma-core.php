<?php
/**
 * Plugin Name: Camino del Dharma Core
 * Plugin URI: https://caminodeldharma.org
 * Description: Domain plugin for Comunidad Buddhista Camino del Dharma — content model, routing and migration tooling (ADR 0024).
 * Version: 0.6.0
 * Requires at least: 7.1
 * Requires PHP: 8.3
 * Author: Comunidad Buddhista Camino del Dharma
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: camino-del-dharma-core
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CDD_CORE_VERSION', '0.6.0' );
define( 'CDD_CORE_PLUGIN_FILE', __FILE__ );

// Pure domain classes (no WordPress APIs; unit-testable without a boot).
require_once __DIR__ . '/includes/class-cdd-core-event-status.php';
require_once __DIR__ . '/includes/class-cdd-core-ics-generator.php';
require_once __DIR__ . '/includes/class-cdd-core-calendar-data.php';
require_once __DIR__ . '/includes/class-cdd-core-featured-event-policy.php';
require_once __DIR__ . '/includes/class-cdd-core-authors-list.php';
require_once __DIR__ . '/includes/seo/class-cdd-core-json-ld.php';
require_once __DIR__ . '/includes/seo/class-cdd-core-seo-document.php';

// Migration toolkit (ADR 0032): pure extractors usable without WordPress.
require_once __DIR__ . '/includes/migration/class-cdd-core-spanish-date.php';
require_once __DIR__ . '/includes/migration/class-cdd-core-dom.php';
require_once __DIR__ . '/includes/migration/class-cdd-core-share-extractor.php';
require_once __DIR__ . '/includes/migration/class-cdd-core-seo-extractor.php';
require_once __DIR__ . '/includes/migration/class-cdd-core-event-extractor.php';
require_once __DIR__ . '/includes/migration/class-cdd-core-blog-extractor.php';
require_once __DIR__ . '/includes/migration/class-cdd-core-gallery-extractor.php';
require_once __DIR__ . '/includes/migration/class-cdd-core-page-extractor.php';
require_once __DIR__ . '/includes/migration/class-cdd-core-media-inventory.php';
require_once __DIR__ . '/includes/migration/class-cdd-core-payload-builder.php';
require_once __DIR__ . '/includes/migration/class-cdd-core-importer.php';
require_once __DIR__ . '/includes/migration/class-cdd-core-content-converter.php';
require_once __DIR__ . '/includes/migration/class-cdd-core-convert-service.php';

// WordPress-facing functions (definitions only; safe to load without WP).
require_once __DIR__ . '/includes/post-types.php';
require_once __DIR__ . '/includes/taxonomies.php';
require_once __DIR__ . '/includes/meta.php';
require_once __DIR__ . '/includes/authors-guard.php';
require_once __DIR__ . '/includes/events.php';
require_once __DIR__ . '/includes/routing.php';
require_once __DIR__ . '/includes/seo.php';
require_once __DIR__ . '/includes/admin.php';

// Hook wiring only when WordPress is present (the unit bootstrap loads
// this file without WordPress to reach the pure domain classes).
if ( function_exists( 'add_action' ) ) {
	add_action( 'init', 'cdd_core_register_post_types' );
	add_action( 'init', 'cdd_core_register_taxonomies' );
	add_action( 'init', 'cdd_core_register_meta' );
	add_action( 'init', 'cdd_core_register_seo_meta' );
	add_action( 'init', 'cdd_core_register_rewrites' );
	add_action( 'init', 'cdd_core_maybe_upgrade', 20 );

	add_filter( 'query_vars', 'cdd_core_register_query_vars' );
	add_filter( 'author_rewrite_rules', 'cdd_core_disable_user_author_rewrites' );
	add_filter( 'request', 'cdd_core_block_user_author_requests' );
	add_action( 'pre_get_posts', 'cdd_core_include_attachments_in_album_archives' );
	add_action( 'template_redirect', 'cdd_core_serve_event_ics' );

	add_filter( 'locale', 'cdd_core_default_locale' );
	add_filter( 'wp_robots', 'cdd_core_seo_robots' );
	add_filter( 'wp_sitemaps_add_provider', 'cdd_core_seo_sitemap_provider', 10, 2 );
	add_filter( 'wp_sitemaps_taxonomies', 'cdd_core_seo_sitemap_taxonomies' );
	add_action( 'admin_menu', 'cdd_core_register_admin_pages' );

	add_filter( 'wp_insert_post_data', 'cdd_core_guard_post_publish', 10, 2 );
	add_filter( 'rest_pre_insert_post', 'cdd_core_rest_guard_post_publish', 10, 2 );
	add_action( 'wp_after_insert_post', 'cdd_core_clear_requested_authors' );
	add_filter( 'update_post_metadata', 'cdd_core_protect_published_authors_update', 10, 4 );
	add_filter( 'delete_post_metadata', 'cdd_core_protect_published_authors_delete', 10, 3 );

	register_activation_hook( CDD_CORE_PLUGIN_FILE, 'cdd_core_activate' );

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		require_once __DIR__ . '/includes/migration/class-cdd-core-cli.php';
		Cdd_Core_CLI::register();
	}
}
