<?php
/**
 * iCalendar payload generator (OWN-009 / OWN-012).
 *
 * Pure domain code: no WordPress APIs. The contract is the production
 * .ics pair that shipped with the static site: VCALENDAR 2.0 with the
 * community PRODID, all-day DTSTART/DTEND with an exclusive end, RFC 5545
 * text escaping and CRLF line endings. Lines are not folded, matching the
 * files already consumed by calendar clients in production.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generates a single-event iCalendar document from event data.
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
			'BEGIN:VEVENT',
			'UID:' . $this->escape_text( (string) $event['uid'] ),
			'DTSTAMP:' . $event['dtstamp']->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Ymd\THis\Z' ),
			'DTSTART;VALUE=DATE:' . $this->compact_date( (string) $event['start'] ),
			'DTEND;VALUE=DATE:' . $this->exclusive_end( $event ),
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
		$lines[] = 'END:VCALENDAR';

		return implode( "\r\n", $lines ) . "\r\n";
	}

	/**
	 * The exclusive DTEND date: the day after the inclusive end (or after
	 * the start for single-day events), as production encodes it.
	 *
	 * @param array $event Event data.
	 */
	private function exclusive_end( array $event ): string {
		$inclusive_end = (string) ( $event['end'] ?? '' );
		if ( '' === $inclusive_end ) {
			$inclusive_end = (string) $event['start'];
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
