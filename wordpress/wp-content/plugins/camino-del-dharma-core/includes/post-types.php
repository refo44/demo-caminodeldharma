<?php
/**
 * First-party post types: event (ADR 0035) and blog_author (ADR 0037).
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the event and blog_author post types.
 */
function cdd_core_register_post_types() {
	register_post_type(
		'event',
		array(
			'labels'       => array(
				'name'          => __( 'Eventos', 'camino-del-dharma-core' ),
				'singular_name' => __( 'Evento', 'camino-del-dharma-core' ),
				'add_new_item'  => __( 'Añadir evento', 'camino-del-dharma-core' ),
				'edit_item'     => __( 'Editar evento', 'camino-del-dharma-core' ),
			),
			'public'       => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-calendar-alt',
			'has_archive'  => 'eventos',
			'rewrite'      => array(
				'slug'       => 'eventos',
				'with_front' => false,
			),
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
		)
	);

	register_post_type(
		'blog_author',
		array(
			'labels'          => array(
				'name'          => __( 'Autores del blog', 'camino-del-dharma-core' ),
				'singular_name' => __( 'Autor del blog', 'camino-del-dharma-core' ),
				'add_new_item'  => __( 'Añadir ficha de autor', 'camino-del-dharma-core' ),
				'edit_item'     => __( 'Editar ficha de autor', 'camino-del-dharma-core' ),
			),
			'public'          => true,
			'show_in_rest'    => true,
			'menu_icon'       => 'dashicons-id-alt',
			'has_archive'     => true,
			// NEVER 'author': that query var belongs to native WP-user
			// archives and would 404 the CPT single (ADR 0037 §9).
			'query_var'       => 'blog_author',
			'rewrite'         => array(
				'slug'       => 'author',
				'with_front' => false,
			),
			'capability_type' => array( 'blog_author', 'blog_authors' ),
			'map_meta_cap'    => true,
			// 'custom-fields': the block editor reads and writes the head meta
			// (META-004) over the REST `meta` object, which the CPT only
			// exposes with this support.
			'supports'        => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		)
	);
}

/**
 * Grants the blog_author capabilities to the editorial roles. The custom
 * capability_type grants nothing by itself; without this the CPT is
 * invisible even to administrators. Runs on activation/upgrade.
 */
function cdd_core_grant_capabilities() {
	$capabilities = array(
		'edit_blog_authors',
		'edit_others_blog_authors',
		'edit_private_blog_authors',
		'edit_published_blog_authors',
		'publish_blog_authors',
		'read_private_blog_authors',
		'delete_blog_authors',
		'delete_private_blog_authors',
		'delete_published_blog_authors',
		'delete_others_blog_authors',
	);

	foreach ( array( 'administrator', 'editor' ) as $role_name ) {
		$role = get_role( $role_name );
		if ( ! $role ) {
			continue;
		}
		foreach ( $capabilities as $capability ) {
			$role->add_cap( $capability );
		}
	}
}
