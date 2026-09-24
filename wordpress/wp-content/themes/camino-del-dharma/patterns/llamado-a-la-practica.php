<?php
/**
 * Title: Llamado a la práctica
 * Slug: camino-del-dharma/llamado-a-la-practica
 * Categories: call-to-action, camino-del-dharma
 * Inserter: yes
 * Description: Closing call to action. The label and the link are editable; the default target is Contacto.
 *
 * @package Camino_Del_Dharma
 */

?>
<!-- wp:buttons {"className":"llamado-practica section-gap","layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons llamado-practica section-gap">
	<!-- wp:button -->
	<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/contacto' ) ); ?>"><?php esc_html_e( 'Practica con nosotros', 'camino-del-dharma' ); ?></a></div>
	<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
