<?php
/**
 * Level 1: the offer opening date published as offers.validFrom (issue #54).
 *
 * An editor value becomes an America/Bogota instant. An empty or unusable
 * value stays empty so the graph can omit the field instead of inventing
 * the event start or "now".
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: how an inscription opening date is stored for schema.org.
 */
final class Event_OfferTest extends TestCase {

	/**
	 * A calendar day opens at midnight in Bogota. A wall clock keeps its
	 * hour and gains the Bogota offset. Colombia does not observe DST.
	 */
	public function test_opening_date_is_a_bogota_instant() {
		$this->assertSame( '2026-08-13T00:00:00-05:00', Cdd_Core_Event_Offer::valid_from( '2026-08-13' ) );
		$this->assertSame( '2026-08-13T12:00:00-05:00', Cdd_Core_Event_Offer::valid_from( '2026-08-13T12:00' ) );
		$this->assertSame( '2026-08-13T12:00:00-05:00', Cdd_Core_Event_Offer::valid_from( '2026-08-13T12:00:00-05:00' ) );
	}

	/**
	 * Empty and impossible values do not become a date.
	 */
	public function test_unknown_opening_date_stays_empty() {
		$this->assertSame( '', Cdd_Core_Event_Offer::valid_from( '' ) );
		$this->assertSame( '', Cdd_Core_Event_Offer::valid_from( '2026-13-40' ) );
		$this->assertSame( '', Cdd_Core_Event_Offer::valid_from( 'mañana' ) );
		$this->assertSame( '', Cdd_Core_Event_Offer::valid_from( 'tomorrow-05:00' ) );
		$this->assertSame( '', Cdd_Core_Event_Offer::valid_from( '2026-02-30T12:00:00-05:00' ) );
	}

	/**
	 * Post meta keeps a wall clock the datetime control can show again.
	 * The offset is added only when the graph is published.
	 */
	public function test_stored_opening_date_is_a_bogota_wall_clock() {
		$this->assertSame( '2026-08-13T00:00:00', Cdd_Core_Event_Offer::stored( '2026-08-13' ) );
		$this->assertSame( '2026-08-13T12:00:00', Cdd_Core_Event_Offer::stored( '2026-08-13T12:00' ) );
		$this->assertSame( '2026-08-13T12:00:00', Cdd_Core_Event_Offer::stored( '2026-08-13T17:00:00Z' ) );
		$this->assertSame( '', Cdd_Core_Event_Offer::stored( 'mañana' ) );
		$this->assertSame( '', Cdd_Core_Event_Offer::stored( 'tomorrow-05:00' ) );
		$this->assertSame( '', Cdd_Core_Event_Offer::stored( '2026-02-30T12:00:00-05:00' ) );
	}
}
