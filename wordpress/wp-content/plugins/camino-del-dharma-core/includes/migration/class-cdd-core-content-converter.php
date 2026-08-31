<?php
/**
 * Field-scoped wp:html → block conversion of imported page content
 * (WU-07). Pure string surgery, no WordPress APIs: each method converts
 * one documented fragment and returns the new content, or null when
 * there is nothing left to convert (idempotent by construction).
 *
 * The conversions are the ones recorded in the migration matrix:
 * - inicio: hardcoded featured-event aside and blog cards become the
 *   theme's dynamic blocks (doc 03 §3);
 * - galeria: the empty #gallery-albums JS mount point becomes native
 *   Gutenberg Gallery blocks per album (ADR 0021 / ADR 0036);
 * - comunidad: profile links to the blog_author fichas are added
 *   without touching the published biography (OWN-016);
 * - practica: the hand-written mantra players become native core/audio
 *   blocks bound to the imported attachments (WU-08A).
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Converts documented fragments of imported wp:html content to blocks.
 */
final class Cdd_Core_Content_Converter {

	const BLOCK_BREAK_OPEN  = "<!-- /wp:html -->\n\n";
	const BLOCK_BREAK_CLOSE = "\n\n<!-- wp:html -->";

	/**
	 * Converts the inicio page: the featured-event aside and the home
	 * blog grid become dynamic blocks, the static <picture> wrappers
	 * (handmade WebP variants that do not migrate — doc 03 §5.1) unwrap
	 * to their Library <img>, and handmade thumb paths remap to Library
	 * URLs. Null when nothing is pending.
	 *
	 * @param string $content   Stored post_content (wp:html wrapped).
	 * @param array  $media_map Source basename => Library URL for images
	 *                          whose static path has no imported original.
	 */
	public function convert_inicio( string $content, array $media_map = array() ): ?string {
		$converted = $this->replace_element(
			$content,
			'<aside class="home-featured-event"',
			'</aside>',
			self::BLOCK_BREAK_OPEN . '<!-- wp:camino-del-dharma/evento-destacado /-->' . self::BLOCK_BREAK_CLOSE
		);

		$converted = $this->replace_element(
			$converted ?? $content,
			'<ul class="home-blog-grid"',
			'</ul>',
			self::BLOCK_BREAK_OPEN . '<!-- wp:camino-del-dharma/blog-recientes /-->' . self::BLOCK_BREAK_CLOSE
		) ?? $converted;

		$unwrapped = $this->unwrap_pictures( $converted ?? $content );
		if ( null !== $unwrapped ) {
			$converted = $unwrapped;
		}

		$remapped = $this->remap_handmade_thumbs( $converted ?? $content, $media_map );
		if ( null !== $remapped ) {
			$converted = $remapped;
		}

		return $converted;
	}

	/**
	 * Unwraps every <picture> to its inner <img>, dropping the <source>
	 * variants. Null when no picture element remains.
	 *
	 * @param string $content Content to convert.
	 */
	private function unwrap_pictures( string $content ): ?string {
		if ( false === strpos( $content, '<picture>' ) ) {
			return null;
		}

		return preg_replace_callback(
			'#<picture>(.*?)</picture>#s',
			static function ( array $found ): string {
				return preg_match( '#<img\b[^>]*>#', $found[1], $img ) ? $img[0] : '';
			},
			$content
		);
	}

	/**
	 * Remaps handmade gallery-thumb paths to their Library URL. Thumbs
	 * without a mapped original stay untouched. Null when no mapped
	 * thumb path remains.
	 *
	 * @param string $content   Content to convert.
	 * @param array  $media_map Source basename => Library URL.
	 */
	private function remap_handmade_thumbs( string $content, array $media_map ): ?string {
		$changed   = false;
		$converted = preg_replace_callback(
			'#src="/?assets/images/galeria/thumbs/([a-z0-9-]+\.(?:jpe?g|png))"#',
			static function ( array $found ) use ( $media_map, &$changed ): string {
				if ( ! isset( $media_map[ $found[1] ] ) ) {
					return $found[0];
				}
				$changed = true;

				return 'src="' . htmlspecialchars( (string) $media_map[ $found[1] ], ENT_QUOTES, 'UTF-8', false ) . '"';
			},
			$content
		);

		return $changed ? $converted : null;
	}

	/**
	 * Converts the galeria hub: the empty JS mount point becomes one
	 * heading (linked to the term route, ADR 0036) plus one native
	 * Gallery block per album. Null when the mount point is gone.
	 *
	 * @param string $content Stored post_content (wp:html wrapped).
	 * @param array  $albums  Albums as {slug, title, images:{id,url,alt}[]}[].
	 */
	public function convert_galeria( string $content, array $albums ): ?string {
		$sections = array();
		foreach ( $albums as $album ) {
			$sections[] = $this->album_section( $album );
		}

		return $this->replace_element(
			$content,
			'<div id="gallery-albums"',
			'</div>',
			self::BLOCK_BREAK_OPEN . implode( "\n\n", $sections ) . self::BLOCK_BREAK_CLOSE
		);
	}

