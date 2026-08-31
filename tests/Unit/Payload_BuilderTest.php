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
}
