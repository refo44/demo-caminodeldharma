<?php
/**
 * Level 1: deterministic event extraction from the production static HTML
 * (ADR 0034/0035, OWN-004). The extractor reads the real repo files — the
 * same ones the payload will be generated from.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: the 10 production events extracted with approved slugs.
 */
final class Event_ExtractorTest extends TestCase {

	/**
	 * Protects the ADR 0035 contract: exactly 10 events, in listing order,
	 * with exactly the approved slugs — never invented ones.
	 */
	public function test_extracts_the_ten_events_with_the_approved_slugs() {
		$events = $this->extract_events();

		$this->assertSame(
			array(
				'circulos-de-presencia-consciente',
				'encuentro-nacional-2026',
				'meditacion-presencial-barranquilla',
				'festival-calma-en-la-ciudad',
				'pausa-profunda-medellin',
				'ansiedad-agotamiento-crisis-de-atencion',
				'vesak-2026',
				'pausa-profunda-cali',
				'buddhismo-tiempos-cansancio',
				'6-encuentro-nacional-2025',
			),
			array_column( $events, 'slug' )
		);
	}

	/**
	 * Protects the current-event card contract: status/featured from the
	 * data attributes, type label, machine dates from the single's
	 * JSON-LD (start Sep 3, end Oct 24 — not the one-day calendar
	 * trigger), and the real signup URL.
	 */
	public function test_circulos_carries_the_current_event_contract() {
		$circulos = $this->event( 'circulos-de-presencia-consciente' );

		$this->assertSame( 'vigente', $circulos['status'] );
		$this->assertTrue( $circulos['featured'] );
		$this->assertSame( 'Curso', $circulos['type'] );
		$this->assertSame( '2026-09-03', $circulos['start'] );
		$this->assertSame( '2026-10-24', $circulos['end'] );
		$this->assertStringContainsString( 'docs.google.com/forms', $circulos['signup_url'] );
		$this->assertSame( 'assets/images/eventos/evento-circulos-de-presencia-consciente.jpg', $circulos['poster'] );
	}

	/**
	 * Protects the calendar contract (doc 03 §3): the Círculos cronograma
	 * yields the explicit session dates the published calendar marks —
	 * the September subset is exactly 3, 10, 15, 17, 22, 24 and 29.
	 */
	public function test_circulos_calendar_dates_come_from_the_cronograma() {
		$circulos = $this->event( 'circulos-de-presencia-consciente' );

		$september = array_values(
			array_filter(
				$circulos['calendar_dates'],
				static function ( string $date ): bool {
					return 0 === strpos( $date, '2026-09-' );
				}
			)
		);

		$this->assertSame(
			array( '2026-09-03', '2026-09-10', '2026-09-15', '2026-09-17', '2026-09-22', '2026-09-24', '2026-09-29' ),
			$september
		);
		$this->assertContains( '2026-10-17', $circulos['calendar_dates'], 'The Bogotá presencial session is marked.' );
		$this->assertContains( '2026-10-24', $circulos['calendar_dates'], 'The Cali presencial session is marked.' );
	}

	/**
	 * Protects the generated-ICS parity input (OWN-009): the current
	 * event's excerpt is the production calendar description with the
	 * {{EVENT_URL}} placeholder resolved to the canonical single URL.
	 */
	public function test_circulos_excerpt_is_the_production_calendar_description() {
		$circulos = $this->event( 'circulos-de-presencia-consciente' );

		$this->assertSame(
			'Sesión virtual de bienvenida (7:00 p. m., hora de Colombia). El curso incluye orientación, seis sesiones virtuales y un encuentro presencial en Bogotá o Cali. Cronograma: https://caminodeldharma.org/eventos/circulos-de-presencia-consciente',
			$circulos['excerpt']
		);
	}

	/**
	 * Protects completed-event extraction: JSON-LD dates from the single,
	 * the place text, no signup URL, and the card lead as excerpt.
	 */
	public function test_encuentro_2026_is_a_completed_event_with_jsonld_dates() {
		$encuentro = $this->event( 'encuentro-nacional-2026' );

		$this->assertSame( 'finalizado', $encuentro['status'] );
		$this->assertFalse( $encuentro['featured'] );
		$this->assertSame( '2026-08-07', $encuentro['start'] );
		$this->assertSame( '2026-08-09', $encuentro['end'] );
		$this->assertStringContainsString( 'Casa Retiro San Pablo', $encuentro['place'] );
		$this->assertNull( $encuentro['signup_url'] );
		$this->assertStringContainsString( 'Tres días para detenerte', $encuentro['excerpt'] );
	}

	/**
	 * Protects listing-only extraction via JSON-LD: a card without a
	 * single page still gets machine dates and its city from the
	 * structured data already published.
	 */
	public function test_listing_only_events_get_dates_and_city_from_jsonld() {
		$barranquilla = $this->event( 'meditacion-presencial-barranquilla' );
		$vesak        = $this->event( 'vesak-2026' );

		$this->assertSame( '2026-07-09', $barranquilla['start'] );
		$this->assertNull( $barranquilla['end'] );
		$this->assertSame( array( 'Barranquilla' ), $barranquilla['cities'] );
		$this->assertSame( 'Meditación', $barranquilla['type'] );

		$this->assertSame( '2026-05-09', $vesak['start'] );
		$this->assertSame( array( 'Bogotá' ), $vesak['cities'] );
		$this->assertSame( 'Celebración', $vesak['type'] );
	}

