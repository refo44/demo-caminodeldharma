<?php
/**
 * Level 1: generated .ics calendar data (OWN-009 / OWN-012).
 *
 * The contract is the production .ics pair under static/eventos/ical/:
 * VCALENDAR 2.0, the community PRODID, all-day DTSTART/DTEND (exclusive
 * end), escaped text values and CRLF line endings.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: iCalendar payload generation from event data.
 */
final class Ics_GeneratorTest extends TestCase {

	/**
	 * The Círculos cronograma as published (docs/inventario §Eventos):
	 * ten sessions, irregularly spaced, not a contiguous range.
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
	 * Protects the calendar envelope observed in production: version,
	 * community PRODID, Gregorian scale and PUBLISH method.
	 */
	public function test_calendar_envelope_matches_the_production_contract() {
		$ics = ( new Cdd_Core_Ics_Generator() )->generate( $this->encuentro_event() );

		$this->assertStringContainsString( "BEGIN:VCALENDAR\r\n", $ics );
		$this->assertStringContainsString( "VERSION:2.0\r\n", $ics );
		$this->assertStringContainsString( "PRODID:-//Comunidad Buddhista Camino del Dharma//Eventos//ES\r\n", $ics );
		$this->assertStringContainsString( "CALSCALE:GREGORIAN\r\n", $ics );
		$this->assertStringContainsString( "METHOD:PUBLISH\r\n", $ics );
		$this->assertStringContainsString( "END:VCALENDAR\r\n", $ics );
	}

	/**
	 * Protects the event identity and all-day date encoding: UID from the
	 * slug at the site domain, DTSTART on the first day and an exclusive
	 * DTEND one day after the inclusive end (Aug 7–9 ends 20260810).
	 */
	public function test_event_block_uses_all_day_dates_with_exclusive_end() {
		$ics = ( new Cdd_Core_Ics_Generator() )->generate( $this->encuentro_event() );

		$this->assertStringContainsString( "UID:encuentro-nacional-2026@caminodeldharma.org\r\n", $ics );
		$this->assertStringContainsString( "DTSTART;VALUE=DATE:20260807\r\n", $ics );
		$this->assertStringContainsString( "DTEND;VALUE=DATE:20260810\r\n", $ics );
		$this->assertStringContainsString( "DTSTAMP:20260716T230000Z\r\n", $ics );
	}

	/**
	 * Protects the single-day case: without an inclusive end date the event
	 * lasts one day, so DTEND is the day after DTSTART (Círculos contract).
	 */
	public function test_single_day_event_ends_the_next_day() {
		$event          = $this->encuentro_event();
		$event['start'] = '2026-09-03';
		$event['end']   = null;

		$ics = ( new Cdd_Core_Ics_Generator() )->generate( $event );

		$this->assertStringContainsString( "DTSTART;VALUE=DATE:20260903\r\n", $ics );
		$this->assertStringContainsString( "DTEND;VALUE=DATE:20260904\r\n", $ics );
	}

	/**
	 * Protects RFC 5545 text escaping as production already applies it:
	 * commas and semicolons are backslash-escaped and newlines become \n.
	 */
	public function test_text_values_are_escaped() {
		$event                = $this->encuentro_event();
		$event['description'] = "Tres días para detenernos, respirar; volver.\nInformación: https://caminodeldharma.org";

		$ics = ( new Cdd_Core_Ics_Generator() )->generate( $event );

		$this->assertStringContainsString( 'LOCATION:Casa Retiro San Pablo\\, Puerto Colombia', $ics );
		$this->assertStringContainsString( 'DESCRIPTION:Tres días para detenernos\\, respirar\\; volver.\\nInformación: https://caminodeldharma.org', $ics );
	}

	/**
	 * Protects the organizer and attachment lines that production emits for
	 * every event: community CN with MAILTO and the poster as URI ATTACH.
	 */
	public function test_organizer_and_poster_lines_match_production() {
		$ics = ( new Cdd_Core_Ics_Generator() )->generate( $this->encuentro_event() );

		$this->assertStringContainsString( "ORGANIZER;CN=Comunidad Buddhista Camino del Dharma:MAILTO:caminodeldharma1@gmail.com\r\n", $ics );
		$this->assertStringContainsString( "ATTACH;FMTTYPE=image/jpeg;VALUE=URI:https://caminodeldharma.org/assets/images/eventos/evento-7-encuentro-nacional-2026.jpg\r\n", $ics );
		$this->assertStringContainsString( "URL:https://caminodeldharma.org/eventos/encuentro-nacional-2026\r\n", $ics );
	}

