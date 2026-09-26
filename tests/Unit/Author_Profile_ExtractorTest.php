<?php
/**
 * Level 1: author profiles reuse published short copy and photos
 * (OWN-020 / D-08). The long founder essay stays on /comunidad.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: the two approved profiles from bylines plus /comunidad.
 */
final class Author_Profile_ExtractorTest extends TestCase {

	/**
	 * Zheng Gong keeps the short Sangha byline on the page. The head
	 * description and the photo come from the published Person JSON-LD.
	 */
	public function test_zheng_gong_reuses_the_published_byline_description_and_photo() {
		$profile = $this->profiles()['zheng-gong'];

		$this->assertSame( 'Zheng Gong', $profile['name'] );
		$this->assertStringContainsString( 'Maestro budista de las tradiciones Chan y Tierra Pura', $profile['bio'] );
		$this->assertStringNotContainsString( 'Doctor en Educación', $profile['bio'] );
		$this->assertSame(
			'Maestro buddhista contemporáneo, fundador de la Comunidad Buddhista Camino del Dharma. Su enseñanza integra la sabiduría del Buddhismo Chan y Tierra Pura con los desafíos de la vida moderna.',
			$profile['seo']['description']
		);
		$this->assertSame( '', $profile['seo']['title'] );
		$this->assertSame( 'assets/images/fundador/foto-biografia-fundador.jpg', $profile['thumbnail'] );
		$this->assertSame( 'Venerable Maestro Zheng Gong', $profile['thumbnail_alt'] );
	}

	/**
	 * Comunidad has no byline biography. The page uses the first
	 * «Quiénes somos» paragraph; the head uses the /comunidad meta description.
	 */
	public function test_comunidad_reuses_the_published_paragraph_description_and_photo() {
		$profile = $this->profiles()['comunidad-camino-del-dharma'];

		$this->assertSame( 'Comunidad Camino del Dharma', $profile['name'] );
		$this->assertStringStartsWith( 'La Comunidad Buddhista Camino del Dharma es un espacio de aprendizaje', $profile['bio'] );
		$this->assertStringNotContainsString( 'Doctor en Educación', $profile['bio'] );
		$this->assertSame(
			'Conoce Camino del Dharma, una comunidad budista en Colombia dedicada a la práctica del budismo Chan y Tierra Pura.',
			$profile['seo']['description']
		);
		$this->assertSame( 'assets/images/comunidad-linaje/comunidad-quienes-somos.jpg', $profile['thumbnail'] );
		$this->assertSame( 'Comunidad Buddhista Camino del Dharma', $profile['thumbnail_alt'] );
	}

	/**
	 * The two profiles keyed by slug, extracted from the real static files.
	 */
	private function profiles(): array {
		static $profiles = null;
		if ( null !== $profiles ) {
			return $profiles;
		}

		$static = dirname( __DIR__, 2 ) . '/tests/fixtures/published-static';
		$posts  = ( new Cdd_Core_Blog_Extractor() )->extract(
			array(
				'circulos-de-presencia-consciente' => file_get_contents( $static . '/blog/circulos-de-presencia-consciente/index.html' ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repo files in a unit test without WordPress.
				'sangha-refugio-hiperconexion'     => file_get_contents( $static . '/blog/sangha-refugio-hiperconexion/index.html' ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repo files in a unit test without WordPress.
			)
		);

		$bylines = array();
		foreach ( $posts as $post ) {
			if ( ! isset( $bylines[ $post['author_slug'] ] ) || '' === $bylines[ $post['author_slug'] ]['bio'] ) {
				$bylines[ $post['author_slug'] ] = array(
					'slug' => $post['author_slug'],
					'name' => $post['author_name'],
					'bio'  => $post['author_bio'],
				);
			}
		}
		ksort( $bylines );

		$extracted = ( new Cdd_Core_Author_Profile_Extractor() )->extract(
			array_values( $bylines ),
			file_get_contents( $static . '/comunidad/index.html' ) // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repo files in a unit test without WordPress.
		);

		$profiles = array();
		foreach ( $extracted as $profile ) {
			$profiles[ $profile['slug'] ] = $profile;
		}

		return $profiles;
	}
}
