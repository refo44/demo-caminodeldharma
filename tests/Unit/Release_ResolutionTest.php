<?php
/**
 * Level 1: release tag resolution and staging target guard (ADR 0046).
 *
 * Written RED-first. `tools/release/resolve-release.sh` decides, from a tag
 * name and a checked-out tree alone, which single component a tag may
 * deploy and whether the tag's SemVer equals what that component declares.
 * `tools/release/check-staging-target.sh` refuses any WordPress root that is
 * not the staging contract. Both are pure: no network, no git, no secrets,
 * and they fail closed with a non-zero exit.
 *
 * @package Camino_Del_Dharma_Core
 */

// phpcs:disable WordPress.WP.AlternativeFunctions, WordPress.PHP.DiscouragedPHPFunctions -- fixture trees in the OS temp dir and a bash process under test, in a unit test without WordPress loaded.

use PHPUnit\Framework\TestCase;

/**
 * Release cluster: tag namespace, SemVer, component version, staging root.
 */
final class Release_ResolutionTest extends TestCase {

	private const STAGING_ROOT = '/home/u548735796/domains/teal-woodpecker-284165.hostingersite.com/public_html';

	private const PRODUCTION_ROOT = '/home/u548735796/domains/caminodeldharma.org/public_html';

	/**
	 * Fixture tree root, recreated per test.
	 *
	 * @var string
	 */
	private $tree = '';

	/**
	 * Build a minimal tree holding both version declarations.
	 */
	protected function setUp(): void {
		$this->tree = sys_get_temp_dir() . '/cdd-release-' . bin2hex( random_bytes( 6 ) );
		$this->write_tree( '0.5.3', '0.7.5', '0.7.5' );
	}

	/**
	 * Remove the fixture tree.
	 */
	protected function tearDown(): void {
		$this->remove_dir( $this->tree );
	}

	/**
	 * A theme tag resolves to the theme directory and nothing else.
	 */
	public function test_theme_tag_selects_only_the_theme() {
		$result = $this->resolve( 'theme-v0.5.3' );

		$this->assertSame( 0, $result['status'] );
		$this->assertSame(
			array(
				'COMPONENT'  => 'theme',
				'VERSION'    => '0.5.3',
				'SOURCE_DIR' => 'wordpress/wp-content/themes/camino-del-dharma',
				'TARGET_REL' => 'wp-content/themes/camino-del-dharma',
				'SLUG'       => 'camino-del-dharma',
			),
			$result['fields']
		);
	}

	/**
	 * A plugin tag resolves to the plugin directory and nothing else.
	 */
	public function test_plugin_tag_selects_only_the_plugin() {
		$result = $this->resolve( 'plugin-v0.7.5' );

		$this->assertSame( 0, $result['status'] );
		$this->assertSame(
			array(
				'COMPONENT'  => 'plugin',
				'VERSION'    => '0.7.5',
				'SOURCE_DIR' => 'wordpress/wp-content/plugins/camino-del-dharma-core',
				'TARGET_REL' => 'wp-content/plugins/camino-del-dharma-core',
				'SLUG'       => 'camino-del-dharma-core',
			),
			$result['fields']
		);
	}

	/**
	 * The two release lines are independent: a theme tag ignores the plugin
	 * version and vice versa.
	 */
	public function test_theme_and_plugin_versions_are_independent() {
		$this->assertSame( 0, $this->resolve( 'theme-v0.5.3' )['status'] );
		$this->assertSame( 0, $this->resolve( 'plugin-v0.7.5' )['status'] );
		$this->assertNotSame( 0, $this->resolve( 'theme-v0.7.5' )['status'] );
		$this->assertNotSame( 0, $this->resolve( 'plugin-v0.5.3' )['status'] );
	}

	/**
	 * Only `theme-v<SemVer>` and `plugin-v<SemVer>` are release tags.
	 *
	 * @dataProvider rejected_tags
	 *
	 * @param string $tag Tag name that must be refused.
	 */
	public function test_rejected_tags_fail_closed( $tag ) {
		$result = $this->resolve( $tag );

		$this->assertNotSame( 0, $result['status'], $tag );
		$this->assertSame( array(), $result['fields'], $tag );
	}

