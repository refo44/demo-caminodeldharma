<?php
/**
 * Monthly calendar domain data (doc 03 §3 "Calendario del mes").
 *
 * Pure domain code: no WordPress APIs. The plugin selects the cells; the
 * theme paints them. Event days carry the event link and tooltip; Mondays
 * without another event that day are the weekly-meditation cell; the month
 * shown is that of the next upcoming marked date.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the month grid data for the events calendar block.
 */
final class Cdd_Core_Calendar_Data {

	const MONDAY = 1;

	/**
	 * The month (Y-m) the calendar should show: the month of the next
	 * upcoming marked date in America/Bogota, falling back to the present
	 * Bogotá month when nothing upcoming remains.
	 *
	 * @param array             $events Calendar event descriptors
	 *                                  (title, url, dates[]).
	 * @param DateTimeImmutable $now    Request-time instant (any timezone).
	 */
	public function choose_month( array $events, DateTimeImmutable $now ): string {
		$today    = Cdd_Core_Event_Status::today( $now );
		$upcoming = array();

		foreach ( $events as $event ) {
			foreach ( (array) ( $event['dates'] ?? array() ) as $date ) {
				if ( $date >= $today ) {
					$upcoming[] = $date;
				}
			}
		}

		$reference = empty( $upcoming ) ? $today : min( $upcoming );

		return substr( $reference, 0, 7 );
	}

	/**
	 * Builds the grid for one month.
	 *
	 * @param array  $events         Calendar event descriptors (title, url, dates[]).
	 * @param string $month          Month to build (Y-m).
	 * @param string $practice_url   Weekly meditation page URL.
	 * @param string $practice_label Weekly meditation tooltip label.
	 */
	public function build( string $month, array $events, string $practice_url, string $practice_label ): array {
		$first_day     = new DateTimeImmutable( $month . '-01' );
		$days_in_month = (int) $first_day->format( 't' );
		$first_weekday = (int) $first_day->format( 'N' );

		$days = array();
		for ( $day = 1; $day <= $days_in_month; $day++ ) {
			$days[ $day ] = array(
				'events'   => array(),
				'practice' => false,
			);
		}

		foreach ( $events as $event ) {
			foreach ( (array) ( $event['dates'] ?? array() ) as $date ) {
				if ( substr( $date, 0, 7 ) !== $month ) {
					continue;
				}
				$day = (int) substr( $date, 8, 2 );
				if ( isset( $days[ $day ] ) ) {
					$days[ $day ]['events'][] = array(
						'title' => (string) ( $event['title'] ?? '' ),
						'url'   => (string) ( $event['url'] ?? '' ),
					);
				}
			}
		}

		foreach ( $days as $day => $cell ) {
			$weekday = ( ( $first_weekday - 1 + ( $day - 1 ) ) % 7 ) + 1;
			if ( self::MONDAY === $weekday && empty( $cell['events'] ) ) {
				$days[ $day ]['practice'] = true;
			}
		}

		return array(
			'month'          => $month,
			'days_in_month'  => $days_in_month,
			'first_weekday'  => $first_weekday,
			'days'           => $days,
			'practice_url'   => $practice_url,
			'practice_label' => $practice_label,
		);
	}
}
