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
}