	/**
	 * Tag names outside the contract.
	 *
	 * @return array<string, array<int, string>>
	 */
	public function rejected_tags() {
		return array(
			'static line'        => array( 'v1.0.36' ),
			'static line v0.5.3' => array( 'v0.5.3' ),
			'non-release tag'    => array( 'fase3-pre-reorg-v1.0.35' ),
			'no separator'       => array( 'themev0.5.3' ),
			'wrong case'         => array( 'Theme-v0.5.3' ),
			'no v'               => array( 'theme-0.5.3' ),
			'text as version'    => array( 'theme-vfoo' ),
			'two components'     => array( 'theme-v0.5' ),
			'one component'      => array( 'plugin-v1' ),
			'four components'    => array( 'theme-v0.5.3.1' ),
			'leading zero major' => array( 'theme-v00.5.3' ),
			'leading zero minor' => array( 'theme-v0.05.3' ),
			'leading zero patch' => array( 'theme-v0.5.03' ),
			'prerelease'         => array( 'theme-v0.5.3-beta.1' ),
			'prerelease plugin'  => array( 'plugin-v0.7.5-rc.1' ),
			'build metadata'     => array( 'theme-v0.5.3+build.7' ),
			'trailing text'      => array( 'theme-v0.5.3x' ),
			'trailing newline'   => array( "theme-v0.5.3\n" ),
			'second line'        => array( "theme-v0.5.3\nplugin-v0.7.5" ),
			'trailing space'     => array( 'theme-v0.5.3 ' ),
			'path traversal'     => array( 'theme-v0.5.3/../x' ),
			'both components'    => array( 'theme-plugin-v0.5.3' ),
			'nested namespace'   => array( 'plugin-theme-v0.5.3' ),
			'empty'              => array( '' ),
			'refs prefix'        => array( 'refs/tags/theme-v0.5.3' ),
		);
	}

	/**
	 * The tag SemVer must equal the theme `style.css` Version.
	 */
	public function test_theme_tag_must_match_the_style_css_version() {
		$this->write_tree( '0.5.4', '0.7.5', '0.7.5' );

		$this->assertNotSame( 0, $this->resolve( 'theme-v0.5.3' )['status'] );
		$this->assertSame( 0, $this->resolve( 'theme-v0.5.4' )['status'] );
	}

	/**
	 * The tag SemVer must equal the plugin header Version.
	 */
	public function test_plugin_tag_must_match_the_header_version() {
		$this->write_tree( '0.5.3', '0.7.6', '0.7.6' );

		$this->assertNotSame( 0, $this->resolve( 'plugin-v0.7.5' )['status'] );
		$this->assertSame( 0, $this->resolve( 'plugin-v0.7.6' )['status'] );
	}

	/**
	 * The plugin header and `CDD_CORE_VERSION` are both normative: if they
	 * disagree the release is ambiguous and nothing deploys, whichever the
	 * tag says.
	 */
	public function test_plugin_header_and_constant_must_agree() {
		$this->write_tree( '0.5.3', '0.7.5', '0.7.4' );

		$this->assertNotSame( 0, $this->resolve( 'plugin-v0.7.5' )['status'] );
		$this->assertNotSame( 0, $this->resolve( 'plugin-v0.7.4' )['status'] );
	}

	/**
	 * A theme release does not read the plugin, so a broken plugin
	 * declaration cannot block or alter it.
	 */
	public function test_theme_release_does_not_depend_on_the_plugin_declarations() {
		$this->write_tree( '0.5.3', '0.7.5', '0.7.4' );

		$this->assertSame( 0, $this->resolve( 'theme-v0.5.3' )['status'] );
	}

	/**
	 * A tree without the component's declaration fails closed.
	 */
	public function test_missing_declarations_fail_closed() {
		$this->remove_dir( $this->tree . '/wordpress/wp-content/themes' );

		$this->assertNotSame( 0, $this->resolve( 'theme-v0.5.3' )['status'] );
		$this->assertSame( 0, $this->resolve( 'plugin-v0.7.5' )['status'] );
	}

	/**
	 * A declared version that is not strict SemVer never matches a tag.
	 */
	public function test_non_semver_component_version_fails_closed() {
		$this->write_tree( '0.5', '0.7.5', '0.7.5' );

		$this->assertNotSame( 0, $this->resolve( 'theme-v0.5.0' )['status'] );
		$this->assertNotSame( 0, $this->resolve( 'theme-v0.5' )['status'] );
	}

