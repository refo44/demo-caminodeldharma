<?php
/**
 * Server-side markup of the theme's dynamic blocks (WU-07, docs/12 §2).
 *
 * Presentation only: every domain decision (which events are current,
 * which cells the calendar marks, which posts belong to an author) comes
 * resolved from camino-del-dharma-core; these renderers paint the exact
 * structure the static production site publishes (OWN-007). Documented
 * substitutions (compact past cards per doc 03 §3, generated date
 * strings) live in the migration matrix.
 *
 * @package Camino_Del_Dharma
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Markup builders for the camino-del-dharma dynamic blocks.
 */
final class Camino_Del_Dharma_Renderers {

	const SECTION_ICON_UPCOMING = '<svg class="eventos-section-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><path d="M3 10h18"/></svg>';

	const SECTION_ICON_PAST = '<svg class="eventos-section-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>';

	const ICON_CALENDAR = '<svg class="lucide-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg>';

	const ICON_SHARE = '<svg class="lucide-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"/></svg>';

	/**
	 * Share platforms in the published dialog order, with the meta key
	 * holding each message template.
	 */
	const SHARE_PLATFORMS = array( 'whatsapp', 'x', 'threads' );

	/**
	 * The month calendar grid, matching the published markup of
	 * static/eventos/index.html cell by cell (doc 03 §3).
	 *
	 * @param array $data Month data from cdd_core_calendar_month_data()
	 *                    (cell URLs already resolved by the caller).
	 */
	public static function calendar( array $data ): string {
		$weekdays = array( 'Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb' );

		$header = '';
		foreach ( $weekdays as $weekday ) {
			$header .= '<span class="eventos-calendar-weekday" role="columnheader">' . esc_html( $weekday ) . '</span>';
		}

		$cells = array();
		// Leading blanks: ISO weekday N maps to column N % 7 (Sunday first).
		$lead = ( (int) $data['first_weekday'] ) % 7;
		for ( $i = 0; $i < $lead; $i++ ) {
			$cells[] = '<span class="eventos-calendar-day" role="gridcell" aria-hidden="true"></span>';
		}

		foreach ( $data['days'] as $day => $cell ) {
			$cells[] = self::calendar_cell( (int) $day, $cell, (string) $data['practice_url'], (string) $data['practice_label'] );
		}

		$rows = '<div class="eventos-calendar-row" role="row">' . $header . '</div>';
		foreach ( array_chunk( $cells, 7 ) as $week ) {
			$rows .= "\n" . '<div class="eventos-calendar-row" role="row">' . implode( '', $week ) . '</div>';
		}

		return '<section class="eventos-calendar section-gap read-width" aria-label="' . esc_attr__( 'Calendario del mes', 'camino-del-dharma' ) . '">' . "\n" .
			'<h2 class="eventos-calendar-title">' . esc_html( Camino_Del_Dharma_Format::month_title( (string) $data['month'] ) ) . '</h2>' . "\n" .
			'<div class="eventos-calendar-grid" role="grid" aria-readonly="true">' . "\n" . $rows . "\n" . '</div>' . "\n" .
			'<p class="eventos-calendar-hint" aria-hidden="true">' . esc_html__( 'Toca de nuevo para ver el evento.', 'camino-del-dharma' ) . '</p>' . "\n" .
			'</section>';
	}

