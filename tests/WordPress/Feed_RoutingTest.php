<?php
/**
 * Level 2: the native feed surface answers a real 404 (ADR 0044 /
 * OWN-025 / D-03) and the head no longer advertises RSS or Atom.
 *
 * Written RED-first. Published production answers 404 on /feed,
 * /blog/feed and /comments/feed, and those URLs are not in
 * docs/11-arbol-urls-final.md. Routes are driven as incoming requests
 * (ADR 0032), not through get_feed_link().
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * Behavior cluster: feed requests 404, public routes untouched.
 */
final class Feed_RoutingTest extends WP_UnitTestCase {

	/**
	 * Last status code WordPress sent for the request under test, or null
	 * when it sent none (a normal 200 response).
	 *
	 * @var int|null
	 */
	private $sent_status = null;

	/**
	 * Pretty permalinks mirroring the target site (posts under /blog,
	 * no trailing slash — ADR 0008). The domain objects are re-registered
	 * because WordPress only adds their permastructs when a permalink
	 * structure exists at registration time.
	 */
	public function set_up() {
		parent::set_up();
		$this->set_permalink_structure( '/blog/%postname%' );
		cdd_core_register_post_types();
		cdd_core_register_taxonomies();
		cdd_core_register_rewrites();
		flush_rewrite_rules();

		$this->sent_status = null;
		add_filter( 'status_header', array( $this, 'record_status' ), 10, 2 );
	}

	public function tear_down() {
		remove_filter( 'status_header', array( $this, 'record_status' ), 10 );

		parent::tear_down();
	}

	/**
	 * Records the status code WordPress sends for the current request.
	 *
	 * @param string $header Full status header.
	 * @param int    $code   Status code.
	 */
	public function record_status( $header, $code ) {
		$this->sent_status = (int) $code;

		return $header;
	}

	/**
	 * The site-wide aliases core registers (feed, rdf, rss, rss2, atom),
	 * pretty and query-string, plus the comment feed: every one is a real
	 * 404, never a feed document.
	 */
	public function test_native_site_feed_routes_return_404() {
		$routes = array(
			'/feed',
			'/feed/feed',
			'/feed/rdf',
			'/feed/rss',
			'/feed/rss2',
			'/feed/atom',
			'/rdf',
			'/rss',
			'/rss2',
			'/atom',
			'/comments/feed',
			'/comments/feed/atom',
			'/?feed=rss2',
			'/?feed=atom',
			'/?feed=rss2&withcomments=1',
		);

		foreach ( $routes as $route ) {
			$this->assert_route_is_a_real_404( $route );
		}
	}

	/**
	 * Content-scoped feeds — the posts page, a single entry's comment
	 * feed, the event archive and the author profile archive — are 404
	 * too. A 200 here would be a new indexable surface (ADR 0044).
	 */
	public function test_content_scoped_feed_routes_return_404() {
		$blog = self::factory()->post->create(
			array(
				'post_type' => 'page',
				'post_name' => 'blog',
			)
		);
		update_option( 'page_for_posts', $blog );

		$this->create_entry( 'estamos-conectados' );
		self::factory()->post->create(
			array(
				'post_type' => 'event',
				'post_name' => 'vesak-2026',
			)
		);
		self::factory()->post->create(
			array(
				'post_type' => 'blog_author',
				'post_name' => 'zheng-gong',
			)
		);

		$routes = array(
			'/blog/feed',
			'/blog/feed/atom',
			'/blog/estamos-conectados/feed',
			'/eventos/feed',
			'/eventos/vesak-2026/feed',
			'/author/feed',
			'/author/zheng-gong/feed',
		);

		foreach ( $routes as $route ) {
			$this->assert_route_is_a_real_404( $route );
		}
	}

	/**
	 * The block is scoped to feeds: the front page, a Page, an entry, an
	 * event single and the event archive still resolve normally.
	 */
	public function test_public_routes_still_resolve() {
		$page  = self::factory()->post->create(
			array(
				'post_type' => 'page',
				'post_name' => 'linaje',
			)
		);
		$post  = $this->create_entry( 'estamos-conectados' );
		$event = self::factory()->post->create(
			array(
				'post_type' => 'event',
				'post_name' => 'vesak-2026',
			)
		);

		$expectations = array(
			get_permalink( $page )                => 'is_page',
			get_permalink( $post )                => 'is_single',
			get_permalink( $event )               => 'is_single',
			get_post_type_archive_link( 'event' ) => 'is_post_type_archive',
		);

		foreach ( $expectations as $url => $assertion ) {
			$this->sent_status = null;
			$this->go_to( $url );

			$this->assertFalse( is_404(), $url . ' must not be a 404.' );
			$this->assertTrue( call_user_func( $assertion ), $url . ' must resolve as ' . $assertion . '.' );
			$this->assertNotSame( 404, $this->sent_status, $url . ' must not send a 404 status.' );
		}
	}

