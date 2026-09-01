<?php
/**
 * Request-time event queries: status, visibility, home selection,
 * calendar data and the generated ICS response (OWN-009, OWN-012,
 * OWN-013; doc 03 §3).
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The request-time instant in America/Bogota.
 */
function cdd_core_now(): DateTimeImmutable {
	return new DateTimeImmutable( 'now', new DateTimeZone( Cdd_Core_Event_Status::TIMEZONE ) );
}

/**
 * The public status of one event at request time: the stored dates decide;
 * the stored event_status only wins when it says cancelled.
 *
 * @param int|WP_Post            $event Event post or ID.
 * @param DateTimeImmutable|null $now   Request-time instant (defaults to now).
 */
function cdd_core_event_status( $event, ?DateTimeImmutable $now = null ): string {
	$event = get_post( $event );
	if ( ! $event ) {
		return '';
	}

	$start = (string) get_post_meta( $event->ID, 'event_date', true );
	$end   = (string) get_post_meta( $event->ID, 'event_end', true );

	return Cdd_Core_Event_Status::resolve(
		'' !== $start ? $start : null,
		'' !== $end ? $end : null,
		Cdd_Core_Event_Status::STATUS_CANCELLED === get_post_meta( $event->ID, 'event_status', true ),
		$now ?? cdd_core_now()
	);
}

/**
 * Whether one event is current at request time.
 *
 * @param int|WP_Post            $event Event post or ID.
 * @param DateTimeImmutable|null $now   Request-time instant.
 */
function cdd_core_event_is_current( $event, ?DateTimeImmutable $now = null ): bool {
	return Cdd_Core_Event_Status::STATUS_CURRENT === cdd_core_event_status( $event, $now );
}

/**
 * All published events (the whole catalog is small by design; visibility
 * splits happen in PHP with the request-time rule).
 */
function cdd_core_events(): array {
	return get_posts(
		array(
			'post_type'   => 'event',
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby'     => 'ID',
			'order'       => 'ASC',
		)
	);
}

/**
 * The current events at request time, soonest start first.
 *
 * @param DateTimeImmutable|null $now Request-time instant.
 */
function cdd_core_current_events( ?DateTimeImmutable $now = null ): array {
	$now = $now ?? cdd_core_now();

	$current = array();
	foreach ( cdd_core_events() as $event ) {
		if ( cdd_core_event_is_current( $event, $now ) ) {
			$current[] = $event;
		}
	}

	usort(
		$current,
		static function ( WP_Post $a, WP_Post $b ): int {
			return strcmp( (string) get_post_meta( $a->ID, 'event_date', true ), (string) get_post_meta( $b->ID, 'event_date', true ) );
		}
	);

	return $current;
}

/**
 * The events that are not current at request time (completed or
 * cancelled), newest start first — the archive block of memory
 * (doc 03 §3, WU-07).
 *
 * @param DateTimeImmutable|null $now Request-time instant.
 */
function cdd_core_past_events( ?DateTimeImmutable $now = null ): array {
	$now = $now ?? cdd_core_now();

	$past = array();
	foreach ( cdd_core_events() as $event ) {
		if ( ! cdd_core_event_is_current( $event, $now ) ) {
			$past[] = $event;
		}
	}

	usort(
		$past,
		static function ( WP_Post $a, WP_Post $b ): int {
			return strcmp( (string) get_post_meta( $b->ID, 'event_date', true ), (string) get_post_meta( $a->ID, 'event_date', true ) );
		}
	);

	return $past;
}

/**
 * The single event the home page may show, or null (doc 03 §3: a current
 * featured event wins; else the nearest current start; completed featured
 * events are ignored; no current event renders nothing).
 *
 * @param DateTimeImmutable|null $now Request-time instant.
 */
function cdd_core_featured_home_event( ?DateTimeImmutable $now = null ): ?WP_Post {
	$now = $now ?? cdd_core_now();

	$descriptors = array();
	foreach ( cdd_core_events() as $event ) {
		$descriptors[] = array(
			'id'          => $event->ID,
			'is_current'  => cdd_core_event_is_current( $event, $now ),
			'is_featured' => (bool) get_post_meta( $event->ID, 'event_featured', true ),
			'start'       => (string) get_post_meta( $event->ID, 'event_date', true ),
		);
	}

	$selected = ( new Cdd_Core_Featured_Event_Policy() )->select( $descriptors );

	return $selected ? get_post( $selected['id'] ) : null;
}

/**
 * The dates the calendar marks for one event: the explicit session list
 * when present (the static calendar marks course sessions), else the
 * event_date..event_end range.
 *
 * @param WP_Post $event Event post.
 */
