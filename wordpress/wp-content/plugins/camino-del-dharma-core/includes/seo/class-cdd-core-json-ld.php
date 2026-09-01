<?php
/**
 * schema.org graph construction (docs/15-assets-strategy.md §12.3–§12.5).
 *
 * One rule governs every builder here: **never invent an optional
 * field**. A value the model does not hold is omitted, not filled with a
 * placeholder — an absent `performer` costs nothing, an invented one is
 * a lie in structured data.
 *
 * Pure domain code: no WordPress APIs.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the JSON-LD nodes the site publishes.
 */
final class Cdd_Core_Json_Ld {

	const SCHEMA = 'https://schema.org/';

	/**
	 * Event status per the request-time editorial state (OWN-013).
	 */
	const EVENT_STATUS = array(
		'current'   => self::SCHEMA . 'EventScheduled',
		'completed' => self::SCHEMA . 'EventCompleted',
		'cancelled' => self::SCHEMA . 'EventCancelled',
	);

	/**
	 * Attendance modes. An unknown modality yields no field at all.
	 */
	const ATTENDANCE = array(
		'offline' => self::SCHEMA . 'OfflineEventAttendanceMode',
		'online'  => self::SCHEMA . 'OnlineEventAttendanceMode',
		'mixed'   => self::SCHEMA . 'MixedEventAttendanceMode',
	);

	/**
	 * An `Event` node from real event data.
	 *
	 * @param array $event Normalized event data.
	 */
	public static function event( array $event ): array {
		$state     = $event['state'] ?? 'current';
		$is_online = in_array( $event['attendance'] ?? '', array( 'online', 'mixed' ), true );

		$node = array(
			'@type'       => 'Event',
			'@id'         => ( $event['url'] ?? '' ) . '#event',
			'name'        => $event['name'] ?? '',
			'startDate'   => $event['start'] ?? '',
			'eventStatus' => self::EVENT_STATUS[ $state ] ?? self::EVENT_STATUS['current'],
			'url'         => $event['url'] ?? '',
		);

		self::set( $node, 'description', $event['description'] ?? '' );
		self::set( $node, 'endDate', $event['end'] ?? '' );
		self::set( $node, 'image', $event['image'] ?? '' );
		self::set( $node, 'eventAttendanceMode', self::ATTENDANCE[ $event['attendance'] ?? '' ] ?? '' );

		$location = array();
		if ( $is_online && '' !== ( $event['url'] ?? '' ) ) {
			$location[] = array(
				'@type' => 'VirtualLocation',
				'url'   => $event['url'],
			);
		}
		foreach ( $event['places'] ?? array() as $place ) {
			$address = array(
				'@type'           => 'PostalAddress',
				'addressLocality' => $place['name'],
			);
			self::set( $address, 'addressRegion', $place['region'] ?? '' );
			$address['addressCountry'] = 'CO';

			$location[] = array(
				'@type'   => 'Place',
				'name'    => $place['name'],
				'address' => $address,
			);
		}
		if ( array() !== $location ) {
			$node['location'] = $location;
		}

		if ( isset( $event['organizer'] ) ) {
			$node['organizer'] = $event['organizer'];
		}

		// A completed or cancelled event never advertises signup, even if
		// the form URL is still stored (master prompt §10.2).
		if ( 'current' === $state && '' !== ( $event['signup_url'] ?? '' ) ) {
			$offer = array(
				'@type'        => 'Offer',
				'availability' => self::SCHEMA . 'InStock',
				'url'          => $event['signup_url'],
			);
			if ( empty( $event['signup_payment'] ) ) {
				$offer['price']         = '0';
				$offer['priceCurrency'] = 'COP';
			}
			$node['offers'] = $offer;
		}

		return self::merge_extra( $node, $event['extra'] ?? array(), 'current' === $state );
	}

