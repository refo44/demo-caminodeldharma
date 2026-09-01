<?php
/**
 * Level 1: the plugin main file is plain PHP includable without WordPress.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: plugin bootstrap contract.
 */
final class Plugin_BootstrapTest extends TestCase {

	/**
	 * Protects the version invariant: the runtime constant must not drift
	 * from the plugin header that WordPress shows to administrators.
	 */
	public function test_version_constant_matches_plugin_header() {
		$plugin_header = file_get_contents( $this->plugin_main_file() ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local repo file in a unit test without WordPress loaded.

		preg_match( '/^\s\*\sVersion:\s*(\S+)\s*$/m', $plugin_header, $header_version );

		$this->assertNotEmpty( $header_version, 'Plugin header must declare a Version.' );
		$this->assertSame( $header_version[1], CDD_CORE_VERSION );
	}

	/**
	 * Protects the bootstrap contract other code relies on to locate the
	 * plugin (asset URLs, includes): the constant points at the real file.
	 */
	public function test_plugin_file_constant_points_at_the_main_file() {
		$this->assertSame( realpath( $this->plugin_main_file() ), realpath( CDD_CORE_PLUGIN_FILE ) );
	}

	/**
	 * Path of the production main file relative to the repo root.
	 */
	private function plugin_main_file(): string {
		return dirname( __DIR__, 2 ) . '/wordpress/wp-content/plugins/camino-del-dharma-core/camino-del-dharma-core.php';
	}
}
