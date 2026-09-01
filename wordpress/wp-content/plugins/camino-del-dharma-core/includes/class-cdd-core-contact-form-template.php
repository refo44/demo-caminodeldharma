<?php
/**
 * The Contact Form 7 form and mail definition this site owns (WU-09).
 *
 * Pure domain code: no WordPress APIs. Contact Form 7 is the only
 * approved third-party plugin (ADR 0025/0026) and its code never travels
 * in Git, so what this repository owns is the definition it provisions
 * into CF7 — and that definition is the published contact form of
 * static/contacto/index.html (OWN-007), unchanged except for the three
 * controls, which become CF7 form-tags.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Contact Form 7 form and mail templates this site owns.
 *
 * Pure: no WordPress APIs. The markup is the published contact form of
 * static/contacto/index.html (OWN-007) with its three controls replaced
 * by CF7 form-tags. The <form> element itself is the one CF7 prints, so
 * the published class and accessible name travel as shortcode options.
 */
final class Cdd_Core_Contact_Form_Template {

	/**
	 * The mailbox confirmed by the owner (ADR 0026).
	 */
	const RECIPIENT = 'caminodeldharma1@gmail.com';

	/**
	 * Title of the CF7 post, and the accessible name of the form.
	 */
	const TITLE = 'Formulario de contacto';

	/**
	 * The published form class, kept on the element CF7 prints.
	 */
	const FORM_CLASS = 'section-gap';

	/**
	 * The CF7 form template: the published markup, with [text*], [email*]
	 * and [textarea*] where production had the three controls.
	 *
	 * The submit control stays the published <button> with its icon:
	 * CF7's [submit] can only print an <input>, and CF7 listens for the
	 * form's submit event, so a plain submit button drives it just the
	 * same. CF7 appends its own [response] output before </form>.
	 */
	public static function form(): string {
		return <<<'FORM'
<div class="form-group">
  <label for="contact-name">Nombre</label>
  [text* nombre id:contact-name autocomplete:name]
</div>
<div class="form-group">
  <label for="contact-email">
    <svg class="lucide-icon lucide-icon--sm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
    Correo electrónico</label>
  [email* correo id:contact-email autocomplete:email]
</div>
<div class="form-group">
  <label for="contact-message">
    <svg class="lucide-icon lucide-icon--sm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 21 1.9-5.7a8.5 8.5 0 1 1 3.8 3.8z"/></svg>
    Mensaje</label>
  [textarea* mensaje id:contact-message]
</div>
<button type="submit" class="btn btn-primary">
  <svg class="lucide-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
  Enviar</button>
FORM;
	}

	/**
	 * The CF7 mail template. Plain text, because the form collects plain
	 * text; Reply-To is the visitor, so answering the notification
	 * answers the person.
	 *
	 * @param string $sender_email Envelope sender of the environment.
	 */
	public static function mail( string $sender_email ): array {
		$body = "Nuevo mensaje desde el formulario de contacto de [_site_title].\n\n"
			. "Nombre: [nombre]\n"
			. "Correo: [correo]\n\n"
			. "Mensaje:\n"
			. "[mensaje]\n\n"
			. "--\n"
			. 'Enviado desde [_site_url]';

		return array(
			'subject'            => '[_site_title] — Mensaje de [nombre]',
			'sender'             => '[_site_title] <' . $sender_email . '>',
			'recipient'          => self::RECIPIENT,
			'body'               => $body,
			'additional_headers' => 'Reply-To: [correo]',
			'attachments'        => '',
			'use_html'           => false,
			'exclude_blank'      => false,
		);
	}

	/**
	 * The CF7 messages this form can produce, in Spanish.
	 *
	 * The site locale comes from cdd_core_default_locale(), not from
	 * WPLANG, so WordPress installs no translation pack for Contact
	 * Form 7 and its own strings would reach a visitor in English. Only
	 * the keys three text fields can actually reach are owned here; the
	 * rest keep CF7's defaults, since no field on this form can raise
	 * them.
	 */
	public static function messages(): array {
		$fallback = 'No pudimos enviar tu mensaje. Escríbenos por WhatsApp o al correo de la comunidad.';

		return array(
			'mail_sent_ok'      => 'Gracias por escribirnos. Hemos recibido tu mensaje y te responderemos pronto.',
			'mail_sent_ng'      => $fallback,
			// Same words for spam as for a delivery failure: a false
			// positive should not be told it looked like spam.
			'spam'              => $fallback,
			'validation_error'  => 'Revisa los campos marcados: falta algo o hay un dato que no podemos leer.',
			'invalid_required'  => 'Este campo es obligatorio.',
			'invalid_too_long'  => 'Este texto es demasiado largo.',
			'invalid_too_short' => 'Este texto es demasiado corto.',
			'invalid_email'     => 'Escribe una dirección de correo válida.',
		);
	}

	/**
	 * The shortcode that renders this form, carrying the published class
	 * and accessible name onto the element CF7 prints.
	 *
	 * @param int $form_id CF7 post id.
	 */
	public static function shortcode( int $form_id ): string {
		return sprintf(
			'[contact-form-7 id="%d" title="%s" html_class="%s" html_title="%s"]',
			$form_id,
			self::TITLE,
			self::FORM_CLASS,
			self::TITLE
		);
	}

	/**
	 * Whether CF7 should run its autop over a template.
	 *
	 * CF7 autoformats the form template by default, which would wrap the
	 * hand-written labels, divs and button of {@see self::form()} in stray
	 * paragraphs. It is disabled for this form only — a form an editor
	 * creates later keeps CF7's own default — and never for mail bodies.
	 *
	 * @param bool     $enabled         CF7's default for this context.
	 * @param string   $context         'form' or 'mail'.
	 * @param int|null $current_form_id Form CF7 is rendering, if any.
	 * @param int      $our_form_id     The provisioned form id.
	 */
	public static function autop_enabled( bool $enabled, string $context, ?int $current_form_id, int $our_form_id ): bool {
		if ( 'form' !== $context || null === $current_form_id || 0 === $our_form_id ) {
			return $enabled;
		}

		return $current_form_id !== $our_form_id ? $enabled : false;
	}
}