	/**
	 * A `BlogPosting` node (doc 15 §12.4). Authors are always `Thing`
	 * profiles from the `authors` relationship (ADR 0037); the publisher
	 * is always the site Organization.
	 *
	 * @param array $post Normalized entry data.
	 */
	public static function blog_posting( array $post ): array {
		$node = array(
			'@type'            => 'BlogPosting',
			'@id'              => ( $post['url'] ?? '' ) . '#article',
			'headline'         => $post['headline'] ?? '',
			'mainEntityOfPage' => $post['url'] ?? '',
			'datePublished'    => $post['published'] ?? '',
			'dateModified'     => $post['modified'] ?? '',
			'inLanguage'       => 'es-CO',
		);

		self::set( $node, 'description', $post['description'] ?? '' );
		self::set( $node, 'image', $post['image'] ?? '' );

		$authors = array();
		foreach ( $post['authors'] ?? array() as $author ) {
			$authors[] = self::thing( $author['name'], $author['url'] );
		}
		if ( array() !== $authors ) {
			$node['author'] = $authors;
		}

		if ( isset( $post['publisher'] ) ) {
			$node['publisher'] = $post['publisher'];
		}
		if ( array() !== ( $post['tags'] ?? array() ) ) {
			$node['keywords'] = array_values( $post['tags'] );
		}

		return $node;
	}

	/**
	 * A `Thing` node for an author profile (§9.5): its name and its
	 * canonical profile URL, nothing else.
	 *
	 * @param string $name Profile name.
	 * @param string $url  Canonical profile URL.
	 */
	public static function thing( string $name, string $url ): array {
		return array(
			'@type' => 'Thing',
			'@id'   => $url,
			'name'  => $name,
			'url'   => $url,
		);
	}

	/**
	 * A `BreadcrumbList` node.
	 *
	 * @param array $items Ordered list of name/url pairs.
	 */
	public static function breadcrumbs( array $items ): array {
		$elements = array();
		$position = 1;

		foreach ( $items as $item ) {
			$elements[] = array(
				'@type'    => 'ListItem',
				'position' => $position,
				'name'     => $item['name'],
				'item'     => $item['url'],
			);
			++$position;
		}

		return array(
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $elements,
		);
	}

	/**
	 * Rewrites the production base URL of stored graph data onto the URL
	 * of the environment actually serving it: staging must never publish
	 * caminodeldharma.org as its own identity.
	 *
	 * @param mixed  $value Graph node, list or scalar.
	 * @param string $from  Stored base URL.
	 * @param string $to    Environment base URL.
	 */
	public static function rebase( $value, string $from, string $to ) {
		if ( is_array( $value ) ) {
			return array_map(
				static function ( $item ) use ( $from, $to ) {
					return self::rebase( $item, $from, $to );
				},
				$value
			);
		}

		if ( is_string( $value ) && 0 === strpos( $value, $from ) ) {
			return $to . substr( $value, strlen( $from ) );
		}

		return $value;
	}

	/**
	 * Merges the stored optional fields under a generated node: a
	 * generated key always wins, and a stored offer never resurrects on
	 * an event that is no longer current.
	 *
	 * @param array $node       Generated node.
	 * @param array $extra      Stored optional fields.
	 * @param bool  $is_current Whether the event is still current.
	 */
	private static function merge_extra( array $node, array $extra, bool $is_current ): array {
		if ( ! $is_current ) {
			unset( $extra['offers'] );
		}

		// The offer is the one field built from both sides: the live
		// signup URL and availability, plus the published price and
		// opening date. Generated keys still win, key by key.
		if ( isset( $extra['offers'], $node['offers'] ) ) {
			$node['offers'] = array_merge( $extra['offers'], $node['offers'] );
			unset( $extra['offers'] );
		}

		return array_merge( $extra, $node );
	}

	/**
	 * Sets a key only when the value is a non-empty string.
	 *
	 * @param array  $node  Node under construction.
	 * @param string $key   Key.
	 * @param string $value Candidate value.
	 */
	private static function set( array &$node, string $key, string $value ) {
		if ( '' !== $value ) {
			$node[ $key ] = $value;
		}
	}
}
