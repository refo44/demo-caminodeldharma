<?php
/**
 * Recognition of the WordPress installer's demo content (D-02 / OWN-024).
 *
 * A fresh WordPress install publishes «Hello world!» and «Sample Page» and
 * drafts a «Privacy Policy» page. That content displaces real content —
 * locally the demo entry pushes «Estamos conectados, pero seguimos solos»
 * out of the home «Del blog» section and of /blog — and it must never be
 * visible in staging or production.
 *
 * Pure domain code: no WordPress APIs. The decision is taken from the
 * installer's own contract (post type + default slug + the status the
 * installer leaves the object in), never from post IDs: a site that already
 * holds production content cannot be cleaned by deleting 1, 2 and 3.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tells the installer's demo content apart from real content.
 */
final class Cdd_Core_Installer_Demo_Content {

	/**
	 * Default slugs the installer writes, per post type.
	 *
	 * WordPress translates these slugs when the install runs in another
	 * language (`_x( 'hello-world', 'Default post slug' )`), so the Spanish
	 * defaults are listed too: Hostinger installs may be created in Spanish
	 * even though the runbook installs the es_CO pack afterwards.
	 */
	const DEFAULT_SLUGS = array(
		'post' => array( 'hello-world', 'hola-mundo' ),
		'page' => array( 'sample-page', 'pagina-de-ejemplo', 'privacy-policy', 'politica-de-privacidad' ),
	);

	/**
	 * The statuses the installer leaves its demo content in: the entry and
	 * the sample page published, the privacy policy drafted. Anything an
	 * editor moved elsewhere is treated as their content, not as demo.
	 */
	const INSTALLER_STATUSES = array( 'publish', 'draft' );

	/**
	 * The post types the installer writes demo content into.
	 */
	public function post_types(): array {
		return array_keys( self::DEFAULT_SLUGS );
	}

	/**
	 * Every default slug, flattened, for a single lookup.
	 */
	public function default_slugs(): array {
		return array_merge( ...array_values( self::DEFAULT_SLUGS ) );
	}

	/**
	 * Whether a post is demo content left behind by the installer.
	 *
	 * @param array $post Descriptor: post_type, slug, status and
	 *                    is_imported (true when the object carries the
	 *                    importer's source key, ADR 0033).
	 */
	public function is_installer_demo( array $post ): bool {
		if ( ! empty( $post['is_imported'] ) ) {
			return false;
		}

		if ( ! in_array( (string) ( $post['status'] ?? '' ), self::INSTALLER_STATUSES, true ) ) {
			return false;
		}

		$slugs = self::DEFAULT_SLUGS[ (string) ( $post['post_type'] ?? '' ) ] ?? array();

		return in_array( (string) ( $post['slug'] ?? '' ), $slugs, true );
	}
}
