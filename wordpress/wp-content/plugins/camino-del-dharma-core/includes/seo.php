<?php
/**
 * Request-time SEO: head resolution, robots policy and the native
 * sitemap (docs/15-assets-strategy.md §12, ADR 0030/0031/0036/0037).
 *
 * First-party by design: no SEO suite is installed (master prompt
 * §10.2). The plugin decides *what* each request publishes; the theme
 * prints it.
 *
 * Every stored URL is written with the production base and rebased onto
 * the URL of the environment actually serving the request, so a staging
 * instance never claims caminodeldharma.org as its own identity.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const CDD_CORE_SEO_OPTION = 'cdd_core_seo_site';

/**
 * The document language of the site (docs/11 §1: español de Colombia).
 *
 * WCAG 3.1.1 needs a truthful `lang` on `<html>`, and WordPress cannot
 * store `es_CO` in `WPLANG` until its translation files are installed —
 * which a fresh or offline environment has not done. This filter states
 * the site's language independently of that download, and steps aside
 * the moment an administrator actually chooses one in Settings.
 *
 * @param string $locale Resolved locale.
 */
function cdd_core_default_locale( $locale ) {
	$chosen = (string) get_option( 'WPLANG', '' );

	return ( '' === $chosen || 'en_US' === $chosen ) ? 'es_CO' : $locale;
}

/**
 * Site-wide SEO data, seeded from the published head by the importer.
 */
function cdd_core_seo_site(): array {
	$stored = get_option( CDD_CORE_SEO_OPTION, array() );
	if ( ! is_array( $stored ) ) {
		$stored = array();
	}

	$seo = array_merge(
		array(
			'base'         => 'https://caminodeldharma.org',
			'site_name'    => get_bloginfo( 'name' ),
			'locale'       => 'es_CO',
			'image'        => '',
			'image_alt'    => '',
			'image_width'  => '',
			'image_height' => '',
			'twitter_card' => 'summary_large_image',
			'archives'     => array(),
		),
		is_array( $stored['seo'] ?? null ) ? $stored['seo'] : array()
	);

	return array(
		'seo'    => $seo,
		'jsonld' => is_array( $stored['jsonld'] ?? null ) ? $stored['jsonld'] : array(),
	);
}

/**
 * Rebases one stored value onto this environment's home URL.
 *
 * @param mixed $value Stored value (string, list or map).
 */
function cdd_core_seo_rebase( $value ) {
	$site = cdd_core_seo_site();

	return Cdd_Core_Json_Ld::rebase( $value, rtrim( $site['seo']['base'], '/' ), rtrim( home_url( '/' ), '/' ) );
}

/**
 * The site Organization reference every publisher/organizer points at.
 */
function cdd_core_seo_organization_ref(): array {
	return array( '@id' => rtrim( home_url( '/' ), '/' ) . '/#organization' );
}

/**
 * Whether the current request must stay out of the index. These are the
 * archives that wait for volume before being indexed: the author profile
 * archive (ADR 0037), gallery album terms (ADR 0036) and blog tags
 * (ADR 0031). They stay `follow`: their links must keep flowing.
 */
function cdd_core_seo_is_low_volume_archive(): bool {
	return is_post_type_archive( 'blog_author' )
		|| is_tax( 'gallery_album' )
		|| is_tag();
}

/**
 * The `wp_robots` policy of the current request. Kept as the single
 * source so the meta tag and any header can never disagree.
 *
 * @param array $robots Robots directives.
 */
function cdd_core_seo_robots( $robots ) {
	if ( is_404() || is_search() || cdd_core_seo_is_low_volume_archive() ) {
		unset( $robots['index'] );
		$robots['noindex'] = true;
		$robots['follow']  = true;

		return $robots;
	}

	$robots['max-image-preview'] = 'large';

	return $robots;
}

/**
 * The robots directive string of the current request, in the published
 * order (`index,follow,max-image-preview:large`).
 */
function cdd_core_seo_robots_value(): string {
	$robots = cdd_core_seo_robots( array() );

	if ( ! empty( $robots['noindex'] ) ) {
		return 'noindex,follow';
	}

	return 'index,follow,max-image-preview:large';
}

/**
 * The resolved SEO context of the current request.
 */