	/**
	 * Protects honest omission: optional lines are absent when there is no
	 * data — no empty LOCATION:, ATTACH; or DESCRIPTION: properties.
	 */
	public function test_optional_lines_are_omitted_when_empty() {
		$event                = $this->encuentro_event();
		$event['location']    = '';
		$event['attach']      = '';
		$event['description'] = '';

		$ics = ( new Cdd_Core_Ics_Generator() )->generate( $event );

		$this->assertStringNotContainsString( 'LOCATION', $ics );
		$this->assertStringNotContainsString( 'ATTACH', $ics );
		$this->assertStringNotContainsString( 'DESCRIPTION', $ics );
	}

	/**
	 * Protects the wire format: every line break is CRLF — no bare LF
	 * survives (calendar clients reject mixed endings).
	 */
	public function test_all_line_endings_are_crlf() {
		$ics = ( new Cdd_Core_Ics_Generator() )->generate( $this->encuentro_event() );

		$this->assertStringNotContainsString( "\n", str_replace( "\r\n", '', $ics ) );
	}

	/**
	 * Event data mirroring the production encuentro-nacional-2026.ics.
	 */
	private function encuentro_event(): array {
		return array(
			'uid'             => 'encuentro-nacional-2026@caminodeldharma.org',
			'summary'         => '7.º Encuentro Nacional Buddhista – 2026',
			'description'     => 'Tres días para detenernos.',
			'location'        => 'Casa Retiro San Pablo, Puerto Colombia',
			'url'             => 'https://caminodeldharma.org/eventos/encuentro-nacional-2026',
			'attach'          => 'https://caminodeldharma.org/assets/images/eventos/evento-7-encuentro-nacional-2026.jpg',
			'organizer_name'  => 'Comunidad Buddhista Camino del Dharma',
			'organizer_email' => 'caminodeldharma1@gmail.com',
			'start'           => '2026-08-07',
			'end'             => '2026-08-09',
			'dtstamp'         => new DateTimeImmutable( '2026-07-16T23:00:00+00:00' ),
		);
	}

	/**
	 * BUG-001: a course that publishes a session schedule exports one
	 * VEVENT per session, inside a single VCALENDAR envelope. The
	 * Círculos cronograma has ten sessions between September 3 and
	 * October 24; a single VEVENT spanning the whole course would tell a
	 * calendar client the course runs for 52 straight days.
	 */
	public function test_a_session_list_emits_one_vevent_per_session() {
		$ics = ( new Cdd_Core_Ics_Generator() )->generate( $this->circulos_event() );

		$this->assertSame( 1, substr_count( $ics, "BEGIN:VCALENDAR\r\n" ) );
		$this->assertCount( 10, $this->vevents( $ics ) );

		foreach ( self::CIRCULOS_SESSIONS as $session ) {
			$this->assertStringContainsString(
				'DTSTART;VALUE=DATE:' . str_replace( '-', '', $session ) . "\r\n",
				$ics
			);
		}

		$this->assertStringNotContainsString(
			"DTSTART;VALUE=DATE:20260904\r\n",
			$ics,
			'September 4 is not a session: the file is a schedule, not a range.'
		);
	}

	/**
	 * BUG-001: each session closes the day after itself, so no session
	 * swallows the days until the next one.
	 */
	public function test_each_session_is_a_single_all_day_entry() {
		$ics = ( new Cdd_Core_Ics_Generator() )->generate( $this->circulos_event() );

		foreach ( $this->vevents( $ics ) as $vevent ) {
			preg_match( '/DTSTART;VALUE=DATE:(\d{8})/', $vevent, $start );
			preg_match( '/DTEND;VALUE=DATE:(\d{8})/', $vevent, $end );

			$this->assertSame(
				( new DateTimeImmutable( $start[1] ) )->modify( '+1 day' )->format( 'Ymd' ),
				$end[1]
			);
		}

		$this->assertStringContainsString(
			"DTEND;VALUE=DATE:20261025\r\n",
			$ics,
			'The last session (October 24) closes on the 25th.'
		);
	}

	/**
	 * BUG-001: every session carries its own UID, so a client stores ten
	 * entries instead of overwriting one. The session UIDs extend the
	 * event UID with the session date, keeping the site host.
	 */
	public function test_each_session_carries_a_unique_uid() {
		$ics = ( new Cdd_Core_Ics_Generator() )->generate( $this->circulos_event() );

		preg_match_all( "/UID:(.*?)\r\n/", $ics, $matches );
		$uids = $matches[1];

		$this->assertCount( 10, $uids );
		$this->assertSame( $uids, array_values( array_unique( $uids ) ) );
		$this->assertSame( 'circulos-de-presencia-consciente-20260903@caminodeldharma.org', $uids[0] );
		$this->assertSame( 'circulos-de-presencia-consciente-20261024@caminodeldharma.org', $uids[9] );
	}

