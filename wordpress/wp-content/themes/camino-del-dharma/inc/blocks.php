<?php
/**
 * Dynamic block registration of the theme (WU-07, docs/12 §2, §11.3).
 *
 * Each block is presentation over data the domain plugin resolves
 * (camino-del-dharma-core, ADR 0024). When the plugin is inactive the
 * blocks degrade to empty output instead of fataling.
 *
 * @package Camino_Del_Dharma
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the camino-del-dharma dynamic blocks.
 */
function camino_del_dharma_register_blocks() {
	$blocks = array(
		'eventos-calendar' => 'camino_del_dharma_render_eventos_calendar',
		'eventos-listado'  => 'camino_del_dharma_render_eventos_listado',
		'evento-destacado' => 'camino_del_dharma_render_evento_destacado',
		'evento-tipo'      => 'camino_del_dharma_render_evento_tipo',
		'evento-meta'      => 'camino_del_dharma_render_evento_meta',
		'evento-cta'       => 'camino_del_dharma_render_evento_cta',
		'entrada-cabecera' => 'camino_del_dharma_render_entrada_cabecera',
		'blog-listado'     => 'camino_del_dharma_render_blog_listado',
		'blog-recientes'   => 'camino_del_dharma_render_blog_recientes',
		'autor-ficha'      => 'camino_del_dharma_render_autor_ficha',
		'album-galeria'    => 'camino_del_dharma_render_album_galeria',
	);

	foreach ( $blocks as $name => $render_callback ) {
		register_block_type(
			'camino-del-dharma/' . $name,
			array(
				'api_version'     => 3,
				'render_callback' => $render_callback,
			)
		);
	}
}
add_action( 'init', 'camino_del_dharma_register_blocks' );

/**
 * The month calendar grid (doc 03 §3). Cells link to the listing card
 * anchors, exactly as published; the tooltip behavior script is enqueued
 * only when the block renders.
 */
function camino_del_dharma_render_eventos_calendar(): string {
	if ( ! function_exists( 'cdd_core_calendar_month_data' ) ) {
		return '';
	}

	$data = cdd_core_calendar_month_data();

	foreach ( $data['days'] as $day => $cell ) {
		foreach ( $cell['events'] as $index => $event ) {
			$path = (string) wp_parse_url( (string) $event['url'], PHP_URL_PATH );
			if ( '' !== $path ) {
				$data['days'][ $day ]['events'][ $index ]['url'] = '#' . basename( untrailingslashit( $path ) );
			}
		}
	}

	wp_enqueue_script(
		'camino-del-dharma-calendar',
		get_template_directory_uri() . '/assets/js/calendar-tooltips.js',
		array(),
		(string) filemtime( get_template_directory() . '/assets/js/calendar-tooltips.js' ),
		true
	);

	return Camino_Del_Dharma_Renderers::calendar( $data );
}

/**
 * The events archive listing: current events in full, completed events
 * as the compact year archive (doc 03 §3).
 */
function camino_del_dharma_render_eventos_listado(): string {
	if ( ! function_exists( 'cdd_core_current_events' ) || ! function_exists( 'cdd_core_past_events' ) ) {
		return '';
	}

	return Camino_Del_Dharma_Renderers::events_listing( cdd_core_current_events(), cdd_core_past_events() );
}

/**
 * The home note of the selected current event, or nothing (doc 03 §3).
 */
function camino_del_dharma_render_evento_destacado(): string {
	if ( ! function_exists( 'cdd_core_featured_home_event' ) ) {
		return '';
	}

	return Camino_Del_Dharma_Renderers::featured_event( cdd_core_featured_home_event() );
}

/**
 * The event_type label of the current single.
 */
function camino_del_dharma_render_evento_tipo(): string {
	$event = get_post();

	return $event instanceof WP_Post ? Camino_Del_Dharma_Renderers::event_type_label( $event ) : '';
}

/**
 * The meta list (Fecha / Lugar / Modalidad) of the current single.
 */
function camino_del_dharma_render_evento_meta(): string {
	$event = get_post();

	return $event instanceof WP_Post ? Camino_Del_Dharma_Renderers::event_meta( $event ) : '';
}

/**
 * The signup CTA of the current single — only while current (OWN-012).
 */
function camino_del_dharma_render_evento_cta(): string {
	$event = get_post();
	if ( ! $event instanceof WP_Post || ! function_exists( 'cdd_core_event_is_current' ) ) {
		return '';
	}

	return Camino_Del_Dharma_Renderers::event_cta( $event, cdd_core_event_is_current( $event ) );
}

/**
 * The blog entry header with the ADR 0037 byline.
 */
function camino_del_dharma_render_entrada_cabecera(): string {
	$post = get_post();

	return $post instanceof WP_Post ? Camino_Del_Dharma_Renderers::entry_header( $post ) : '';
}

/**
 * The /blog listing over the main query's posts.
 */
function camino_del_dharma_render_blog_listado(): string {
	$posts = array_filter(
		$GLOBALS['wp_query']->posts ?? array(),
		static function ( $post ) {
			return $post instanceof WP_Post;
		}
	);

	return Camino_Del_Dharma_Renderers::blog_list( array_values( $posts ) );
}

/**
 * The home «Del blog» cards: the two latest published entries.
 */
function camino_del_dharma_render_blog_recientes(): string {
	$posts = get_posts(
		array(
			'post_type'   => 'post',
			'post_status' => 'publish',
			'numberposts' => 2,
		)
	);

	return Camino_Del_Dharma_Renderers::home_blog_cards( $posts );
}

/**
 * The blog_author profile with its related entries (ADR 0037).
 */
function camino_del_dharma_render_autor_ficha(): string {
	$author = get_post();
	if ( ! $author instanceof WP_Post || 'blog_author' !== $author->post_type ) {
		return '';
	}

	$posts = function_exists( 'cdd_core_posts_by_blog_author' ) ? cdd_core_posts_by_blog_author( $author->ID ) : array();

	return Camino_Del_Dharma_Renderers::author_profile( $author, $posts );
}

/**
 * The gallery_album term view as a native gallery (ADR 0036/0021).
 */
function camino_del_dharma_render_album_galeria(): string {
	$term = get_queried_object();
	if ( ! $term instanceof WP_Term || 'gallery_album' !== $term->taxonomy ) {
		return '';
	}

	$attachments = function_exists( 'cdd_core_album_attachments' ) ? cdd_core_album_attachments( $term ) : array();

	return Camino_Del_Dharma_Renderers::album_gallery( $term, $attachments );
}
