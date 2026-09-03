<?php
/**
 * Level 2: the block-editor SEO / event surface (META-002 / META-003 /
 * META-004 / META-005, ADR 0042).
 *
 * Three things over a live WordPress: the panel assets load on the right
 * editor screens and nowhere else; the head and event meta survive the
 * REST meta round-trip the panel uses (`editPost` → Publicar/Actualizar);
 * and a publish with no stored head copy backfills a truthful
 * `seo_description` from the object itself, without ever overwriting copy
 * an editor or the importer already wrote.
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * Behavior cluster: SEO panel assets scope, REST persistence, backfill.
 */
final class Editor_SeoPanelTest extends WP_UnitTestCase {

	/**
	 * Re-registers the domain meta: the suite tear_down unregisters every
	 * meta key after each test and the REST meta surface only exists for
	 * registered keys.
	 */
	public function set_up() {
		parent::set_up();
		cdd_core_register_meta();
		cdd_core_register_seo_meta();
	}

	/**
	 * Drops the panel script from the global queue: wp-phpunit does not
	 * reset `wp_scripts()` between tests, so an enqueue left over from a
	 * scope check would otherwise surface as a stray dependency notice when
	 * a later test in the run prints the head.
	 */
	public function tear_down() {
		wp_dequeue_script( CDD_CORE_SEO_PANEL_HANDLE );
		wp_dequeue_script( CDD_CORE_AUTHORS_PANEL_HANDLE );
		parent::tear_down();
	}

	/**
	 * Protects the transport contract: the panel runs on the block-editor
	 * packages it needs to write through `core/editor`.
	 */
	public function test_the_seo_panel_script_declares_the_block_editor_dependencies() {
		cdd_core_register_editor_assets();

		$script = wp_scripts()->registered[ CDD_CORE_SEO_PANEL_HANDLE ] ?? null;

		$this->assertNotNull( $script, 'The SEO panel script must be registered.' );
		$this->assertSame(
			array(
				'wp-plugins',
				'wp-edit-post',
				'wp-editor',
				'wp-data',
				'wp-element',
				'wp-components',
				'wp-i18n',
			),
			$script->deps
		);
		$this->assertStringEndsWith( 'assets/js/seo-panel.js', (string) $script->src );
	}

	/**
	 * Protects the scope: the head panel is for every public editorial type,
	 * so it loads on post.php / post-new.php for post, page, event and
	 * blog_author — and never on the front.
	 */
	public function test_the_seo_panel_is_enqueued_on_every_public_editorial_type() {
		foreach ( array( 'post', 'page', 'event', 'blog_author' ) as $post_type ) {
			$this->assertTrue(
				$this->enqueue_block_editor_assets_for( $post_type ),
				$post_type . ' must load the SEO panel.'
			);
		}
	}

	/**
	 * Protects the front: an editor-only asset never reaches a visitor.
	 */
	public function test_the_seo_panel_is_not_enqueued_on_the_front() {
		wp_dequeue_script( CDD_CORE_SEO_PANEL_HANDLE );

		do_action( 'wp_enqueue_scripts' );

		$this->assertFalse( wp_script_is( CDD_CORE_SEO_PANEL_HANDLE, 'enqueued' ) );
	}

	/**
	 * Protects META-004: `blog_author` carries the same head meta as the
	 * other public types and the `custom-fields` support the editor store
	 * needs to expose it over REST.
	 */
	public function test_blog_author_exposes_the_head_meta_over_rest() {
		$this->assertTrue( post_type_supports( 'blog_author', 'custom-fields' ) );

		$registered = get_registered_meta_keys( 'post', 'blog_author' );
		foreach ( array( 'seo_title', 'seo_description', 'seo_keywords', 'og_title', 'og_description', 'seo_related_url' ) as $key ) {
			$this->assertArrayHasKey( $key, $registered, $key );
			$this->assertTrue( $registered[ $key ]['show_in_rest'], $key );
		}
	}

