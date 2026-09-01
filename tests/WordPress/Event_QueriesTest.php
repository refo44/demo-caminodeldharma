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
	 * The Círculos cronograma as published (payload calendar_dates):
	 * ten irregularly spaced sessions between September and October.
	 */
	const CIRCULOS_SESSIONS = array(
		'2026-09-03',
		'2026-09-10',
		'2026-09-15',
		'2026-09-17',
		'2026-09-22',
		'2026-09-24',
		'2026-09-29',
		'2026-10-01',
		'2026-10-17',
		'2026-10-24',
	);

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
	 * Protects the archive block of memory (doc 03 §3, WU-07): completed
	 * and cancelled events come back newest start first, and no current
	 * event leaks into the archive.
	 */
	public function test_past_events_query_returns_completed_events_newest_first() {
		$now = $this->bogota( '2026-09-10 09:00:00' );

		$older_id     = $this->create_event( 'seis-encuentro', '2025-08-16', '2025-08-18' );
		$newer_id     = $this->create_event( 'encuentro-2026', '2026-08-07', '2026-08-09' );
		$cancelled_id = $this->create_event( 'cancelado', '2026-09-20', null, array( 'event_status' => 'cancelado' ) );
		$this->create_event( 'vigente', '2026-09-15', null );

		$past_ids = wp_list_pluck( cdd_core_past_events( $now ), 'ID' );

		$this->assertSame( array( $cancelled_id, $newer_id, $older_id ), $past_ids );
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
	 * Protects the WU-08A invariant: the «Añadir al calendario» dialog and
	 * the .ics a visitor downloads describe the same event. Both read one
	 * calendar payload, so title, dates, description and location can
	 * never drift apart, and the compact dates are the exclusive-end form
	 * Google and Outlook expect.
	 */
	public function test_calendar_payload_feeds_both_the_dialog_and_the_ics() {
		$now   = $this->bogota( '2026-09-01 09:00:00' );
		$event = $this->create_event(
			'circulos',
			'2026-09-03',
			'2026-10-24',
			array( 'event_place' => 'Bogotá y Cali' )
		);
		wp_update_post(
			array(
				'ID'           => $event,
				'post_title'   => 'Círculos de Presencia Consciente',
				'post_excerpt' => 'Sesión virtual de bienvenida.',
			)
		);

		$payload = cdd_core_event_calendar_payload( get_post( $event ) );

		$this->assertSame( 'Círculos de Presencia Consciente', $payload['title'] );
		$this->assertSame( '20260903', $payload['start'] );
		$this->assertSame( '20261025', $payload['end'], 'Exclusive end, as the published dialog encodes it.' );
		$this->assertSame( 'Sesión virtual de bienvenida.', $payload['description'] );
		$this->assertSame( 'Bogotá y Cali', $payload['location'] );
		$this->assertStringEndsWith( '/eventos/ical/circulos.ics', $payload['ics_url'] );
		$this->assertSame( 'circulos.ics', $payload['ics_filename'] );
		$this->assertSame( get_permalink( $event ), $payload['url'] );

		$ics = cdd_core_event_ics_response( 'circulos', $now )['body'];

		$this->assertStringContainsString( 'SUMMARY:' . $payload['title'], $ics );
		$this->assertStringContainsString( 'DTSTART;VALUE=DATE:' . $payload['start'], $ics );
		$this->assertStringContainsString( 'DTEND;VALUE=DATE:' . $payload['end'], $ics );
		$this->assertStringContainsString( 'LOCATION:' . $payload['location'], $ics );
	}

	/**
	 * Protects the single-day case: without an end date the exclusive end
	 * is the day after the start, so a one-day event does not collapse to
	 * a zero-length calendar entry.
	 */
	public function test_calendar_payload_closes_a_single_day_event_on_the_next_day() {
		$event = $this->create_event( 'un-dia', '2026-09-03', null );

		$payload = cdd_core_event_calendar_payload( get_post( $event ) );

		$this->assertSame( '20260903', $payload['start'] );
		$this->assertSame( '20260904', $payload['end'] );
	}

	/**
	 * BUG-001: the .ics of a course exports every published session, not
	 * one entry spanning the whole course. The Círculos cronograma has
	 * ten sessions; the file a visitor downloads carries ten VEVENTs,
	 * including the ones already held — the file is the schedule.
	 */
	public function test_the_ics_of_a_course_exports_every_session() {
		$now   = $this->bogota( '2026-09-20 09:00:00' );
		$event = $this->create_event(
			'circulos',
			'2026-09-03',
			'2026-10-24',
			array(
				'event_place'          => 'Bogotá y Cali',
				'event_calendar_dates' => self::CIRCULOS_SESSIONS,
			)
		);

		$payload = cdd_core_event_calendar_payload( get_post( $event ), $now );

		$this->assertCount( 10, $payload['occurrences'] );
		$this->assertSame( 10, $payload['session_count'] );
		$this->assertSame( '2026-09-03', $payload['occurrences'][0]['start_date'] );
		$this->assertSame( '20260904', $payload['occurrences'][0]['end'], 'Each session is one all-day entry.' );
		$this->assertSame( '2026-09-03', $payload['start_date'], 'The course span is untouched.' );
		$this->assertSame( '2026-10-24', $payload['end_date'] );

		$ics = cdd_core_event_ics_response( 'circulos', $now )['body'];

		$this->assertSame( 10, substr_count( $ics, 'BEGIN:VEVENT' ) );
		foreach ( self::CIRCULOS_SESSIONS as $session ) {
			$this->assertStringContainsString(
				'DTSTART;VALUE=DATE:' . str_replace( '-', '', $session ) . "\r\n",
				$ics,
				'Session ' . $session . ' is exported.'
			);
		}

		preg_match_all( "/UID:(.*?)\r\n/", $ics, $matches );
		$this->assertCount( 10, array_unique( $matches[1] ), 'One UID per session, all distinct.' );
	}

	/**
	 * BUG-001: the dialog and the file may not describe different dates.
	 * Google and Outlook deep links carry a single entry, so they name
	 * the next session — a date the downloaded file actually contains —
	 * instead of a 52-day range that appears in no VEVENT.
	 */
	public function test_the_dialog_deep_link_names_a_session_the_file_contains() {
		$now   = $this->bogota( '2026-09-20 09:00:00' );
		$event = $this->create_event(
			'circulos',
			'2026-09-03',
			'2026-10-24',
			array( 'event_calendar_dates' => self::CIRCULOS_SESSIONS )
		);

		$payload = cdd_core_event_calendar_payload( get_post( $event ), $now );

		$this->assertSame( '20260922', $payload['start'], 'The next session, not the course range.' );
		$this->assertSame( '20260923', $payload['end'] );
		$this->assertSame( '2026-09-22', $payload['next']['start_date'] );

		$ics = cdd_core_event_ics_response( 'circulos', $now )['body'];

		$this->assertStringContainsString( 'DTSTART;VALUE=DATE:' . $payload['start'] . "\r\n", $ics );
		$this->assertStringContainsString( 'DTEND;VALUE=DATE:' . $payload['end'] . "\r\n", $ics );
	}

	/**
	 * BUG-001: before the course starts the deep link names the first
	 * session, so a visitor who adds it lands on the welcome date.
	 */
	public function test_the_deep_link_names_the_first_session_before_the_course_starts() {
		$event = $this->create_event(
			'circulos',
			'2026-09-03',
			'2026-10-24',
			array( 'event_calendar_dates' => self::CIRCULOS_SESSIONS )
		);

		$payload = cdd_core_event_calendar_payload( get_post( $event ), $this->bogota( '2026-09-01 09:00:00' ) );

		$this->assertSame( '20260903', $payload['start'] );
		$this->assertSame( '20260904', $payload['end'] );
	}

	/**
	 * BUG-001 does not weaken OWN-012: a completed course still returns
	 * 410 with an empty body, session list or not.
	 */
	public function test_a_completed_course_still_returns_410_with_no_sessions_exported() {
		$this->create_event(
			'circulos',
			'2026-09-03',
			'2026-10-24',
			array( 'event_calendar_dates' => self::CIRCULOS_SESSIONS )
		);

		$response = cdd_core_event_ics_response( 'circulos', $this->bogota( '2026-10-26 09:00:00' ) );

		$this->assertSame( 410, $response['status'] );
		$this->assertSame( '', $response['body'] );
		$this->assertSame( 'noindex, nofollow', $response['headers']['X-Robots-Tag'] );
	}

	/**
	 * BUG-001 leaves single-date events alone: without a session list the
	 * payload and the file keep the event_date..event_end range of WU-08A.
	 */
	public function test_an_event_without_sessions_keeps_the_range_export() {
		$now   = $this->bogota( '2026-08-01 09:00:00' );
		$event = $this->create_event( 'encuentro', '2026-08-07', '2026-08-09' );

		$payload = cdd_core_event_calendar_payload( get_post( $event ), $now );

		$this->assertSame( array(), $payload['occurrences'] );
		$this->assertSame( 0, $payload['session_count'] );
		$this->assertSame( '20260807', $payload['start'] );
		$this->assertSame( '20260810', $payload['end'] );

		$ics = cdd_core_event_ics_response( 'encuentro', $now )['body'];

		$this->assertSame( 1, substr_count( $ics, 'BEGIN:VEVENT' ) );
		$this->assertStringContainsString( 'UID:encuentro@' . wp_parse_url( home_url(), PHP_URL_HOST ) . "\r\n", $ics );
		$this->assertStringContainsString( "DTSTART;VALUE=DATE:20260807\r\n", $ics );
		$this->assertStringContainsString( "DTEND;VALUE=DATE:20260810\r\n", $ics );
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
