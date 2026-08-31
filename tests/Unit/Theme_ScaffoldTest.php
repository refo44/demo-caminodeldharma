<?php
/**
 * Level 1: the FSE theme scaffold contract (WU-04, ADR 0029).
 *
 * Written RED-first: these tests name the block-theme scaffold contract
 * before any theme file exists (docs/guia-pruebas-plugin-theme-fse.md §4).
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: WordPress recognizes camino-del-dharma as a block theme.
 */
final class Theme_ScaffoldTest extends TestCase {

	/**
	 * Protects the minimum WordPress requirement: without style.css the
	 * theme does not exist for WordPress at all.
	 */
	public function test_style_css_declares_the_theme_header() {
		$style = $this->theme_dir() . '/style.css';

		$this->assertFileExists( $style );

		$header = file_get_contents( $style ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local repo file in a unit test without WordPress loaded.

		$this->assertMatchesRegularExpression( '/^Theme Name:\s*Camino del Dharma$/m', $header );
		$this->assertMatchesRegularExpression( '/^Text Domain:\s*camino-del-dharma$/m', $header );
		$this->assertMatchesRegularExpression( '/^Version:\s*\d+\.\d+\.\d+$/m', $header );
	}

	/**
	 * Protects block-theme recognition: templates/index.html is the minimum
	 * requirement for WordPress to treat the theme as a block theme.
	 */
	public function test_index_template_exists_and_is_not_empty() {
		$index = $this->theme_dir() . '/templates/index.html';

		$this->assertFileExists( $index );
		$this->assertGreaterThan( 0, filesize( $index ) );
	}

	/**
	 * Protects the reusable-parts contract of docs/12 §4: header and footer
	 * are template parts, not per-template markup.
	 */
	public function test_header_and_footer_parts_exist() {
		$this->assertFileExists( $this->theme_dir() . '/parts/header.html' );
		$this->assertFileExists( $this->theme_dir() . '/parts/footer.html' );
	}

	/**
	 * Protects the Global Styles source of truth: theme.json must exist and
	 * parse, and it must register the header/footer parts with their areas.
	 */
	public function test_theme_json_parses_and_registers_template_parts() {
		$theme_json = $this->theme_json();

		$this->assertIsArray( $theme_json );
		$this->assertSame( 3, $theme_json['version'] );

		$parts = array();
		foreach ( $theme_json['templateParts'] ?? array() as $part ) {
			$parts[ $part['name'] ] = $part['area'];
		}

		$this->assertSame( 'header', $parts['header'] ?? null );
		$this->assertSame( 'footer', $parts['footer'] ?? null );
	}

	/**
	 * Protects the bootstrap split of docs/12 §7: functions.php exists and
	 * the complementary stylesheet it enqueues exists on disk.
	 */
	public function test_bootstrap_and_complementary_stylesheet_exist() {
		$this->assertFileExists( $this->theme_dir() . '/functions.php' );
		$this->assertFileExists( $this->theme_dir() . '/assets/css/main.css' );
	}

	/**
	 * Protects ADR 0029: no classic PHP view layer. front-page.php,
	 * page-*.php, single-*.php, archive-*.php, and index.php must not exist.
	 */
	public function test_no_classic_view_templates_exist() {
		$forbidden = array();
		foreach ( array( 'front-page.php', 'page*.php', 'single*.php', 'archive*.php', 'index.php' ) as $pattern ) {
			$matches = glob( $this->theme_dir() . '/' . $pattern );
			if ( is_array( $matches ) ) {
				$forbidden = array_merge( $forbidden, $matches );
			}
		}

		$this->assertSame( array(), $forbidden );
	}

	/**
	 * Path of the theme relative to the repo root.
	 */
	private function theme_dir(): string {
		return dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/camino-del-dharma';
	}

	/**
	 * Decoded theme.json, or null when missing/invalid.
	 */
	private function theme_json(): ?array {
		$path = $this->theme_dir() . '/theme.json';

		if ( ! is_readable( $path ) ) {
			return null;
		}

		return json_decode( file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local repo file in a unit test without WordPress loaded.
	}
}