	/**
	 * The archive listing: current events with the full treatment, then
	 * the completed archive as compact cards grouped by year (doc 03 §3).
	 *
	 * @param array $current Current events (WP_Post[]), soonest first.
	 * @param array $past    Completed/cancelled events, newest start first.
	 */
	public static function events_listing( array $current, array $past ): string {
		$html = '<section class="eventos-section" aria-labelledby="eventos-proximos-heading">' . "\n" .
			'<h2 id="eventos-proximos-heading" class="eventos-section-title">' . self::SECTION_ICON_UPCOMING . ' ' . esc_html__( 'Próximos eventos', 'camino-del-dharma' ) . '</h2>' . "\n";

		if ( empty( $current ) ) {
			$html .= '<p class="eventos-empty read-width">' . esc_html__( 'No hay eventos programados en este momento.', 'camino-del-dharma' ) . '</p>' . "\n";
		} else {
			foreach ( $current as $event ) {
				$html .= self::current_event_card( $event ) . "\n";
			}
		}

		$html .= '</section>' . "\n" .
			'<hr class="eventos-divider" aria-hidden="true">' . "\n" .
			'<section class="eventos-section" aria-labelledby="eventos-realizados-heading">' . "\n" .
			'<h2 id="eventos-realizados-heading" class="eventos-section-title">' . self::SECTION_ICON_PAST . ' ' . esc_html__( 'Eventos realizados', 'camino-del-dharma' ) . '</h2>' . "\n";

		$by_year = array();
		foreach ( $past as $event ) {
			$year               = Camino_Del_Dharma_Format::event_year( (string) get_post_meta( $event->ID, 'event_date', true ) );
			$by_year[ $year ][] = $event;
		}
		krsort( $by_year );

		foreach ( $by_year as $year => $events ) {
			$year_id = 'eventos-' . $year;
			$html   .= '<h3 class="eventos-anio" id="' . esc_attr( $year_id ) . '">' . esc_html( (string) $year ) . '</h3>' . "\n" .
				'<div class="eventos-anio-grupo" role="group" aria-labelledby="' . esc_attr( $year_id ) . '">' . "\n";
			foreach ( $events as $event ) {
				$html .= self::past_event_card( $event ) . "\n";
			}
			$html .= '</div>' . "\n";
		}

		$html .= '</section>';

		return $html;
	}

	/**
	 * The home note of at most one current event, or nothing (doc 03 §3:
	 * no empty module, no message).
	 *
	 * @param WP_Post|null $event The event cdd_core_featured_home_event() chose.
	 */
	public static function featured_event( ?WP_Post $event ): string {
		if ( null === $event ) {
			return '';
		}

		$url      = get_permalink( $event );
		$type     = self::event_type_name( $event );
		$featured = get_post_meta( $event->ID, 'event_featured', true ) ? 'true' : 'false';

		$kicker = '' === $type
			? esc_html__( 'Próximo evento', 'camino-del-dharma' )
			: esc_html__( 'Próximo evento', 'camino-del-dharma' ) . ' · ' . esc_html( $type );

		$thumb = '';
		if ( has_post_thumbnail( $event ) ) {
			$thumb = '<a href="' . esc_url( $url ) . '" class="evento-figure-link" tabindex="-1" aria-hidden="true">' .
				get_the_post_thumbnail( $event, 'medium', array( 'class' => 'home-featured-event-thumb' ) ) .
				'</a>' . "\n";
		}

		$meta_parts = array_filter(
			array(
				esc_html( self::event_date_display( $event ) ),
				esc_html( Camino_Del_Dharma_Format::name_list( self::event_city_names( $event ) ) ),
			)
		);

		return '<aside class="home-featured-event" aria-labelledby="home-featured-event-title" data-event-status="vigente" data-event-featured="' . esc_attr( $featured ) . '">' . "\n" .
			'<p class="home-featured-event-kicker">' . $kicker . '</p>' . "\n" .
			$thumb .
			'<h3 id="home-featured-event-title" class="home-featured-event-title"><a href="' . esc_url( $url ) . '">' . esc_html( get_the_title( $event ) ) . '</a></h3>' . "\n" .
			'<p class="home-featured-event-meta">' . implode( '<br>', $meta_parts ) . '</p>' . "\n" .
			'<p class="home-featured-event-actions"><a href="' . esc_url( $url ) . '">' . esc_html__( 'Ver evento', 'camino-del-dharma' ) . '</a></p>' . "\n" .
			'</aside>';
	}

	/**
	 * The type label above an event title (taxonomy event_type, doc 03 §4).
	 *
	 * @param WP_Post $event Event post.
	 */
	public static function event_type_label( WP_Post $event ): string {
		$type = self::event_type_name( $event );

		return '' === $type ? '' : '<p class="evento-type">' . esc_html( $type ) . '</p>';
	}

