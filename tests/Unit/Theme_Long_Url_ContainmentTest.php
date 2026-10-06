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
	 * A desktop-only query must not satisfy the 320 px contract. The
	 * measured overflow is at that width.
	 */
	public function test_a_media_query_that_excludes_320px_does_not_wrap_the_url() {
		$css = '@media (width >= 768px) { .wp-block-post-content { overflow-wrap: anywhere; } }';

		$this->assertSame( array(), $this->wrapping_selectors_in( $css ) );
	}

	/**
	 * A query that still matches 320 px keeps the wrap. An unconditional
	 * rule does too.
	 */
	public function test_a_media_query_that_includes_320px_still_wraps_the_url() {
		$narrow = '@media (width <= 767px) { .wp-block-post-content { overflow-wrap: anywhere; } }';
		$plain  = '.wp-block-post-content { overflow-wrap: anywhere; }';

		$this->assertSame( array( '.wp-block-post-content' ), $this->wrapping_selectors_in( $narrow ) );
		$this->assertSame( array( '.wp-block-post-content' ), $this->wrapping_selectors_in( $plain ) );
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
			if ( ! $this->applies_at_320( $rule ) ) {
				continue;
			}

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
			if ( ! $this->applies_at_320( $rule ) ) {
				continue;
			}

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
		return $this->wrapping_selectors_in( $this->stylesheet() );
	}

	/**
	 * Selectors whose wrap applies at 320 px.
	 *
	 * @param string $css Stylesheet text.
	 * @return string[]
	 */
	private function wrapping_selectors_in( string $css ): array {
		$found = array();
		foreach ( $this->parse_rules( $css ) as $rule ) {
			if ( ! $this->applies_at_320( $rule ) ) {
				continue;
			}

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
	 * True when every enclosing media query matches a 320 px viewport.
	 * No query means the rule always applies.
	 *
	 * @param array{media: string[]} $rule One parsed rule.
	 */
	private function applies_at_320( array $rule ): bool {
		foreach ( $rule['media'] as $condition ) {
			if ( ! $this->media_matches_320( $condition ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * True when a 320 px viewport matches the media condition. Comma
	 * lists are OR. `and` lists are AND. Width features use the CSS
	 * initial font size (16 px per rem/em). Any other feature, such as
	 * `forced-colors`, does not describe that viewport.
	 *
	 * @param string $condition Text after `@media`.
	 */
	private function media_matches_320( string $condition ): bool {
		foreach ( $this->split_top_level( $condition, ',' ) as $query ) {
			if ( $this->query_matches_320( $query ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * True when one media query, without a top-level comma, matches 320 px.
	 *
	 * @param string $query One comma-separated query.
	 */
	private function query_matches_320( string $query ): bool {
		$query   = trim( $query );
		$negated = 1 === preg_match( '/^not\s+/i', $query );
		if ( $negated ) {
			$query = (string) preg_replace( '/^not\s+/i', '', $query );
		}

		$query = (string) preg_replace( '/^(only\s+)?(all|screen)\s+and\s+/i', '', $query );
		$query = (string) preg_replace( '/^(only\s+)?(all|screen)\s*$/i', '', trim( $query ) );

		if ( 1 === preg_match( '/^(only\s+)?print\b/i', $query ) ) {
			return $negated;
		}

		$matches = true;
		foreach ( $this->split_top_level( $query, 'and' ) as $part ) {
			$part = trim( $part );
			if ( '' === $part ) {
				continue;
			}

			if ( ! $this->width_feature_matches_320( $part ) ) {
				$matches = false;
				break;
			}
		}

		return $negated ? ! $matches : $matches;
	}

	/**
	 * True when one width feature matches 320 px.
	 *
	 * @param string $feature One `and` operand, parentheses optional.
	 */
	private function width_feature_matches_320( string $feature ): bool {
		$feature = trim( $feature, " \t()" );
		if ( 1 !== preg_match( '/^(width|min-width|max-width)\s*(<=|>=|<|>|:)\s*([0-9.]+)\s*(px|rem|em)$/i', $feature, $matches ) ) {
			return false;
		}

		$pixels = (float) $matches[3];
		if ( 'px' !== strtolower( $matches[4] ) ) {
			$pixels *= 16;
		}

		$name = strtolower( $matches[1] );
		$op   = $matches[2];
		if ( 'min-width' === $name ) {
			return 320 >= $pixels;
		}
		if ( 'max-width' === $name ) {
			return 320 <= $pixels;
		}
		if ( '<=' === $op ) {
			return 320 <= $pixels;
		}
		if ( '>=' === $op ) {
			return 320 >= $pixels;
		}
		if ( '<' === $op ) {
			return 320 < $pixels;
		}

		return 320 > $pixels;
	}

	/**
	 * Split on a top-level separator. Parentheses stay intact.
	 *
	 * @param string $value     Media condition text.
	 * @param string $separator `,` or `and`.
	 * @return string[]
	 */
	private function split_top_level( string $value, string $separator ): array {
		$parts   = array();
		$current = '';
		$depth   = 0;
		$length  = strlen( $value );
		$sep_len = strlen( $separator );

		for ( $i = 0; $i < $length; $i++ ) {
			$char = $value[ $i ];
			if ( '(' === $char ) {
				++$depth;
				$current .= $char;
				continue;
			}
			if ( ')' === $char ) {
				$depth    = max( 0, $depth - 1 );
				$current .= $char;
				continue;
			}

			$at_separator = 0 === $depth && substr( $value, $i, $sep_len ) === $separator;
			if ( $at_separator && 'and' === $separator ) {
				$before       = $i > 0 ? $value[ $i - 1 ] : ' ';
				$after        = ( $i + $sep_len ) < $length ? $value[ $i + $sep_len ] : ' ';
				$at_separator = 1 === preg_match( '/\s/', $before ) && 1 === preg_match( '/\s/', $after );
			}

			if ( $at_separator ) {
				$parts[] = $current;
				$current = '';
				$i      += $sep_len - 1;
				continue;
			}

			$current .= $char;
		}

		$parts[] = $current;

		return $parts;
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
	 * Theme stylesheet text.
	 */
	private function stylesheet(): string {
		return (string) file_get_contents( dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/camino-del-dharma/assets/css/main.css' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repo file in a unit test without WordPress loaded.
	}

	/**
	 * Parsed theme rules. Each rule keeps the `@media` conditions that
	 * enclose it so a desktop-only query cannot satisfy the 320 px wrap.
	 *
	 * @return array<int, array{selectors: string[], declarations: array<string, string>, media: string[]}>
	 */
	private function rules(): array {
		static $rules = null;
		if ( null === $rules ) {
			$rules = $this->parse_rules( $this->stylesheet() );
		}

		return $rules;
	}

	/**
	 * Parse style rules, keeping a stack of `@media` conditions.
	 *
	 * @param string   $css   Stylesheet or at-rule body.
	 * @param string[] $media Enclosing `@media` conditions, outer first.
	 * @return array<int, array{selectors: string[], declarations: array<string, string>, media: string[]}>
	 */
	private function parse_rules( string $css, array $media = array() ): array {
		$css    = (string) preg_replace( '#/\*.*?\*/#s', '', $css );
		$rules  = array();
		$length = strlen( $css );
		$offset = 0;

		while ( $offset < $length ) {
			$open = strpos( $css, '{', $offset );
			if ( false === $open ) {
				break;
			}

			$close   = $this->matching_brace( $css, $open );
			$prelude = trim( substr( $css, $offset, $open - $offset ) );
			$body    = substr( $css, $open + 1, $close - $open - 1 );

			if ( '' !== $prelude && '@' === $prelude[0] ) {
				if ( 0 === stripos( $prelude, '@media' ) ) {
					$condition = trim( substr( $prelude, strlen( '@media' ) ) );
					foreach ( $this->parse_rules( $body, array_merge( $media, array( $condition ) ) ) as $rule ) {
						$rules[] = $rule;
					}
				}
			} elseif ( '' !== $prelude ) {
				$rules[] = array(
					'selectors'    => array_map( 'trim', explode( ',', $prelude ) ),
					'declarations' => $this->declarations( $body ),
					'media'        => $media,
				);
			}

			$offset = $close + 1;
		}

		return $rules;
	}

	/**
	 * Index of the `}` that closes the `{` at $open.
	 *
	 * @param string $css  Stylesheet text.
	 * @param int    $open Index of an opening brace.
	 */
	private function matching_brace( string $css, int $open ): int {
		$depth  = 0;
		$length = strlen( $css );

		for ( $i = $open; $i < $length; $i++ ) {
			if ( '{' === $css[ $i ] ) {
				++$depth;
			} elseif ( '}' === $css[ $i ] ) {
				--$depth;
				if ( 0 === $depth ) {
					return $i;
				}
			}
		}

		return $length - 1;
	}

	/**
	 * Declaration block as a property map.
	 *
	 * @param string $body Text between braces.
	 * @return array<string, string>
	 */
	private function declarations( string $body ): array {
		$declarations = array();
		foreach ( explode( ';', $body ) as $declaration ) {
			if ( false === strpos( $declaration, ':' ) ) {
				continue;
			}

			list( $property, $value )                        = explode( ':', $declaration, 2 );
			$declarations[ strtolower( trim( $property ) ) ] = trim( $value );
		}

		return $declarations;
	}
}
