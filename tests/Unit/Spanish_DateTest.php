<?php
/**
 * Level 1: deterministic parsing of the Spanish date strings the static
 * production HTML uses (ADR 0034 — extract, don't retype).
 *
 * Every asserted string exists verbatim in static/eventos/.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: Spanish production dates to Y-m-d.
 */
final class Spanish_DateTest extends TestCase {

	/**
	 * Protects single-day parsing with a leading weekday, as the event
	 * cards write it.
	 */
	public function test_single_day_with_weekday_parses_to_start_only() {
		$this->assertSame(
			array(
				'start' => '2026-01-23',
				'end'   => null,
			),
			Cdd_Core_Spanish_Date::parse_range( 'Viernes 23 de enero de 2026' )
		);
		$this->assertSame(
			array(
				'start' => '2026-02-15',
				'end'   => null,
			),
			Cdd_Core_Spanish_Date::parse_range( 'Domingo 15 de febrero de 2026' )
		);
	}

	/**
	 * Protects day-enumeration ranges ("07, 08 y 09 de agosto de 2026"):
	 * the first day is the start and the last day the inclusive end.
	 */
	public function test_day_enumeration_parses_to_a_range() {
		$this->assertSame(
			array(
				'start' => '2026-08-07',
				'end'   => '2026-08-09',
			),
			Cdd_Core_Spanish_Date::parse_range( '07, 08 y 09 de agosto de 2026' )
		);
		$this->assertSame(
			array(
				'start' => '2025-08-16',
				'end'   => '2025-08-18',
			),
			Cdd_Core_Spanish_Date::parse_range( '16, 17 y 18 de agosto de 2025' )
		);
	}

	/**
	 * Protects honest failure: a month-only phrase carries no calendar day
	 * and must not invent one.
	 */
	public function test_month_only_phrase_is_not_parseable() {
		$this->assertNull( Cdd_Core_Spanish_Date::parse_range( 'Septiembre – octubre 2026' ) );
	}

	/**
	 * Protects extraction of the first date inside a longer sentence, as
	 * the Círculos cronograma list items are written.
	 */
	public function test_first_date_is_found_inside_a_sentence() {
		$this->assertSame(
			'2026-09-03',
			Cdd_Core_Spanish_Date::first_date( 'Sesión virtual de bienvenida: jueves 3 de septiembre de 2026.' )
		);
		$this->assertSame(
			'2026-10-17',
			Cdd_Core_Spanish_Date::first_date( 'Encuentro presencial en Bogotá: sábado 17 de octubre de 2026, de 8:00 a. m. a 12:00 m.' )
		);
		$this->assertNull( Cdd_Core_Spanish_Date::first_date( 'Sin fecha alguna aquí.' ) );
	}
}
