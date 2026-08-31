<?php
/**
 * Level 1: deterministic blog extraction from the two production posts
 * (ADR 0034/0037).
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: the two production posts with their bylines.
 */
final class Blog_ExtractorTest extends TestCase {

	/**
	 * Protects the byline seed of ADR 0037: the "Por …" text yields the
	 * two approved profiles with their slugs, and Zheng Gong's byline
	 * biography travels with him.
	 */
	public function test_posts_yield_the_two_approved_author_profiles() {
		$posts = $this->extract_posts();

		$sangha   = $posts['sangha-refugio-hiperconexion'];
		$circulos = $posts['circulos-de-presencia-consciente'];

		$this->assertSame( 'Zheng Gong', $sangha['author_name'] );
		$this->assertSame( 'zheng-gong', $sangha['author_slug'] );
		$this->assertStringContainsString( 'Maestro budista de las tradiciones Chan y Tierra Pura', $sangha['author_bio'] );

		$this->assertSame( 'Comunidad Camino del Dharma', $circulos['author_name'] );
		$this->assertSame( 'comunidad-camino-del-dharma', $circulos['author_slug'] );
		$this->assertSame( '', $circulos['author_bio'], 'The reading-time line is not a biography.' );
	}

	/**
	 * Protects the editorial identity of each post: title, deck and the
	 * machine publication date from JSON-LD.
	 */
	public function test_posts_carry_title_deck_and_published_date() {
		$posts = $this->extract_posts();

		$sangha = $posts['sangha-refugio-hiperconexion'];

		$this->assertSame( 'Estamos conectados, pero seguimos solos', $sangha['title'] );
		$this->assertSame( 'La Sangha como refugio en tiempos de hiperconexión', $sangha['deck'] );
		$this->assertSame( '2026-07-16', $sangha['date'] );

		$this->assertSame( '2026-08-13', $posts['circulos-de-presencia-consciente']['date'] );
	}

	/**
	 * Protects the hero-image mapping: the article hero becomes the
	 * featured image (with its alt), not inline content.
	 */
	public function test_hero_figure_becomes_the_thumbnail_and_leaves_the_content() {
		$sangha = $this->extract_posts()['sangha-refugio-hiperconexion'];

		$this->assertSame( 'assets/images/blog/sangha-refugio-hiperconexion.jpg', $sangha['thumbnail'] );
		$this->assertNotSame( '', $sangha['thumbnail_alt'] );
		$this->assertStringNotContainsString( 'sangha-refugio-hiperconexion.jpg', $sangha['content_html'] );
	}

	/**
	 * Protects content extraction: body sections and the references
	 * survive; header, share chrome and the trailing blog-nav links do
	 * not.
	 */
	public function test_content_keeps_the_body_and_references_without_chrome() {
		$sangha = $this->extract_posts()['sangha-refugio-hiperconexion'];

		$this->assertStringContainsString( 'La paradoja de la hiperconexión', $sangha['content_html'] );
		$this->assertStringContainsString( 'Referencias consultadas', $sangha['content_html'] );
		$this->assertStringNotContainsString( 'article-byline', $sangha['content_html'] );
		$this->assertStringNotContainsString( '<template', $sangha['content_html'] );
		$this->assertStringNotContainsString( 'share-trigger', $sangha['content_html'] );
		$this->assertStringNotContainsString( 'Ver más entradas del blog', $sangha['content_html'] );
	}

	/**
	 * Protects the published share copy of both entries (WU-08A): the
	 * hand-written message templates travel as data, normalized the way
	 * share.js reads them, with the placeholder intact.
	 */
	public function test_posts_carry_the_published_share_templates() {
		$posts = $this->extract_posts();

		$this->assertSame(
			"Reflexión · Zheng Gong · Camino del Dharma\n\n"
			. "Estamos conectados, pero seguimos solos\n"
			. "La Sangha como refugio en tiempos de hiperconexión\n\n"
			. '{{SHARE_URL}}',
			$posts['sangha-refugio-hiperconexion']['share']['whatsapp']
		);
		$this->assertSame(
			"Reflexión · Zheng Gong · Camino del Dharma\n\nEstamos conectados, pero seguimos solos",
			$posts['sangha-refugio-hiperconexion']['share']['x']
		);
		$this->assertSame(
			"Nota · Comunidad Camino del Dharma\n\nCírculos de Presencia Consciente\n"
			. 'Un espacio para detenernos, escucharnos y aprender a cuidar la vida en comunidad.',
			$posts['circulos-de-presencia-consciente']['share']['threads']
		);
	}

	/**
	 * Runs the extractor over the real repo posts, keyed by slug.
	 */
	private function extract_posts(): array {
		static $posts = null;
		if ( null !== $posts ) {
			return $posts;
		}

		$static_root = dirname( __DIR__, 2 ) . '/static';
		$sources     = array();
		foreach ( array( 'circulos-de-presencia-consciente', 'sangha-refugio-hiperconexion' ) as $slug ) {
			$sources[ $slug ] = file_get_contents( $static_root . '/blog/' . $slug . '/index.html' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repo files in a unit test without WordPress.
		}

		$extracted = ( new Cdd_Core_Blog_Extractor() )->extract( $sources );

		$posts = array();
		foreach ( $extracted as $post ) {
			$posts[ $post['slug'] ] = $post;
		}

		return $posts;
	}
}
