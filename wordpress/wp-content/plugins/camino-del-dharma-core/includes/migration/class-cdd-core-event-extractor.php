<?php
/**
 * Deterministic event extraction from the production static HTML
 * (ADR 0034/0035, OWN-004). Sources, in precedence order per field:
 * the event single (when it exists), the listing card, and the JSON-LD
 * already published for listing-only events.
 *
 * Pure domain code: no WordPress APIs.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extracts the 10 production events with the ADR 0035 approved slugs.
 */
final class Cdd_Core_Event_Extractor {

	/**
	 * Approved slugs for the listing-only cards, keyed by poster file
	 * name (ADR 0035 — never invent slugs; changing one requires a
	 * ledger update).
	 */
	const POSTER_SLUGS = array(
		'evento-meditacion-presencial-barranquilla-jul-2026.jpeg' => 'meditacion-presencial-barranquilla',
		'evento-festival-calma-en-la-ciudad-jun-2026.jpeg' => 'festival-calma-en-la-ciudad',
		'evento-taller-pausa-profunda-medellin-may-2026.jpeg' => 'pausa-profunda-medellin',
		'evento-uniremington-ansiedad-agotamiento-may-2026.jpeg' => 'ansiedad-agotamiento-crisis-de-atencion',
		'evento-vesak-2026-bogota.jpeg'                    => 'vesak-2026',
		'evento-buddhismo-tiempos-cansancio.jpeg'          => 'buddhismo-tiempos-cansancio',
		'evento-6-encuentro-nacional.jpeg'                 => '6-encuentro-nacional-2025',
	);

	const CANONICAL_BASE = 'https://caminodeldharma.org/eventos/';

	/**
	 * Card/single chrome classes that never travel into event content.
	 */
	const CHROME_CLASSES = array(
		'evento-badge',
		'evento-type',
		'evento-title',
		'single-event-title',
		'evento-figure',
		'evento-meta',
		'evento-detail-link',
		'evento-cta',
		'evento-actions',
		'share-actions',
		'single-event-nav',
	);

	/**
	 * Extracts the events from the listing plus the existing singles.
	 *
	 * @param string $listing_html    eventos/index.html content.
	 * @param array  $singles_by_slug Map slug => single page HTML.
	 */
	public function extract( string $listing_html, array $singles_by_slug = array() ): array {
		$listing = Cdd_Core_Dom::load( $listing_html );
		$json_ld = $this->json_ld_by_name( $listing );

		$events = array();
		foreach ( Cdd_Core_Dom::by_class( $listing, 'evento-card' ) as $card ) {
			$events[] = $this->extract_card( $listing, $card, $json_ld, $singles_by_slug );
		}

		return $events;
	}

	/**
	 * Extracts one listing card, merged with its single when it exists.
	 *
	 * @param DOMXPath   $listing         Listing XPath.
	 * @param DOMElement $card            Card element.
	 * @param array      $json_ld         Listing JSON-LD Events by name.
	 * @param array      $singles_by_slug Map slug => single page HTML.
	 */
	private function extract_card( DOMXPath $listing, DOMElement $card, array $json_ld, array $singles_by_slug ): array {
		$title_node = Cdd_Core_Dom::by_class( $listing, 'evento-title', $card )[0];
		$title      = Cdd_Core_Dom::text( $title_node );
		$poster     = $this->poster( $listing, $card );
		$slug       = $this->slug( $listing, $title_node, $poster['file'] );
		$meta       = $this->meta_list( $listing, $card );
		$structured = $json_ld[ $title ] ?? null;

		$single = null;
		if ( isset( $singles_by_slug[ $slug ] ) ) {
			$single        = Cdd_Core_Dom::load( $singles_by_slug[ $slug ] );
			$single_events = Cdd_Core_Dom::json_ld_nodes( $single, 'Event' );
			if ( ! empty( $single_events ) ) {
				$structured = $single_events[0];
			}
		}

		$dates = $this->dates( $structured, $meta['Fecha'] ?? '' );

		return array(
			'slug'              => $slug,
			'title'             => $title,
			'type'              => $this->first_class_text( $listing, 'evento-type', $card ),
			'status'            => '' !== $card->getAttribute( 'data-event-status' ) ? $card->getAttribute( 'data-event-status' ) : 'finalizado',
			'featured'          => 'true' === $card->getAttribute( 'data-event-featured' ),
			'start'             => $dates['start'],
			'end'               => $dates['end'],
			'place'             => $meta['Lugar'] ?? '',
			'modality'          => $meta['Modalidad'] ?? '',
			'cities'            => $this->cities( $structured, $listing, $card ),
			'signup_url'        => $this->signup_url( $listing, $card ),
			'poster'            => $poster['file'],
			'poster_alt'        => $poster['alt'],
			'excerpt'           => $this->excerpt( $listing, $card, $slug ),
			'content_html'      => $this->content_html( $single, $listing, $card ),
			'calendar_dates'    => $this->calendar_dates( $single ),
			'share'             => $this->share( $single, $listing, $card ),
			'seo'               => $this->seo( $singles_by_slug[ $slug ] ?? '' ),
			'attendance_mode'   => $this->attendance_mode( $structured, null !== $single ),
			'jsonld_extra'      => '' !== ( $singles_by_slug[ $slug ] ?? '' )
				? ( new Cdd_Core_Seo_Extractor() )->extract_event_extra( $singles_by_slug[ $slug ] )
				: array(),
			'has_single_source' => null !== $single,
		);
	}

