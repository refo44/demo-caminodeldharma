<?php
/**
 * Title: Cabecera del sitio
 * Slug: camino-del-dharma/header
 * Inserter: no
 *
 * The published header of the static production site (OWN-007): skip
 * link, logo, main/secondary navigation and the language switcher, with
 * the exact ids/classes assets/js/main.js depends on (docs/19).
 *
 * @package Camino_Del_Dharma
 */

?>
<a href="#main" class="skip-link"><?php esc_html_e( 'Saltar al contenido', 'camino-del-dharma' ); ?></a>

<header class="site-header">
	<div class="layout-container">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo" aria-label="<?php esc_attr_e( 'Camino del Dharma — Inicio', 'camino-del-dharma' ); ?>">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>" alt="" width="120" height="48">
			<span class="site-name">Camino del Dharma</span>
		</a>
		<nav class="nav-main" aria-label="<?php esc_attr_e( 'Navegación principal', 'camino-del-dharma' ); ?>">
			<button type="button" id="nav-toggle" class="nav-toggle" aria-controls="nav-menus" aria-expanded="false">
				<span class="visually-hidden"><?php esc_html_e( 'Abrir menú', 'camino-del-dharma' ); ?></span>
				<svg aria-hidden="true" focusable="false" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
				</svg>
			</button>
			<div id="nav-menus" class="nav-menus">
				<ul class="nav-menu">
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'camino-del-dharma' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/comunidad' ) ); ?>"><?php esc_html_e( 'Comunidad', 'camino-del-dharma' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/linaje' ) ); ?>"><?php esc_html_e( 'Linaje', 'camino-del-dharma' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/practica' ) ); ?>"><?php esc_html_e( 'Práctica', 'camino-del-dharma' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/eventos' ) ); ?>"><?php esc_html_e( 'Eventos', 'camino-del-dharma' ); ?></a></li>
				</ul>
				<ul class="nav-sub">
					<li><a href="<?php echo esc_url( home_url( '/galeria' ) ); ?>"><?php esc_html_e( 'Galería', 'camino-del-dharma' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/blog' ) ); ?>"><?php esc_html_e( 'Blog', 'camino-del-dharma' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/donaciones' ) ); ?>"><?php esc_html_e( 'Contribuir', 'camino-del-dharma' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/contacto' ) ); ?>"><?php esc_html_e( 'Contacto', 'camino-del-dharma' ); ?></a></li>
				</ul>
			</div>
		</nav>
		<div class="lang-switcher" aria-label="<?php esc_attr_e( 'Idioma', 'camino-del-dharma' ); ?>">
			<button type="button" class="lang-btn" data-lang="es" aria-current="true">ES</button>
			<span class="lang-switcher-sep" aria-hidden="true">|</span>
			<button type="button" class="lang-btn" data-lang="en" disabled aria-disabled="true" title="<?php esc_attr_e( 'Próximamente en inglés', 'camino-del-dharma' ); ?>">EN</button>
		</div>
	</div>
</header>
