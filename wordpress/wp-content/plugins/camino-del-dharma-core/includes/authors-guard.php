<?php
/**
 * Publication guard for the post `authors` relationship (ADR 0037 §7).
 *
 * Publishing or updating a published post requires at least one published
 * blog_author profile; drafts may carry none; a published post can never
 * end with zero authors; activation never unpublishes legacy posts.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Per-request stash of the authors a REST request carries: REST applies
 * meta *after* inserting the post, so the insert-time guard would not see
 * it otherwise.
 *
 * @param array|null $authors Sanitized authors to stash.
 * @param bool       $clear   Clear the stash instead.
 */
function cdd_core_requested_authors_stash( $authors = null, $clear = false ) {
	static $stash = null;

	if ( $clear ) {
		$stash = null;

		return null;
	}
	if ( null !== $authors ) {
		$stash = $authors;
	}

	return $stash;
}

/**
 * Clears the REST authors stash once the insert settles.
 */
function cdd_core_clear_requested_authors() {
	cdd_core_requested_authors_stash( null, true );
}

/**
 * The authors currently stored for a post.
 *
 * @param int $post_id Post ID (0 for a new post).
 */
function cdd_core_stored_authors( int $post_id ): array {
	if ( $post_id <= 0 ) {
		return array();
	}

	return Cdd_Core_Authors_List::normalize( get_post_meta( $post_id, 'authors', true ) );
}

/**
 * Insert-time guard: a post reaching publish without any prospective
 * author is demoted to draft. Prospective authors come from the REST
 * stash, the meta_input of the insert call, or the stored meta.
 *
 * @param array $data    Slashed, sanitized post data.
 * @param array $postarr Raw insert arguments.
 */
function cdd_core_guard_post_publish( $data, $postarr ) {
	if ( 'post' !== ( $data['post_type'] ?? '' ) ) {
		return $data;
	}
	if ( ! in_array( $data['post_status'] ?? '', array( 'publish', 'future' ), true ) ) {
		return $data;
	}

	$prospective = cdd_core_requested_authors_stash();
	if ( null === $prospective && isset( $postarr['meta_input']['authors'] ) ) {
		$prospective = cdd_core_sanitize_authors( $postarr['meta_input']['authors'] );
	}
	if ( null === $prospective ) {
		$prospective = cdd_core_stored_authors( (int) ( $postarr['ID'] ?? 0 ) );
	}

	if ( empty( $prospective ) ) {
		$data['post_status'] = 'draft';
	}

	return $data;
}

/**
 * REST guard (block editor path): stashes the authors the request carries
 * and rejects a publish without any author as an explicit error instead
 * of a silent demotion.
 *
 * @param stdClass|WP_Error $prepared_post Post object about to be inserted.
 * @param WP_REST_Request   $request       The request.
 */
function cdd_core_rest_guard_post_publish( $prepared_post, $request ) {
	if ( is_wp_error( $prepared_post ) ) {
		return $prepared_post;
	}

	cdd_core_clear_requested_authors();

	$meta = $request['meta'] ?? null;
	if ( is_array( $meta ) && array_key_exists( 'authors', $meta ) ) {
		cdd_core_requested_authors_stash( cdd_core_sanitize_authors( (array) $meta['authors'] ) );
	}

	if ( ! in_array( $prepared_post->post_status ?? '', array( 'publish', 'future' ), true ) ) {
		return $prepared_post;
	}

	$prospective = cdd_core_requested_authors_stash();
	if ( null === $prospective ) {
		$prospective = cdd_core_stored_authors( (int) ( $prepared_post->ID ?? 0 ) );
	}

	if ( empty( $prospective ) ) {
		return new WP_Error(
			'cdd_core_missing_authors',
			__( 'Para publicar una entrada asigna al menos una ficha de autor publicada.', 'camino-del-dharma-core' ),
			array( 'status' => 400 )
		);
	}

	return $prepared_post;
}

/**
 * Rejects emptying the authors of a published post: the sanitized new
 * value is empty, so the stored byline stays.
 *
 * @param null|bool $check      Whether to short-circuit the update.
 * @param int       $object_id  Post ID.
 * @param string    $meta_key   Meta key being updated.
 * @param mixed     $meta_value Sanitized new value.
 */
function cdd_core_protect_published_authors_update( $check, $object_id, $meta_key, $meta_value ) {
	if ( 'authors' !== $meta_key ) {
		return $check;
	}
	if ( 'post' !== get_post_type( $object_id ) || 'publish' !== get_post_status( $object_id ) ) {
		return $check;
	}
	if ( empty( $meta_value ) ) {
		return false;
	}

	return $check;
}

/**
 * Rejects deleting the authors meta of a published post.
 *
 * @param null|bool $check     Whether to short-circuit the delete.
 * @param int       $object_id Post ID.
 * @param string    $meta_key  Meta key being deleted.
 */
function cdd_core_protect_published_authors_delete( $check, $object_id, $meta_key ) {
	if ( 'authors' !== $meta_key ) {
		return $check;
	}
	if ( 'post' === get_post_type( $object_id ) && 'publish' === get_post_status( $object_id ) ) {
		return false;
	}

	return $check;
}

/**
 * The published blog posts related to one blog_author ficha through the
 * authors meta (ADR 0037 — never post_author), newest first.
 *
 * @param int $author_id Published blog_author post ID.
 */
function cdd_core_posts_by_blog_author( int $author_id ): array {
	$posts = get_posts(
		array(
			'post_type'   => 'post',
			'post_status' => 'publish',
			'numberposts' => -1,
			'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- small catalog; the serialized-int LIKE is the documented lookup for the authors relation.
				array(
					'key'     => 'authors',
					'value'   => 'i:' . $author_id . ';',
					'compare' => 'LIKE',
				),
			),
		)
	);

	// The LIKE over the serialized array is only a prefilter; the stored
	// relation stays authoritative.
	return array_values(
		array_filter(
			$posts,
			static function ( WP_Post $post ) use ( $author_id ): bool {
				return in_array( $author_id, cdd_core_stored_authors( $post->ID ), true );
			}
		)
	);
}
