<?php
/**
 * iCalendar payload generator (OWN-009 / OWN-012 / BUG-001).
 *
 * Pure domain code: no WordPress APIs. The contract is the production
 * .ics pair that shipped with the static site: VCALENDAR 2.0 with the
 * community PRODID, all-day DTSTART/DTEND with an exclusive end, RFC 5545
 * text escaping and CRLF line endings. Lines are not folded, matching the
 * files already consumed by calendar clients in production.
 *
 * An event that publishes a session schedule exports one VEVENT per
 * session (BUG-001): a course with ten irregular sessions is ten entries
 * a client can store, not one block of 52 straight days. Without a
 * schedule the event stays the single event_date..event_end entry.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generates an iCalendar document from event data.
 */
final class Cdd_Core_Ics_Generator {

	const PRODID = '-//Comunidad Buddhista Camino del Dharma//Eventos//ES';

	/**
	 * Generates the .ics document.
	 *
	 * @param array $event {
	 *     Event data.
	 *
	 *     @type string            $uid             Calendar UID (slug@host).
	 *     @type string            $summary         Event title.
	 *     @type string            $description     Optional description.
	 *     @type string            $location        Optional location.
	 *     @type string            $url             Optional public event URL.
	 *     @type string            $attach          Optional poster image URL.
	 *     @type string            $organizer_name  Optional organizer CN.
	 *     @type string            $organizer_email Optional organizer mail.
	 *     @type string            $start           Start date (Y-m-d).
	 *     @type string|null       $end             Inclusive end date (Y-m-d) or null.
	 *     @type array             $occurrences     Optional session list, each
	 *                                              array{start:string,end:?string};
	 *                                              empty means the start..end range.
	 *     @type DateTimeImmutable $dtstamp         Generation instant.
	 * }
	 */
	public function generate( array $event ): string {
		$lines = array(
			'BEGIN:VCALENDAR',
			'VERSION:2.0',
			'PRODID:' . self::PRODID,
			'CALSCALE:GREGORIAN',
			'METHOD:PUBLISH',
		);

		foreach ( $this->occurrences( $event ) as $occurrence ) {
			$lines = array_merge( $lines, $this->vevent( $event, $occurrence ) );
		}

		$lines[] = 'END:VCALENDAR';

		return implode( "\r\n", $lines ) . "\r\n";
	}

	/**
	 * The occurrences to export: the published session schedule when the
	 * event has one, else the single event_date..event_end entry. Only
	 * the schedule form suffixes the UID, so an event without sessions
	 * keeps the exact UID production already published.
	 *
	 * @param array $event Event data.
	 */
	private function occurrences( array $event ): array {
		$sessions = (array) ( $event['occurrences'] ?? array() );

		if ( empty( $sessions ) ) {
			return array(
				array(
					'uid'   => (string) $event['uid'],
					'start' => (string) $event['start'],
					'end'   => (string) ( $event['end'] ?? '' ),
				),
			);
		}

		$occurrences = array();
		foreach ( $sessions as $session ) {
			$start         = (string) $session['start'];
			$occurrences[] = array(
				'uid'   => $this->session_uid( (string) $event['uid'], $start ),
				'start' => $start,
				'end'   => (string) ( $session['end'] ?? '' ),
			);
		}

		return $occurrences;
	}

	/**
	 * The UID of one session: the event UID with the session date, so ten
	 * sessions are ten entries a client stores side by side instead of
	 * overwriting one another.
	 *
	 * @param string $event_uid Event UID (slug@host).
	 * @param string $start     Session start date (Y-m-d).
	 */
	private function session_uid( string $event_uid, string $start ): string {
		$date     = $this->compact_date( $start );
		$position = strpos( $event_uid, '@' );

		if ( false === $position ) {
			return $event_uid . '-' . $date;
		}

		return substr( $event_uid, 0, $position ) . '-' . $date . substr( $event_uid, $position );
	}

	/**
	 * One VEVENT: the dates of this occurrence plus the shared identity
	 * of the event, so any single session opened in a client still names
	 * the course, its place, its page and its poster.
	 *
	 * @param array $event      Event data.
	 * @param array $occurrence Occurrence (uid, start, end).
	 */
	private function vevent( array $event, array $occurrence ): array {
		$lines = array(
			'BEGIN:VEVENT',
			'UID:' . $this->escape_text( $occurrence['uid'] ),
			'DTSTAMP:' . $event['dtstamp']->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Ymd\THis\Z' ),
			'DTSTART;VALUE=DATE:' . $this->compact_date( $occurrence['start'] ),
			'DTEND;VALUE=DATE:' . $this->exclusive_end( $occurrence ),
			'SUMMARY:' . $this->escape_text( (string) $event['summary'] ),
		);

		if ( '' !== (string) ( $event['description'] ?? '' ) ) {
			$lines[] = 'DESCRIPTION:' . $this->escape_text( (string) $event['description'] );
		}
		if ( '' !== (string) ( $event['location'] ?? '' ) ) {
			$lines[] = 'LOCATION:' . $this->escape_text( (string) $event['location'] );
		}
		if ( '' !== (string) ( $event['url'] ?? '' ) ) {
			$lines[] = 'URL:' . (string) $event['url'];
		}
		if ( '' !== (string) ( $event['attach'] ?? '' ) ) {
			$lines[] = 'ATTACH;FMTTYPE=' . $this->image_mime( (string) $event['attach'] ) . ';VALUE=URI:' . (string) $event['attach'];
		}
		if ( '' !== (string) ( $event['organizer_email'] ?? '' ) ) {
			$lines[] = 'ORGANIZER;CN=' . (string) ( $event['organizer_name'] ?? '' ) . ':MAILTO:' . (string) $event['organizer_email'];
		}

		$lines[] = 'END:VEVENT';

		return $lines;
	}

	/**
	 * The exclusive DTEND date of one occurrence: the day after its
	 * inclusive end (or after its start when it lasts a single day), as
	 * production encodes it.
	 *
	 * @param array $occurrence Occurrence (start, end).
	 */
	private function exclusive_end( array $occurrence ): string {
		$inclusive_end = (string) ( $occurrence['end'] ?? '' );
		if ( '' === $inclusive_end ) {
			$inclusive_end = (string) $occurrence['start'];
		}

		return ( new DateTimeImmutable( $inclusive_end ) )->modify( '+1 day' )->format( 'Ymd' );
	}

	/**
	 * A Y-m-d date compacted to the iCalendar Ymd form.
	 *
	 * @param string $date Date (Y-m-d).
	 */
	private function compact_date( string $date ): string {
		return ( new DateTimeImmutable( $date ) )->format( 'Ymd' );
	}

	/**
	 * RFC 5545 TEXT escaping: backslash, semicolon and comma are escaped;
	 * line breaks become literal \n.
	 *
	 * @param string $value Raw text value.
	 */
	private function escape_text( string $value ): string {
		$value = str_replace( '\\', '\\\\', $value );
		$value = str_replace( array( ';', ',' ), array( '\\;', '\\,' ), $value );

		return str_replace( array( "\r\n", "\n", "\r" ), '\\n', $value );
	}

	/**
	 * FMTTYPE for a poster URL by extension (production posters are JPEG).
	 *
	 * @param string $url Image URL.
	 */
	private function image_mime( string $url ): string {
		$extension = strtolower( (string) pathinfo( (string) parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- pure class usable without WordPress loaded.

		$mimes = array(
			'png'  => 'image/png',
			'webp' => 'image/webp',
			'gif'  => 'image/gif',
		);

		return $mimes[ $extension ] ?? 'image/jpeg';
	}
}
