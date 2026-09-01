<?php
/**
 * Home featured-event selection (doc 03 §3 "Un evento en el Inicio").
 *
 * Pure domain code: no WordPress APIs. At most one current event reaches
 * the front page: a current featured one wins; otherwise the current event
 * with the nearest start date; a completed event never appears even if it
 * still carries the featured mark; with no current event, nothing renders.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Selects the single event note for the home page.
 */
final class Cdd_Core_Featured_Event_Policy {

	/**
	 * Selects the event to show, or null when no current event exists.
	 *
	 * @param array $events Event descriptors: is_current (bool),
	 *                      is_featured (bool), start (Y-m-d), plus any
	 *                      caller payload returned untouched.
	 */
	public function select( array $events ): ?array {
		$current = array();
		foreach ( $events as $event ) {
			if ( ! empty( $event['is_current'] ) ) {
				$current[] = $event;
			}
		}

		if ( empty( $current ) ) {
			return null;
		}

		$featured = array();
		foreach ( $current as $event ) {
			if ( ! empty( $event['is_featured'] ) ) {
				$featured[] = $event;
			}
		}

		$pool = empty( $featured ) ? $current : $featured;

		usort(
			$pool,
			static function ( array $a, array $b ): int {
				return self::compare_start( (string) ( $a['start'] ?? '' ), (string) ( $b['start'] ?? '' ) );
			}
		);

		return $pool[0];
	}

	/**
	 * Nearest start date: valid Y-m-d before empty. strcmp would put '' first.
	 *
	 * @param string $left  Start date (Y-m-d or empty).
	 * @param string $right Start date (Y-m-d or empty).
	 */
	private static function compare_start( string $left, string $right ): int {
		$left_empty  = ( '' === $left );
		$right_empty = ( '' === $right );

		if ( $left_empty === $right_empty ) {
			return strcmp( $left, $right );
		}

		return $left_empty ? 1 : -1;
	}
}
