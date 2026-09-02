<?php
/**
 * Level 1: the converted mantra players stay inside their column at a
 * 320 px viewport (D-04 / OWN-026, issue #12).
 *
 * Written RED-first. WU-10 measured /practica at 320 px and found
 * `scrollWidth` 324 vs `clientWidth` 320 — the only visual regression
 * against published production, which renders the player at 272 px and
 * does not overflow. The cause is WordPress core's own audio block
 * stylesheet:
 *
 *     .wp-block-audio audio { width: 100%; min-width: 300px; }
 *
 * That 300 px floor beats the theme's `width` declaration (a floor wins
 * over a preferred width) and stretches the whole content column.
 *
 * The guide forbids pixel assertions in CI (docs/guia-pruebas-plugin-theme-fse.md),
 * so the contract lives here as CSS the browser cannot resolve any other
 * way: the theme rule must reach the converted player, must outrank the
 * core rule on specificity alone — never on the order in which WordPress
 * happens to print the block stylesheet — and must lift the floor rather
 * than hide the symptom on `html`/`body`.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: narrow-viewport containment of the mantra players.
 */
final class Theme_Audio_ContainmentTest extends TestCase {

	/**
	 * The declaration the theme has to defeat, as shipped by core in
	 * wp-includes/blocks/audio/style.css (WordPress 7.1).
	 */
	const CORE_AUDIO_SELECTOR = '.wp-block-audio audio';

	/**
	 * The converter emits `<figure class="wp-block-audio mantra-audio">`
	 * (Content_ConverterTest), so the theme rule has to match an `audio`
	 * that carries both classes on its figure.
	 */
	public function test_a_theme_rule_reaches_the_converted_mantra_player() {
		$this->assertNotEmpty(
			$this->mantra_audio_rules(),
			'The theme stylesheet must style the audio of figure.wp-block-audio.mantra-audio.'
		);
	}

	/**
	 * Protects the fix against load order: `wp-block-audio-inline-css` is
	 * printed by core and the theme cannot guarantee it comes first, so
	 * winning must not depend on it. At least one selector reaching the
	 * player must be strictly more specific than core's.
	 */
	public function test_the_theme_rule_outranks_core_on_specificity_alone() {
		$core = $this->specificity( self::CORE_AUDIO_SELECTOR );

		$winners = array();
		foreach ( $this->mantra_audio_rules() as $rule ) {
			foreach ( $rule['selectors'] as $selector ) {
				if ( $this->specificity( $selector ) > $core && $this->reaches_converted_player( $selector ) ) {
					$winners[] = $rule;
					break;
				}
			}
		}

		$this->assertNotEmpty(
			$winners,
			'No selector reaching the converted player outranks ' . self::CORE_AUDIO_SELECTOR . '.'
		);

		return $winners;
	}

	/**
	 * The regression itself: the winning rule must lift core's 300 px
	 * floor to zero and cap the player at its column, box borders and
	 * padding included. Without `min-width` the player cannot shrink
	 * below 300 px however small the column gets.
	 *
	 * @depends test_the_theme_rule_outranks_core_on_specificity_alone
	 *
	 * @param array $winners Rules that already beat core on specificity.
	 */
	public function test_the_winning_rule_lifts_the_floor_and_caps_the_player( array $winners ) {
		$contained = false;

		foreach ( $winners as $rule ) {
			$declarations = $rule['declarations'];

			$floor_lifted = isset( $declarations['min-width'] ) && $this->is_zero( $declarations['min-width'] );
			$capped       = isset( $declarations['max-width'] ) && '100%' === $declarations['max-width'];
			$border_box   = ! isset( $declarations['box-sizing'] ) || 'border-box' === $declarations['box-sizing'];

			if ( $floor_lifted && $capped && $border_box ) {
				$contained = true;
			}
		}

		$this->assertTrue(
			$contained,
			'The rule that beats core must set min-width to 0 and max-width to 100% on the player.'
		);
	}

	/**
	 * The published `width: min(100%, 32rem)` cap of the static site is
	 * presentation the migration keeps (OWN-007): the fix narrows the
	 * player at 320 px, it does not restyle it at every width.
	 */
	public function test_the_published_width_cap_survives() {
		$widths = array();
		foreach ( $this->mantra_audio_rules() as $rule ) {
			if ( isset( $rule['declarations']['width'] ) ) {
				$widths[] = preg_replace( '/\s+/', '', $rule['declarations']['width'] );
			}
		}

		$this->assertContains( 'min(100%,32rem)', $widths );
	}

	/**
	 * Protects D-09 and every other page from a blanket cure: hiding the
	 * overflow on the document would mask the Sangha long-URL overflow
	 * the owner decided to keep (OWN-021) and would silently kill
	 * horizontal scrolling everywhere.
	 */
	public function test_the_theme_does_not_hide_overflow_on_the_document() {
		foreach ( $this->rules() as $rule ) {
			foreach ( $rule['selectors'] as $selector ) {
				if ( ! preg_match( '/^(html|body|html\s*,\s*body)$/', $selector ) ) {
					continue;
				}

				foreach ( array( 'overflow', 'overflow-x' ) as $property ) {
					$this->assertNotEquals(
						'hidden',
						$rule['declarations'][ $property ] ?? null,
						"{$selector} must not hide horizontal overflow."
					);
				}
			}
		}
	}

	/**
	 * Rules whose selector list reaches the `audio` of a mantra player.
	 *
	 * @return array<int, array{selectors: string[], declarations: array<string, string>}>
	 */
	private function mantra_audio_rules(): array {
		$found = array();
		foreach ( $this->rules() as $rule ) {
			foreach ( $rule['selectors'] as $selector ) {
				if ( preg_match( '/\.mantra-audio\b[^,]*\baudio$/', $selector ) ) {
					$found[] = $rule;
					break;
				}
			}
		}

		return $found;
	}

	/**
	 * True when the selector matches a figure carrying both the core
	 * block class and the converter's hook, i.e. the real converted DOM.
	 *
	 * @param string $selector One selector of a rule.
	 */
	private function reaches_converted_player( string $selector ): bool {
		return false !== strpos( $selector, '.wp-block-audio' )
			&& false !== strpos( $selector, '.mantra-audio' );
	}

	/**
	 * Cascade weight of a selector as a single comparable integer
	 * (ids, classes/attributes/pseudo-classes, elements). Enough for the
	 * flat, class-based selectors this stylesheet uses.
	 *
	 * @param string $selector One selector.
	 */
	private function specificity( string $selector ): int {
		$ids      = preg_match_all( '/#[\w-]+/', $selector );
		$classes  = preg_match_all( '/\.[\w-]+|\[[^\]]+\]|:(?!:)[\w-]+/', $selector );
		$elements = preg_match_all( '/(^|[\s>+~])([a-z][\w-]*)/i', $selector );

		return ( $ids * 10000 ) + ( $classes * 100 ) + $elements;
	}

	/**
	 * True for a length that resolves to zero, with or without a unit.
	 *
	 * @param string $value Declaration value.
	 */
	private function is_zero( string $value ): bool {
		return 1 === preg_match( '/^0(?:[a-z%]+)?$/i', trim( $value ) );
	}

	/**
	 * The complementary stylesheet parsed into flat rules. At-rule
	 * bodies are flattened: a media query does not change which
	 * declarations exist, only when they apply.
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
