<?php
/**
 * Deterministic parsing of the Spanish date strings used by the static
 * production HTML (ADR 0034 — extract, don't retype).
 *
 * Pure domain code: no WordPress APIs.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parses production Spanish dates into Y-m-d values.
 */
final class Cdd_Core_Spanish_Date {

	const MONTHS = array(
		'enero'      => '01',
		'febrero'    => '02',
		'marzo'      => '03',
		'abril'      => '04',
		'mayo'       => '05',
		'junio'      => '06',
		'julio'      => '07',
		'agosto'     => '08',
		'septiembre' => '09',
		'octubre'    => '10',
		'noviembre'  => '11',
		'diciembre'  => '12',
	);

	/**
	 * Parses a Fecha value into a start/end pair.
	 *
	 * Supports the production forms "Viernes 23 de enero de 2026" (single
	 * day) and "07, 08 y 09 de agosto de 2026" (day enumeration: first day
	 * starts, last day is the inclusive end). Month-only phrases carry no
	 * calendar day and yield null.
	 *
	 * @param string $text Fecha text.
	 */
	public static function parse_range( string $text ): ?array {
		$month_pattern = implode( '|', array_keys( self::MONTHS ) );

		if ( preg_match( '/(\d{1,2}(?:\s*,\s*\d{1,2})*(?:\s*y\s*\d{1,2})?)\s+de\s+(' . $month_pattern . ')\s+(?:de\s+)?(\d{4})/iu', $text, $matches ) ) {
			preg_match_all( '/\d{1,2}/', $matches[1], $days );
			$month = self::MONTHS[ self::lower( $matches[2] ) ] ?? null;
			if ( null === $month ) {
				return null;
			}
			$year = $matches[3];

			$first = str_pad( $days[0][0], 2, '0', STR_PAD_LEFT );
			$last  = str_pad( end( $days[0] ), 2, '0', STR_PAD_LEFT );

			return array(
				'start' => "{$year}-{$month}-{$first}",
				'end'   => $last === $first ? null : "{$year}-{$month}-{$last}",
			);
		}

		return null;
	}

	/**
	 * The first parseable date inside a longer sentence (the Círculos
	 * cronograma items), or null.
	 *
	 * @param string $text Sentence to scan.
	 */
	public static function first_date( string $text ): ?string {
		$range = self::parse_range( $text );

		return null !== $range ? $range['start'] : null;
	}

	/**
	 * A Y-m-d date as the Spanish long form the published pages print
	 * («31 de agosto de 2026»). The inverse of parse_range, and the reason
	 * it lives here: no WordPress locale is involved, so the string reads
	 * the same in every environment. Returns an empty string for anything
	 * that is not a calendar date.
	 *
	 * @param string $ymd Date in Y-m-d.
	 */
	public static function long_form( string $ymd ): string {
		$date = DateTimeImmutable::createFromFormat( '!Y-m-d', $ymd, new DateTimeZone( 'UTC' ) );
		if ( ! $date instanceof DateTimeImmutable || $date->format( 'Y-m-d' ) !== $ymd ) {
			return '';
		}

		$month = array_search( $date->format( 'm' ), self::MONTHS, true );

		return sprintf( '%d de %s de %d', (int) $date->format( 'j' ), $month, (int) $date->format( 'Y' ) );
	}

	/**
	 * Lowercases month names without requiring the mbstring extension.
	 *
	 * @param string $text Month token from a production Fecha value.
	 */
	private static function lower( string $text ): string {
		if ( function_exists( 'mb_strtolower' ) ) {
			return mb_strtolower( $text, 'UTF-8' );
		}

		return strtolower( $text );
	}
}
