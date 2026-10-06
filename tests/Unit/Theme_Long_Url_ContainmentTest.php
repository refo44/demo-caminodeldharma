<?php
/**
 * Level 1: a long URL in post content stays inside the column at 320 px
 * (POST-008 / OWN-021 / D-09, issue #7).
 *
 * Written RED-first. `/blog/sangha-refugio-hiperconexion` measured
 * `scrollWidth` 339 vs `clientWidth` 320 because the article prints
 * `https://www.who.int/groups/commission-on-social-connection` as the
 * text of a link inside `.article-references`, and that string has no
 * ordinary wrap point. The owner left the overflow through cutover so
 * WordPress would match the published static site. WordPress has served
 * the canonical domain since 2026-09-26, so the wrap is now theme CSS.
 *
 * The guide forbids pixel assertions in CI (docs/guia-pruebas-plugin-theme-fse.md).
 * The contract is the declaration that lets the URL break inside the
 * column and shrink the content's min-content size: `overflow-wrap:
 * anywhere` on the post content (it inherits to the link). Clipping the
 * document, or breaking every word with `break-all`, is a different fix.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: narrow-viewport containment of a long editorial URL.
 */
final class Theme_Long_Url_ContainmentTest extends TestCase {

	/**
	 * The reference link lives in the converted article body:
	 * `.wp-block-post-content .article-references a`.
	 */
	public function test_post_content_breaks_a_long_url_inside_the_column() {
		$wrapping = $this->wrapping_selectors();

		$this->assertNotEmpty(
			$wrapping,
			'Post content must set overflow-wrap to anywhere so the Sangha reference URL breaks inside the column.'
		);
	}

	/**
	 * `anywhere` is the value that both wraps the URL and lets the
	 * content's min-content shrink. `break-word` leaves the min-content
	 * at the full URL, so a flex ancestor can still widen the page.
	 *
	 * @depends test_post_content_breaks_a_long_url_inside_the_column
	 */
	public function test_the_wrap_uses_anywhere_rather_than_clipping_the_url() {
		foreach ( $this->rules() as $rule ) {
			foreach ( $rule['selectors'] as $selector ) {
				if ( ! $this->reaches_reference_link( $selector ) ) {
					continue;
				}

				$this->assertNotSame(
					'hidden',
					$rule['declarations']['overflow-x'] ?? null,
					"{$selector} must wrap the URL, not clip it."
				);
				$this->assertNotSame(
					'hidden',
					$rule['declarations']['overflow'] ?? null,
					"{$selector} must wrap the URL, not clip it."
				);
			}
		}

		foreach ( $this->rules() as $rule ) {
			$matched = false;
			foreach ( $rule['selectors'] as $selector ) {
				if ( in_array( $selector, $this->wrapping_selectors(), true ) ) {
					$matched = true;
				}
			}

			if ( ! $matched ) {
				continue;
			}

			$this->assertNotSame(
				'break-all',
				$rule['declarations']['word-break'] ?? null,
				'The wrap must not break every word in the article.'
			);
		}
	}

	/**
	 * Selectors that put `overflow-wrap: anywhere` on the reference link
	 * or on an ancestor the property inherits from.
	 *
	 * @return string[]
	 */
	private function wrapping_selectors(): array {
		$found = array();
		foreach ( $this->rules() as $rule ) {
			if ( 'anywhere' !== ( $rule['declarations']['overflow-wrap'] ?? null ) ) {
				continue;
			}

			foreach ( $rule['selectors'] as $selector ) {
				if ( $this->reaches_reference_link( $selector ) ) {
					$found[] = $selector;
				}
			}
		}

		return $found;
	}

	/**
	 * True when the selector's subject is the post-content or references
	 * container (the property inherits to the link) or the reference
	 * anchor itself. A descendant that merely shares the container class,
	 * such as `.wp-block-post-content img`, does not count.
	 *
	 * @param string $selector One selector of a rule.
	 */
	private function reaches_reference_link( string $selector ): bool {
		$subject = $this->subject_compound( $selector );

		if ( $this->compound_is_container( $subject ) ) {
			return true;
		}

		if ( ! $this->compound_is_anchor( $subject ) ) {
			return false;
		}

		return $this->selector_includes_container( $selector );
	}

	/**
	 * The rightmost compound, with pseudo-classes removed.
	 *
	 * @param string $selector One selector of a rule.
	 */
	private function subject_compound( string $selector ): string {
		$plain = preg_replace( '/::?[a-zA-Z0-9_-]+(\([^)]*\))?/', '', $selector );
		$parts = preg_split( '/\s*[>+~]\s*|\s+/', trim( (string) $plain ) );
		$parts = array_values( array_filter( $parts, 'strlen' ) );

		if ( array() === $parts ) {
			return '';
		}

		return (string) $parts[ count( $parts ) - 1 ];
	}

	/**
	 * True when the compound is one container, not both classes on the
	 * same element. The published markup nests them:
	 * `.wp-block-post-content .article-references`.
	 *
	 * @param string $compound One compound selector.
	 */
	private function compound_is_container( string $compound ): bool {
		return '.wp-block-post-content' === $compound
			|| '.article-references' === $compound;
	}

	/**
	 * True when the compound is an `a`, ignoring classes and attributes.
	 *
	 * @param string $compound One compound selector.
	 */
	private function compound_is_anchor( string $compound ): bool {
		$rest = preg_replace( '/\.[a-zA-Z0-9_-]+|\[[^\]]+\]/', '', $compound );

		return 'a' === $rest;
	}

	/**
	 * True when some compound in the selector is the post body or the
	 * references section.
	 *
	 * @param string $selector One selector of a rule.
	 */
	private function selector_includes_container( string $selector ): bool {
		$plain = preg_replace( '/::?[a-zA-Z0-9_-]+(\([^)]*\))?/', '', $selector );
		$parts = preg_split( '/\s*[>+~]\s*|\s+/', trim( (string) $plain ) );

		foreach ( $parts as $part ) {
			if ( $this->compound_is_container( $part ) ) {
				return true;
			}
		}

		return false;
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
