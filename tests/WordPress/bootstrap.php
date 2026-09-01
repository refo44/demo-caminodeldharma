<?php
/**
 * Level 2 (wp-phpunit) bootstrap. Runs only inside the ephemeral Docker
 * harness started by tools/run-phpunit-wp.sh — never against the developer
 * volume or production (docs/guia-pruebas-plugin-theme-fse.md §2).
 *
 * @package Camino_Del_Dharma_Core
 */

$cdd_root = dirname( __DIR__, 2 );

require_once $cdd_root . '/vendor/autoload.php';

$cdd_tests_dir = getenv( 'WP_PHPUNIT__DIR' );
if ( ! $cdd_tests_dir ) {
	$cdd_tests_dir = $cdd_root . '/vendor/wp-phpunit/wp-phpunit';
}

putenv( 'WP_PHPUNIT__TESTS_CONFIG=' . __DIR__ . '/wp-tests-config.php' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv -- wp-phpunit reads its config path from the environment.

require_once $cdd_tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	static function () use ( $cdd_root ) {
		// Register the repo theme tree early so WP_DEFAULT_THEME
		// (camino-del-dharma) resolves and its functions.php loads (WU-07).
		register_theme_directory( $cdd_root . '/wordpress/wp-content/themes' );

		require $cdd_root . '/wordpress/wp-content/plugins/camino-del-dharma-core/camino-del-dharma-core.php';
	}
);

require $cdd_tests_dir . '/includes/bootstrap.php';
