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
 * Runs the convert pass over the three documented pages.
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
	 * @param array $options environment / confirm_production / backup_evidence.
	 */
	public function __construct( array $options = array() ) {
		$this->options = array_merge(
			array(
				'environment'        => wp_get_environment_type(),
				'confirm_production' => false,
				'backup_evidence'    => '',
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

		foreach ( array( 'inicio', 'galeria', 'comunidad' ) as $slug ) {
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

		return $report;
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