	/**
	 * The event meta list (Fecha / Lugar / Modalidad) from real meta.
	 *
	 * @param WP_Post $event Event post.
	 */
	public static function event_meta( WP_Post $event ): string {
		$rows = array(
			__( 'Fecha', 'camino-del-dharma' )     => self::event_date_display( $event ),
			__( 'Lugar', 'camino-del-dharma' )     => (string) get_post_meta( $event->ID, 'event_place', true ),
			__( 'Modalidad', 'camino-del-dharma' ) => (string) get_post_meta( $event->ID, 'event_modality', true ),
		);

		$items = '';
		foreach ( $rows as $label => $value ) {
			if ( '' === $value ) {
				continue;
			}
			$items .= '<dt>' . esc_html( $label ) . '</dt>' . "\n" . '<dd>' . esc_html( $value ) . '</dd>' . "\n";
		}

		return '' === $items ? '' : '<dl class="evento-meta">' . "\n" . $items . '</dl>';
	}

	/**
	 * The signup CTA — only while the event is current and has a real
	 * signup URL (OWN-012 / ADR 0035: completed events never invite).
	 *
	 * @param WP_Post $event   Event post.
	 * @param bool    $current Whether the event is current at request time.
	 */
	public static function event_cta( WP_Post $event, bool $current ): string {
		$signup_url = (string) get_post_meta( $event->ID, 'event_signup_url', true );
		if ( ! $current || '' === $signup_url ) {
			return '';
		}

		return '<p class="evento-cta">' . "\n" .
			'<a href="' . esc_url( $signup_url ) . '" class="btn btn-primary" target="_blank" rel="noopener noreferrer">' .
			esc_html__( 'Preinscribirme', 'camino-del-dharma' ) .
			' <span class="visually-hidden">' . esc_html__( '(abre en nueva pestaña)', 'camino-del-dharma' ) . '</span></a>' . "\n" .
			'</p>';
	}

	/**
	 * The dialog triggers of an event, as published: «Añadir al
	 * calendario» plus «Compartir», preceded by the message templates
	 * share.js reads. Only while the event is current — a completed
	 * event invites nobody (OWN-012), exactly as the published past
	 * singles do.
	 *
	 * @param WP_Post $event    Event post.
	 * @param bool    $current  Whether the event is current at request time.
	 * @param array   $calendar Calendar payload from
	 *                          cdd_core_event_calendar_payload().
	 */
	public static function event_actions( WP_Post $event, bool $current, array $calendar ): string {
		if ( ! $current ) {
			return '';
		}

		$calendar_button = '';
		if ( ! empty( $calendar ) ) {
			$calendar_button = '<button' . "\n" .
				'type="button"' . "\n" .
				'class="btn btn-secondary calendar-trigger"' . "\n" .
				'data-calendar-title="' . esc_attr( (string) $calendar['title'] ) . '"' . "\n" .
				'data-calendar-start="' . esc_attr( (string) $calendar['start'] ) . '"' . "\n" .
				'data-calendar-end="' . esc_attr( (string) $calendar['end'] ) . '"' . "\n" .
				'data-calendar-description="' . esc_attr( (string) $calendar['description'] ) . '"' . "\n" .
				'data-calendar-event-url="' . esc_attr( (string) $calendar['url'] ) . '"' . "\n" .
				'data-calendar-location="' . esc_attr( (string) $calendar['location'] ) . '"' . "\n" .
				'data-calendar-ics="' . esc_attr( (string) $calendar['ics_url'] ) . '"' . "\n" .
				self::calendar_schedule_attributes( $calendar ) .
				'>' . "\n" . self::ICON_CALENDAR . "\n" .
				esc_html__( 'Añadir al calendario', 'camino-del-dharma' ) . "\n" .
				'</button>' . "\n";
		}

		return self::share_templates( $event ) .
			'<div class="evento-actions">' . "\n" .
			$calendar_button .
			'<p class="share-actions">' . "\n" .
			self::share_trigger( $event, self::share_title( $event ) ) . "\n" .
			'</p>' . "\n" .
			'</div>';
	}

