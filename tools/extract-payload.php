<?php
/**
 * Deterministic migration-payload extraction runner (ADR 0032 §8.1).
 *
 * Read-only over static/: parses the production HTML/JSON with the pure
 * extractor classes of camino-del-dharma-core and writes the versioned,
 * reviewable payload to migration/payload.json. No WordPress boot, no
 * network, no writes outside migration/.
 *
 * Usage (via tools/extract-payload.sh):
 *   php tools/extract-payload.php <source-commit>
 *
 * @package Camino_Del_Dharma_Core
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( 1 );
}

define( 'ABSPATH', sys_get_temp_dir() . '/' );

$cdd_root = dirname( __DIR__ );
require $cdd_root . '/wordpress/wp-content/plugins/camino-del-dharma-core/camino-del-dharma-core.php';

$cdd_commit = $argv[1] ?? '';
if ( '' === $cdd_commit ) {
	fwrite( STDERR, "extract-payload: missing <source-commit> argument\n" );
	exit( 1 );
}

$cdd_static = $cdd_root . '/static';

/**
 * Reads one static file.
 *
 * @param string $path Repo-relative path under static/.
 */
function cdd_read( string $path ): string {
	global $cdd_static;
	$content = file_get_contents( $cdd_static . '/' . $path );
	if ( false === $content ) {
		fwrite( STDERR, "extract-payload: cannot read static/{$path}\n" );
		exit( 1 );
	}

	return $content;
}

// --- Pages (inventory §1: the 11 Page objects; /eventos is the CPT archive).
$cdd_page_sources = array(
	'inicio'                                 => 'index.html',
	'comunidad'                              => 'comunidad/index.html',
	'linaje'                                 => 'linaje/index.html',
	'practica'                               => 'practica/index.html',
	'practica/videos'                        => 'practica/videos/index.html',
	'practica/meditacion-semanal-en-linea'   => 'practica/meditacion-semanal-en-linea/index.html',
	'galeria'                                => 'galeria/index.html',
	'donaciones'                             => 'donaciones/index.html',
	'contacto'                               => 'contacto/index.html',
	'blog'                                   => 'blog/index.html',
	'privacidad'                             => 'privacidad/index.html',
);

$cdd_page_extractor = new Cdd_Core_Page_Extractor();
$cdd_pages          = array();
foreach ( $cdd_page_sources as $cdd_slug => $cdd_path ) {
	$cdd_page                = $cdd_page_extractor->extract( $cdd_slug, cdd_read( $cdd_path ) );
	$cdd_page['source_path'] = 'static/' . $cdd_path;
	$cdd_page['parent']      = false !== strpos( $cdd_slug, '/' ) ? dirname( $cdd_slug ) : '';
	// The posts page renders the query, not stored content.
	if ( 'blog' === $cdd_slug ) {
		$cdd_page['content_html'] = '';
	}
	$cdd_pages[] = $cdd_page;
}

// --- Events (inventory §2).
$cdd_single_slugs = array( 'circulos-de-presencia-consciente', 'encuentro-nacional-2026', 'pausa-profunda-cali' );
$cdd_singles      = array();
foreach ( $cdd_single_slugs as $cdd_slug ) {
	$cdd_singles[ $cdd_slug ] = cdd_read( 'eventos/' . $cdd_slug . '/index.html' );
}
$cdd_events = ( new Cdd_Core_Event_Extractor() )->extract( cdd_read( 'eventos/index.html' ), $cdd_singles );
foreach ( $cdd_events as &$cdd_event ) {
	$cdd_event['source_path'] = in_array( $cdd_event['slug'], $cdd_single_slugs, true )
		? 'static/eventos/' . $cdd_event['slug'] . '/index.html'
		: 'static/eventos/index.html';
}
unset( $cdd_event );

// --- Posts and author profiles (inventory §3, ADR 0037).
$cdd_post_sources = array();
foreach ( array( 'circulos-de-presencia-consciente', 'sangha-refugio-hiperconexion' ) as $cdd_slug ) {
	$cdd_post_sources[ $cdd_slug ] = cdd_read( 'blog/' . $cdd_slug . '/index.html' );
}
$cdd_posts = ( new Cdd_Core_Blog_Extractor() )->extract( $cdd_post_sources );
foreach ( $cdd_posts as &$cdd_post ) {
	$cdd_post['source_path'] = 'static/blog/' . $cdd_post['slug'] . '/index.html';
	$cdd_post['authors']     = array( $cdd_post['author_slug'] );
}
unset( $cdd_post );

$cdd_authors = array();
foreach ( $cdd_posts as $cdd_post ) {
	if ( ! isset( $cdd_authors[ $cdd_post['author_slug'] ] ) || '' === $cdd_authors[ $cdd_post['author_slug'] ]['bio'] ) {
		$cdd_authors[ $cdd_post['author_slug'] ] = array(
			'slug' => $cdd_post['author_slug'],
			'name' => $cdd_post['author_name'],
			'bio'  => $cdd_post['author_bio'],
		);
	}
}
ksort( $cdd_authors );
$cdd_blog_authors = array_values( $cdd_authors );

