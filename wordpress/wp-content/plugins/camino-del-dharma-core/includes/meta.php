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
 * Sanitizes the modality against the doc 03 list.
 *
 * @param mixed $value Raw meta value.
 */
function cdd_core_sanitize_event_modality( $value ): string {
	$allowed = array( 'presencial', 'virtual', 'híbrido' );

	return in_array( $value, $allowed, true ) ? $value : '';
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
