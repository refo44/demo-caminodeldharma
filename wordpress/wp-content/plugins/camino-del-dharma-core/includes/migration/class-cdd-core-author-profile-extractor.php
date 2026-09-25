<?php
/**
 * Author-profile entity fields from published copy (OWN-020 / D-08).
 *
 * Bylines supply the name and, when present, the short on-page bio.
 * /comunidad supplies the head description, the Comunidad paragraph,
 * and the two photos. The long founder essay is never copied.
 *
 * Pure domain code: no WordPress APIs.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enriches byline profiles with published entity copy and photos.
 */
final class Cdd_Core_Author_Profile_Extractor {

	const ZHENG_GONG_SLUG = 'zheng-gong';

	const COMUNIDAD_SLUG = 'comunidad-camino-del-dharma';

	/**
	 * Returns each byline profile with bio, seo, and thumbnail filled
	 * from /comunidad. A non-empty byline bio is kept.
	 *
	 * @param array  $profiles       List of slug/name/bio byline profiles.
	 * @param string $comunidad_html Published /comunidad HTML.
	 */
	public function extract( array $profiles, string $comunidad_html ): array {
		$xpath    = Cdd_Core_Dom::load( $comunidad_html );
		$entities = array(
			self::ZHENG_GONG_SLUG => $this->founder( $xpath ),
			self::COMUNIDAD_SLUG  => $this->community( $xpath, $comunidad_html ),
		);

		$enriched = array();
		foreach ( $profiles as $profile ) {
			$enriched[] = $this->apply_entity( $profile, $entities[ $profile['slug'] ] ?? $this->empty_entity() );
		}

		return $enriched;
	}

	/**
	 * Zheng Gong: Person JSON-LD description and the founder photo.
	 *
	 * @param DOMXPath $xpath /comunidad document.
	 */
	private function founder( DOMXPath $xpath ): array {
		$description = '';
		foreach ( Cdd_Core_Dom::json_ld_nodes( $xpath, 'Person' ) as $person ) {
			if ( isset( $person['description'] ) ) {
				$description = (string) $person['description'];
				break;
			}
		}

		$photo = $this->photo( $xpath, 'foto-biografia-fundador.jpg' );

		return array(
			'bio'           => $description,
			'thumbnail'     => $photo['file'],
			'thumbnail_alt' => $photo['alt'],
			'seo'           => $this->description_seo( $description ),
		);
	}

	/**
	 * Comunidad: the first «Quiénes somos» paragraph, the page meta
	 * description, and that section's photo.
	 *
	 * @param DOMXPath $xpath          /comunidad document.
	 * @param string   $comunidad_html Same document, for the head extractor.
	 */
	private function community( DOMXPath $xpath, string $comunidad_html ): array {
		$paragraph = '';
		foreach ( $xpath->query( '//h2' ) as $heading ) {
			if ( 'Quiénes somos' !== Cdd_Core_Dom::text( $heading ) ) {
				continue;
			}
			$next = $xpath->query( 'following::p[1]', $heading )->item( 0 );
			if ( $next instanceof DOMElement ) {
				$paragraph = Cdd_Core_Dom::text( $next );
			}
			break;
		}

		$photo = $this->photo( $xpath, 'comunidad-quienes-somos.jpg' );
		$seo   = ( new Cdd_Core_Seo_Extractor() )->extract( $comunidad_html );

		return array(
			'bio'           => $paragraph,
			'thumbnail'     => $photo['file'],
			'thumbnail_alt' => $photo['alt'],
			'seo'           => $this->description_seo( $seo['description'] ),
		);
	}

	/**
	 * An img whose src ends with the published file name.
	 *
	 * @param DOMXPath $xpath    Document XPath.
	 * @param string   $filename File name to match.
	 */
	private function photo( DOMXPath $xpath, string $filename ): array {
		foreach ( $xpath->query( '//img' ) as $image ) {
			$src = $image->getAttribute( 'src' );
			if ( substr( $src, -strlen( $filename ) ) !== $filename ) {
				continue;
			}

			return array(
				'file' => Cdd_Core_Dom::to_source_path( $src ),
				'alt'  => $image->getAttribute( 'alt' ),
			);
		}

		return array(
			'file' => '',
			'alt'  => '',
		);
	}

	/**
	 * SEO object with only the published description. Titles are not
	 * invented: these URLs never existed on the static site.
	 *
	 * @param string $description Published description.
	 */
	private function description_seo( string $description ): array {
		$seo                = Cdd_Core_Seo_Extractor::EMPTY_SEO;
		$seo['description'] = $description;

		return $seo;
	}

	/**
	 * Keeps a non-empty byline bio. Otherwise uses the entity paragraph.
	 *
	 * @param array $profile Byline profile.
	 * @param array $entity  Published entity fields.
	 */
	private function apply_entity( array $profile, array $entity ): array {
		$bio = trim( (string) ( $profile['bio'] ?? '' ) );

		return array(
			'slug'          => (string) $profile['slug'],
			'name'          => (string) $profile['name'],
			'bio'           => '' !== $bio ? $bio : $entity['bio'],
			'thumbnail'     => $entity['thumbnail'],
			'thumbnail_alt' => $entity['thumbnail_alt'],
			'seo'           => $entity['seo'],
		);
	}

	/**
	 * Fields for a profile this decision does not describe.
	 */
	private function empty_entity(): array {
		return array(
			'bio'           => '',
			'thumbnail'     => '',
			'thumbnail_alt' => '',
			'seo'           => Cdd_Core_Seo_Extractor::EMPTY_SEO,
		);
	}
}