	/**
	 * The schedule attributes of a course trigger (BUG-001). A Google or
	 * Outlook deep link carries a single entry, so it adds the next
	 * session while the .ics carries all of them; the note says so, and
	 * the dialog is described by it. An event without a published
	 * schedule prints neither attribute — the trigger stays as WU-08A
	 * shipped it.
	 *
	 * @param array $calendar Calendar payload from
	 *                        cdd_core_event_calendar_payload().
	 */
	private static function calendar_schedule_attributes( array $calendar ): string {
		$sessions = (int) ( $calendar['session_count'] ?? 0 );
		if ( $sessions < 2 ) {
			return '';
		}

		$note = sprintf(
			/* translators: 1: number of sessions, 2: date of the next session. */
			esc_html__( 'El archivo .ics incluye las %1$d sesiones del curso. Google Calendar y Outlook añaden la próxima: %2$s.', 'camino-del-dharma' ),
			$sessions,
			Camino_Del_Dharma_Format::event_date_range( (string) $calendar['next']['start_date'], $calendar['next']['end_date'] ?? null )
		);

		return 'data-calendar-sessions="' . esc_attr( (string) $sessions ) . '"' . "\n" .
			'data-calendar-note="' . esc_attr( $note ) . '"' . "\n";
	}

	/**
	 * The share control of a blog entry, as published: the message
	 * templates followed by the trigger.
	 *
	 * @param WP_Post $post Blog post.
	 */
	public static function entry_share( WP_Post $post ): string {
		return self::share_templates( $post ) .
			'<p class="share-actions">' . "\n" .
			self::share_trigger( $post, get_the_title( $post ) ) . "\n" .
			'</p>';
	}

	/**
	 * The <template> bodies of the stored share messages. A platform
	 * without stored copy prints nothing: share.js then falls back to
	 * title + URL instead of sharing an empty message.
	 *
	 * @param WP_Post $post Event or blog post.
	 */
	private static function share_templates( WP_Post $post ): string {
		$html = '';
		foreach ( self::SHARE_PLATFORMS as $platform ) {
			$template = (string) get_post_meta( $post->ID, 'share_' . $platform, true );
			if ( '' === $template ) {
				continue;
			}
			$html .= '<template id="' . esc_attr( $platform . '-' . $post->post_name ) . '">' . "\n" .
				esc_html( $template ) . "\n" . '</template>' . "\n";
		}

		return $html;
	}

	/**
	 * The share trigger button, carrying the URL to share and the ids of
	 * the templates that exist for this object.
	 *
	 * @param WP_Post $post  Event or blog post.
	 * @param string  $title Title the dialog shows and shares.
	 */
	private static function share_trigger( WP_Post $post, string $title ): string {
		$templates = '';
		foreach ( self::SHARE_PLATFORMS as $platform ) {
			if ( '' === (string) get_post_meta( $post->ID, 'share_' . $platform, true ) ) {
				continue;
			}
			$templates .= 'data-share-' . $platform . '-template="' . esc_attr( $platform . '-' . $post->post_name ) . '"' . "\n";
		}

		return '<button' . "\n" .
			'type="button"' . "\n" .
			'class="btn btn-secondary share-trigger"' . "\n" .
			'data-share-title="' . esc_attr( $title ) . '"' . "\n" .
			'data-share-url="' . esc_attr( (string) get_permalink( $post ) ) . '"' . "\n" .
			$templates .
			'>' . "\n" . self::ICON_SHARE . "\n" .
			esc_html__( 'Compartir', 'camino-del-dharma' ) . "\n" .
			'</button>';
	}

	/**
	 * The calendar payload of an event, or nothing when the domain plugin
	 * is inactive (the views degrade, they never fatal).
	 *
	 * @param WP_Post $event Event post.
	 */
	private static function calendar_payload( WP_Post $event ): array {
		return function_exists( 'cdd_core_event_calendar_payload' ) ? cdd_core_event_calendar_payload( $event ) : array();
	}