function cdd_core_seo_context(): array {
	$site    = cdd_core_seo_site();
	$context = array_merge(
		array(
			'og_type'  => 'website',
			'robots'   => cdd_core_seo_robots_value(),
			'title'    => '',
			'jsonld'   => array(),
			'related'  => '',
			'keywords' => '',
		),
		array_diff_key(
			$site['seo'],
			array(
				'archives' => true,
				'base'     => true,
			)
		)
	);

	if ( is_singular() ) {
		$post = get_queried_object();

		return array_merge( $context, cdd_core_seo_singular_context( $post ) );
	}

	if ( is_post_type_archive() ) {
		$post_type = get_query_var( 'post_type' );
		$archive   = $site['seo']['archives'][ is_array( $post_type ) ? reset( $post_type ) : $post_type ] ?? array();

		return array_merge( $context, cdd_core_seo_archive_context( (string) ( is_array( $post_type ) ? reset( $post_type ) : $post_type ), $archive ) );
	}

	if ( is_tax() || is_tag() || is_category() ) {
		$term = get_queried_object();

		return array_merge(
			$context,
			array(
				'title'     => single_term_title( '', false ) . ' — ' . get_bloginfo( 'name' ),
				'canonical' => (string) get_term_link( $term ),
				'jsonld'    => array(
					cdd_core_seo_breadcrumbs(
						array(
							array(
								'name' => single_term_title( '', false ),
								'url'  => (string) get_term_link( $term ),
							),
						)
					),
				),
			)
		);
	}

	if ( is_home() ) {
		$posts_page = get_option( 'page_for_posts' );

		if ( $posts_page ) {
			return array_merge( $context, cdd_core_seo_singular_context( get_post( $posts_page ) ) );
		}
	}

	if ( is_404() ) {
		return array_merge(
			$context,
			array(
				'title'     => __( 'Página no encontrada', 'camino-del-dharma-core' ) . ' — ' . get_bloginfo( 'name' ),
				'canonical' => '',
			)
		);
	}

	$context['title']     = get_bloginfo( 'name' );
	$context['canonical'] = home_url( '/' );

	return $context;
}

/**
 * The context of a singular request.
 *
 * @param mixed $post Queried post.
 */
