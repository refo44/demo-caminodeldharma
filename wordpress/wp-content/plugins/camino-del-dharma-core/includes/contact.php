<?php
/**
 * Contact form: the first-party definition and wiring around Contact
 * Form 7 (WU-09, ADR 0026/0041).
 *
 * Contact Form 7 is the only approved third-party plugin (ADR 0025) and
 * its code never travels in Git. What this plugin owns is the
 * *definition* — the form template and the mail template — plus the
 * provisioning that writes them into CF7 once per environment, and the
 * gates around it:
 *
 * - the notice on /privacidad must already describe a form that submits
 *   before CF7 is enabled in an environment (ADR 0041 point 3);
 * - nothing here may fatal when CF7 is absent: a site without it keeps
 *   working on WhatsApp and email, which is the operational fallback
 *   ADR 0041 point 5 allows at cutover;
 * - Settings → Formulario de contacto can hide the form. Absent means
 *   shown. Hidden uses that same WhatsApp and email fallback.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The option holding the provisioned CF7 form id.
 */
const CDD_CORE_CONTACT_FORM_OPTION = 'cdd_core_contact_form_id';

/**
 * Whether /contacto shows the form. Absent stays shown.
 */
const CDD_CORE_CONTACT_FORM_VISIBLE_OPTION = 'cdd_core_contact_form_visible';

/**
 * Settings → Formulario de contacto.
 */
const CDD_CORE_CONTACT_FORM_PAGE = 'cdd-core-contact-form';

/**
 * Settings API group for the visibility checkbox.
 */
const CDD_CORE_CONTACT_FORM_SETTINGS_GROUP = 'cdd_core_contact_form_settings';

/**
 * The mailbox the contact form delivers to (ADR 0026).
 */
function cdd_core_contact_form_recipient(): string {
	return Cdd_Core_Contact_Form_Template::RECIPIENT;
}

/**
 * The provisioned CF7 form id, or 0 when this environment has none.
 */
function cdd_core_contact_form_id(): int {
	return (int) get_option( CDD_CORE_CONTACT_FORM_OPTION, 0 );
}

/**
 * Whether an editor has left the form on. An environment that has never
 * saved the setting still shows it.
 */
function cdd_core_contact_form_visible(): bool {
	return 1 === (int) get_option( CDD_CORE_CONTACT_FORM_VISIBLE_OPTION, 1 );
}

/**
 * Stores the checkbox as 1 or 0. Anything else is off.
 *
 * @param mixed $value Submitted value.
 */
function cdd_core_sanitize_contact_form_visible( $value ): int {
	return 1 === (int) $value ? 1 : 0;
}

/**
 * Whether this environment can actually render the contact form: CF7
 * loaded, a form provisioned, and that form still present.
 */
function cdd_core_contact_form_available(): bool {
	if ( ! defined( 'WPCF7_VERSION' ) || ! function_exists( 'wpcf7_contact_form' ) ) {
		return false;
	}

	$form_id = cdd_core_contact_form_id();
	if ( 0 === $form_id ) {
		return false;
	}

	return null !== wpcf7_contact_form( $form_id );
}

/**
 * The rendered contact form, or an empty string when this environment
 * has none. Never returns an unrendered shortcode.
 */
function cdd_core_contact_form_html(): string {
	if ( ! cdd_core_contact_form_visible() || ! cdd_core_contact_form_available() ) {
		return '';
	}

	return do_shortcode( Cdd_Core_Contact_Form_Template::shortcode( cdd_core_contact_form_id() ) );
}

/**
 * Whether the published /privacidad notice already describes a form that
 * submits server-side (ADR 0041 point 3). This is the gate that keeps an
 * environment from enabling CF7 while its own notice still denies it.
 */
function cdd_core_privacy_delta_applied(): bool {
	$page = get_page_by_path( 'privacidad', OBJECT, 'page' );
	if ( ! $page instanceof WP_Post ) {
		return false;
	}

	return Cdd_Core_Content_Converter::privacidad_delta_applied( $page->post_content );
}

/**
 * Creates the contact form in Contact Form 7 for this environment.
 *
 * Create-missing-only and idempotent, the same semantics as the importer
 * (ADR 0033): an environment whose form already exists is reported as
 * such, and an editor's later changes to that form are never overwritten.
 *
 * @param bool $apply Write the form; false = dry run.
 */