	/**
	 * The published head SEO of one event (WU-08B). Only the three events
	 * with a real single have one; the ADR 0035 events that live as
	 * listing cards carry the empty object, and WordPress falls back to
	 * their own title instead of inventing head copy.
	 *
	 * @param string $single_html Single page HTML, or '' when there is none.
	 */
	private function seo( string $single_html ): array {
		return '' !== $single_html
			? ( new Cdd_Core_Seo_Extractor() )->extract( $single_html )
			: Cdd_Core_Seo_Extractor::EMPTY_SEO;
	}

	/**
	 * The schema.org attendance mode published in the single's JSON-LD.
	 * The free-text modality is editorial copy (OWN-007) and is never
	 * parsed into a machine value: an unknown mode stays empty and the
	 * JSON-LD omits the field.
	 *
	 * @param array|null $structured Structured Event node, when present.
	 * @param bool       $has_single Whether the node came from a single.
	 */
	private function attendance_mode( ?array $structured, bool $has_single ): string {
		if ( ! $has_single || null === $structured ) {
			return '';
		}

		$modes = array(
			'https://schema.org/OfflineEventAttendanceMode' => 'offline',
			'https://schema.org/OnlineEventAttendanceMode' => 'online',
			'https://schema.org/MixedEventAttendanceMode'  => 'mixed',
		);

		return $modes[ $structured['eventAttendanceMode'] ?? '' ] ?? '';
	}

	/**
	 * The published share copy of one event (WU-08A): the single wins when
	 * it publishes a share control, else the listing card. Events for
	 * which production publishes no share control carry empty copy — the
	 * dialog falls back to title + URL, and nothing is invented.
	 *
	 * @param DOMXPath|null $single  Single page XPath, when it exists.
	 * @param DOMXPath      $listing Listing XPath.
	 * @param DOMElement    $card    Listing card element.
	 */
	private function share( ?DOMXPath $single, DOMXPath $listing, DOMElement $card ): array {
		$extractor = new Cdd_Core_Share_Extractor();

		if ( null !== $single ) {
			$from_single = $extractor->extract( $single );
			if ( ! Cdd_Core_Share_Extractor::is_empty( $from_single ) ) {
				return $from_single;
			}
		}

		return $extractor->extract( $listing, $card );
	}

	/**
	 * Listing JSON-LD Events keyed by their name.
	 *
	 * @param DOMXPath $listing Listing XPath.
	 */
	private function json_ld_by_name( DOMXPath $listing ): array {
		$by_name = array();
		foreach ( Cdd_Core_Dom::json_ld_nodes( $listing, 'Event' ) as $node ) {
			$by_name[ $node['name'] ?? '' ] = $node;
		}

		return $by_name;
	}

