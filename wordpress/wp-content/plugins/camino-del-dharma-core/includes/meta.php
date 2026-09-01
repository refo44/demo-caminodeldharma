<?php
/**
 * Event metadata (doc 03 §3) and the post `authors` relationship
 * (ADR 0037 §6): registration and sanitization.
 *
 * Native fields stay native: title = event name, content = description,
 * featured image = poster. Meta only complements what core lacks.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers event meta and the post authors relationship.
 */
function cdd_core_register_meta() {
	$string_meta = array(
		'event_date'       => 'cdd_core_sanitize_event_date',
		'event_end'        => 'cdd_core_sanitize_event_date',
		'event_place'      => 'sanitize_text_field',
		'event_modality'   => 'cdd_core_sanitize_event_modality',
		'event_signup_url' => 'esc_url_raw',
	);

	foreach ( $string_meta as $meta_key => $sanitize_callback ) {
		register_post_meta(
			'event',
			$meta_key,
			array(
				'type'              => 'string',
				'single'            => true,
				'sanitize_callback' => $sanitize_callback,
				'auth_callback'     => 'cdd_core_meta_auth',
				'show_in_rest'      => true,
			)
		);
	}

	register_post_meta(
		'event',
		'event_status',
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => 'vigente',
			'sanitize_callback' => 'cdd_core_sanitize_event_status',
			'auth_callback'     => 'cdd_core_meta_auth',
			'show_in_rest'      => true,
		)
	);

	foreach ( array( 'event_featured', 'event_signup_payment' ) as $boolean_key ) {
		register_post_meta(
			'event',
			$boolean_key,
			array(
				'type'              => 'boolean',
				'single'            => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'auth_callback'     => 'cdd_core_meta_auth',
				'show_in_rest'      => true,
			)
		);
	}

	register_post_meta(
		'event',
		'event_calendar_dates',
		array(
			'type'              => 'array',
			'single'            => true,
			'sanitize_callback' => 'cdd_core_sanitize_event_calendar_dates',
			'auth_callback'     => 'cdd_core_meta_auth',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array( 'type' => 'string' ),
				),
			),
		)
	);

	foreach ( array( 'event', 'post' ) as $shareable ) {
		foreach ( array( 'share_whatsapp', 'share_x', 'share_threads' ) as $share_key ) {
			register_post_meta(
				$shareable,
				$share_key,
				array(
					'type'              => 'string',
					'single'            => true,
					'default'           => '',
					'sanitize_callback' => 'cdd_core_sanitize_share_template',
					'auth_callback'     => 'cdd_core_meta_auth',
					'show_in_rest'      => true,
				)
			);
		}
	}

	register_post_meta(
		'post',
		'authors',
		array(
			'type'              => 'array',
			'single'            => true,
			'sanitize_callback' => 'cdd_core_sanitize_authors',
			'auth_callback'     => 'cdd_core_meta_auth',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array( 'type' => 'integer' ),
				),
			),
		)
	);
}

/**
 * Meta auth: whoever can edit the post can edit its domain meta.
 *
 * @param bool   $allowed   Unused default.
 * @param string $meta_key  Unused meta key.
 * @param int    $object_id Post ID.
 */
function cdd_core_meta_auth( $allowed, $meta_key, $object_id ) {
	return current_user_can( 'edit_post', $object_id );
}

/**
 * Whether a value is a real calendar date in Y-m-d form.
 *
 * @param mixed $value Candidate value.
 */
function cdd_core_is_ymd( $value ): bool {
	if ( ! is_string( $value ) ) {
		return false;
	}
	$date = DateTimeImmutable::createFromFormat( '!Y-m-d', $value );

	return ( false !== $date && $date->format( 'Y-m-d' ) === $value );
}

/**
 * Sanitizes an event date: a valid Y-m-d date or empty.
 *
 * @param mixed $value Raw meta value.
 */
function cdd_core_sanitize_event_date( $value ): string {
	return cdd_core_is_ymd( $value ) ? $value : '';
}

/**
 * Sanitizes the modality as the published free-text copy (plain text,
 * no markup). The doc 03 select (presencial/virtual/híbrido) does not
 * fit the descriptive modalities production publishes — «Híbrida —
 * bienvenida, orientación, seis sesiones virtuales y un encuentro
 * presencial» — and OWN-007 makes that copy authoritative (WU-07;
 * discrepancy recorded in the execution state).
 *
 * @param mixed $value Raw meta value.
 */
function cdd_core_sanitize_event_modality( $value ): string {
	return is_string( $value ) ? sanitize_text_field( $value ) : '';
}

/**
 * Sanitizes the stored status flag. Only 'cancelado' carries editorial
 * meaning; anything unknown falls back to 'vigente' (request-time
 * resolution is authoritative anyway — OWN-013).
 *
 * @param mixed $value Raw meta value.
 */
