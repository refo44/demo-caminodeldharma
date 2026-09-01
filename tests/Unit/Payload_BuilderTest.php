<?php
/**
 * Level 1: the migration payload contract (ADR 0032 §8.1–§8.2): source
 * identification, stable source keys, content hashes and deterministic
 * output.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: payload assembly with keys, hashes and determinism.
 */
final class Payload_BuilderTest extends TestCase {

	/**
	 * Protects source identification: the payload names the schema, the
	 * repo VERSION and the source commit it was extracted from.
	 */
	public function test_payload_identifies_its_source() {
		$payload = $this->build_payload();

		$this->assertSame( 'cdd-migration/1', $payload['schema'] );
		$this->assertSame( '1.0.35', $payload['source']['version'] );
		$this->assertSame( 'abc1234', $payload['source']['commit'] );
	}

	/**
	 * Protects the stable keys the importer matches on: every object in
	 * every collection carries a namespaced _source_key and a
	 * _source_hash over its own fields.
	 */
	public function test_every_object_carries_source_key_and_hash() {
		$payload = $this->build_payload();

		$this->assertSame( 'event:vesak-2026', $payload['events'][0]['_source_key'] );
		$this->assertSame( 'page:practica', $payload['pages'][0]['_source_key'] );
		$this->assertMatchesRegularExpression( '/^[0-9a-f]{64}$/', $payload['events'][0]['_source_hash'] );
	}

	/**
	 * Protects determinism (ADR 0032 §8.1): building twice from the same
	 * input yields byte-identical JSON, and a changed field changes only
	 * that object's hash.
	 */
	public function test_payload_json_is_deterministic_and_hashes_track_content() {
		$first  = Cdd_Core_Payload_Builder::to_json( $this->build_payload() );
		$second = Cdd_Core_Payload_Builder::to_json( $this->build_payload() );

		$this->assertSame( $first, $second );

		$changed = $this->build_payload( 'Vesak 2027 – renombrado' );

		$this->assertNotSame( $this->build_payload()['events'][0]['_source_hash'], $changed['events'][0]['_source_hash'] );
	}

	/**
	 * Protects the hash contract: bookkeeping keys (_source_key and
	 * _source_hash) are not part of the canonical content hash, so a
	 * prefix/format change does not rewrite every object's hash.
	 */
	public function test_source_hash_excludes_bookkeeping_keys() {
		$content = array(
			'slug'  => 'vesak-2026',
			'title' => 'Vesak 2026 – Colombia Cuida la Vida',
		);

		$this->assertSame(
			Cdd_Core_Payload_Builder::hash_object( $content ),
			Cdd_Core_Payload_Builder::hash_object(
				$content + array(
					'_source_key'  => 'event:vesak-2026',
					'_source_hash' => 'deadbeef',
				)
			)
		);
		$this->assertSame(
			Cdd_Core_Payload_Builder::hash_object( $content + array( '_source_key' => 'event:vesak-2026' ) ),
			Cdd_Core_Payload_Builder::hash_object( $content + array( '_source_key' => 'page:vesak-2026' ) )
		);
		$this->assertSame(
			Cdd_Core_Payload_Builder::hash_object( $content ),
			$this->build_payload()['events'][0]['_source_hash']
		);
	}

	/**
	 * Protects the reconciliation surface: the payload publishes its own
	 * counts so validate/verify can compare them against the documented
	 * baseline without re-deriving them.
	 */
	public function test_payload_publishes_its_counts() {
		$counts = $this->build_payload()['counts'];

		$this->assertSame( 1, $counts['events'] );
		$this->assertSame( 1, $counts['pages'] );
	}

	/**
	 * Builds a minimal payload from synthetic collections.
	 *
	 * @param string $event_title Title for the single event.
	 */
	private function build_payload( string $event_title = 'Vesak 2026 – Colombia Cuida la Vida' ): array {
		return ( new Cdd_Core_Payload_Builder() )->build(
			array(
				'events' => array(
					array(
						'slug'  => 'vesak-2026',
						'title' => $event_title,
					),
				),
				'pages'  => array(
					array(
						'slug'  => 'practica',
						'title' => 'Práctica',
					),
				),
			),
			array(
				'version' => '1.0.35',
				'commit'  => 'abc1234',
				'root'    => 'static',
			)
		);
	}
	/**
	 * WU-08B: site-wide SEO is not a collection. It has no slug, no
	 * source key and no count of its own — adding it must not disturb the
	 * reconciliation counts (docs/conteos-reconciliacion-migracion.md).
	 */
	public function test_site_section_travels_outside_the_counted_collections() {
		$payload = ( new Cdd_Core_Payload_Builder() )->build(
			array( 'pages' => array( array( 'slug' => 'inicio' ) ) ),
			array(
				'version' => '1.0.35',
				'commit'  => 'abc1234',
				'root'    => 'static',
			),
			array(
				'seo'    => array( 'site_name' => 'Camino del Dharma' ),
				'jsonld' => array( 'home_graph' => array( array( '@type' => 'Organization' ) ) ),
			)
		);

		$this->assertSame( array( 'pages' => 1 ), $payload['counts'] );
		$this->assertSame( 'Camino del Dharma', $payload['site']['seo']['site_name'] );
		$this->assertArrayNotHasKey( '_source_key', $payload['site'] );
	}

	/**
	 * Omitting the site section keeps the payload shape it had before.
	 */
	public function test_site_section_is_optional() {
		$payload = ( new Cdd_Core_Payload_Builder() )->build(
			array( 'pages' => array() ),
			array(
				'version' => '1.0.35',
				'commit'  => 'abc1234',
				'root'    => 'static',
			)
		);

		$this->assertArrayNotHasKey( 'site', $payload );
	}
}
