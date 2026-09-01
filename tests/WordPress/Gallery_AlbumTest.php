<?php
/**
 * Level 2: gallery_album taxonomy and /galeria routing (ADR 0036).
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * Behavior cluster: album taxonomy without stealing the gallery hub Page.
 */
final class Gallery_AlbumTest extends WP_UnitTestCase {

	/**
	 * Pretty permalinks mirroring the target site (ADR 0008); the domain
	 * objects are re-registered afterwards because WordPress only adds
	 * their permastructs when a permalink structure exists at
	 * registration time.
	 */
	public function set_up() {
		parent::set_up();
		$this->set_permalink_structure( '/blog/%postname%' );
		cdd_core_register_post_types();
		cdd_core_register_taxonomies();
		cdd_core_register_rewrites();
		flush_rewrite_rules();
	}

	/**
	 * Protects ADR 0036: albums are a flat taxonomy on Media Library
	 * attachments with public term routes under /galeria (no with_front),
	 * not a CPT and not a child-page tree.
	 */
	public function test_gallery_album_taxonomy_is_registered_on_attachments() {
		$album_taxonomy = get_taxonomy( 'gallery_album' );

		$this->assertNotFalse( $album_taxonomy );
		$this->assertContains( 'attachment', $album_taxonomy->object_type );
		$this->assertFalse( $album_taxonomy->hierarchical );
		$this->assertTrue( $album_taxonomy->public );
		$this->assertTrue( $album_taxonomy->publicly_queryable );
		$this->assertTrue( $album_taxonomy->show_in_rest );
		$this->assertSame( 'galeria', $album_taxonomy->rewrite['slug'] );
		$this->assertFalse( $album_taxonomy->rewrite['with_front'] );
	}

	/**
	 * Protects ADR 0036 §4: the /galeria hub stays the Page — the album
	 * rewrite must not steal it.
	 */
	public function test_galeria_hub_page_is_not_stolen_by_the_album_rewrite() {
		$page_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
				'post_name' => 'galeria',
			)
		);

		$this->go_to( '/galeria' );

		$this->assertTrue( is_page( 'galeria' ) );
		$this->assertSame( $page_id, get_queried_object_id() );
	}

	/**
	 * Protects the album term route: /galeria/{slug} resolves the term
	 * archive and lists the attachments carrying the term (attachments use
	 * the inherit status, which a vanilla tax query would exclude).
	 */
	public function test_album_term_route_resolves_and_lists_its_attachments() {
		wp_insert_term( '2023', 'gallery_album' );
		$attachment_id = self::factory()->attachment->create();
		wp_set_object_terms( $attachment_id, '2023', 'gallery_album' );

		$this->go_to( '/galeria/2023' );

		$this->assertTrue( is_tax( 'gallery_album', '2023' ) );
		$this->assertContains( $attachment_id, wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ) );
	}
}
