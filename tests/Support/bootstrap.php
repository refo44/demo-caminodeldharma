<?php
/**
 * Level 1 (unit) bootstrap: dummy ABSPATH, no WordPress boot.
 *
 * Lives in Support/ so PHPUnit does not collect it as a test
 * (docs/guia-pruebas-plugin-theme-fse.md §7).
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', sys_get_temp_dir() . '/' );
}

require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

// Plain PHP of the plugin, includable without WordPress.
require_once dirname( __DIR__, 2 ) . '/wordpress/wp-content/plugins/camino-del-dharma-core/camino-del-dharma-core.php';
