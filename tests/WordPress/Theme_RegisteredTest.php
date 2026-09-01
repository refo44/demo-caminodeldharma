<?php
/**
 * Level 2: WordPress itself recognizes camino-del-dharma as a valid block
 * theme (WU-04, ADR 0029).
 *
 * @package Camino_Del_Dharma_Core
 */

/**
 * Behavior cluster: theme recognized by a real WordPress process.
 */
final class Theme_RegisteredTest extends WP_UnitTestCase {

	/**
	 * Protects theme validity: WordPress finds the theme in the repo tree
	 * and reports no registration errors.
	 */
	public function test_theme_exists_without_errors() {
		$theme = $this->theme();

		$this->assertTrue( $theme->exists() );
		$this->assertFalse( $theme->errors() );
		$this->assertSame( 'camino-del-dharma', $theme->get( 'TextDomain' ) );
	}

	/**
	 * Protects ADR 0029: WordPress classifies the theme as a block theme
	 * (templates/index.html present), not a classic PHP theme.
	 */
	public function test_theme_is_a_block_theme() {
		$this->assertTrue( $this->theme()->is_block_theme() );
	}

	/**
	 * The theme under test, read from the repository tree mounted into the
	 * ephemeral harness — not from the WordPress default theme directory.
	 */
	private function theme(): WP_Theme {
		$themes_root = dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes';

		register_theme_directory( $themes_root );

		return wp_get_theme( 'camino-del-dharma', $themes_root );
	}
}
