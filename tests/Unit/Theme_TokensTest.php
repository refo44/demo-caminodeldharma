<?php
/**
 * Level 1: theme.json reproduces the static production tokens exactly
 * (WU-04 visual baseline, ADR 0029).
 *
 * The expected values are extracted programmatically from the :root block
 * of static/assets/css/main.css — the live production tokens — so this
 * suite asserts reproduction, not a retyped copy
 * (docs/guia-pruebas-plugin-theme-fse.md §4).
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: visual token parity between static/ and theme.json.
 */
final class Theme_TokensTest extends TestCase {

	/**
	 * Static :root tokens, parsed once per test run.
	 *
	 * @var array<string, string>|null
	 */
	private static $static_tokens = null;

	/**
	 * Protects the brand palette: every brand color and accessibility tint
	 * in the static :root appears in the theme.json palette with the exact
	 * same hex value, plus the literal text-on-dark white.
	 */
	public function test_palette_reproduces_the_static_brand_colors() {
		$palette = array();
		foreach ( $this->theme_json()['settings']['color']['palette'] as $entry ) {
			$palette[ $entry['slug'] ] = strtolower( $entry['color'] );
		}

		$brand_slugs = array( 'brand-1', 'brand-2', 'brand-3', 'brand-4', 'brand-1-deep', 'brand-2-deep' );
		foreach ( $brand_slugs as $slug ) {
			$this->assertSame(
				strtolower( $this->static_token( $slug ) ),
				$palette[ $slug ] ?? null,
				"Palette color {$slug} must equal the static token."
			);
		}

		$this->assertSame( strtolower( $this->static_token( 'text-on-dark' ) ), $palette['text-on-dark'] ?? null );
	}

	/**
	 * Protects the semantic color layer: each semantic role in the static
	 * :root (--bg, --link, --header-bg, ...) exists in settings.custom.color
	 * with the same brand indirection, translated to WordPress presets.
	 */
	public function test_semantic_color_roles_keep_the_static_indirection() {
		$custom_color = $this->theme_json()['settings']['custom']['color'];

		$semantic_roles = array(
			'bg',
			'text',
			'text-muted',
			'surface',
			'border',
			'link',
			'link-hover',
			'primary',
			'primary-hover',
			'header-bg',
			'footer-bg',
		);

		foreach ( $semantic_roles as $role ) {
			$static_value = $this->static_token( $role );

			$this->assertMatchesRegularExpression( '/^var\(--brand-[0-9a-z-]+\)$/', $static_value, "Static --{$role} must be a brand indirection." );

			$expected = str_replace( 'var(--brand-', 'var(--wp--preset--color--brand-', $static_value );

			$this->assertSame(
				$expected,
				$custom_color[ $this->camel_case( $role ) ] ?? null,
				"Semantic role {$role} must keep the static brand indirection."
			);
		}

		$this->assertSame( 'var(--wp--preset--color--text-on-dark)', $custom_color['textOnDark'] ?? null );
	}

	/**
	 * Protects the typography stacks: the three static font tokens are
	 * reproduced character-for-character as theme.json font families.
	 */
	public function test_font_families_reproduce_the_static_stacks() {
		$families = array();
		foreach ( $this->theme_json()['settings']['typography']['fontFamilies'] as $entry ) {
			$families[ $entry['slug'] ] = $entry['fontFamily'];
		}

		$this->assertSame( $this->static_token( 'font-display' ), $families['display'] ?? null );
		$this->assertSame( $this->static_token( 'font-heading' ), $families['heading'] ?? null );
		$this->assertSame( $this->static_token( 'font-body' ), $families['body'] ?? null );
	}

	/**
	 * Protects the spacing scale: --space-xs..xl map to spacingSizes with
	 * identical values.
	 */
	public function test_spacing_scale_reproduces_the_static_tokens() {
		$sizes = array();
		foreach ( $this->theme_json()['settings']['spacing']['spacingSizes'] as $entry ) {
			$sizes[ $entry['slug'] ] = $entry['size'];
		}

		foreach ( array( 'xs', 'sm', 'md', 'lg', 'xl' ) as $step ) {
			$this->assertSame(
				$this->static_token( 'space-' . $step ),
				$sizes[ $step ] ?? null,
				"Spacing size {$step} must equal the static token."
			);
		}
	}

