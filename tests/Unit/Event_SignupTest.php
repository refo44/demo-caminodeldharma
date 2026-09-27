<?php
/**
 * Level 1: request-time signup visibility (issue #52).
 *
 * Signup shows only while the event is current, a signup URL is stored,
 * the manual control is open, and no close instant has been reached in
 * America/Bogota. Closing signup does not change event status.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: when a stored signup URL may be shown.
 */
final class Event_SignupTest extends TestCase {

	/**
	 * Protects the default: an empty manual flag and an empty close
	 * instant leave a current event with a URL open.
	 */
	public function test_current_event_with_url_and_empty_controls_is_open() {
		$open = Cdd_Core_Event_Signup::is_open(
			true,
			'https://forms.example/x',
			false,
			'',
			$this->bogota_now( '2026-09-26 12:00:00' )
		);

		$this->assertTrue( $open );
	}

	/**
	 * Protects the manual control: closed hides signup immediately, even
	 * when the scheduled instant is empty or still ahead.
	 */
	public function test_manual_close_hides_signup_before_any_schedule() {
		$now = $this->bogota_now( '2026-09-26 12:00:00' );

		$empty_schedule  = Cdd_Core_Event_Signup::is_open( true, 'https://forms.example/x', true, '', $now );
		$future_schedule = Cdd_Core_Event_Signup::is_open( true, 'https://forms.example/x', true, '2026-10-01T18:00:00', $now );

		$this->assertFalse( $empty_schedule );
		$this->assertFalse( $future_schedule );
	}

	/**
	 * Protects reopening: clearing the manual close shows signup again
	 * while the close instant is still ahead.
	 */
	public function test_reopening_shows_signup_when_the_schedule_is_still_ahead() {
		$open = Cdd_Core_Event_Signup::is_open(
			true,
			'https://forms.example/x',
			false,
			'2026-10-01T18:00:00',
			$this->bogota_now( '2026-09-26 12:00:00' )
		);

		$this->assertTrue( $open );
	}

	/**
	 * Protects the schedule: at and after the close instant, signup is
	 * hidden while the manual control stays open.
	 */
	public function test_signup_hides_at_and_after_the_close_instant() {
		$closes_at = '2026-10-01T15:30:00';

		$before = Cdd_Core_Event_Signup::is_open( true, 'https://forms.example/x', false, $closes_at, $this->bogota_now( '2026-10-01 15:29:59' ) );
		$at     = Cdd_Core_Event_Signup::is_open( true, 'https://forms.example/x', false, $closes_at, $this->bogota_now( '2026-10-01 15:30:00' ) );
		$after  = Cdd_Core_Event_Signup::is_open( true, 'https://forms.example/x', false, $closes_at, $this->bogota_now( '2026-10-01 15:30:01' ) );

		$this->assertTrue( $before );
		$this->assertFalse( $at );
		$this->assertFalse( $after );
	}

	/**
	 * Protects a date without a time: it closes at 00:00 America/Bogota
	 * on that calendar day, so the whole day is closed.
	 */
	public function test_date_without_time_closes_at_midnight_in_bogota() {
		$closes_at = '2026-10-01';

		$before = Cdd_Core_Event_Signup::is_open( true, 'https://forms.example/x', false, $closes_at, $this->bogota_now( '2026-09-30 23:59:59' ) );
		$at     = Cdd_Core_Event_Signup::is_open( true, 'https://forms.example/x', false, $closes_at, $this->bogota_now( '2026-10-01 00:00:00' ) );
		$later  = Cdd_Core_Event_Signup::is_open( true, 'https://forms.example/x', false, $closes_at, $this->bogota_now( '2026-10-01 09:00:00' ) );

		$this->assertTrue( $before );
		$this->assertFalse( $at );
		$this->assertFalse( $later );
	}

	/**
	 * Protects the timezone contract: the close instant is a Bogotá wall
	 * clock. 05:00 UTC is still 00:00 the same calendar day in Bogotá.
	 */
	public function test_close_instant_is_compared_in_america_bogota() {
		$utc = new DateTimeImmutable( '2026-10-01T04:59:59+00:00' );

		$open = Cdd_Core_Event_Signup::is_open( true, 'https://forms.example/x', false, '2026-10-01T00:00:00', $utc );

		$this->assertTrue( $open );
	}

	/**
	 * Protects an empty schedule: it never hides signup by itself.
	 */
	public function test_empty_close_instant_does_not_hide_signup() {
		$open = Cdd_Core_Event_Signup::is_open(
			true,
			'https://forms.example/x',
			false,
			'',
			$this->bogota_now( '2099-01-01 00:00:00' )
		);

		$this->assertTrue( $open );
	}

	/**
	 * Protects OWN-012: a completed or cancelled event never shows
	 * signup, even when these fields say open and the URL is stored.
	 */
	public function test_completed_or_cancelled_event_never_shows_signup() {
		$now = $this->bogota_now( '2026-09-26 12:00:00' );

		$completed = Cdd_Core_Event_Signup::is_open( false, 'https://forms.example/x', false, '', $now );

		$this->assertFalse( $completed );
	}

	/**
	 * Protects the stored URL gate: an empty URL never invites, and a
	 * garbage close instant does not hide a current open signup.
	 */
	public function test_empty_url_hides_signup_and_garbage_schedule_does_not() {
		$now = $this->bogota_now( '2026-09-26 12:00:00' );

		$no_url  = Cdd_Core_Event_Signup::is_open( true, '', false, '', $now );
		$garbage = Cdd_Core_Event_Signup::is_open( true, 'https://forms.example/x', false, 'not-a-date', $now );

		$this->assertFalse( $no_url );
		$this->assertTrue( $garbage );
	}

	/**
	 * Protects the stored form of the close instant: a calendar day stays
	 * a date, a clock becomes Bogotá wall time with seconds, and anything
	 * else is discarded.
	 */
	public function test_close_instant_normalizes_to_a_bogota_wall_clock() {
		$this->assertSame( '', Cdd_Core_Event_Signup::normalize( '' ) );
		$this->assertSame( '', Cdd_Core_Event_Signup::normalize( '2026-13-40' ) );
		$this->assertSame( '', Cdd_Core_Event_Signup::normalize( '2026-10-01T25:00' ) );
		$this->assertSame( '2026-10-01', Cdd_Core_Event_Signup::normalize( '2026-10-01' ) );
		$this->assertSame( '2026-10-01T15:30:00', Cdd_Core_Event_Signup::normalize( '2026-10-01T15:30' ) );
		$this->assertSame( '2026-10-01T15:30:45', Cdd_Core_Event_Signup::normalize( '2026-10-01 15:30:45' ) );
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
