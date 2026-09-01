<?php
/**
 * Level 1: media classification for the seed (OWN-001, OWN-002, OWN-003,
 * OWN-009/OWN-009-img): referenced media is public content, unreferenced
 * media is hidden, derivatives and retired files never travel.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: seed classification of the static media tree.
 */
final class Media_InventoryTest extends TestCase {

	/**
	 * Protects the visibility rule: referenced files seed as public,
	 * unreferenced ones as hidden reserves (OWN-003) — same seed, no
	 * public URL.
	 */
	public function test_referenced_media_is_public_and_orphans_are_hidden() {
		$media = ( new Cdd_Core_Media_Inventory() )->classify(
			array(
				'assets/images/galeria/galeria-01.jpg',
				'assets/images/celebraciones/huerfana.jpg',
			),
			array( 'assets/images/galeria/galeria-01.jpg' )
		);

		$this->assertSame( 'public', $media[0]['visibility'] );
		$this->assertTrue( $media[0]['referenced'] );
		$this->assertSame( 'hidden', $media[1]['visibility'] );
		$this->assertFalse( $media[1]['referenced'] );
	}

	/**
	 * Protects the exclusions: handmade thumbs are derivatives WordPress
	 * regenerates, OS noise never seeds, and the retired PDF and on-disk
	 * .ics files are not media (OWN-002, OWN-009).
	 */
	public function test_derivatives_noise_and_retired_files_are_excluded() {
		$media = ( new Cdd_Core_Media_Inventory() )->classify(
			array(
				'assets/images/galeria/thumbs/galeria-01-300.jpg',
				'assets/images/.DS_Store',
				'assets/documents/recitacion-practica-comida.pdf',
				'eventos/ical/encuentro-nacional-2026.ics',
				'assets/images/galeria/galeria-01.jpg',
			),
			array()
		);

		$this->assertSame( array( 'assets/images/galeria/galeria-01.jpg' ), array_column( $media, 'file' ) );
	}

	/**
	 * Protects the kind split the importer needs: mp3 files are audio,
	 * everything else that survives is an image.
	 */
	public function test_kind_distinguishes_audio_from_images() {
		$media = ( new Cdd_Core_Media_Inventory() )->classify(
			array(
				'assets/audio/amitabha.mp3',
				'assets/images/eventos/evento-vesak-2026-bogota.jpeg',
			),
			array( 'assets/audio/amitabha.mp3', 'assets/images/eventos/evento-vesak-2026-bogota.jpeg' )
		);

		$this->assertSame( 'audio', $media[0]['kind'] );
		$this->assertSame( 'image', $media[1]['kind'] );
	}
}
