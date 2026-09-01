<?php
/**
 * Spanish display formatting shared by the theme views (WU-07).
 *
 * Pure presentation code: no WordPress APIs, deterministic output. The
 * rules are calibrated against the published production copy (OWN-007):
 * where the hand-written static strings follow a reproducible pattern,
 * that exact pattern is implemented; the deviations are documented in the
 * migration matrix.
 *
 * @package Camino_Del_Dharma
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formats dates, name lists and reading time in the site's voice.
 */
final class Camino_Del_Dharma_Format {

	const MONTHS = array(
		1  => 'enero',
		2  => 'febrero',
		3  => 'marzo',
		4  => 'abril',
		5  => 'mayo',
		6  => 'junio',
		7  => 'julio',
		8  => 'agosto',
		9  => 'septiembre',
		10 => 'octubre',
		11 => 'noviembre',
		12 => 'diciembre',
	);

	const WEEKDAYS = array(
		1 => 'lunes',
		2 => 'martes',
		3 => 'miércoles',
		4 => 'jueves',
		5 => 'viernes',
		6 => 'sábado',
		7 => 'domingo',
	);

	/**
	 * Longest range rendered as a day enumeration («07, 08 y 09 de …»).
	 */
	const ENUMERATION_MAX_DAYS = 4;

	/**
	 * The display date of an event, in the published voice:
	 * «Jueves 9 de julio de 2026», «07, 08 y 09 de agosto de 2026»,
	 * «1 – 15 de septiembre de 2026», «Septiembre – octubre 2026».
	 *
	 * @param string      $start Start date (Y-m-d).
	 * @param string|null $end   End date (Y-m-d) or null.
	 */
	public static function event_date_range( string $start, ?string $end ): string {
		$from = date_create_immutable( $start );
		if ( false === $from ) {
			return '';
		}

		$to = null !== $end && '' !== $end ? date_create_immutable( $end ) : false;
		if ( false === $to || null === $to || $to <= $from ) {
			$to = $from;
		}

		if ( $from->format( 'Y-m-d' ) === $to->format( 'Y-m-d' ) ) {
			return self::ucfirst_es( self::WEEKDAYS[ (int) $from->format( 'N' ) ] ) . ' ' .
				(int) $from->format( 'j' ) . ' de ' . self::MONTHS[ (int) $from->format( 'n' ) ] .
				' de ' . $from->format( 'Y' );
		}

		if ( $from->format( 'Y-m' ) === $to->format( 'Y-m' ) ) {
			$days = (int) $to->format( 'j' ) - (int) $from->format( 'j' ) + 1;
			$tail = ' de ' . self::MONTHS[ (int) $from->format( 'n' ) ] . ' de ' . $from->format( 'Y' );

			if ( $days <= self::ENUMERATION_MAX_DAYS ) {
				$list     = array();
				$last_day = (int) $to->format( 'j' );
				for ( $day = (int) $from->format( 'j' ); $day <= $last_day; $day++ ) {
					$list[] = sprintf( '%02d', $day );
				}

				return self::join_series( $list ) . $tail;
			}

			return (int) $from->format( 'j' ) . ' – ' . (int) $to->format( 'j' ) . $tail;
		}

		$from_month = self::ucfirst_es( self::MONTHS[ (int) $from->format( 'n' ) ] );
		$to_month   = self::MONTHS[ (int) $to->format( 'n' ) ];

		if ( $from->format( 'Y' ) === $to->format( 'Y' ) ) {
			return $from_month . ' – ' . $to_month . ' ' . $from->format( 'Y' );
		}

		return $from_month . ' ' . $from->format( 'Y' ) . ' – ' . $to_month . ' ' . $to->format( 'Y' );
	}

	/**
	 * The archive grouping year of an event.
	 *
	 * @param string $start Start date (Y-m-d).
	 */
	public static function event_year( string $start ): string {
		return substr( $start, 0, 4 );
	}

	/**
	 * The calendar heading for a month: «Septiembre 2026».
	 *
	 * @param string $month Month (Y-m).
	 */
	public static function month_title( string $month ): string {
		return self::ucfirst_es( self::MONTHS[ (int) substr( $month, 5, 2 ) ] ) . ' ' . substr( $month, 0, 4 );
	}

	/**
	 * Joins names in the documented voice: «A», «A y B», «A, B y C».
	 *
	 * @param array $names Plain names in order.
	 */
	public static function name_list( array $names ): string {
		return self::join_series( array_values( array_map( 'strval', $names ) ) );
	}

	/**
	 * Reading minutes: round(words / 200), minimum one. Calibrated on the
	 * published «Tiempo de lectura: 5 minutos» of the Círculos post.
	 *
	 * @param string $content Post content (HTML allowed).
	 */
	public static function reading_minutes( string $content ): int {
		$text  = trim( preg_replace( '/\s+/u', ' ', strip_tags( $content ) ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- pure presentation code without WordPress loaded (unit-tested).
		$words = '' === $text ? 0 : count( explode( ' ', $text ) );

		return max( 1, (int) round( $words / 200 ) );
	}

	/**
	 * Series join without an Oxford comma: «A, B y C».
	 *
	 * @param array $items String items.
	 */
	private static function join_series( array $items ): string {
		$count = count( $items );
		if ( 0 === $count ) {
			return '';
		}
		if ( 1 === $count ) {
			return $items[0];
		}

		$last = array_pop( $items );

		return implode( ', ', $items ) . ' y ' . $last;
	}

	/**
	 * Multibyte-safe first-letter capitalization for Spanish words.
	 *
	 * @param string $word Lowercase word.
	 */
	private static function ucfirst_es( string $word ): string {
		return mb_strtoupper( mb_substr( $word, 0, 1 ) ) . mb_substr( $word, 1 );
	}
}
