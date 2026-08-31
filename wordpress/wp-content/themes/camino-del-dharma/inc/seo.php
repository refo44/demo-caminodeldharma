<?php
/**
 * Head assembly (docs/12 §7): the theme prints the SEO document the
 * domain plugin resolves, and escapes every value for its context.
 *
 * No policy lives here. Which URL is canonical, which archive stays out
 * of the index and which structured-data node a request publishes are
 * domain decisions of camino-del-dharma-core (ADR 0024); this file is
 * the printer. If the plugin is not active the theme prints nothing extra
 * and WordPress keeps its own defaults.
 *
 * @package Camino_Del_Dharma
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the domain plugin can resolve this request's head.
 */
function camino_del_dharma_has_seo(): bool {
	return function_exists( 'cdd_core_seo_document' );
}

/**
 * Prints the resolved head document.
 *
 * Runs at priority 1 so the document opens with title, description and
 * canonical, the way the published pages do.
 */
function camino_del_dharma_print_seo() {
	if ( ! camino_del_dharma_has_seo() ) {
		return;
	}

	foreach ( cdd_core_seo_document() as $tag ) {
		switch ( $tag['tag'] ) {
			case 'title':
				echo "\t<title>" . esc_html( $tag['text'] ) . "</title>\n";
				break;

			case 'meta':
			case 'link':
				$attributes = '';
				foreach ( $tag['attr'] as $name => $value ) {
					$attributes .= ' ' . esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
				}
				echo "\t<" . esc_html( $tag['tag'] ) . $attributes . ">\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- every name and value is escaped as an attribute above.
				break;

			case 'jsonld':
				echo "\t<script type=\"application/ld+json\">" . wp_json_encode( $tag['document'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
				break;
		}
	}
}
add_action( 'wp_head', 'camino_del_dharma_print_seo', 1 );

/**
 * The document title comes from the plugin, so core must not print a
 * second one.
 */
function camino_del_dharma_suppress_core_head() {
	if ( ! camino_del_dharma_has_seo() ) {
		return;
	}

	remove_action( 'wp_head', 'rel_canonical' );
	remove_action( 'wp_head', '_wp_render_title_tag', 1 );
}
add_action( 'wp', 'camino_del_dharma_suppress_core_head' );