function cdd_core_event_calendar_dates( WP_Post $event ): array {
	$sessions = get_post_meta( $event->ID, 'event_calendar_dates', true );
	if ( is_array( $sessions ) && ! empty( $sessions ) ) {
		return array_values( $sessions );
	}

	$start = (string) get_post_meta( $event->ID, 'event_date', true );
	if ( ! cdd_core_is_ymd( $start ) ) {
		return array();
	}
	$end = (string) get_post_meta( $event->ID, 'event_end', true );
	if ( ! cdd_core_is_ymd( $end ) || $end < $start ) {
		$end = $start;
	}

	$dates  = array();
	$cursor = new DateTimeImmutable( $start );
	// Hard cap keeps a typo in event_end from expanding into thousands of cells.
	for ( $i = 0; $i < 90; $i++ ) {
		$date = $cursor->format( 'Y-m-d' );
		if ( $date > $end ) {
			break;
		}
		$dates[] = $date;
		$cursor  = $cursor->modify( '+1 day' );
	}

	return $dates;
}

/**
 * The month grid the events calendar block renders (domain data only; the
 * theme paints it — doc 03 §3, ADR 0024).
 *
 * @param DateTimeImmutable|null $now Request-time instant.
 */
function cdd_core_calendar_month_data( ?DateTimeImmutable $now = null ): array {
	$now = $now ?? cdd_core_now();

	$descriptors = array();
	foreach ( cdd_core_current_events( $now ) as $event ) {
		$descriptors[] = array(
			'title' => get_the_title( $event ),
			'url'   => get_permalink( $event ),
			'dates' => cdd_core_event_calendar_dates( $event ),
		);
	}

	$calendar = new Cdd_Core_Calendar_Data();

	return $calendar->build(
		$calendar->choose_month( $descriptors, $now ),
		$descriptors,
		home_url( '/practica/meditacion-semanal-en-linea' ),
		__( 'Meditación semanal en línea', 'camino-del-dharma-core' )
	);
}

/**
 * Everything a calendar surface needs about one event, resolved once
 * (WU-08A / BUG-001). The «Añadir al calendario» dialog and the
 * generated /eventos/ical/{slug}.ics both read this array, so the
 * Google/Outlook deep links a visitor follows and the file they download
 * can never describe different dates.
 *
 * Dates come in three forms: the course span as stored (start_date /
 * end_date), the published session schedule the file exports one VEVENT
 * each (occurrences), and the compact Ymd pair with the exclusive end
 * the dialog deep-links (start / end). A deep link carries a single
 * entry, so it names the next session — a date the file contains —
 * rather than a range that appears in no VEVENT. Without a schedule all
 * three describe the same event_date..event_end range, as WU-08A shipped.
 *
 * @param WP_Post                $event Event post.
 * @param DateTimeImmutable|null $now   Request-time instant.
 */
function cdd_core_event_calendar_payload( WP_Post $event, ?DateTimeImmutable $now = null ): array {
	$start = (string) get_post_meta( $event->ID, 'event_date', true );
	$end   = (string) get_post_meta( $event->ID, 'event_end', true );

	if ( ! cdd_core_is_ymd( $start ) ) {
		return array();
	}
	if ( ! cdd_core_is_ymd( $end ) || $end < $start ) {
		$end = '';
	}

	$occurrences = cdd_core_event_calendar_occurrences( $event );
	$deep_link   = cdd_core_event_calendar_deep_link( $occurrences, $start, $end, $now ?? cdd_core_now() );

	return array(
		'title'         => get_the_title( $event ),
		'start_date'    => $start,
		'end_date'      => '' !== $end ? $end : null,
		'occurrences'   => $occurrences,
		'session_count' => count( $occurrences ),
		'next'          => $deep_link,
		'start'         => $deep_link['start'],
		'end'           => $deep_link['end'],
		'description'   => $event->post_excerpt ? wp_strip_all_tags( $event->post_excerpt ) : '',
		'location'      => (string) get_post_meta( $event->ID, 'event_place', true ),
		'url'           => (string) get_permalink( $event ),
		'ics_url'       => home_url( '/eventos/ical/' . $event->post_name . '.ics' ),
		'ics_filename'  => $event->post_name . '.ics',
	);
}

/**
 * The published sessions of an event as calendar occurrences, in order
 * (BUG-001). An event without an explicit schedule has none: its single
 * range entry is built by the caller.
 *
 * @param WP_Post $event Event post.
 */
function cdd_core_event_calendar_occurrences( WP_Post $event ): array {
	$sessions = get_post_meta( $event->ID, 'event_calendar_dates', true );
	if ( ! is_array( $sessions ) ) {
		return array();
	}

	$sessions = array_values( array_filter( $sessions, 'cdd_core_is_ymd' ) );
	sort( $sessions );

	return array_map( 'cdd_core_calendar_occurrence', $sessions );
}

