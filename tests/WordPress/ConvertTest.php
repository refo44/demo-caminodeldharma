<?php
/**
 * Level 2: the explicit wp:html → block conversion pass (WU-07).
 *
 * Written RED-first. The conversion is a field-scoped, documented edit of
 * imported content (ADR 0033: the importer never overwrites edits; this
 * pass IS the edit, dry-run by default, idempotent under re-runs).
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * Behavior cluster: `wp cdd-core migrate convert` service semantics.
 */
final class ConvertTest extends WP_UnitTestCase {

	/**
	 * Imported pages by slug, created from the real payload.
	 */
	private array $pages = array();

	public function set_up() {
		parent::set_up();

		$payload = json_decode(
			file_get_contents( dirname( __DIR__, 2 ) . '/migration/payload.json' ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repository fixture read inside the ephemeral harness.
			true
		);

		foreach ( $payload['pages'] as $page ) {
			if ( ! in_array( $page['slug'], array( 'inicio', 'galeria', 'comunidad' ), true ) ) {
				continue;
			}
			$this->pages[ $page['slug'] ] = self::factory()->post->create(
				array(
					'post_type'    => 'page',
					'post_name'    => $page['slug'],
					'post_title'   => $page['title'],
					'post_content' => "<!-- wp:html -->\n" . $page['content_html'] . "\n<!-- /wp:html -->",
				)
			);
		}
	}

	/**
	 * Protects the dry-run default: the service reports the pending
	 * conversions without writing a byte.
	 */
	public function test_dry_run_reports_without_writing() {
		$before = get_post( $this->pages['inicio'] )->post_content;

		$report = ( new Cdd_Core_Convert_Service() )->run( false );

		$this->assertTrue( $report['dry_run'] );
		$this->assertContains( 'inicio', $report['pending'] );
		$this->assertContains( 'galeria', $report['pending'] );
		$this->assertContains( 'comunidad', $report['pending'] );
		$this->assertSame( array(), $report['converted'] );
		$this->assertSame( $before, get_post( $this->pages['inicio'] )->post_content );
	}

	/**
	 * Protects the applied conversion end to end: dynamic blocks land in
	 * inicio, the gallery hub gains native galleries fed by the real
	 * album attachments in filename order, and comunidad gains the
	 * profile links (OWN-016).
	 */
	public function test_apply_converts_the_three_documented_pages() {
		$term = wp_insert_term( 'General', 'gallery_album', array( 'slug' => 'general' ) );

		$second = self::factory()->attachment->create_object(
			array(
				'file'           => '2026/08/galeria-02.jpg',
				'post_mime_type' => 'image/jpeg',
				'post_title'     => 'galeria-02',
				'post_name'      => 'galeria-02',
			)
		);
		$first  = self::factory()->attachment->create_object(
			array(
				'file'           => '2026/08/galeria-01.jpg',
				'post_mime_type' => 'image/jpeg',
				'post_title'     => 'galeria-01',
				'post_name'      => 'galeria-01',
			)
		);
		wp_set_object_terms( $second, (int) $term['term_id'], 'gallery_album' );
		wp_set_object_terms( $first, (int) $term['term_id'], 'gallery_album' );

		$report = ( new Cdd_Core_Convert_Service() )->run( true );

		$this->assertFalse( $report['dry_run'] );
		$this->assertEqualsCanonicalizing( array( 'inicio', 'galeria', 'comunidad' ), $report['converted'] );

		$inicio = get_post( $this->pages['inicio'] )->post_content;
		$this->assertStringContainsString( '<!-- wp:camino-del-dharma/evento-destacado /-->', $inicio );
		$this->assertStringContainsString( '<!-- wp:camino-del-dharma/blog-recientes /-->', $inicio );
		$this->assertStringNotContainsString( 'home-featured-event-title', $inicio );

		$galeria = get_post( $this->pages['galeria'] )->post_content;
		$this->assertStringContainsString( '<!-- wp:gallery', $galeria );
		$this->assertStringContainsString( 'wp-image-' . $first, $galeria );
		$this->assertStringContainsString( 'wp-image-' . $second, $galeria );
		$this->assertLessThan(
			strpos( $galeria, 'wp-image-' . $second ),
			strpos( $galeria, 'wp-image-' . $first ),
			'Album images keep the published filename order.'
		);
		$this->assertStringContainsString( '/galeria/general', $galeria );

		$comunidad = get_post( $this->pages['comunidad'] )->post_content;
		$this->assertStringContainsString( 'href="/author/zheng-gong"', $comunidad );
		$this->assertStringContainsString( 'href="/author/comunidad-camino-del-dharma"', $comunidad );
	}

	/**
	 * Protects the mantra players (WU-08A): /practica converts only when
	 * the imported audio attachments exist, and the resulting blocks point
	 * at the Library, never at the static path.
	 */
	public function test_apply_converts_the_mantra_players_of_practica() {
		$page  = $this->create_practica_page();
		$audio = $this->create_audio_attachment( 'amitabha' );
		$this->create_audio_attachment( 'namo-guan-shi-yin-pusa' );

		$report = ( new Cdd_Core_Convert_Service() )->run( true );

		$this->assertContains( 'practica', $report['converted'] );

		$content = get_post( $page )->post_content;
		$this->assertStringContainsString( '<!-- wp:audio {"id":' . $audio . ',"className":"mantra-audio","preload":"metadata"} -->', $content );
		$this->assertStringContainsString( '<figure class="wp-block-audio mantra-audio">', $content );
		$this->assertStringContainsString( 'Recitación de Amitābha.', $content );
		$this->assertStringNotContainsString( '<figure class="mantra-audio">', $content );
		$this->assertStringNotContainsString( 'assets/audio/', $content );

		$this->assertNotContains( 'practica', ( new Cdd_Core_Convert_Service() )->run( true )['converted'] );
	}

	/**
	 * Protects the share copy of already-imported objects (WU-08A): the
	 * pass seeds the published message templates as meta from the payload
	 * without ever overwriting what an editor rewrote in wp-admin.
	 */
	public function test_apply_seeds_the_share_templates_without_overwriting_edits() {
		$payload = $this->payload();
		$event   = self::factory()->post->create(
			array(
				'post_type'  => 'event',
				'post_name'  => 'circulos-de-presencia-consciente',
				'meta_input' => array( '_cdd_source_key' => 'event:circulos-de-presencia-consciente' ),
			)
		);
		$post    = self::factory()->post->create(
			array(
				'post_name'  => 'sangha-refugio-hiperconexion',
				'meta_input' => array(
					'_cdd_source_key' => 'post:sangha-refugio-hiperconexion',
					'share_x'         => 'Texto reescrito por la editora',
				),
			)
		);

		$report = ( new Cdd_Core_Convert_Service( array( 'payload' => $payload ) ) )->run( true );

		$this->assertContains( 'share:event:circulos-de-presencia-consciente', $report['converted'] );
		$this->assertStringContainsString( '{{SHARE_URL}}', get_post_meta( $event, 'share_whatsapp', true ) );
		$this->assertStringContainsString( 'Camino del Dharma', get_post_meta( $event, 'share_threads', true ) );
		$this->assertSame(
			'Texto reescrito por la editora',
			get_post_meta( $post, 'share_x', true ),
			'A wp-admin edit is never overwritten (ADR 0033).'
		);
		$this->assertStringContainsString( 'Zheng Gong', get_post_meta( $post, 'share_whatsapp', true ) );

		// Idempotent: a second pass has nothing left to seed.
		$second = ( new Cdd_Core_Convert_Service( array( 'payload' => $payload ) ) )->run( true );

		foreach ( $second['converted'] as $item ) {
			$this->assertStringStartsNotWith( 'share:', $item );
		}
	}

	/**
	 * Protects the optional input: without a payload the pass still runs
	 * the content conversions and simply seeds no share copy.
	 */
	public function test_share_seeding_is_skipped_without_a_payload() {
		self::factory()->post->create(
			array(
				'post_type'  => 'event',
				'post_name'  => 'circulos-de-presencia-consciente',
				'meta_input' => array( '_cdd_source_key' => 'event:circulos-de-presencia-consciente' ),
			)
		);

		$report = ( new Cdd_Core_Convert_Service() )->run( true );

		foreach ( $report['converted'] as $item ) {
			$this->assertStringStartsNotWith( 'share:', $item );
		}
	}

	/**
	 * The versioned payload as the CLI passes it.
	 */
	private function payload(): array {
		return json_decode(
			file_get_contents( dirname( __DIR__, 2 ) . '/migration/payload.json' ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repository fixture read inside the ephemeral harness.
			true
		);
	}

	/**
	 * Creates the imported /practica page from the real payload.
	 *
	 * kses is lifted around the insert because the importer runs under
	 * WP-CLI, where kses filters are not active: the stored content keeps
	 * the published <source> element (kses drops it, since `source` is not
	 * an allowed post tag). Without this the fixture would not be the
	 * content the converter meets in a real environment.
	 */
	private function create_practica_page(): int {
		foreach ( $this->payload()['pages'] as $page ) {
			if ( 'practica' !== $page['slug'] ) {
				continue;
			}

			kses_remove_filters();
			$page_id = self::factory()->post->create(
				array(
					'post_type'    => 'page',
					'post_name'    => 'practica',
					'post_title'   => $page['title'],
					'post_content' => "<!-- wp:html -->\n" . $page['content_html'] . "\n<!-- /wp:html -->",
				)
			);
			kses_init_filters();

			return $page_id;
		}

		$this->fail( 'The payload has no practica page.' );
	}

	/**
	 * Creates one imported mp3 attachment named like the static file.
	 *
	 * @param string $name File base name without extension.
	 */
	private function create_audio_attachment( string $name ): int {
		return self::factory()->attachment->create_object(
			array(
				'file'           => '2026/08/' . $name . '.mp3',
				'post_mime_type' => 'audio/mpeg',
				'post_title'     => $name,
				'post_name'      => $name,
			)
		);
	}

	/**
	 * Protects idempotency: a second apply finds nothing pending and
	 * rewrites nothing.
	 */
	public function test_second_apply_converts_nothing() {
		( new Cdd_Core_Convert_Service() )->run( true );
		$after_first = get_post( $this->pages['inicio'] )->post_content;

		$report = ( new Cdd_Core_Convert_Service() )->run( true );

		$this->assertSame( array(), $report['converted'] );
		$this->assertSame( array(), $report['pending'] );
		$this->assertSame( $after_first, get_post( $this->pages['inicio'] )->post_content );
	}

	/**
	 * WU-08B: an environment imported before the head copy travelled
	 * converges through `convert --payload`, add-only — a value an editor
	 * already wrote, or deliberately emptied, is never rewritten.
	 */
	public function test_convert_seeds_head_seo_add_only() {
		$page = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_name'  => 'linaje',
				'post_title' => 'El linaje',
				'meta_input' => array( '_cdd_source_key' => 'page:linaje' ),
			)
		);
		update_post_meta( $page, 'seo_title', 'Título ya editado' );

		$payload = array(
			'pages' => array(
				array(
					'_source_key' => 'page:linaje',
					'seo'         => array(
						'title'          => 'Linaje Chan y Tierra Pura | Camino del Dharma',
						'description'    => 'El linaje.',
						'keywords'       => '',
						'og_title'       => '',
						'og_description' => '',
						'related'        => '',
					),
				),
			),
		);

		$service = new Cdd_Core_Convert_Service(
			array(
				'environment' => 'local',
				'payload'     => $payload,
			)
		);

		$dry = $service->run( false );
		$this->assertContains( 'seo:page:linaje', $dry['pending'] );
		$this->assertSame( 'Título ya editado', get_post_meta( $page, 'seo_title', true ) );

		$applied = $service->run( true );
		$this->assertContains( 'seo:page:linaje', $applied['converted'] );
		$this->assertSame( 'Título ya editado', get_post_meta( $page, 'seo_title', true ) );
		$this->assertSame( 'El linaje.', get_post_meta( $page, 'seo_description', true ) );

		$this->assertNotContains( 'seo:page:linaje', $service->run( true )['converted'], 'Seeding is idempotent.' );
	}

