<?php
/**
 * Title: Pie del sitio
 * Slug: camino-del-dharma/footer
 * Inserter: no
 *
 * The published footer of the static production site (OWN-007):
 * institutional block, social links, direct contact, donations copy with
 * the bank table, copyright with the privacy link, and credits.
 *
 * @package Camino_Del_Dharma
 */

?>
<footer class="site-footer">
	<div class="layout-container">
		<div class="footer-top">
			<div class="footer-block">
				<p class="footer-org">Comunidad Buddhista Camino del Dharma</p>
				<p><?php esc_html_e( 'Personería Jurídica Especial – Ministerio del Interior de Colombia', 'camino-del-dharma' ); ?></p>
				<p><a href="<?php echo esc_url( home_url( '/contacto' ) ); ?>"><?php esc_html_e( 'Contacto', 'camino-del-dharma' ); ?></a> | <?php esc_html_e( 'Redes sociales', 'camino-del-dharma' ); ?></p>
			</div>
			<div class="footer-block">
				<h3 class="footer-heading"><?php esc_html_e( 'Redes sociales', 'camino-del-dharma' ); ?></h3>
				<ul class="footer-links">
					<li><a href="https://www.facebook.com/caminodeldharmacolombia" target="_blank" rel="noopener noreferrer">Facebook <span class="visually-hidden"><?php esc_html_e( '(abre en nueva pestaña)', 'camino-del-dharma' ); ?></span></a></li>
					<li><a href="https://www.instagram.com/camino_del_dharma/" target="_blank" rel="noopener noreferrer">Instagram <span class="visually-hidden"><?php esc_html_e( '(abre en nueva pestaña)', 'camino-del-dharma' ); ?></span></a></li>
				</ul>
			</div>
			<div class="footer-block">
				<h3 class="footer-heading"><?php esc_html_e( 'Contacto directo', 'camino-del-dharma' ); ?></h3>
				<p><svg class="lucide-icon lucide-icon--sm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg> <a href="mailto:caminodeldharma1@gmail.com">caminodeldharma1@gmail.com</a></p>
				<p><svg class="lucide-icon lucide-icon--sm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg> <a href="https://wa.me/573206627608" target="_blank" rel="noopener noreferrer">+57 320 662 7608</a></p>
			</div>
		</div>

		<div class="footer-donate">
			<h3 class="footer-heading"><?php esc_html_e( 'Cómo contribuir', 'camino-del-dharma' ); ?></h3>
			<p><?php esc_html_e( 'La Comunidad Buddhista Camino del Dharma se sostiene gracias a la práctica, la participación y la generosidad consciente de quienes la acompañan.', 'camino-del-dharma' ); ?></p>
			<p><?php esc_html_e( 'Las donaciones permiten mantener los espacios de práctica, formación y encuentro, así como el funcionamiento básico de la comunidad y sus actividades presenciales y virtuales. Donar es una forma voluntaria de participar en el sostenimiento de este camino compartido.', 'camino-del-dharma' ); ?></p>
			<p><a href="<?php echo esc_url( home_url( '/donaciones' ) ); ?>"><?php esc_html_e( 'Ver datos para contribuir', 'camino-del-dharma' ); ?></a></p>
			<h3 class="footer-heading"><?php esc_html_e( 'Datos bancarios', 'camino-del-dharma' ); ?></h3>
			<table class="footer-table">
				<tr><th scope="row"><?php esc_html_e( 'Titular', 'camino-del-dharma' ); ?></th><td>Camino del Dharma</td></tr>
				<tr><th scope="row"><?php esc_html_e( 'Banco', 'camino-del-dharma' ); ?></th><td>Banco Popular</td></tr>
				<tr><th scope="row"><?php esc_html_e( 'Tipo de cuenta', 'camino-del-dharma' ); ?></th><td><?php esc_html_e( 'Ahorros', 'camino-del-dharma' ); ?></td></tr>
				<tr><th scope="row"><?php esc_html_e( 'Número de cuenta', 'camino-del-dharma' ); ?></th><td>220065151425</td></tr>
				<tr><th scope="row">NIT</th><td>901333226</td></tr>
			</table>
			<p><strong><?php esc_html_e( 'Una práctica de generosidad.', 'camino-del-dharma' ); ?></strong> <?php esc_html_e( 'Cada aporte, sin importar su monto, contribuye a que la práctica continúe siendo accesible y viva. Si en este momento no puedes donar, tu presencia y tu práctica también son una forma valiosa de sostener la comunidad.', 'camino-del-dharma' ); ?></p>
		</div>

		<p class="footer-copyright">
			<?php esc_html_e( '© 2026 Comunidad Buddhista Camino del Dharma. Todos los derechos reservados.', 'camino-del-dharma' ); ?>
			<span aria-hidden="true"> · </span><a href="<?php echo esc_url( home_url( '/privacidad' ) ); ?>"><?php esc_html_e( 'Privacidad', 'camino-del-dharma' ); ?></a>
		</p>

		<div class="footer-credits">
			<p><?php esc_html_e( 'Sitio creado por:', 'camino-del-dharma' ); ?> <a href="https://www.linkedin.com/in/rafaelfigueredo/" target="_blank" rel="noopener noreferrer">Rafael Figueredo Oropeza <span class="visually-hidden"><?php esc_html_e( '(abre en nueva pestaña)', 'camino-del-dharma' ); ?></span></a> · <a href="mailto:refo44@gmail.com">refo44@gmail.com</a> · <a href="https://www.instagram.com/ref8chan/" target="_blank" rel="noopener noreferrer">@ref8chan <span class="visually-hidden"><?php esc_html_e( '(abre en nueva pestaña)', 'camino-del-dharma' ); ?></span></a></p>
			<p><?php esc_html_e( 'Fuente MarloweEscapade — atribución al autor según licencia. Icons made from', 'camino-del-dharma' ); ?> <a href="https://www.onlinewebfonts.com/icon" target="_blank" rel="noopener noreferrer">svg icons <span class="visually-hidden"><?php esc_html_e( '(abre en nueva pestaña)', 'camino-del-dharma' ); ?></span></a> is licensed by CC BY 4.0.</p>
		</div>
	</div>
</footer>