	/**
	 * Protects the layout widths: reading measure and container max carry
	 * over as contentSize and wideSize.
	 */
	public function test_layout_widths_reproduce_the_static_tokens() {
		$layout = $this->theme_json()['settings']['layout'];

		$this->assertSame( $this->static_token( 'read-max' ), $layout['contentSize'] );
		$this->assertSame( $this->static_token( 'container-max' ), $layout['wideSize'] );
	}

	/**
	 * Protects the remaining rhythm tokens (gutter, section gap, header
	 * offset, grid gap, line heights) as settings.custom values.
	 */
	public function test_rhythm_tokens_reproduce_the_static_tokens() {
		$custom = $this->theme_json()['settings']['custom'];

		$this->assertSame( $this->static_token( 'gutter' ), $custom['gutter'] );
		$this->assertSame( $this->static_token( 'section-gap' ), $custom['sectionGap'] );
		$this->assertSame( $this->static_token( 'header-offset' ), $custom['headerOffset'] );
		$this->assertSame( $this->static_token( 'grid-gap' ), $custom['gridGap'] );
		$this->assertSame( $this->static_token( 'line-height-body' ), (string) $custom['lineHeight']['body'] );
		$this->assertSame( $this->static_token( 'line-height-tight' ), (string) $custom['lineHeight']['tight'] );
	}

	/**
	 * Protects the palette-only policy (docs/12 §8: only the brand manual
	 * colors): free-form color pickers and the WordPress default palette
	 * stay off, so no hex outside the palette can enter from the editor.
	 */
	public function test_editor_color_policy_is_palette_only() {
		$color = $this->theme_json()['settings']['color'];

		$this->assertFalse( $color['custom'] );
		$this->assertFalse( $color['customGradient'] );
		$this->assertFalse( $color['defaultPalette'] );
		$this->assertFalse( $color['defaultGradients'] );
	}

	/**
	 * One static token by name (without the -- prefix), failing loudly when
	 * the extraction finds nothing.
	 */
	private function static_token( string $name ): string {
		$tokens = $this->static_tokens();

		$this->assertArrayHasKey( $name, $tokens, "Static :root must define --{$name}." );

		return $tokens[ $name ];
	}

	/**
	 * All custom properties of the static :root block, parsed from
	 * static/assets/css/main.css with comments stripped.
	 *
	 * @return array<string, string>
	 */
	private function static_tokens(): array {
		if ( null !== self::$static_tokens ) {
			return self::$static_tokens;
		}

		$css = file_get_contents( dirname( __DIR__, 2 ) . '/static/assets/css/main.css' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local repo file in a unit test without WordPress loaded.

		$this->assertSame( 1, preg_match( '/^:root\s*\{(.*?)^\}/ms', $css, $root_block ), 'static main.css must contain a :root block.' );

		$declarations = preg_replace( '#/\*.*?\*/#s', '', $root_block[1] );

		preg_match_all( '/--([a-z0-9-]+)\s*:\s*([^;]+);/', $declarations, $matches, PREG_SET_ORDER );

		$tokens = array();
		foreach ( $matches as $match ) {
			$tokens[ $match[1] ] = trim( $match[2] );
		}

		self::$static_tokens = $tokens;

		return $tokens;
	}

	/**
	 * kebab-case token name to the camelCase key used in theme.json custom
	 * settings (WordPress converts camelCase back to kebab in CSS output).
	 */
	private function camel_case( string $kebab ): string {
		return lcfirst( str_replace( '-', '', ucwords( $kebab, '-' ) ) );
	}

	/**
	 * Decoded theme.json of the theme under test.
	 */
	private function theme_json(): array {
		$path = dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/camino-del-dharma/theme.json';

		$this->assertFileExists( $path );

		$decoded = json_decode( file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local repo file in a unit test without WordPress loaded.

		$this->assertIsArray( $decoded, 'theme.json must be valid JSON.' );

		return $decoded;
	}
}
