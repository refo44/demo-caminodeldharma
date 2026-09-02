<?php
/**
 * Level 2: the WordPress installer's demo content never reaches the public
 * site, and a first-party command removes it (D-02 / OWN-024, issue #10).
 *
 * Written RED-first. The observable failure is real: in the local
 * environment «Hello world!» is listed in the home «Del blog» section and
 * in /blog, where it displaces the real entry «Estamos conectados, pero
 * seguimos solos»; «Sample Page» is published and the installer's «Privacy
 * Policy» draft is still around. Staging and production must show
 * production content only.
 *
 * The fixtures are created with the ADR 0037 §7 publication guard detached,
 * because that is how the objects under test actually come into existence:
 * the WordPress installer writes them before this plugin is ever active.
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * Behavior cluster: installer demo content vs production content.
 */
final class Demo_Content_RemovalTest extends WP_UnitTestCase {

	/**
	 * Protects the observable failure of D-02: after the plugin takes over
	 * an install, the demo entry is no longer listed where visitors read
	 * the blog, and the real entry is.
	 */
	public function test_installer_demo_entry_is_not_publicly_listed_after_activation() {
		$this->given_a_wordpress_install_with_demo_content();
		$real_entry = $this->given_an_imported_entry( 'sangha-refugio-hiperconexion' );

		cdd_core_activate();

		$listed = wp_list_pluck( ( new WP_Query( array( 'post_type' => 'post' ) ) )->posts, 'post_name' );

		$this->assertSame( array( 'sangha-refugio-hiperconexion' ), $listed );
		$this->assertSame( 'publish', get_post_status( $real_entry ) );
	}

	/**
	 * Protects the two remaining installer objects: neither the sample page
	 * nor the installer's privacy draft stays a published object.
	 */
	public function test_installer_demo_pages_are_not_published_after_activation() {
		$demos = $this->given_a_wordpress_install_with_demo_content();

		cdd_core_activate();

		$this->assertNotSame( 'publish', get_post_status( $demos['sample_page'] ) );
		$this->assertNotSame( 'publish', get_post_status( $demos['privacy_policy'] ) );
	}

	/**
	 * Protects an install that is already running the plugin: the versioned
	 * upgrade pass cleans the demo content too, without a reactivation.
	 */
	public function test_versioned_upgrade_unpublishes_installer_demo_content() {
		$demos = $this->given_a_wordpress_install_with_demo_content();
		update_option( 'cdd_core_version', '0.0.1' );

		cdd_core_maybe_upgrade();

		$this->assertNotSame( 'publish', get_post_status( $demos['hello_world'] ) );
		$this->assertNotSame( 'publish', get_post_status( $demos['sample_page'] ) );
	}

	/**
	 * Protects production content above everything else (ADR 0033): the
	 * imported /privacidad Page and the imported entries survive the pass
	 * untouched.
	 */
	public function test_imported_content_survives_the_activation_pass() {
		$privacidad = $this->given_an_imported_page( 'privacidad' );
		$entry      = $this->given_an_imported_entry( 'circulos-de-presencia-consciente' );

		cdd_core_activate();

		$this->assertSame( 'publish', get_post_status( $privacidad ) );
		$this->assertSame( 'publish', get_post_status( $entry ) );
	}

	/**
	 * Protects the dry run (ADR 0033): the command reports what it would
	 * remove and removes nothing until asked.
	 */
	public function test_purge_dry_run_reports_the_demo_content_without_removing_it() {
		$demos = $this->given_a_wordpress_install_with_demo_content();

		$report = cdd_core_purge_installer_demo_content( false );

		$this->assertTrue( $report['dry_run'] );
		$this->assertSame( array(), $report['removed'] );
		$this->assertSame(
			array( 'hello-world', 'privacy-policy', 'sample-page' ),
			$this->sorted_slugs( $report['found'] )
		);
		$this->assertInstanceOf( WP_Post::class, get_post( $demos['hello_world'] ) );
	}

	/**
	 * Protects the purge itself: applying removes the three installer
	 * objects and nothing else.
	 */
	public function test_purge_removes_only_installer_demo_content() {
		$demos      = $this->given_a_wordpress_install_with_demo_content();
		$privacidad = $this->given_an_imported_page( 'privacidad' );
		$entry      = $this->given_an_imported_entry( 'sangha-refugio-hiperconexion' );

		$report = cdd_core_purge_installer_demo_content( true );

		$this->assertCount( 3, $report['removed'] );
		$this->assertNull( get_post( $demos['hello_world'] ) );
		$this->assertNull( get_post( $demos['sample_page'] ) );
		$this->assertNull( get_post( $demos['privacy_policy'] ) );
		$this->assertInstanceOf( WP_Post::class, get_post( $privacidad ) );
		$this->assertInstanceOf( WP_Post::class, get_post( $entry ) );
	}

	/**
	 * Protects idempotence: a second pass over a clean install is a no-op,
	 * exactly like a second `import --apply`.
	 */
	public function test_second_purge_removes_nothing() {
		$this->given_a_wordpress_install_with_demo_content();
		cdd_core_purge_installer_demo_content( true );

		$report = cdd_core_purge_installer_demo_content( true );

		$this->assertSame( array(), $report['found'] );
		$this->assertSame( array(), $report['removed'] );
	}