function cdd_core_seo_singular_context( $post ): array {
	if ( ! $post instanceof WP_Post ) {
		return array();
	}

	$permalink = (string) get_permalink( $post );
	$image     = (string) get_the_post_thumbnail_url( $post, 'full' );
	$context   = array(
		'title'          => cdd_core_seo_meta( $post, 'seo_title', get_the_title( $post ) . ' — ' . get_bloginfo( 'name' ) ),
		'description'    => cdd_core_seo_meta( $post, 'seo_description', '' ),
		'keywords'       => cdd_core_seo_meta( $post, 'seo_keywords', '' ),
		'og_title'       => cdd_core_seo_meta( $post, 'og_title', '' ),
		'og_description' => cdd_core_seo_meta( $post, 'og_description', '' ),
		'related'        => cdd_core_seo_rebase( cdd_core_seo_meta( $post, 'seo_related_url', '' ) ),
		'canonical'      => $permalink,
		'og_type'        => 'article',
	);

	if ( '' !== $image ) {
		$attachment_id        = (int) get_post_thumbnail_id( $post );
		$context['image']     = $image;
		$alt                  = (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
		$context['image_alt'] = '' !== $alt ? $alt : '';
		unset( $context['image_width'], $context['image_height'] );
	}

	$breadcrumb_trail = array();

	switch ( $post->post_type ) {
		case 'event':
			$breadcrumb_trail  = array(
				array(
					'name' => __( 'Eventos', 'camino-del-dharma-core' ),
					'url'  => (string) get_post_type_archive_link( 'event' ),
				),
				array(
					'name' => get_the_title( $post ),
					'url'  => $permalink,
				),
			);
			$context['jsonld'] = array(
				cdd_core_seo_breadcrumbs( $breadcrumb_trail ),
				cdd_core_seo_event_node( $post, $permalink, $image ),
			);

			if ( cdd_core_event_is_current( $post ) ) {
				$context['alternate'] = array(
					'href'  => home_url( '/eventos/ical/' . $post->post_name . '.ics' ),
					'type'  => 'text/calendar',
					'title' => get_the_title( $post ),
				);
			}
			break;

		case 'post':
			$posts_page        = (int) get_option( 'page_for_posts' );
			$breadcrumb_trail  = array(
				array(
					'name' => $posts_page ? get_the_title( $posts_page ) : __( 'Blog', 'camino-del-dharma-core' ),
					'url'  => (string) get_permalink( $posts_page ),
				),
				array(
					'name' => get_the_title( $post ),
					'url'  => $permalink,
				),
			);
			$context['jsonld'] = array(
				cdd_core_seo_breadcrumbs( $breadcrumb_trail ),
				cdd_core_seo_blog_posting_node( $post, $permalink, $image ),
			);
			break;

		case 'blog_author':
			$breadcrumb_trail  = array(
				array(
					'name' => __( 'Autores', 'camino-del-dharma-core' ),
					'url'  => (string) get_post_type_archive_link( 'blog_author' ),
				),
				array(
					'name' => get_the_title( $post ),
					'url'  => $permalink,
				),
			);
			$context['jsonld'] = array(
				cdd_core_seo_breadcrumbs( $breadcrumb_trail ),
				Cdd_Core_Json_Ld::thing( get_the_title( $post ), $permalink ),
			);
			break;

		default:
			$context['og_type'] = 'website';
			$context['jsonld']  = cdd_core_seo_page_graph( $post, $permalink, $context );
			break;
	}

	return $context;
}

/**
 * The graph of an institutional Page: the published home `@graph` on the
 * front page (doc 15 §12.5), breadcrumbs elsewhere.
 *
 * @param WP_Post $post      Page.
 * @param string  $permalink Canonical URL.
 * @param array   $context   Resolved context so far.
 */
function cdd_core_seo_page_graph( WP_Post $post, string $permalink, array $context ): array {
	if ( ! is_front_page() ) {
		return array(
			cdd_core_seo_breadcrumbs(
				array(
					array(
						'name' => get_the_title( $post ),
						'url'  => $permalink,
					),
				)
			),
		);
	}

	$site  = cdd_core_seo_site();
	$graph = cdd_core_seo_rebase( $site['jsonld']['home_graph'] ?? array() );

	$featured = cdd_core_featured_home_event();

	foreach ( $graph as $index => $node ) {
		if ( 'WebPage' !== ( $node['@type'] ?? '' ) ) {
			continue;
		}

		// The page's own copy is editable; the institutional nodes are not.
		if ( '' !== $context['title'] ) {
			$graph[ $index ]['name'] = $context['title'];
		}
		if ( '' !== $context['description'] ) {
			$graph[ $index ]['description'] = $context['description'];
		}

		// The Event object lives only on its own page (doc 15 §12.3): the
		// home page references it, or says nothing.
		unset( $graph[ $index ]['mentions'] );
		if ( $featured instanceof WP_Post ) {
			$graph[ $index ]['mentions'] = array( '@id' => get_permalink( $featured ) . '#event' );
		}
	}

	return array_values( $graph );
}

/**
 * The `Event` node of one event post, from live model data.
 *
 * @param WP_Post $post      Event.
 * @param string  $permalink Canonical URL.
 * @param string  $image     Poster URL.
 */
function cdd_core_seo_event_node( WP_Post $post, string $permalink, string $image ): array {
	$status = cdd_core_event_status( $post );
	$state  = 'vigente' === $status ? 'current' : ( 'cancelado' === $status ? 'cancelled' : 'completed' );

	$places = array();
	foreach ( wp_get_post_terms( $post->ID, 'event_city' ) as $city ) {
		$places[] = array(
			'name'   => $city->name,
			'region' => (string) get_term_meta( $city->term_id, 'cdd_region', true ),
		);
	}

	$extra = json_decode( (string) get_post_meta( $post->ID, 'seo_jsonld_extra', true ), true );

	return Cdd_Core_Json_Ld::event(
		array(
			'name'           => get_the_title( $post ),
			// The published meta description is the event's own editorial
			// summary; the excerpt is the calendar description (WU-06) and
			// only stands in when no head copy was written.
			'description'    => cdd_core_seo_meta( $post, 'seo_description', wp_strip_all_tags( (string) $post->post_excerpt ) ),
			'url'            => $permalink,
			'image'          => $image,
			'start'          => (string) get_post_meta( $post->ID, 'event_date', true ),
			'end'            => (string) get_post_meta( $post->ID, 'event_end', true ),
			'state'          => $state,
			'attendance'     => (string) get_post_meta( $post->ID, 'event_attendance_mode', true ),
			'places'         => $places,
			'signup_url'     => (string) get_post_meta( $post->ID, 'event_signup_url', true ),
			'signup_payment' => (bool) get_post_meta( $post->ID, 'event_signup_payment', true ),
			'organizer'      => cdd_core_seo_organization_ref(),
			'extra'          => is_array( $extra ) ? cdd_core_seo_rebase( $extra ) : array(),
		)
	);
}

/**
 * The `BlogPosting` node of one entry (ADR 0037: authors are profiles).
 *
 * @param WP_Post $post      Entry.
 * @param string  $permalink Canonical URL.
 * @param string  $image     Featured image URL.
 */
function cdd_core_seo_blog_posting_node( WP_Post $post, string $permalink, string $image ): array {
	$authors = array();
	foreach ( cdd_core_stored_authors( $post->ID ) as $author_id ) {
		$authors[] = array(
			'name' => get_the_title( $author_id ),
			'url'  => (string) get_permalink( $author_id ),
		);
	}

	$tags = array();
	foreach ( wp_get_post_terms( $post->ID, 'post_tag' ) as $tag ) {
		$tags[] = $tag->name;
	}

	return Cdd_Core_Json_Ld::blog_posting(
		array(
			'headline'    => get_the_title( $post ),
			'description' => cdd_core_seo_meta( $post, 'seo_description', wp_strip_all_tags( (string) $post->post_excerpt ) ),
			'url'         => $permalink,
			'image'       => $image,
			'published'   => (string) get_the_date( 'Y-m-d', $post ),
			'modified'    => (string) get_the_modified_date( 'Y-m-d', $post ),
			'authors'     => $authors,
			'publisher'   => cdd_core_seo_organization_ref(),
			'tags'        => $tags,
		)
	);
}

/**
 * The context of a post-type archive. `/eventos` is the CPT archive, not
 * a Page: its published head travels as site data.
 *
 * @param string $post_type Archive post type.
 * @param array  $stored    Stored archive SEO object.
 */
function cdd_core_seo_archive_context( string $post_type, array $stored ): array {
	$link   = (string) get_post_type_archive_link( $post_type );
	$object = get_post_type_object( $post_type );
	$title  = (string) ( $stored['title'] ?? '' );
	if ( '' === $title ) {
		$title = $object ? $object->labels->name . ' — ' . get_bloginfo( 'name' ) : get_bloginfo( 'name' );
	}

	return array(
		'title'          => $title,
		'description'    => $stored['description'] ?? '',
		'keywords'       => $stored['keywords'] ?? '',
		'og_title'       => $stored['og_title'] ?? '',
		'og_description' => $stored['og_description'] ?? '',
		'canonical'      => $link,
		'jsonld'         => array(
			cdd_core_seo_breadcrumbs(
				array(
					array(
						'name' => $object ? $object->labels->name : $post_type,
						'url'  => $link,
					),
				)
			),
		),
	);
}

/**
 * A breadcrumb node that always starts at the home page.
 *
 * @param array $trail Trail after the home page.
 */
function cdd_core_seo_breadcrumbs( array $trail ): array {
	return Cdd_Core_Json_Ld::breadcrumbs(
		array_merge(
			array(
				array(
					'name' => __( 'Inicio', 'camino-del-dharma-core' ),
					'url'  => rtrim( home_url( '/' ), '/' ),
				),
			),
			$trail
		)
	);
}

/**
 * One stored head value, or the given fallback when the editor has not
 * written one. Never a value derived from another head field.
 *
 * @param WP_Post $post     Post.
 * @param string  $key      Meta key.
 * @param string  $fallback Fallback value.
 */
function cdd_core_seo_meta( WP_Post $post, string $key, string $fallback ): string {
	$value = (string) get_post_meta( $post->ID, $key, true );

	return '' !== $value ? $value : $fallback;
}

/**
 * The head document of the current request, for the theme to print.
 */
function cdd_core_seo_document(): array {
	return Cdd_Core_Seo_Document::tags( cdd_core_seo_context() );
}

/**
 * Trims the native sitemap to the URL tree of docs/11 (ADR 0030). Native
 * WP-user author archives are 404 (ADR 0037 §5), so listing their
 * provider would publish a sitemap full of soft 404s.
 *
 * @param WP_Sitemaps_Provider $provider Provider about to be registered.
 * @param string               $name     Provider name.
 */
function cdd_core_seo_sitemap_provider( $provider, $name ) {
	if ( 'users' === $name ) {
		return false;
	}

	// Swapped here, not at load time: the parent class only exists once
	// WordPress has built its sitemap server.
	if ( 'posts' === $name ) {
		require_once __DIR__ . '/seo/class-cdd-core-sitemap-posts.php';

		return new Cdd_Core_Sitemap_Posts();
	}

	return $provider;
}

/**
 * Removes every taxonomy from the sitemap: categories are not in the URL
 * tree, tags and album terms are `noindex, follow` until the documented
 * review changes (ADR 0031/0036).
 *
 * @param array $taxonomies Sitemap taxonomies.
 */
function cdd_core_seo_sitemap_taxonomies( $taxonomies ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- filter signature.
	return array();
}
