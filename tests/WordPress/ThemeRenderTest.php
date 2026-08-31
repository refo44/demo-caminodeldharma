<?php
/**
 * Level 2: the real FSE views over a live WordPress (WU-07).
 *
 * Written RED-first. The calendar assertion diffs the rendered block
 * against the published grid markup of static/eventos/index.html
 * (OWN-007): the theme must paint exactly what production paints.
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * Behavior cluster: theme dynamic blocks and template resolution.
 */
final class ThemeRenderTest extends WP_UnitTestCase {

	/**
	 * Protects the block set of docs/12 §2/§11.3: every view that core
	 * blocks cannot express is a registered dynamic block of the theme.
	 */
	public function test_theme_dynamic_blocks_are_registered() {
		$registry = WP_Block_Type_Registry::get_instance();

		$expected = array(
			'camino-del-dharma/eventos-calendar',
			'camino-del-dharma/eventos-listado',
			'camino-del-dharma/evento-destacado',
			'camino-del-dharma/evento-tipo',
			'camino-del-dharma/evento-meta',
			'camino-del-dharma/evento-cta',
			'camino-del-dharma/entrada-cabecera',
			'camino-del-dharma/blog-listado',
			'camino-del-dharma/blog-recientes',
			'camino-del-dharma/autor-ficha',
			'camino-del-dharma/album-galeria',
			'camino-del-dharma/evento-acciones',
			'camino-del-dharma/entrada-compartir',
		);

		foreach ( $expected as $name ) {
			$this->assertTrue( $registry->is_registered( $name ), $name );
		}
	}

