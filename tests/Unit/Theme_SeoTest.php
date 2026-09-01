<?php
/**
 * Level 1: the theme's half of the SEO surface (WU-08B).
 *
 * Written RED-first. The plugin owns *what* the head says; the theme
 * assembles and prints it (ADR 0024). These tests protect that split and
 * the escaping of every printed value.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * SEO cluster: theme head assembly.
 */
final class Theme_SeoTest extends TestCase {

	/**
	 * The theme prints the head, it does not compute it: no schema.org
	 * type and no robots policy may be decided in the theme.
	 */
	public function test_theme_does_not_own_the_seo_domain() {
		$seo = $this->theme_file( 'inc/seo.php' );

		$this->assertStringContainsString( 'cdd_core_seo_document', $seo );
		$this->assertStringNotContainsString( 'schema.org', $seo );
		$this->assertStringNotContainsString( 'noindex', $seo );
	}

	/**
	 * The head is printed early and replaces core's canonical, so the
	 * document never carries two different canonical URLs.
	 */
	public function test_head_is_hooked_early_and_core_canonical_is_removed() {
		$seo = $this->theme_file( 'inc/seo.php' );

		$this->assertStringContainsString( "add_action( 'wp_head', 'camino_del_dharma_print_seo', 1 )", $seo );
		$this->assertStringContainsString( "remove_action( 'wp_head', 'rel_canonical' )", $seo );
		// Core registers robots and title at priority 1: removing them at
		// the default 10 silently does nothing.
		$this->assertStringContainsString( "remove_action( 'wp_head', 'wp_robots', 1 )", $seo );
		$this->assertStringContainsString( "add_action( 'wp_head', 'camino_del_dharma_suppress_core_head', 0 )", $seo );
		// A block theme swaps core's title printer for its own.
		$this->assertStringContainsString( "remove_action( 'wp_head', '_wp_render_title_tag', 1 )", $seo );
		$this->assertStringContainsString( "remove_action( 'wp_head', '_block_template_render_title_tag', 1 )", $seo );
	}

	/**
	 * Every value reaches the document escaped for its context, and the
	 * JSON-LD is printed with `wp_json_encode` (never raw concatenation).
	 */
	public function test_printed_values_are_escaped() {
		$seo = $this->theme_file( 'inc/seo.php' );

		$this->assertStringContainsString( 'esc_attr', $seo );
		$this->assertStringContainsString( 'esc_html', $seo );
		$this->assertStringContainsString( 'wp_json_encode', $seo );
	}

	/**
	 * Malformed tags and a failed JSON-LD encode must not emit PHP warnings
	 * or print the literal string "false" into the document.
	 */
	public function test_seo_printer_guards_malformed_tags_and_failed_json_encode() {
		$seo = $this->theme_file( 'inc/seo.php' );

		$this->assertStringContainsString( "\$tag['attr'] ?? array()", $seo );
		$this->assertStringContainsString( 'false === $json', $seo );
	}

	/**
	 * Asset versions must not call bare filemtime() on paths that may be
	 * missing during a partial deploy.
	 */
	public function test_asset_enqueue_uses_safe_version_helper() {
		$functions = $this->theme_file( 'functions.php' );
		$blocks    = $this->theme_file( 'inc/blocks.php' );

		$this->assertStringContainsString( 'function camino_del_dharma_asset_version', $functions );
		$this->assertStringContainsString( 'camino_del_dharma_asset_version', $functions );
		$this->assertStringNotContainsString( '(string) filemtime', $functions );
		$this->assertStringContainsString( 'camino_del_dharma_asset_version', $blocks );
		$this->assertStringNotContainsString( '(string) filemtime', $blocks );
	}

	/**
	 * The theme loads the file from its bootstrap, like the rest of inc/.
	 */
	public function test_seo_is_wired_from_functions_php() {
		$this->assertStringContainsString( '/inc/seo.php', $this->theme_file( 'functions.php' ) );
	}

	/**
	 * docs/19 §10: the document language must be the site's, and the
	 * theme must not hardcode it.
	 */
	public function test_theme_never_hardcodes_the_document_language() {
		$this->assertStringNotContainsString( 'lang="es', $this->theme_file( 'inc/seo.php' ) );
	}

	/**
	 * docs/19 §10: one skip link. A block theme gets core's own — in the
	 * site's admin language, pointing at the same `#main` — on top of the
	 * published one the header pattern renders, so a keyboard user tabs
	 * twice through the same control. The theme keeps its own and drops
	 * core's two halves (the markup filter and its stylesheet).
	 */
	public function test_core_block_template_skip_link_is_removed() {
		$functions = $this->theme_file( 'functions.php' );

		$this->assertStringContainsString( "remove_action( 'wp_footer', 'the_block_template_skip_link' )", $functions );
		$this->assertStringContainsString( "remove_action( 'wp_enqueue_scripts', 'wp_enqueue_block_template_skip_link' )", $functions );
	}

	/**
	 * Reads one theme file.
	 *
	 * @param string $path Path under the theme root.
	 */
	private function theme_file( string $path ): string {
		$full = dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/camino-del-dharma/' . $path;
		$this->assertFileExists( $full );

		return (string) file_get_contents( $full ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repo files in a unit test without WordPress.
	}
}
