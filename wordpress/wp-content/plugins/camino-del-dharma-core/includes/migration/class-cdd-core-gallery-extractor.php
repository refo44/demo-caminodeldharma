<?php
/**
 * Deterministic gallery extraction from the embedded production JSON
 * (ADR 0034/0036, OWN-001).
 *
 * Pure domain code: no WordPress APIs.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extracts the production albums and image memberships.
 */
final class Cdd_Core_Gallery_Extractor {

	/**
	 * Extracts albums and images from the gallery hub page.
	 *
	 * @param string $html galeria/index.html content.
	 */
	public function extract( string $html ): array {
		$xpath = Cdd_Core_Dom::load( $html );

		$items      = $this->embedded_json( $xpath, 'gallery-data' );
		$album_data = $this->embedded_json( $xpath, 'gallery-albums-data' );

		$albums = array();
		foreach ( $album_data as $album ) {
			$albums[] = array(
				'slug'      => preg_replace( '/^galeria-/', '', $album['id'] ),
				'title'     => $album['title'],
				'source_id' => $album['id'],
				'start'     => (int) $album['start'],
				'end'       => (int) $album['end'],
			);
		}

		$images = array();
		foreach ( $items as $position => $item ) {
			$images[] = array(
				'file'     => Cdd_Core_Dom::to_source_path( $item['src'] ),
				'alt'      => $item['alt'],
				'album'    => $this->album_for_position( $albums, $position ),
				'position' => $position,
			);
		}

		foreach ( $albums as &$album ) {
			unset( $album['start'], $album['end'] );
		}

		return array(
			'albums' => $albums,
			'images' => $images,
		);
	}

	/**
	 * Decodes one embedded JSON script by id.
	 *
	 * @param DOMXPath $xpath Document XPath.
	 * @param string   $id    Script element id.
	 */
	private function embedded_json( DOMXPath $xpath, string $id ): array {
		$script = $xpath->query( '//script[@id="' . $id . '"]' )->item( 0 );
		if ( null === $script ) {
			throw new RuntimeException( "Embedded JSON #{$id} not found in the gallery page." ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- extraction-time failure surfaced on the CLI, never in HTML.
		}

		$decoded = json_decode( $script->textContent, true ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOM API property.
		if ( ! is_array( $decoded ) ) {
			throw new RuntimeException( "Embedded JSON #{$id} does not parse." ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- extraction-time failure surfaced on the CLI, never in HTML.
		}

		return $decoded;
	}

	/**
	 * The album slug covering a zero-based image position, per the
	 * production index ranges.
	 *
	 * @param array $albums   Albums with start/end ranges.
	 * @param int   $position Image position.
	 */
	private function album_for_position( array $albums, int $position ): string {
		foreach ( $albums as $album ) {
			if ( $position >= $album['start'] && $position < $album['end'] ) {
				return $album['slug'];
			}
		}

		throw new RuntimeException( "Image position {$position} is outside every album range." ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- extraction-time failure surfaced on the CLI, never in HTML.
	}
}