function cdd_core_sanitize_event_status( $value ): string {
	$allowed = array( 'vigente', 'finalizado', 'cancelado' );

	return in_array( $value, $allowed, true ) ? $value : 'vigente';
}

/**
 * Sanitizes the optional session-date list the calendar marks (the static
 * calendar marks course sessions, not a contiguous range).
 *
 * @param mixed $value Raw meta value.
 */
function cdd_core_sanitize_event_calendar_dates( $value ): array {
	if ( ! is_array( $value ) ) {
		return array();
	}

	return array_values( array_filter( $value, 'cdd_core_is_ymd' ) );
}

/**
 * Sanitizes one share message template (WU-08A). The value is plain text
 * injected into share intent URLs, never into HTML: markup goes, but the
 * line breaks and the {{SHARE_URL}} placeholder are the message's own
 * structure and survive untouched (static/assets/js/share.js).
 *
 * @param mixed $value Raw meta value.
 */
function cdd_core_sanitize_share_template( $value ): string {
	if ( ! is_string( $value ) ) {
		return '';
	}

	$text = str_replace( array( "\r\n", "\r" ), "\n", $value );

	return trim( wp_strip_all_tags( $text ) );
}

/**
 * Sanitizes the authors relationship: an ordered, unique list of
 * *published* blog_author IDs (ADR 0037 §6).
 *
 * @param mixed $value Raw meta value.
 */
function cdd_core_sanitize_authors( $value ): array {
	$published = array();

	foreach ( Cdd_Core_Authors_List::normalize( $value ) as $author_id ) {
		if ( 'blog_author' === get_post_type( $author_id ) && 'publish' === get_post_status( $author_id ) ) {
			$published[] = $author_id;
		}
	}

	return $published;
}

/**
 * Registers the editable head metadata (WU-08B). The published titles,
 * descriptions, keywords and Open Graph copy are hand-written production
 * content (ADR 0034, OWN-007), so they live as meta an editor can
 * rewrite from wp-admin — never as strings a template regenerates.
 */
function cdd_core_register_seo_meta() {
	$head_meta = array(
		'seo_title'       => 'sanitize_text_field',
		'seo_description' => 'sanitize_text_field',
		'seo_keywords'    => 'sanitize_text_field',
		'og_title'        => 'sanitize_text_field',
		'og_description'  => 'sanitize_text_field',
		'seo_related_url' => 'esc_url_raw',
	);

	foreach ( array( 'page', 'post', 'event' ) as $post_type ) {
		foreach ( $head_meta as $meta_key => $sanitize_callback ) {
			register_post_meta(
				$post_type,
				$meta_key,
				array(
					'type'              => 'string',
					'single'            => true,
					'default'           => '',
					'sanitize_callback' => $sanitize_callback,
					'auth_callback'     => 'cdd_core_meta_auth',
					'show_in_rest'      => true,
				)
			);
		}
	}

	// The published attendance mode of an event. Free-text modality is
	// editorial copy (OWN-007) and cannot be parsed into schema.org
	// reliably, so the machine-readable value is its own field; empty
	// means the JSON-LD omits it rather than guessing.
	register_post_meta(
		'event',
		'event_attendance_mode',
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'cdd_core_sanitize_attendance_mode',
			'auth_callback'     => 'cdd_core_meta_auth',
			'show_in_rest'      => true,
		)
	);

	// The optional JSON-LD fields WordPress cannot re-derive (course
	// type, audience, facilitator, published price). Stored as JSON and
	// merged *under* the generated node, so a generated field always wins.
	register_post_meta(
		'event',
		'seo_jsonld_extra',
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'cdd_core_sanitize_jsonld_extra',
			'auth_callback'     => 'cdd_core_meta_auth',
			'show_in_rest'      => true,
		)
	);

	// The addressRegion each city publishes: real institutional data no
	// WordPress field carries, editable on the term itself.
	register_term_meta(
		'event_city',
		'cdd_region',
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
		)
	);
}

/**
 * Sanitizes the schema.org attendance mode. Anything unknown becomes
 * empty: the JSON-LD then omits the field instead of inventing one.
 *
 * @param mixed $value Raw meta value.
 */
function cdd_core_sanitize_attendance_mode( $value ): string {
	return in_array( $value, array( 'offline', 'online', 'mixed' ), true ) ? $value : '';
}

/**
 * Sanitizes the stored JSON-LD extras: valid JSON object or empty.
 *
 * @param mixed $value Raw meta value.
 */
function cdd_core_sanitize_jsonld_extra( $value ): string {
	if ( ! is_string( $value ) || '' === trim( $value ) ) {
		return '';
	}

	$decoded = json_decode( $value, true );
	if ( ! is_array( $decoded ) ) {
		return '';
	}

	return (string) wp_json_encode( $decoded );
}
