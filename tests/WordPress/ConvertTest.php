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
