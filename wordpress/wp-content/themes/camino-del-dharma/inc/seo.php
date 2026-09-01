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
		if ( ! is_array( $tag ) || ! isset( $tag['tag'] ) ) {
			continue;
		}

		switch ( $tag['tag'] ) {
			case 'title':
				$text = $tag['text'] ?? '';
				if ( '' === $text ) {
					break;
				}
				echo "\t<title>" . esc_html( $text ) . "</title>\n";
				break;

			case 'meta':
			case 'link':
				$attributes = '';
				foreach ( (array) ( $tag['attr'] ?? array() ) as $name => $value ) {
					$attributes .= ' ' . esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
				}
				echo "\t<" . esc_html( $tag['tag'] ) . $attributes . ">\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- every name and value is escaped as an attribute above.
				break;

			case 'jsonld':
				$json = wp_json_encode(
					$tag['document'] ?? array(),
					JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP
				);
				if ( false === $json ) {
					break;
				}
				echo "\t<script type=\"application/ld+json\">" . $json . "</script>\n";
				break;
		}
	}
}
add_action( 'wp_head', 'camino_del_dharma_print_seo', 1 );

/**
 * Title, canonical and robots come from the plugin, so core must not
 * print a second one of each.
 *
 * Runs on `wp_head` itself, at priority 0: a block theme registers its
 * unconditional title printer inside locate_block_template(), which
 * happens after `wp` and after `template_redirect`, so an earlier hook
 * would remove a callback that is not there yet. Core's own robots and
 * title callbacks sit at priority 1, which is why the priority is given
 * explicitly — removing them at the default 10 silently does nothing.
 */
function camino_del_dharma_suppress_core_head() {
	if ( ! camino_del_dharma_has_seo() ) {
		return;
	}

	remove_action( 'wp_head', 'rel_canonical' );
	remove_action( 'wp_head', 'wp_robots', 1 );
	remove_action( 'wp_head', '_wp_render_title_tag', 1 );
	remove_action( 'wp_head', '_block_template_render_title_tag', 1 );
}
add_action( 'wp_head', 'camino_del_dharma_suppress_core_head', 0 );
