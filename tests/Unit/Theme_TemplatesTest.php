<?php
/**
 * Level 1: the real FSE template set (WU-07, ADR 0029, docs/12 §5–§6).
 *
 * Written RED-first: these tests name the template/part/pattern contract
 * before the real view files exist. A template file never creates the Page
 * (ADR 0032); these checks are about presentation structure only.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: the theme ships every view docs/12 §6 maps.
 */
final class Theme_TemplatesTest extends TestCase {

	const TEMPLATES = array(
		'index',
		'front-page',
		'page',
		'page-comunidad',
		'page-linaje',
		'page-practica',
		'page-galeria',
		'page-donaciones',
		'page-contacto',
		'home',
		'single',
		'single-event',
		'archive-event',
		'single-blog_author',
		'archive-blog_author',
		'archive',
		'taxonomy-gallery_album',
		'404',
	);

	/**
	 * Blocks the theme registers for views core blocks cannot express
	 * (docs/12 §2 and §11.3: dynamic block, not template hacks).
	 */
	const DYNAMIC_BLOCKS = array(
		'camino-del-dharma/eventos-calendar',
		'camino-del-dharma/eventos-listado',
		'camino-del-dharma/evento-destacado',
		'camino-del-dharma/evento-tipo',
		'camino-del-dharma/evento-meta',
		'camino-del-dharma/evento-cta',
		'camino-del-dharma/entrada-cabecera',
		'camino-del-dharma/blog-listado',
		'camino-del-dharma/blog-recientes',
		'camino-del-dharma/autor-ficha',
		'camino-del-dharma/album-galeria',
	);

	/**
	 * Protects docs/12 §6: every mapped route has its block template file.
	 */
	public function test_every_mapped_template_exists() {
		foreach ( self::TEMPLATES as $name ) {
			$this->assertFileExists( $this->theme_dir() . '/templates/' . $name . '.html', $name );
		}
	}

	/**
	 * Protects doc 12 §1: /eventos resolves through the CPT archive; a
	 * page-eventos.html would invite a Page with slug eventos (forbidden).
	 */
	public function test_no_page_eventos_template_exists() {
		$this->assertFileDoesNotExist( $this->theme_dir() . '/templates/page-eventos.html' );
	}

	/**
	 * Protects docs/12 §4: every template composes the shared header and
	 * footer template parts instead of repeating chrome markup.
	 */
	public function test_every_template_uses_header_and_footer_parts() {
		foreach ( self::TEMPLATES as $name ) {
			$html = $this->template( $name );
			$this->assertStringContainsString( 'wp:template-part {"slug":"header"', $html, $name );
			$this->assertStringContainsString( 'wp:template-part {"slug":"footer"', $html, $name );
		}
	}

	/**
	 * Protects the skip-link target contract of the static mockup
	 * (docs/19): every template renders a main landmark with id="main".
	 */
	public function test_every_template_declares_the_main_landmark() {
		foreach ( self::TEMPLATES as $name ) {
			$html = $this->template( $name );
			$this->assertStringContainsString( '"tagName":"main"', $html, $name );
			$this->assertStringContainsString( '"anchor":"main"', $html, $name );
		}
	}

	/**
	 * Protects ADR 0032 (Template ≠ Page): institutional pages render the
	 * imported editorial content through Post Content, never hardcoded copy.
	 */
	public function test_page_templates_render_post_content() {
		foreach ( array( 'front-page', 'page', 'page-comunidad', 'page-linaje', 'page-practica', 'page-galeria', 'page-donaciones', 'page-contacto' ) as $name ) {
			$this->assertStringContainsString( 'wp:post-content', $this->template( $name ), $name );
		}
	}

	/**
	 * Protects doc 03 §3 / docs/12 §2: the events archive is the calendar
	 * dynamic block plus the current/past listing block.
	 */
	public function test_archive_event_composes_calendar_and_listing_blocks() {
		$html = $this->template( 'archive-event' );

		$this->assertStringContainsString( 'wp:camino-del-dharma/eventos-calendar', $html );
		$this->assertStringContainsString( 'wp:camino-del-dharma/eventos-listado', $html );
	}

