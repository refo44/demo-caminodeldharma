<?php
/**
 * Home featured-article selection.
 *
 * Pure domain code: no WordPress APIs. The home column lists every
 * published article an editor marked featured, newest first. An unmarked
 * article never appears there, and a featured draft never does either.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Selects the articles that may join the home featured column.
 */
final class Cdd_Core_Featured_Post_Policy {

	/**
	 * Selects the published featured articles, newest publication date first.
	 *
	 * @param array $articles Article descriptors: is_published (bool),
	 *                        is_featured (bool), date (timestamp string),
	 *                        plus any caller payload returned untouched.
	 */
	public function select( array $articles ): array {
		$featured = array();
		foreach ( $articles as $article ) {
			if ( ! empty( $article['is_published'] ) && ! empty( $article['is_featured'] ) ) {
				$featured[] = $article;
			}
		}

		usort(
			$featured,
			static function ( array $left, array $right ): int {
				return self::compare_newest_first(
					(string) ( $left['date'] ?? '' ),
					(string) ( $right['date'] ?? '' )
				);
			}
		);

		return $featured;
	}

	/**
	 * Newest timestamp first. An empty date sorts after any real one
	 * (strcmp would put '' first).
	 *
	 * @param string $left  Publication timestamp, or empty.
	 * @param string $right Publication timestamp, or empty.
	 */
	private static function compare_newest_first( string $left, string $right ): int {
		$left_empty  = ( '' === $left );
		$right_empty = ( '' === $right );

		if ( $left_empty !== $right_empty ) {
			return $left_empty ? 1 : -1;
		}

		return strcmp( $right, $left );
	}
}
