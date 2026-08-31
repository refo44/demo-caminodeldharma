<?php
/**
 * Level 2: the migration importer contract (ADR 0032 §8.2, ADR 0033):
 * dry-run by default, explicit apply, idempotent, create-missing-only,
 * source keys/hashes, wp-admin edits never overwritten, no deletions,
 * production guard.
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * Behavior cluster: validate/plan/import/verify over real WordPress.
 */
final class ImporterTest extends WP_UnitTestCase {

	/**
	 * Re-registers the domain meta (the suite tear_down unregisters every
	 * meta key after each test; sanitizers survive via the hook backup).
	 */
	public function set_up() {
		parent::set_up();
		cdd_core_register_meta();
	}

	/**
	 * Protects validation: a payload whose media file does not exist on
	 * disk, or whose post cites an unknown author, is rejected before
	 * anything touches the database.
	 */
	public function test_validate_rejects_missing_files_and_unknown_authors() {
		$payload = $this->payload(
			array(
				'media' => array( $this->media_object( 'assets/images/no-existe.jpg' ) ),
				'posts' => array( $this->post_object( array( 'authors' => array( 'autor-fantasma' ) ) ) ),
			)
		);

		$issues = $this->importer( $payload )->validate();

		$this->assertNotEmpty( $issues );
		$this->assertStringContainsString( 'no-existe.jpg', implode( ' ', $issues ) );
		$this->assertStringContainsString( 'autor-fantasma', implode( ' ', $issues ) );
	}

