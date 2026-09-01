<?php
/**
 * Level 1: wp:html → block conversion of imported content (WU-07).
 *
 * Written RED-first. The fixtures are the real extracted page contents in
 * migration/payload.json (VERSION 1.0.35), so the conversions are proven
 * against the production copy they must preserve (OWN-007). The converter
 * is explicit and field-scoped: it only touches the documented fragments,
 * and a second pass returns null (nothing left to convert).
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: the documented conversions on imported page content.
 */
final class Content_ConverterTest extends TestCase {

	/**
	 * Payload page contents keyed by slug, wrapped as the importer stores
	 * them (a single wp:html block).
	 */
	private static array $pages = array();

	/**
	 * The imported form of one payload page, loaded lazily from the real
	 * versioned payload.
	 */
	private static function page( string $slug ): string {
		if ( empty( self::$pages ) ) {
			$payload = json_decode(
				file_get_contents( dirname( __DIR__, 2 ) . '/migration/payload.json' ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local repo file in a unit test without WordPress loaded.
				true
			);

			foreach ( $payload['pages'] as $page ) {
				self::$pages[ $page['slug'] ] = "<!-- wp:html -->\n" . $page['content_html'] . "\n<!-- /wp:html -->";
			}
		}

		return self::$pages[ $slug ];
	}

	/**
	 * Protects doc 03 §3: the home event note is the dynamic selection of
	 * camino-del-dharma-core, never the hardcoded aside frozen at import.
	 */
	public function test_inicio_swaps_the_hardcoded_aside_for_the_dynamic_block() {
		$converted = ( new Cdd_Core_Content_Converter() )->convert_inicio( self::page( 'inicio' ) );

		$this->assertIsString( $converted );
		$this->assertStringNotContainsString( '<aside class="home-featured-event"', $converted );
		$this->assertStringNotContainsString( 'home-featured-event-title', $converted );
		$this->assertStringContainsString( '<!-- wp:camino-del-dharma/evento-destacado /-->', $converted );

		// The layout row that hosted the aside survives around the block.
		$this->assertStringContainsString( 'home-community-row', $converted );
		$this->assertStringContainsString( 'Un poco de nuestra comunidad', $converted );
	}

	/**
	 * Protects the media contract (doc 03 §5.1 / ADR 0034): the static
	 * <picture> wrappers point at handmade WebP variants and thumbs that
	 * deliberately do not migrate; the conversion unwraps them to the
	 * Library <img> and remaps handmade thumb paths to Library URLs.
	 */
	public function test_inicio_unwraps_picture_sources_and_remaps_handmade_thumbs() {
		$converted = ( new Cdd_Core_Content_Converter() )->convert_inicio(
			self::page( 'inicio' ),
			array( 'galeria-01.jpg' => '/wp-content/uploads/2026/08/galeria-01.jpg' )
		);

		$this->assertStringNotContainsString( '<picture>', $converted );
		$this->assertStringNotContainsString( '<source', $converted );
		$this->assertStringContainsString( 'hero-estatua-buda-montanas.jpg', $converted, 'The Library img inside each picture survives.' );
		$this->assertStringContainsString( 'src="/wp-content/uploads/2026/08/galeria-01.jpg"', $converted );
		$this->assertStringNotContainsString( 'thumbs/galeria-01.jpg', $converted );

		// Unmapped thumbs stay untouched rather than breaking silently.
		$this->assertStringContainsString( 'thumbs/galeria-02.jpg', $converted );
	}

	/**
	 * Protects the home «Del blog» section: latest entries come from
	 * WordPress, not from the two cards frozen at import.
	 */
	public function test_inicio_swaps_the_blog_cards_for_the_dynamic_block() {
		$converted = ( new Cdd_Core_Content_Converter() )->convert_inicio( self::page( 'inicio' ) );

		$this->assertStringNotContainsString( 'home-blog-grid', $converted );
		$this->assertStringContainsString( '<!-- wp:camino-del-dharma/blog-recientes /-->', $converted );
		$this->assertStringContainsString( 'Del blog', $converted );
		$this->assertStringContainsString( 'Ver todas las entradas', $converted );
	}

	/**
	 * Protects idempotency: converting twice changes nothing (null means
	 * «no pending conversion», the create-missing-only spirit of ADR 0033).
	 */
	public function test_inicio_conversion_is_idempotent() {
		$converter = new Cdd_Core_Content_Converter();
		$converted = $converter->convert_inicio( self::page( 'inicio' ) );

		$this->assertNull( $converter->convert_inicio( $converted ) );
	}

	/**
	 * Protects ADR 0036/0021: the gallery hub renders native Gutenberg
	 * Gallery blocks per album at the old JS mount point, headings linking
	 * to the term routes, and keeps the rest of the imported copy.
	 */
	public function test_galeria_replaces_the_js_mount_with_album_gallery_blocks() {
		$albums = array(
			array(
				'slug'   => 'general',
				'title'  => 'General',
				'images' => array(
					array(
						'id'  => 11,
						'url' => '/wp-content/uploads/2026/08/galeria-01.jpg',
						'alt' => 'Grupo numeroso meditando.',
					),
					array(
						'id'  => 12,
						'url' => '/wp-content/uploads/2026/08/galeria-02.jpg',
						'alt' => '',
					),
				),
			),
			array(
				'slug'   => '2023',
				'title'  => '2023',
				'images' => array(
					array(
						'id'  => 21,
						'url' => '/wp-content/uploads/2026/08/galeria-26.jpg',
						'alt' => 'Dos monjes.',
					),
				),
			),
		);

		$converted = ( new Cdd_Core_Content_Converter() )->convert_galeria( self::page( 'galeria' ), $albums );

		$this->assertIsString( $converted );
		$this->assertStringNotContainsString( 'id="gallery-albums"', $converted );
		$this->assertStringContainsString( '<h2 class="wp-block-heading"><a href="/galeria/general">General</a></h2>', $converted );
		$this->assertStringContainsString( '<h2 class="wp-block-heading"><a href="/galeria/2023">2023</a></h2>', $converted );
		$this->assertStringContainsString( '<!-- wp:gallery', $converted );
		$this->assertStringContainsString( '"id":11', $converted );
		$this->assertStringContainsString( 'wp-image-21', $converted );
		$this->assertStringContainsString( 'alt="Grupo numeroso meditando."', $converted );
		$this->assertStringContainsString( 'Galería comunitaria', $converted );
		$this->assertStringContainsString( 'Volver al inicio', $converted );

		// Idempotent: the mount point is gone, nothing left to convert.
		$this->assertNull( ( new Cdd_Core_Content_Converter() )->convert_galeria( $converted, $albums ) );
	}

	/**
	 * Protects the mantra players (WU-08A): the two hand-written audio
	 * figures of /practica become native core/audio blocks bound to the
	 * imported Library attachments, keeping the published caption, the
	 * preload hint and the .mantra-audio hook the stylesheet paints.
	 */
	public function test_practica_converts_the_mantra_players_to_native_audio_blocks() {
		$original  = self::page( 'practica' );
		$converted = ( new Cdd_Core_Content_Converter() )->convert_practica(
			$original,
			array(
				'/assets/audio/amitabha.mp3'               => array(
					'id'  => 41,
					'url' => 'https://example.test/wp-content/uploads/2026/08/amitabha.mp3',
				),
				'/assets/audio/namo-guan-shi-yin-pusa.mp3' => array(
					'id'  => 42,
					'url' => 'https://example.test/wp-content/uploads/2026/08/namo-guan-shi-yin-pusa.mp3',
				),
			)
		);

		$this->assertIsString( $converted );
		$this->assertStringNotContainsString( '<figure class="mantra-audio">', $converted );
		$this->assertStringNotContainsString( '</source>', $converted );
		$this->assertStringContainsString( '<!-- wp:audio {"id":41,"className":"mantra-audio","preload":"metadata"} -->', $converted );
		$this->assertStringContainsString(
			'<figure class="wp-block-audio mantra-audio"><audio controls src="https://example.test/wp-content/uploads/2026/08/amitabha.mp3" preload="metadata"></audio>'
			. '<figcaption class="wp-element-caption">Recitación de Amitābha.</figcaption></figure>',
			$converted
		);
		$this->assertStringContainsString( '"id":42', $converted );
		$this->assertStringContainsString( 'Recitación de Guān Shì Yīn Púsà.', $converted );

		// The surrounding published copy is untouched.
		$this->assertStringContainsString( 'Mantras para la práctica', $converted );
		$this->assertStringContainsString( 'Luz Infinita (o Luz Inconmensurable)', $converted );

		// Idempotent: no handmade player left to convert.
		$this->assertNull( ( new Cdd_Core_Content_Converter() )->convert_practica( $converted, array() ) );
	}

	/**
	 * Protects the honest no-op: without an imported attachment for a
	 * player, the published markup stays exactly as it is rather than
	 * becoming a block pointing at a static path that WordPress does not
	 * serve.
	 */
	public function test_practica_leaves_players_without_an_imported_attachment_alone() {
		$original = self::page( 'practica' );

		$this->assertNull( ( new Cdd_Core_Content_Converter() )->convert_practica( $original, array() ) );
	}

	/**
	 * Protects OWN-016: /comunidad gains links to both blog_author profiles
	 * without replacing a single word of its published biography.
	 */
	public function test_comunidad_adds_profile_links_without_touching_the_biography() {
		$original  = self::page( 'comunidad' );
		$converted = ( new Cdd_Core_Content_Converter() )->convert_comunidad( $original );

		$this->assertIsString( $converted );
		$this->assertStringContainsString( 'href="/author/zheng-gong"', $converted );
		$this->assertStringContainsString( 'href="/author/comunidad-camino-del-dharma"', $converted );

		// Removing the two added paragraphs must give back the original.
		$stripped = preg_replace( '#<p class="autor-ficha-link">.*?</p>\n?#s', '', $converted );
		$this->assertSame( $original, $stripped );

		// Idempotent.
		$this->assertNull( ( new Cdd_Core_Content_Converter() )->convert_comunidad( $converted ) );
	}

	/**
	 * Protects ADR 0026/0041: the published form that never delivered
	 * (`action="#"`, FUNC-001) becomes the theme block that renders the
	 * Contact Form 7 form — and nothing else on the page moves.
	 */
	public function test_contacto_swaps_the_dead_form_for_the_form_block() {
		$original  = self::page( 'contacto' );
		$converted = ( new Cdd_Core_Content_Converter() )->convert_contacto( $original );

		$this->assertIsString( $converted );
		$this->assertStringNotContainsString( 'action="#"', $converted );
		$this->assertStringNotContainsString( '<form', $converted );
		$this->assertStringNotContainsString( 'id="contact-name"', $converted );
		$this->assertStringContainsString( '<!-- wp:camino-del-dharma/contacto-formulario /-->', $converted );

		// The published copy around the form is untouched, including the
		// WhatsApp/email channels TASK-0002 added beside it.
		$this->assertStringContainsString( 'La práctica se fortalece cuando se comparte.', $converted );
		$this->assertStringContainsString( 'https://wa.me/573206627608', $converted );
		$this->assertStringContainsString( 'caminodeldharma1@gmail.com', $converted );
		$this->assertStringContainsString( 'contact-social-heading', $converted );
	}

	/**
	 * Protects idempotency: a second pass has no dead form left to swap.
	 */
	public function test_contacto_conversion_is_idempotent() {
		$converter = new Cdd_Core_Content_Converter();
		$converted = $converter->convert_contacto( self::page( 'contacto' ) );

		$this->assertNull( $converter->convert_contacto( $converted ) );
	}

	/**
	 * Protects the ADR 0041 copy delta: on WordPress the form does submit,
	 * so the notice must say so. Field-scoped — the summary bullet, §2.2,
	 * the provisional box clause, the §8 trigger and the date. Nothing
	 * else in the notice is rewritten.
	 */
	public function test_privacidad_applies_the_approved_form_copy_delta() {
		$original  = self::page( 'privacidad' );
		$converted = ( new Cdd_Core_Content_Converter() )->convert_privacidad( $original, '31 de agosto de 2026' );

		$this->assertIsString( $converted );

		// The four claims that stopped being true.
		$this->assertStringNotContainsString( 'El formulario de contacto de la página no envía nada a nuestro servidor.', $converted );
		$this->assertStringNotContainsString( 'En esta versión del sitio, el formulario de contacto no envía nada a nuestro servidor.', $converted );
		$this->assertStringNotContainsString( 'cuando el formulario de contacto pase a enviarse a un servidor', $converted );
		$this->assertStringNotContainsString( 'cuando el formulario de contacto pase a procesarse en un servidor', $converted );

		// The approved replacements, verbatim (ADR 0041).
		$this->assertStringContainsString(
			'<li>El formulario de contacto envía tu mensaje al correo de la comunidad. WhatsApp y el correo directo siguen disponibles.</li>',
			$converted
		);
		$this->assertStringContainsString(
			'El formulario de la página Contacto se procesa en el servidor del sitio (Contact Form 7) y entrega el mensaje a caminodeldharma1@gmail.com. Tratamos el nombre, el correo y el contenido que envíes, con la finalidad de leer y responder tu consulta. No publicamos esos envíos en el sitio. WhatsApp y el correo directo siguen siendo canales operativos.',
			$converted
		);
		$this->assertStringContainsString( 'Última actualización: 31 de agosto de 2026.', $converted );
	}

	/**
	 * Protects ADR 0041 point 2: the notice stays provisional. The box
	 * keeps its seal and keeps saying a later legal review may change the
	 * wording — only the form clause goes.
	 */
	public function test_privacidad_keeps_the_provisional_disclaimer() {
		$converted = ( new Cdd_Core_Content_Converter() )->convert_privacidad( self::page( 'privacidad' ), '31 de agosto de 2026' );

		$this->assertStringContainsString( '<strong>Documento provisional.</strong>', $converted );
		$this->assertStringContainsString( 'aún no ha sido validada por asesoría legal', $converted );
		$this->assertStringContainsString( 'Su redacción podrá cambiar tras esa revisión.', $converted );
		$this->assertStringContainsString( 'Cada apartado indica lo que ya está activo.', $converted );

		// §8 keeps every other trigger, legal review included.
		$this->assertStringContainsString( 'cuando una asesoría legal valide o corrija el texto;', $converted );
		$this->assertStringContainsString( 'si se cambia de proveedor de alojamiento o de correo.', $converted );
	}

	/**
	 * Protects the rest of the notice: cookies, analytics, embeds,
	 * donations, rights and Ley 1581 are not rewritten (ADR 0041 point 3).
	 */
	public function test_privacidad_does_not_rewrite_the_rest_of_the_notice() {
		$original  = self::page( 'privacidad' );
		$converted = ( new Cdd_Core_Content_Converter() )->convert_privacidad( $original, '31 de agosto de 2026' );

		foreach ( array( 'cookies-heading', 'analitica-heading', 'terceros-heading', 'destinatarios-heading', 'derechos-heading', 'Ley 1581 de 2012' ) as $kept ) {
			$this->assertStringContainsString( $kept, $converted );
		}

		// Sections 2.3 to 2.6 are untouched between their own headings.
		$slice = static function ( string $html ): string {
			$from = strpos( $html, '2.3. Al escribirnos por WhatsApp' );
			$to   = strpos( $html, '3. Cookies' );

			return substr( $html, $from, $to - $from );
		};
		$this->assertSame( $slice( $original ), $slice( $converted ) );
	}

	/**
	 * Protects the markup around the delta: removing a paragraph and a
	 * list item takes their whole line with them, so the notice gains no
	 * stray blank lines where a claim used to be.
	 */
	public function test_privacidad_leaves_no_blank_lines_behind() {
		$original  = self::page( 'privacidad' );
		$converted = ( new Cdd_Core_Content_Converter() )->convert_privacidad( $original, '31 de agosto de 2026' );

		$blank = static function ( string $html ): int {
			return preg_match_all( '#\n[ \t]+\n#', $html );
		};

		$this->assertSame( $blank( $original ), $blank( $converted ) );
		$this->assertSame(
			substr_count( $original, "\n" ) - 2,
			substr_count( $converted, "\n" ),
			'Exactly the two removed lines are gone.'
		);
	}

	/**
	 * Protects idempotency: with the delta applied there is nothing left
	 * to convert, and the gate that guards CF7 activation says so.
	 */
	public function test_privacidad_conversion_is_idempotent_and_reports_the_delta() {
		$converter = new Cdd_Core_Content_Converter();
		$original  = self::page( 'privacidad' );

		$this->assertFalse( $converter::privacidad_delta_applied( $original ) );

		$converted = $converter->convert_privacidad( $original, '31 de agosto de 2026' );

		$this->assertTrue( $converter::privacidad_delta_applied( $converted ) );
		$this->assertNull( $converter->convert_privacidad( $converted, '1 de septiembre de 2026' ) );
	}
}
