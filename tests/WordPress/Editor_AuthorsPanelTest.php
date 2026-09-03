<?php
/**
 * Level 2: the block-editor surface of the `authors` relationship —
 * the «Autores del blog» panel assets and the core Author control
 * (ADR 0037 §4/§6, ADR 0042 · META-001).
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * Behavior cluster: editor assets scope and the hidden user-author control.
 */
final class Editor_AuthorsPanelTest extends WP_UnitTestCase {

	/**
	 * Protects the transport contract of META-001: the panel runs on the
	 * block-editor packages it needs to write through `core/editor`, so a
	 * dependency dropped from the enqueue cannot leave the picker mutating
	 * nothing but the DOM.
	 */
	public function test_the_panel_script_declares_the_block_editor_dependencies() {
		cdd_core_register_editor_assets();

		$script = wp_scripts()->registered[ CDD_CORE_AUTHORS_PANEL_HANDLE ] ?? null;

		$this->assertNotNull( $script, 'The authors panel script must be registered.' );
		$this->assertSame(
			array(
				'wp-plugins',
				'wp-edit-post',
				'wp-editor',
				'wp-data',
				'wp-api-fetch',
				'wp-element',
				'wp-components',
				'wp-i18n',
			),
			$script->deps
		);
		$this->assertStringEndsWith( 'assets/js/authors-panel.js', (string) $script->src );
	}

	/**
	 * Protects the scope: the relationship only exists on blog entries, so
	 * the panel loads on post.php / post-new.php for `post` and nowhere
	 * else — not on an event, a page or a profile.
	 */
	public function test_the_panel_is_enqueued_only_on_the_post_editor() {
		$enqueued = array();
		foreach ( array( 'post', 'event', 'page', 'blog_author' ) as $post_type ) {
			$enqueued[ $post_type ] = $this->enqueue_block_editor_assets_for( $post_type );
		}

		$this->assertTrue( $enqueued['post'] );
		$this->assertFalse( $enqueued['event'] );
		$this->assertFalse( $enqueued['page'] );
		$this->assertFalse( $enqueued['blog_author'] );
	}

	/**
	 * Protects the front: an editor-only asset never reaches a visitor.
	 */
	public function test_the_panel_is_not_enqueued_on_the_front() {
		do_action( 'wp_enqueue_scripts' );

		$this->assertFalse( wp_script_is( CDD_CORE_AUTHORS_PANEL_HANDLE, 'enqueued' ) );
	}

	/**
	 * Protects ADR 0037 §4: the byline is the CPT ficha, so the block
	 * editor must not offer the WordPress user as an author to assign —
	 * the control the editor renders from `wp:action-assign-author` is
	 * gone, while every other action link stays.
	 */
	public function test_the_editor_cannot_assign_the_wordpress_user_as_author() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$post_id = self::factory()->post->create( array( 'post_status' => 'draft' ) );

		$request = new WP_REST_Request( 'GET', '/wp/v2/posts/' . $post_id );
		$request->set_param( 'context', 'edit' );
		$links = rest_do_request( $request )->get_links();

		$this->assertArrayNotHasKey( 'https://api.w.org/action-assign-author', $links );
		$this->assertArrayHasKey( 'https://api.w.org/action-publish', $links );
	}

	/**
	 * Protects the accountability half of ADR 0037 §4: hiding the control
	 * never touches who created the post — `post_author` keeps its native
	 * support (list table, quick edit, revisions) and its stored value.
	 */
	public function test_hiding_the_control_keeps_post_author_native() {
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$post_id = self::factory()->post->create( array( 'post_status' => 'draft' ) );

		$this->assertTrue( post_type_supports( 'post', 'author' ) );
		$this->assertSame( $user_id, (int) get_post_field( 'post_author', $post_id ) );
	}

	/**
	 * Runs the block-editor enqueue on the post editor screen of one post
	 * type and reports whether the panel script ended up enqueued.
	 *
	 * @param string $post_type Post type being edited.
	 */
	private function enqueue_block_editor_assets_for( string $post_type ): bool {
		wp_dequeue_script( CDD_CORE_AUTHORS_PANEL_HANDLE );

		set_current_screen( 'post' );
		get_current_screen()->post_type = $post_type;

		do_action( 'enqueue_block_editor_assets' );

		return wp_script_is( CDD_CORE_AUTHORS_PANEL_HANDLE, 'enqueued' );
	}
}
