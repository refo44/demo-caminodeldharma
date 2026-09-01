<?php
/**
 * Migration payload assembly (ADR 0032 §8.1): source identification,
 * stable source keys, per-object content hashes, published counts and
 * deterministic JSON.
 *
 * Pure domain code: no WordPress APIs.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the versioned, reviewable migration payload.
 */
final class Cdd_Core_Payload_Builder {

	const SCHEMA = 'cdd-migration/1';

	/**
	 * Source-key namespace per collection.
	 */
	const KEY_PREFIXES = array(
		'pages'          => 'page',
		'events'         => 'event',
		'posts'          => 'post',
		'blog_authors'   => 'blog_author',
		'gallery_albums' => 'album',
		'gallery_images' => 'gallery',
		'media'          => 'media',
		'video_embeds'   => 'embed',
	);

	/**
	 * Assembles the payload.
	 *
	 * @param array $collections Map collection name => list of objects.
	 * @param array $source      Source identity (version, commit, root).
	 * @param array $site        Site-wide data (WU-08B). Not a
	 *                           collection: no slug, no source key and no
	 *                           count, so the reconciliation totals of
	 *                           docs/conteos-reconciliacion-migracion.md
	 *                           stay untouched.
	 */
	public function build( array $collections, array $source, array $site = array() ): array {
		$payload = array(
			'schema' => self::SCHEMA,
			'source' => array(
				'version' => $source['version'],
				'commit'  => $source['commit'],
				'root'    => $source['root'],
			),
			'counts' => array(),
		);

		foreach ( $collections as $name => $objects ) {
			$keyed = array();
			foreach ( $objects as $object ) {
				$object['_source_key']  = $this->source_key( (string) $name, $object );
				$object['_source_hash'] = self::hash_object( $object );
				$keyed[]                = $object;
			}
			$payload[ $name ]           = $keyed;
			$payload['counts'][ $name ] = count( $keyed );
		}

		if ( array() !== $site ) {
			$payload['site'] = $site;
		}

		return $payload;
	}

	/**
	 * Canonical hash of one object's own fields (source bookkeeping keys
	 * excluded).
	 *
	 * @param array $payload_object Payload object.
	 */
	public static function hash_object( array $payload_object ): string {
		unset( $payload_object['_source_hash'], $payload_object['_source_key'] );

		return hash( 'sha256', json_encode( $payload_object, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- deterministic canonical form; pure class usable without WordPress loaded.
	}

	/**
	 * Deterministic pretty JSON for review and Git.
	 *
	 * @param array $payload Payload array.
	 */
	public static function to_json( array $payload ): string {
		return json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "\n"; // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- pure class usable without WordPress loaded.
	}

	/**
	 * The namespaced stable key for one object.
	 *
	 * @param string $collection Collection name.
	 * @param array  $payload_object Payload object.
	 */
	private function source_key( string $collection, array $payload_object ): string {
		$prefix   = self::KEY_PREFIXES[ $collection ] ?? $collection;
		$identity = $payload_object['slug'] ?? $payload_object['file'] ?? $payload_object['url'] ?? null;
		if ( null === $identity ) {
			throw new RuntimeException( "Object in {$collection} has no slug/file/url identity." ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- extraction-time failure surfaced on the CLI, never in HTML.
		}

		return $prefix . ':' . $identity;
	}
}
