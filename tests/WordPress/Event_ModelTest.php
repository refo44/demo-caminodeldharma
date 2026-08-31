<?php
/**
 * Level 2: CPT `event` registration, taxonomies and metadata (doc 03 §3–§4).
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * Behavior cluster: event domain model registered in real WordPress.
 */
final class Event_ModelTest extends WP_UnitTestCase {

	/**
	 * Re-registers the domain meta: the suite tear_down unregisters every
	 * meta key after each test (only the sanitize hooks survive via the
	 * hook backup), so the registry assertions need a fresh registration.
	 */
	public function set_up() {
		parent::set_up();
		cdd_core_register_meta();
	}

	/**
	 * Protects the routing contract of ADR 0035: public CPT at /eventos
	 * with archive, REST exposure for the block editor, and no with_front
	 * prefix leakage.
	 */
	public function test_event_post_type_is_registered_for_public_routing() {
		$event_type = get_post_type_object( 'event' );

		$this->assertNotNull( $event_type );
		$this->assertTrue( $event_type->public );
		$this->assertTrue( $event_type->show_in_rest );
		$this->assertSame( 'eventos', $event_type->has_archive );
		$this->assertSame( 'eventos', $event_type->rewrite['slug'] );
		$this->assertFalse( $event_type->rewrite['with_front'] );
		$this->assertTrue( post_type_supports( 'event', 'title' ) );
		$this->assertTrue( post_type_supports( 'event', 'editor' ) );
		$this->assertTrue( post_type_supports( 'event', 'thumbnail' ) );
		$this->assertTrue( post_type_supports( 'event', 'excerpt' ) );
	}

	/**
	 * Protects ADR 0022: event_type (hierarchical) and event_city (flat)
	 * exist as data/labels on events but expose no public archive URL.
	 */
	public function test_event_taxonomies_are_registered_without_public_archives() {
		$event_type_tax = get_taxonomy( 'event_type' );
		$event_city_tax = get_taxonomy( 'event_city' );

		$this->assertNotFalse( $event_type_tax );
		$this->assertNotFalse( $event_city_tax );
		$this->assertTrue( $event_type_tax->hierarchical );
		$this->assertFalse( $event_city_tax->hierarchical );

		foreach ( array( $event_type_tax, $event_city_tax ) as $taxonomy ) {
			$this->assertContains( 'event', $taxonomy->object_type );
			$this->assertFalse( $taxonomy->public );
			$this->assertFalse( $taxonomy->publicly_queryable );
			$this->assertFalse( $taxonomy->rewrite );
			$this->assertTrue( $taxonomy->show_ui );
			$this->assertTrue( $taxonomy->show_in_rest );
		}
	}

	/**
	 * Protects the metadata surface of doc 03 §3: every event field the
	 * core model needs is registered for the event subtype.
	 */
	public function test_event_meta_fields_are_registered() {
		$registered_meta = get_registered_meta_keys( 'post', 'event' );

		foreach ( array( 'event_date', 'event_end', 'event_place', 'event_modality', 'event_status', 'event_featured', 'event_signup_url', 'event_signup_payment', 'event_calendar_dates' ) as $meta_key ) {
			$this->assertArrayHasKey( $meta_key, $registered_meta, "Meta {$meta_key} must be registered for events." );
		}
	}

	/**
	 * Protects data integrity: dates are Y-m-d or empty, the modality and
	 * status enums reject values outside the doc 03 lists, and the signup
	 * URL is sanitized as a URL.
	 */
	public function test_event_meta_is_sanitized_on_write() {
		$event_id = self::factory()->post->create( array( 'post_type' => 'event' ) );

		update_post_meta( $event_id, 'event_date', '2026-13-40' );
		update_post_meta( $event_id, 'event_end', '2026-09-04' );
		update_post_meta( $event_id, 'event_modality', 'banana' );
		update_post_meta( $event_id, 'event_status', 'archivado' );
		update_post_meta( $event_id, 'event_signup_url', 'javascript:alert(1)' );
		update_post_meta( $event_id, 'event_calendar_dates', array( '2026-09-03', 'bad', '2026-09-10' ) );

		$this->assertSame( '', get_post_meta( $event_id, 'event_date', true ) );
		$this->assertSame( '2026-09-04', get_post_meta( $event_id, 'event_end', true ) );
		$this->assertSame( '', get_post_meta( $event_id, 'event_modality', true ) );
		$this->assertSame( 'vigente', get_post_meta( $event_id, 'event_status', true ) );
		$this->assertStringNotContainsString( 'javascript:', get_post_meta( $event_id, 'event_signup_url', true ) );
		$this->assertSame( array( '2026-09-03', '2026-09-10' ), get_post_meta( $event_id, 'event_calendar_dates', true ) );
	}

	/**
	 * Protects the valid-value path: the doc 03 enums and dates survive a
	 * write unchanged (meta persists through wp_insert_post's meta_input).
	 */
	public function test_valid_event_meta_survives_insertion() {
		$event_id = self::factory()->post->create(
			array(
				'post_type'  => 'event',
				'meta_input' => array(
					'event_date'     => '2026-09-03',
					'event_modality' => 'presencial',
					'event_status'   => 'cancelado',
					'event_place'    => 'Casa Retiro San Pablo, Puerto Colombia',
				),
			)
		);

		$this->assertSame( '2026-09-03', get_post_meta( $event_id, 'event_date', true ) );
		$this->assertSame( 'presencial', get_post_meta( $event_id, 'event_modality', true ) );
		$this->assertSame( 'cancelado', get_post_meta( $event_id, 'event_status', true ) );
		$this->assertSame( 'Casa Retiro San Pablo, Puerto Colombia', get_post_meta( $event_id, 'event_place', true ) );
	}
}