	/**
	 * The head of a public Page carries no RSS or Atom autodiscovery:
	 * nothing may announce a feed that answers 404 (ADR 0044 §2).
	 */
	public function test_public_head_has_no_rss_or_atom_autodiscovery() {
		$page = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_name'   => 'linaje',
				'post_status' => 'publish',
			)
		);

		$types = $this->alternate_link_types( get_permalink( $page ) );

		$this->assertNotContains( 'application/rss+xml', $types );
		$this->assertNotContains( 'application/atom+xml', $types );
		$this->assertNotContains( 'application/rdf+xml', $types );
	}

	/**
	 * An entry with comments open used to get its own comment-feed link
	 * (feed_links_extra); it must be gone as well.
	 */
	public function test_entry_head_has_no_comment_feed_autodiscovery() {
		$post = $this->create_entry( 'estamos-conectados', array( 'comment_status' => 'open' ) );

		$types = $this->alternate_link_types( get_permalink( $post ) );

		$this->assertNotContains( 'application/rss+xml', $types );
		$this->assertNotContains( 'application/atom+xml', $types );
	}

	/**
	 * OWN-014 guard: dropping the RSS autodiscovery must not touch the
	 * calendar alternate a current event prints for its generated .ics.
	 */
	public function test_current_event_head_keeps_its_calendar_alternate() {
		$event = self::factory()->post->create(
			array(
				'post_type'  => 'event',
				'post_name'  => 'evento-vigente',
				'meta_input' => array(
					'event_date' => gmdate( 'Y-m-d', strtotime( '+10 days' ) ),
					'event_end'  => gmdate( 'Y-m-d', strtotime( '+12 days' ) ),
				),
			)
		);

		$head  = $this->head_of( get_permalink( $event ) );
		$types = $this->alternate_link_types_in( $head );

		$this->assertContains( 'text/calendar', $types );
		$this->assertStringContainsString( '/eventos/ical/evento-vigente.ics', $head );
		$this->assertNotContains( 'application/rss+xml', $types );
	}

	/**
	 * Creates a published blog entry. A `post` only reaches publish with
	 * at least one published author profile (ADR 0037 §7), so the profile
	 * comes first and the relationship travels in the insert.
	 *
	 * @param string $slug Entry slug.
	 * @param array  $args Extra insert arguments.
	 */
	private function create_entry( string $slug, array $args = array() ): int {
		$author = self::factory()->post->create( array( 'post_type' => 'blog_author' ) );

		return self::factory()->post->create(
			array_merge(
				array(
					'post_name'  => $slug,
					'meta_input' => array( 'authors' => array( $author ) ),
				),
				$args
			)
		);
	}

	/**
	 * Drives an incoming route and asserts a real 404: the query is a 404,
	 * it is not a feed (no soft 404 with a feed body), no post was
	 * matched, and WordPress sent a 404 status.
	 *
	 * @param string $route Incoming path or query string.
	 */
	private function assert_route_is_a_real_404( string $route ) {
		$this->sent_status = null;
		$this->go_to( $route );

		$this->assertTrue( is_404(), $route . ' must be a 404.' );
		$this->assertFalse( is_feed(), $route . ' must not be served as a feed.' );
		$this->assertSame( 404, $this->sent_status, $route . ' must send a 404 status.' );
	}

	/**
	 * Renders the head WordPress would print for an incoming URL.
	 *
	 * @param string $url Incoming URL.
	 */
	private function head_of( string $url ): string {
		$this->go_to( $url );

		ob_start();
		wp_head();

		return (string) ob_get_clean();
	}

	/**
	 * The `type` of every `rel="alternate"` link an incoming URL prints.
	 *
	 * @param string $url Incoming URL.
	 */
	private function alternate_link_types( string $url ): array {
		return $this->alternate_link_types_in( $this->head_of( $url ) );
	}

	/**
	 * The `type` of every `rel="alternate"` link in a rendered head.
	 *
	 * @param string $head Rendered head.
	 */
	private function alternate_link_types_in( string $head ): array {
		preg_match_all( '/<link[^>]*rel=["\']alternate["\'][^>]*>/i', $head, $links );

		$types = array();
		foreach ( $links[0] as $link ) {
			if ( preg_match( '/type=["\']([^"\']+)["\']/i', $link, $type ) ) {
				$types[] = $type[1];
			}
		}

		return $types;
	}
}
