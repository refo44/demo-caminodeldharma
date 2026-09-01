<?php
/**
 * Applies the documented wp:html → block conversions to the local
 * WordPress content (WU-07). Dry-run by default, explicit apply,
 * idempotent, production guarded — the same semantics as the importer
 * (ADR 0033). The conversion is the recorded wp-admin-equivalent edit:
 * after it, the content hash no longer matches _cdd_source_hash, which
 * is exactly how edited objects are protected from re-imports.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Runs the convert pass over the documented pages and, when a payload is
 * supplied, seeds the published share templates (WU-08A) and the
 * published head SEO copy (WU-08B).
 */
final class Cdd_Core_Convert_Service {

	/**
	 * Options (environment, confirm_production, backup_evidence).
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Constructor.
	 *
	 * @param array $options environment / confirm_production /
	 *                       backup_evidence / payload.
	 */
	public function __construct( array $options = array() ) {
		$this->options = array_merge(
			array(
				'environment'        => wp_get_environment_type(),
				'confirm_production' => false,
				'backup_evidence'    => '',
				'payload'            => array(),
			),
			$options
		);
	}

	/**
	 * Computes (and with $apply, writes) the pending conversions.
	 *
	 * @param bool $apply Write the converted content; false = dry run.
	 */
	public function run( bool $apply ): array {
		$report = array(
			'dry_run'   => ! $apply,
			'pending'   => array(),
			'converted' => array(),
		);

		if ( $apply && 'production' === $this->options['environment']
			&& ( ! $this->options['confirm_production'] || '' === trim( (string) $this->options['backup_evidence'] ) ) ) {
			$report['error'] = 'Production conversion requires --confirm-production and --backup-evidence.';

			return $report;
		}

		$converter = new Cdd_Core_Content_Converter();

		// privacidad before contacto: the notice must describe a form
		// that submits BEFORE the form block reaches /contacto in this
		// environment (ADR 0041 point 3).
		foreach ( array( 'inicio', 'galeria', 'comunidad', 'practica', 'privacidad', 'contacto' ) as $slug ) {
			$page = get_page_by_path( $slug, OBJECT, 'page' );
			if ( ! $page instanceof WP_Post ) {
				continue;
			}

			switch ( $slug ) {
				case 'inicio':
					$converted = $converter->convert_inicio( $page->post_content, $this->thumb_media_map( $page->post_content ) );
					break;
				case 'galeria':
					$converted = $converter->convert_galeria( $page->post_content, $this->albums() );
					break;
				case 'practica':
					$converted = $converter->convert_practica( $page->post_content, $this->audio_media_map( $page->post_content ) );
					break;
				case 'privacidad':
					$converted = $converter->convert_privacidad(
						$page->post_content,
						Cdd_Core_Spanish_Date::long_form( current_time( 'Y-m-d' ) )
					);
					break;
				case 'contacto':
					$converted = $converter->convert_contacto( $page->post_content );
					break;
				default:
					$converted = $converter->convert_comunidad( $page->post_content );
			}

			if ( null === $converted ) {
				continue;
			}

			if ( ! $apply ) {
				$report['pending'][] = $slug;
				continue;
			}

			wp_update_post(
				array(
					'ID'           => $page->ID,
					'post_content' => $converted,
				)
			);
			$report['converted'][] = $slug;
		}

		$this->seed_share_templates( $apply, $report );
		$this->seed_head_seo( $apply, $report );

		return $report;
	}

