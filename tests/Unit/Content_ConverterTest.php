<?php
/**
 * Level 1: wp:html → block conversion of imported content (WU-07).
 *
 * Written RED-first. The fixtures are the real extracted page contents in
 * migration/payload.json (VERSION 1.0.35), so the conversions are proven
 * against the production copy they must preserve (OWN-007). The converter
 * is explicit and field-scoped: it only touches the documented fragments,
 * and a second pass returns null (nothing left to convert).
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: the documented conversions on imported page content.
 */
final class Content_ConverterTest extends TestCase {

	/**
	 * Payload page contents keyed by slug, wrapped as the importer stores
	 * them (a single wp:html block).
	 */
	private static array $pages = array();

	/**
	 * The imported form of one payload page, loaded lazily from the real
	 * versioned payload.
	 */
	private static function page( string $slug ): string {
		if ( empty( self::$pages ) ) {
			$payload = json_decode(
				file_get_contents( dirname( __DIR__, 2 ) . '/migration/payload.json' ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local repo file in a unit test without WordPress loaded.
				true
			);

			foreach ( $payload['pages'] as $page ) {
				self::$pages[ $page['slug'] ] = "<!-- wp:html -->\n" . $page['content_html'] . "\n<!-- /wp:html -->";
			}
		}

		return self::$pages[ $slug ];
	}

	/**
	 * Protects doc 03 §3: the home event note is the dynamic selection of
	 * camino-del-dharma-core, never the hardcoded aside frozen at import.
	 */
	public function test_inicio_swaps_the_hardcoded_aside_for_the_dynamic_block() {
		$converted = ( new Cdd_Core_Content_Converter() )->convert_inicio( self::page( 'inicio' ) );

		$this->assertIsString( $converted );
		$this->assertStringNotContainsString( '<aside class="home-featured-event"', $converted );
		$this->assertStringNotContainsString( 'home-featured-event-title', $converted );
		$this->assertStringContainsString( '<!-- wp:camino-del-dharma/evento-destacado /-->', $converted );

		// The layout row that hosted the aside survives around the block.
		$this->assertStringContainsString( 'home-community-row', $converted );
		$this->assertStringContainsString( 'Un poco de nuestra comunidad', $converted );
	}

	/**
	 * Protects the media contract (doc 03 §5.1 / ADR 0034): the static
	 * <picture> wrappers point at handmade WebP variants and thumbs that
	 * deliberately do not migrate; the conversion unwraps them to the
	 * Library <img> and remaps handmade thumb paths to Library URLs.
	 */
	public function test_inicio_unwraps_picture_sources_and_remaps_handmade_thumbs() {
		$converted = ( new Cdd_Core_Content_Converter() )->convert_inicio(
			self::page( 'inicio' ),
			array( 'galeria-01.jpg' => '/wp-content/uploads/2026/08/galeria-01.jpg' )
		);

		$this->assertStringNotContainsString( '<picture>', $converted );
		$this->assertStringNotContainsString( '<source', $converted );
		$this->assertStringContainsString( 'hero-estatua-buda-montanas.jpg', $converted, 'The Library img inside each picture survives.' );
		$this->assertStringContainsString( 'src="/wp-content/uploads/2026/08/galeria-01.jpg"', $converted );
		$this->assertStringNotContainsString( 'thumbs/galeria-01.jpg', $converted );

		// Unmapped thumbs stay untouched rather than breaking silently.
		$this->assertStringContainsString( 'thumbs/galeria-02.jpg', $converted );
	}

	/**
	 * Protects the home «Del blog» section: latest entries come from
	 * WordPress, not from the two cards frozen at import.
	 */
	public function test_inicio_swaps_the_blog_cards_for_the_dynamic_block() {
		$converted = ( new Cdd_Core_Content_Converter() )->convert_inicio( self::page( 'inicio' ) );

		$this->assertStringNotContainsString( 'home-blog-grid', $converted );
		$this->assertStringContainsString( '<!-- wp:camino-del-dharma/blog-recientes /-->', $converted );
		$this->assertStringContainsString( 'Del blog', $converted );
		$this->assertStringContainsString( 'Ver todas las entradas', $converted );
	}

	/**
	 * Protects idempotency: converting twice changes nothing (null means
	 * «no pending conversion», the create-missing-only spirit of ADR 0033).
	 */
	public function test_inicio_conversion_is_idempotent() {
		$converter = new Cdd_Core_Content_Converter();
		$converted = $converter->convert_inicio( self::page( 'inicio' ) );

		$this->assertNull( $converter->convert_inicio( $converted ) );
	}

	/**
	 * Protects ADR 0036/0021: the gallery hub renders native Gutenberg
	 * Gallery blocks per album at the old JS mount point, headings linking
	 * to the term routes, and keeps the rest of the imported copy.
	 */
	public function test_galeria_replaces_the_js_mount_with_album_gallery_blocks() {
		$albums = array(
			array(
				'slug'   => 'general',
				'title'  => 'General',
				'images' => array(
					array(
						'id'  => 11,
						'url' => '/wp-content/uploads/2026/08/galeria-01.jpg',
						'alt' => 'Grupo numeroso meditando.',
					),
					array(
						'id'  => 12,
						'url' => '/wp-content/uploads/2026/08/galeria-02.jpg',
						'alt' => '',
					),
				),
			),
			array(
				'slug'   => '2023',
				'title'  => '2023',
				'images' => array(
					array(
						'id'  => 21,
						'url' => '/wp-content/uploads/2026/08/galeria-26.jpg',
						'alt' => 'Dos monjes.',
					),
				),
			),
		);

		$converted = ( new Cdd_Core_Content_Converter() )->convert_galeria( self::page( 'galeria' ), $albums );

		$this->assertIsString( $converted );
		$this->assertStringNotContainsString( 'id="gallery-albums"', $converted );
		$this->assertStringContainsString( '<h2 class="wp-block-heading"><a href="/galeria/general">General</a></h2>', $converted );
		$this->assertStringContainsString( '<h2 class="wp-block-heading"><a href="/galeria/2023">2023</a></h2>', $converted );
		$this->assertStringContainsString( '<!-- wp:gallery', $converted );
		$this->assertStringContainsString( '"id":11', $converted );
		$this->assertStringContainsString( 'wp-image-21', $converted );
		$this->assertStringContainsString( 'alt="Grupo numeroso meditando."', $converted );
		$this->assertStringContainsString( 'Galería comunitaria', $converted );
		$this->assertStringContainsString( 'Volver al inicio', $converted );

		// Idempotent: the mount point is gone, nothing left to convert.
		$this->assertNull( ( new Cdd_Core_Content_Converter() )->convert_galeria( $converted, $albums ) );
	}

	/**
	 * Protects OWN-016: /comunidad gains links to both blog_author profiles
	 * without replacing a single word of its published biography.
	 */
	public function test_comunidad_adds_profile_links_without_touching_the_biography() {
		$original  = self::page( 'comunidad' );
		$converted = ( new Cdd_Core_Content_Converter() )->convert_comunidad( $original );

		$this->assertIsString( $converted );
		$this->assertStringContainsString( 'href="/author/zheng-gong"', $converted );
		$this->assertStringContainsString( 'href="/author/comunidad-camino-del-dharma"', $converted );

		// Removing the two added paragraphs must give back the original.
		$stripped = preg_replace( '#<p class="autor-ficha-link">.*?</p>\n?#s', '', $converted );
		$this->assertSame( $original, $stripped );

		// Idempotent.
		$this->assertNull( ( new Cdd_Core_Content_Converter() )->convert_comunidad( $converted ) );
	}
}
