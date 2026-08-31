<?php
/**
 * wp-admin tooling of the domain plugin: «Eliminar huérfanos» (OWN-015).
 *
 * WordPress generates `/eventos/ical/{slug}.ics` on request and never
 * stores a file (OWN-009), so the only calendar attachments that can
 * exist are leftovers — from the static era, or from an event that has
 * since finished. The automatic pass (OWN-013) already stops serving
 * them; this screen is the manual broom.
 *
 * Scope is deliberately narrow (OWN-015): `text/calendar` only. Photos
 * (OWN-003), mantra mp3s and posters are never touched, and neither are
 * the trash or post revisions.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const CDD_CORE_ORPHANS_SLUG  = 'cdd-core-orphans';
const CDD_CORE_ORPHANS_NONCE = 'cdd_core_delete_orphans';

/**
 * Whether the current user may run the tool: whoever can edit events.
 */
function cdd_core_can_manage_orphans(): bool {
	$post_type = get_post_type_object( 'event' );

	return null !== $post_type && current_user_can( $post_type->cap->edit_posts );
}

/**
 * The calendar attachments that no current event backs.
 *
 * Read-only: listing never deletes (dry-run first, OWN-015).
 */
function cdd_core_orphan_calendars(): array {
	$attachments = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'text/calendar',
			'numberposts'    => -1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		)
	);

	$orphans = array();
	foreach ( $attachments as $attachment ) {
		$slug  = cdd_core_calendar_slug( $attachment );
		$event = $slug ? get_page_by_path( $slug, OBJECT, 'event' ) : null;

		if ( $event instanceof WP_Post && cdd_core_event_is_current( $event ) ) {
			continue;
		}

		$orphans[] = array(
			'id'     => $attachment->ID,
			'file'   => (string) get_post_meta( $attachment->ID, '_wp_attached_file', true ),
			'title'  => $attachment->post_title,
			'reason' => $event instanceof WP_Post
				? __( 'el evento ya no está vigente', 'camino-del-dharma-core' )
				: __( 'no corresponde a ningún evento', 'camino-del-dharma-core' ),
		);
	}

	return $orphans;
}

/**
 * The event slug a calendar attachment refers to, from its file name.
 *
 * @param WP_Post $attachment Calendar attachment.
 */
function cdd_core_calendar_slug( WP_Post $attachment ): string {
	$file = (string) get_post_meta( $attachment->ID, '_wp_attached_file', true );
	$name = '' !== $file ? wp_basename( $file ) : $attachment->post_title;

	return sanitize_title( preg_replace( '/\.ics$/i', '', $name ) );
}

/**
 * Deletes the listed orphans permanently and returns how many went.
 */
function cdd_core_delete_orphan_calendars(): int {
	$deleted = 0;

	foreach ( cdd_core_orphan_calendars() as $orphan ) {
		if ( wp_delete_attachment( $orphan['id'], true ) ) {
			++$deleted;
		}
	}

	return $deleted;
}

/**
 * Registers the Tools screen.
 */
function cdd_core_register_admin_pages() {
	$post_type = get_post_type_object( 'event' );

	add_management_page(
		__( 'Eliminar huérfanos', 'camino-del-dharma-core' ),
		__( 'Eliminar huérfanos', 'camino-del-dharma-core' ),
		null !== $post_type ? $post_type->cap->edit_posts : 'edit_posts',
		CDD_CORE_ORPHANS_SLUG,
		'cdd_core_render_orphans_page'
	);
}

/**
 * Renders the screen: the list first, deletion only behind an explicit,
 * nonce-protected submit.
 */
function cdd_core_render_orphans_page() {
	if ( ! cdd_core_can_manage_orphans() ) {
		wp_die( esc_html__( 'No tienes permiso para usar esta herramienta.', 'camino-del-dharma-core' ) );
	}

	$deleted = null;
	if ( isset( $_POST['cdd_core_orphans_apply'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified on the next line.
		check_admin_referer( CDD_CORE_ORPHANS_NONCE );
		$deleted = cdd_core_delete_orphan_calendars();
	}

	$orphans = cdd_core_orphan_calendars();

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'Eliminar huérfanos', 'camino-del-dharma-core' ) . '</h1>';
	echo '<p>' . esc_html__( 'Los calendarios .ics se generan en cada petición: no debería quedar ninguno guardado en la biblioteca. Esta herramienta solo lista y borra archivos .ics; nunca fotografías, audios ni carteles.', 'camino-del-dharma-core' ) . '</p>';

	if ( null !== $deleted ) {
		printf(
			'<div class="notice notice-success"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: %d: number of deleted calendar files. */
					_n( 'Se eliminó %d calendario huérfano.', 'Se eliminaron %d calendarios huérfanos.', $deleted, 'camino-del-dharma-core' ),
					$deleted
				)
			)
		);
	}

	if ( array() === $orphans ) {
		echo '<p>' . esc_html__( 'No hay calendarios huérfanos.', 'camino-del-dharma-core' ) . '</p>';
		echo '</div>';

		return;
	}

	echo '<table class="widefat striped"><thead><tr>';
	echo '<th scope="col">' . esc_html__( 'Archivo', 'camino-del-dharma-core' ) . '</th>';
	echo '<th scope="col">' . esc_html__( 'Motivo', 'camino-del-dharma-core' ) . '</th>';
	echo '</tr></thead><tbody>';
	foreach ( $orphans as $orphan ) {
		echo '<tr><td>' . esc_html( '' !== $orphan['file'] ? $orphan['file'] : $orphan['title'] ) . '</td>';
		echo '<td>' . esc_html( $orphan['reason'] ) . '</td></tr>';
	}
	echo '</tbody></table>';

	echo '<form method="post">';
	wp_nonce_field( CDD_CORE_ORPHANS_NONCE );
	submit_button( __( 'Eliminar huérfanos', 'camino-del-dharma-core' ), 'delete', 'cdd_core_orphans_apply' );
	echo '</form></div>';
}
