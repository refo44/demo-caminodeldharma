<?php
/**
 * The `<head>` document as data (docs/15-assets-strategy.md §12).
 *
 * Returns an ordered list of tag descriptors — `title`, `meta`, `link`,
 * `jsonld` — that the theme prints and escapes. Keeping the document as
 * data (and out of the theme) is what lets the head be asserted without
 * booting WordPress, and what keeps `schema.org` out of presentation
 * code (ADR 0024).
 *
 * An empty value prints no tag at all: an empty `og:description` is
 * worse than an absent one.
 *
 * Pure domain code: no WordPress APIs.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the ordered head-tag list of one request.
 */
final class Cdd_Core_Seo_Document {

	/**
	 * The head tags for one resolved request context.
	 *
	 * @param array $context Resolved SEO context.
	 */
	public static function tags( array $context ): array {
		$get = static function ( string $key ) use ( $context ): string {
			return isset( $context[ $key ] ) && is_string( $context[ $key ] ) ? $context[ $key ] : '';
		};

		$canonical      = $get( 'canonical' );
		$og_title       = '' !== $get( 'og_title' ) ? $get( 'og_title' ) : $get( 'title' );
		$og_description = '' !== $get( 'og_description' ) ? $get( 'og_description' ) : $get( 'description' );
		$image          = $get( 'image' );

		$tags = array();

		self::title( $tags, $get( 'title' ) );
		self::meta( $tags, 'name', 'description', $get( 'description' ) );
		self::meta( $tags, 'name', 'keywords', $get( 'keywords' ) );
		self::meta( $tags, 'name', 'robots', $get( 'robots' ) );
		self::link(
			$tags,
			array(
				'rel'  => 'canonical',
				'href' => $canonical,
			)
		);

		self::link(
			$tags,
			array(
				'rel'  => 'related',
				'href' => $get( 'related' ),
			)
		);

		// OWN-014: the generated calendar is an alternate representation
		// of a current event, never an indexable URL of its own.
		if ( isset( $context['alternate']['href'] ) ) {
			self::link(
				$tags,
				array(
					'rel'   => 'alternate',
					'type'  => $context['alternate']['type'],
					'href'  => $context['alternate']['href'],
					'title' => $context['alternate']['title'] ?? '',
				)
			);
		}

		self::meta( $tags, 'property', 'og:locale', $get( 'locale' ) );
		self::meta( $tags, 'property', 'og:type', $get( 'og_type' ) );
		self::meta( $tags, 'property', 'og:site_name', $get( 'site_name' ) );
		self::meta( $tags, 'property', 'og:title', $og_title );
		self::meta( $tags, 'property', 'og:description', $og_description );
		self::meta( $tags, 'property', 'og:url', $canonical );
		self::meta( $tags, 'property', 'og:image', $image );
		if ( '' !== $image ) {
			self::meta( $tags, 'property', 'og:image:width', $get( 'image_width' ) );
			self::meta( $tags, 'property', 'og:image:height', $get( 'image_height' ) );
			self::meta( $tags, 'property', 'og:image:alt', $get( 'image_alt' ) );
		}

		self::meta( $tags, 'name', 'twitter:card', $get( 'twitter_card' ) );
		self::meta( $tags, 'name', 'twitter:title', $og_title );
		self::meta( $tags, 'name', 'twitter:description', $og_description );
		self::meta( $tags, 'name', 'twitter:image', $image );
		if ( '' !== $image ) {
			self::meta( $tags, 'name', 'twitter:image:alt', $get( 'image_alt' ) );
		}

		if ( array() !== ( $context['jsonld'] ?? array() ) ) {
			// The whole JSON-LD document, @context included: the theme
			// serializes it and never composes schema.org itself.
			$tags[] = array(
				'tag'      => 'jsonld',
				'document' => array(
					'@context' => 'https://schema.org',
					'@graph'   => $context['jsonld'],
				),
			);
		}

		return $tags;
	}

	/**
	 * Appends the document title.
	 *
	 * @param array  $tags Tag list.
	 * @param string $text Title text.
	 */
	private static function title( array &$tags, string $text ) {
		if ( '' !== $text ) {
			$tags[] = array(
				'tag'  => 'title',
				'text' => $text,
			);
		}
	}

	/**
	 * Appends one meta tag when it carries a value.
	 *
	 * @param array  $tags      Tag list.
	 * @param string $attribute Identifying attribute (name/property).
	 * @param string $key       Identifying value.
	 * @param string $content   Tag content.
	 */
	private static function meta( array &$tags, string $attribute, string $key, string $content ) {
		if ( '' === $content ) {
			return;
		}

		$tags[] = array(
			'tag'  => 'meta',
			'attr' => array(
				$attribute => $key,
				'content'  => $content,
			),
		);
	}

	/**
	 * Appends one link tag when it has an href.
	 *
	 * @param array $tags       Tag list.
	 * @param array $attributes Link attributes.
	 */
	private static function link( array &$tags, array $attributes ) {
		if ( '' === ( $attributes['href'] ?? '' ) ) {
			return;
		}

		$tags[] = array(
			'tag'  => 'link',
			'attr' => array_filter(
				$attributes,
				static function ( string $value ): bool {
					return '' !== $value;
				}
			),
		);
	}
}
