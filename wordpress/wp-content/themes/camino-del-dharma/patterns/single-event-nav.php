<?php
/**
 * Title: Navegación del evento
 * Slug: camino-del-dharma/single-event-nav
 * Inserter: no
 *
 * The closing navigation of an event single, as published.
 *
 * @package Camino_Del_Dharma
 */

?>
<nav class="single-event-nav" aria-label="<?php esc_attr_e( 'Navegación del evento', 'camino-del-dharma' ); ?>">
	<p><a href="<?php echo esc_url( home_url( '/eventos' ) ); ?>"><?php esc_html_e( '← Volver a Eventos', 'camino-del-dharma' ); ?></a></p>
	<p><a href="<?php echo esc_url( home_url( '/practica' ) ); ?>"><?php esc_html_e( 'Práctica', 'camino-del-dharma' ); ?></a> · <a href="<?php echo esc_url( home_url( '/contacto' ) ); ?>"><?php esc_html_e( 'Contacto', 'camino-del-dharma' ); ?></a></p>
</nav>