	/**
	 * Two `Version:` headers are ambiguous.
	 */
	public function test_duplicate_version_header_fails_closed() {
		$style = $this->tree . '/wordpress/wp-content/themes/camino-del-dharma/style.css';
		file_put_contents( $style, "/*\nTheme Name: X\nVersion: 0.5.3\nVersion: 9.9.9\n*/\n" );

		$this->assertNotSame( 0, $this->resolve( 'theme-v0.5.3' )['status'] );
	}

	/**
	 * Both arguments are required.
	 */
	public function test_missing_arguments_fail_closed() {
		$this->assertNotSame( 0, $this->run_script( 'resolve-release.sh', array() )['status'] );
		$this->assertNotSame( 0, $this->run_script( 'resolve-release.sh', array( 'theme-v0.5.3' ) )['status'] );
	}

	/**
	 * The two staging targets resolve inside the staging root.
	 */
	public function test_staging_target_accepts_only_the_contract_root_and_component_dirs() {
		$theme  = $this->check_target( self::STAGING_ROOT, 'wp-content/themes/camino-del-dharma' );
		$plugin = $this->check_target( self::STAGING_ROOT, 'wp-content/plugins/camino-del-dharma-core' );

		$this->assertSame( 0, $theme['status'] );
		$this->assertSame(
			array(
				'TARGET_DIR'          => self::STAGING_ROOT . '/wp-content/themes/camino-del-dharma',
				'FORBIDDEN_REAL_ROOT' => self::PRODUCTION_ROOT,
			),
			$theme['fields']
		);
		$this->assertSame( 0, $plugin['status'] );
		$this->assertSame(
			array(
				'TARGET_DIR'          => self::STAGING_ROOT . '/wp-content/plugins/camino-del-dharma-core',
				'FORBIDDEN_REAL_ROOT' => self::PRODUCTION_ROOT,
			),
			$plugin['fields']
		);
	}

	/**
	 * Any root other than the staging contract fails, above all production.
	 *
	 * @dataProvider rejected_roots
	 *
	 * @param string $root Configured root that must be refused.
	 */
	public function test_staging_target_rejects_every_other_root( $root ) {
		$result = $this->check_target( $root, 'wp-content/themes/camino-del-dharma' );

		$this->assertNotSame( 0, $result['status'], $root );
		$this->assertSame( array(), $result['fields'], $root );
	}

	/**
	 * Roots outside the staging contract.
	 *
	 * @return array<string, array<int, string>>
	 */
	public function rejected_roots() {
		return array(
			'empty'                   => array( '' ),
			'production'              => array( self::PRODUCTION_ROOT ),
			'production trailing'     => array( self::PRODUCTION_ROOT . '/' ),
			'staging trailing slash'  => array( self::STAGING_ROOT . '/' ),
			'staging parent'          => array( dirname( self::STAGING_ROOT ) ),
			'staging subdirectory'    => array( self::STAGING_ROOT . '/wp-content' ),
			'traversal to production' => array( self::STAGING_ROOT . '/../../caminodeldharma.org/public_html' ),
			'relative'                => array( 'public_html' ),
			'home'                    => array( '/home/u548735796' ),
			'filesystem root'         => array( '/' ),
		);
	}

	/**
	 * Only the two whitelisted component directories may be targeted, so a
	 * `--delete` can never reach the WordPress root.
	 *
	 * @dataProvider rejected_targets
	 *
	 * @param string $target Component target that must be refused.
	 */
	public function test_staging_target_rejects_every_other_component_dir( $target ) {
		$result = $this->check_target( self::STAGING_ROOT, $target );

		$this->assertNotSame( 0, $result['status'], $target );
		$this->assertSame( array(), $result['fields'], $target );
	}

	/**
	 * Targets outside the whitelist.
	 *
	 * @return array<string, array<int, string>>
	 */
	public function rejected_targets() {
		return array(
			'empty'             => array( '' ),
			'document root'     => array( '.' ),
			'slash'             => array( '/' ),
			'wp-content'        => array( 'wp-content' ),
			'themes directory'  => array( 'wp-content/themes' ),
			'plugins directory' => array( 'wp-content/plugins' ),
			'other theme'       => array( 'wp-content/themes/twentytwentyfive' ),
			'other plugin'      => array( 'wp-content/plugins/contact-form-7' ),
			'uploads'           => array( 'wp-content/uploads' ),
			'absolute'          => array( '/wp-content/themes/camino-del-dharma' ),
			'traversal'         => array( 'wp-content/themes/camino-del-dharma/../../..' ),
			'trailing slash'    => array( 'wp-content/themes/camino-del-dharma/' ),
			'production path'   => array( self::PRODUCTION_ROOT . '/wp-content/themes/camino-del-dharma' ),
			'htaccess'          => array( '.htaccess' ),
		);
	}

