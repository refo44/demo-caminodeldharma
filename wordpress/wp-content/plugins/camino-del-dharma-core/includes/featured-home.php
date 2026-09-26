<?php
/**
 * Articles an editor marked for the home column.
 *
 * The event note keeps its own selection (includes/events.php). An article
 * joins that column only through post_featured on a published post.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Published articles marked for the home column, newest first.
 *
 * @return WP_Post[]
 */
function cdd_core_featured_home_posts(): array {
	$found = get_posts(
		array(
			'post_type'   => 'post',
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby'     => 'date',
			'order'       => 'DESC',
			'meta_key'    => 'post_featured', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- home column lookup on the blog catalog.
			'meta_value'  => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- home column lookup on the blog catalog.
		)
	);

	$by_id       = array();
	$descriptors = array();
	foreach ( $found as $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
		}

		$by_id[ $post->ID ] = $post;
		$descriptors[]      = array(
			'id'           => $post->ID,
			'is_published' => ( 'publish' === $post->post_status ),
			'is_featured'  => (bool) get_post_meta( $post->ID, 'post_featured', true ),
			'date'         => $post->post_date,
		);
	}

	$posts = array();
	foreach ( ( new Cdd_Core_Featured_Post_Policy() )->select( $descriptors ) as $selected ) {
		$post_id = $selected['id'] ?? 0;
		if ( isset( $by_id[ $post_id ] ) ) {
			$posts[] = $by_id[ $post_id ];
		}
	}

	return $posts;
}
