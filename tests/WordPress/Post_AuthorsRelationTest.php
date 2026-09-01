<?php
/**
 * Level 2: the ordered `authors` relationship on posts and its publication
 * guard (ADR 0037 §6–§7).
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * Behavior cluster: authors meta integrity and publish guard.
 */
final class Post_AuthorsRelationTest extends WP_UnitTestCase {

	/**
	 * Re-registers the domain meta: the suite tear_down unregisters every
	 * meta key after each test, and the REST meta surface only exists for
	 * keys present in the registry.
	 */
	public function set_up() {
		parent::set_up();
		cdd_core_register_meta();
	}

	/**
	 * Protects the relationship contract: the meta stores an ordered,
	 * unique array of *published* blog_author IDs — drafts and duplicates
	 * are dropped, order is preserved.
	 */
	public function test_authors_meta_keeps_only_published_profiles_ordered_and_unique() {
		$zheng     = $this->create_profile( 'zheng-gong', 'publish' );
		$comunidad = $this->create_profile( 'comunidad-camino-del-dharma', 'publish' );
		$draft     = $this->create_profile( 'borrador', 'draft' );
		$post_id   = self::factory()->post->create( array( 'post_status' => 'draft' ) );

		update_post_meta( $post_id, 'authors', array( $comunidad, $draft, $zheng, $comunidad ) );

		$this->assertSame( array( $comunidad, $zheng ), get_post_meta( $post_id, 'authors', true ) );
	}

	/**
	 * Protects ADR 0037 §7: publishing requires at least one published
	 * profile — a programmatic publish without authors is demoted to
	 * draft, and one with authors goes through.
	 */
	public function test_publishing_a_post_requires_at_least_one_author() {
		$zheng = $this->create_profile( 'zheng-gong', 'publish' );

		$without_authors = wp_insert_post(
			array(
				'post_title'  => 'Sin autor',
				'post_status' => 'publish',
			)
		);
		$with_authors    = wp_insert_post(
			array(
				'post_title'  => 'Con autor',
				'post_status' => 'publish',
				'meta_input'  => array( 'authors' => array( $zheng ) ),
			)
		);

		$this->assertSame( 'draft', get_post_status( $without_authors ) );
		$this->assertSame( 'publish', get_post_status( $with_authors ) );
	}

	/**
	 * Protects the drafts exception: a draft may carry no authors and is
	 * saved untouched.
	 */
	public function test_drafts_save_without_authors() {
		$draft_id = wp_insert_post(
			array(
				'post_title'  => 'Borrador sin autor',
				'post_status' => 'draft',
			)
		);

		$this->assertSame( 'draft', get_post_status( $draft_id ) );
	}

	/**
	 * Protects "a published post can never end with zero authors": both
	 * emptying and deleting the meta on a published post are rejected and
	 * the stored byline survives.
	 */
	public function test_published_post_cannot_lose_its_last_author() {
		$zheng   = $this->create_profile( 'zheng-gong', 'publish' );
		$post_id = self::factory()->post->create(
			array(
				'post_status' => 'publish',
				'meta_input'  => array( 'authors' => array( $zheng ) ),
			)
		);

		$emptied = update_post_meta( $post_id, 'authors', array() );
		$deleted = delete_post_meta( $post_id, 'authors' );

		$this->assertFalse( $emptied );
		$this->assertFalse( $deleted );
		$this->assertSame( array( $zheng ), get_post_meta( $post_id, 'authors', true ) );
	}

	/**
	 * Protects legacy content (ADR 0037 §7): posts published before the
	 * relationship existed are not unpublished by plugin activation.
	 */
	public function test_activation_does_not_unpublish_legacy_posts_without_authors() {
		remove_filter( 'wp_insert_post_data', 'cdd_core_guard_post_publish', 10 );
		$legacy_id = wp_insert_post(
			array(
				'post_title'  => 'Entrada legada',
				'post_status' => 'publish',
			)
		);
		add_filter( 'wp_insert_post_data', 'cdd_core_guard_post_publish', 10, 2 );

		cdd_core_activate();

		$this->assertSame( 'publish', get_post_status( $legacy_id ) );
	}

	/**
	 * Protects the block-editor path: a REST publish carrying the authors
	 * meta in the same request succeeds, and a REST publish without any
	 * author is rejected as an error, not silently demoted.
	 */
	public function test_rest_publish_honors_the_authors_guard() {
		$zheng = $this->create_profile( 'zheng-gong', 'publish' );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		$with_authors = new WP_REST_Request( 'POST', '/wp/v2/posts' );
		$with_authors->set_body_params(
			array(
				'title'  => 'Con autor',
				'status' => 'publish',
				'meta'   => array( 'authors' => array( $zheng ) ),
			)
		);
		$created = rest_do_request( $with_authors );

		$without_authors = new WP_REST_Request( 'POST', '/wp/v2/posts' );
		$without_authors->set_body_params(
			array(
				'title'  => 'Sin autor',
				'status' => 'publish',
			)
		);
		$rejected = rest_do_request( $without_authors );

		$this->assertSame( 201, $created->get_status() );
		$this->assertSame( 'publish', get_post_status( $created->get_data()['id'] ) );
		$this->assertSame( array( $zheng ), get_post_meta( $created->get_data()['id'], 'authors', true ) );
		$this->assertSame( 400, $rejected->get_status() );
	}

	/**
	 * Protects the assignment search (ADR 0037 §6): the REST collection an
	 * anonymous metabox search hits returns only published profiles.
	 */
	public function test_rest_search_returns_only_published_profiles() {
		$this->create_profile( 'zheng-gong', 'publish', 'Zheng Gong' );
		$this->create_profile( 'zheng-borrador', 'draft', 'Zheng Borrador' );
		wp_set_current_user( 0 );

		$search = new WP_REST_Request( 'GET', '/wp/v2/blog_author' );
		$search->set_query_params( array( 'search' => 'Zheng' ) );
		$results = rest_do_request( $search );

		$this->assertSame( 200, $results->get_status() );
		$this->assertCount( 1, $results->get_data() );
		$this->assertSame( 'zheng-gong', $results->get_data()[0]['slug'] );
	}

	/**
	 * Creates a blog_author profile.
	 *
	 * @param string $slug   Profile slug.
	 * @param string $status Post status.
	 * @param string $title  Profile title.
	 */
	private function create_profile( string $slug, string $status, string $title = '' ): int {
		return self::factory()->post->create(
			array(
				'post_type'   => 'blog_author',
				'post_name'   => $slug,
				'post_status' => $status,
				'post_title'  => '' !== $title ? $title : $slug,
			)
		);
	}
}
