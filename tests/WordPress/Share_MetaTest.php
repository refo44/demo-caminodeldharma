<?php
/**
 * Level 2: the share message templates as editable domain meta (WU-08A).
 *
 * Written RED-first. The published site hard-codes one hand-written
 * WhatsApp/X/Threads message per event and per entry inside <template>
 * elements (ADR 0034: production content). In WordPress that copy becomes
 * meta an editor can rewrite from wp-admin, and the theme prints it back
 * into the templates share.js reads.
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * Behavior cluster: share_* meta registration and sanitization.
 */
final class Share_MetaTest extends WP_UnitTestCase {

	/**
	 * The suite unregisters meta after each test; re-register for the
	 * registry assertions.
	 */
	public function set_up() {
		parent::set_up();
		cdd_core_register_meta();
	}

	/**
	 * Protects the model: the three share templates are registered meta on
	 * both shareable types, exposed to the block editor through REST.
	 */
	public function test_share_templates_are_registered_on_events_and_posts() {
		foreach ( array( 'event', 'post' ) as $post_type ) {
			$registered = get_registered_meta_keys( 'post', $post_type );

			foreach ( array( 'share_whatsapp', 'share_x', 'share_threads' ) as $key ) {
				$this->assertArrayHasKey( $key, $registered, $post_type . '/' . $key );
				$this->assertSame( 'string', $registered[ $key ]['type'] );
				$this->assertTrue( $registered[ $key ]['single'] );
				$this->assertTrue( $registered[ $key ]['show_in_rest'] );
			}
		}
	}

	/**
	 * Protects the copy: line breaks and the {{SHARE_URL}} placeholder are
	 * the message's structure and must survive sanitization, while markup
	 * must not — the value is injected into intent URLs, never into HTML.
	 */
	public function test_share_template_sanitization_keeps_line_breaks_and_the_placeholder() {
		$event = self::factory()->post->create( array( 'post_type' => 'event' ) );

		update_post_meta(
			$event,
			'share_whatsapp',
			"Comparto esta invitación:\n\n*Curso*\n\n{{SHARE_URL}}"
		);

		$this->assertSame(
			"Comparto esta invitación:\n\n*Curso*\n\n{{SHARE_URL}}",
			get_post_meta( $event, 'share_whatsapp', true )
		);
	}

	/**
	 * Protects the boundary: a template is plain text. Markup, scripts and
	 * carriage returns are normalized away before storage.
	 */
	public function test_share_template_sanitization_strips_markup() {
		$post = self::factory()->post->create();

		update_post_meta( $post, 'share_x', "<script>alert(1)</script>Título\r\nsegunda línea" );

		$this->assertSame( "Título\nsegunda línea", get_post_meta( $post, 'share_x', true ) );
	}

	/**
	 * Protects the empty case: a non-string value stores as the empty
	 * string, never as an array or `Array`.
	 */
	public function test_share_template_sanitization_rejects_non_strings() {
		$post = self::factory()->post->create();

		update_post_meta( $post, 'share_threads', array( 'a', 'b' ) );

		$this->assertSame( '', get_post_meta( $post, 'share_threads', true ) );
	}
}
