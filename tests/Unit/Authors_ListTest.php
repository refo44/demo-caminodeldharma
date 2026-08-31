<?php
/**
 * Level 1: normalization of the post `authors` relationship (ADR 0037).
 *
 * The meta is an ordered, unique array of blog_author IDs — order is the
 * byline. Normalization keeps first occurrences, drops non-IDs and never
 * reorders. Filtering to *published* profiles is a WordPress concern
 * tested at level 2.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: ordered unique author ID list.
 */
final class Authors_ListTest extends TestCase {

	/**
	 * Protects the byline order invariant: the stored order is preserved
	 * exactly, with duplicates collapsed onto their first occurrence.
	 */
	public function test_order_is_preserved_and_duplicates_collapse_to_first_occurrence() {
		$this->assertSame( array( 5, 3, 8 ), Cdd_Core_Authors_List::normalize( array( 5, 3, 5, 8, 3 ) ) );
	}

	/**
	 * Protects data integrity: numeric strings become IDs; zero, negatives
	 * and non-numeric noise are dropped.
	 */
	public function test_non_ids_are_dropped_and_numeric_strings_are_cast() {
		$this->assertSame( array( 3, 5, 8 ), Cdd_Core_Authors_List::normalize( array( '3', 5, -1, 0, 'x', '8' ) ) );
	}

	/**
	 * Protects the empty cases: a draft may carry no authors, and garbage
	 * input degrades to an empty list instead of an error.
	 */
	public function test_empty_or_non_array_input_normalizes_to_an_empty_list() {
		$this->assertSame( array(), Cdd_Core_Authors_List::normalize( array() ) );
		$this->assertSame( array(), Cdd_Core_Authors_List::normalize( 'not-a-list' ) );
		$this->assertSame( array(), Cdd_Core_Authors_List::normalize( null ) );
	}
}
