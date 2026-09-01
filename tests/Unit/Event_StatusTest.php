<?php
/**
 * Level 1: request-time event status policy (OWN-013, ADR 0035).
 *
 * The end date rules, not a manual switch: in America/Bogota, an event is
 * completed when today > event_end (falling back to event_date). The final
 * day itself remains current. Cancelled is editorial and never overridden.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: current/completed/cancelled resolution from dates.
 */
final class Event_StatusTest extends TestCase {

	/**
	 * Protects OWN-013's core rule: the last day of the event is still
	 * current — completion begins strictly after the end date.
	 */
	public function test_event_is_current_on_its_final_day() {
		$status = Cdd_Core_Event_Status::resolve( '2026-08-07', '2026-08-09', false, $this->bogota_now( '2026-08-09 09:00:00' ) );

		$this->assertSame( Cdd_Core_Event_Status::STATUS_CURRENT, $status );
	}

	/**
	 * Protects the automatic completion rule: the day after event_end the
	 * event is completed without any editor action.
	 */
	public function test_event_is_completed_the_day_after_its_end_date() {
		$status = Cdd_Core_Event_Status::resolve( '2026-08-07', '2026-08-09', false, $this->bogota_now( '2026-08-10 00:10:00' ) );

		$this->assertSame( Cdd_Core_Event_Status::STATUS_COMPLETED, $status );
	}

	/**
	 * Protects the fallback: without event_end, event_date is the end
	 * (doc 03 §3 — "si no se rellena, vale event_date").
	 */
	public function test_missing_end_date_falls_back_to_start_date() {
		$current   = Cdd_Core_Event_Status::resolve( '2026-08-07', null, false, $this->bogota_now( '2026-08-07 20:00:00' ) );
		$completed = Cdd_Core_Event_Status::resolve( '2026-08-07', null, false, $this->bogota_now( '2026-08-08 06:00:00' ) );

		$this->assertSame( Cdd_Core_Event_Status::STATUS_CURRENT, $current );
		$this->assertSame( Cdd_Core_Event_Status::STATUS_COMPLETED, $completed );
	}

	/**
	 * Protects the editorial override: cancelled is set by the editor and
	 * the date never changes it — past or future.
	 */
	public function test_cancelled_is_never_overridden_by_dates() {
		$future_cancelled = Cdd_Core_Event_Status::resolve( '2099-01-01', '2099-01-02', true, $this->bogota_now( '2026-08-09 09:00:00' ) );
		$past_cancelled   = Cdd_Core_Event_Status::resolve( '2020-01-01', '2020-01-02', true, $this->bogota_now( '2026-08-09 09:00:00' ) );

		$this->assertSame( Cdd_Core_Event_Status::STATUS_CANCELLED, $future_cancelled );
		$this->assertSame( Cdd_Core_Event_Status::STATUS_CANCELLED, $past_cancelled );
	}

	/**
	 * Protects the timezone contract: "today" is the America/Bogota date at
	 * request time, not the server or UTC date. 03:00 UTC on Aug 10 is
	 * still 22:00 Aug 9 in Bogotá, so the event remains current.
	 */
	public function test_today_is_computed_in_america_bogota_not_utc() {
		$utc_after_midnight = new DateTimeImmutable( '2026-08-10T03:00:00+00:00' );

		$status = Cdd_Core_Event_Status::resolve( '2026-08-07', '2026-08-09', false, $utc_after_midnight );

		$this->assertSame( Cdd_Core_Event_Status::STATUS_CURRENT, $status );
	}

	/**
	 * Protects OWN-013's reversal rule: extending the end date makes a
	 * completed event current again.
	 */
	public function test_extending_the_end_date_makes_the_event_current_again() {
		$now = $this->bogota_now( '2026-08-10 09:00:00' );

		$before_extension = Cdd_Core_Event_Status::resolve( '2026-08-07', '2026-08-09', false, $now );
		$after_extension  = Cdd_Core_Event_Status::resolve( '2026-08-07', '2026-08-12', false, $now );

		$this->assertSame( Cdd_Core_Event_Status::STATUS_COMPLETED, $before_extension );
		$this->assertSame( Cdd_Core_Event_Status::STATUS_CURRENT, $after_extension );
	}

	/**
	 * Protects the safe default for incomplete data: an event with no dates
	 * cannot be proven past, so it stays current instead of silently
	 * disappearing into the archive.
	 */
	public function test_event_without_any_date_is_current() {
		$status = Cdd_Core_Event_Status::resolve( null, null, false, $this->bogota_now( '2026-08-09 09:00:00' ) );

		$this->assertSame( Cdd_Core_Event_Status::STATUS_CURRENT, $status );
	}

	/**
	 * A request-time instant expressed directly in America/Bogota.
	 *
	 * @param string $local_datetime Local Bogotá date-time (Y-m-d H:i:s).
	 */
	private function bogota_now( string $local_datetime ): DateTimeImmutable {
		return new DateTimeImmutable( $local_datetime, new DateTimeZone( 'America/Bogota' ) );
	}
}
