<?php
/**
 * Level 2: request-time SEO over a live WordPress (WU-08B).
 *
 * Written RED-first. The plugin owns *what* each request's head says;
 * these tests drive real queries (front page, Page, event single, entry,
 * author profile, author archive, album term, tag archive) and assert the
 * resolved document, the robots policy and the JSON-LD graph.
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * SEO cluster: request-time head resolution and robots policy.
 */
final class Seo_HeadTest extends WP_UnitTestCase {

	/**
	 * Registers meta and seeds the site SEO option the way the importer
	 * does, so the request-time resolver has the published defaults.
	 */
	public function set_up() {
		parent::set_up();
		cdd_core_register_meta();
		cdd_core_register_seo_meta();
		update_option(
			'cdd_core_seo_site',
			array(
				'seo'    => array(
					'base'         => 'https://caminodeldharma.org',
					'site_name'    => 'Camino del Dharma',
					'locale'       => 'es_CO',
					'image'        => 'https://caminodeldharma.org/assets/images/og-default.jpg',
					'image_alt'    => 'Comunidad Buddhista Camino del Dharma',
					'image_width'  => '1200',
					'image_height' => '630',
					'twitter_card' => 'summary_large_image',
					'archives'     => array(
						'event' => array(
							'title'          => 'Eventos y Retiros Budistas en Colombia | Camino del Dharma',
							'description'    => 'Próximos cursos, encuentros, talleres y retiros budistas en Colombia.',
							'keywords'       => 'camino del dharma, budismo colombia',
							'og_title'       => 'Eventos y Retiros Budistas en Colombia | Camino del Dharma',
							'og_description' => 'Próximos cursos, encuentros, talleres y retiros budistas en Colombia.',
							'related'        => '',
						),
					),
				),
				'jsonld' => array(
					'home_graph' => array(
						array(
							'@type' => 'Organization',
							'@id'   => 'https://caminodeldharma.org/#organization',
							'name'  => 'Comunidad Buddhista Camino del Dharma',
							'url'   => 'https://caminodeldharma.org',
						),
						array(
							'@type' => 'WebSite',
							'@id'   => 'https://caminodeldharma.org/#website',
							'url'   => 'https://caminodeldharma.org',
						),
						array(
							'@type'       => 'WebPage',
							'@id'         => 'https://caminodeldharma.org/#webpage',
							'url'         => 'https://caminodeldharma.org',
							'name'        => 'Budismo Chan y Tierra Pura en Colombia | Camino del Dharma',
							'description' => 'Comunidad budista en Colombia.',
							'about'       => array(
								'@type' => 'Thing',
								'name'  => 'budismo en Colombia',
							),
							'inLanguage'  => 'es-CO',
						),
					),
				),
			)
		);
	}

	/**
	 * Protects the model: the published head copy is editable meta, not
	 * text regenerated from the post title.
	 */
	public function test_head_meta_is_registered_on_pages_posts_and_events() {
		foreach ( array( 'page', 'post', 'event' ) as $post_type ) {
			$registered = get_registered_meta_keys( 'post', $post_type );

			foreach ( array( 'seo_title', 'seo_description', 'seo_keywords', 'og_title', 'og_description' ) as $key ) {
				$this->assertArrayHasKey( $key, $registered, $post_type . '/' . $key );
				$this->assertTrue( $registered[ $key ]['show_in_rest'], $post_type . '/' . $key );
			}
		}
	}

