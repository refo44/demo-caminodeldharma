<?php
/**
 * Level 1: the Contact Form 7 definition owned by the plugin (WU-09).
 *
 * Written RED-first. Contact Form 7 is the only approved third-party
 * plugin (ADR 0025/0026) and its code never travels in Git, so what this
 * repository owns is the *definition*: the form template and the mail
 * template that first-party code provisions into CF7. These tests hold
 * that definition against the published form in
 * static/contacto/index.html (OWN-007): same labels, same field ids and
 * names, same icons, same submit button. The form element itself is the
 * one CF7 prints.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: the first-party CF7 form and mail definition.
 */
final class Contact_Form_TemplateTest extends TestCase {

	/**
	 * The published contact form, read from the live static production
	 * tree rather than retyped (ADR 0034).
	 */
	private static function published_form(): string {
		$html = file_get_contents( dirname( __DIR__, 2 ) . '/static/contacto/index.html' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local repo file in a unit test without WordPress loaded.

		preg_match( '#<form\b.*?</form>#s', (string) $html, $found );

		return $found[0];
	}

	/**
	 * Protects the destination confirmed by the owner (ADR 0026): the
	 * mailbox is part of the definition, never a per-environment guess.
	 */
	public function test_the_recipient_is_the_community_mailbox() {
		$this->assertSame(
			'caminodeldharma1@gmail.com',
			Cdd_Core_Contact_Form_Template::RECIPIENT
		);
	}

	/**
	 * Protects OWN-007: every published label, with its `for` and its
	 * icon, survives into the CF7 template unchanged.
	 */
	public function test_the_form_keeps_the_published_labels_and_icons() {
		$form      = Cdd_Core_Contact_Form_Template::form();
		$published = self::published_form();

		foreach ( array( 'contact-name', 'contact-email', 'contact-message' ) as $id ) {
			$this->assertStringContainsString( 'for="' . $id . '"', $form );
		}

		$this->assertStringContainsString( '>Nombre</label>', $form );
		$this->assertStringContainsString( 'Correo electrónico</label>', $form );
		$this->assertStringContainsString( 'Mensaje</label>', $form );

		// Every icon path published inside the form travels verbatim.
		preg_match_all( '#<path d="([^"]+)"#', $published, $paths );
		$this->assertNotEmpty( $paths[1] );
		foreach ( $paths[1] as $path ) {
			$this->assertStringContainsString( 'd="' . $path . '"', $form );
		}

		// The three .form-group wrappers the stylesheet paints.
		$this->assertSame( 3, substr_count( $form, '<div class="form-group">' ) );
	}

	/**
	 * Protects the field contract: the published names, ids and
	 * autocomplete hints become CF7 form-tags, all three required.
	 */
	public function test_the_form_declares_the_published_fields_as_required_cf7_tags() {
		$form = Cdd_Core_Contact_Form_Template::form();

		$this->assertStringContainsString( '[text* nombre id:contact-name autocomplete:name]', $form );
		$this->assertStringContainsString( '[email* correo id:contact-email autocomplete:email]', $form );
		$this->assertStringContainsString( '[textarea* mensaje id:contact-message]', $form );

		// The raw controls are gone: CF7 prints them from the tags.
		$this->assertStringNotContainsString( '<input', $form );
		$this->assertStringNotContainsString( '<textarea', $form );
	}

	/**
	 * Protects the published submit control: production ships a <button>
	 * carrying the send icon, and CF7's [submit] can only print an
	 * <input>. CF7 binds to the form's submit event, so the published
	 * button keeps working — and keeps its icon.
	 */
	public function test_the_form_keeps_the_published_submit_button_with_its_icon() {
		$form = Cdd_Core_Contact_Form_Template::form();

		$this->assertStringContainsString( '<button type="submit" class="btn btn-primary">', $form );
		$this->assertStringContainsString( 'Enviar</button>', $form );
		$this->assertStringContainsString( 'class="lucide-icon"', $form );
		$this->assertStringNotContainsString( '[submit', $form );
	}

	/**
	 * Protects the form element itself: CF7 prints it, so the published
	 * class and accessible name travel as shortcode attributes, not as
	 * markup this definition would duplicate.
	 */
	public function test_the_definition_does_not_print_its_own_form_element() {
		$form = Cdd_Core_Contact_Form_Template::form();

		$this->assertStringNotContainsString( '<form', $form );
		$this->assertStringNotContainsString( '</form>', $form );
	}

	/**
	 * Protects the shortcode CF7 renders: the published class and the
	 * published aria-label survive on the form element CF7 prints.
	 */
	public function test_the_shortcode_carries_the_published_class_and_accessible_name() {
		$shortcode = Cdd_Core_Contact_Form_Template::shortcode( 42 );

		$this->assertStringContainsString( 'id="42"', $shortcode );
		$this->assertStringContainsString( 'html_class="section-gap"', $shortcode );
		$this->assertStringContainsString( 'html_title="Formulario de contacto"', $shortcode );
		$this->assertStringStartsWith( '[contact-form-7 ', $shortcode );
	}

	/**
	 * Protects the delivery contract: the message reaches the community
	 * mailbox, carries the three published fields, and answers to the
	 * visitor rather than to the site's own sender address.
	 */
	public function test_the_mail_template_delivers_the_three_published_fields() {
		$mail = Cdd_Core_Contact_Form_Template::mail( 'wordpress@caminodeldharma.org' );

		$this->assertSame( 'caminodeldharma1@gmail.com', $mail['recipient'] );
		$this->assertStringContainsString( 'wordpress@caminodeldharma.org', $mail['sender'] );
		$this->assertSame( 'Reply-To: [correo]', $mail['additional_headers'] );

		foreach ( array( '[nombre]', '[correo]', '[mensaje]' ) as $tag ) {
			$this->assertStringContainsString( $tag, $mail['body'] );
		}

		$this->assertStringContainsString( '[nombre]', $mail['subject'] );
		$this->assertFalse( $mail['use_html'], 'Plain text: the form collects plain text.' );
	}

	/**
	 * Protects the markup: CF7 runs its own autop over the form template,
	 * which would wrap the hand-written labels and divs in stray <p>. It
	 * is disabled for this form only — another form an editor creates
	 * later keeps CF7's default.
	 */
	public function test_autop_is_disabled_for_this_form_only() {
		$this->assertFalse(
			Cdd_Core_Contact_Form_Template::autop_enabled( true, 'form', 42, 42 ),
			'Our own form template is hand-written markup.'
		);
		$this->assertTrue(
			Cdd_Core_Contact_Form_Template::autop_enabled( true, 'form', 7, 42 ),
			'Another form keeps CF7 defaults.'
		);
		$this->assertTrue(
			Cdd_Core_Contact_Form_Template::autop_enabled( true, 'mail', 42, 42 ),
			'The mail body is not the form markup.'
		);
		$this->assertTrue(
			Cdd_Core_Contact_Form_Template::autop_enabled( true, 'form', null, 42 ),
			'No current form: nothing of ours to protect.'
		);
	}

	/**
	 * Protects the language a visitor actually reads. The site locale
	 * comes from cdd_core_default_locale(), not from WPLANG, so WordPress
	 * never installs a Spanish pack for Contact Form 7 and its shipped
	 * strings would surface in English on a Spanish site. The messages
	 * this form can produce are therefore owned here, in the site's voice.
	 */
	public function test_the_form_owns_its_spanish_messages() {
		$messages = Cdd_Core_Contact_Form_Template::messages();

		foreach (
			array(
				'mail_sent_ok',
				'mail_sent_ng',
				'validation_error',
				'spam',
				'invalid_required',
				'invalid_too_long',
				'invalid_too_short',
				'invalid_email',
			) as $key
		) {
			$this->assertArrayHasKey( $key, $messages, $key );
			$this->assertNotSame( '', trim( $messages[ $key ] ), $key );
		}

		// The failure paths point at the channels that do work.
		$this->assertStringContainsString( 'WhatsApp', $messages['mail_sent_ng'] );

		// Spanish, not a translated-looking English string.
		$this->assertStringContainsString( 'Gracias por escribirnos', $messages['mail_sent_ok'] );
		$this->assertStringContainsString( 'obligatorio', $messages['invalid_required'] );

		// Only what this three-field form can produce: no file, date,
		// number, quiz or captcha copy invented for fields it has not got.
		$this->assertCount( 8, $messages );
	}
}
