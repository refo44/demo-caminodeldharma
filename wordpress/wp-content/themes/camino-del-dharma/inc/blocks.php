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
		'eventos-calendar'    => 'camino_del_dharma_render_eventos_calendar',
		'eventos-listado'     => 'camino_del_dharma_render_eventos_listado',
		'evento-destacado'    => 'camino_del_dharma_render_evento_destacado',
		'evento-tipo'         => 'camino_del_dharma_render_evento_tipo',
		'evento-meta'         => 'camino_del_dharma_render_evento_meta',
		'evento-cta'          => 'camino_del_dharma_render_evento_cta',
		'evento-acciones'     => 'camino_del_dharma_render_evento_acciones',
		'entrada-cabecera'    => 'camino_del_dharma_render_entrada_cabecera',
		'entrada-compartir'   => 'camino_del_dharma_render_entrada_compartir',
		'blog-listado'        => 'camino_del_dharma_render_blog_listado',
		'blog-recientes'      => 'camino_del_dharma_render_blog_recientes',
		'autor-ficha'         => 'camino_del_dharma_render_autor_ficha',
		'album-galeria'       => 'camino_del_dharma_render_album_galeria',
		'contacto-formulario' => 'camino_del_dharma_render_contacto_formulario',
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

	$current = cdd_core_current_events();
	if ( ! empty( $current ) ) {
		camino_del_dharma_enqueue_behavior( array( 'share', 'calendar-dialog' ) );
	}

	return Camino_Del_Dharma_Renderers::events_listing( $current, cdd_core_past_events() );
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
 * The dialog triggers of the current single: «Añadir al calendario» and
 * «Compartir». Only a current event renders them (OWN-012), and the two
 * dialog scripts are enqueued only when they do.
 */
function camino_del_dharma_render_evento_acciones(): string {
	$event = get_post();
	if ( ! $event instanceof WP_Post || ! function_exists( 'cdd_core_event_is_current' ) ) {
		return '';
	}

	$current = cdd_core_event_is_current( $event );
	if ( ! $current ) {
		return '';
	}

	camino_del_dharma_enqueue_behavior( array( 'share', 'calendar-dialog' ) );

	return Camino_Del_Dharma_Renderers::event_actions(
		$event,
		true,
		function_exists( 'cdd_core_event_calendar_payload' ) ? cdd_core_event_calendar_payload( $event ) : array()
	);
}

/**
 * The share control of the current blog entry.
 */
function camino_del_dharma_render_entrada_compartir(): string {
	$post = get_post();
	if ( ! $post instanceof WP_Post ) {
		return '';
	}

	camino_del_dharma_enqueue_behavior( array( 'share' ) );

	return Camino_Del_Dharma_Renderers::entry_share( $post );
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

/**
 * Enqueues the behavior scripts a rendered block needs, by file name
 * (docs/12 §7: nothing global that only one view uses).
 *
 * @param array $scripts Script base names under assets/js.
 */
function camino_del_dharma_enqueue_behavior( array $scripts ) {
	foreach ( $scripts as $script ) {
		$path = get_template_directory() . '/assets/js/' . $script . '.js';
		if ( ! file_exists( $path ) ) {
			continue;
		}

		wp_enqueue_script(
			'camino-del-dharma-' . $script,
			get_template_directory_uri() . '/assets/js/' . $script . '.js',
			array(),
			(string) filemtime( $path ),
			true
		);
	}
}

/**
 * Restores the accessible name of the mantra players (docs/19). The
 * native core/audio block carries no aria-label, and the published
 * players name themselves («Recitación de Amitābha»); the caption holds
 * that name, so the theme puts it back on the element a screen reader
 * announces. Audio blocks without a caption are left alone.
 *
 * @param string $block_content Rendered block HTML.
 * @param array  $block         Parsed block.
 */
function camino_del_dharma_name_audio_blocks( $block_content, $block ) {
	if ( 'core/audio' !== ( $block['blockName'] ?? '' ) || false !== strpos( $block_content, 'aria-label' ) ) {
		return $block_content;
	}

	if ( ! preg_match( '#<figcaption[^>]*>(.*?)</figcaption>#s', $block_content, $caption ) ) {
		return $block_content;
	}

	$name = trim( wp_strip_all_tags( $caption[1] ), " \t\n\r\0\x0B." );
	if ( '' === $name ) {
		return $block_content;
	}

	return preg_replace(
		'#<audio\b#',
		'<audio aria-label="' . esc_attr( $name ) . '"',
		$block_content,
		1
	);
}
add_filter( 'render_block', 'camino_del_dharma_name_audio_blocks', 10, 2 );

/**
 * The contact form (WU-09, ADR 0026/0041).
 *
 * The form itself belongs to Contact Form 7, provisioned by
 * camino-del-dharma-core. The block exists so the page content carries no
 * third-party shortcode: with CF7 absent or disabled the visitor reads the
 * channels that do work instead of a raw shortcode string or an empty
 * hole. That degraded state is the operational fallback ADR 0041 point 5
 * allows at cutover, so it is a real state of this site, not an error.
 */
function camino_del_dharma_render_contacto_formulario(): string {
	if ( function_exists( 'cdd_core_contact_form_available' ) && cdd_core_contact_form_available() ) {
		return cdd_core_contact_form_html();
	}

	$whatsapp = 'https://wa.me/573206627608';
	$mailbox  = function_exists( 'cdd_core_contact_form_recipient' )
		? cdd_core_contact_form_recipient()
		: 'caminodeldharma1@gmail.com';

	return sprintf(
		'<p class="contact-form-unavailable section-gap">%1$s <a href="%2$s" target="_blank" rel="noopener noreferrer">%3$s<span class="visually-hidden"> %4$s</span></a> %5$s <a href="%6$s">%7$s</a>.</p>',
		esc_html__( 'El formulario no está disponible en este momento. Escríbenos por WhatsApp al', 'camino-del-dharma' ),
		esc_url( $whatsapp ),
		esc_html__( '+57 320 662 7608', 'camino-del-dharma' ),
		esc_html__( '(abre en nueva pestaña)', 'camino-del-dharma' ),
		esc_html__( 'o al correo', 'camino-del-dharma' ),
		esc_url( 'mailto:' . $mailbox ),
		esc_html( $mailbox )
	);
}
