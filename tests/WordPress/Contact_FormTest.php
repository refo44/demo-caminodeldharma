<?php
/**
 * Level 2: the first-party wiring around Contact Form 7 (WU-09).
 *
 * Written RED-first. Contact Form 7 is a third-party plugin whose code
 * never travels in Git (ADR 0025), so it is absent from this hermetic
 * harness by design. That is exactly the branch worth protecting here:
 * with CF7 missing the site must still render /contacto, must never leak
 * a raw shortcode to a visitor, and must refuse to provision rather than
 * fatal. It is also the operational fallback ADR 0041 point 5 allows at
 * cutover — so it has to work, not merely not crash.
 *
 * The CF7-present path is verified against a real environment with a real
 * submission (`Pass (local)`), never by mocking WordPress or CF7.
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * Behavior cluster: contact form availability, provisioning gates and the
 * degraded render.
 */
final class Contact_FormTest extends WP_UnitTestCase {

	/**
	 * The harness runs as an anonymous user, so KSES strips <form> and its
	 * controls out of any fixture on insert. The real environments import
	 * through WP-CLI, where those filters are not installed, so keeping
	 * them here would test a fixture WordPress never actually stores.
	 */
	public function set_up() {
		parent::set_up();

		kses_remove_filters();
	}

	public function tear_down() {
		kses_init_filters();

		parent::tear_down();
	}