/**
 * One all-day calendar occurrence in both forms the surfaces need: the
 * stored inclusive dates and the compact pair with the exclusive end.
 *
 * @param string      $start Start date (Y-m-d).
 * @param string|null $end   Inclusive end date (Y-m-d), or null for one day.
 */
function cdd_core_calendar_occurrence( string $start, ?string $end = null ): array {
	$last = ( null !== $end && '' !== $end ) ? $end : $start;

	return array(
		'start_date' => $start,
		'end_date'   => ( null !== $end && '' !== $end ) ? $end : null,
		'start'      => ( new DateTimeImmutable( $start ) )->format( 'Ymd' ),
		'end'        => ( new DateTimeImmutable( $last ) )->modify( '+1 day' )->format( 'Ymd' ),
	);
}

/**
 * The occurrence the Google/Outlook deep links add (BUG-001): the first
 * session that has not happened yet in America/Bogota, the last one when
 * every session is behind us, and the whole event range when there is no
 * schedule at all.
 *
 * @param array             $occurrences Published sessions, in order.
 * @param string            $start       Event start date (Y-m-d).
 * @param string            $end         Inclusive event end date (Y-m-d) or ''.
 * @param DateTimeImmutable $now         Request-time instant.
 */
function cdd_core_event_calendar_deep_link( array $occurrences, string $start, string $end, DateTimeImmutable $now ): array {
	if ( empty( $occurrences ) ) {
		return cdd_core_calendar_occurrence( $start, '' !== $end ? $end : null );
	}

	$today = Cdd_Core_Event_Status::today( $now );
	foreach ( $occurrences as $occurrence ) {
		if ( ( $occurrence['end_date'] ?? $occurrence['start_date'] ) >= $today ) {
			return $occurrence;
		}
	}

	return end( $occurrences );
}

/**
 * One calendar occurrence in the inclusive form the ICS generator
 * consumes. The payload also carries the compact pair with the
 * exclusive end the dialog deep-links; handing that one to the
 * generator would push every DTEND a day too far.
 *
 * @param array $occurrence Occurrence from the calendar payload.
 */
function cdd_core_ics_occurrence( array $occurrence ): array {
	return array(
		'start' => $occurrence['start_date'],
		'end'   => $occurrence['end_date'],
	);
}

/**
 * The response for /eventos/ical/{slug}.ics as data: 200 with generated
 * calendar content for a current event, 410 for a completed/cancelled
 * one (OWN-012; no orphan files — the payload is generated, never
 * stored), 404 for anything else. The route handler sends it.
 *
 * @param string                 $slug Event slug from the route.
 * @param DateTimeImmutable|null $now  Request-time instant.
 */
function cdd_core_event_ics_response( string $slug, ?DateTimeImmutable $now = null ): array {
	$now      = $now ?? cdd_core_now();
	$noindex  = array( 'X-Robots-Tag' => 'noindex, nofollow' );
	$events   = get_posts(
		array(
			'post_type'   => 'event',
			'post_status' => 'publish',
			'name'        => $slug,
			'numberposts' => 1,
		)
	);
	$event    = $events[0] ?? null;
	$calendar = $event ? cdd_core_event_calendar_payload( $event, $now ) : array();

	if ( empty( $calendar ) ) {
		return array(
			'status'  => 404,
			'headers' => $noindex,
			'body'    => '',
		);
	}

	if ( ! cdd_core_event_is_current( $event, $now ) ) {
		return array(
			'status'  => 410,
			'headers' => $noindex,
			'body'    => '',
		);
	}

	$generator = new Cdd_Core_Ics_Generator();
	$body      = $generator->generate(
		array(
			'uid'             => $slug . '@' . wp_parse_url( home_url(), PHP_URL_HOST ),
			'summary'         => $calendar['title'],
			'description'     => $calendar['description'],
			'location'        => $calendar['location'],
			'url'             => $calendar['url'],
			'attach'          => (string) get_the_post_thumbnail_url( $event, 'full' ),
			'organizer_name'  => 'Comunidad Buddhista Camino del Dharma',
			'organizer_email' => 'caminodeldharma1@gmail.com',
			'start'           => $calendar['start_date'],
			'end'             => $calendar['end_date'],
			'occurrences'     => array_map( 'cdd_core_ics_occurrence', $calendar['occurrences'] ),
			'dtstamp'         => $now,
		)
	);

	return array(
		'status'  => 200,
		'headers' => array_merge(
			$noindex,
			array(
				'Content-Type'        => 'text/calendar; charset=utf-8',
				'Content-Disposition' => 'attachment; filename="' . $slug . '.ics"',
			)
		),
		'body'    => $body,
	);
}
