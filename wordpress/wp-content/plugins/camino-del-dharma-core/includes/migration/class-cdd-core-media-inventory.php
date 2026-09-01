<?php
/**
 * Media classification for the seed (OWN-001/002/003, OWN-009/009-img):
 * referenced files seed as public content, unreferenced files as hidden
 * reserves; derivatives, OS noise and retired files never travel.
 *
 * Pure domain code: no WordPress APIs — the runner walks the filesystem.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classifies the static media tree for the Media Library seed.
 */
final class Cdd_Core_Media_Inventory {

	/**
	 * Classifies media files against the set of referenced paths.
	 *
	 * @param array $files      Repo-relative file paths (under static/).
	 * @param array $referenced Repo-relative paths referenced by HTML/JSON.
	 */
	public function classify( array $files, array $referenced ): array {
		$referenced_set = array_fill_keys( $referenced, true );

		$media = array();
		foreach ( $files as $file ) {
			if ( $this->is_excluded( $file ) ) {
				continue;
			}
			$is_referenced = isset( $referenced_set[ $file ] );

			$media[] = array(
				'file'       => $file,
				'kind'       => 'mp3' === $this->extension( $file ) ? 'audio' : 'image',
				'referenced' => $is_referenced,
				'visibility' => $is_referenced ? 'public' : 'hidden',
			);
		}

		return $media;
	}

	/**
	 * Whether a file never seeds: handmade thumbs (WordPress regenerates
	 * derivatives), OS noise, the retired PDF (OWN-002) and on-disk .ics
	 * files (OWN-009: generated, never Media Library).
	 *
	 * @param string $file Repo-relative path.
	 */
	private function is_excluded( string $file ): bool {
		if ( false !== strpos( $file, '/thumbs/' ) ) {
			return true;
		}
		if ( '.DS_Store' === basename( $file ) ) {
			return true;
		}

		return in_array( $this->extension( $file ), array( 'pdf', 'ics' ), true );
	}

	/**
	 * Lowercase file extension.
	 *
	 * @param string $file Repo-relative path.
	 */
	private function extension( string $file ): string {
		return strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
	}
}
