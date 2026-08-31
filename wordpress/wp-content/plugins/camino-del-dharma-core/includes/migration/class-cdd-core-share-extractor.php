<?php
/**
 * Extraction of the published share message templates (WU-08A).
 *
 * The static site hand-writes one WhatsApp/X/Threads message per event
 * and per entry inside <template> elements, referenced by the share
 * trigger through data-share-*-template ids (static/assets/js/share.js).
 * That copy is production content (ADR 0034): it travels as data instead
 * of being regenerated, and lands in WordPress as editable meta.
 *
 * Pure domain code: no WordPress APIs.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads the share templates a document publishes for one item.
 */
final class Cdd_Core_Share_Extractor {

	/**
	 * Platforms, in the published dialog order.
	 */
	const PLATFORMS = array( 'whatsapp', 'x', 'threads' );

	/**
	 * The share copy referenced by the share trigger inside a context, or
	 * empty strings when the surface publishes no share control.
	 *
	 * @param DOMXPath        $document Document holding the templates.
	 * @param DOMElement|null $context  Element containing the trigger
	 *                                  (null = the whole document).
	 */
	public function extract( DOMXPath $document, ?DOMElement $context = null ): array {
		$share = array_fill_keys( self::PLATFORMS, '' );

		$trigger = $this->trigger( $document, $context );
		if ( null === $trigger ) {
			return $share;
		}

		foreach ( self::PLATFORMS as $platform ) {
			$template_id = $trigger->getAttribute( 'data-share-' . $platform . '-template' );
			if ( '' === $template_id ) {
				continue;
			}
			$share[ $platform ] = $this->template_text( $document, $template_id );
		}

		return $share;
	}

	/**
	 * Whether a surface publishes any share copy at all.
	 *
	 * @param array $share Extracted share templates.
	 */
	public static function is_empty( array $share ): bool {
		return '' === implode( '', $share );
	}

	/**
	 * The share trigger element, or null.
	 *
	 * @param DOMXPath        $document Document XPath.
	 * @param DOMElement|null $context  Optional context element.
	 */
	private function trigger( DOMXPath $document, ?DOMElement $context ): ?DOMElement {
		$query    = './/*[@data-share-title]';
		$triggers = null !== $context ? $document->query( $query, $context ) : $document->query( '//*[@data-share-title]' );

		foreach ( $triggers as $trigger ) {
			if ( $trigger instanceof DOMElement ) {
				return $trigger;
			}
		}

		return null;
	}

	/**
	 * The normalized text of one <template>, exactly as share.js reads it:
	 * every line trimmed, runs of blank lines collapsed to one, the whole
	 * message trimmed. The {{SHARE_URL}} placeholder is left in place —
	 * the dialog injects the real URL at click time.
	 *
	 * @param DOMXPath $document    Document XPath.
	 * @param string   $template_id Template element id.
	 */
	private function template_text( DOMXPath $document, string $template_id ): string {
		$nodes = $document->query( '//template[@id="' . $template_id . '"]' );
		if ( 0 === $nodes->length ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOM API property.
			return '';
		}

		$lines = array_map( 'trim', explode( "\n", $nodes->item( 0 )->textContent ) ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOM API property.

		return trim( preg_replace( "/\n{3,}/", "\n\n", implode( "\n", $lines ) ) );
	}
}