	/**
	 * The title an event is shared under: the published dialog names the
	 * event with its type first («Curso Círculos de Presencia
	 * Consciente»).
	 *
	 * @param WP_Post $event Event post.
	 */
	private static function share_title( WP_Post $event ): string {
		$type = self::event_type_name( $event );

		return '' === $type ? get_the_title( $event ) : $type . ' ' . get_the_title( $event );
	}

	/**
	 * The blog entry header: breadcrumb, title, deck, byline from the
	 * ordered authors relation (ADR 0037) and reading time.
	 *
	 * @param WP_Post $post Blog post.
	 */
	public static function entry_header( WP_Post $post ): string {
		$html = '<header>' . "\n" .
			'<p class="article-meta"><a href="' . esc_url( home_url( '/blog' ) ) . '">' . esc_html__( 'Blog', 'camino-del-dharma' ) . '</a></p>' . "\n" .
			'<h1>' . esc_html( get_the_title( $post ) ) . '</h1>' . "\n";

		if ( '' !== $post->post_excerpt ) {
			$html .= '<p class="article-deck">' . esc_html( $post->post_excerpt ) . '</p>' . "\n";
		}

		$authors = self::post_authors( $post );

		$html .= '<div class="article-byline">' . "\n";

		if ( ! empty( $authors ) ) {
			$links = array();
			foreach ( $authors as $author ) {
				$links[] = '<a href="' . esc_url( get_permalink( $author ) ) . '">' . esc_html( get_the_title( $author ) ) . '</a>';
			}
			$html .= '<p><strong>' . esc_html__( 'Por', 'camino-del-dharma' ) . ' ' . Camino_Del_Dharma_Format::name_list( $links ) . '</strong></p>' . "\n";

			foreach ( $authors as $author ) {
				$bio = trim( wp_strip_all_tags( $author->post_content ) );
				if ( '' !== $bio ) {
					$html .= '<p>' . esc_html( $bio ) . '</p>' . "\n";
				}
			}
		}

		$minutes = Camino_Del_Dharma_Format::reading_minutes( $post->post_content );
		$html   .= '<p>' . esc_html(
			sprintf(
				/* translators: %d: reading time in minutes. */
				_n( 'Tiempo de lectura: %d minuto', 'Tiempo de lectura: %d minutos', $minutes, 'camino-del-dharma' ),
				$minutes
			)
		) . '</p>' . "\n" .
			'</div>' . "\n" .
			'</header>';

		return $html;
	}

	/**
	 * The /blog listing, in the published card structure.
	 *
	 * @param array $posts Blog posts (WP_Post[]).
	 */
	public static function blog_list( array $posts ): string {
		$items = '';
		foreach ( $posts as $post ) {
			$url   = get_permalink( $post );
			$thumb = has_post_thumbnail( $post ) ? get_the_post_thumbnail( $post, 'medium' ) : '';

			$items .= '<li>' . "\n" . '<article class="blog-list-item">' . "\n" .
				'<a href="' . esc_url( $url ) . '" class="blog-list-link">' . "\n" .
				'<span class="blog-list-thumb">' . $thumb . '</span>' . "\n" .
				'<div class="blog-list-body">' . "\n" .
				'<h2 class="blog-list-title">' . esc_html( get_the_title( $post ) ) . '</h2>' . "\n" .
				'<p class="blog-list-excerpt">' . esc_html( $post->post_excerpt ) . '</p>' . "\n" .
				'</div>' . "\n" . '</a>' . "\n" . '</article>' . "\n" . '</li>' . "\n";
		}

		return '<ul class="blog-list" role="list">' . "\n" . $items . '</ul>';
	}

