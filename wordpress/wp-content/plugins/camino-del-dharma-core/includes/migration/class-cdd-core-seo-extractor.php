<?php
/**
 * Deterministic extraction of the published `<head>` SEO surface
 * (ADR 0034; OWN-007 — the published copy is the source).
 *
 * Titles, descriptions, keywords and Open Graph copy are hand-written
 * production content, not text a generator can re-derive from a post
 * title, so they travel through the payload like the share copy of
 * WU-08A. The home JSON-LD `@graph` travels verbatim: founding date,
 * telephone and `sameAs` are institutional data, not markup.
 *
 * Pure domain code: no WordPress APIs.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extracts head metadata from the production static HTML.
 */
final class Cdd_Core_Seo_Extractor {

	/**
	 * The empty SEO object: every key always present, never invented.
	 */
	const EMPTY_SEO = array(
		'title'          => '',
		'description'    => '',
		'keywords'       => '',
		'og_title'       => '',
		'og_description' => '',
		'related'        => '',
	);

	/**
	 * The head SEO object of one published document.
	 *
	 * @param string $html Document HTML.
	 */
	public function extract( string $html ): array {
		$xpath = Cdd_Core_Dom::load( $html );
		$title = $xpath->query( '//head/title' )->item( 0 );

		return array(
			'title'          => null !== $title ? Cdd_Core_Dom::text( $title ) : '',
			'description'    => $this->meta( $xpath, 'name', 'description' ),
			'keywords'       => $this->meta( $xpath, 'name', 'keywords' ),
			'og_title'       => $this->meta( $xpath, 'property', 'og:title' ),
			'og_description' => $this->meta( $xpath, 'property', 'og:description' ),
			'related'        => $this->link( $xpath, 'related' ),
		);
	}

	/**
	 * Site-wide SEO data: the social defaults every page repeats, the
	 * head of each CPT archive (which has no Page to hang on) and the
	 * published home `@graph`.
	 *
	 * @param string $home_html Home page HTML.
	 * @param array  $archives  Map archive id => archive HTML.
	 */
	public function extract_site( string $home_html, array $archives ): array {
		$xpath = Cdd_Core_Dom::load( $home_html );

		$archive_seo = array();
		foreach ( $archives as $archive_id => $archive_html ) {
			$archive_seo[ $archive_id ] = $this->extract( $archive_html );
		}

		return array(
			'seo'    => array(
				'base'         => rtrim( $this->meta( $xpath, 'property', 'og:url' ), '/' ),
				'site_name'    => $this->meta( $xpath, 'property', 'og:site_name' ),
				'locale'       => $this->meta( $xpath, 'property', 'og:locale' ),
				'image'        => $this->meta( $xpath, 'property', 'og:image' ),
				'image_alt'    => $this->meta( $xpath, 'property', 'og:image:alt' ),
				'image_width'  => $this->meta( $xpath, 'property', 'og:image:width' ),
				'image_height' => $this->meta( $xpath, 'property', 'og:image:height' ),
				'twitter_card' => $this->meta( $xpath, 'name', 'twitter:card' ),
				'archives'     => $archive_seo,
			),
			'jsonld' => array(
				'home_graph' => $this->graph( $xpath ),
			),
		);
	}

	/**
	 * The `addressRegion` each city publishes in its event JSON-LD. The
	 * region is real institutional data that no WordPress field carries,
	 * so it becomes term metadata of `event_city` instead of being
	 * invented at render time.
	 *
	 * @param array $documents Event single HTML documents.
	 */
	public function extract_city_regions( array $documents ): array {
		$regions = array();

		foreach ( $documents as $html ) {
			foreach ( $this->graph( Cdd_Core_Dom::load( $html ) ) as $node ) {
				foreach ( $this->places( $node['location'] ?? array() ) as $place ) {
					$locality = $place['address']['addressLocality'] ?? '';
					$region   = $place['address']['addressRegion'] ?? '';
					if ( '' !== $locality && '' !== $region ) {
						$regions[ $locality ] = $region;
					}
				}
			}
		}

		ksort( $regions );

		return $regions;
	}

	/**
	 * The optional JSON-LD fields of one published `Event` that
	 * WordPress cannot re-derive from its own model (course type,
	 * alternate name, audience, facilitator, related entry, published
	 * price). They travel as editable data merged *under* the generated
	 * node, so a generated field always wins and nothing goes stale.
	 *
	 * @param string $html Event single HTML.
	 */
	public function extract_event_extra( string $html ): array {
		$carried = array( 'additionalType', 'alternateName', 'audience', 'performer', 'subjectOf', 'offers' );

		foreach ( $this->graph( Cdd_Core_Dom::load( $html ) ) as $node ) {
			if ( 'Event' !== ( $node['@type'] ?? '' ) ) {
				continue;
			}

			$extra = array_intersect_key( $node, array_flip( $carried ) );
			if ( isset( $extra['offers'] ) ) {
				// The signup URL and availability are live model data; only
				// the published price and opening date travel.
				$extra['offers'] = array_intersect_key(
					$extra['offers'],
					array_flip( array( '@type', 'price', 'priceCurrency', 'validFrom' ) )
				);
			}

			return $extra;
		}

		return array();
	}

	/**
	 * Flattens a JSON-LD `location` value into its Place nodes.
	 *
	 * @param mixed $location Location value.
	 */
	private function places( $location ): array {
		if ( ! is_array( $location ) ) {
			return array();
		}
		$nodes = isset( $location['@type'] ) ? array( $location ) : $location;

		return array_filter(
			$nodes,
			static function ( $node ): bool {
				return is_array( $node ) && 'Place' === ( $node['@type'] ?? '' );
			}
		);
	}

	/**
	 * The `@graph` of the document's JSON-LD block, or the single node it
	 * publishes, as a list.
	 *
	 * @param DOMXPath $xpath Document XPath.
	 */
	private function graph( DOMXPath $xpath ): array {
		$script = $xpath->query( '//script[@type="application/ld+json"]' )->item( 0 );
		if ( null === $script ) {
			return array();
		}

		$decoded = json_decode( $script->textContent, true ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOM API property.
		if ( ! is_array( $decoded ) ) {
			return array();
		}

		return isset( $decoded['@graph'] ) ? $decoded['@graph'] : array( $decoded );
	}

	/**
	 * The content of one meta tag.
	 *
	 * @param DOMXPath $xpath     Document XPath.
	 * @param string   $attribute Identifying attribute (name/property).
	 * @param string   $value     Identifying value.
	 */
	private function meta( DOMXPath $xpath, string $attribute, string $value ): string {
		$node = $xpath->query( '//head/meta[@' . $attribute . '="' . $value . '"]' )->item( 0 );

		return $node instanceof DOMElement ? $node->getAttribute( 'content' ) : '';
	}

	/**
	 * The href of one link relation.
	 *
	 * @param DOMXPath $xpath Document XPath.
	 * @param string   $rel   Link relation.
	 */
	private function link( DOMXPath $xpath, string $rel ): string {
		$node = $xpath->query( '//head/link[@rel="' . $rel . '"]' )->item( 0 );

		return $node instanceof DOMElement ? $node->getAttribute( 'href' ) : '';
	}
}
