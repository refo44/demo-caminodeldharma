<?php
/**
 * Level 1: presentation formatting helpers of the theme (WU-07).
 *
 * Written RED-first. Every expectation below is calibrated against the
 * published production copy (OWN-007): the date strings are the exact
 * `Fecha` values of eventos/index.html where a deterministic rule can
 * reproduce them.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

$camino_del_dharma_format = dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/camino-del-dharma/inc/class-camino-del-dharma-format.php';
if ( file_exists( $camino_del_dharma_format ) ) {
	require_once $camino_del_dharma_format;
}

/**
 * Behavior cluster: Spanish display formatting the views share.
 */
final class Theme_FormatTest extends TestCase {

	/**
	 * Production: «Jueves 9 de julio de 2026» (Meditación Barranquilla).
	 * One-day events show the capitalized weekday and no zero padding.
	 */
	public function test_single_day_shows_weekday_and_long_date() {
		$this->assertSame( 'Jueves 9 de julio de 2026', Camino_Del_Dharma_Format::event_date_range( '2026-07-09', null ) );
		$this->assertSame( 'Domingo 28 de junio de 2026', Camino_Del_Dharma_Format::event_date_range( '2026-06-28', '2026-06-28' ) );
		$this->assertSame( 'Sábado 23 de mayo de 2026', Camino_Del_Dharma_Format::event_date_range( '2026-05-23', null ) );
	}

	/**
	 * Production: «07, 08 y 09 de agosto de 2026» and «16, 17 y 18 de
	 * agosto de 2025». Short same-month runs enumerate zero-padded days.
	 */
	public function test_short_same_month_range_enumerates_days() {
		$this->assertSame( '07, 08 y 09 de agosto de 2026', Camino_Del_Dharma_Format::event_date_range( '2026-08-07', '2026-08-09' ) );
		$this->assertSame( '16, 17 y 18 de agosto de 2025', Camino_Del_Dharma_Format::event_date_range( '2025-08-16', '2025-08-18' ) );
	}

	/**
	 * Long same-month ranges collapse to a dash instead of a listing.
	 */
	public function test_long_same_month_range_uses_a_dash() {
		$this->assertSame( '1 – 15 de septiembre de 2026', Camino_Del_Dharma_Format::event_date_range( '2026-09-01', '2026-09-15' ) );
	}

	/**
	 * Production: «Septiembre – octubre 2026» (Círculos). Cross-month
	 * ranges show only the months, first one capitalized.
	 */
	public function test_cross_month_range_shows_months_only() {
		$this->assertSame( 'Septiembre – octubre 2026', Camino_Del_Dharma_Format::event_date_range( '2026-09-03', '2026-10-24' ) );
	}

	/**
	 * Cross-year ranges keep both years so the copy cannot mislead.
	 */
	public function test_cross_year_range_shows_both_years() {
		$this->assertSame( 'Diciembre 2026 – enero 2027', Camino_Del_Dharma_Format::event_date_range( '2026-12-28', '2027-01-05' ) );
	}

	/**
	 * The archive year grouping heading uses the start year.
	 */
	public function test_event_year_comes_from_the_start_date() {
		$this->assertSame( '2025', Camino_Del_Dharma_Format::event_year( '2025-08-16' ) );
	}

	/**
	 * Production byline voice: «Por A y B» (master prompt §9.5); three or
	 * more names use the serial listing without an Oxford comma.
	 */
	public function test_name_list_joins_in_the_documented_voice() {
		$this->assertSame( 'Zheng Gong', Camino_Del_Dharma_Format::name_list( array( 'Zheng Gong' ) ) );
		$this->assertSame( 'A y B', Camino_Del_Dharma_Format::name_list( array( 'A', 'B' ) ) );
		$this->assertSame( 'A, B y C', Camino_Del_Dharma_Format::name_list( array( 'A', 'B', 'C' ) ) );
		$this->assertSame( '', Camino_Del_Dharma_Format::name_list( array() ) );
	}

	/**
	 * Calibrated on the published «Tiempo de lectura: 5 minutos» of the
	 * Círculos post (1043 words): round(words / 200), minimum one minute.
	 * The Sangha post shows 8 on production; no deterministic rule
	 * reproduces both, so the rounded value is the documented substitution.
	 */
	public function test_reading_minutes_round_at_two_hundred_words() {
		$this->assertSame( 5, Camino_Del_Dharma_Format::reading_minutes( str_repeat( 'palabra ', 1043 ) ) );
		$this->assertSame( 6, Camino_Del_Dharma_Format::reading_minutes( str_repeat( 'palabra ', 1239 ) ) );
		$this->assertSame( 1, Camino_Del_Dharma_Format::reading_minutes( 'corto' ) );
		$this->assertSame( 1, Camino_Del_Dharma_Format::reading_minutes( '<p>etiquetas <strong>fuera</strong></p>' ) );
	}

	/**
	 * The calendar heading month: «Septiembre 2026» (production grid title).
	 */
	public function test_month_title_capitalizes_the_spanish_month() {
		$this->assertSame( 'Septiembre 2026', Camino_Del_Dharma_Format::month_title( '2026-09' ) );
		$this->assertSame( 'Enero 2027', Camino_Del_Dharma_Format::month_title( '2027-01' ) );
	}
}