// --- Gallery (inventory §4).
$cdd_gallery = ( new Cdd_Core_Gallery_Extractor() )->extract( cdd_read( 'galeria/index.html' ) );

// --- Media inventory (inventory §6): files on disk vs referenced paths.
$cdd_files    = array();
$cdd_iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $cdd_static . '/assets', FilesystemIterator::SKIP_DOTS ) );
foreach ( $cdd_iterator as $cdd_file ) {
	$cdd_relative = substr( $cdd_file->getPathname(), strlen( $cdd_static ) + 1 );
	if ( preg_match( '#^assets/(images|audio)/#', $cdd_relative ) ) {
		$cdd_files[] = $cdd_relative;
	}
}
sort( $cdd_files );

$cdd_referenced = array();
$cdd_html_files = array_merge(
	array_values( $cdd_page_sources ),
	array( 'eventos/index.html', '404.html' ),
	array_map(
		static function ( string $slug ): string {
			return 'eventos/' . $slug . '/index.html';
		},
		$cdd_single_slugs
	),
	array_map(
		static function ( string $slug ): string {
			return 'blog/' . $slug . '/index.html';
		},
		array_keys( $cdd_post_sources )
	)
);
foreach ( $cdd_html_files as $cdd_path ) {
	$cdd_html = cdd_read( $cdd_path );
	preg_match_all( '/\b(?:src|href|poster|content)="([^"]*)"/', $cdd_html, $cdd_urls );
	$cdd_url_list = $cdd_urls[1];
	// srcset holds comma-separated "url width" candidates.
	preg_match_all( '/\bsrcset="([^"]*)"/', $cdd_html, $cdd_srcsets );
	foreach ( $cdd_srcsets[1] as $cdd_srcset ) {
		foreach ( explode( ',', $cdd_srcset ) as $cdd_candidate_entry ) {
			$cdd_url_list[] = strtok( trim( $cdd_candidate_entry ), " \t\n" );
		}
	}
	foreach ( $cdd_url_list as $cdd_url ) {
		$cdd_url = preg_replace( '#^https?://caminodeldharma\.org/#', '', $cdd_url );
		$cdd_candidate = Cdd_Core_Dom::to_source_path( $cdd_url );
		if ( preg_match( '#^assets/(images|audio)/#', $cdd_candidate ) ) {
			$cdd_referenced[ $cdd_candidate ] = true;
		}
	}
}
// Structured references live outside HTML attributes: the gallery JSON,
// the event posters and the post heroes are extracted collections.
foreach ( $cdd_gallery['images'] as $cdd_image ) {
	$cdd_referenced[ $cdd_image['file'] ] = true;
}
foreach ( $cdd_events as $cdd_event_row ) {
	if ( '' !== $cdd_event_row['poster'] ) {
		$cdd_referenced[ $cdd_event_row['poster'] ] = true;
	}
}
foreach ( $cdd_posts as $cdd_post_row ) {
	if ( '' !== $cdd_post_row['thumbnail'] ) {
		$cdd_referenced[ $cdd_post_row['thumbnail'] ] = true;
	}
}

$cdd_media = ( new Cdd_Core_Media_Inventory() )->classify( $cdd_files, array_keys( $cdd_referenced ) );

// --- Video embeds (inventory §7).
$cdd_embeds = $cdd_page_extractor->extract_embeds( cdd_read( 'practica/videos/index.html' ) );
foreach ( $cdd_embeds as &$cdd_embed ) {
	$cdd_embed['page'] = 'practica/videos';
}
unset( $cdd_embed );

// --- Assemble.
$cdd_payload = ( new Cdd_Core_Payload_Builder() )->build(
	array(
		'pages'          => $cdd_pages,
		'events'         => $cdd_events,
		'posts'          => $cdd_posts,
		'blog_authors'   => $cdd_blog_authors,
		'gallery_albums' => $cdd_gallery['albums'],
		'gallery_images' => $cdd_gallery['images'],
		'media'          => $cdd_media,
		'video_embeds'   => $cdd_embeds,
	),
	array(
		'version' => trim( (string) file_get_contents( $cdd_root . '/VERSION' ) ),
		'commit'  => $cdd_commit,
		'root'    => 'static',
	)
);

if ( ! is_dir( $cdd_root . '/migration' ) ) {
	mkdir( $cdd_root . '/migration', 0755 );
}
file_put_contents( $cdd_root . '/migration/payload.json', Cdd_Core_Payload_Builder::to_json( $cdd_payload ) );

fwrite( STDOUT, 'payload written: migration/payload.json (' . implode( ', ', array_map( static function ( $k, $v ) { return "{$k}={$v}"; }, array_keys( $cdd_payload['counts'] ), $cdd_payload['counts'] ) ) . ")\n" );
