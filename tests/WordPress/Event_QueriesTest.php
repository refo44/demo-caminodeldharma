<?php
/**
 * Level 2: request-time event visibility and calendar/ICS data over real
 * posts and meta (OWN-012 / OWN-013, doc 03 §3).
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * Behavior cluster: event queries resolved at request time in America/Bogota.
 */
final class Event_QueriesTest extends WP_UnitTestCase {

	/**
	 * Protects OWN-013 wired to real meta: the stored dates decide the
	 * status at request time; the stored event_status only wins when it
	 * says cancelled.
	 */
	public function test_event_status_is_resolved_from_meta_at_request_time() {
		$now = $this->bogota( '2026-09-10 09:00:00' );

		$current   = $this->create_event( 'vigente-evento', '2026-09-15', null );
		$completed = $this->create_event( 'pasado-evento', '2026-09-01', '2026-09-05' );
		$cancelled = $this->create_event( 'cancelado-evento', '2026-09-20', null, array( 'event_status' => 'cancelado' ) );

		$this->assertSame( 'vigente', cdd_core_event_status( $current, $now ) );
		$this->assertSame( 'finalizado', cdd_core_event_status( $completed, $now ) );
		$this->assertSame( 'cancelado', cdd_core_event_status( $cancelled, $now ) );
	}

	/**
	 * Protects the archive split: current events and completed events are
	 * separated by the request-time rule, never by the stored flag alone
	 * (a stale 'vigente' on a past event must not leak into current).
	 */
	public function test_current_events_query_excludes_past_and_cancelled_events() {
		$now = $this->bogota( '2026-09-10 09:00:00' );

		$current_id = $this->create_event( 'proximo', '2026-09-15', null );
		$this->create_event( 'pasado-con-flag-viejo', '2026-09-01', '2026-09-05', array( 'event_status' => 'vigente' ) );
		$this->create_event( 'cancelado', '2026-09-20', null, array( 'event_status' => 'cancelado' ) );

		$current_ids = wp_list_pluck( cdd_core_current_events( $now ), 'ID' );

		$this->assertSame( array( $current_id ), $current_ids );
	}

	/**
	 * Protects the home rule: the featured current event wins; when the
	 * only featured event is already completed, its mark is ignored and no
	 * fallback promotes it back.
	 */
	public function test_home_event_selection_ignores_completed_featured_events() {
		$now = $this->bogota( '2026-09-10 09:00:00' );

		$this->create_event( 'destacado-pasado', '2026-09-01', '2026-09-05', array( 'event_featured' => '1' ) );
		$soonest_id = $this->create_event( 'proximo', '2026-09-15', null );
		$this->create_event( 'lejano', '2026-10-15', null );

		$selected = cdd_core_featured_home_event( $now );

		$this->assertSame( $soonest_id, $selected->ID );
	}

	/**
	 * Protects the empty state: with no current event the home module gets
	 * nothing to render.
	 */
	public function test_home_event_selection_is_null_without_current_events() {
		$now = $this->bogota( '2026-09-10 09:00:00' );

		$this->create_event( 'pasado', '2026-09-01', '2026-09-05' );

		$this->assertNull( cdd_core_featured_home_event( $now ) );
	}

	/**
	 * Protects the calendar provider: current events project their marked
	 * dates (session meta when present, else the date range) into the
	 * month grid with their permalink as cell URL.
	 */
	public function test_calendar_month_data_marks_event_days_with_permalinks() {
		$now = $this->bogota( '2026-08-20 09:00:00' );

		$event_id = $this->create_event(
			'circulos-de-presencia-consciente',
			'2026-09-03',
			'2026-09-29',
			array( 'event_calendar_dates' => array( '2026-09-03', '2026-09-10' ) )
		);

		$calendar = cdd_core_calendar_month_data( $now );

		$this->assertSame( '2026-09', $calendar['month'] );
		$this->assertSame( get_permalink( $event_id ), $calendar['days'][3]['events'][0]['url'] );
		$this->assertSame( array(), $calendar['days'][4]['events'], 'Unmarked days between sessions stay plain.' );
		$this->assertTrue( $calendar['days'][7]['practice'], 'Monday Sep 7 without event is the weekly practice cell.' );
	}

	/**
	 * Protects the range fallback of the provider: without session meta an
	 * event marks every day from event_date through event_end.
	 */
	public function test_calendar_month_data_falls_back_to_the_date_range() {
		$now = $this->bogota( '2026-08-01 09:00:00' );

		$this->create_event( 'encuentro-nacional-2026', '2026-08-07', '2026-08-09' );

		$calendar = cdd_core_calendar_month_data( $now );

		$this->assertSame( '2026-08', $calendar['month'] );
		$this->assertNotEmpty( $calendar['days'][7]['events'] );
		$this->assertNotEmpty( $calendar['days'][8]['events'] );
		$this->assertNotEmpty( $calendar['days'][9]['events'] );
		$this->assertSame( array(), $calendar['days'][10]['events'] );
	}

	/**
	 * Protects OWN-009/OWN-012 response data: a current event's ICS route
	 * yields generated calendar data with the noindex header; a completed
	 * event yields 410 gone; an unknown slug yields 404.
	 */
	public function test_ics_response_serves_current_events_and_410s_completed_ones() {
		$now = $this->bogota( '2026-09-01 09:00:00' );

		$this->create_event( 'circulos-de-presencia-consciente', '2026-09-03', '2026-09-29' );
		$this->create_event( 'encuentro-nacional-2026', '2026-08-07', '2026-08-09' );

		$current_response   = cdd_core_event_ics_response( 'circulos-de-presencia-consciente', $now );
		$completed_response = cdd_core_event_ics_response( 'encuentro-nacional-2026', $now );
		$unknown_response   = cdd_core_event_ics_response( 'no-existe', $now );

		$this->assertSame( 200, $current_response['status'] );
		$this->assertStringContainsString( 'text/calendar', $current_response['headers']['Content-Type'] );
		$this->assertSame( 'noindex, nofollow', $current_response['headers']['X-Robots-Tag'] );
		$this->assertStringContainsString( 'UID:circulos-de-presencia-consciente@' . wp_parse_url( home_url(), PHP_URL_HOST ), $current_response['body'] );
		$this->assertStringContainsString( 'BEGIN:VEVENT', $current_response['body'] );

		$this->assertSame( 410, $completed_response['status'] );
		$this->assertSame( '', $completed_response['body'] );

		$this->assertSame( 404, $unknown_response['status'] );
	}

	/**
	 * Creates a published event with dates and extra meta.
	 *
	 * @param string      $slug  Post slug.
	 * @param string|null $start event_date meta.
	 * @param string|null $end   event_end meta.
	 * @param array       $meta  Extra meta values.
	 */
	private function create_event( string $slug, ?string $start, ?string $end, array $meta = array() ): int {
		$meta_input = $meta;
		if ( null !== $start ) {
			$meta_input['event_date'] = $start;
		}
		if ( null !== $end ) {
			$meta_input['event_end'] = $end;
		}

		return self::factory()->post->create(
			array(
				'post_type'  => 'event',
				'post_name'  => $slug,
				'post_title' => $slug,
				'meta_input' => $meta_input,
			)
		);
	}

	/**
	 * A request-time instant expressed in America/Bogota.
	 *
	 * @param string $local_datetime Local Bogotá date-time (Y-m-d H:i:s).
	 */
	private function bogota( string $local_datetime ): DateTimeImmutable {
		return new DateTimeImmutable( $local_datetime, new DateTimeZone( 'America/Bogota' ) );
	}
}
