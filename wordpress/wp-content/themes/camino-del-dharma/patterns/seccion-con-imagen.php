<?php
/**
 * Title: Sección con imagen
 * Slug: camino-del-dharma/seccion-con-imagen
 * Categories: text, camino-del-dharma
 * Inserter: yes
 * Description: Institutional section with an editable heading, image, and paragraphs.
 *
 * @package Camino_Del_Dharma
 */

?>
<!-- wp:group {"className":"read-width section-gap","layout":{"type":"default"}} -->
<div class="wp-block-group read-width section-gap">
	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading"><?php esc_html_e( 'Título de la sección', 'camino-del-dharma' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:image {"className":"section-figure","sizeSlug":"large"} -->
	<figure class="wp-block-image size-large section-figure"><img alt="<?php echo esc_attr__( 'Descripción de la imagen', 'camino-del-dharma' ); ?>"/></figure>
	<!-- /wp:image -->

	<!-- wp:paragraph -->
	<p><?php esc_html_e( 'Primer párrafo de ejemplo. El editor lo sustituye por el texto de la sección.', 'camino-del-dharma' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph -->
	<p><?php esc_html_e( 'Segundo párrafo de ejemplo, también editable.', 'camino-del-dharma' ); ?></p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
