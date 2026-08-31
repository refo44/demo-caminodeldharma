<?php
/**
 * Level 2: incoming event routes and the generated .ics route (ADR 0008,
 * 0035; OWN-009 / OWN-012). Rewrite resolution is tested against incoming
 * paths, not only get_permalink() (ADR 0032). Full HTTP behavior (redirects,
 * real headers) stays level 3.
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * Behavior cluster: event URL resolution without trailing slashes.
 */
final class Event_RoutingTest extends WP_UnitTestCase {

	/**
	 * Pretty permalinks mirroring the target site (posts under /blog,
	 * canonical URLs without trailing slash — ADR 0008). The domain
	 * objects are re-registered afterwards: WordPress only adds their
	 * permastructs when a permalink structure exists at registration time,
	 * and the suite bootstrap runs init with plain permalinks.
	 */
	public function set_up() {
		parent::set_up();
		$this->set_permalink_structure( '/blog/%postname%' );
		cdd_core_register_post_types();
		cdd_core_register_taxonomies();
		cdd_core_register_rewrites();
		flush_rewrite_rules();
	}

	/**
	 * Protects ADR 0035 routing: an approved event slug resolves as the
	 * event single from the incoming path, and its canonical permalink has
	 * no trailing slash.
	 */
	public function test_event_single_resolves_from_incoming_path_without_trailing_slash() {
		$event_id = self::factory()->post->create(
			array(
				'post_type' => 'event',
				'post_name' => 'vesak-2026',
			)
		);

		$this->assertStringEndsWith( '/eventos/vesak-2026', get_permalink( $event_id ) );

		$this->go_to( '/eventos/vesak-2026' );

		$this->assertQueryTrue( 'is_singular', 'is_single' );
		$this->assertSame( $event_id, get_queried_object_id() );
	}

	/**
	 * Protects the listing route: /eventos is the CPT archive (never a
	 * Page with that slug).
	 */
	public function test_eventos_is_the_event_archive() {
		self::factory()->post->create( array( 'post_type' => 'event' ) );

		$this->go_to( '/eventos' );

		$this->assertTrue( is_post_type_archive( 'event' ) );
	}

	/**
	 * Protects the generated-calendar route: /eventos/ical/{slug}.ics maps
	 * to the plugin's query var instead of colliding with the event single
	 * rules (OWN-009: generated, never Media Library).
	 */
	public function test_ics_route_resolves_to_the_plugin_query_var() {
		self::factory()->post->create(
			array(
				'post_type' => 'event',
				'post_name' => 'circulos-de-presencia-consciente',
			)
		);

		$this->go_to( '/eventos/ical/circulos-de-presencia-consciente.ics' );

		$this->assertSame( 'circulos-de-presencia-consciente', get_query_var( 'cdd_core_event_ics' ) );
	}
}