	/**
	 * Regression (WU-08B): the meta API unslashes what it stores, so JSON
	 * seeded without wp_slash() loses every backslash — `\u00ed` becomes
	 * `u00ed` and «Círculos» reaches the page as «Cu00edrculos».
	 */
	public function test_convert_seeds_json_ld_extras_without_losing_escapes() {
		$event = self::factory()->post->create(
			array(
				'post_type'  => 'event',
				'post_name'  => 'circulos',
				'post_title' => 'Círculos',
				'meta_input' => array( '_cdd_source_key' => 'event:circulos' ),
			)
		);

		$extra = array( 'alternateName' => 'Curso Círculos de Presencia Consciente' );

		( new Cdd_Core_Convert_Service(
			array(
				'environment' => 'local',
				'payload'     => array(
					'events' => array(
						array(
							'_source_key'  => 'event:circulos',
							'seo'          => array( 'title' => 'Círculos — Camino del Dharma' ),
							'jsonld_extra' => $extra,
						),
					),
				),
			)
		) )->run( true );

		$this->assertSame( $extra, json_decode( get_post_meta( $event, 'seo_jsonld_extra', true ), true ) );
		$this->assertSame( 'Círculos — Camino del Dharma', get_post_meta( $event, 'seo_title', true ) );
	}

	/**
	 * Protects the production guard (ADR 0033): in a production
	 * environment the service refuses to write without explicit
	 * confirmation plus backup evidence.
	 */
	public function test_production_guard_blocks_unconfirmed_writes() {
		$service = new Cdd_Core_Convert_Service( array( 'environment' => 'production' ) );

		$report = $service->run( true );

		$this->assertArrayHasKey( 'error', $report );
		$this->assertSame( array(), $report['converted'] );

		$confirmed = new Cdd_Core_Convert_Service(
			array(
				'environment'        => 'production',
				'confirm_production' => true,
				'backup_evidence'    => 'backup-2026-08-31',
			)
		);

		$this->assertArrayNotHasKey( 'error', $confirmed->run( false ) );
	}
}
