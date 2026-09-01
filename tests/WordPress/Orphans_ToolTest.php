<?php
/**
 * Level 2: the wp-admin «Eliminar huérfanos» tool (OWN-015, WU-08B).
 *
 * Written RED-first. WordPress generates `/eventos/ical/{slug}.ics` on
 * request and never stores a file (OWN-009), so the only calendar files
 * that can exist are leftovers. The tool lists them first and deletes
 * only on an explicit, nonce-protected apply — and it touches nothing
 * else: photos (OWN-003), mantra mp3s and posters are out of scope.
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * Maintenance cluster: orphan `.ics` attachments.
 */
final class Orphans_ToolTest extends WP_UnitTestCase {

	/**
	 * A calendar attachment whose event is over is an orphan; one whose
	 * event is still current is not.
	 */
	public function test_only_calendars_without_a_current_event_are_listed() {
		$current  = $this->create_event( 'evento-vigente', '+10 days' );
		$finished = $this->create_event( 'evento-pasado', '-10 days' );

		$kept    = $this->create_ics( 'evento-vigente.ics' );
		$orphan  = $this->create_ics( 'evento-pasado.ics' );
		$unknown = $this->create_ics( 'evento-inexistente.ics' );

		$listed = wp_list_pluck( cdd_core_orphan_calendars(), 'id' );

		$this->assertContains( $orphan, $listed );
		$this->assertContains( $unknown, $listed );
		$this->assertNotContains( $kept, $listed );
		$this->assertIsInt( $current );
		$this->assertIsInt( $finished );
	}

	/**
	 * OWN-015 scope: photos, audio and posters are never listed, whatever
	 * their orphan status.
	 */
	public function test_other_media_is_out_of_scope() {
		$photo = self::factory()->attachment->create( array( 'post_mime_type' => 'image/jpeg' ) );
		$audio = self::factory()->attachment->create( array( 'post_mime_type' => 'audio/mpeg' ) );

		$listed = wp_list_pluck( cdd_core_orphan_calendars(), 'id' );

		$this->assertNotContains( $photo, $listed );
		$this->assertNotContains( $audio, $listed );
	}

	/**
	 * Dry-run is the default: listing must not delete anything.
	 */
	public function test_listing_deletes_nothing() {
		$orphan = $this->create_ics( 'huerfano.ics' );

		cdd_core_orphan_calendars();

		$this->assertInstanceOf( WP_Post::class, get_post( $orphan ) );
	}

	/**
	 * Applying deletes the listed orphans permanently and reports how
	 * many went.
	 */
	public function test_apply_deletes_the_listed_orphans() {
		$this->create_event( 'evento-vigente', '+10 days' );
		$kept   = $this->create_ics( 'evento-vigente.ics' );
		$orphan = $this->create_ics( 'huerfano.ics' );

		$deleted = cdd_core_delete_orphan_calendars();

		$this->assertSame( 1, $deleted );
		$this->assertNull( get_post( $orphan ) );
		$this->assertInstanceOf( WP_Post::class, get_post( $kept ) );
	}

	/**
	 * Who can run it: whoever can edit events. A subscriber cannot see
	 * the tool and cannot delete through it.
	 */
	public function test_capability_is_required() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$this->assertFalse( cdd_core_can_manage_orphans() );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$this->assertTrue( cdd_core_can_manage_orphans() );
	}

	/**
	 * The tool is a wp-admin page of the domain plugin, not a third-party
	 * plugin and not a theme screen (ADR 0024).
	 */
	public function test_tool_page_is_registered_under_tools() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		set_current_screen( 'tools.php' );

		cdd_core_register_admin_pages();

		$this->assertNotEmpty( menu_page_url( 'cdd-core-orphans', false ) );
	}

	/**
	 * Creates an event relative to now.
	 *
	 * @param string $slug  Event slug.
	 * @param string $start Relative start date.
	 */
	private function create_event( string $slug, string $start ): int {
		return self::factory()->post->create(
			array(
				'post_type'  => 'event',
				'post_name'  => $slug,
				'post_title' => $slug,
				'meta_input' => array( 'event_date' => gmdate( 'Y-m-d', strtotime( $start ) ) ),
			)
		);
	}

	/**
	 * Creates a calendar attachment.
	 *
	 * @param string $file File name.
	 */
	private function create_ics( string $file ): int {
		return self::factory()->attachment->create(
			array(
				'post_mime_type' => 'text/calendar',
				'post_title'     => $file,
				'file'           => '2026/08/' . $file,
			)
		);
	}
}
