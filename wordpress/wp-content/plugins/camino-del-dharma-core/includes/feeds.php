<?php
/**
 * Native feed surface: 404 at cutover (ADR 0044 / OWN-025 / D-03).
 *
 * Published production answers 404 on `/feed`, `/blog/feed`,
 * `/comments/feed` and every core alias, and none of those URLs is in
 * `docs/11-arbol-urls-final.md` — if a URL is not in the tree, it does
 * not exist. WordPress registers the whole surface anyway and advertises
 * it in the head, so the domain plugin closes it.
 *
 * Not a permanent ban: a public RSS is a later decision (POST-010) that
 * needs its own row in the URL tree and the redirect ledger. Reopening it
 * means dropping the two hooks wired for this file, nothing else.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Turns any incoming feed request into a real 404 before the main query
 * runs, so no feed document is ever rendered and the theme 404 template
 * takes over.
 *
 * Every alias core registers — `feed`, `rdf`, `rss`, `rss2`, `atom`,
 * pretty or as `?feed=`, for the site, the posts page, an entry's
 * comments, an archive or a CPT — resolves to the `feed` query var, so
 * one guard covers the surface. Presence, not a non-empty value: core
 * treats `/?feed=` as the default feed. Same shape as the WP-user
 * author guard (ADR 0037 §5): the request keeps nothing but the 404.
 *
 * @param array $query_vars Parsed request query vars.
 */
function cdd_core_block_feed_requests( $query_vars ) {
	if ( isset( $query_vars['feed'] ) ) {
		return array( 'error' => '404' );
	}

	return $query_vars;
}

/**
 * Drops core's RSS/Atom autodiscovery: nothing may advertise a feed that
 * answers 404 (ADR 0044 §2).
 *
 * Runs on `wp_head` itself, at priority 0, and names the priorities core
 * registers (`feed_links` at 2, `feed_links_extra` at 3) — removing them
 * at the default 10 silently does nothing. The calendar `rel=alternate`
 * of a current event (OWN-014) comes from the plugin's SEO document and
 * is untouched.
 */
function cdd_core_disable_feed_autodiscovery() {
	remove_action( 'wp_head', 'feed_links', 2 );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
}