	/**
	 * The card slug: the single link when the card has one, otherwise the
	 * approved-slug map keyed by poster file name.
	 *
	 * @param DOMXPath   $listing     Listing XPath.
	 * @param DOMElement $title_node  Card title element.
	 * @param string     $poster_file Poster source path.
	 */
	private function slug( DOMXPath $listing, DOMElement $title_node, string $poster_file ): string {
		$link = $listing->query( './/a', $title_node )->item( 0 );
		if ( $link instanceof DOMElement ) {
			return basename( $link->getAttribute( 'href' ) );
		}

		$basename = basename( $poster_file );
		if ( ! isset( self::POSTER_SLUGS[ $basename ] ) ) {
			throw new RuntimeException( "No approved slug for poster {$basename} (ADR 0035)." ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- extraction-time failure surfaced on the CLI, never in HTML.
		}

		return self::POSTER_SLUGS[ $basename ];
	}

	/**
	 * Poster path (repo-relative) and alt text.
	 *
	 * @param DOMXPath   $listing Listing XPath.
	 * @param DOMElement $card    Card element.
	 */
	private function poster( DOMXPath $listing, DOMElement $card ): array {
		$image = $listing->query( './/figure//img', $card )->item( 0 );

		return array(
			'file' => $image instanceof DOMElement ? Cdd_Core_Dom::to_source_path( $image->getAttribute( 'src' ) ) : '',
			'alt'  => $image instanceof DOMElement ? $image->getAttribute( 'alt' ) : '',
		);
	}

	/**
	 * The dt => dd map of the card meta list.
	 *
	 * @param DOMXPath   $listing Listing XPath.
	 * @param DOMElement $card    Card element.
	 */
	private function meta_list( DOMXPath $listing, DOMElement $card ): array {
		$meta = array();
		foreach ( $listing->query( './/dl[contains(@class,"evento-meta")]/dt', $card ) as $dt ) {
			$dd = $dt->nextElementSibling; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOM API property.
			if ( $dd instanceof DOMElement ) {
				$meta[ Cdd_Core_Dom::text( $dt ) ] = Cdd_Core_Dom::text( $dd );
			}
		}

		return $meta;
	}

	/**
	 * Machine dates: JSON-LD first (already published structured data),
	 * else the Spanish Fecha text.
	 *
	 * @param array|null $structured JSON-LD Event node.
	 * @param string     $fecha_text Visible Fecha text.
	 */
	private function dates( ?array $structured, string $fecha_text ): array {
		if ( null !== $structured && isset( $structured['startDate'] ) ) {
			$start = substr( $structured['startDate'], 0, 10 );
			$end   = isset( $structured['endDate'] ) ? substr( $structured['endDate'], 0, 10 ) : null;

			return array(
				'start' => $start,
				'end'   => ( null !== $end && $end !== $start ) ? $end : null,
			);
		}

		$parsed = Cdd_Core_Spanish_Date::parse_range( $fecha_text );

		return null !== $parsed ? $parsed : array(
			'start' => null,
			'end'   => null,
		);
	}

	/**
	 * City terms: JSON-LD addressLocality values when published, else the
	 * first span of the Lugar cell split on " y ".
	 *
	 * @param array|null $structured JSON-LD Event node.
	 * @param DOMXPath   $listing    Listing XPath.
	 * @param DOMElement $card       Card element.
	 */
	private function cities( ?array $structured, DOMXPath $listing, DOMElement $card ): array {
		if ( null !== $structured && isset( $structured['location'] ) ) {
			$cities    = array();
			$locations = isset( $structured['location'][0] ) ? $structured['location'] : array( $structured['location'] );
			foreach ( $locations as $location ) {
				$locality = $location['address']['addressLocality'] ?? null;
				if ( null !== $locality && ! in_array( $locality, $cities, true ) ) {
					$cities[] = $locality;
				}
			}
			if ( ! empty( $cities ) ) {
				return $cities;
			}
		}

		$span = $listing->query( './/dl[contains(@class,"evento-meta")]/dd/span[1]', $card )->item( 0 );
		if ( ! $span instanceof DOMElement ) {
			return array();
		}

		$lugar_dt = null;
		foreach ( $listing->query( './/dl[contains(@class,"evento-meta")]/dt', $card ) as $dt ) {
			if ( 'Lugar' === Cdd_Core_Dom::text( $dt ) ) {
				$lugar_dt = $dt;
				break;
			}
		}
		if ( null === $lugar_dt ) {
			return array();
		}
		$dd         = $lugar_dt->nextElementSibling; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOM API property.
		$first_span = $dd instanceof DOMElement ? $listing->query( './span[1]', $dd )->item( 0 ) : null;
		if ( ! $first_span instanceof DOMElement ) {
			return array();
		}

		return array_values( array_filter( array_map( 'trim', explode( ' y ', Cdd_Core_Dom::text( $first_span ) ) ) ) );
	}

	/**
	 * The real signup URL of a card CTA, when the card offers one.
	 *
	 * @param DOMXPath   $listing Listing XPath.
	 * @param DOMElement $card    Card element.
	 */
	private function signup_url( DOMXPath $listing, DOMElement $card ): ?string {
		$cta = $listing->query( './/p[contains(@class,"evento-cta")]//a', $card )->item( 0 );

		return $cta instanceof DOMElement ? $cta->getAttribute( 'href' ) : null;
	}

	/**
	 * Editorial excerpt: the production calendar description (with the
	 * {{EVENT_URL}} placeholder resolved) for events that offer the
	 * calendar control, else the card lead.
	 *
	 * @param DOMXPath   $listing Listing XPath.
	 * @param DOMElement $card    Card element.
	 * @param string     $slug    Approved slug.
	 */
	private function excerpt( DOMXPath $listing, DOMElement $card, string $slug ): string {
		$trigger = Cdd_Core_Dom::by_class( $listing, 'calendar-trigger', $card );
		if ( ! empty( $trigger ) ) {
			$description = $trigger[0]->getAttribute( 'data-calendar-description' );
			if ( '' !== $description ) {
				return str_replace( '{{EVENT_URL}}', self::CANONICAL_BASE . $slug, $description );
			}
		}

		return $this->first_class_text( $listing, 'evento-lead', $card );
	}

	/**
	 * Event content: the single's article when it exists (richer copy),
	 * else the card — both minus chrome, with root-relative URLs.
	 *
	 * @param DOMXPath|null $single  Single page XPath, when the event has one.
	 * @param DOMXPath      $listing Listing XPath.
	 * @param DOMElement    $card    Card element.
	 */
	private function content_html( ?DOMXPath $single, DOMXPath $listing, DOMElement $card ): string {
		if ( null !== $single ) {
			$article = $single->query( '//main//article' )->item( 0 );
			if ( $article instanceof DOMElement ) {
				return $this->clean_article( $single, $article );
			}
		}

		return $this->clean_article( $listing, $card );
	}

	/**
	 * Removes chrome from an event article and serializes what remains.
	 *
	 * @param DOMXPath   $xpath   Owning document XPath.
	 * @param DOMElement $article Article/card element.
	 */
	private function clean_article( DOMXPath $xpath, DOMElement $article ): string {
		$scope = $article->cloneNode( true );
		$local = new DOMXPath( $scope->ownerDocument ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOM API property.

		foreach ( self::CHROME_CLASSES as $class ) {
			foreach ( Cdd_Core_Dom::by_class( $local, $class, $scope ) as $node ) {
				Cdd_Core_Dom::remove( $node );
			}
		}
		foreach ( array( 'template', 'h1', 'nav', 'figure' ) as $tag ) {
			foreach ( iterator_to_array( $local->query( './/' . $tag, $scope ) ) as $node ) {
				Cdd_Core_Dom::remove( $node );
			}
		}
		foreach ( iterator_to_array( $local->query( './/dl', $scope ) ) as $node ) {
			Cdd_Core_Dom::remove( $node );
		}
		foreach ( iterator_to_array( $local->query( './/p[contains(concat(" ", normalize-space(@class), " "), " visually-hidden ")]', $scope ) ) as $node ) {
			Cdd_Core_Dom::remove( $node );
		}

		return Cdd_Core_Dom::normalize_urls( Cdd_Core_Dom::inner_html( $scope ) );
	}

	/**
	 * Explicit session dates from the single's Cronograma list (the
	 * published calendar marks sessions, not a contiguous range).
	 *
	 * @param DOMXPath|null $single Single page XPath.
	 */
	private function calendar_dates( ?DOMXPath $single ): array {
		if ( null === $single ) {
			return array();
		}

		$dates = array();
		foreach ( $single->query( '//main//h2[normalize-space(text())="Cronograma"]/following-sibling::ul[1]/li' ) as $item ) {
			$date = Cdd_Core_Spanish_Date::first_date( Cdd_Core_Dom::text( $item ) );
			if ( null !== $date ) {
				$dates[] = $date;
			}
		}

		return $dates;
	}

	/**
	 * Text of the first element with a class inside a card.
	 *
	 * @param DOMXPath   $listing Listing XPath.
	 * @param string     $class_name Class name.
	 * @param DOMElement $card    Card element.
	 */
	private function first_class_text( DOMXPath $listing, string $class_name, DOMElement $card ): string {
		$nodes = Cdd_Core_Dom::by_class( $listing, $class_name, $card );

		return empty( $nodes ) ? '' : Cdd_Core_Dom::text( $nodes[0] );
	}
}
