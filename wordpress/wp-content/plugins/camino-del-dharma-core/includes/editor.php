<?php
/**
 * Block-editor surface of the `authors` relationship (ADR 0037 §4/§6).
 *
 * Two halves of the same rule — the public byline is a `blog_author`
 * ficha, never the WordPress user who signed in:
 *
 * 1. The «Autores del blog» panel: a native `PluginDocumentSettingPanel`
 *    (ADR 0025 native-first, ADR 0042) that writes the relationship
 *    through `core/editor`, so Publicar carries `meta.authors` in the
 *    same REST body the guard reads. No classic metabox: a metabox that
 *    only fills the DOM is exactly the META-001 failure.
 * 2. The core Author control is taken out of the editor, so nobody
 *    mistakes it for the byline. WordPress 7.1 renders it as a row of
 *    the Summary panel, not as a panel of its own, so there is no
 *    `removeEditorPanel()` name to drop; the editor renders the row only
 *    when the post carries the `wp:action-assign-author` REST link, and
 *    that link is what goes. `post_author` itself is untouched — the
 *    post type keeps `author` support, so the list table column, quick
 *    edit and revisions still answer who created and saved the entry.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const CDD_CORE_AUTHORS_PANEL_HANDLE = 'cdd-core-authors-panel';

/**
 * The block-editor packages the panel runs on. Every one of them is a
 * real call site: the panel registers itself (`wp-plugins`), renders in
 * the document sidebar (`wp-edit-post` / `wp-editor`), reads and writes
 * the post through the store (`wp-data`), searches fichas over REST
 * (`wp-api-fetch`), renders with `wp-element` / `wp-components` and
 * speaks Spanish through `wp-i18n`.
 */
function cdd_core_editor_script_dependencies(): array {
	return array(
		'wp-plugins',
		'wp-edit-post',
		'wp-editor',
		'wp-data',
		'wp-api-fetch',
		'wp-element',
		'wp-components',
		'wp-i18n',
	);
}

/**
 * Cache-busting version of a plugin asset: its mtime when readable, the
 * plugin version otherwise.
 *
 * @param string $path Absolute path of the asset.
 */
function cdd_core_asset_version( string $path ): string {
	$mtime = is_readable( $path ) ? filemtime( $path ) : false;

	return false !== $mtime ? (string) $mtime : CDD_CORE_VERSION;
}

/**
 * Registers the panel script. Registration is unconditional and cheap;
 * only the enqueue is scoped.
 */
function cdd_core_register_editor_assets() {
	$relative = 'assets/js/authors-panel.js';

	wp_register_script(
		CDD_CORE_AUTHORS_PANEL_HANDLE,
		plugins_url( $relative, CDD_CORE_PLUGIN_FILE ),
		cdd_core_editor_script_dependencies(),
		cdd_core_asset_version( plugin_dir_path( CDD_CORE_PLUGIN_FILE ) . $relative ),
		true
	);

	wp_set_script_translations( CDD_CORE_AUTHORS_PANEL_HANDLE, 'camino-del-dharma-core' );
}

/**
 * Whether a screen is the block editor of a blog entry: the relationship
 * only exists on `post`, so the panel never loads on an event, a page, a
 * ficha, the site editor or the widgets screen.
 *
 * @param WP_Screen|null $screen Current admin screen.
 */
function cdd_core_is_post_editor_screen( $screen ): bool {
	return $screen instanceof WP_Screen
		&& 'post' === $screen->base
		&& 'post' === $screen->post_type;
}

/**
 * Enqueues the panel on post.php / post-new.php for a blog entry.
 */
function cdd_core_enqueue_editor_assets() {
	if ( ! function_exists( 'get_current_screen' ) ) {
		return;
	}
	if ( ! cdd_core_is_post_editor_screen( get_current_screen() ) ) {
		return;
	}

	wp_enqueue_script( CDD_CORE_AUTHORS_PANEL_HANDLE );
}

/**
 * Drops the `wp:action-assign-author` link from a blog entry, which is
 * what the block editor reads before rendering the core Author control
 * (ADR 0037 §4). Only the editorial affordance goes: `post_author` keeps
 * being stored, shown in the list table and recorded in revisions.
 *
 * @param WP_REST_Response|mixed $response Prepared REST response.
 */
function cdd_core_hide_editor_author_control( $response ) {
	if ( $response instanceof WP_REST_Response ) {
		$response->remove_link( 'https://api.w.org/action-assign-author' );
	}

	return $response;
}