	/**
	 * Seeds the published head copy on the objects the importer already
	 * created (WU-08B). Same contract as the share templates: a fresh
	 * import writes it on create, and this pass converges an environment
	 * imported before it travelled. Add-only — a key an editor already
	 * wrote, or deliberately emptied, is never rewritten (ADR 0033).
	 *
	 * @param bool  $apply  Write the meta; false = dry run.
	 * @param array $report Report to extend, by reference.
	 */
	private function seed_head_seo( bool $apply, array &$report ) {
		$payload = (array) $this->options['payload'];

		foreach ( array( 'pages', 'events', 'posts' ) as $collection ) {
			foreach ( (array) ( $payload[ $collection ] ?? array() ) as $object ) {
				$seo = Cdd_Core_Importer::seo_meta( $object );
				if ( 'events' === $collection ) {
					$seo = array_merge( $seo, $this->event_seo_meta( $object ) );
				}
				if ( empty( $seo ) ) {
					continue;
				}

				$post_id = $this->post_by_source_key( (string) ( $object['_source_key'] ?? '' ) );
				if ( null === $post_id ) {
					continue;
				}

				$pending = array();
				foreach ( $seo as $meta_key => $value ) {
					if ( ! metadata_exists( 'post', $post_id, $meta_key ) ) {
						$pending[ $meta_key ] = $value;
					}
				}
				if ( empty( $pending ) ) {
					continue;
				}

				$item = 'seo:' . $object['_source_key'];
				if ( ! $apply ) {
					$report['pending'][] = $item;
					continue;
				}

				foreach ( $pending as $meta_key => $value ) {
					// The meta API unslashes what it stores: JSON written
					// raw would lose every backslash, turning \u00ed into
					// u00ed and «Círculos» into «Cu00edrculos».
					add_post_meta( $post_id, $meta_key, wp_slash( $value ), true );
				}
				$report['converted'][] = $item;
			}
		}
	}

	/**
	 * The event-only structured-data meta of one payload object.
	 *
	 * @param array $payload_object Event payload object.
	 */
	private function event_seo_meta( array $payload_object ): array {
		return array_filter(
			array(
				'event_attendance_mode' => (string) ( $payload_object['attendance_mode'] ?? '' ),
				'seo_jsonld_extra'      => array() !== (array) ( $payload_object['jsonld_extra'] ?? array() )
					? (string) wp_json_encode( $payload_object['jsonld_extra'] )
					: '',
			),
			static function ( string $value ): bool {
				return '' !== $value;
			}
		);
	}

	/**
	 * Seeds the published share message templates as meta on the objects
	 * the importer already created (WU-08A). A fresh import writes them
	 * on create; this pass is what converges an environment imported
	 * before the templates travelled. Add-only by construction: a key
	 * that already exists — including one an editor emptied on purpose —
	 * is never rewritten (ADR 0033).
	 *
	 * @param bool  $apply  Write the meta; false = dry run.
	 * @param array $report Report to extend, by reference.
	 */
	private function seed_share_templates( bool $apply, array &$report ) {
		$payload = (array) $this->options['payload'];

		foreach ( array( 'events', 'posts' ) as $collection ) {
			foreach ( (array) ( $payload[ $collection ] ?? array() ) as $object ) {
				$share = Cdd_Core_Importer::share_meta( $object );
				if ( empty( $share ) ) {
					continue;
				}

				$post_id = $this->post_by_source_key( (string) ( $object['_source_key'] ?? '' ) );
				if ( null === $post_id ) {
					continue;
				}

				$pending = array();
				foreach ( $share as $meta_key => $value ) {
					if ( ! metadata_exists( 'post', $post_id, $meta_key ) ) {
						$pending[ $meta_key ] = $value;
					}
				}
				if ( empty( $pending ) ) {
					continue;
				}

				$item = 'share:' . $object['_source_key'];
				if ( ! $apply ) {
					$report['pending'][] = $item;
					continue;
				}

				foreach ( $pending as $meta_key => $value ) {
					add_post_meta( $post_id, $meta_key, wp_slash( $value ), true );
				}
				$report['converted'][] = $item;
			}
		}
	}

