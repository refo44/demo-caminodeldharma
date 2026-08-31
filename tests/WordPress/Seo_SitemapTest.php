<?php
/**
 * Level 2: the native sitemap as the only sitemap (ADR 0030, WU-08B).
 *
 * Written RED-first. No SEO suite is installed, so `/wp-sitemap.xml`
 * must be trimmed to the URL tree of docs/11-arbol-urls-final.md: the
 * non-public event taxonomies were never eligible, but WordPress ships
 * providers for users, categories, tags and album terms that publish
 * URLs the site does not own or does not index.
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * SEO cluster: native sitemap providers and entries.
 */
final class Seo_SitemapTest extends WP_UnitTestCase {

	/**
	 * Native WP-user author archives are 404 (ADR 0037 §5): listing them
	 * would publish a sitemap full of soft 404s.
	 */
	public function test_users_provider_is_removed() {
		$this->assertArrayNotHasKey( 'users', wp_sitemaps_get_server()->registry->get_providers() );
	}

	/**
	 * Only the object types of the URL tree are listed.
	 */
	public function test_post_types_are_pages_posts_events_and_author_profiles() {
		$provider = wp_sitemaps_get_server()->registry->get_provider( 'posts' );

		$types = array_keys( $provider->get_object_subtypes() );
		sort( $types );

		$this->assertSame( array( 'blog_author', 'event', 'page', 'post' ), $types );
	}

	/**
	 * `noindex, follow` archives stay out of the sitemap: album terms and
	 * blog tags (ADR 0031/0036), plus categories, which are not in the
	 * URL tree at all.
	 */
	public function test_noindex_taxonomies_are_not_listed() {
		self::factory()->term->create(
			array(
				'taxonomy' => 'gallery_album',
				'slug'     => '2023',
			)
		);
		self::factory()->post->create( array( 'tags_input' => array( 'sangha' ) ) );

		$provider = wp_sitemaps_get_server()->registry->get_provider( 'taxonomies' );

		$this->assertSame( array(), array_keys( $provider->get_object_subtypes() ) );
	}

	/**
	 * ADR 0022: the event taxonomies have no public archive, so they can
	 * never reach the sitemap.
	 */
	public function test_event_taxonomies_are_never_eligible() {
		foreach ( array( 'event_type', 'event_city' ) as $taxonomy ) {
			$this->assertFalse( get_taxonomy( $taxonomy )->public, $taxonomy );
		}
	}

	/**
	 * A staging environment must not publish a sitemap at all: with
	 * `blog_public` off, WordPress disables it and the site stays
	 * non-indexable (deployment runbook §5).
	 */
	public function test_sitemap_is_disabled_when_the_site_is_not_public() {
		update_option( 'blog_public', 0 );

		$this->assertFalse( wp_sitemaps_get_server()->sitemaps_enabled() );
	}
}
