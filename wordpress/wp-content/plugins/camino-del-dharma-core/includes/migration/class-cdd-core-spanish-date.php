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
			$month = self::MONTHS[ mb_strtolower( $matches[2], 'UTF-8' ) ];
			$year  = $matches[3];

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
}
