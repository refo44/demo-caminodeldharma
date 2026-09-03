<?php
/**
 * Level 2: CPT `blog_author` registration, /author routing and suppression
 * of native WP-user author archives (ADR 0037).
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * Behavior cluster: blog author profiles registered and routed.
 */
final class Blog_AuthorTest extends WP_UnitTestCase {

	/**
	 * Pretty permalinks mirroring the target site (ADR 0008); the domain
	 * objects are re-registered afterwards because WordPress only adds
	 * their permastructs when a permalink structure exists at
	 * registration time.
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
	 * Protects ADR 0037 §9 — the 404 bug to never repeat: the CPT rewrites
	 * under /author but its query var is blog_author, never author, with
	 * its own capability type mapped to meta caps.
	 */
	public function test_blog_author_registration_isolates_the_author_query_var() {
		$blog_author_type = get_post_type_object( 'blog_author' );

		$this->assertNotNull( $blog_author_type );
		$this->assertTrue( $blog_author_type->public );
		$this->assertTrue( $blog_author_type->show_in_rest );
		$this->assertTrue( $blog_author_type->has_archive );
		$this->assertSame( 'blog_author', $blog_author_type->query_var );
		$this->assertSame( 'author', $blog_author_type->rewrite['slug'] );
		$this->assertFalse( $blog_author_type->rewrite['with_front'] );
		$this->assertSame( 'edit_blog_author', $blog_author_type->cap->edit_post );
		$this->assertSame( 'edit_blog_authors', $blog_author_type->cap->edit_posts );
		$this->assertTrue( $blog_author_type->map_meta_cap );
		$this->assertTrue( post_type_supports( 'blog_author', 'title' ) );
		$this->assertTrue( post_type_supports( 'blog_author', 'editor' ) );
		$this->assertTrue( post_type_supports( 'blog_author', 'thumbnail' ) );
		// META-004: the block editor reads and writes the head meta over the
		// REST `meta` object, which the CPT only exposes with this support.
		$this->assertTrue( post_type_supports( 'blog_author', 'custom-fields' ) );
	}

	/**
	 * Protects the profile route: /author/{slug} resolves the CPT single
	 * from the incoming path, without a trailing slash.
	 */
	public function test_author_profile_single_resolves_from_incoming_path() {
		$profile_id = self::factory()->post->create(
			array(
				'post_type' => 'blog_author',
				'post_name' => 'zheng-gong',
			)
		);

		$this->assertStringEndsWith( '/author/zheng-gong', get_permalink( $profile_id ) );

		$this->go_to( '/author/zheng-gong' );

		$this->assertTrue( is_singular( 'blog_author' ) );
		$this->assertSame( $profile_id, get_queried_object_id() );
	}

	/**
	 * Protects the profile archive route: /author lists the CPT profiles
	 * (noindex policy is a later, separate surface).
	 */
	public function test_author_archive_is_the_profile_archive() {
		self::factory()->post->create( array( 'post_type' => 'blog_author' ) );

		$this->go_to( '/author' );

		$this->assertTrue( is_post_type_archive( 'blog_author' ) );
	}

	/**
	 * Protects ADR 0037 §5: native WP-user author archives are off — an
	 * author query resolves as a real 404, and no rewrite rule routes to
	 * the native author_name query var.
	 */
	public function test_native_user_author_archives_return_404() {
		$user_id = self::factory()->user->create( array( 'role' => 'author' ) );

		$this->go_to( '/?author=' . $user_id );

		$this->assertTrue( is_404() );

		foreach ( get_option( 'rewrite_rules' ) as $rule => $target ) {
			$this->assertStringNotContainsString( 'author_name=', $target, "Rule {$rule} must not route to the native user author archive." );
		}
	}

	/**
	 * Protects editorial access: after the capability grant, administrators
	 * and editors manage profiles (the custom capability_type grants
	 * nothing by itself).
	 */
	public function test_capability_grant_enables_administrators_and_editors() {
		cdd_core_grant_capabilities();

		foreach ( array( 'administrator', 'editor' ) as $role_name ) {
			$role = get_role( $role_name );
			$this->assertTrue( $role->has_cap( 'edit_blog_authors' ), "{$role_name} must edit profiles." );
			$this->assertTrue( $role->has_cap( 'publish_blog_authors' ), "{$role_name} must publish profiles." );
			$this->assertTrue( $role->has_cap( 'delete_blog_authors' ), "{$role_name} must delete profiles." );
		}
	}
}
