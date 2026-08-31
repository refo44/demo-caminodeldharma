<?php
/**
 * First-party taxonomies: event_type / event_city (ADR 0022, no public
 * archives) and gallery_album (ADR 0036, public term routes under
 * /galeria).
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the event and gallery taxonomies.
 */
function cdd_core_register_taxonomies() {
	register_taxonomy(
		'event_type',
		'event',
		array(
			'labels'             => array(
				'name'          => __( 'Tipos de evento', 'camino-del-dharma-core' ),
				'singular_name' => __( 'Tipo de evento', 'camino-del-dharma-core' ),
			),
			'hierarchical'       => true,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_rest'       => true,
			'show_admin_column'  => true,
			'rewrite'            => false,
		)
	);

	register_taxonomy(
		'event_city',
		'event',
		array(
			'labels'             => array(
				'name'          => __( 'Ciudades del evento', 'camino-del-dharma-core' ),
				'singular_name' => __( 'Ciudad del evento', 'camino-del-dharma-core' ),
			),
			'hierarchical'       => false,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_rest'       => true,
			'show_admin_column'  => true,
			'rewrite'            => false,
		)
	);

	register_taxonomy(
		'gallery_album',
		'attachment',
		array(
			'labels'            => array(
				'name'          => __( 'Álbumes de galería', 'camino-del-dharma-core' ),
				'singular_name' => __( 'Álbum de galería', 'camino-del-dharma-core' ),
			),
			'hierarchical'      => false,
			'public'            => true,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug'       => 'galeria',
				'with_front' => false,
			),
		)
	);
}

/**
 * Makes album term archives actually list their attachments: attachments
 * use the inherit status, which a vanilla taxonomy main query excludes.
 *
 * @param WP_Query $query The query being prepared.
 */
function cdd_core_include_attachments_in_album_archives( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_tax( 'gallery_album' ) ) {
		return;
	}

	$query->set( 'post_type', 'attachment' );
	$query->set( 'post_status', 'inherit' );
}