	/**
	 * Protects the Spanish-text fallback: the two cards without JSON-LD
	 * still yield machine dates from their visible Fecha text, and the
	 * online conference has no city.
	 */
	public function test_events_without_jsonld_fall_back_to_the_spanish_date_text() {
		$buddhismo      = $this->event( 'buddhismo-tiempos-cansancio' );
		$encuentro_2025 = $this->event( '6-encuentro-nacional-2025' );

		$this->assertSame( '2026-01-23', $buddhismo['start'] );
		$this->assertNull( $buddhismo['end'] );
		$this->assertSame( array(), $buddhismo['cities'] );
		$this->assertStringContainsString( 'En línea', $buddhismo['modality'] );

		$this->assertSame( '2025-08-16', $encuentro_2025['start'] );
		$this->assertSame( '2025-08-18', $encuentro_2025['end'] );
		$this->assertSame( array( 'Cali' ), $encuentro_2025['cities'] );
	}

	/**
	 * Protects content extraction: editorial copy survives (lead,
	 * highlights, cronograma when a single exists) while card chrome
	 * (badges, meta list, poster figure, share/calendar controls) does
	 * not travel into the content.
	 */
	public function test_content_keeps_editorial_copy_and_drops_card_chrome() {
		$vesak    = $this->event( 'vesak-2026' );
		$circulos = $this->event( 'circulos-de-presencia-consciente' );

		$this->assertStringContainsString( 'Una mañana de meditación colectiva', $vesak['content_html'] );
		$this->assertStringContainsString( 'Concierto de música colombiana en vivo', $vesak['content_html'] );
		$this->assertStringNotContainsString( 'evento-badge', $vesak['content_html'] );
		$this->assertStringNotContainsString( '<dl', $vesak['content_html'] );
		$this->assertStringNotContainsString( '<figure', $vesak['content_html'] );

		$this->assertStringContainsString( 'Cronograma', $circulos['content_html'], 'The single page content wins over the card.' );
		$this->assertStringNotContainsString( 'share-trigger', $circulos['content_html'] );
		$this->assertStringNotContainsString( 'calendar-trigger', $circulos['content_html'] );
		$this->assertStringNotContainsString( '<template', $circulos['content_html'] );
	}

	/**
	 * Protects the published share copy of the current event (WU-08A):
	 * the three hand-written message templates travel as data, already
	 * normalized the way share.js reads them, with the {{SHARE_URL}}
	 * placeholder intact.
	 */
	public function test_circulos_carries_the_published_share_templates() {
		$share = $this->event( 'circulos-de-presencia-consciente' )['share'];

		$this->assertSame(
			"Comparto esta invitación de Camino del Dharma:\n\n"
			. "*Curso Círculos de Presencia Consciente*\n\n"
			. "Un espacio para detenernos, escucharnos y aprender a cuidar la vida en comunidad.\n\n"
			. "Bienvenida, orientación, seis sesiones virtuales y un encuentro presencial en Bogotá o Cali.\n"
			. "Becas completas. Cupos limitados.\n\n"
			. "📅 Septiembre – octubre 2026\n"
			. "📍 Bogotá y Cali\n\n"
			. '{{SHARE_URL}}',
			$share['whatsapp']
		);
		$this->assertSame(
			"Curso · Camino del Dharma\n\nCírculos de Presencia Consciente\n📅 sep–oct 2026 · 📍 Bogotá y Cali",
			$share['x']
		);
		$this->assertSame(
			"Curso · Camino del Dharma\n\nCírculos de Presencia Consciente\n"
			. "Un espacio para detenernos, escucharnos y aprender a cuidar la vida en comunidad.\n\n"
			. "📅 Septiembre – octubre 2026\n📍 Bogotá y Cali",
			$share['threads']
		);
	}

	/**
	 * Protects the honest empty case: events for which production
	 * publishes no share controls carry no invented copy — the dialog
	 * falls back to title + URL (share.js).
	 */
	public function test_events_without_published_share_controls_carry_no_copy() {
		foreach ( array( 'vesak-2026', 'encuentro-nacional-2026' ) as $slug ) {
			$this->assertSame(
				array(
					'whatsapp' => '',
					'x'        => '',
					'threads'  => '',
				),
				$this->event( $slug )['share'],
				$slug
			);
		}
	}

	/**
	 * Runs the extractor over the real repo sources.
	 */
	private function extract_events(): array {
		static $events = null;
		if ( null !== $events ) {
			return $events;
		}

		$static_root = dirname( __DIR__, 2 ) . '/static';
		$singles     = array();
		foreach ( array( 'circulos-de-presencia-consciente', 'encuentro-nacional-2026', 'pausa-profunda-cali' ) as $slug ) {
			$singles[ $slug ] = file_get_contents( $static_root . '/eventos/' . $slug . '/index.html' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repo files in a unit test without WordPress.
		}

		$events = ( new Cdd_Core_Event_Extractor() )->extract(
			file_get_contents( $static_root . '/eventos/index.html' ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repo file in a unit test without WordPress.
			$singles
		);

		return $events;
	}

	/**
	 * One extracted event by slug.
	 *
	 * @param string $slug Approved slug.
	 */
	private function event( string $slug ): array {
		foreach ( $this->extract_events() as $event ) {
			if ( $event['slug'] === $slug ) {
				return $event;
			}
		}
		$this->fail( "Event {$slug} not extracted." );
	}
}
