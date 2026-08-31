<?php
/**
 * Plugin Name: Camino del Dharma Core
 * Plugin URI: https://caminodeldharma.org
 * Description: Domain plugin for Comunidad Buddhista Camino del Dharma — content model, routing and migration tooling (ADR 0024).
 * Version: 0.2.0
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

define( 'CDD_CORE_VERSION', '0.2.0' );
define( 'CDD_CORE_PLUGIN_FILE', __FILE__ );

// Pure domain classes (no WordPress APIs; unit-testable without a boot).
require_once __DIR__ . '/includes/class-cdd-core-event-status.php';
require_once __DIR__ . '/includes/class-cdd-core-ics-generator.php';
require_once __DIR__ . '/includes/class-cdd-core-calendar-data.php';
require_once __DIR__ . '/includes/class-cdd-core-featured-event-policy.php';
require_once __DIR__ . '/includes/class-cdd-core-authors-list.php';

// WordPress-facing functions (definitions only; safe to load without WP).
require_once __DIR__ . '/includes/post-types.php';
require_once __DIR__ . '/includes/taxonomies.php';
require_once __DIR__ . '/includes/meta.php';
require_once __DIR__ . '/includes/authors-guard.php';
require_once __DIR__ . '/includes/events.php';
require_once __DIR__ . '/includes/routing.php';

// Hook wiring only when WordPress is present (the unit bootstrap loads
// this file without WordPress to reach the pure domain classes).
if ( function_exists( 'add_action' ) ) {
	add_action( 'init', 'cdd_core_register_post_types' );
	add_action( 'init', 'cdd_core_register_taxonomies' );
	add_action( 'init', 'cdd_core_register_meta' );
	add_action( 'init', 'cdd_core_register_rewrites' );
	add_action( 'init', 'cdd_core_maybe_upgrade', 20 );

	add_filter( 'query_vars', 'cdd_core_register_query_vars' );
	add_filter( 'author_rewrite_rules', 'cdd_core_disable_user_author_rewrites' );
	add_filter( 'request', 'cdd_core_block_user_author_requests' );
	add_action( 'pre_get_posts', 'cdd_core_include_attachments_in_album_archives' );
	add_action( 'template_redirect', 'cdd_core_serve_event_ics' );

	add_filter( 'wp_insert_post_data', 'cdd_core_guard_post_publish', 10, 2 );
	add_filter( 'rest_pre_insert_post', 'cdd_core_rest_guard_post_publish', 10, 2 );
	add_action( 'wp_after_insert_post', 'cdd_core_clear_requested_authors' );
	add_filter( 'update_post_metadata', 'cdd_core_protect_published_authors_update', 10, 4 );
	add_filter( 'delete_post_metadata', 'cdd_core_protect_published_authors_delete', 10, 3 );

	register_activation_hook( CDD_CORE_PLUGIN_FILE, 'cdd_core_activate' );
}