	/**
	 * Protects the site setting: WordPress points its privacy policy option
	 * at the installer draft, and that reference must not dangle once the
	 * draft is gone.
	 */
	public function test_purge_clears_the_privacy_policy_option_pointing_at_the_demo() {
		$this->given_a_wordpress_install_with_demo_content();

		cdd_core_purge_installer_demo_content( true );

		$this->assertSame( 0, (int) get_option( 'wp_page_for_privacy_policy' ) );
	}

	/**
	 * Protects the production guard (ADR 0033): a deleting command refuses
	 * to run against production without explicit confirmation and backup
	 * evidence.
	 */
	public function test_purge_refuses_production_without_confirmation_and_backup_evidence() {
		$demos = $this->given_a_wordpress_install_with_demo_content();

		$report = cdd_core_purge_installer_demo_content( true, array( 'environment' => 'production' ) );

		$this->assertNotSame( '', $report['error'] );
		$this->assertSame( array(), $report['removed'] );
		$this->assertInstanceOf( WP_Post::class, get_post( $demos['hello_world'] ) );
	}

	/**
	 * Protects the cleanup query from third-party `the_posts` filters
	 * (and the other WP_Query result filters `suppress_filters` gates)
	 * that would otherwise hide the objects this pass must unpublish or purge.
	 */
	public function test_installer_demo_query_ignores_third_party_query_filters() {
		$demos = $this->given_a_wordpress_install_with_demo_content();

		$hide = static function ( array $posts ): array {
			return array_values(
				array_filter(
					$posts,
					static function ( $post ) {
						return 'hello-world' !== $post->post_name;
					}
				)
			);
		};
		add_filter( 'the_posts', $hide );

		$found = wp_list_pluck( cdd_core_installer_demo_posts(), 'ID' );

		remove_filter( 'the_posts', $hide );

		$expected = array( $demos['hello_world'], $demos['sample_page'], $demos['privacy_policy'] );
		sort( $found );
		sort( $expected );

		$this->assertSame( $expected, $found );
	}

	/**
	 * Creates the three objects a fresh WordPress install leaves behind and
	 * points the privacy option at the draft, as the installer does.
	 */
	private function given_a_wordpress_install_with_demo_content(): array {
		$demos = array(
			'hello_world'    => $this->install_default( 'post', 'hello-world', 'Hello world!', 'publish' ),
			'sample_page'    => $this->install_default( 'page', 'sample-page', 'Sample Page', 'publish' ),
			'privacy_policy' => $this->install_default( 'page', 'privacy-policy', 'Privacy Policy', 'draft' ),
		);

		update_option( 'wp_page_for_privacy_policy', $demos['privacy_policy'] );

		return $demos;
	}

	/**
	 * Inserts one installer default. The publication guard is detached for
	 * the insert because the installer writes these objects before the
	 * plugin exists, and the guard would otherwise draft the demo entry and
	 * hide the very failure under test.
	 *
	 * @param string $post_type Post type.
	 * @param string $slug      Default slug.
	 * @param string $title     Default title.
	 * @param string $status    Status the installer leaves it in.
	 */
	private function install_default( string $post_type, string $slug, string $title, string $status ): int {
		remove_filter( 'wp_insert_post_data', 'cdd_core_guard_post_publish', 10 );

		$post_id = self::factory()->post->create(
			array(
				'post_type'   => $post_type,
				'post_name'   => $slug,
				'post_title'  => $title,
				'post_status' => $status,
			)
		);

		add_filter( 'wp_insert_post_data', 'cdd_core_guard_post_publish', 10, 2 );

		return $post_id;
	}

	/**
	 * An imported Page, carrying the importer's source key.
	 *
	 * @param string $slug Page slug.
	 */
	private function given_an_imported_page( string $slug ): int {
		return self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_name'   => $slug,
				'post_status' => 'publish',
				'meta_input'  => array( Cdd_Core_Importer::SOURCE_KEY_META => 'pages/' . $slug ),
			)
		);
	}

	/**
	 * An imported blog entry with its published author card, as the
	 * importer creates it.
	 *
	 * @param string $slug Entry slug.
	 */
	private function given_an_imported_entry( string $slug ): int {
		$author = self::factory()->post->create(
			array(
				'post_type'   => 'blog_author',
				'post_name'   => 'comunidad-camino-del-dharma',
				'post_status' => 'publish',
			)
		);

		return self::factory()->post->create(
			array(
				'post_type'   => 'post',
				'post_name'   => $slug,
				'post_status' => 'publish',
				'meta_input'  => array(
					'authors'                          => array( $author ),
					Cdd_Core_Importer::SOURCE_KEY_META => 'posts/' . $slug,
				),
			)
		);
	}

	/**
	 * The slugs of a report entry list, sorted for a stable assertion.
	 *
	 * @param array $found Report entries.
	 */
	private function sorted_slugs( array $found ): array {
		$slugs = wp_list_pluck( $found, 'slug' );
		sort( $slugs );

		return $slugs;
	}
}
