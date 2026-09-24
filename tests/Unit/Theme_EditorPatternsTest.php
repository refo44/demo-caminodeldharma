<?php
/**
 * Editor-facing block patterns (theme presentation only).
 *
 * Structural chrome stays out of the inserter. The three editorial
 * patterns are the only ones an editor can insert.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: the curated pattern library and its button bridge.
 */
final class Theme_EditorPatternsTest extends TestCase {

	const CATEGORY_SLUG = 'camino-del-dharma';

	const EDITOR_PATTERNS = array(
		'seccion-con-imagen.php'    => 'camino-del-dharma/seccion-con-imagen',
		'tres-tarjetas.php'         => 'camino-del-dharma/tres-tarjetas',
		'llamado-a-la-practica.php' => 'camino-del-dharma/llamado-a-la-practica',
	);

	const STRUCTURAL_PATTERNS = array(
		'header.php'           => 'camino-del-dharma/header',
		'footer.php'           => 'camino-del-dharma/footer',
		'blog-single-nav.php'  => 'camino-del-dharma/blog-single-nav',
		'single-event-nav.php' => 'camino-del-dharma/single-event-nav',
	);

	/**
	 * The theme registers one inserter category before WordPress loads
	 * theme patterns (init priority 9).
	 */
	public function test_theme_registers_the_editor_pattern_category() {
		$setup = (string) file_get_contents( $this->theme_dir() . '/functions.php' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repo file in a unit test without WordPress.

		$this->assertStringContainsString( 'register_block_pattern_category(', $setup );
		$this->assertStringContainsString( "'" . self::CATEGORY_SLUG . "'", $setup );
		$this->assertStringContainsString( 'Camino del Dharma', $setup );
		$this->assertStringContainsString( "'camino-del-dharma'", $setup );
		$this->assertMatchesRegularExpression(
			"/add_action\(\s*'init',\s*'camino_del_dharma_register_pattern_category',\s*8\s*\)/",
			$setup
		);

		$registration = $this->function_body( $setup, 'camino_del_dharma_register_pattern_category' );
		$this->assertStringNotContainsString( 'cdd_core', $registration );
		$this->assertStringNotContainsString( 'camino-del-dharma-core', $registration );
	}

	/**
	 * Exactly the three editorial patterns are offered in the inserter.
	 */
	public function test_only_the_three_editorial_patterns_are_insertable() {
		$insertable = array();

		foreach ( $this->pattern_files() as $file ) {
			$headers = $this->pattern_headers( $file );
			$this->assertArrayHasKey( 'Title', $headers, $file );
			$this->assertArrayHasKey( 'Slug', $headers, $file );
			$this->assertNotSame( '', $headers['Title'], $file );
			$this->assertMatchesRegularExpression( '/^camino-del-dharma\/[a-z0-9-]+$/', $headers['Slug'], $file );

			if ( 'yes' === ( $headers['Inserter'] ?? '' ) ) {
				$insertable[] = $headers['Slug'];
			}
		}

		sort( $insertable );

		$this->assertSame(
			array(
				'camino-del-dharma/llamado-a-la-practica',
				'camino-del-dharma/seccion-con-imagen',
				'camino-del-dharma/tres-tarjetas',
			),
			$insertable
		);
	}

	/**
	 * Header, footer, and the single-view nav stay template-only.
	 */
	public function test_structural_patterns_stay_out_of_the_inserter() {
		foreach ( self::STRUCTURAL_PATTERNS as $file => $slug ) {
			$source  = $this->pattern_source( $file );
			$headers = $this->pattern_headers( $file );

			$this->assertSame( $slug, $headers['Slug'], $file );
			$this->assertSame( 'no', $headers['Inserter'], $file );
			$this->assertStringNotContainsString( self::CATEGORY_SLUG, $headers['Categories'] ?? '', $file );
			$this->assertStringNotContainsString( 'Categories:', $source, $file );
		}
	}

	/**
	 * Sección con imagen is an editable heading, image, and paragraphs.
	 */
	public function test_image_section_pattern_uses_core_blocks_and_existing_classes() {
		$source  = $this->pattern_source( 'seccion-con-imagen.php' );
		$headers = $this->pattern_headers( 'seccion-con-imagen.php' );

		$this->assertSame( 'Sección con imagen', $headers['Title'] );
		$this->assertSame( 'camino-del-dharma/seccion-con-imagen', $headers['Slug'] );
		$this->assertSame( 'yes', $headers['Inserter'] );
		$this->assertStringContainsString( 'text', $headers['Categories'] );
		$this->assertStringContainsString( self::CATEGORY_SLUG, $headers['Categories'] );
		$this->assertStringContainsString( 'wp:heading', $source );
		$this->assertStringContainsString( 'wp:image', $source );
		$this->assertGreaterThanOrEqual( 1, substr_count( $source, 'wp:paragraph' ) );
		$this->assertStringContainsString( 'read-width', $source );
		$this->assertStringContainsString( 'section-gap', $source );
		$this->assertStringContainsString( 'section-figure', $source );
		$this->assertStringNotContainsString( 'wp:html', $source );
		$this->assertStringNotContainsString( 'cdd_core', $source );
	}

	/**
	 * Tres tarjetas is three editable groups wearing the published card classes.
	 */
	public function test_three_card_pattern_reuses_the_card_grid_classes() {
		$source  = $this->pattern_source( 'tres-tarjetas.php' );
		$headers = $this->pattern_headers( 'tres-tarjetas.php' );
		$css     = (string) file_get_contents( $this->theme_dir() . '/assets/css/main.css' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repo file in a unit test without WordPress.

		$this->assertSame( 'Tres tarjetas', $headers['Title'] );
		$this->assertSame( 'camino-del-dharma/tres-tarjetas', $headers['Slug'] );
		$this->assertSame( 'yes', $headers['Inserter'] );
		$this->assertStringContainsString( 'columns', $headers['Categories'] );
		$this->assertStringContainsString( self::CATEGORY_SLUG, $headers['Categories'] );
		$this->assertStringContainsString( 'grid-auto', $source );
		$this->assertStringContainsString( 'grid-three', $source );
		$this->assertStringContainsString( 'section-gap', $source );
		$this->assertSame( 3, substr_count( $source, '"className":"card"' ) );
		$this->assertSame( 3, substr_count( $source, '"level":3' ) );
		$this->assertStringNotContainsString( 'wp:html', $source );
		$this->assertStringNotContainsString( '<article', $source );
		$this->assertStringNotContainsString( 'cdd_core', $source );
		$this->assertDoesNotMatchRegularExpression( '/article\.card/', $css );
		$this->assertMatchesRegularExpression( '/\.card\s*\{/', $css );
		$this->assertMatchesRegularExpression( '/\.card h3\s*\{/', $css );
	}

	/**
	 * The closing call to action is a native button, scoped to the existing design.
	 */
	public function test_practice_cta_is_a_core_button_bridged_to_the_theme_buttons() {
		$source  = $this->pattern_source( 'llamado-a-la-practica.php' );
		$headers = $this->pattern_headers( 'llamado-a-la-practica.php' );
		$css     = (string) file_get_contents( $this->theme_dir() . '/assets/css/main.css' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repo file in a unit test without WordPress.

		$this->assertSame( 'Llamado a la práctica', $headers['Title'] );
		$this->assertSame( 'camino-del-dharma/llamado-a-la-practica', $headers['Slug'] );
		$this->assertSame( 'yes', $headers['Inserter'] );
		$this->assertStringContainsString( 'call-to-action', $headers['Categories'] );
		$this->assertStringContainsString( self::CATEGORY_SLUG, $headers['Categories'] );
		$this->assertStringContainsString( 'wp:button', $source );
		$this->assertStringContainsString( 'wp-block-button__link', $source );
		$this->assertStringContainsString( '/contacto', $source );
		$this->assertStringContainsString( 'llamado-practica', $source );
		$this->assertStringNotContainsString( 'wp:html', $source );
		$this->assertStringNotContainsString( 'class="btn', $source );
		$this->assertStringNotContainsString( 'cdd_core', $source );
		$this->assertStringContainsString( '.btn,', $css );
		$this->assertStringContainsString( '.llamado-practica .wp-element-button', $css );
		$this->assertMatchesRegularExpression( '/\.btn,\s*\n\.llamado-practica \.wp-element-button \{/', $css );
		$this->assertMatchesRegularExpression( '/\.btn-primary,\s*\n\.llamado-practica \.wp-element-button \{/', $css );
		$this->assertMatchesRegularExpression( '/\.btn-primary:hover,\s*\n\.llamado-practica \.wp-element-button:hover \{/', $css );
		$this->assertMatchesRegularExpression( '/\.btn:focus-visible,\s*\n\.llamado-practica \.wp-element-button:focus-visible \{/', $css );
		$this->assertMatchesRegularExpression( '/\.btn-primary:focus-visible,\s*\n\.llamado-practica \.wp-element-button:focus-visible \{/', $css );
		$this->assertMatchesRegularExpression( '/\.btn:active,\s*\n\.llamado-practica \.wp-element-button:active \{/', $css );
	}

	/**
	 * Every pattern file is valid PHP. The cheap gate also runs php -l.
	 */
	public function test_pattern_files_parse() {
		foreach ( $this->pattern_files() as $file ) {
			$source = (string) file_get_contents( $this->theme_dir() . '/patterns/' . $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repo file in a unit test without WordPress.

			try {
				token_get_all( $source, TOKEN_PARSE );
			} catch ( ParseError $error ) {
				$this->fail( $file . ' does not parse: ' . $error->getMessage() );
			}
		}
	}

	/**
	 * Source of one theme function, so a file-level comment cannot
	 * satisfy or break a dependency check.
	 *
	 * @param string $source   PHP source.
	 * @param string $name Function name.
	 */
	private function function_body( string $source, string $name ): string {
		$pattern = '/function ' . preg_quote( $name, '/' ) . '\s*\([^)]*\)\s*\{.*?\n\}/s';
		$this->assertMatchesRegularExpression( $pattern, $source );

		preg_match( $pattern, $source, $match );

		return $match[0];
	}

	/**
	 * Pattern filenames under the theme.
	 *
	 * @return array<int, string>
	 */
	private function pattern_files(): array {
		$files = glob( $this->theme_dir() . '/patterns/*.php' );
		$this->assertNotFalse( $files );

		return array_map( 'basename', $files );
	}

	/**
	 * Raw pattern source.
	 *
	 * @param string $file Basename under patterns/.
	 */
	private function pattern_source( string $file ): string {
		$path = $this->theme_dir() . '/patterns/' . $file;
		$this->assertFileExists( $path );

		return (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repo file in a unit test without WordPress.
	}

	/**
	 * Header fields WordPress reads from a pattern file.
	 *
	 * @param string $file Basename under patterns/.
	 * @return array<string, string>
	 */
	private function pattern_headers( string $file ): array {
		$source = $this->pattern_source( $file );
		$found  = array();

		foreach ( array( 'Title', 'Slug', 'Categories', 'Inserter' ) as $field ) {
			if ( preg_match( '/^[ \t]*\*?\s*' . $field . ':\s*(.+)$/m', $source, $match ) ) {
				$found[ $field ] = trim( $match[1] );
			}
		}

		return $found;
	}

	/**
	 * Path of the theme relative to the repo root.
	 */
	private function theme_dir(): string {
		return dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/camino-del-dharma';
	}
}
