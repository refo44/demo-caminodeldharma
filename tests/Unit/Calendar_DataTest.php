<?php
/**
 * Level 1: monthly calendar domain data (doc 03 §3 "Calendario del mes").
 *
 * The plugin selects the cells; the theme paints them. Event days carry the
 * event link and tooltip; Mondays without another event that day are the
 * weekly-meditation cell; the month shown is the next current event's.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: month grid selection for the events calendar block.
 */
final class Calendar_DataTest extends TestCase {

	const PRACTICE_URL   = '/practica/meditacion-semanal-en-linea';
	const PRACTICE_LABEL = 'Meditación semanal en línea';

	/**
	 * Protects the event-cell contract: each marked date of a current event
	 * becomes an event cell with the event's URL and tooltip title, and
	 * unmarked days stay plain.
	 */
	public function test_event_dates_become_event_cells_with_url_and_title() {
		$grid = $this->build_september_grid( array( $this->circulos( array( '2026-09-03', '2026-09-10' ) ) ) );

		$this->assertSame(
			array(
				array(
					'title' => 'Círculos de Presencia Consciente',
					'url'   => '/eventos/circulos-de-presencia-consciente',
				),
			),
			$grid['days'][3]['events']
		);
		$this->assertSame( array(), $grid['days'][4]['events'] );
		$this->assertNotEmpty( $grid['days'][10]['events'] );
	}

	/**
	 * Protects the weekly-meditation rule: every Monday without another
	 * event that day is a practice cell linking the weekly meditation page
	 * (September 2026 Mondays: 7, 14, 21, 28).
	 */
	public function test_mondays_without_events_are_practice_cells() {
		$grid = $this->build_september_grid( array( $this->circulos( array( '2026-09-03' ) ) ) );

		foreach ( array( 7, 14, 21, 28 ) as $monday ) {
			$this->assertTrue( $grid['days'][ $monday ]['practice'], "Monday {$monday} must be a practice cell." );
		}
		$this->assertFalse( $grid['days'][8]['practice'], 'A plain Tuesday is not a practice cell.' );
		$this->assertSame( self::PRACTICE_URL, $grid['practice_url'] );
		$this->assertSame( self::PRACTICE_LABEL, $grid['practice_label'] );
	}

	/**
	 * Protects the collision rule from doc 03: if a Monday already has an
	 * event, only the event is marked — never both.
	 */
	public function test_monday_with_an_event_marks_only_the_event() {
		$grid = $this->build_september_grid( array( $this->circulos( array( '2026-09-07' ) ) ) );

		$this->assertNotEmpty( $grid['days'][7]['events'] );
		$this->assertFalse( $grid['days'][7]['practice'] );
	}

	/**
	 * Protects the renderer inputs: day count and the ISO weekday of the
	 * first day, so the theme can place the leading offset without
	 * recomputing dates (2026-09-01 is a Tuesday).
	 */
	public function test_grid_exposes_month_shape() {
		$grid = $this->build_september_grid( array() );

		$this->assertSame( '2026-09', $grid['month'] );
		$this->assertSame( 30, $grid['days_in_month'] );
		$this->assertSame( 2, $grid['first_weekday'] );
		$this->assertCount( 30, $grid['days'] );
	}

	/**
	 * Protects the month-selection rule (memory + doc 03): the calendar
	 * shows the month of the next upcoming marked date, not necessarily the
	 * present month.
	 */
	public function test_month_shown_is_that_of_the_next_upcoming_event_date() {
		$august_now = new DateTimeImmutable( '2026-08-20T12:00:00', new DateTimeZone( 'America/Bogota' ) );

		$month = ( new Cdd_Core_Calendar_Data() )->choose_month(
			array( $this->circulos( array( '2026-09-03', '2026-09-10' ) ) ),
			$august_now
		);

		$this->assertSame( '2026-09', $month );
	}

	/**
	 * Protects the fallback: when no marked date is today or later, the
	 * calendar falls back to the present America/Bogota month.
	 */
	public function test_month_falls_back_to_the_present_bogota_month() {
		$october_now = new DateTimeImmutable( '2026-10-02T09:00:00', new DateTimeZone( 'America/Bogota' ) );

		$month = ( new Cdd_Core_Calendar_Data() )->choose_month(
			array( $this->circulos( array( '2026-09-03' ) ) ),
			$october_now
		);

		$this->assertSame( '2026-10', $month );
	}

	/**
	 * Protects the timezone edge: at 03:00 UTC it is still the previous
	 * day in Bogotá, so a marked date on that Bogotá day is still upcoming.
	 */
	public function test_month_selection_uses_the_bogota_date() {
		$utc_after_midnight = new DateTimeImmutable( '2026-09-04T03:00:00+00:00' );

		$month = ( new Cdd_Core_Calendar_Data() )->choose_month(
			array( $this->circulos( array( '2026-09-03' ) ) ),
			$utc_after_midnight
		);

		$this->assertSame( '2026-09', $month );
	}

	/**
	 * Builds the September 2026 grid under test.
	 *
	 * @param array $events Calendar event descriptors.
	 */
	private function build_september_grid( array $events ): array {
		return ( new Cdd_Core_Calendar_Data() )->build( '2026-09', $events, self::PRACTICE_URL, self::PRACTICE_LABEL );
	}

	/**
	 * The Círculos course as a calendar descriptor with explicit session
	 * dates (the static calendar marks sessions, not a contiguous range).
	 *
	 * @param array $dates Marked Y-m-d dates.
	 */
	private function circulos( array $dates ): array {
		return array(
			'title' => 'Círculos de Presencia Consciente',
			'url'   => '/eventos/circulos-de-presencia-consciente',
			'dates' => $dates,
		);
	}
}
