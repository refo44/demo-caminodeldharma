<?php
/**
 * Level 1: home featured-article selection.
 *
 * The home column shows a published article only when an editor marked it
 * featured. An unmarked article never fills that column, and an unpublished
 * mark never reaches the visitor. Several marked articles appear, newest
 * first.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: which articles may join the home featured column.
 */
final class Featured_Post_PolicyTest extends TestCase {

	/**
	 * Protects the editorial gate: a published article with no featured
	 * mark is absent from the column.
	 */
	public function test_unmarked_published_article_stays_out() {
		$selected = ( new Cdd_Core_Featured_Post_Policy() )->select(
			array(
				$this->article( 'sangha', true, false, '2026-08-01 10:00:00' ),
			)
		);

		$this->assertSame( array(), $selected );
	}

	/**
	 * Protects the publication gate: a featured draft stays out of the
	 * public column.
	 */
	public function test_featured_draft_is_excluded() {
		$selected = ( new Cdd_Core_Featured_Post_Policy() )->select(
			array(
				$this->article( 'borrador', false, true, '2026-09-20 10:00:00' ),
				$this->article( 'publicado', true, true, '2026-08-01 10:00:00' ),
			)
		);

		$this->assertSame( array( 'publicado' ), array_column( $selected, 'id' ) );
	}

	/**
	 * Protects the stack: every published featured article is kept, newest
	 * publication date first.
	 */
	public function test_featured_articles_are_newest_first() {
		$selected = ( new Cdd_Core_Featured_Post_Policy() )->select(
			array(
				$this->article( 'antiguo', true, true, '2026-01-02 10:00:00' ),
				$this->article( 'reciente', true, true, '2026-09-01 10:00:00' ),
				$this->article( 'sin-marca', true, false, '2026-09-20 10:00:00' ),
			)
		);

		$this->assertSame( array( 'reciente', 'antiguo' ), array_column( $selected, 'id' ) );
	}

	/**
	 * Protects date order when one featured article has no publication
	 * timestamp: an empty date must not sort ahead of a real one.
	 */
	public function test_undated_featured_article_sorts_after_dated_ones() {
		$selected = ( new Cdd_Core_Featured_Post_Policy() )->select(
			array(
				$this->article( 'sin-fecha', true, true, '' ),
				$this->article( 'fechado', true, true, '2026-03-01 10:00:00' ),
			)
		);

		$this->assertSame( array( 'fechado', 'sin-fecha' ), array_column( $selected, 'id' ) );
	}

	/**
	 * An article descriptor as the policy consumes it.
	 *
	 * @param string $id           Identifier the assertions track.
	 * @param bool   $is_published Whether the article is published.
	 * @param bool   $is_featured  Editorial featured mark.
	 * @param string $date         Publication timestamp.
	 */
	private function article( string $id, bool $is_published, bool $is_featured, string $date ): array {
		return array(
			'id'           => $id,
			'is_published' => $is_published,
			'is_featured'  => $is_featured,
			'date'         => $date,
		);
	}
}
