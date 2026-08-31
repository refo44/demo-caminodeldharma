<?php
/**
 * Level 1: deterministic extraction of the published <head> SEO surface
 * (WU-08B).
 *
 * Written RED-first. Titles, descriptions, keywords, Open Graph copy and
 * the home JSON-LD graph are hand-written production content (ADR 0034,
 * OWN-007), not text a generator can re-derive from a post title. They
 * travel through the payload the same way the share copy did in WU-08A,
 * so WordPress can print the published head instead of inventing one.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Extraction cluster: per-object head SEO and site-wide SEO.
 */
final class Seo_ExtractorTest extends TestCase {

	/**
	 * The published head of an institutional Page travels verbatim.
	 */
	public function test_page_head_yields_the_published_seo_object() {
		$seo = ( new Cdd_Core_Seo_Extractor() )->extract( $this->stat( 'index.html' ) );

		$this->assertSame( 'Budismo Chan y Tierra Pura en Colombia | Camino del Dharma', $seo['title'] );
		$this->assertSame(
			'Comunidad budista en Colombia dedicada al budismo Chan y Tierra Pura. Meditación, estudio, cursos y retiros con Camino del Dharma.',
			$seo['description']
		);
		$this->assertStringContainsString( 'cursos budistas', $seo['keywords'] );
		$this->assertSame( 'Budismo Chan y Tierra Pura en Colombia | Camino del Dharma', $seo['og_title'] );
		$this->assertSame(
			'Comunidad budista en Colombia dedicada al budismo Chan y Tierra Pura. Meditación, estudio, cursos y retiros.',
			$seo['og_description']
		);
	}

	/**
	 * The Open Graph copy of the blog entry is shorter than the <title>
	 * on purpose; both survive, neither is derived from the other.
	 */
	public function test_post_head_keeps_title_and_open_graph_copy_apart() {
		$seo = ( new Cdd_Core_Seo_Extractor() )->extract( $this->stat( 'blog/sangha-refugio-hiperconexion/index.html' ) );

		$this->assertSame( 'Estamos conectados, pero seguimos solos — Blog — Camino del Dharma', $seo['title'] );
		$this->assertSame( 'Estamos conectados, pero seguimos solos', $seo['og_title'] );
		$this->assertSame(
			'La Sangha como refugio en tiempos de hiperconexión. Reflexión de Zheng Gong sobre soledad, presencia y comunidad de práctica.',
			$seo['description']
		);
	}

	/**
	 * `rel="related"` is an editorial relation (event ↔ entry), not a
	 * value a template can infer.
	 */
	public function test_related_link_travels_with_the_object() {
		$event = ( new Cdd_Core_Seo_Extractor() )->extract( $this->stat( 'eventos/circulos-de-presencia-consciente/index.html' ) );

		$this->assertSame(
			'https://caminodeldharma.org/blog/circulos-de-presencia-consciente',
			$event['related']
		);
	}

	/**
	 * A source without a head SEO surface yields empty strings, never
	 * invented copy.
	 */
	public function test_missing_head_fields_stay_empty() {
		$seo = ( new Cdd_Core_Seo_Extractor() )->extract( '<html><head><title>Solo título</title></head><body></body></html>' );

		$this->assertSame( 'Solo título', $seo['title'] );
		$this->assertSame( '', $seo['description'] );
		$this->assertSame( '', $seo['keywords'] );
		$this->assertSame( '', $seo['og_title'] );
		$this->assertSame( '', $seo['og_description'] );
		$this->assertSame( '', $seo['related'] );
	}

	/**
	 * /eventos is the CPT archive, not a Page: its published head has no
	 * post object to hang on, so it travels as site-level data.
	 */
	public function test_site_seo_carries_the_defaults_and_the_event_archive() {
		$site = ( new Cdd_Core_Seo_Extractor() )->extract_site(
			$this->stat( 'index.html' ),
			array( 'event' => $this->stat( 'eventos/index.html' ) )
		);

		$this->assertSame( 'https://caminodeldharma.org', $site['seo']['base'] );
		$this->assertSame( 'Camino del Dharma', $site['seo']['site_name'] );
		$this->assertSame( 'es_CO', $site['seo']['locale'] );
		$this->assertSame( 'https://caminodeldharma.org/assets/images/og-default.jpg', $site['seo']['image'] );
		$this->assertSame( '1200', $site['seo']['image_width'] );
		$this->assertSame( '630', $site['seo']['image_height'] );
		$this->assertSame( 'summary_large_image', $site['seo']['twitter_card'] );
		$this->assertNotSame( '', $site['seo']['image_alt'] );

		$this->assertSame(
			'Eventos y Retiros Budistas en Colombia | Camino del Dharma',
			$site['seo']['archives']['event']['title']
		);
		$this->assertStringContainsString(
			'retiros budistas en Colombia',
			$site['seo']['archives']['event']['description']
		);
	}

	/**
	 * The published home @graph (Organization, founder, developer,
	 * WebSite, WebPage) travels verbatim: institutional data — founding
	 * date, phone, sameAs — is content, not something to re-type.
	 */
	public function test_site_jsonld_carries_the_published_home_graph() {
		$site = ( new Cdd_Core_Seo_Extractor() )->extract_site( $this->stat( 'index.html' ), array() );

		$types = array_map(
			static function ( array $node ): string {
				return $node['@type'];
			},
			$site['jsonld']['home_graph']
		);

		$this->assertSame( array( 'Organization', 'Person', 'Person', 'WebSite', 'WebPage' ), $types );

		$organization = $site['jsonld']['home_graph'][0];
		$this->assertSame( 'https://caminodeldharma.org/#organization', $organization['@id'] );
		$this->assertSame( 'Comunidad Buddhista Camino del Dharma', $organization['name'] );
		$this->assertSame( '2019', $organization['foundingDate'] );
		$this->assertContains( 'https://www.instagram.com/camino_del_dharma/', $organization['sameAs'] );
	}

	/**
	 * Reads one production file.
	 *
	 * @param string $path Path under static/.
	 */
	private function stat( string $path ): string {
		return (string) file_get_contents( dirname( __DIR__, 2 ) . '/static/' . $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repo files in a unit test without WordPress.
	}
}
