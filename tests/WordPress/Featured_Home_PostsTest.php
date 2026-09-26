<?php
/**
 * Level 2: featured articles in the home column.
 *
 * A published entry appears there only when post_featured is on. Drafts
 * and unmarked entries stay out. The column stacks those articles under
 * the event note, newest first, and renders nothing when both are absent.
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * Behavior cluster: the home-column article mark over real posts.
 */
final class Featured_Home_PostsTest extends WP_UnitTestCase {

	/**
	 * Re-registers domain meta. The suite tear_down drops registered keys.
	 */
	/**
	 * Published blog_author the entries need in order to stay published
	 * (ADR 0037).
	 *
	 * @var int
	 */
	private int $author_id = 0;

	/**
	 * Re-registers domain meta. The suite tear_down drops registered keys.
	 */
	public function set_up() {
		parent::set_up();
		cdd_core_register_meta();
		$this->author_id = self::factory()->post->create(
			array(
				'post_type'  => 'blog_author',
				'post_title' => 'Zheng Gong',
				'post_name'  => 'zheng-gong',
			)
		);
	}

	/**
	 * Protects the editor contract: the mark is boolean meta on posts and
	 * is visible to the block editor.
	 */
	public function test_post_featured_meta_is_registered_for_the_editor() {
		$registered = get_registered_meta_keys( 'post', 'post' );

		$this->assertArrayHasKey( 'post_featured', $registered );
		$this->assertSame( 'boolean', $registered['post_featured']['type'] );
		$this->assertTrue( $registered['post_featured']['show_in_rest'] );
	}

	/**
	 * Protects the column gate: only published entries with the mark are
	 * returned, newest first.
	 */
	public function test_home_column_query_returns_published_featured_articles_newest_first() {
		$older_id = $this->create_article(
			'articulo-antiguo',
			'publish',
			'2026-01-02 10:00:00',
			array( 'post_featured' => '1' )
		);
		$newer_id = $this->create_article(
			'articulo-reciente',
			'publish',
			'2026-09-01 10:00:00',
			array( 'post_featured' => '1' )
		);
		$this->create_article( 'sin-marca', 'publish', '2026-09-20 10:00:00' );
		$this->create_article(
			'borrador-destacado',
			'draft',
			'2026-09-21 10:00:00',
			array( 'post_featured' => '1' )
		);

		$ids = wp_list_pluck( cdd_core_featured_home_posts(), 'ID' );

		$this->assertSame( array( $newer_id, $older_id ), $ids );
	}

	/**
	 * Protects the column markup: the event note stays first, a marked
	 * article follows it, and an unmarked article is absent. With neither
	 * an event nor a marked article, the column is empty.
	 */
	public function test_home_column_stacks_the_event_and_featured_articles() {
		$event_id = self::factory()->post->create(
			array(
				'post_type'  => 'event',
				'post_title' => 'Círculos de Presencia Consciente',
				'post_name'  => 'circulos-de-presencia-consciente',
			)
		);
		$event    = get_post( $event_id );

		$featured = get_post(
			$this->create_article(
				'estamo-conectados',
				'publish',
				'2026-08-01 10:00:00',
				array( 'post_featured' => '1' ),
				'La Sangha como refugio.'
			)
		);
		$unmarked = get_post(
			$this->create_article( 'sin-marca', 'publish', '2026-08-02 10:00:00', array(), '', 'Entrada sin marca' )
		);

		$html = Camino_Del_Dharma_Renderers::home_featured_column( $event, array( $featured ) );

		$this->assertStringContainsString( 'home-featured-column', $html );
		$this->assertLessThan( strpos( $html, 'home-featured-post' ), strpos( $html, 'home-featured-event' ) );
		$this->assertStringContainsString( 'Artículo', $html );
		$this->assertStringContainsString( 'Leer artículo', $html );
		$this->assertStringContainsString( 'La Sangha como refugio.', $html );
		$this->assertStringNotContainsString( 'destacado', strtolower( $html ) );
		$this->assertStringNotContainsString( get_the_title( $unmarked ), $html );

		$this->assertSame( '', Camino_Del_Dharma_Renderers::home_featured_column( null, array() ) );
	}

	/**
	 * Protects the block: the home column block prints a marked article
	 * and leaves an unmarked one out when no event is current.
	 */
	public function test_featured_block_prints_only_marked_articles() {
		$this->create_article(
			'articulo-marcado',
			'publish',
			'2026-08-01 10:00:00',
			array( 'post_featured' => '1' ),
			'',
			'Artículo marcado'
		);
		$this->create_article( 'articulo-libre', 'publish', '2026-08-02 10:00:00', array(), '', 'Artículo libre' );

		$html = camino_del_dharma_render_evento_destacado();

		$this->assertStringContainsString( 'Artículo marcado', $html );
		$this->assertStringContainsString( 'Leer artículo', $html );
		$this->assertStringNotContainsString( 'Artículo libre', $html );
	}

	/**
	 * Creates a blog entry.
	 *
	 * @param string $slug    Post slug.
	 * @param string $status  Post status.
	 * @param string $date    Publication timestamp.
	 * @param array  $meta    Extra meta.
	 * @param string $excerpt Editorial excerpt.
	 * @param string $title   Title. Defaults to the slug.
	 */
	private function create_article( string $slug, string $status, string $date, array $meta = array(), string $excerpt = '', string $title = '' ): int {
		if ( 'publish' === $status ) {
			$meta['authors'] = array( $this->author_id );
		}

		return self::factory()->post->create(
			array(
				'post_type'     => 'post',
				'post_status'   => $status,
				'post_name'     => $slug,
				'post_title'    => '' === $title ? $slug : $title,
				'post_excerpt'  => $excerpt,
				'post_date'     => $date,
				'post_date_gmt' => $date,
				'meta_input'    => $meta,
			)
		);
	}
}
