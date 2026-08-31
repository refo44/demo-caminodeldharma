<?php
/**
 * Level 1: deterministic Page extraction from the production static HTML
 * (ADR 0034; OWN-007 — published copy is the source).
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: institutional pages extracted with normalized URLs.
 */
final class Page_ExtractorTest extends TestCase {

	/**
	 * Protects the page identity fields: slug from the path, the visible
	 * h1, the head title and the meta description.
	 */
	public function test_page_carries_slug_heading_and_seo_fields() {
		$practica = $this->extract_page( 'practica', 'practica/index.html' );

		$this->assertSame( 'practica', $practica['slug'] );
		$this->assertNotSame( '', $practica['title'] );
		$this->assertStringContainsString( 'Práctica', $practica['seo']['title'] );
		$this->assertNotSame( '', $practica['seo']['description'] );
		$this->assertArrayNotHasKey( 'head_title', $practica, 'Head SEO travels as one object (WU-08B).' );
		$this->assertArrayNotHasKey( 'meta_description', $practica );
	}

	/**
	 * Protects content extraction: the main region's editorial markup
	 * survives with root-relative asset URLs, while scripts, templates
	 * and share chrome do not travel.
	 */
	public function test_practica_content_is_clean_with_root_relative_urls() {
		$practica = $this->extract_page( 'practica', 'practica/index.html' );

		$this->assertStringContainsString( 'src="/assets/images/galeria/galeria-04.jpg"', $practica['content_html'] );
		$this->assertStringContainsString( '<audio', $practica['content_html'] );
		$this->assertStringNotContainsString( '<script', $practica['content_html'] );
		$this->assertStringNotContainsString( 'src="../', $practica['content_html'] );
	}

	/**
	 * Protects the home page: extracted as the front page object with the
	 * 'inicio' slug and its hero content.
	 */
	public function test_home_page_extracts_as_inicio() {
		$home = $this->extract_page( 'inicio', 'index.html' );

		$this->assertSame( 'inicio', $home['slug'] );
		$this->assertNotSame( '', $home['content_html'] );
		$this->assertStringNotContainsString( 'href="assets/', $home['content_html'] );
	}

	/**
	 * Protects the embed inventory: the videos page yields the five
	 * production embeds (4 YouTube nocookie + 1 Vimeo dnt) with titles.
	 */
	public function test_videos_page_yields_the_five_production_embeds() {
		$html   = $this->page_html( 'practica/videos/index.html' );
		$embeds = ( new Cdd_Core_Page_Extractor() )->extract_embeds( $html );

		$this->assertCount( 5, $embeds );
		$youtube = array_filter(
			$embeds,
			static function ( array $embed ): bool {
				return false !== strpos( $embed['url'], 'youtube-nocookie.com' );
			}
		);
		$vimeo   = array_filter(
			$embeds,
			static function ( array $embed ): bool {
				return false !== strpos( $embed['url'], 'player.vimeo.com' );
			}
		);
		$this->assertCount( 4, $youtube );
		$this->assertCount( 1, $vimeo );
		$this->assertNotSame( '', $embeds[0]['title'] );
	}

	/**
	 * Extracts one page from the real repo sources.
	 *
	 * @param string $slug Target page slug.
	 * @param string $path Repo-relative static path.
	 */
	private function extract_page( string $slug, string $path ): array {
		return ( new Cdd_Core_Page_Extractor() )->extract( $slug, $this->page_html( $path ) );
	}

	/**
	 * Raw HTML of one static page.
	 *
	 * @param string $path Repo-relative static path.
	 */
	private function page_html( string $path ): string {
		return file_get_contents( dirname( __DIR__, 2 ) . '/static/' . $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repo file in a unit test without WordPress.
	}
}