	/**
	 * The published /privacidad content, from the real payload.
	 */
	private static function privacidad_content(): string {
		$payload = json_decode(
			file_get_contents( dirname( __DIR__, 2 ) . '/migration/payload.json' ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repository fixture read inside the ephemeral harness.
			true
		);

		foreach ( $payload['pages'] as $page ) {
			if ( 'privacidad' === $page['slug'] ) {
				return "<!-- wp:html -->\n" . $page['content_html'] . "\n<!-- /wp:html -->";
			}
		}

		return '';
	}

	/**
	 * Creates the /privacidad Page as the importer would.
	 *
	 * @param string $content Page content.
	 */
	private function create_privacidad( string $content ): int {
		return self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_name'    => 'privacidad',
				'post_title'   => 'Aviso de privacidad',
				'post_content' => $content,
			)
		);
	}

	/**
	 * Protects the honest answer: without CF7 the form is not available,
	 * whatever the stored form id says.
	 */
	public function test_the_form_is_unavailable_without_contact_form_7() {
		update_option( 'cdd_core_contact_form_id', 4242 );

		$this->assertFalse( cdd_core_contact_form_available() );
	}

	/**
	 * Protects ADR 0041 point 5: with CF7 off the block prints the
	 * published channels, not a raw shortcode and not an empty hole.
	 */
	public function test_the_block_degrades_to_the_published_channels() {
		update_option( 'cdd_core_contact_form_id', 4242 );

		$html = do_blocks( '<!-- wp:camino-del-dharma/contacto-formulario /-->' );

		$this->assertStringNotContainsString( '[contact-form-7', $html );
		$this->assertStringNotContainsString( '<form', $html );
		$this->assertStringContainsString( 'contact-form-unavailable', $html );
		$this->assertStringContainsString( 'wa.me/573206627608', $html );
		$this->assertStringContainsString( 'caminodeldharma1@gmail.com', $html );
	}

	/**
	 * Protects the guard: provisioning refuses when CF7 is not installed,
	 * with a reason, instead of fataling on an undefined CF7 class.
	 */
	public function test_provisioning_refuses_without_contact_form_7() {
		$this->create_privacidad(
			( new Cdd_Core_Content_Converter() )->convert_privacidad( self::privacidad_content(), '31 de agosto de 2026' )
		);

		$report = cdd_core_provision_contact_form( true );

		$this->assertFalse( $report['provisioned'] );
		$this->assertStringContainsString( 'Contact Form 7', implode( ' ', $report['blockers'] ) );
		$this->assertSame( 0, (int) get_option( 'cdd_core_contact_form_id', 0 ) );
	}

	/**
	 * Protects ADR 0041 point 3 mechanically: the notice must describe the
	 * real submission BEFORE CF7 is enabled in an environment. The gate
	 * reads the published Page, so an environment that skipped the
	 * conversion cannot quietly enable a form the notice denies.
	 */
	public function test_provisioning_is_gated_on_the_privacy_copy_delta() {
		$page_id = $this->create_privacidad( self::privacidad_content() );

		$report = cdd_core_provision_contact_form( true );

		$this->assertFalse( $report['provisioned'] );
		$this->assertStringContainsString( '/privacidad', implode( ' ', $report['blockers'] ) );
		$this->assertStringContainsString( 'migrate convert', implode( ' ', $report['blockers'] ) );

		// The gate is about the copy, not about the page existing.
		$this->assertFalse( cdd_core_privacy_delta_applied() );

		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => ( new Cdd_Core_Content_Converter() )->convert_privacidad( self::privacidad_content(), '31 de agosto de 2026' ),
			)
		);

		$this->assertTrue( cdd_core_privacy_delta_applied() );
	}

	/**
	 * Protects the same gate when /privacidad has not been imported at
	 * all: no notice means no enabling.
	 */
	public function test_the_privacy_gate_is_closed_without_the_notice() {
		$this->assertFalse( cdd_core_privacy_delta_applied() );
	}

	/**
	 * Protects the ordering ADR 0041 point 3 requires: the convert pass
	 * updates the notice before it puts the form block on /contacto.
	 */
	public function test_the_convert_pass_updates_the_notice_before_the_contact_page() {
		$this->create_privacidad( self::privacidad_content() );

		$payload = json_decode(
			file_get_contents( dirname( __DIR__, 2 ) . '/migration/payload.json' ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repository fixture read inside the ephemeral harness.
			true
		);
		foreach ( $payload['pages'] as $page ) {
			if ( 'contacto' === $page['slug'] ) {
				self::factory()->post->create(
					array(
						'post_type'    => 'page',
						'post_name'    => 'contacto',
						'post_title'   => $page['title'],
						'post_content' => "<!-- wp:html -->\n" . $page['content_html'] . "\n<!-- /wp:html -->",
					)
				);
			}
		}

		$report = ( new Cdd_Core_Convert_Service() )->run( false );

		$privacidad = array_search( 'privacidad', $report['pending'], true );
		$contacto   = array_search( 'contacto', $report['pending'], true );

		$this->assertNotFalse( $privacidad );
		$this->assertNotFalse( $contacto );
		$this->assertLessThan( $contacto, $privacidad );
	}

	/**
	 * Protects the applied pass over both pages: the notice tells the
	 * truth, the dead form is gone, and a second run has nothing to do.
	 */
	public function test_apply_converts_both_pages_and_is_idempotent() {
		$privacidad_id = $this->create_privacidad( self::privacidad_content() );

		$payload     = json_decode(
			file_get_contents( dirname( __DIR__, 2 ) . '/migration/payload.json' ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repository fixture read inside the ephemeral harness.
			true
		);
		$contacto_id = 0;
		foreach ( $payload['pages'] as $page ) {
			if ( 'contacto' === $page['slug'] ) {
				$contacto_id = self::factory()->post->create(
					array(
						'post_type'    => 'page',
						'post_name'    => 'contacto',
						'post_title'   => $page['title'],
						'post_content' => "<!-- wp:html -->\n" . $page['content_html'] . "\n<!-- /wp:html -->",
					)
				);
			}
		}

		$service = new Cdd_Core_Convert_Service();
		$report  = $service->run( true );

		$this->assertContains( 'privacidad', $report['converted'] );
		$this->assertContains( 'contacto', $report['converted'] );

		$privacidad = get_post( $privacidad_id )->post_content;
		$contacto   = get_post( $contacto_id )->post_content;

		$this->assertStringContainsString( 'entrega el mensaje a caminodeldharma1@gmail.com', $privacidad );
		$this->assertStringNotContainsString( 'action="#"', $contacto );
		$this->assertStringContainsString( '<!-- wp:camino-del-dharma/contacto-formulario /-->', $contacto );

		$second = ( new Cdd_Core_Convert_Service() )->run( true );

		$this->assertNotContains( 'privacidad', $second['converted'] );
		$this->assertNotContains( 'contacto', $second['converted'] );
		$this->assertSame( $privacidad, get_post( $privacidad_id )->post_content );
		$this->assertSame( $contacto, get_post( $contacto_id )->post_content );
	}

	/**
	 * Protects the notice date: the conversion stamps the day it runs, in
	 * the site's own timezone, in the Spanish long form the page prints.
	 */
	public function test_the_notice_records_the_day_of_the_change() {
		$this->create_privacidad( self::privacidad_content() );

		( new Cdd_Core_Convert_Service() )->run( true );

		$expected = Cdd_Core_Spanish_Date::long_form( current_time( 'Y-m-d' ) );

		$this->assertStringContainsString(
			'Última actualización: ' . $expected . '.',
			get_post( get_page_by_path( 'privacidad' )->ID )->post_content
		);
	}
}
