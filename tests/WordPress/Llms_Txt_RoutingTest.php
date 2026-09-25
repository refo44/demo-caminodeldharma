<?php
/**
 * Level 2: /llms.txt is generated on the request (issue #37).
 *
 * An absent option is public. Disabled is a real 404. Drafts stay out.
 * Refresh requires manage_options and a nonce.
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * Behavior cluster: the llms.txt route and its publishing switch.
 */
final class Llms_Txt_RoutingTest extends WP_UnitTestCase {

	/**
	 * Pretty permalinks, then the llms rule, because registration during
	 * the suite bootstrap sees plain permalinks.
	 */
	public function set_up() {
		parent::set_up();
		delete_option( Cdd_Core_Llms_Txt::OPTION_NAME );
		$this->set_permalink_structure( '/blog/%postname%' );
		cdd_core_register_post_types();
		cdd_core_register_taxonomies();
		cdd_core_register_rewrites();
		Cdd_Core_Llms_Txt::register_rewrite();
		flush_rewrite_rules();
	}

	public function tear_down() {
		delete_option( Cdd_Core_Llms_Txt::OPTION_NAME );
		parent::tear_down();
	}

	/**
	 * Before an administrator saves the screen, the URL is text and 200.
	 */
	public function test_llms_txt_is_public_when_the_option_is_absent() {
		$this->assertFalse( get_option( Cdd_Core_Llms_Txt::OPTION_NAME ) );

		$this->go_to( '/llms.txt' );
		$response = Cdd_Core_Llms_Txt::response();

		$this->assertSame( 1, (int) get_query_var( Cdd_Core_Llms_Txt::QUERY_VAR ) );
		$this->assertSame( 200, $response['status'] );
		$this->assertStringContainsString( 'Monday marks on the Events calendar', $response['body'] );
		$this->assertStringNotContainsString( '.ics', $response['body'] );
	}

	/**
	 * Unchecked publishing is a 404 with an empty body.
	 */
	public function test_disabled_option_answers_404() {
		update_option( Cdd_Core_Llms_Txt::OPTION_NAME, 0 );

		$this->go_to( '/llms.txt' );
		$response = Cdd_Core_Llms_Txt::response();

		$this->assertSame( 404, $response['status'] );
		$this->assertSame( '', $response['body'] );
	}

	/**
	 * Turning the checkbox back on publishes the document again.
	 */
	public function test_enabled_option_answers_200() {
		update_option( Cdd_Core_Llms_Txt::OPTION_NAME, 1 );

		$this->go_to( '/llms.txt' );

		$this->assertSame( 200, Cdd_Core_Llms_Txt::response()['status'] );
	}

	/**
	 * A draft of an allow-listed page is not a link. A published page is.
	 */
	public function test_draft_page_is_omitted_and_published_page_is_listed() {
		self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
				'post_name'   => 'comunidad',
				'post_title'  => 'Borrador de comunidad',
			)
		);
		self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_name'   => 'contacto',
				'post_title'  => 'Contacto',
			)
		);

		$document = Cdd_Core_Llms_Txt::render();

		$this->assertStringNotContainsString( 'Borrador de comunidad', $document );
		$this->assertStringNotContainsString( '/comunidad', $document );
		$this->assertStringContainsString( '/contacto', $document );
	}

	/**
	 * The home note is the one current featured event. A finished event stays out.
	 */
	public function test_featured_current_event_is_listed_and_a_past_event_is_not() {
		self::factory()->post->create(
			array(
				'post_type'   => 'event',
				'post_status' => 'publish',
				'post_title'  => 'Evento pasado secreto',
				'post_name'   => 'evento-pasado',
				'meta_input'  => array(
					'event_date'     => '2020-01-01',
					'event_end'      => '2020-01-02',
					'event_featured' => '1',
				),
			)
		);
		self::factory()->post->create(
			array(
				'post_type'   => 'event',
				'post_status' => 'publish',
				'post_title'  => 'Evento vigente visible',
				'post_name'   => 'evento-vigente',
				'meta_input'  => array(
					'event_date'     => '2026-12-01',
					'event_featured' => '1',
				),
			)
		);
		self::factory()->post->create(
			array(
				'post_type'   => 'event',
				'post_status' => 'draft',
				'post_title'  => 'Evento en borrador',
				'post_name'   => 'evento-borrador',
				'meta_input'  => array(
					'event_date'     => '2026-12-15',
					'event_featured' => '1',
				),
			)
		);

		$document = Cdd_Core_Llms_Txt::render();

		$this->assertStringContainsString( 'Evento vigente visible', $document );
		$this->assertStringContainsString( '/eventos/evento-vigente', $document );
		$this->assertStringNotContainsString( 'Evento pasado secreto', $document );
		$this->assertStringNotContainsString( 'Evento en borrador', $document );
		$this->assertStringNotContainsString( '.ics', $document );
	}

	/**
	 * A subscriber must not flush permalinks.
	 */
	public function test_subscriber_refresh_is_forbidden_and_does_not_flush() {
		update_option( 'rewrite_rules', array( 'keep' => '1' ) );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$result = Cdd_Core_Llms_Txt::refresh_if_authorized( wp_create_nonce( Cdd_Core_Llms_Txt::REFRESH_NONCE ) );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'forbidden', $result->get_error_code() );
		$this->assertSame( array( 'keep' => '1' ), get_option( 'rewrite_rules' ) );
	}

	/**
	 * A bad nonce must not flush permalinks, even for an administrator.
	 */
	public function test_bad_nonce_does_not_flush() {
		update_option( 'rewrite_rules', array( 'keep' => '1' ) );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		$result = Cdd_Core_Llms_Txt::refresh_if_authorized( 'not-a-nonce' );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'invalid_nonce', $result->get_error_code() );
		$this->assertSame( array( 'keep' => '1' ), get_option( 'rewrite_rules' ) );
	}
}
