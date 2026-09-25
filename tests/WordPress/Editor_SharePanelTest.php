<?php
/**
 * Level 2: the «Compartir» panel on posts and events (issue #39, ADR 0042).
 *
 * The script loads on the block editor of a post and of an event, and
 * nowhere else. The three existing share keys survive the REST meta
 * round-trip the panel uses, including a later save that omits `meta`.
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * Behavior cluster: share panel assets scope and REST persistence.
 */
final class Editor_SharePanelTest extends WP_UnitTestCase {

	/**
	 * Re-registers the domain meta: the suite tear_down unregisters every
	 * meta key after each test.
	 */
	public function set_up() {
		parent::set_up();
		cdd_core_register_meta();
	}

	/**
	 * Drops the panel script from the global queue: wp-phpunit does not
	 * reset `wp_scripts()` between tests.
	 */
	public function tear_down() {
		wp_dequeue_script( CDD_CORE_SHARE_PANEL_HANDLE );
		wp_dequeue_script( CDD_CORE_SEO_PANEL_HANDLE );
		wp_dequeue_script( CDD_CORE_AUTHORS_PANEL_HANDLE );
		parent::tear_down();
	}

	/**
	 * Protects the transport: the panel runs on the block-editor packages
	 * it needs to write through `core/editor`.
	 */
	public function test_the_share_panel_script_declares_the_block_editor_dependencies() {
		cdd_core_register_editor_assets();

		$script = wp_scripts()->registered[ CDD_CORE_SHARE_PANEL_HANDLE ] ?? null;

		$this->assertNotNull( $script, 'The share panel script must be registered.' );
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
		$this->assertStringEndsWith( 'assets/js/share-panel.js', (string) $script->src );
	}

	/**
	 * Protects the scope: articles and events, not pages or author fichas.
	 */
	public function test_the_share_panel_is_enqueued_on_posts_and_events_only() {
		foreach ( array( 'post', 'event' ) as $post_type ) {
			$this->assertTrue(
				$this->enqueue_block_editor_assets_for( $post_type ),
				$post_type . ' must load the Compartir panel.'
			);
		}

		foreach ( array( 'page', 'blog_author' ) as $post_type ) {
			$this->assertFalse(
				$this->enqueue_block_editor_assets_for( $post_type ),
				$post_type . ' must not load the Compartir panel.'
			);
		}
	}

	/**
	 * Protects the front: an editor-only asset never reaches a visitor.
	 */
	public function test_the_share_panel_is_not_enqueued_on_the_front() {
		wp_dequeue_script( CDD_CORE_SHARE_PANEL_HANDLE );

		do_action( 'wp_enqueue_scripts' );

		$this->assertFalse( wp_script_is( CDD_CORE_SHARE_PANEL_HANDLE, 'enqueued' ) );
	}

	/**
	 * Protects the round-trip: sending `meta.share_*` persists on a post
	 * and on an event, and a later save that omits `meta` does not wipe it.
	 */
	public function test_rest_persists_share_messages_and_a_later_save_keeps_them() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		foreach ( array( 'post', 'event' ) as $post_type ) {
			$create = new WP_REST_Request( 'POST', $this->rest_base( $post_type ) );
			$create->set_body_params(
				array(
					'title'  => 'Compartir ' . $post_type,
					'status' => 'draft',
					'meta'   => array(
						'share_whatsapp' => "Invitación\n\n{{SHARE_URL}}",
						'share_x'        => 'Una línea para X',
						'share_threads'  => 'Una línea para Threads',
					),
				)
			);
			$created = rest_do_request( $create );
			$this->assertSame( 201, $created->get_status(), $post_type );
			$id = $created->get_data()['id'];
			$this->assertSame( "Invitación\n\n{{SHARE_URL}}", get_post_meta( $id, 'share_whatsapp', true ), $post_type );

			$update = new WP_REST_Request( 'POST', $this->rest_base( $post_type ) . '/' . $id );
			$update->set_body_params( array( 'title' => 'Compartir ' . $post_type . ' revisado' ) );
			$updated = rest_do_request( $update );
			$this->assertSame( 200, $updated->get_status(), $post_type );
			$this->assertSame( "Invitación\n\n{{SHARE_URL}}", get_post_meta( $id, 'share_whatsapp', true ), $post_type );
			$this->assertSame( 'Una línea para X', get_post_meta( $id, 'share_x', true ), $post_type );
			$this->assertSame( 'Una línea para Threads', get_post_meta( $id, 'share_threads', true ), $post_type );
		}
	}

	/**
	 * The REST collection route of one shareable post type.
	 *
	 * @param string $post_type Post type.
	 */
	private function rest_base( string $post_type ): string {
		return 'post' === $post_type ? '/wp/v2/posts' : '/wp/v2/' . $post_type;
	}

	/**
	 * Runs the block-editor enqueue on the post editor screen of one post
	 * type and reports whether the share panel script ended up enqueued.
	 *
	 * @param string $post_type Post type being edited.
	 */
	private function enqueue_block_editor_assets_for( string $post_type ): bool {
		wp_dequeue_script( CDD_CORE_SHARE_PANEL_HANDLE );
		wp_dequeue_script( CDD_CORE_SEO_PANEL_HANDLE );
		wp_dequeue_script( CDD_CORE_AUTHORS_PANEL_HANDLE );

		set_current_screen( 'post' );
		get_current_screen()->post_type = $post_type;

		do_action( 'enqueue_block_editor_assets' );

		return wp_script_is( CDD_CORE_SHARE_PANEL_HANDLE, 'enqueued' );
	}
}
