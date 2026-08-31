<?php
/**
 * Level 1: deterministic gallery extraction from the embedded production
 * JSON (ADR 0034/0036, OWN-001).
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: 3 albums and 35 images with alt text and memberships.
 */
final class Gallery_ExtractorTest extends TestCase {

	/**
	 * Protects ADR 0036: the three production albums with the approved
	 * term slugs (general, 2023, 2021) in hub order.
	 */
	public function test_extracts_the_three_albums_with_approved_slugs() {
		$gallery = $this->extract_gallery();

		$this->assertSame( array( 'general', '2023', '2021' ), array_column( $gallery['albums'], 'slug' ) );
		$this->assertSame( array( 'General', '2023', '2021' ), array_column( $gallery['albums'], 'title' ) );
	}

	/**
	 * Protects the 35-image contract: every JSON item with its alt text
	 * and repo-relative file path; galeria-04 is not a gallery item
	 * (OWN-001).
	 */
	public function test_extracts_the_thirty_five_images_without_galeria_04() {
		$images = $this->extract_gallery()['images'];

		$this->assertCount( 35, $images );
		$this->assertSame( 'assets/images/galeria/galeria-01.jpg', $images[0]['file'] );
		$this->assertNotSame( '', $images[0]['alt'] );
		$this->assertNotContains( 'assets/images/galeria/galeria-04.jpg', array_column( $images, 'file' ) );
	}

	/**
	 * Protects album membership: the index ranges of the production JSON
	 * (General 0–25, 2023 25–30, 2021 30–35) become per-image album slugs.
	 */
	public function test_album_membership_follows_the_production_ranges() {
		$images = $this->extract_gallery()['images'];

		$this->assertSame( 'general', $images[0]['album'] );
		$this->assertSame( 'general', $images[24]['album'] );
		$this->assertSame( '2023', $images[25]['album'] );
		$this->assertSame( '2023', $images[29]['album'] );
		$this->assertSame( '2021', $images[30]['album'] );
		$this->assertSame( '2021', $images[34]['album'] );
	}

	/**
	 * Runs the extractor over the real gallery page.
	 */
	private function extract_gallery(): array {
		static $gallery = null;
		if ( null === $gallery ) {
			$gallery = ( new Cdd_Core_Gallery_Extractor() )->extract(
				file_get_contents( dirname( __DIR__, 2 ) . '/static/galeria/index.html' ) // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repo file in a unit test without WordPress.
			);
		}

		return $gallery;
	}
}
