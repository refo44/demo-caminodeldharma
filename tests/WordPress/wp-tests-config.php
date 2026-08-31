<?php
/**
 * wp-phpunit configuration for the ephemeral Docker harness
 * (docker compose -p cdd-wp-phpunit, tools/run-phpunit-wp.sh).
 *
 * Credentials come from the compose environment of that disposable project
 * (tools/wp-tests.env); the wptests_ table prefix keeps the suite away from
 * any real install even if misconfigured.
 *
 * @package Camino_Del_Dharma_Core
 */

define( 'ABSPATH', '/var/www/html/' );

define( 'DB_NAME', getenv( 'WORDPRESS_DB_NAME' ) ? getenv( 'WORDPRESS_DB_NAME' ) : 'wptests' );
define( 'DB_USER', getenv( 'WORDPRESS_DB_USER' ) ? getenv( 'WORDPRESS_DB_USER' ) : 'wptests' );
define( 'DB_PASSWORD', getenv( 'WORDPRESS_DB_PASSWORD' ) ? getenv( 'WORDPRESS_DB_PASSWORD' ) : 'wptests' );
define( 'DB_HOST', getenv( 'WORDPRESS_DB_HOST' ) ? getenv( 'WORDPRESS_DB_HOST' ) : 'db' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wptests_'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- wp-phpunit requires this global; the wptests_ prefix is the isolation.

define( 'WP_ENVIRONMENT_TYPE', 'local' );
define( 'WP_DEBUG', true );

define( 'WP_TESTS_DOMAIN', 'localhost' );
define( 'WP_TESTS_EMAIL', 'admin@localhost.test' );
define( 'WP_TESTS_TITLE', 'Camino del Dharma wp-phpunit' );
define( 'WP_PHP_BINARY', 'php' );

define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname( __DIR__, 2 ) . '/vendor/yoast/phpunit-polyfills' );
