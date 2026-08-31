<?php
/**
 * Theme bootstrap: setup and asset enqueueing only (docs/12 §7, §11.2).
 *
 * Domain (CPTs, taxonomies, roles, fields) lives in camino-del-dharma-core
 * (ADR 0024); design tokens live in theme.json (ADR 0029). If this file
 * grows beyond bootstrap duties, split it into inc/.
 *
 * @package Camino_Del_Dharma
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/inc/class-camino-del-dharma-format.php';
require_once __DIR__ . '/inc/class-camino-del-dharma-renderers.php';
require_once __DIR__ . '/inc/blocks.php';
require_once __DIR__ . '/inc/seo.php';

/**
 * Theme supports that theme.json does not cover.
 */
function camino_del_dharma_setup() {
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/main.css' );
}
add_action( 'after_setup_theme', 'camino_del_dharma_setup' );

/**
 * Enqueue the complementary stylesheet: layout, reading rhythm, and focus
 * states that Global Styles cannot express (docs/12 §7). Versioned by
 * mtime so deploys bust caches without manual bumps.
 */
function camino_del_dharma_enqueue_assets() {
	$stylesheet = get_template_directory() . '/assets/css/main.css';

	wp_enqueue_style(
		'camino-del-dharma-main',
		get_template_directory_uri() . '/assets/css/main.css',
		array(),
		(string) filemtime( $stylesheet )
	);

	$script = get_template_directory() . '/assets/js/main.js';

	wp_enqueue_script(
		'camino-del-dharma-main',
		get_template_directory_uri() . '/assets/js/main.js',
		array(),
		(string) filemtime( $script ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'camino_del_dharma_enqueue_assets' );

/**
 * Drops core's block-template skip link (docs/19 §10).
 *
 * A block theme is given one automatically, pointing at the same
 * `#main` the header pattern's published «Saltar al contenido» already
 * targets — so a keyboard user tabs through two identical controls, the
 * first of them in the admin language rather than the site's. The
 * published one stays; core's markup and its stylesheet go.
 */
function camino_del_dharma_remove_core_skip_link() {
	remove_action( 'wp_footer', 'the_block_template_skip_link' );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_block_template_skip_link' );
}
add_action( 'after_setup_theme', 'camino_del_dharma_remove_core_skip_link' );