	/**
	 * A Page prints its own published title and description, with the
	 * canonical rebased onto this environment's home URL.
	 */
	public function test_page_head_uses_the_stored_copy_and_the_local_canonical() {
		$page = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_name'  => 'linaje',
				'post_title' => 'El linaje',
				'meta_input' => array(
					'seo_title'       => 'Linaje Chan y Tierra Pura | Camino del Dharma',
					'seo_description' => 'El linaje Chan y Tierra Pura.',
					'og_title'        => 'Linaje Chan y Tierra Pura',
				),
			)
		);

		$this->go_to( get_permalink( $page ) );
		$context = cdd_core_seo_context();

		$this->assertSame( 'Linaje Chan y Tierra Pura | Camino del Dharma', $context['title'] );
		$this->assertSame( 'El linaje Chan y Tierra Pura.', $context['description'] );
		$this->assertSame( 'Linaje Chan y Tierra Pura', $context['og_title'] );
		$this->assertSame( get_permalink( $page ), $context['canonical'] );
		$this->assertStringStartsWith( 'index', $context['robots'] );
		$this->assertStringNotContainsString( 'caminodeldharma.org', $context['canonical'] );
	}

	/**
	 * A Page with no stored copy still gets a usable, honest head: the
	 * post title, and no invented description.
	 */
	public function test_page_without_stored_copy_falls_back_to_real_data() {
		$page = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_name'  => 'sin-seo',
				'post_title' => 'Sin SEO',
			)
		);

		$this->go_to( get_permalink( $page ) );
		$context = cdd_core_seo_context();

		$this->assertStringContainsString( 'Sin SEO', $context['title'] );
		$this->assertSame( '', $context['description'] );
	}

	/**
	 * The front page prints the published home graph, rebased, plus the
	 * WebPage node — and never a second Event object (doc 15 §12.3).
	 */
	public function test_front_page_graph_is_the_published_home_graph_rebased() {
		$front = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_name'  => 'inicio',
				'post_title' => 'Camino del Dharma',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $front );

		$this->go_to( home_url( '/' ) );
		$context = cdd_core_seo_context();

		$types = array_column( $context['jsonld'], '@type' );
		$this->assertSame( array( 'Organization', 'WebSite', 'WebPage' ), $types );
		$this->assertNotContains( 'Event', $types );
		$this->assertSame( home_url( '/#organization' ), $context['jsonld'][0]['@id'] );
		$this->assertStringNotContainsString( 'caminodeldharma.org', wp_json_encode( $context['jsonld'] ) );
	}

	/**
	 * A current event: Event JSON-LD, the calendar alternate link, and
	 * breadcrumbs Inicio → Eventos → evento.
	 */
	public function test_current_event_single_links_its_calendar_and_publishes_event_json_ld() {
		$event = $this->create_event(
			'evento-vigente',
			'+10 days',
			'+12 days',
			array(
				'event_signup_url' => 'https://forms.example/x',
				'seo_description'  => 'La descripción publicada.',
			)
		);

		$this->go_to( get_permalink( $event ) );
		$context = cdd_core_seo_context();

		$this->assertSame( home_url( '/eventos/ical/evento-vigente.ics' ), $context['alternate']['href'] );
		$this->assertSame( 'text/calendar', $context['alternate']['type'] );

		$graph = array_column( $context['jsonld'], null, '@type' );
		$this->assertArrayHasKey( 'BreadcrumbList', $graph );
		$this->assertArrayHasKey( 'Event', $graph );
		$this->assertSame( 'https://schema.org/EventScheduled', $graph['Event']['eventStatus'] );
		$this->assertSame( get_permalink( $event ), $graph['Event']['url'] );
		$this->assertSame( 'https://forms.example/x', $graph['Event']['offers']['url'] );
		$this->assertSame( 'La descripción publicada.', $graph['Event']['description'] );
		$this->assertCount( 3, $graph['BreadcrumbList']['itemListElement'] );
	}

	/**
	 * A completed event: EventCompleted, no offer, and no calendar link —
	 * the `.ics` route is 410 (OWN-012/OWN-013).
	 */
	public function test_completed_event_single_has_no_calendar_link_and_no_offer() {
		$event = $this->create_event( 'evento-pasado', '-20 days', '-18 days', array( 'event_signup_url' => 'https://forms.example/x' ) );

		$this->go_to( get_permalink( $event ) );
		$context = cdd_core_seo_context();

		$this->assertArrayNotHasKey( 'alternate', $context );

		$graph = array_column( $context['jsonld'], null, '@type' );
		$this->assertSame( 'https://schema.org/EventCompleted', $graph['Event']['eventStatus'] );
		$this->assertArrayNotHasKey( 'offers', $graph['Event'] );
	}

	/**
	 * ADR 0037: the entry's authors come from the `authors` relationship,
	 * never from the WP user, and the publisher is the site Organization.
	 */
	public function test_blog_single_publishes_blog_posting_with_profile_authors() {
		$author = self::factory()->post->create(
			array(
				'post_type'  => 'blog_author',
				'post_name'  => 'zheng-gong',
				'post_title' => 'Zheng Gong',
			)
		);
		$post   = self::factory()->post->create(
			array(
				'post_title'   => 'Estamos conectados, pero seguimos solos',
				'post_name'    => 'sangha-refugio-hiperconexion',
				'post_excerpt' => 'La Sangha como refugio.',
				'meta_input'   => array( 'authors' => array( $author ) ),
			)
		);

		$this->go_to( get_permalink( $post ) );
		$graph = array_column( cdd_core_seo_context()['jsonld'], null, '@type' );

		$this->assertSame( 'Thing', $graph['BlogPosting']['author'][0]['@type'] );
		$this->assertSame( get_permalink( $author ), $graph['BlogPosting']['author'][0]['url'] );
		$this->assertSame( home_url( '/#organization' ), $graph['BlogPosting']['publisher']['@id'] );
		$this->assertSame( get_permalink( $post ), $graph['BlogPosting']['mainEntityOfPage'] );
	}

	/**
	 * §9.5: an author profile emits `Thing` with its name and canonical
	 * profile URL, and stays indexable (ADR 0037).
	 */
	public function test_author_profile_is_indexable_and_emits_thing() {
		$author = self::factory()->post->create(
			array(
				'post_type'  => 'blog_author',
				'post_name'  => 'zheng-gong',
				'post_title' => 'Zheng Gong',
			)
		);

		$this->go_to( get_permalink( $author ) );
		$context = cdd_core_seo_context();
		$graph   = array_column( $context['jsonld'], null, '@type' );

		$this->assertStringStartsWith( 'index', $context['robots'] );
		$this->assertSame( 'Zheng Gong', $graph['Thing']['name'] );
		$this->assertSame( get_permalink( $author ), $graph['Thing']['url'] );
	}

	/**
	 * The three archives that stay out of the index until they have
	 * volume: /author, gallery album terms and blog tags — all of them
	 * `noindex, follow`, never `nofollow`.
	 */
	public function test_low_volume_archives_are_noindex_follow() {
		$album = self::factory()->term->create(
			array(
				'taxonomy' => 'gallery_album',
				'slug'     => '2023',
			)
		);
		$tag   = self::factory()->term->create(
			array(
				'taxonomy' => 'post_tag',
				'slug'     => 'sangha',
			)
		);
		self::factory()->post->create(
			array(
				'post_type' => 'blog_author',
				'post_name' => 'zheng-gong',
			)
		);
		self::factory()->post->create( array( 'tags_input' => array( 'sangha' ) ) );

		$routes = array(
			get_post_type_archive_link( 'blog_author' ),
			get_term_link( $album, 'gallery_album' ),
			get_term_link( $tag, 'post_tag' ),
		);

		foreach ( $routes as $route ) {
			$this->go_to( $route );
			$robots = cdd_core_seo_context()['robots'];

			$this->assertStringContainsString( 'noindex', $robots, $route );
			$this->assertStringContainsString( 'follow', $robots, $route );
			$this->assertStringNotContainsString( 'nofollow', $robots, $route );
		}
	}

	/**
	 * The `wp_robots` filter must agree with the resolved context: the
	 * header and the meta tag cannot disagree.
	 */
	public function test_wp_robots_filter_reflects_the_noindex_policy() {
		self::factory()->post->create(
			array(
				'post_type' => 'blog_author',
				'post_name' => 'zheng-gong',
			)
		);

		$this->go_to( get_post_type_archive_link( 'blog_author' ) );
		$robots = cdd_core_seo_robots( array( 'max-image-preview' => 'large' ) );

		$this->assertTrue( $robots['noindex'] );
		$this->assertTrue( $robots['follow'] );
		$this->assertArrayNotHasKey( 'index', $robots );
	}

	/**
	 * A 404 is never indexable and never advertises a canonical of its
	 * own (no soft 404s).
	 */
	public function test_not_found_is_noindex() {
		$this->go_to( home_url( '/no-existe-esta-ruta' ) );
		$context = cdd_core_seo_context();

		$this->assertStringContainsString( 'noindex', $context['robots'] );
	}

	/**
	 * The event archive has no post object: its published head travels as
	 * site data (the static /eventos page is not a WordPress Page).
	 */
	public function test_event_archive_uses_the_stored_archive_copy() {
		$this->create_event( 'evento-vigente', '+10 days', '+12 days' );

		$this->go_to( get_post_type_archive_link( 'event' ) );
		$context = cdd_core_seo_context();

		$this->assertSame( 'Eventos y Retiros Budistas en Colombia | Camino del Dharma', $context['title'] );
		$this->assertSame( get_post_type_archive_link( 'event' ), $context['canonical'] );
	}

	/**
	 * Creates an event relative to now.
	 *
	 * @param string $slug  Event slug.
	 * @param string $start Relative start.
	 * @param string $end   Relative end.
	 * @param array  $meta  Extra meta.
	 */
	private function create_event( string $slug, string $start, string $end, array $meta = array() ): int {
		$meta['event_date'] = gmdate( 'Y-m-d', strtotime( $start ) );
		$meta['event_end']  = gmdate( 'Y-m-d', strtotime( $end ) );

		return self::factory()->post->create(
			array(
				'post_type'  => 'event',
				'post_name'  => $slug,
				'post_title' => $slug,
				'meta_input' => $meta,
			)
		);
	}
}