	/**
	 * The imported post carrying one payload source key, or null.
	 *
	 * @param string $source_key Payload _source_key.
	 */
	private function post_by_source_key( string $source_key ): ?int {
		if ( '' === $source_key ) {
			return null;
		}

		$posts = get_posts(
			array(
				'post_type'   => 'any',
				'post_status' => 'any',
				'numberposts' => 1,
				'meta_key'    => Cdd_Core_Importer::SOURCE_KEY_META, // phpcs:ignore WordPress.DB.SlowMetaQuery.slow_db_query -- migration bookkeeping lookup, CLI only.
				'meta_value'  => $source_key, // phpcs:ignore WordPress.DB.SlowMetaQuery.slow_db_query -- migration bookkeeping lookup, CLI only.
			)
		);

		return empty( $posts ) ? null : (int) $posts[0]->ID;
	}

	/**
	 * Maps each audio src the content references to the imported
	 * attachment behind it, matched by file name. Players whose file was
	 * never imported are simply absent, and stay as published.
	 *
	 * @param string $content Page content.
	 */
	private function audio_media_map( string $content ): array {
		if ( ! preg_match_all( '#<source[^>]+src="([^"]+\.mp3)"#', $content, $matches, PREG_SET_ORDER ) ) {
			return array();
		}

		$map = array();
		foreach ( $matches as $match ) {
			$name        = basename( $match[1], '.mp3' );
			$attachments = get_posts(
				array(
					'post_type'   => 'attachment',
					'post_status' => 'inherit',
					'name'        => $name,
					'numberposts' => 1,
				)
			);
			if ( empty( $attachments ) ) {
				continue;
			}

			$map[ $match[1] ] = array(
				'id'  => (int) $attachments[0]->ID,
				'url' => (string) wp_get_attachment_url( $attachments[0]->ID ),
			);
		}

		return $map;
	}

	/**
	 * Maps each handmade gallery-thumb basename referenced by the content
	 * to the Library URL of its imported original (the handmade thumbs of
	 * the static era do not migrate — doc 03 §5.1).
	 *
	 * @param string $content Page content.
	 */
	private function thumb_media_map( string $content ): array {
		if ( ! preg_match_all( '#assets/images/galeria/thumbs/([a-z0-9-]+)\.(jpe?g|png)#', $content, $matches, PREG_SET_ORDER ) ) {
			return array();
		}

		$map = array();
		foreach ( $matches as $match ) {
			$attachments = get_posts(
				array(
					'post_type'   => 'attachment',
					'post_status' => 'inherit',
					'name'        => $match[1],
					'numberposts' => 1,
				)
			);
			if ( empty( $attachments ) ) {
				continue;
			}

			$url = wp_get_attachment_image_url( $attachments[0]->ID, 'medium_large' );
			if ( ! $url ) {
				$url = (string) wp_get_attachment_url( $attachments[0]->ID );
			}

			$map[ $match[1] . '.' . $match[2] ] = $url;
		}

		return $map;
	}

	/**
	 * The albums for the gallery hub, in the published order: General
	 * first, then the year albums newest first (static hub order).
	 */
	private function albums(): array {
		$terms = get_terms(
			array(
				'taxonomy'   => 'gallery_album',
				'hide_empty' => false,
			)
		);
		if ( is_wp_error( $terms ) ) {
			return array();
		}

		usort(
			$terms,
			static function ( WP_Term $a, WP_Term $b ): int {
				if ( 'general' === $a->slug ) {
					return -1;
				}
				if ( 'general' === $b->slug ) {
					return 1;
				}

				return strcmp( $b->slug, $a->slug );
			}
		);

		$albums = array();
		foreach ( $terms as $term ) {
			$images = array();
			foreach ( cdd_core_album_attachments( $term ) as $attachment ) {
				$url = wp_get_attachment_image_url( $attachment->ID, 'large' );
				if ( ! $url ) {
					$url = (string) wp_get_attachment_url( $attachment->ID );
				}
				$images[] = array(
					'id'  => $attachment->ID,
					'url' => $url,
					'alt' => (string) get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
				);
			}

			$albums[] = array(
				'slug'   => $term->slug,
				'title'  => $term->name,
				'images' => $images,
			);
		}

		return $albums;
	}
}
