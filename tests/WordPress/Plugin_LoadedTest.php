<?php
/**
 * Level 2: the plugin bootstrap loads inside real WordPress (wp-phpunit).
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * Behavior cluster: plugin loaded in a real WordPress process.
 */
final class Plugin_LoadedTest extends WP_UnitTestCase {

	/**
	 * Protects the integration contract: the plugin main file loads inside
	 * WordPress (via muplugins_loaded in the suite bootstrap) without a
	 * fatal, leaving its bootstrap constants defined.
	 */
	public function test_plugin_bootstrap_is_loaded_inside_wordpress() {
		$this->assertTrue( defined( 'CDD_CORE_VERSION' ) );
		$this->assertTrue( defined( 'CDD_CORE_PLUGIN_FILE' ) );
	}

	/**
	 * Protects the safety invariant of the harness itself: the suite must
	 * only ever run against a disposable local environment (ADR 0033).
	 */
	public function test_suite_runs_in_a_local_environment() {
		$this->assertSame( 'local', wp_get_environment_type() );
	}
}
