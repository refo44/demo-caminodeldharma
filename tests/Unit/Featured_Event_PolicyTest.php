<?php
/**
 * Level 1: home featured-event selection (doc 03 §3 "Un evento en el Inicio").
 *
 * At most one current event on the front page: a current featured one wins;
 * otherwise the current event with the nearest start date; a completed
 * event never appears even if still marked featured; none current → none.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: selection of the single home event note.
 */
final class Featured_Event_PolicyTest extends TestCase {

	/**
	 * Protects rule 2: among current events, the one marked featured is
	 * shown even when another current event starts sooner.
	 */
	public function test_current_featured_event_wins_over_a_sooner_start() {
		$selected = ( new Cdd_Core_Featured_Event_Policy() )->select(
			array(
				$this->event( 'pausa', true, false, '2026-09-01' ),
				$this->event( 'circulos', true, true, '2026-09-03' ),
			)
		);

		$this->assertSame( 'circulos', $selected['id'] );
	}

	/**
	 * Protects rule 3: with no featured mark, the current event with the
	 * nearest start date is shown.
	 */
	public function test_without_featured_mark_the_nearest_current_start_wins() {
		$selected = ( new Cdd_Core_Featured_Event_Policy() )->select(
			array(
				$this->event( 'vesak', true, false, '2026-10-05' ),
				$this->event( 'pausa', true, false, '2026-09-01' ),
			)
		);

		$this->assertSame( 'pausa', $selected['id'] );
	}

	/**
	 * Protects the completed-featured rule: a featured mark on a completed
	 * event is ignored — it must never reach the home page.
	 */
	public function test_completed_featured_event_is_ignored() {
		$selected = ( new Cdd_Core_Featured_Event_Policy() )->select(
			array(
				$this->event( 'encuentro-2025', false, true, '2025-08-01' ),
				$this->event( 'pausa', true, false, '2026-09-01' ),
			)
		);

		$this->assertSame( 'pausa', $selected['id'] );
	}

	/**
	 * Protects rule 4: with more than one current featured event, the one
	 * with the nearest start date is shown.
	 */
	public function test_multiple_current_featured_events_resolve_by_nearest_start() {
		$selected = ( new Cdd_Core_Featured_Event_Policy() )->select(
			array(
				$this->event( 'vesak', true, true, '2026-10-05' ),
				$this->event( 'circulos', true, true, '2026-09-03' ),
			)
		);

		$this->assertSame( 'circulos', $selected['id'] );
	}

	/**
	 * Protects rule 5: with no current event the module does not render —
	 * the selection is null, never an empty box.
	 */
	public function test_no_current_event_selects_nothing() {
		$selected = ( new Cdd_Core_Featured_Event_Policy() )->select(
			array(
				$this->event( 'encuentro-2025', false, true, '2025-08-01' ),
				$this->event( 'vesak-2026', false, false, '2026-05-01' ),
			)
		);

		$this->assertNull( $selected );
	}

	/**
	 * An event descriptor as the policy consumes it.
	 *
	 * @param string $id          Identifier the assertions track.
	 * @param bool   $is_current  Request-time current flag.
	 * @param bool   $is_featured Editorial featured mark.
	 * @param string $start       Start date (Y-m-d).
	 */
	private function event( string $id, bool $is_current, bool $is_featured, string $start ): array {
		return array(
			'id'          => $id,
			'is_current'  => $is_current,
			'is_featured' => $is_featured,
			'start'       => $start,
		);
	}
}
