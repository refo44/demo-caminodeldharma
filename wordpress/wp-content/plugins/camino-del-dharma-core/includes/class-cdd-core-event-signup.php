<?php
/**
 * Request-time signup visibility (issue #52).
 *
 * Pure domain code: no WordPress APIs. A current event shows its signup
 * URL only while the manual control is open and the optional close
 * instant, read as an America/Bogota wall clock, is still ahead. Closing
 * signup does not change vigente / finalizado / cancelado.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Decides whether a stored signup URL may be shown at one instant.
 */
final class Cdd_Core_Event_Signup {

	/**
	 * Whether signup is public at this instant.
	 *
	 * @param bool              $is_current         Event is current at request time.
	 * @param string            $signup_url         Stored signup URL.
	 * @param bool              $is_manually_closed Editor closed signup now.
	 * @param string            $closes_at          Optional close instant, or empty.
	 * @param DateTimeImmutable $now                Request-time instant (any timezone).
	 */
	public static function is_open( bool $is_current, string $signup_url, bool $is_manually_closed, string $closes_at, DateTimeImmutable $now ): bool {
		if ( ! $is_current || '' === $signup_url || $is_manually_closed ) {
			return false;
		}

		$close = self::close_instant( $closes_at );
		if ( null === $close ) {
			return true;
		}

		$request = $now->setTimezone( new DateTimeZone( Cdd_Core_Event_Status::TIMEZONE ) );

		return $request < $close;
	}

	/**
	 * Canonical close instant: a calendar day, or a Bogotá wall clock
	 * with seconds. Anything else is discarded so a bad value cannot
	 * hide signup on its own.
	 *
	 * @param mixed $value Raw meta value.
	 */
	public static function normalize( $value ): string {
		if ( ! is_string( $value ) ) {
			return '';
		}

		$value = trim( $value );
		if ( '' === $value ) {
			return '';
		}

		if ( self::is_calendar_date( $value ) ) {
			return $value;
		}

		if ( 1 !== preg_match( '/^(\d{4}-\d{2}-\d{2})[T ](\d{2}):(\d{2})(?::(\d{2}))?$/', $value, $matches ) ) {
			return '';
		}

		if ( ! self::is_calendar_date( $matches[1] ) ) {
			return '';
		}

		$hour   = (int) $matches[2];
		$minute = (int) $matches[3];
		$second = ( isset( $matches[4] ) && '' !== $matches[4] ) ? (int) $matches[4] : 0;
		if ( $hour > 23 || $minute > 59 || $second > 59 ) {
			return '';
		}

		return sprintf( '%sT%02d:%02d:%02d', $matches[1], $hour, $minute, $second );
	}

	/**
	 * The close instant in America/Bogota, or null when none is stored.
	 * A date without a time closes at 00:00 on that calendar day.
	 *
	 * @param string $closes_at Stored close instant.
	 */
	private static function close_instant( string $closes_at ): ?DateTimeImmutable {
		$normalized = self::normalize( $closes_at );
		if ( '' === $normalized ) {
			return null;
		}

		$zone = new DateTimeZone( Cdd_Core_Event_Status::TIMEZONE );
		if ( self::is_calendar_date( $normalized ) ) {
			$instant = DateTimeImmutable::createFromFormat( '!Y-m-d H:i:s', $normalized . ' 00:00:00', $zone );

			return false === $instant ? null : $instant;
		}

		$instant = DateTimeImmutable::createFromFormat( '!Y-m-d\TH:i:s', $normalized, $zone );

		return false === $instant ? null : $instant;
	}

	/**
	 * Whether a value is a real calendar date in Y-m-d form.
	 *
	 * @param string $value Candidate date.
	 */
	private static function is_calendar_date( string $value ): bool {
		$date = DateTimeImmutable::createFromFormat( '!Y-m-d', $value, new DateTimeZone( Cdd_Core_Event_Status::TIMEZONE ) );

		return false !== $date && $date->format( 'Y-m-d' ) === $value;
	}
}