	/**
	 * The home «Del blog» cards: deck plus the byline voice, exactly as
	 * published («… Por Comunidad Camino del Dharma.»).
	 *
	 * @param array $posts Latest blog posts (WP_Post[]).
	 */
	public static function home_blog_cards( array $posts ): string {
		$items = '';
		foreach ( $posts as $post ) {
			$url   = get_permalink( $post );
			$thumb = has_post_thumbnail( $post ) ? get_the_post_thumbnail( $post, 'medium' ) : '';

			$excerpt = rtrim( $post->post_excerpt );
			if ( '' !== $excerpt && ! in_array( substr( $excerpt, -1 ), array( '.', '!', '?' ), true ) ) {
				$excerpt .= '.';
			}

			$names = Camino_Del_Dharma_Format::name_list( array_map( 'get_the_title', self::post_authors( $post ) ) );
			if ( '' !== $names ) {
				$excerpt = trim(
					$excerpt . ' ' . sprintf(
					/* translators: %s: author names. */
						__( 'Por %s.', 'camino-del-dharma' ),
						$names
					)
				);
			}

			$items .= '<li>' . "\n" . '<article class="home-blog-card">' . "\n" .
				'<a href="' . esc_url( $url ) . '" class="home-blog-link">' . "\n" .
				'<span class="home-blog-thumb">' . $thumb . '</span>' . "\n" .
				'<h3 class="home-blog-title">' . esc_html( get_the_title( $post ) ) . '</h3>' . "\n" .
				'<p class="home-blog-excerpt">' . esc_html( $excerpt ) . '</p>' . "\n" .
				'</a>' . "\n" . '</article>' . "\n" . '</li>' . "\n";
		}

		return '<ul class="home-blog-grid" role="list">' . "\n" . $items . '</ul>';
	}

	/**
	 * The author profile: name, bio, and the entries related through the
	 * authors meta (ADR 0037 — never post_author).
	 *
	 * @param WP_Post $author Published blog_author post.
	 * @param array   $posts  Related blog posts (WP_Post[]).
	 */
	public static function author_profile( WP_Post $author, array $posts ): string {
		$html = '<article class="autor-ficha read-width section-gap">' . "\n" .
			'<header>' . "\n" .
			'<p class="article-meta"><a href="' . esc_url( home_url( '/blog' ) ) . '">' . esc_html__( 'Blog', 'camino-del-dharma' ) . '</a></p>' . "\n" .
			'<h1>' . esc_html( get_the_title( $author ) ) . '</h1>' . "\n" .
			'</header>' . "\n";

		if ( has_post_thumbnail( $author ) ) {
			$html .= '<figure class="section-figure">' . get_the_post_thumbnail( $author, 'medium' ) . '</figure>' . "\n";
		}

		if ( '' !== trim( $author->post_content ) ) {
			$html .= wp_kses_post( wpautop( do_blocks( $author->post_content ) ) ) . "\n";
		}

		if ( ! empty( $posts ) ) {
			$html .= '<section class="autor-entradas" aria-labelledby="autor-entradas-heading">' . "\n" .
				'<h2 id="autor-entradas-heading">' . esc_html(
					sprintf(
						/* translators: %s: author name. */
						__( 'Entradas de %s', 'camino-del-dharma' ),
						get_the_title( $author )
					)
				) . '</h2>' . "\n" .
				self::blog_list( $posts ) . "\n" .
				'</section>' . "\n";
		}

		$html .= '</article>';

		return $html;
	}

	/**
	 * One album term view: back link to the hub, heading, and a native
	 * Gutenberg gallery of the term's attachments (ADR 0036 / ADR 0021).
	 *
	 * @param WP_Term $term        gallery_album term.
	 * @param array   $attachments Attachment posts of the album, in order.
	 */
	public static function album_gallery( WP_Term $term, array $attachments ): string {
		$images = '';
		foreach ( $attachments as $attachment ) {
			$src = wp_get_attachment_image_url( $attachment->ID, 'large' );
			if ( ! $src ) {
				$src = (string) wp_get_attachment_url( $attachment->ID );
			}
			$alt = (string) get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );

			$images .= '<!-- wp:image {"id":' . (int) $attachment->ID . ',"sizeSlug":"large","linkDestination":"none"} -->' .
				'<figure class="wp-block-image size-large"><img src="' . esc_url( $src ) . '" alt="' . esc_attr( $alt ) . '" class="wp-image-' . (int) $attachment->ID . '"/></figure>' .
				'<!-- /wp:image -->';
		}

