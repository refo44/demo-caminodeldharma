<?php
/**
 * Deterministic Page extraction from the production static HTML
 * (ADR 0034; OWN-007 — the published copy is the source).
 *
 * Pure domain code: no WordPress APIs.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extracts institutional pages and their embeds.
 */
final class Cdd_Core_Page_Extractor {

	/**
	 * Extracts one page.
	 *
	 * @param string $slug Page slug ('inicio' for the front page,
	 *                     'practica/videos' for children).
	 * @param string $html Page HTML.
	 */
	public function extract( string $slug, string $html ): array {
		$xpath = Cdd_Core_Dom::load( $html );
		$main  = $xpath->query( '//main' )->item( 0 );
		$h1    = $xpath->query( '//main//h1' )->item( 0 );

		return array(
			'slug'         => $slug,
			'title'        => null !== $h1 ? Cdd_Core_Dom::text( $h1 ) : '',
			'seo'          => ( new Cdd_Core_Seo_Extractor() )->extract( $html ),
			'content_html' => null !== $main ? $this->content_html( $main ) : '',
		);
	}

	/**
	 * The production embeds (iframes) of a page's main region.
	 *
	 * @param string $html Page HTML.
	 */
	public function extract_embeds( string $html ): array {
		$xpath = Cdd_Core_Dom::load( $html );

		$embeds = array();
		foreach ( $xpath->query( '//main//iframe' ) as $iframe ) {
			$embeds[] = array(
				'url'   => $iframe->getAttribute( 'src' ),
				'title' => $iframe->getAttribute( 'title' ),
			);
		}

		return $embeds;
	}

	/**
	 * The main region markup minus scripts/templates and share chrome,
	 * with root-relative URLs.
	 *
	 * @param DOMNode $main Main element.
	 */
	private function content_html( DOMNode $main ): string {
		$scope = $main->cloneNode( true );
		$local = new DOMXPath( $scope->ownerDocument ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOM API property.

		foreach ( array( 'script', 'template' ) as $tag ) {
			foreach ( iterator_to_array( $local->query( './/' . $tag, $scope ) ) as $node ) {
				Cdd_Core_Dom::remove( $node );
			}
		}
		foreach ( Cdd_Core_Dom::by_class( $local, 'share-actions', $scope ) as $node ) {
			Cdd_Core_Dom::remove( $node );
		}

		return Cdd_Core_Dom::normalize_urls( Cdd_Core_Dom::inner_html( $scope ) );
	}
}
