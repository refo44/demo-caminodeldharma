<?php
/**
 * The inscription opening date published as offers.validFrom (issue #54).
 *
 * Pure domain code: no WordPress APIs. An editor value is an America/Bogota
 * wall clock. The graph publishes that instant with the Bogota offset.
 * An empty or unusable value stays empty: the event start and "now" are
 * never substitutes.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalizes the date the inscription opened.
 */
final class Cdd_Core_Event_Offer {

	/**
	 * The schema.org instant, or '' when the value is not a real date.
	 *
	 * @param mixed $value Raw editor or stored value.
	 */
	public static function valid_from( $value ): string {
		$stored = self::stored( $value );
		if ( '' === $stored ) {
			return '';
		}

		$zone    = new DateTimeZone( Cdd_Core_Event_Status::TIMEZONE );
		$instant = DateTimeImmutable::createFromFormat( '!Y-m-d\TH:i:s', $stored, $zone );
		if ( false === $instant ) {
			return '';
		}

		return $instant->format( 'Y-m-d\TH:i:sP' );
	}

	/**
	 * The value stored in post meta: a Bogota wall clock with seconds, so
	 * the block editor's datetime control can show it again.
	 *
	 * @param mixed $value Raw editor or stored value.
	 */
	public static function stored( $value ): string {
		if ( ! is_string( $value ) ) {
			return '';
		}

		$value = trim( $value );
		if ( '' === $value ) {
			return '';
		}

		$zone = new DateTimeZone( Cdd_Core_Event_Status::TIMEZONE );

		if ( 1 === preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
			$day = DateTimeImmutable::createFromFormat( '!Y-m-d', $value, $zone );
			if ( false === $day || $day->format( 'Y-m-d' ) !== $value ) {
				return '';
			}

			return $day->format( 'Y-m-d\T00:00:00' );
		}

		if ( 1 === preg_match( '/(?:Z|[+-]\d{2}:\d{2})$/', $value ) ) {
			try {
				$instant = new DateTimeImmutable( $value );
			} catch ( Exception ) {
				return '';
			}

			return $instant->setTimezone( $zone )->format( 'Y-m-d\TH:i:s' );
		}

		if ( 1 !== preg_match( '/^(\d{4}-\d{2}-\d{2})[T ](\d{2}):(\d{2})(?::(\d{2}))?$/', $value, $matches ) ) {
			return '';
		}

		$day = DateTimeImmutable::createFromFormat( '!Y-m-d', $matches[1], $zone );
		if ( false === $day || $day->format( 'Y-m-d' ) !== $matches[1] ) {
			return '';
		}

		$hour   = (int) $matches[2];
		$minute = (int) $matches[3];
		$second = ( isset( $matches[4] ) && '' !== $matches[4] ) ? (int) $matches[4] : 0;
		if ( $hour > 23 || $minute > 59 || $second > 59 ) {
			return '';
		}

		return $day->setTime( $hour, $minute, $second )->format( 'Y-m-d\TH:i:s' );
	}
}