	/**
	 * Protects the published calendar contract byte-for-byte (modulo
	 * insignificant whitespace): the same month data must produce the same
	 * grid the static page publishes — weekday headers, empty lead cells,
	 * filled event days with tooltip/aria, practice Mondays, touch hint.
	 */
	public function test_calendar_renderer_matches_the_published_september_grid() {
		$calendar = new Cdd_Core_Calendar_Data();
		$data     = $calendar->build(
			'2026-09',
			array(
				array(
					'title' => 'Círculos de Presencia Consciente',
					'url'   => '#circulos-de-presencia-consciente',
					'dates' => array( '2026-09-03', '2026-09-10', '2026-09-15', '2026-09-17', '2026-09-22', '2026-09-24', '2026-09-29' ),
				),
			),
			'/practica/meditacion-semanal-en-linea',
			'Meditación semanal en línea'
		);

		$static_html = file_get_contents( dirname( __DIR__, 2 ) . '/static/eventos/index.html' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repository fixture read inside the ephemeral harness.
		preg_match( '#<section class="eventos-calendar.*?</section>#s', $static_html, $match );

		$this->assertSame(
			$this->normalize( $match[0] ),
			$this->normalize( Camino_Del_Dharma_Renderers::calendar( $data ) )
		);
	}

	/**
	 * Protects the archive block wiring: the calendar block renders the
	 * current month grid at request time and enqueues its tooltip script.
	 */
	public function test_calendar_block_renders_and_enqueues_tooltips() {
		$this->create_event(
			'evento-futuro',
			gmdate( 'Y-m-d', strtotime( '+10 days' ) ),
			gmdate( 'Y-m-d', strtotime( '+11 days' ) )
		);

		$html = do_blocks( '<!-- wp:camino-del-dharma/eventos-calendar /-->' );

		$this->assertStringContainsString( 'eventos-calendar-grid', $html );
		$this->assertStringContainsString( 'has-event', $html );
		$this->assertStringContainsString( 'href="#evento-futuro"', $html, 'Cells link to the listing card anchor, as published.' );
		$this->assertTrue( wp_script_is( 'camino-del-dharma-calendar', 'enqueued' ) );
	}

	/**
	 * Protects doc 03 §3 listing rules: current events keep the full card
	 * (type label, poster, meta list, detail link, live CTA); completed
	 * events group by year, newest first, as compact cards with the
	 * finalized badge and no signup or calendar affordances.
	 */
	public function test_events_listing_separates_current_and_past_events() {
		$now = new DateTimeImmutable( '2026-09-10 09:00:00', new DateTimeZone( 'America/Bogota' ) );

		$current_id = $this->create_event(
			'circulos-de-presencia-consciente',
			'2026-09-03',
			'2026-10-24',
			array(
				'event_place'      => 'Bogotá y Cali',
				'event_modality'   => 'Híbrida',
				'event_signup_url' => 'https://example.test/preinscripcion',
			)
		);
		wp_set_object_terms( $current_id, 'Curso', 'event_type' );

		$past_2026 = $this->create_event( 'encuentro-nacional-2026', '2026-08-07', '2026-08-09' );
		wp_set_object_terms( $past_2026, 'Retiro', 'event_type' );
		wp_set_object_terms( $past_2026, 'Puerto Colombia', 'event_city' );

		$past_2025 = $this->create_event( '6-encuentro-nacional-2025', '2025-08-16', '2025-08-18' );

		$html = Camino_Del_Dharma_Renderers::events_listing(
			cdd_core_current_events( $now ),
			cdd_core_past_events( $now )
		);

		$this->assertStringContainsString( 'Próximos eventos', $html );
		$this->assertStringContainsString( 'Eventos realizados', $html );

		// Current card: full treatment.
		$this->assertStringContainsString( 'id="circulos-de-presencia-consciente"', $html );
		$this->assertStringContainsString( 'data-event-status="vigente"', $html );
		$this->assertStringContainsString( '<p class="evento-type">Curso</p>', $html );
		$this->assertStringContainsString( '<dt>Fecha</dt>', $html );
		$this->assertStringContainsString( '<dd>Septiembre – octubre 2026</dd>', $html );
		$this->assertStringContainsString( '<dt>Lugar</dt>', $html );
		$this->assertStringContainsString( 'Ver evento →', $html );
		$this->assertStringContainsString( 'https://example.test/preinscripcion', $html );
		$this->assertStringContainsString( 'Preinscribirme', $html );

		// Past cards: compact, badged, grouped by year, newest year first.
		$this->assertStringContainsString( 'evento-card--compact', $html );
		$this->assertStringContainsString( 'Evento finalizado', $html );
		$this->assertStringContainsString( '<h3 class="eventos-anio" id="eventos-2026">2026</h3>', $html );
		$this->assertStringContainsString( '<h3 class="eventos-anio" id="eventos-2025">2025</h3>', $html );
		$this->assertLessThan(
			strpos( $html, 'id="eventos-2025"' ),
			strpos( $html, 'id="eventos-2026"' ),
			'2026 group renders before 2025.'
		);
		$this->assertStringContainsString( 'Puerto Colombia', $html );

		// No signup affordance travels into the archive of completed events.
		$past_section = substr( $html, strpos( $html, 'Eventos realizados' ) );
		$this->assertStringNotContainsString( 'Preinscribirme', $past_section );
		$this->assertStringNotContainsString( 'Inscribirme', $past_section );
	}

	/**
	 * Protects the home note (doc 03 §3): kicker with the type, poster at
	 * medium size linking to the single, calibrated date plus cities, and
	 * «Ver evento» — or nothing at all without a current event.
	 */
	public function test_featured_event_aside_and_its_empty_state() {
		$now = new DateTimeImmutable( '2026-09-10 09:00:00', new DateTimeZone( 'America/Bogota' ) );

		$event_id = $this->create_event(
			'circulos-de-presencia-consciente',
			'2026-09-03',
			'2026-10-24',
			array( 'event_featured' => '1' )
		);
		wp_set_object_terms( $event_id, 'Curso', 'event_type' );
		wp_set_object_terms( $event_id, array( 'Bogotá', 'Cali' ), 'event_city' );

		$html = Camino_Del_Dharma_Renderers::featured_event( cdd_core_featured_home_event( $now ) );

		$this->assertStringContainsString( '<aside class="home-featured-event"', $html );
		$this->assertStringContainsString( 'Próximo evento · Curso', $html );
		$this->assertStringContainsString( 'home-featured-event-title', $html );
		$this->assertStringContainsString( 'Septiembre – octubre 2026<br>Bogotá y Cali', $html );
		$this->assertStringContainsString( 'Ver evento', $html );

		$this->assertSame( '', Camino_Del_Dharma_Renderers::featured_event( null ), 'No current event renders no module at all.' );
	}

	/**
	 * Protects the single-event helper blocks: the type label, the meta
	 * list from real meta, and the CTA that only current events render.
	 */
	public function test_event_type_meta_and_cta_blocks() {
		$event_id = $this->create_event(
			'meditacion-presencial-barranquilla',
			'2026-07-09',
			null,
			array(
				'event_place'      => 'Barranquilla – Calle 71 # 39-117',
				'event_modality'   => 'Presencial en Barranquilla con guía virtual en vivo',
				'event_signup_url' => 'https://example.test/inscripcion',
			)
		);
		wp_set_object_terms( $event_id, 'Meditación', 'event_type' );
		$event = get_post( $event_id );

		$this->assertSame( '<p class="evento-type">Meditación</p>', Camino_Del_Dharma_Renderers::event_type_label( $event ) );

		$meta = Camino_Del_Dharma_Renderers::event_meta( $event );
		$this->assertStringContainsString( '<dl class="evento-meta">', $meta );
		$this->assertStringContainsString( '<dd>Jueves 9 de julio de 2026</dd>', $meta );
		$this->assertStringContainsString( '<dd>Barranquilla – Calle 71 # 39-117</dd>', $meta );
		$this->assertStringContainsString( '<dt>Modalidad</dt>', $meta );

		$cta = Camino_Del_Dharma_Renderers::event_cta( $event, true );
		$this->assertStringContainsString( 'https://example.test/inscripcion', $cta );
		$this->assertStringContainsString( 'btn btn-primary', $cta );

		$this->assertSame( '', Camino_Del_Dharma_Renderers::event_cta( $event, false ), 'Completed events render no signup CTA (OWN-012).' );
	}

	/**
	 * Protects ADR 0037 rendering: the entry header carries the deck, the
	 * «Por …» byline linked to the published blog_author profiles, each
	 * bio, and the reading time.
	 */
	public function test_entry_header_renders_deck_byline_and_reading_time() {
		$author_id = self::factory()->post->create(
			array(
				'post_type'    => 'blog_author',
				'post_name'    => 'zheng-gong',
				'post_title'   => 'Zheng Gong',
				'post_content' => 'Maestro budista de las tradiciones Chan y Tierra Pura.',
			)
		);

		$post_id = self::factory()->post->create(
			array(
				'post_title'   => 'Estamos conectados, pero seguimos solos',
				'post_excerpt' => 'La Sangha como refugio en tiempos de hiperconexión',
				'post_content' => str_repeat( 'palabra ', 1043 ),
				'meta_input'   => array( 'authors' => array( $author_id ) ),
			)
		);

		$html = Camino_Del_Dharma_Renderers::entry_header( get_post( $post_id ) );

		$this->assertStringContainsString( '<p class="article-deck">La Sangha como refugio en tiempos de hiperconexión</p>', $html );
		$this->assertStringContainsString( 'Por <a href="' . esc_url( get_permalink( $author_id ) ) . '">Zheng Gong</a>', $html );
		$this->assertStringContainsString( 'Maestro budista de las tradiciones Chan y Tierra Pura.', $html );
		$this->assertStringContainsString( 'Tiempo de lectura: 5 minutos', $html );
	}

	/**
	 * Protects the listing views of the blog: /blog items and the home
	 * «Del blog» cards render the published card structure, the home
	 * excerpt closing with the byline voice («… Por X.»).
	 */
	public function test_blog_listing_and_home_cards() {
		$author_id = self::factory()->post->create(
			array(
				'post_type'  => 'blog_author',
				'post_name'  => 'comunidad-camino-del-dharma',
				'post_title' => 'Comunidad Camino del Dharma',
			)
		);
		$post_id   = self::factory()->post->create(
			array(
				'post_title'   => 'Círculos de Presencia Consciente',
				'post_excerpt' => 'Un espacio para detenernos, escucharnos y aprender a cuidar la vida en comunidad.',
				'meta_input'   => array( 'authors' => array( $author_id ) ),
			)
		);
		$posts     = array( get_post( $post_id ) );

		$list = Camino_Del_Dharma_Renderers::blog_list( $posts );
		$this->assertStringContainsString( '<ul class="blog-list" role="list">', $list );
		$this->assertStringContainsString( '<h2 class="blog-list-title">Círculos de Presencia Consciente</h2>', $list );
		$this->assertStringContainsString( 'Un espacio para detenernos', $list );

		$cards = Camino_Del_Dharma_Renderers::home_blog_cards( $posts );
		$this->assertStringContainsString( '<ul class="home-blog-grid" role="list">', $cards );
		$this->assertStringContainsString( '<h3 class="home-blog-title">Círculos de Presencia Consciente</h3>', $cards );
		$this->assertStringContainsString( 'en comunidad. Por Comunidad Camino del Dharma.</p>', $cards );
	}

	/**
	 * Protects the author profile view (ADR 0037): name, bio, and only the
	 * posts related through the authors meta — never post_author.
	 */
	public function test_author_profile_lists_only_related_posts() {
		$author_id = self::factory()->post->create(
			array(
				'post_type'    => 'blog_author',
				'post_name'    => 'zheng-gong',
				'post_title'   => 'Zheng Gong',
				'post_content' => 'Maestro budista.',
			)
		);
		$other_id  = self::factory()->post->create(
			array(
				'post_type'  => 'blog_author',
				'post_name'  => 'otra-ficha',
				'post_title' => 'Otra Ficha',
			)
		);

		$related_id = self::factory()->post->create(
			array(
				'post_title' => 'Entrada relacionada',
				'meta_input' => array( 'authors' => array( $author_id ) ),
			)
		);
		self::factory()->post->create(
			array(
				'post_title' => 'Entrada ajena',
				'meta_input' => array( 'authors' => array( $other_id ) ),
			)
		);

		$related = cdd_core_posts_by_blog_author( $author_id );
		$this->assertSame( array( $related_id ), wp_list_pluck( $related, 'ID' ) );

		$html = Camino_Del_Dharma_Renderers::author_profile( get_post( $author_id ), $related );
		$this->assertStringContainsString( '<h1>Zheng Gong</h1>', $html );
		$this->assertStringContainsString( 'Maestro budista.', $html );
		$this->assertStringContainsString( 'Entradas de Zheng Gong', $html );
		$this->assertStringContainsString( 'Entrada relacionada', $html );
		$this->assertStringNotContainsString( 'Entrada ajena', $html );
	}

	/**
	 * Protects ADR 0036/0021: the album term view renders a native
	 * Gutenberg gallery of the term's attachments, with the hub back link.
	 */
	public function test_album_view_renders_a_native_gallery() {
		$term = wp_insert_term( 'General', 'gallery_album', array( 'slug' => 'general' ) );

		$attachment_id = self::factory()->attachment->create_object(
			array(
				'file'           => '2026/08/galeria-01.jpg',
				'post_mime_type' => 'image/jpeg',
				'post_title'     => 'galeria-01',
			)
		);
		wp_set_object_terms( $attachment_id, (int) $term['term_id'], 'gallery_album' );

		$html = Camino_Del_Dharma_Renderers::album_gallery(
			get_term( $term['term_id'], 'gallery_album' ),
			array( get_post( $attachment_id ) )
		);

		$this->assertStringContainsString( 'wp-block-gallery', $html );
		$this->assertStringContainsString( 'wp-image-' . $attachment_id, $html );
		$this->assertStringContainsString( 'General', $html );
		$this->assertStringContainsString( 'Volver a la galería', $html );
	}

	/**
	 * Protects docs/12 §6: WordPress resolves the real template files of
	 * the active theme for every mapped view.
	 */
	public function test_block_templates_resolve_for_the_mapped_views() {
		foreach ( array( 'front-page', 'archive-event', 'single-event', 'home', 'single', 'single-blog_author', 'taxonomy-gallery_album', 'page-comunidad', '404' ) as $slug ) {
			$template = get_block_template( 'camino-del-dharma//' . $slug );
			$this->assertNotNull( $template, $slug );
			$this->assertNotEmpty( $template->content, $slug );
		}
	}

	/**
	 * Protects the chrome contract: the header/footer patterns register and
	 * render the static mockup's structure with generated URLs.
	 */
	public function test_header_and_footer_patterns_render_the_static_chrome() {
		$registry = WP_Block_Patterns_Registry::get_instance();
		$this->assertTrue( $registry->is_registered( 'camino-del-dharma/header' ) );
		$this->assertTrue( $registry->is_registered( 'camino-del-dharma/footer' ) );

		$header = do_blocks( '<!-- wp:pattern {"slug":"camino-del-dharma/header"} /-->' );
		$this->assertStringContainsString( 'class="skip-link"', $header );
		$this->assertStringContainsString( 'id="nav-toggle"', $header );
		$this->assertStringContainsString( 'id="nav-menus"', $header );
		$this->assertStringContainsString( esc_url( home_url( '/comunidad' ) ), $header );
		$this->assertStringContainsString( 'Camino del Dharma', $header );

		$footer = do_blocks( '<!-- wp:pattern {"slug":"camino-del-dharma/footer"} /-->' );
		$this->assertStringContainsString( 'footer-donate', $footer );
		$this->assertStringContainsString( '220065151425', $footer );
		$this->assertStringContainsString( esc_url( home_url( '/privacidad' ) ), $footer );
	}

	/**
	 * Protects the published event actions (WU-08A): a current event
	 * offers «Añadir al calendario» and «Compartir», the calendar trigger
	 * carries the same payload as the generated .ics, and the share
	 * trigger points at the stored message templates.
	 */
	public function test_event_actions_block_renders_the_published_triggers() {
		$event = $this->create_event(
			'circulos',
			gmdate( 'Y-m-d', strtotime( '+10 days' ) ),
			gmdate( 'Y-m-d', strtotime( '+20 days' ) ),
			array(
				'event_place'    => 'Bogotá y Cali',
				'share_whatsapp' => "Comparto esta invitación:\n\n{{SHARE_URL}}",
				'share_x'        => 'Curso · Camino del Dharma',
			)
		);
		$this->go_to_event( $event );

		$html = do_blocks( '<!-- wp:camino-del-dharma/evento-acciones /-->' );

		$this->assertStringContainsString( 'class="evento-actions"', $html );
		$this->assertStringContainsString( 'calendar-trigger', $html );
		$this->assertStringContainsString( 'Añadir al calendario', $html );
		$this->assertStringContainsString( 'data-calendar-location="Bogotá y Cali"', $html );
		$this->assertStringContainsString( 'data-calendar-ics="' . esc_attr( home_url( '/eventos/ical/circulos.ics' ) ) . '"', $html );
		$this->assertStringContainsString( 'data-calendar-start="' . gmdate( 'Ymd', strtotime( '+10 days' ) ) . '"', $html );
		$this->assertStringContainsString( 'data-calendar-end="' . gmdate( 'Ymd', strtotime( '+21 days' ) ) . '"', $html );

		$this->assertStringContainsString( 'share-trigger', $html );
		$this->assertStringContainsString( 'data-share-whatsapp-template="whatsapp-circulos"', $html );
		$this->assertStringContainsString( '<template id="whatsapp-circulos">', $html );
		$this->assertStringContainsString( '{{SHARE_URL}}', $html );
		$this->assertStringContainsString( '<template id="x-circulos">', $html );
		$this->assertStringNotContainsString( 'data-share-threads-template', $html, 'No stored copy, no dangling template reference.' );

		$this->assertTrue( wp_script_is( 'camino-del-dharma-share', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'camino-del-dharma-calendar-dialog', 'enqueued' ) );
	}

	/**
	 * Protects OWN-012 for behavior too: a completed event never invites
	 * anyone to add it to a calendar or to share it — exactly as the
	 * published past-event singles do.
	 */
	public function test_event_actions_block_is_silent_for_completed_events() {
		$event = $this->create_event( 'pasado', '2020-01-01', '2020-01-02' );
		$this->go_to_event( $event );

		$this->assertSame( '', do_blocks( '<!-- wp:camino-del-dharma/evento-acciones /-->' ) );
	}

	/**
	 * Protects the blog share control: every entry offers it, with the
	 * stored templates when the editor wrote them.
	 */
	public function test_blog_share_block_renders_the_published_trigger() {
		$post = self::factory()->post->create(
			array(
				'post_name'  => 'sangha-refugio-hiperconexion',
				'post_title' => 'Estamos conectados, pero seguimos solos',
				'meta_input' => array( 'share_whatsapp' => "Reflexión\n\n{{SHARE_URL}}" ),
			)
		);
		$this->go_to( get_permalink( $post ) );
		$GLOBALS['post'] = get_post( $post );

		$html = do_blocks( '<!-- wp:camino-del-dharma/entrada-compartir /-->' );

		$this->assertStringContainsString( 'class="share-actions"', $html );
		$this->assertStringContainsString( 'data-share-title="Estamos conectados, pero seguimos solos"', $html );
		$this->assertStringContainsString( 'data-share-url="' . esc_attr( get_permalink( $post ) ) . '"', $html );
		$this->assertStringContainsString( '<template id="whatsapp-sangha-refugio-hiperconexion">', $html );
		$this->assertTrue( wp_script_is( 'camino-del-dharma-share', 'enqueued' ) );
	}

	/**
	 * Protects the published listing: the current-event card carries the
	 * same two controls, while the compact past cards carry none.
	 */
	public function test_events_listing_carries_the_actions_for_current_events_only() {
		$current = $this->create_event( 'vigente', gmdate( 'Y-m-d', strtotime( '+5 days' ) ), null );
		$past    = $this->create_event( 'finalizado', '2020-01-01', '2020-01-02' );

		$html = Camino_Del_Dharma_Renderers::events_listing( array( get_post( $current ) ), array( get_post( $past ) ) );

		$this->assertSame( 1, substr_count( $html, 'calendar-trigger' ) );
		$this->assertSame( 1, substr_count( $html, 'share-trigger' ) );
		$this->assertStringContainsString( 'data-calendar-event-url="' . esc_attr( get_permalink( $current ) ) . '"', $html );

		$past_card = substr( $html, (int) strpos( $html, 'eventos-realizados-heading' ) );
		$this->assertStringNotContainsString( 'share-trigger', $past_card );
	}

	/**
	 * Protects the accessible name of the mantra players (docs/19): the
	 * native audio block has no aria-label attribute, so the theme adds
	 * the published one from the caption.
	 */
	public function test_audio_block_takes_its_accessible_name_from_the_caption() {
		$html = do_blocks(
			'<!-- wp:audio {"id":41,"className":"mantra-audio","preload":"metadata"} -->' . "\n" .
			'<figure class="wp-block-audio mantra-audio"><audio controls src="https://example.test/amitabha.mp3" preload="metadata"></audio>' .
			'<figcaption class="wp-element-caption">Recitación de Amitābha.</figcaption></figure>' . "\n" .
			'<!-- /wp:audio -->'
		);

		$this->assertStringContainsString( 'aria-label="Recitación de Amitābha"', $html );
		$this->assertStringContainsString( 'preload="metadata"', $html );
	}

	/**
	 * Puts the main query on one event single.
	 *
	 * @param int $event Event post ID.
	 */
	private function go_to_event( int $event ) {
		$this->go_to( get_permalink( $event ) );
		$GLOBALS['post'] = get_post( $event );
	}

	/**
	 * Creates a published event with dates and extra meta.
	 *
	 * @param string      $slug  Post slug.
	 * @param string|null $start event_date meta.
	 * @param string|null $end   event_end meta.
	 * @param array       $meta  Extra meta values.
	 */
	private function create_event( string $slug, ?string $start, ?string $end, array $meta = array() ): int {
		$meta_input = $meta;
		if ( null !== $start ) {
			$meta_input['event_date'] = $start;
		}
		if ( null !== $end ) {
			$meta_input['event_end'] = $end;
		}

		return self::factory()->post->create(
			array(
				'post_type'  => 'event',
				'post_name'  => $slug,
				'post_title' => $slug,
				'meta_input' => $meta_input,
			)
		);
	}

	/**
	 * Collapses insignificant whitespace so markup diffs compare structure
	 * and copy, not indentation.
	 *
	 * @param string $html Markup.
	 */
	private function normalize( string $html ): string {
		$html = preg_replace( '/>\s+</', '><', $html );
		$html = preg_replace( '/\s+/', ' ', $html );

		return trim( $html );
	}
}