	/**
	 * BUG-001: the shared identity of the course — summary, description,
	 * place, link, poster and organizer — repeats in every session, so a
	 * visitor who opens any one of them sees the whole event.
	 */
	public function test_every_session_repeats_the_shared_event_properties() {
		$ics = ( new Cdd_Core_Ics_Generator() )->generate( $this->circulos_event() );

		foreach ( $this->vevents( $ics ) as $vevent ) {
			$this->assertStringContainsString( 'SUMMARY:Círculos de Presencia Consciente', $vevent );
			$this->assertStringContainsString( 'DESCRIPTION:Sesión virtual de bienvenida.', $vevent );
			$this->assertStringContainsString( 'LOCATION:Bogotá y Cali', $vevent );
			$this->assertStringContainsString( 'URL:https://caminodeldharma.org/eventos/circulos-de-presencia-consciente', $vevent );
			$this->assertStringContainsString( 'ATTACH;FMTTYPE=image/jpeg;VALUE=URI:', $vevent );
			$this->assertStringContainsString( 'ORGANIZER;CN=Comunidad Buddhista Camino del Dharma:MAILTO:', $vevent );
			$this->assertStringContainsString( 'DTSTAMP:20260813T170000Z', $vevent );
		}
	}

	/**
	 * BUG-001 leaves the range fallback alone: an event without a session
	 * list is still one VEVENT from event_date to event_end, under the
	 * unsuffixed UID production already published.
	 */
	public function test_an_event_without_sessions_keeps_the_single_range_vevent() {
		$event                = $this->encuentro_event();
		$event['occurrences'] = array();

		$ics = ( new Cdd_Core_Ics_Generator() )->generate( $event );

		$this->assertCount( 1, $this->vevents( $ics ) );
		$this->assertStringContainsString( "UID:encuentro-nacional-2026@caminodeldharma.org\r\n", $ics );
		$this->assertStringContainsString( "DTSTART;VALUE=DATE:20260807\r\n", $ics );
		$this->assertStringContainsString( "DTEND;VALUE=DATE:20260810\r\n", $ics );
	}

	/**
	 * BUG-001: a session that lasts more than a day keeps its own
	 * exclusive end — the schedule may hold a weekend retreat.
	 */
	public function test_a_multi_day_session_closes_the_day_after_its_own_end() {
		$event                = $this->circulos_event();
		$event['occurrences'] = array(
			array(
				'start' => '2026-10-17',
				'end'   => '2026-10-18',
			),
		);

		$ics = ( new Cdd_Core_Ics_Generator() )->generate( $event );

		$this->assertCount( 1, $this->vevents( $ics ) );
		$this->assertStringContainsString( "DTSTART;VALUE=DATE:20261017\r\n", $ics );
		$this->assertStringContainsString( "DTEND;VALUE=DATE:20261019\r\n", $ics );
	}

	/**
	 * The wire format survives the multi-session document: still CRLF
	 * everywhere, still unfolded.
	 */
	public function test_a_multi_session_document_is_still_crlf() {
		$ics = ( new Cdd_Core_Ics_Generator() )->generate( $this->circulos_event() );

		$this->assertStringNotContainsString( "\n", str_replace( "\r\n", '', $ics ) );
	}

	/**
	 * The bodies of every VEVENT in a document.
	 *
	 * @param string $ics Calendar document.
	 */
	private function vevents( string $ics ): array {
		preg_match_all( "/BEGIN:VEVENT\r\n(.*?)END:VEVENT\r\n/s", $ics, $matches );

		return $matches[1];
	}

	/**
	 * The Círculos course with the ten sessions of its published
	 * cronograma (payload calendar_dates / meta event_calendar_dates).
	 */
	private function circulos_event(): array {
		$occurrences = array();
		foreach ( self::CIRCULOS_SESSIONS as $session ) {
			$occurrences[] = array(
				'start' => $session,
				'end'   => null,
			);
		}

		return array(
			'uid'             => 'circulos-de-presencia-consciente@caminodeldharma.org',
			'summary'         => 'Círculos de Presencia Consciente',
			'description'     => 'Sesión virtual de bienvenida.',
			'location'        => 'Bogotá y Cali',
			'url'             => 'https://caminodeldharma.org/eventos/circulos-de-presencia-consciente',
			'attach'          => 'https://caminodeldharma.org/assets/images/eventos/evento-circulos-de-presencia-consciente.jpg',
			'organizer_name'  => 'Comunidad Buddhista Camino del Dharma',
			'organizer_email' => 'caminodeldharma1@gmail.com',
			'start'           => '2026-09-03',
			'end'             => '2026-10-24',
			'occurrences'     => $occurrences,
			'dtstamp'         => new DateTimeImmutable( '2026-08-13T17:00:00+00:00' ),
		);
	}
}
