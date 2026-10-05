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

		if ( 1 === preg_match( '/^(\d{4}-\d{2}-\d{2})T(\d{2}):(\d{2}):(\d{2})(Z|[+-]\d{2}:\d{2})$/', $value, $matches ) ) {
			return self::stored_from_offset( $matches );
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

	/**
	 * A complete ISO instant, converted to the Bogota wall clock.
	 * The shape is checked before parsing, and a rolled-over calendar
	 * day is rejected.
	 *
	 * @param array $matches Date, time and offset captured from the value.
	 */
	private static function stored_from_offset( array $matches ): string {
		$hour   = (int) $matches[2];
		$minute = (int) $matches[3];
		$second = (int) $matches[4];
		if ( $hour > 23 || $minute > 59 || $second > 59 ) {
			return '';
		}

		if ( ! self::is_utc_offset( $matches[5] ) ) {
			return '';
		}

		$offset     = 'Z' === $matches[5] ? '+00:00' : $matches[5];
		$normalized = sprintf( '%sT%02d:%02d:%02d%s', $matches[1], $hour, $minute, $second, $offset );
		$instant    = DateTimeImmutable::createFromFormat( '!Y-m-d\TH:i:sP', $normalized );
		if ( false === $instant || self::has_date_errors() || $instant->format( 'Y-m-d\TH:i:sP' ) !== $normalized ) {
			return '';
		}

		$zone = new DateTimeZone( Cdd_Core_Event_Status::TIMEZONE );

		return $instant->setTimezone( $zone )->format( 'Y-m-d\TH:i:s' );
	}

	/**
	 * An ISO offset is Z or ±HH:MM with the hour in 00–23 and the
	 * minute in 00–59. PHP otherwise accepts +24:00 and shifts the day.
	 *
	 * @param string $offset Captured offset, including Z.
	 */
	private static function is_utc_offset( string $offset ): bool {
		if ( 'Z' === $offset ) {
			return true;
		}

		if ( 1 !== preg_match( '/^[+-](\d{2}):(\d{2})$/', $offset, $parts ) ) {
			return false;
		}

		return (int) $parts[1] <= 23 && (int) $parts[2] <= 59;
	}

	/**
	 * Whether the last DateTime parse warned or failed.
	 */
	private static function has_date_errors(): bool {
		$errors = DateTimeImmutable::getLastErrors();
		if ( ! is_array( $errors ) ) {
			return false;
		}

		return ( $errors['warning_count'] ?? 0 ) > 0 || ( $errors['error_count'] ?? 0 ) > 0;
	}
}
