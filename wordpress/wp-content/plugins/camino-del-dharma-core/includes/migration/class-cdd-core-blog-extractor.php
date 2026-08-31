<?php
/**
 * Deterministic blog extraction from the two production posts
 * (ADR 0034/0037). Bylines seed the blog_author profiles; the hero
 * figure becomes the featured image.
 *
 * Pure domain code: no WordPress APIs.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extracts the production posts with their bylines.
 */
final class Cdd_Core_Blog_Extractor {

	/**
	 * Extracts every post, keyed input slug => post HTML.
	 *
	 * @param array $sources Map slug => post page HTML.
	 */
	public function extract( array $sources ): array {
		$posts = array();
		foreach ( $sources as $slug => $html ) {
			$posts[] = $this->extract_post( (string) $slug, $html );
		}

		return $posts;
	}

	/**
	 * Extracts one post.
	 *
	 * @param string $slug Post slug (from its directory).
	 * @param string $html Post page HTML.
	 */
	private function extract_post( string $slug, string $html ): array {
		$xpath   = Cdd_Core_Dom::load( $html );
		$article = $xpath->query( '//main//article' )->item( 0 );
		$byline  = $this->byline( $xpath, $article );
		$hero    = $this->hero( $xpath, $article );

		$deck = Cdd_Core_Dom::by_class( $xpath, 'article-deck', $article );
		$meta = $xpath->query( '//meta[@name="description"]' )->item( 0 );

		return array(
			'slug'             => $slug,
			'title'            => Cdd_Core_Dom::text( $xpath->query( './/h1', $article )->item( 0 ) ),
			'deck'             => empty( $deck ) ? '' : Cdd_Core_Dom::text( $deck[0] ),
			'date'             => $this->published_date( $xpath ),
			'author_name'      => $byline['name'],
			'author_slug'      => Cdd_Core_Dom::slugify( $byline['name'] ),
			'author_bio'       => $byline['bio'],
			'thumbnail'        => $hero['file'],
			'thumbnail_alt'    => $hero['alt'],
			'meta_description' => $meta instanceof DOMElement ? $meta->getAttribute( 'content' ) : '',
			'content_html'     => $this->content_html( $article ),
			'share'            => ( new Cdd_Core_Share_Extractor() )->extract( $xpath ),
			'tags'             => array(),
		);
	}

	/**
	 * The byline: author name from the "Por …" line, biography from the
	 * remaining byline paragraphs (the reading-time line is not one).
	 *
	 * @param DOMXPath   $xpath   Document XPath.
	 * @param DOMElement $article Article element.
	 */
	private function byline( DOMXPath $xpath, DOMElement $article ): array {
		$name = '';
		$bio  = array();
		$box  = Cdd_Core_Dom::by_class( $xpath, 'article-byline', $article );
		if ( ! empty( $box ) ) {
			foreach ( $xpath->query( './p', $box[0] ) as $paragraph ) {
				$text = Cdd_Core_Dom::text( $paragraph );
				if ( 0 === strpos( $text, 'Por ' ) ) {
					$name = substr( $text, 4 );
					continue;
				}
				if ( 0 === strpos( $text, 'Tiempo de lectura' ) ) {
					continue;
				}
				$bio[] = $text;
			}
		}

		return array(
			'name' => $name,
			'bio'  => implode( ' ', $bio ),
		);
	}

	/**
	 * The hero figure image as the future featured image.
	 *
	 * @param DOMXPath   $xpath   Document XPath.
	 * @param DOMElement $article Article element.
	 */
	private function hero( DOMXPath $xpath, DOMElement $article ): array {
		$image = $xpath->query( './/figure//img', $article )->item( 0 );

		return array(
			'file' => $image instanceof DOMElement ? Cdd_Core_Dom::to_source_path( $image->getAttribute( 'src' ) ) : '',
			'alt'  => $image instanceof DOMElement ? $image->getAttribute( 'alt' ) : '',
		);
	}

	/**
	 * The machine publication date from the page's JSON-LD.
	 *
	 * @param DOMXPath $xpath Document XPath.
	 */
	private function published_date( DOMXPath $xpath ): string {
		foreach ( array( 'BlogPosting', 'Article', 'NewsArticle' ) as $type ) {
			foreach ( Cdd_Core_Dom::json_ld_nodes( $xpath, $type ) as $node ) {
				if ( isset( $node['datePublished'] ) ) {
					return substr( $node['datePublished'], 0, 10 );
				}
			}
		}

		return '';
	}

	/**
	 * Post content: the article minus header, hero figure, share chrome
	 * and the trailing blog-navigation paragraph, with root-relative
	 * URLs.
	 *
	 * @param DOMElement $article Article element.
	 */
	private function content_html( DOMElement $article ): string {
		$scope = $article->cloneNode( true );
		$local = new DOMXPath( $scope->ownerDocument ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOM API property.

		foreach ( array( 'header', 'template' ) as $tag ) {
			foreach ( iterator_to_array( $local->query( './/' . $tag, $scope ) ) as $node ) {
				Cdd_Core_Dom::remove( $node );
			}
		}
		foreach ( Cdd_Core_Dom::by_class( $local, 'share-actions', $scope ) as $node ) {
			Cdd_Core_Dom::remove( $node );
		}
		$first_figure = $local->query( './/figure', $scope )->item( 0 );
		if ( null !== $first_figure ) {
			Cdd_Core_Dom::remove( $first_figure );
		}
		foreach ( iterator_to_array( $local->query( './/p[.//a[@href="/blog"]]', $scope ) ) as $node ) {
			Cdd_Core_Dom::remove( $node );
		}

		return Cdd_Core_Dom::normalize_urls( Cdd_Core_Dom::inner_html( $scope ) );
	}
}
