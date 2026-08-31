<?php
/**
 * Request-time event status policy (OWN-013, ADR 0035).
 *
 * Pure domain code: no WordPress APIs. The end date rules, not a manual
 * switch — in America/Bogota, an event is completed when today is after
 * event_end (falling back to event_date). The final day remains current.
 * Cancelled is editorial and never overridden by dates.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves an event's public status from its dates at a given instant.
 */
final class Cdd_Core_Event_Status {

	const STATUS_CURRENT   = 'vigente';
	const STATUS_COMPLETED = 'finalizado';
	const STATUS_CANCELLED = 'cancelado';

	const TIMEZONE = 'America/Bogota';

	/**
	 * Resolves the status for one event.
	 *
	 * @param string|null       $start_date   Event start date (Y-m-d) or null.
	 * @param string|null       $end_date     Inclusive event end date (Y-m-d) or null.
	 * @param bool              $is_cancelled Editorial cancelled flag.
	 * @param DateTimeImmutable $now          Request-time instant (any timezone).
	 */
	public static function resolve( ?string $start_date, ?string $end_date, bool $is_cancelled, DateTimeImmutable $now ): string {
		if ( $is_cancelled ) {
			return self::STATUS_CANCELLED;
		}

		$effective_end = ( null !== $end_date && '' !== $end_date ) ? $end_date : $start_date;

		if ( null === $effective_end || '' === $effective_end ) {
			// Without dates the event cannot be proven past; it stays current.
			return self::STATUS_CURRENT;
		}

		return ( self::today( $now ) > $effective_end ) ? self::STATUS_COMPLETED : self::STATUS_CURRENT;
	}

	/**
	 * The calendar date (Y-m-d) of an instant in America/Bogota.
	 *
	 * @param DateTimeImmutable $now Request-time instant (any timezone).
	 */
	public static function today( DateTimeImmutable $now ): string {
		return $now->setTimezone( new DateTimeZone( self::TIMEZONE ) )->format( 'Y-m-d' );
	}
}