	/**
	 * Protects the single-event contract (doc 03 §2, static mockup): type
	 * label, native title/featured image/content, meta list and status-aware
	 * CTA — in that order.
	 */
	public function test_single_event_composes_native_fields_and_event_blocks() {
		$html = $this->template( 'single-event' );

		$needles = array(
			'wp:camino-del-dharma/evento-tipo',
			'wp:post-title',
			'wp:post-featured-image',
			'wp:post-content',
			'wp:camino-del-dharma/evento-meta',
			'wp:camino-del-dharma/evento-cta',
		);

		$offset = 0;
		foreach ( $needles as $needle ) {
			$position = strpos( $html, $needle, $offset );
			$this->assertNotFalse( $position, $needle );
			$offset = $position;
		}
	}

	/**
	 * Protects ADR 0037: blog views render the CPT byline header, and the
	 * author profile view exists as its own template with the profile block.
	 */
	public function test_blog_views_use_byline_and_profile_blocks() {
		$this->assertStringContainsString( 'wp:camino-del-dharma/entrada-cabecera', $this->template( 'single' ) );
		$this->assertStringContainsString( 'wp:post-content', $this->template( 'single' ) );
		$this->assertStringContainsString( 'wp:camino-del-dharma/blog-listado', $this->template( 'home' ) );
		$this->assertStringContainsString( 'wp:camino-del-dharma/autor-ficha', $this->template( 'single-blog_author' ) );
	}

	/**
	 * Protects ADR 0036: album term routes render the native-gallery album
	 * block, not a custom gallery system.
	 */
	public function test_album_template_uses_album_block() {
		$this->assertStringContainsString( 'wp:camino-del-dharma/album-galeria', $this->template( 'taxonomy-gallery_album' ) );
	}

	/**
	 * Protects the published 404 copy (OWN-007, static 404.html).
	 */
	public function test_404_template_keeps_the_published_copy() {
		$html = $this->template( '404' );

		$this->assertStringContainsString( 'Esta página no existe.', $html );
		$this->assertStringContainsString( 'Volver al inicio', $html );
	}

