<?php
/**
 * Ordered unique author ID list (ADR 0037 §6).
 *
 * Pure domain code: no WordPress APIs. The `authors` post meta is an
 * ordered, unique array of blog_author IDs — the order is the byline.
 * Filtering to *published* profiles happens in the WordPress sanitizer.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalizes the authors relationship value.
 */
final class Cdd_Core_Authors_List {

	/**
	 * Normalizes to an ordered list of unique positive integer IDs.
	 *
	 * Order is preserved; duplicates collapse onto their first occurrence;
	 * non-numeric noise, zero and negatives are dropped; non-array input
	 * degrades to an empty list.
	 *
	 * @param mixed $value Raw meta value.
	 */
	public static function normalize( $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$ids = array();
		foreach ( $value as $candidate ) {
			if ( ! is_int( $candidate ) && ! ( is_string( $candidate ) && ctype_digit( $candidate ) ) ) {
				continue;
			}
			$id = (int) $candidate;
			if ( $id > 0 && ! in_array( $id, $ids, true ) ) {
				$ids[] = $id;
			}
		}

		return $ids;
	}
}