function cdd_core_provision_contact_form( bool $apply ): array {
	$report = array(
		'dry_run'     => ! $apply,
		'provisioned' => false,
		'form_id'     => cdd_core_contact_form_id(),
		'blockers'    => array(),
		'error'       => '',
	);

	// Every blocker is reported, not just the first: an operator running
	// this on a fresh environment should learn in one pass that CF7 is
	// missing AND that the notice still has to be converted.
	if ( ! defined( 'WPCF7_VERSION' ) || ! class_exists( 'WPCF7_ContactForm' ) ) {
		$report['blockers'][] = __( 'Contact Form 7 is not active in this environment.', 'camino-del-dharma-core' );
	}

	if ( ! cdd_core_privacy_delta_applied() ) {
		$report['blockers'][] = __(
			'The /privacidad notice does not describe a form that submits yet. Run `wp cdd-core migrate convert --apply` before enabling the form (ADR 0041).',
			'camino-del-dharma-core'
		);
	}

	if ( empty( $report['blockers'] ) && cdd_core_contact_form_available() ) {
		$report['blockers'][] = __( 'The contact form already exists in this environment.', 'camino-del-dharma-core' );
	}

	if ( ! empty( $report['blockers'] ) ) {
		$report['error'] = implode( ' ', $report['blockers'] );

		return $report;
	}

	if ( ! $apply ) {
		return $report;
	}

	$host   = (string) wp_parse_url( home_url(), PHP_URL_HOST );
	$sender = 'wordpress@' . ( '' !== $host ? $host : 'localhost' );

	$form = WPCF7_ContactForm::get_template(
		array(
			'title'  => Cdd_Core_Contact_Form_Template::TITLE,
			'locale' => get_locale(),
		)
	);

	$form->set_properties(
		array(
			'form'     => Cdd_Core_Contact_Form_Template::form(),
			'mail'     => Cdd_Core_Contact_Form_Template::mail( $sender ),
			'messages' => array_merge(
				(array) $form->prop( 'messages' ),
				Cdd_Core_Contact_Form_Template::messages()
			),
		)
	);

	$form_id = $form->save();
	if ( ! $form_id ) {
		$report['blockers'][] = __( 'Contact Form 7 refused to save the form.', 'camino-del-dharma-core' );
		$report['error']      = implode( ' ', $report['blockers'] );

		return $report;
	}

	update_option( CDD_CORE_CONTACT_FORM_OPTION, (int) $form_id, false );

	$report['provisioned'] = true;
	$report['form_id']     = (int) $form_id;

	return $report;
}

/**
 * Keeps CF7's autop away from the hand-written form template.
 *
 * @param bool  $enabled CF7 default.
 * @param array $options CF7 context ('for' => 'form'|'mail').
 */
function cdd_core_contact_form_autop( $enabled, $options ) {
	$current = class_exists( 'WPCF7_ContactForm' ) ? WPCF7_ContactForm::get_current() : null;

	return Cdd_Core_Contact_Form_Template::autop_enabled(
		(bool) $enabled,
		(string) ( is_array( $options ) ? ( $options['for'] ?? '' ) : '' ),
		$current instanceof WPCF7_ContactForm ? (int) $current->id() : null,
		cdd_core_contact_form_id()
	);
}

/**
 * Settings → Formulario de contacto.
 */
function cdd_core_register_contact_form_settings_page() {
	add_options_page(
		__( 'Formulario de contacto', 'camino-del-dharma-core' ),
		__( 'Formulario de contacto', 'camino-del-dharma-core' ),
		'manage_options',
		CDD_CORE_CONTACT_FORM_PAGE,
		'cdd_core_render_contact_form_settings_page'
	);
}

/**
 * Registers the checkbox. Does not write the option.
 */
function cdd_core_register_contact_form_setting() {
	register_setting(
		CDD_CORE_CONTACT_FORM_SETTINGS_GROUP,
		CDD_CORE_CONTACT_FORM_VISIBLE_OPTION,
		array(
			'type'              => 'integer',
			'sanitize_callback' => 'cdd_core_sanitize_contact_form_visible',
			'default'           => 1,
			'show_in_rest'      => false,
		)
	);

	add_settings_section(
		CDD_CORE_CONTACT_FORM_SETTINGS_GROUP . '_section',
		'',
		'__return_false',
		CDD_CORE_CONTACT_FORM_PAGE
	);
	add_settings_field(
		CDD_CORE_CONTACT_FORM_VISIBLE_OPTION,
		__( 'Mostrar formulario', 'camino-del-dharma-core' ),
		'cdd_core_render_contact_form_visible_field',
		CDD_CORE_CONTACT_FORM_PAGE,
		CDD_CORE_CONTACT_FORM_SETTINGS_GROUP . '_section'
	);
}

/**
 * Checkbox with a hidden 0 so an unchecked box turns the form off.
 */
function cdd_core_render_contact_form_visible_field() {
	printf( '<input type="hidden" name="%1$s" value="0">', esc_attr( CDD_CORE_CONTACT_FORM_VISIBLE_OPTION ) );
	printf(
		'<label><input type="checkbox" name="%1$s" value="1"%2$s> %3$s</label>',
		esc_attr( CDD_CORE_CONTACT_FORM_VISIBLE_OPTION ),
		checked( cdd_core_contact_form_visible(), true, false ),
		esc_html__( 'Mostrar el formulario de contacto', 'camino-del-dharma-core' )
	);
	echo '<p class="description">' . esc_html__( 'Activado: la página de contacto muestra el formulario. Desactivado: los visitantes ven WhatsApp y el correo.', 'camino-del-dharma-core' ) . '</p>';
}

/**
 * The settings screen. A user who cannot manage options gets nothing.
 */
function cdd_core_render_contact_form_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'Formulario de contacto', 'camino-del-dharma-core' ) . '</h1>';
	echo '<p>' . esc_html__( 'Decide si /contacto muestra el formulario. Ocultarlo deja los canales de WhatsApp y correo.', 'camino-del-dharma-core' ) . '</p>';
	echo '<form action="options.php" method="post">';
	settings_fields( CDD_CORE_CONTACT_FORM_SETTINGS_GROUP );
	do_settings_sections( CDD_CORE_CONTACT_FORM_PAGE );
	submit_button();
	echo '</form>';
	echo '</div>';
}