	/**
	 * Run the resolver against the fixture tree.
	 *
	 * @param string $tag Tag name.
	 * @return array{status:int, fields:array<string,string>}
	 */
	private function resolve( $tag ) {
		return $this->run_script( 'resolve-release.sh', array( $tag, $this->tree ) );
	}

	/**
	 * Run the staging target guard.
	 *
	 * @param string $root   Configured WordPress root.
	 * @param string $target Component path relative to the root.
	 * @return array{status:int, fields:array<string,string>}
	 */
	private function check_target( $root, $target ) {
		return $this->run_script( 'check-staging-target.sh', array( $root, $target ) );
	}

	/**
	 * Run a release script and parse its KEY=VALUE stdout.
	 *
	 * @param string   $script Script file name under tools/release.
	 * @param string[] $args   Positional arguments.
	 * @return array{status:int, fields:array<string,string>}
	 */
	private function run_script( $script, array $args ) {
		$path    = dirname( __DIR__, 2 ) . '/tools/release/' . $script;
		$command = 'bash ' . escapeshellarg( $path );
		foreach ( $args as $arg ) {
			$command .= ' ' . escapeshellarg( $arg );
		}

		$process = proc_open(
			$command,
			array(
				1 => array( 'pipe', 'w' ),
				2 => array( 'pipe', 'w' ),
			),
			$pipes
		);
		$stdout  = stream_get_contents( $pipes[1] );
		stream_get_contents( $pipes[2] );
		fclose( $pipes[1] );
		fclose( $pipes[2] );
		$status = proc_close( $process );

		$fields = array();
		foreach ( explode( "\n", trim( (string) $stdout ) ) as $line ) {
			if ( false === strpos( $line, '=' ) ) {
				continue;
			}
			list( $key, $value ) = explode( '=', $line, 2 );
			$fields[ $key ]      = $value;
		}

		// A failing script must print no partial result for a caller to use.
		return array(
			'status' => $status,
			'fields' => 0 === $status ? $fields : array(),
		);
	}

	/**
	 * Write both component declarations into the fixture tree.
	 *
	 * @param string $theme    Theme `Version:` header.
	 * @param string $plugin   Plugin `Version:` header.
	 * @param string $constant Plugin `CDD_CORE_VERSION` value.
	 */
	private function write_tree( $theme, $plugin, $constant ) {
		$theme_dir  = $this->tree . '/wordpress/wp-content/themes/camino-del-dharma';
		$plugin_dir = $this->tree . '/wordpress/wp-content/plugins/camino-del-dharma-core';
		is_dir( $theme_dir ) || mkdir( $theme_dir, 0777, true );
		is_dir( $plugin_dir ) || mkdir( $plugin_dir, 0777, true );

		file_put_contents(
			$theme_dir . '/style.css',
			"/*\nTheme Name: Camino del Dharma\nVersion: {$theme}\nRequires PHP: 8.3\n*/\n"
		);
		file_put_contents(
			$plugin_dir . '/camino-del-dharma-core.php',
			"<?php\n/**\n * Plugin Name: Camino del Dharma Core\n * Version: {$plugin}\n */\n\ndefine( 'CDD_CORE_VERSION', '{$constant}' );\n"
		);
	}

	/**
	 * Recursively delete a directory if it exists.
	 *
	 * @param string $dir Directory to remove.
	 */
	private function remove_dir( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		foreach ( scandir( $dir ) as $entry ) {
			if ( '.' === $entry || '..' === $entry ) {
				continue;
			}
			$path = $dir . '/' . $entry;
			is_dir( $path ) ? $this->remove_dir( $path ) : unlink( $path );
		}
		rmdir( $dir );
	}
}

// phpcs:enable WordPress.WP.AlternativeFunctions, WordPress.PHP.DiscouragedPHPFunctions