	/**
	 * Protects META-005 for the head copy: the meta object the panel sends
	 * through `editPost` — head keys inside the same REST body as the save —
	 * persists on every public type, create and update alike.
	 */
	public function test_rest_persists_the_head_meta_on_every_public_type() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		foreach ( array( 'page', 'event', 'blog_author' ) as $post_type ) {
			$create = new WP_REST_Request( 'POST', $this->rest_base( $post_type ) );
			$create->set_body_params(
				array(
					'title'  => 'Head ' . $post_type,
					'status' => 'draft',
					'meta'   => array(
						'seo_title'       => 'Título ' . $post_type,
						'seo_description' => 'Descripción de ' . $post_type . '.',
					),
				)
			);
			$created = rest_do_request( $create );
			$this->assertSame( 201, $created->get_status(), $post_type );
			$id = $created->get_data()['id'];
			$this->assertSame( 'Descripción de ' . $post_type . '.', get_post_meta( $id, 'seo_description', true ), $post_type );

			$update = new WP_REST_Request( 'PUT', $this->rest_base( $post_type ) . '/' . $id );
			$update->set_body_params( array( 'meta' => array( 'seo_description' => 'Reescrita.' ) ) );
			$updated = rest_do_request( $update );
			$this->assertSame( 200, $updated->get_status(), $post_type );
			$this->assertSame( 'Reescrita.', get_post_meta( $id, 'seo_description', true ), $post_type );
		}
	}

	/**
	 * Protects META-005 for the event structured data: dates, place,
	 * schedule, attendance mode and the boolean flags survive the same
	 * round-trip and keep their registered types.
	 */
	public function test_rest_persists_the_event_structured_data() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		$create = new WP_REST_Request( 'POST', '/wp/v2/event' );
		$create->set_body_params(
			array(
				'title'  => 'Retiro de primavera',
				'status' => 'draft',
				'meta'   => array(
					'event_date'            => '2026-10-01',
					'event_end'             => '2026-10-03',
					'event_place'           => 'Casa de la Sangha, Bogotá',
					'event_attendance_mode' => 'offline',
					'event_status'          => 'vigente',
					'event_signup_payment'  => true,
					'event_calendar_dates'  => array( '2026-10-01', '2026-10-02', '2026-10-03' ),
				),
			)
		);
		$created = rest_do_request( $create );

		$this->assertSame( 201, $created->get_status() );
		$id = $created->get_data()['id'];

		$this->assertSame( '2026-10-01', get_post_meta( $id, 'event_date', true ) );
		$this->assertSame( 'Casa de la Sangha, Bogotá', get_post_meta( $id, 'event_place', true ) );
		$this->assertSame( 'offline', get_post_meta( $id, 'event_attendance_mode', true ) );
		$this->assertTrue( (bool) get_post_meta( $id, 'event_signup_payment', true ) );
		$this->assertSame( array( '2026-10-01', '2026-10-02', '2026-10-03' ), get_post_meta( $id, 'event_calendar_dates', true ) );
	}

	/**
	 * Protects binding rule #1: publishing with no stored head copy
	 * backfills `seo_description` from the object's own excerpt, so the
	 * panel and the front agree without WP-CLI.
	 */
	public function test_publishing_backfills_seo_description_from_the_excerpt() {
		$page = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'El linaje',
				'post_excerpt' => 'El linaje Chan y Tierra Pura de la comunidad.',
			)
		);

		$this->assertSame( 'El linaje Chan y Tierra Pura de la comunidad.', get_post_meta( $page, 'seo_description', true ) );
	}

	/**
	 * Protects the fallback chain: with no excerpt the backfill derives a
	 * trimmed summary from the rendered content, not from the title.
	 */
	public function test_publishing_backfills_seo_description_from_the_content() {
		$body = str_repeat( 'La práctica sostenida en comunidad transforma la vida cotidiana. ', 6 );
		$post = wp_insert_post(
			array(
				'post_type'    => 'blog_author',
				'post_status'  => 'publish',
				'post_title'   => 'Zheng Gong',
				'post_content' => '<!-- wp:paragraph --><p>' . $body . '</p><!-- /wp:paragraph -->',
			)
		);

		$stored = get_post_meta( $post, 'seo_description', true );

		$this->assertNotSame( '', $stored );
		$this->assertStringNotContainsString( '<', $stored );
		$this->assertStringStartsWith( 'La práctica sostenida en comunidad', $stored );
		$this->assertLessThanOrEqual( 160, mb_strlen( $stored ) );
	}

	/**
	 * Protects "stored copy wins" (WU-08B): a publish never overwrites a
	 * `seo_description` an editor or the importer already wrote.
	 */
	public function test_publishing_never_overwrites_stored_copy() {
		$page = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Círculos',
				'post_excerpt' => 'Un excerpt distinto.',
				'meta_input'   => array( 'seo_description' => 'Copia importada de producción.' ),
			)
		);

		$this->assertSame( 'Copia importada de producción.', get_post_meta( $page, 'seo_description', true ) );
	}

	/**
	 * Protects the draft exception: a draft carries no invented head copy —
	 * the backfill is a publish-time step.
	 */
	public function test_a_draft_is_not_backfilled() {
		$draft = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'draft',
				'post_title'   => 'Borrador',
				'post_excerpt' => 'Un excerpt de borrador.',
			)
		);

		$this->assertSame( '', get_post_meta( $draft, 'seo_description', true ) );
	}

	/**
	 * Protects acceptance #5 (OWN-013): a date and a place entered in the
	 * panel reach the JSON-LD `Event` node and the generated `.ics` of a
	 * current event.
	 */
	public function test_event_panel_values_reach_json_ld_and_ics() {
		$start = gmdate( 'Y-m-d', strtotime( '+10 days' ) );
		$end   = gmdate( 'Y-m-d', strtotime( '+12 days' ) );
		$event = self::factory()->post->create(
			array(
				'post_type'   => 'event',
				'post_name'   => 'retiro-vigente',
				'post_title'  => 'Retiro vigente',
				'post_status' => 'publish',
				'meta_input'  => array(
					'event_date'  => $start,
					'event_end'   => $end,
					'event_place' => 'Casa de la Sangha, Bogotá',
				),
			)
		);

		$this->go_to( get_permalink( $event ) );
		$graph = array_column( cdd_core_seo_context()['jsonld'], null, '@type' );

		$this->assertArrayHasKey( 'Event', $graph );
		$this->assertSame( $start, substr( (string) $graph['Event']['startDate'], 0, 10 ) );

		$payload = cdd_core_event_calendar_payload( get_post( $event ) );
		$this->assertSame( 'Casa de la Sangha, Bogotá', $payload['location'] );
	}

	/**
	 * Runs the block-editor enqueue on the post editor screen of one post
	 * type and reports whether the SEO panel script ended up enqueued.
	 *
	 * @param string $post_type Post type being edited.
	 */
	private function enqueue_block_editor_assets_for( string $post_type ): bool {
		wp_dequeue_script( CDD_CORE_SEO_PANEL_HANDLE );
		// The `post` screen also enqueues the authors panel: clear it too so
		// a scope check does not leak `wp-edit-post` into a later test.
		wp_dequeue_script( CDD_CORE_AUTHORS_PANEL_HANDLE );

		set_current_screen( 'post' );
		get_current_screen()->post_type = $post_type;

		do_action( 'enqueue_block_editor_assets' );

		return wp_script_is( CDD_CORE_SEO_PANEL_HANDLE, 'enqueued' );
	}

	/**
	 * The REST collection route of one post type.
	 *
	 * @param string $post_type Post type.
	 */
	private function rest_base( string $post_type ): string {
		if ( 'page' === $post_type ) {
			return '/wp/v2/pages';
		}

		return '/wp/v2/' . $post_type;
	}
}
