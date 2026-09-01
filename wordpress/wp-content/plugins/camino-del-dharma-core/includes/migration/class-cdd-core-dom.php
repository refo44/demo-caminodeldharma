<?php
/**
 * Small DOM toolkit shared by the static-HTML extractors (ADR 0032 §8.1).
 *
 * Pure domain code: no WordPress APIs. Wraps DOMDocument with UTF-8-safe
 * loading, class queries, fragment serialization and URL normalization.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DOM helpers for deterministic extraction.
 */
final class Cdd_Core_Dom {

	/**
	 * Loads an HTML document and returns an XPath over it.
	 *
	 * @param string $html Raw HTML (UTF-8).
	 */
	public static function load( string $html ): DOMXPath {
		$document = new DOMDocument();
		$document->loadHTML( '<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING );

		return new DOMXPath( $document );
	}

	/**
	 * Elements carrying a CSS class, in document order.
	 *
	 * @param DOMXPath     $xpath   Document XPath.
	 * @param string       $class_name   Single class name.
	 * @param DOMNode|null $context Optional context node.
	 */
	public static function by_class( DOMXPath $xpath, string $class_name, ?DOMNode $context = null ): array {
		$query = ".//*[contains(concat(' ', normalize-space(@class), ' '), ' " . $class_name . " ')]";
		$list  = null !== $context ? $xpath->query( $query, $context ) : $xpath->query( '/' . ltrim( $query, '.' ) );

		return iterator_to_array( $list );
	}

	/**
	 * Whether an element carries a CSS class.
	 *
	 * @param DOMNode $node  Element.
	 * @param string  $class_name Class name.
	 */
	public static function has_class( DOMNode $node, string $class_name ): bool {
		if ( ! $node instanceof DOMElement ) {
			return false;
		}

		return in_array( $class_name, preg_split( '/\s+/', trim( $node->getAttribute( 'class' ) ) ), true );
	}

	/**
	 * Whitespace-normalized text of a node.
	 *
	 * @param DOMNode $node Node.
	 */
	public static function text( DOMNode $node ): string {
		return trim( preg_replace( '/\s+/u', ' ', $node->textContent ) ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOM API property.
	}

	/**
	 * Serialized inner HTML of a node.
	 *
	 * @param DOMNode $node Node.
	 */
	public static function inner_html( DOMNode $node ): string {
		$html = '';
		foreach ( iterator_to_array( $node->childNodes ) as $child ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOM API property.
			$html .= $node->ownerDocument->saveHTML( $child ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOM API property.
		}

		return trim( $html );
	}

	/**
	 * Removes a node from its document.
	 *
	 * @param DOMNode $node Node to remove.
	 */
	public static function remove( DOMNode $node ) {
		if ( $node->parentNode ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOM API property.
			$node->parentNode->removeChild( $node ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOM API property.
		}
	}

	/**
	 * Normalizes src/href URLs in serialized HTML to root-relative form:
	 * "../assets/x" and "assets/x" become "/assets/x"; absolute URLs,
	 * fragments, mailto:/tel: and already-root-relative paths stay.
	 *
	 * @param string $html Serialized HTML fragment.
	 */
	public static function normalize_urls( string $html ): string {
		return preg_replace_callback(
			'/\b(src|href|poster)="([^"]*)"/',
			static function ( array $matches ): string {
				$url = $matches[2];
				if ( '' === $url || preg_match( '#^(https?:|//|/|\#|mailto:|tel:|data:)#', $url ) ) {
					return $matches[0];
				}
				$url = preg_replace( '#^(\.\./)+#', '', $url, 1 );

				return $matches[1] . '="/' . $url . '"';
			},
			$html
		);
	}

	/**
	 * Strips a URL to the repo-relative static path ("assets/…"): leading
	 * "../" chains and a leading slash are removed.
	 *
	 * @param string $url Attribute URL.
	 */
	public static function to_source_path( string $url ): string {
		return ltrim( preg_replace( '#^(\.\./)+#', '', $url, 1 ), '/' );
	}

	/**
	 * Every JSON-LD node of a given @type found in the document's
	 * ld+json scripts (flattening @graph containers).
	 *
	 * @param DOMXPath $xpath Document XPath.
	 * @param string   $type  Schema.org type (e.g. 'Event').
	 */
	public static function json_ld_nodes( DOMXPath $xpath, string $type ): array {
		$nodes = array();
		foreach ( $xpath->query( '//script[@type="application/ld+json"]' ) as $script ) {
			$decoded = json_decode( $script->textContent, true ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOM API property.
			if ( ! is_array( $decoded ) ) {
				continue;
			}
			$candidates = isset( $decoded['@graph'] ) ? $decoded['@graph'] : array( $decoded );
			foreach ( $candidates as $candidate ) {
				if ( is_array( $candidate ) && ( $candidate['@type'] ?? '' ) === $type ) {
					$nodes[] = $candidate;
				}
			}
		}

		return $nodes;
	}

	/**
	 * ASCII slug of a Spanish name (deterministic accent map, no
	 * transliteration extension required).
	 *
	 * @param string $name Human name.
	 */
	public static function slugify( string $name ): string {
		$map  = array(
			'á' => 'a',
			'é' => 'e',
			'í' => 'i',
			'ó' => 'o',
			'ú' => 'u',
			'ü' => 'u',
			'ñ' => 'n',
		);
		$slug = strtr( mb_strtolower( $name, 'UTF-8' ), $map );
		$slug = preg_replace( '/[^a-z0-9]+/', '-', $slug );

		return trim( $slug, '-' );
	}
}