	/**
	 * Converts the comunidad page: adds one profile link per ficha
	 * (founder → zheng-gong, community → comunidad-camino-del-dharma)
	 * at the end of their sections, leaving every published word
	 * untouched (OWN-016). Null when the links already exist.
	 *
	 * @param string $content Stored post_content (wp:html wrapped).
	 */
	public function convert_comunidad( string $content ): ?string {
		if ( false !== strpos( $content, 'class="autor-ficha-link"' ) ) {
			return null;
		}

		$links = array(
			'<h2>Nuestro fundador</h2>' => '<p class="autor-ficha-link"><a href="/author/zheng-gong">Entradas del Maestro Zheng Gong en el blog</a></p>',
			'<h2>Quiénes somos</h2>'    => '<p class="autor-ficha-link"><a href="/author/comunidad-camino-del-dharma">Entradas de la Comunidad en el blog</a></p>',
		);

		$converted = $content;
		$changed   = false;
		foreach ( $links as $heading => $paragraph ) {
			$heading_at = strpos( $converted, $heading );
			if ( false === $heading_at ) {
				continue;
			}
			$section_end = strpos( $converted, '</section>', $heading_at );
			if ( false === $section_end ) {
				continue;
			}
			$converted = substr_replace( $converted, $paragraph . "\n", $section_end, 0 );
			$changed   = true;
		}

		return $changed ? $converted : null;
	}

	/**
	 * Converts the practica page: the two hand-written mantra players
	 * become native core/audio blocks bound to the imported attachments,
	 * keeping the published caption, the preload hint and the
	 * .mantra-audio class the stylesheet paints. A player whose file has
	 * no imported attachment is left exactly as published — a block
	 * pointing at a static path WordPress does not serve would be worse
	 * than the markup already there. Null when nothing is pending.
	 *
	 * @param string $content   Stored post_content (wp:html wrapped).
	 * @param array  $audio_map Source src => {id, url} of the attachment.
	 */
	public function convert_practica( string $content, array $audio_map ): ?string {
		$changed   = false;
		$converted = preg_replace_callback(
			'#<figure class="mantra-audio">.*?</figure>#s',
			function ( array $found ) use ( $audio_map, &$changed ): string {
				$block = $this->audio_block( $found[0], $audio_map );
				if ( null === $block ) {
					return $found[0];
				}
				$changed = true;

				return $block;
			},
			$content
		);

		return $changed ? $converted : null;
	}

	/**
	 * One published mantra player as a serialized core/audio block, or
	 * null when its file has no imported attachment.
	 *
	 * @param string $figure    Published figure markup.
	 * @param array  $audio_map Source src => {id, url} of the attachment.
	 */
	private function audio_block( string $figure, array $audio_map ): ?string {
		if ( ! preg_match( '#<source[^>]+src="([^"]+)"#', $figure, $source ) ) {
			return null;
		}
		if ( ! isset( $audio_map[ $source[1] ] ) ) {
			return null;
		}

		$attachment = $audio_map[ $source[1] ];
		$id         = (int) $attachment['id'];
		$caption    = preg_match( '#<figcaption[^>]*>(.*?)</figcaption>#s', $figure, $found ) ? trim( $found[1] ) : '';

		return self::BLOCK_BREAK_OPEN .
			'<!-- wp:audio {"id":' . $id . ',"className":"mantra-audio","preload":"metadata"} -->' . "\n" .
			'<figure class="wp-block-audio mantra-audio"><audio controls src="' . $this->attr( (string) $attachment['url'] ) . '" preload="metadata"></audio>' .
			( '' === $caption ? '' : '<figcaption class="wp-element-caption">' . $caption . '</figcaption>' ) .
			'</figure>' . "\n" .
			'<!-- /wp:audio -->' .
			self::BLOCK_BREAK_CLOSE;
	}

	/**
	 * One album as serialized blocks: linked heading + native gallery.
	 *
	 * @param array $album Album {slug, title, images:{id,url,alt}[]}.
	 */
	private function album_section( array $album ): string {
		$slug  = (string) $album['slug'];
		$title = (string) $album['title'];

		$images = '';
		foreach ( $album['images'] as $image ) {
			$id      = (int) $image['id'];
			$images .= '<!-- wp:image {"id":' . $id . ',"sizeSlug":"large","linkDestination":"none"} -->' . "\n" .
				'<figure class="wp-block-image size-large"><img src="' . $this->attr( (string) $image['url'] ) . '" alt="' . $this->attr( (string) $image['alt'] ) . '" class="wp-image-' . $id . '"/></figure>' . "\n" .
				'<!-- /wp:image -->' . "\n";
		}

		return '<!-- wp:heading -->' . "\n" .
			'<h2 class="wp-block-heading"><a href="/galeria/' . $this->attr( $slug ) . '">' . $this->text( $title ) . '</a></h2>' . "\n" .
			'<!-- /wp:heading -->' . "\n\n" .
			'<!-- wp:gallery {"columns":3,"imageCrop":true,"linkTo":"none","sizeSlug":"large"} -->' . "\n" .
			'<figure class="wp-block-gallery has-nested-images columns-3 is-cropped">' . "\n" . $images . '</figure>' . "\n" .
			'<!-- /wp:gallery -->';
	}

	/**
	 * Replaces one complete, non-nested element with a block insertion.
	 * Null when the element is not present (already converted).
	 *
	 * @param string $content     Content to search.
	 * @param string $open_needle Start of the element's opening tag.
	 * @param string $close_tag   The element's closing tag.
	 * @param string $replacement Replacement string.
	 */
	private function replace_element( string $content, string $open_needle, string $close_tag, string $replacement ): ?string {
		$start = strpos( $content, $open_needle );
		if ( false === $start ) {
			return null;
		}

		$close = strpos( $content, $close_tag, $start );
		if ( false === $close ) {
			return null;
		}

		$end = $close + strlen( $close_tag );

		return substr( $content, 0, $start ) . $replacement . substr( $content, $end );
	}

	/**
	 * Attribute-context escaping without WordPress (pure class).
	 *
	 * @param string $value Raw value.
	 */
	private function attr( string $value ): string {
		return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8', false );
	}

	/**
	 * Text-context escaping without WordPress (pure class).
	 *
	 * @param string $value Raw value.
	 */
	private function text( string $value ): string {
		return htmlspecialchars( $value, ENT_NOQUOTES, 'UTF-8', false );
	}
}
