<?php
/**
 * Level 1: the converted album galleries render as the uniform square grid
 * of published production instead of core's ragged flex layout.
 *
 * Written RED-first. On staging /galeria the native `core/gallery` block
 * (`is-layout-flex`, `columns-3`, `is-cropped`) sizes every tile from the
 * intrinsic ratio of its image: portrait and landscape shots came out at
 * 320x427, 320x240 and 484x323 in the same album. Production renders every
 * tile as a square crop (`aspect-ratio: 1; object-fit: cover`) in a 2/3/4
 * column grid.
 *
 * Core sizes each figure with
 * `.wp-block-gallery.has-nested-images.columns-N figure.wp-block-image:not(#individual-image)`,
 * so the theme rule that resets the width has to outrank it on specificity
 * alone, never on the order in which WordPress prints the block stylesheet.
 * The guide forbids pixel assertions in CI, so the contract is the CSS.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: uniform grid for native album galleries.
 */
final class Theme_Gallery_GridTest extends TestCase {

	/**
	 * Core's per-figure width rule the theme has to defeat.
	 */
	const CORE_FIGURE_SELECTOR = '.wp-block-gallery.has-nested-images.columns-3 figure.wp-block-image:not(#individual-image)';

	public function test_the_native_gallery_becomes_a_grid() {
		$this->assertNotNull(
			$this->declaration_for( $this->container_rules(), 'display', 'grid' ),
			'A theme rule must set display: grid on .wp-block-gallery.has-nested-images.'
		);
	}

	public function test_the_grid_grows_from_two_to_four_columns_like_production() {
		$columns = array();
		foreach ( $this->container_rules() as $rule ) {
			if ( isset( $rule['declarations']['grid-template-columns'] ) ) {
				$columns[] = preg_replace( '/\s+/', '', $rule['declarations']['grid-template-columns'] );
			}
		}

		foreach ( array( 2, 3, 4 ) as $count ) {
			$this->assertContains(
				"repeat({$count},minmax(0,1fr))",
				$columns,
				"Missing the {$count}-column breakpoint of the gallery grid."
			);
		}
	}

	public function test_a_rule_outranks_core_figure_width_on_specificity_alone() {
		$core    = $this->specificity( self::CORE_FIGURE_SELECTOR );
		$winners = array();

		foreach ( $this->figure_rules() as $rule ) {
			foreach ( $rule['selectors'] as $selector ) {
				if ( $this->specificity( $selector ) > $core ) {
					$winners[] = $rule;
					break;
				}
			}
		}

		$this->assertNotEmpty( $winners, 'No theme selector outranks core\'s figure width rule.' );

		return $winners;
	}

	/**
	 * @depends test_a_rule_outranks_core_figure_width_on_specificity_alone
	 *
	 * @param array $winners Rules that already beat core on specificity.
	 */
	public function test_the_winning_rule_fills_the_grid_cell( array $winners ) {
		$fills = false;
		foreach ( $winners as $rule ) {
			$fills = $fills || ( isset( $rule['declarations']['width'] ) && '100%' === $rule['declarations']['width'] );
		}

		$this->assertTrue( $fills, 'The rule beating core must set the figure width to 100% of its grid cell.' );
	}

	public function test_every_tile_is_a_square_crop() {
		$square = false;
		foreach ( $this->rules() as $rule ) {
			foreach ( $rule['selectors'] as $selector ) {
				if ( false === strpos( $selector, '.wp-block-gallery' ) || ! preg_match( '/\bimg$/', $selector ) ) {
					continue;
				}
				$declarations = $rule['declarations'];
				$square       = $square || (
					'1' === ( $declarations['aspect-ratio'] ?? null )
					&& 'cover' === ( $declarations['object-fit'] ?? null )
					&& 'auto' === ( $declarations['height'] ?? null )
				);
			}
		}

		$this->assertTrue( $square, 'Gallery images need aspect-ratio: 1, object-fit: cover and height: auto (core forces height: 100%).' );
	}

	/**
	 * Rules whose selector targets the gallery container itself.
	 *
	 * @return array<int, array{selectors: string[], declarations: array<string, string>}>
	 */
	private function container_rules(): array {
		return $this->rules_matching( '/\.wp-block-gallery\.has-nested-images$/' );
	}

	/**
	 * Rules whose selector targets the figure of a nested image.
	 *
	 * @return array<int, array{selectors: string[], declarations: array<string, string>}>
	 */
	private function figure_rules(): array {
		return $this->rules_matching( '/\.wp-block-gallery[^,]*figure\.wp-block-image(:not\(#individual-image\))?$/' );
	}

	/**
	 * @param string $pattern Regex tested against each selector.
	 *
	 * @return array<int, array{selectors: string[], declarations: array<string, string>}>
	 */
	private function rules_matching( string $pattern ): array {
		$found = array();
		foreach ( $this->rules() as $rule ) {
			foreach ( $rule['selectors'] as $selector ) {
				if ( preg_match( $pattern, $selector ) ) {
					$found[] = $rule;
					break;
				}
			}
		}

		return $found;
	}

	/**
	 * @param array  $rules    Parsed rules.
	 * @param string $property Declaration name.
	 * @param string $value    Expected value.
	 */
	private function declaration_for( array $rules, string $property, string $value ) {
		foreach ( $rules as $rule ) {
			if ( ( $rule['declarations'][ $property ] ?? null ) === $value ) {
				return $rule;
			}
		}

		return null;
	}

	/**
	 * Cascade weight of a selector as a single comparable integer.
	 *
	 * @param string $selector One selector.
	 */
	private function specificity( string $selector ): int {
		$ids      = preg_match_all( '/#[\w-]+/', $selector );
		$classes  = preg_match_all( '/\.[\w-]+|\[[^\]]+\]|:(?!:)(?!not)[\w-]+/', $selector );
		$elements = preg_match_all( '/(^|[\s>+~])([a-z][\w-]*)/i', $selector );

		return ( $ids * 10000 ) + ( $classes * 100 ) + $elements;
	}

	/**
	 * The complementary stylesheet parsed into flat rules; at-rule bodies
	 * are flattened because a media query changes when, not which.
	 *
	 * @return array<int, array{selectors: string[], declarations: array<string, string>}>
	 */
	private function rules(): array {
		static $rules = null;
		if ( null !== $rules ) {
			return $rules;
		}

		$css = (string) file_get_contents( dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/camino-del-dharma/assets/css/main.css' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repo file in a unit test without WordPress loaded.
		$css = (string) preg_replace( '#/\*.*?\*/#s', '', $css );

		$rules = array();
		if ( ! preg_match_all( '/([^{}]+)\{([^{}]*)\}/s', $css, $matches, PREG_SET_ORDER ) ) {
			return $rules;
		}

		foreach ( $matches as $match ) {
			$prelude = trim( $match[1] );
			if ( '' === $prelude || '@' === $prelude[0] ) {
				continue;
			}

			$declarations = array();
			foreach ( explode( ';', $match[2] ) as $declaration ) {
				if ( false === strpos( $declaration, ':' ) ) {
					continue;
				}
				list( $property, $value ) = explode( ':', $declaration, 2 );

				$declarations[ strtolower( trim( $property ) ) ] = trim( $value );
			}

			$rules[] = array(
				'selectors'    => array_map( 'trim', explode( ',', $prelude ) ),
				'declarations' => $declarations,
			);
		}

		return $rules;
	}
}