	/**
	 * Protects docs/12 §11.3 (no hardcoded URLs in block templates): the
	 * chrome that needs generated URLs lives in patterns, referenced from
	 * the parts.
	 */
	public function test_parts_delegate_to_php_patterns() {
		$header = file_get_contents( $this->theme_dir() . '/parts/header.html' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local repo file in a unit test without WordPress loaded.
		$footer = file_get_contents( $this->theme_dir() . '/parts/footer.html' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local repo file in a unit test without WordPress loaded.

		$this->assertStringContainsString( 'wp:pattern {"slug":"camino-del-dharma/header"}', $header );
		$this->assertStringContainsString( 'wp:pattern {"slug":"camino-del-dharma/footer"}', $footer );
		$this->assertFileExists( $this->theme_dir() . '/patterns/header.php' );
		$this->assertFileExists( $this->theme_dir() . '/patterns/footer.php' );
	}

	/**
	 * Protects the absolute-URL ban inside block templates (docs/12 §11.3).
	 */
	public function test_templates_hardcode_no_site_urls() {
		foreach ( self::TEMPLATES as $name ) {
			$this->assertStringNotContainsString( 'caminodeldharma.org', $this->template( $name ), $name );
			$this->assertStringNotContainsString( 'localhost', $this->template( $name ), $name );
		}
	}

	/**
	 * Protects the token discipline of docs/12 §7: the complementary
	 * stylesheet consumes presets/custom properties, never redefines the
	 * palette or keeps the legacy :root token block.
	 */
	public function test_complementary_stylesheet_consumes_presets_only() {
		$css = file_get_contents( $this->theme_dir() . '/assets/css/main.css' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local repo file in a unit test without WordPress loaded.

		$this->assertStringContainsString( 'var(--wp--preset--color--brand-1)', $css );
		$this->assertStringContainsString( 'var(--wp--custom--color--bg)', $css );
		$this->assertDoesNotMatchRegularExpression( '/^:root\s*\{/m', $css );
		$this->assertStringNotContainsString( 'var(--brand-1)', $css );
		$this->assertStringNotContainsString( '--brand-1:', $css );
	}

	/**
	 * Protects the self-hosted font contract (docs/15, WU-04 pending item):
	 * theme.json declares fontFace entries whose woff2 files ship with the
	 * theme.
	 */
	public function test_theme_json_declares_font_faces_with_shipped_files() {
		$theme_json = json_decode( file_get_contents( $this->theme_dir() . '/theme.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local repo file in a unit test without WordPress loaded.

		$families = array();
		foreach ( $theme_json['settings']['typography']['fontFamilies'] as $family ) {
			$families[ $family['slug'] ] = $family;
		}

		foreach ( array( 'display', 'heading', 'body' ) as $slug ) {
			$this->assertNotEmpty( $families[ $slug ]['fontFace'] ?? array(), $slug );
			foreach ( $families[ $slug ]['fontFace'] as $face ) {
				foreach ( (array) $face['src'] as $src ) {
					$this->assertStringStartsWith( 'file:./', $src );
					$this->assertFileExists( $this->theme_dir() . '/' . substr( $src, strlen( 'file:./' ) ) );
				}
			}
		}
	}

	/**
	 * Protects the native-lightbox decision (ADR 0021): images enlarge on
	 * click through core, not through a plugin or custom viewer.
	 */
	public function test_theme_json_enables_the_native_lightbox() {
		$theme_json = json_decode( file_get_contents( $this->theme_dir() . '/theme.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local repo file in a unit test without WordPress loaded.

		$this->assertTrue( $theme_json['settings']['blocks']['core/image']['lightbox']['enabled'] ?? false );
	}

	/**
	 * Protects the navigation DOM contract of the static mockup (main.js
	 * selectors: #nav-toggle, #nav-menus): the ported script and the header
	 * pattern keep the ids the behavior depends on.
	 */
	public function test_header_pattern_and_js_keep_the_nav_contract() {
		$pattern = file_get_contents( $this->theme_dir() . '/patterns/header.php' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local repo file in a unit test without WordPress loaded.
		$js      = file_get_contents( $this->theme_dir() . '/assets/js/main.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local repo file in a unit test without WordPress loaded.

		foreach ( array( 'nav-toggle', 'nav-menus', 'skip-link', 'site-header', 'nav-menu', 'nav-sub', 'lang-switcher' ) as $hook ) {
			$this->assertStringContainsString( $hook, $pattern, $hook );
		}

		$this->assertStringContainsString( "getElementById('nav-toggle')", $js );
		$this->assertStringContainsString( "getElementById('nav-menus')", $js );
	}

	/**
	 * The template file content by name.
	 */
	private function template( string $name ): string {
		$path = $this->theme_dir() . '/templates/' . $name . '.html';

		return is_readable( $path ) ? file_get_contents( $path ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local repo file in a unit test without WordPress loaded.
	}

	/**
	 * docs/19 §9: one H1 per page. The archives that fall back to
	 * `index.html` — /author and /blog/tag/{slug} — had none, so a screen
	 * reader reached a list of links with no page heading. Every archive
	 * template names itself with the query title as an H1.
	 */
	public function test_archive_templates_carry_a_single_h1() {
		foreach ( array( 'archive', 'archive-blog_author' ) as $name ) {
			$html = $this->template( $name );

			$this->assertStringContainsString( 'wp:query-title', $html, $name );
			$this->assertStringContainsString( '"level":1', $html, $name );
			$this->assertSame( 1, substr_count( $html, '"level":1' ), $name );
		}
	}

	/**
	 * docs/19 §8: a decorative SVG must carry BOTH `aria-hidden="true"`
	 * and `focusable="false"`. Production ships four icons with only the
	 * first (two section icons and the two footer contact icons), so
	 * without this the port inherits a real defect. The attribute is
	 * invisible and additive: no copy and no layout changes (OWN-007
	 * protects the published copy, not an accessibility omission).
	 */
	public function test_decorative_svgs_are_hidden_and_unfocusable() {
		$sources = array(
			'patterns/header.php',
			'patterns/footer.php',
			'inc/class-camino-del-dharma-renderers.php',
		);

		foreach ( $sources as $source ) {
			$code = (string) file_get_contents( $this->theme_dir() . '/' . $source ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repo files in a unit test without WordPress.

			preg_match_all( '/<svg\b[^>]*>/', $code, $matches );
			$this->assertNotEmpty( $matches[0], $source );

			foreach ( $matches[0] as $svg ) {
				$this->assertStringContainsString( 'aria-hidden="true"', $svg, $source );
				$this->assertStringContainsString( 'focusable="false"', $svg, $source );
			}
		}
	}

	/**
	 * Path of the theme relative to the repo root.
	 */
	private function theme_dir(): string {
		return dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/camino-del-dharma';
	}
}