	/**
	 * Protects the real artifact: the committed payload validates cleanly
	 * against the repo's static tree — the reconciliation baseline the
	 * import will run from.
	 */
	public function test_committed_payload_validates_against_the_static_tree() {
		$payload = json_decode( file_get_contents( '/repo/migration/payload.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repo artifact inside the ephemeral harness.

		$this->assertSame( array(), $this->importer( $payload )->validate() );
		$this->assertSame( 10, $payload['counts']['events'] );
		$this->assertSame( 11, $payload['counts']['pages'] );
		$this->assertSame( 35, $payload['counts']['gallery_images'] );
	}

	/**
	 * Protects dry-run-by-default (ADR 0033): without apply, import only
	 * reports the plan and writes nothing.
	 */
	public function test_import_without_apply_writes_nothing() {
		$report = $this->importer( $this->small_payload() )->import( false );

		$this->assertTrue( $report['dry_run'] );
		$this->assertSame( 0, (int) wp_count_posts( 'event' )->publish );
		$this->assertSame( 0, (int) wp_count_posts( 'blog_author' )->publish );
		$this->assertNull( get_page_by_path( 'practica' ) );
	}

	/**
	 * Protects the apply path end to end on a small real-file payload:
	 * profiles, media with alt text, album terms on attachments, events
	 * with meta/terms/poster, posts with the authors relation and pages
	 * with hierarchy and rewritten media URLs.
	 */
	public function test_import_apply_creates_the_wordpress_objects() {
		$importer = $this->importer( $this->small_payload() );
		$report   = $importer->import( true );

		$this->assertFalse( $report['dry_run'] );

		// Profile.
		$zheng = $this->post_by_source_key( 'blog_author:zheng-gong' );
		$this->assertSame( 'publish', $zheng->post_status );
		$this->assertSame( 'Zheng Gong', $zheng->post_title );

		// Media: gallery image with alt, album term, position; audio attachment.
		$image = $this->post_by_source_key( 'media:assets/images/galeria/galeria-01.jpg' );
		$this->assertSame( 'attachment', $image->post_type );
		$this->assertNotSame( '', get_post_meta( $image->ID, '_wp_attachment_image_alt', true ) );
		$this->assertTrue( has_term( '2023', 'gallery_album', $image->ID ) );
		$audio = $this->post_by_source_key( 'media:assets/audio/amitabha.mp3' );
		$this->assertStringContainsString( 'audio', $audio->post_mime_type );

		// Event: meta, non-public terms, poster as featured image.
		$event = $this->post_by_source_key( 'event:vesak-2026' );
		$this->assertSame( 'event', $event->post_type );
		$this->assertSame( '2026-05-09', get_post_meta( $event->ID, 'event_date', true ) );
		$this->assertTrue( has_term( 'Celebración', 'event_type', $event->ID ) );
		$this->assertTrue( has_term( 'Bogotá', 'event_city', $event->ID ) );
		$this->assertSame( $this->post_by_source_key( 'media:assets/images/eventos/evento-vesak-2026-bogota.jpeg' )->ID, (int) get_post_thumbnail_id( $event->ID ) );

		// Post: ordered authors relation.
		$post = $this->post_by_source_key( 'post:entrada-de-prueba' );
		$this->assertSame( array( $zheng->ID ), get_post_meta( $post->ID, 'authors', true ) );

		// Pages: hierarchy and media URL rewriting into the Media Library.
		$child = get_page_by_path( 'practica/hijo' );
		$this->assertNotNull( $child );
		$this->assertSame( get_page_by_path( 'practica' )->ID, $child->post_parent );
		$this->assertStringContainsString( wp_get_attachment_url( $image->ID ), get_page_by_path( 'practica' )->post_content );
		$this->assertStringNotContainsString( '"/assets/images/galeria/galeria-01.jpg"', get_page_by_path( 'practica' )->post_content );
	}

	/**
	 * Protects current-event visibility: an event whose start date lies in
	 * the future must import as published, never as a scheduled 'future'
	 * post (request-time status is the domain rule, not post scheduling).
	 */
	public function test_future_dated_event_imports_as_published() {
		$payload = $this->payload(
			array(
				'events' => array(
					array(
						'slug'           => 'evento-futuro',
						'title'          => 'Evento futuro',
						'type'           => 'Curso',
						'status'         => 'vigente',
						'featured'       => false,
						'start'          => gmdate( 'Y-m-d', time() + 30 * DAY_IN_SECONDS ),
						'end'            => null,
						'place'          => 'Bogotá',
						'modality'       => 'virtual',
						'cities'         => array(),
						'signup_url'     => null,
						'poster'         => '',
						'poster_alt'     => '',
						'excerpt'        => '',
						'content_html'   => '<p>Próximamente.</p>',
						'calendar_dates' => array(),
					),
				),
			)
		);

		$this->importer( $payload )->import( true );

		$this->assertSame( 'publish', get_post_status( $this->post_by_source_key( 'event:evento-futuro' )->ID ) );
	}

	/**
	 * Protects the share copy on a fresh import (WU-08A): the published
	 * message templates land as editable meta on the created objects, so
	 * staging never needs the convert backfill.
	 */
	public function test_import_stores_the_share_templates_as_meta() {
		$payload = $this->payload(
			array(
				'events' => array(
					array(
						'slug'           => 'evento-con-copy',
						'title'          => 'Evento con copy',
						'type'           => 'Curso',
						'status'         => 'vigente',
						'featured'       => false,
						'start'          => gmdate( 'Y-m-d', time() + 30 * DAY_IN_SECONDS ),
						'end'            => null,
						'place'          => 'Bogotá',
						'modality'       => 'virtual',
						'cities'         => array(),
						'signup_url'     => null,
						'poster'         => '',
						'poster_alt'     => '',
						'excerpt'        => '',
						'content_html'   => '<p>Próximamente.</p>',
						'calendar_dates' => array(),
						'share'          => array(
							'whatsapp' => "Comparto esta invitación:\n\n{{SHARE_URL}}",
							'x'        => 'Curso · Camino del Dharma',
							'threads'  => '',
						),
					),
				),
			)
		);

		$this->importer( $payload )->import( true );

		$event = $this->post_by_source_key( 'event:evento-con-copy' );

		$this->assertSame( "Comparto esta invitación:\n\n{{SHARE_URL}}", get_post_meta( $event->ID, 'share_whatsapp', true ) );
		$this->assertSame( 'Curso · Camino del Dharma', get_post_meta( $event->ID, 'share_x', true ) );
		$this->assertSame( '', get_post_meta( $event->ID, 'share_threads', true ) );
	}

	/**
	 * Protects idempotency and create-missing-only (ADR 0032 §8.2): a
	 * second apply creates nothing new, and a wp-admin edit survives
	 * re-import untouched.
	 */
	public function test_reimport_is_idempotent_and_never_overwrites_edits() {
		$payload = $this->small_payload();
		$this->importer( $payload )->import( true );

		$event = $this->post_by_source_key( 'event:vesak-2026' );
		wp_update_post(
			array(
				'ID'         => $event->ID,
				'post_title' => 'Vesak editado en wp-admin',
			)
		);

		$second = $this->importer( $payload )->import( true );

		$this->assertSame( 0, array_sum( wp_list_pluck( $second['collections'], 'created' ) ) );
		$this->assertSame( 1, (int) wp_count_posts( 'event' )->publish );
		$this->assertSame( 'Vesak editado en wp-admin', get_post( $event->ID )->post_title );
	}

	/**
	 * Protects the reading/permalink settings step: apply wires the front
	 * page, the posts page and the ADR 0008 permalink structure.
	 */
	public function test_import_apply_wires_front_page_posts_page_and_permalinks() {
		// Simulate the CLI reality: the process booted under plain
		// permalinks, so no domain permastruct was registered at init.
		global $wp_rewrite;
		$wp_rewrite->extra_permastructs = array();
		update_option( 'rewrite_rules', '' );

		$this->importer( $this->small_payload() )->import( true );

		$this->assertSame( 'page', get_option( 'show_on_front' ) );
		$this->assertSame( get_page_by_path( 'inicio' )->ID, (int) get_option( 'page_on_front' ) );
		$this->assertSame( get_page_by_path( 'blog' )->ID, (int) get_option( 'page_for_posts' ) );
		$this->assertSame( '/blog/%postname%', get_option( 'permalink_structure' ) );

		// The flush must happen with the domain permastructs present, even
		// when the process booted under plain permalinks: the imported
		// event single resolves from its incoming pretty route.
		$this->go_to( '/eventos/vesak-2026' );
		$this->assertTrue( is_singular( 'event' ) );
	}

	/**
	 * Protects the production guard (ADR 0033): applying against a
	 * production environment requires explicit confirmation plus backup
	 * evidence, and refuses otherwise.
	 */
	public function test_production_apply_requires_confirmation_and_backup_evidence() {
		$importer = $this->importer( $this->small_payload(), 'production' );

		$refused = $importer->import( true );

		$this->assertArrayHasKey( 'error', $refused );
		$this->assertSame( 0, (int) wp_count_posts( 'event' )->publish );

		$confirmed = $this->importer(
			$this->small_payload(),
			'production',
			array(
				'confirm_production' => true,
				'backup_evidence'    => 'backup-2026-08-31.tar.gz sha256:abc',
			)
		)->import( true );

		$this->assertArrayNotHasKey( 'error', $confirmed );
	}

	/**
	 * Protects verify: after an apply, verify reports reconciled counts
	 * and no missing objects.
	 */
	public function test_verify_reconciles_counts_after_apply() {
		$importer = $this->importer( $this->small_payload() );
		$importer->import( true );

		$verification = $importer->verify();

		$this->assertSame( array(), $verification['missing'] );
		foreach ( $verification['collections'] as $collection => $row ) {
			$this->assertSame( $row['expected'], $row['found'], "Collection {$collection} must reconcile." );
		}
	}

	/**
	 * Builds an importer over the repo mount.
	 *
	 * @param array  $payload     Payload array.
	 * @param string $environment Environment override.
	 * @param array  $options     Extra options.
	 */
	private function importer( array $payload, string $environment = 'local', array $options = array() ): Cdd_Core_Importer {
		return new Cdd_Core_Importer( $payload, '/repo', array_merge( array( 'environment' => $environment ), $options ) );
	}

	/**
	 * A post found by its stable source key.
	 *
	 * @param string $source_key Source key.
	 */
	private function post_by_source_key( string $source_key ): WP_Post {
		$found = get_posts(
			array(
				'post_type'   => 'any',
				'post_status' => 'any',
				'numberposts' => 1,
				'meta_key'    => '_cdd_source_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- test lookup.
				'meta_value'  => $source_key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- test lookup.
			)
		);
		$this->assertNotEmpty( $found, "Object {$source_key} must exist." );

		return $found[0];
	}

	/**
	 * A small but real payload: existing repo files, one object per
	 * collection type.
	 *
	 * @param array $overrides Collection overrides.
	 */
	private function payload( array $overrides = array() ): array {
		$collections = array_merge(
			array(
				'pages'          => array(),
				'events'         => array(),
				'posts'          => array(),
				'blog_authors'   => array(),
				'gallery_albums' => array(),
				'gallery_images' => array(),
				'media'          => array(),
				'video_embeds'   => array(),
			),
			$overrides
		);

		return ( new Cdd_Core_Payload_Builder() )->build(
			$collections,
			array(
				'version' => '1.0.35',
				'commit'  => 'test-commit',
				'root'    => 'static',
			)
		);
	}

	/**
	 * The end-to-end small payload used by the apply tests.
	 */
	private function small_payload(): array {
		return $this->payload(
			array(
				'blog_authors'   => array(
					array(
						'slug' => 'zheng-gong',
						'name' => 'Zheng Gong',
						'bio'  => 'Maestro budista.',
					),
				),
				'media'          => array(
					$this->media_object( 'assets/images/galeria/galeria-01.jpg' ),
					$this->media_object( 'assets/images/eventos/evento-vesak-2026-bogota.jpeg' ),
					$this->media_object( 'assets/audio/amitabha.mp3', 'audio' ),
				),
				'gallery_albums' => array(
					array(
						'slug'      => '2023',
						'title'     => '2023',
						'source_id' => 'galeria-2023',
					),
				),
				'gallery_images' => array(
					array(
						'file'     => 'assets/images/galeria/galeria-01.jpg',
						'alt'      => 'Grupo meditando.',
						'album'    => '2023',
						'position' => 0,
					),
				),
				'events'         => array(
					array(
						'slug'           => 'vesak-2026',
						'title'          => 'Vesak 2026 – Colombia Cuida la Vida',
						'type'           => 'Celebración',
						'status'         => 'finalizado',
						'featured'       => false,
						'start'          => '2026-05-09',
						'end'            => null,
						'place'          => 'Bogotá – Biblioteca Virgilio Barco',
						'modality'       => 'presencial',
						'cities'         => array( 'Bogotá' ),
						'signup_url'     => null,
						'poster'         => 'assets/images/eventos/evento-vesak-2026-bogota.jpeg',
						'poster_alt'     => 'Cartel de Vesak 2026.',
						'excerpt'        => 'Una mañana de meditación colectiva.',
						'content_html'   => '<p>Una mañana de meditación colectiva.</p>',
						'calendar_dates' => array(),
					),
				),
				'posts'          => array( $this->post_object() ),
				'pages'          => array(
					array(
						'slug'             => 'inicio',
						'parent'           => '',
						'title'            => 'Inicio',
						'head_title'       => 'Camino del Dharma',
						'meta_description' => 'Comunidad.',
						'content_html'     => '<p>Hola.</p>',
					),
					array(
						'slug'             => 'practica',
						'parent'           => '',
						'title'            => 'Práctica',
						'head_title'       => 'Práctica',
						'meta_description' => 'Práctica.',
						'content_html'     => '<p><img src="/assets/images/galeria/galeria-01.jpg" alt=""></p>',
					),
					array(
						'slug'             => 'practica/hijo',
						'parent'           => 'practica',
						'title'            => 'Hijo',
						'head_title'       => 'Hijo',
						'meta_description' => '',
						'content_html'     => '<p>Hijo.</p>',
					),
					array(
						'slug'             => 'blog',
						'parent'           => '',
						'title'            => 'Blog',
						'head_title'       => 'Blog',
						'meta_description' => '',
						'content_html'     => '',
					),
				),
			)
		);
	}

	/**
	 * A media payload object.
	 *
	 * @param string $file Repo-relative file.
	 * @param string $kind Media kind.
	 */
	private function media_object( string $file, string $kind = 'image' ): array {
		return array(
			'file'       => $file,
			'kind'       => $kind,
			'referenced' => true,
			'visibility' => 'public',
		);
	}

	/**
	 * WU-08B: the published head copy is written on create, so a clean
	 * staging import already carries it — no follow-up pass needed.
	 */
	public function test_import_writes_the_published_head_meta() {
		$this->import_fixture();

		$page = get_page_by_path( 'linaje' );
		$this->assertSame( 'Linaje Chan y Tierra Pura | Camino del Dharma', get_post_meta( $page->ID, 'seo_title', true ) );
		$this->assertSame( 'El linaje.', get_post_meta( $page->ID, 'seo_description', true ) );

		$event = get_page_by_path( 'evento-seo', OBJECT, 'event' );
		$this->assertSame( 'Evento — Camino del Dharma', get_post_meta( $event->ID, 'seo_title', true ) );
		$this->assertSame( 'mixed', get_post_meta( $event->ID, 'event_attendance_mode', true ) );
		$this->assertSame(
			'https://caminodeldharma.org/blog/entrada-seo',
			get_post_meta( $event->ID, 'seo_related_url', true )
		);
		$this->assertSame(
			array( 'additionalType' => 'https://schema.org/Course' ),
			json_decode( get_post_meta( $event->ID, 'seo_jsonld_extra', true ), true )
		);
	}

	/**
	 * The site-wide SEO data becomes an option, and the published
	 * addressRegion of each city becomes term metadata.
	 */
	public function test_import_seeds_the_site_seo_option_and_city_regions() {
		$this->import_fixture();

		$stored = get_option( 'cdd_core_seo_site' );
		$this->assertSame( 'Camino del Dharma', $stored['seo']['site_name'] );
		$this->assertSame( 'Organization', $stored['jsonld']['home_graph'][0]['@type'] );

		$city = get_term_by( 'name', 'Bogotá', 'event_city' );
		$this->assertInstanceOf( WP_Term::class, $city );
		$this->assertSame( 'Bogotá D.C.', get_term_meta( $city->term_id, 'cdd_region', true ) );
	}

	/**
	 * docs/11 §3.2: blog tags live under /blog/tag/{slug}; OWN-013
	 * resolves event status in America/Bogota.
	 */
	public function test_import_applies_the_timezone_and_tag_base() {
		$this->import_fixture();

		$this->assertSame( 'blog/tag', get_option( 'tag_base' ) );
		$this->assertSame( 'America/Bogota', get_option( 'timezone_string' ) );
	}

	/**
	 * WCAG 3.1.1 / docs/19 §10: the document language is es-CO in every
	 * environment, including one whose translation files were never
	 * downloaded — and an administrator's own choice still wins.
	 */
	public function test_document_language_is_colombian_spanish() {
		$this->assertSame( 'es_CO', get_locale() );
		$this->assertSame( 'es-CO', get_bloginfo( 'language' ) );

		// An administrator who has chosen a language in Settings keeps it.
		// Asserted on the filter itself: WordPress refuses to *store* a
		// locale whose translation files this offline harness never
		// downloaded, which is the very reason the filter exists.
		$this->assertSame( 'es_CO', cdd_core_default_locale( 'en_US' ) );

		update_option( 'WPLANG', 'es_ES' );
		add_filter( 'option_WPLANG', static fn() => 'es_ES' );
		$this->assertSame( 'es_ES', cdd_core_default_locale( 'es_ES' ) );
	}

	/**
	 * Re-importing never rewrites head copy an editor has changed.
	 */
	public function test_import_does_not_overwrite_edited_head_meta() {
		$this->import_fixture();

		$page = get_page_by_path( 'linaje' );
		update_post_meta( $page->ID, 'seo_title', 'Título editado a mano' );

		$this->import_fixture();

		$this->assertSame( 'Título editado a mano', get_post_meta( $page->ID, 'seo_title', true ) );
	}

	/**
	 * Runs the WU-08B fixture payload through a real import.
	 */
	private function import_fixture() {
		$importer = new Cdd_Core_Importer(
			$this->seo_payload(),
			dirname( __DIR__, 2 ) . '/static',
			array( 'environment' => 'local' )
		);
		$importer->import( true );
	}

	/**
	 * A minimal payload exercising every WU-08B write path.
	 */
	private function seo_payload(): array {
		return ( new Cdd_Core_Payload_Builder() )->build(
			array(
				'pages'  => array(
					array(
						'slug'         => 'linaje',
						'title'        => 'El linaje',
						'parent'       => '',
						'content_html' => '<p>Linaje.</p>',
						'seo'          => array(
							'title'          => 'Linaje Chan y Tierra Pura | Camino del Dharma',
							'description'    => 'El linaje.',
							'keywords'       => 'linaje, chan',
							'og_title'       => 'Linaje Chan y Tierra Pura',
							'og_description' => 'El linaje.',
							'related'        => '',
						),
					),
				),
				'events' => array(
					array(
						'slug'            => 'evento-seo',
						'title'           => 'Evento',
						'type'            => 'Curso',
						'status'          => 'vigente',
						'featured'        => false,
						'start'           => '2026-09-03',
						'end'             => '2026-09-04',
						'place'           => 'Virtual',
						'modality'        => 'Híbrida',
						'cities'          => array( 'Bogotá' ),
						'signup_url'      => '',
						'poster'          => '',
						'poster_alt'      => '',
						'excerpt'         => 'Excerpt.',
						'content_html'    => '<p>Evento.</p>',
						'calendar_dates'  => array(),
						'share'           => array(),
						'attendance_mode' => 'mixed',
						'jsonld_extra'    => array( 'additionalType' => 'https://schema.org/Course' ),
						'seo'             => array(
							'title'          => 'Evento — Camino del Dharma',
							'description'    => 'Evento.',
							'keywords'       => '',
							'og_title'       => '',
							'og_description' => '',
							'related'        => 'https://caminodeldharma.org/blog/entrada-seo',
						),
					),
				),
			),
			array(
				'version' => '1.0.35',
				'commit'  => 'abc1234',
				'root'    => 'static',
			),
			array(
				'seo'    => array(
					'site_name'    => 'Camino del Dharma',
					'city_regions' => array( 'Bogotá' => 'Bogotá D.C.' ),
				),
				'jsonld' => array( 'home_graph' => array( array( '@type' => 'Organization' ) ) ),
			)
		);
	}

	/**
	 * A post payload object.
	 *
	 * @param array $overrides Field overrides.
	 */
	private function post_object( array $overrides = array() ): array {
		return array_merge(
			array(
				'slug'             => 'entrada-de-prueba',
				'title'            => 'Entrada de prueba',
				'deck'             => 'Subtítulo.',
				'date'             => '2026-07-16',
				'author_name'      => 'Zheng Gong',
				'author_slug'      => 'zheng-gong',
				'author_bio'       => '',
				'thumbnail'        => '',
				'thumbnail_alt'    => '',
				'meta_description' => '',
				'content_html'     => '<p>Contenido.</p>',
				'tags'             => array(),
				'authors'          => array( 'zheng-gong' ),
			),
			$overrides
		);
	}
}