		$gallery = '<!-- wp:gallery {"columns":3,"imageCrop":true,"linkTo":"none","sizeSlug":"large"} -->' .
			'<figure class="wp-block-gallery has-nested-images columns-3 is-cropped">' . $images . '</figure>' .
			'<!-- /wp:gallery -->';

		return '<p class="page-back"><a href="' . esc_url( home_url( '/galeria' ) ) . '">' . esc_html__( '← Volver a la galería', 'camino-del-dharma' ) . '</a></p>' . "\n" .
			'<h1>' . esc_html(
				sprintf(
					/* translators: %s: album title. */
					__( 'Galería · %s', 'camino-del-dharma' ),
					$term->name
				)
			) . '</h1>' . "\n" .
			do_blocks( $gallery );
	}

	/**
	 * One current event as the full published card.
	 *
	 * @param WP_Post $event Current event.
	 */
	private static function current_event_card( WP_Post $event ): string {
		$url = get_permalink( $event );

		$figure = '';
		if ( has_post_thumbnail( $event ) ) {
			$figure = '<figure class="evento-figure">' . "\n" .
				'<a href="' . esc_url( $url ) . '" class="evento-figure-link" tabindex="-1" aria-hidden="true">' .
				get_the_post_thumbnail( $event, 'full' ) . '</a>' . "\n" . '</figure>' . "\n";
		}

		return '<article id="' . esc_attr( $event->post_name ) . '" class="evento-card read-width section-gap" data-event-status="vigente">' . "\n" .
			self::event_type_label( $event ) . "\n" .
			'<h3 class="evento-title"><a href="' . esc_url( $url ) . '">' . esc_html( get_the_title( $event ) ) . '</a></h3>' . "\n" .
			$figure .
			self::event_intro( $event ) . "\n" .
			self::event_meta( $event ) . "\n" .
			'<p class="evento-detail-link"><a href="' . esc_url( $url ) . '">' . esc_html__( 'Ver evento →', 'camino-del-dharma' ) . '</a></p>' . "\n" .
			self::event_cta( $event, true ) . "\n" .
			self::event_actions( $event, true, self::calendar_payload( $event ) ) . "\n" .
			'</article>';
	}

	/**
	 * One completed event as the compact archive card (doc 03 §3
	 * «Densidad» — the documented substitution for the full static card).
	 *
	 * @param WP_Post $event Completed/cancelled event.
	 */
	private static function past_event_card( WP_Post $event ): string {
		$url = get_permalink( $event );

		$thumb = '';
		if ( has_post_thumbnail( $event ) ) {
			$thumb = '<a href="' . esc_url( $url ) . '" class="evento-compact-thumb" tabindex="-1" aria-hidden="true">' .
				get_the_post_thumbnail( $event, 'thumbnail' ) . '</a>' . "\n";
		}

		$meta_parts = array_filter(
			array(
				Camino_Del_Dharma_Format::name_list( self::event_city_names( $event ) ),
				self::event_date_display( $event ),
			)
		);

		return '<article id="' . esc_attr( $event->post_name ) . '" class="evento-card evento-card--past evento-card--compact">' . "\n" .
			'<p class="evento-badge evento-badge--finalizado" aria-hidden="true">' . esc_html__( 'Evento finalizado', 'camino-del-dharma' ) . '</p>' . "\n" .
			'<p class="visually-hidden">' . esc_html__( 'Evento finalizado', 'camino-del-dharma' ) . '</p>' . "\n" .
			'<div class="evento-compact-row">' . "\n" . $thumb .
			'<div class="evento-compact-body">' . "\n" .
			self::event_type_label( $event ) . "\n" .
			'<h4 class="evento-title"><a href="' . esc_url( $url ) . '">' . esc_html( get_the_title( $event ) ) . '</a></h4>' . "\n" .
			'<p class="evento-compact-meta">' . esc_html( implode( ' · ', $meta_parts ) ) . '</p>' . "\n" .
			'<p class="evento-detail-link"><a href="' . esc_url( $url ) . '">' . esc_html__( 'Ver evento →', 'camino-del-dharma' ) . '</a></p>' . "\n" .
			'</div>' . "\n" . '</div>' . "\n" .
			'</article>';
	}

	/**
	 * The intro of an event: its rendered content up to the first section
	 * heading (the lead and opening paragraphs of the single).
	 *
	 * @param WP_Post $event Event post.
	 */
	private static function event_intro( WP_Post $event ): string {
		$content = trim( do_blocks( $event->post_content ) );

		$cut = stripos( $content, '<h2' );
		if ( false !== $cut ) {
			$content = trim( substr( $content, 0, $cut ) );
		}

		return wp_kses_post( $content );
	}

	/**
	 * One calendar grid cell.
	 *
	 * @param int    $day            Day of the month.
	 * @param array  $cell           Cell data (events, practice).
	 * @param string $practice_url   Weekly meditation URL.
	 * @param string $practice_label Weekly meditation label.
	 */
	private static function calendar_cell( int $day, array $cell, string $practice_url, string $practice_label ): string {
		if ( ! empty( $cell['events'] ) ) {
			$event = $cell['events'][0];

			return '<span class="eventos-calendar-day has-event" role="gridcell">' .
				'<a href="' . esc_url( (string) $event['url'] ) . '" data-tooltip="' . esc_attr( (string) $event['title'] ) . '" aria-label="' . esc_attr( $day . ': ' . $event['title'] ) . '">' . $day . '</a></span>';
		}

		if ( ! empty( $cell['practice'] ) ) {
			$aria = sprintf(
				/* translators: 1: day of the month, 2: weekly practice label. */
				__( 'Lunes %1$d: %2$s', 'camino-del-dharma' ),
				$day,
				mb_strtolower( mb_substr( $practice_label, 0, 1 ) ) . mb_substr( $practice_label, 1 )
			);

			return '<span class="eventos-calendar-day has-practice" role="gridcell">' .
				'<a href="' . esc_url( $practice_url ) . '" data-tooltip="' . esc_attr( $practice_label ) . '" aria-label="' . esc_attr( $aria ) . '">' . $day . '</a></span>';
		}

		return '<span class="eventos-calendar-day" role="gridcell">' . $day . '</span>';
	}

	/**
	 * The display date of an event from its stored meta.
	 *
	 * @param WP_Post $event Event post.
	 */
	private static function event_date_display( WP_Post $event ): string {
		$start = (string) get_post_meta( $event->ID, 'event_date', true );
		if ( '' === $start ) {
			return '';
		}

		$end = (string) get_post_meta( $event->ID, 'event_end', true );

		return Camino_Del_Dharma_Format::event_date_range( $start, '' === $end ? null : $end );
	}

	/**
	 * The event_type term name of an event, or ''.
	 *
	 * @param WP_Post $event Event post.
	 */
	private static function event_type_name( WP_Post $event ): string {
		$terms = get_the_terms( $event, 'event_type' );

		return is_array( $terms ) && ! empty( $terms ) ? $terms[0]->name : '';
	}

	/**
	 * The event_city term names of an event.
	 *
	 * @param WP_Post $event Event post.
	 */
	private static function event_city_names( WP_Post $event ): array {
		$terms = get_the_terms( $event, 'event_city' );

		return is_array( $terms ) ? wp_list_pluck( $terms, 'name' ) : array();
	}

	/**
	 * The published blog_author posts related to a post, in stored order.
	 *
	 * @param WP_Post $post Blog post.
	 */
	private static function post_authors( WP_Post $post ): array {
		if ( ! function_exists( 'cdd_core_stored_authors' ) ) {
			return array();
		}

		$authors = array();
		foreach ( cdd_core_stored_authors( $post->ID ) as $author_id ) {
			$author = get_post( $author_id );
			if ( $author instanceof WP_Post && 'publish' === $author->post_status ) {
				$authors[] = $author;
			}
		}

		return $authors;
	}
}
