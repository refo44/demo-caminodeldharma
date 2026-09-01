<?php
/**
 * The native posts sitemap provider, plus the event archive (ADR 0030).
 *
 * `/eventos` is an indexable URL of docs/11 with no post object of its
 * own, so the core provider — which lists posts — would never emit it.
 * WordPress exposes no filter on the finished list, so the provider is
 * subclassed rather than reimplemented: the query, pagination and entry
 * filters all stay core's.
 *
 * Loaded lazily, only once WordPress has declared its parent class.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Posts provider that also lists the event archive.
 */
class Cdd_Core_Sitemap_Posts extends WP_Sitemaps_Posts {

	/**
	 * The sitemap entries of one page, with the event archive first on
	 * the first page of the event sitemap.
	 *
	 * @param int    $page_num       1-based page of the sitemap.
	 * @param string $object_subtype Post type name.
	 */
	public function get_url_list( $page_num, $object_subtype = '' ) {
		$url_list = parent::get_url_list( $page_num, $object_subtype );

		if ( 'event' === $object_subtype && 1 === (int) $page_num ) {
			array_unshift( $url_list, array( 'loc' => (string) get_post_type_archive_link( 'event' ) ) );
		}

		return $url_list;
	}
}
