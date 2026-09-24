<?php
/**
 * Level 1: the release symlink gate (ADR 0046).
 *
 * Written RED-first. `tools/release/check-no-symlinks.sh` reads Git tree
 * metadata of the exact tagged commit and refuses a component that holds a
 * Git symlink (mode 120000). It never touches the working tree, so a link is
 * never followed. Fixtures are real Git repositories in the OS temp dir.
 *
 * @package Camino_Del_Dharma_Core
 */

// phpcs:disable WordPress.WP.AlternativeFunctions, WordPress.PHP.DiscouragedPHPFunctions -- fixture repositories in the OS temp dir and a bash process under test, in a unit test without WordPress loaded.

use PHPUnit\Framework\TestCase;

/**
 * Release cluster: zero symlinks in the selected component at the tagged SHA.
 */
final class Release_SymlinkGateTest extends TestCase {

	private const THEME  = 'wordpress/wp-content/themes/camino-del-dharma';
	private const PLUGIN = 'wordpress/wp-content/plugins/camino-del-dharma-core';

	/**
	 * Fixture repository root, recreated per test.
	 *
	 * @var string
	 */
	private $repo = '';

	/**
	 * Create an empty repository holding both components as regular files.
	 */
	protected function setUp(): void {
		exec( 'command -v git', $unused, $status );
		if ( 0 !== $status ) {
			$this->markTestSkipped( 'git is not installed in this environment.' );
		}

		$this->repo = sys_get_temp_dir() . '/cdd-symlink-' . bin2hex( random_bytes( 6 ) );
		mkdir( $this->repo, 0777, true );
		$this->git( 'init -q' );
		foreach ( array( self::THEME . '/style.css', self::PLUGIN . '/camino-del-dharma-core.php' ) as $file ) {
			$this->put( $file, "x\n" );
		}
	}

	/**
	 * Remove the fixture repository.
	 */
	protected function tearDown(): void {
		if ( '' !== $this->repo && is_dir( $this->repo ) ) {
			exec( 'rm -rf ' . escapeshellarg( $this->repo ) );
		}
	}

	/**
	 * A theme without symlinks passes.
	 */
	public function test_theme_without_symlink_passes() {
		$sha = $this->commit();

		$this->assertSame( 0, $this->gate( $sha, self::THEME )['status'] );
	}

	/**
	 * A plugin without symlinks passes.
	 */
	public function test_plugin_without_symlink_passes() {
		$sha = $this->commit();

		$this->assertSame( 0, $this->gate( $sha, self::PLUGIN )['status'] );
	}

	/**
	 * A symlink inside the theme fails, and the offender is named.
	 */
	public function test_theme_with_a_symlink_fails() {
		symlink( 'style.css', $this->repo . '/' . self::THEME . '/link.css' );
		$sha = $this->commit();

		$result = $this->gate( $sha, self::THEME );

		$this->assertNotSame( 0, $result['status'] );
		$this->assertStringContainsString( 'link.css', $result['stderr'] );
	}

	/**
	 * A symlink inside the plugin fails, even in a nested directory.
	 */
	public function test_plugin_with_a_nested_symlink_fails() {
		mkdir( $this->repo . '/' . self::PLUGIN . '/includes', 0777, true );
		symlink( '../../../../../../README', $this->repo . '/' . self::PLUGIN . '/includes/escape.php' );
		$sha = $this->commit();

		$this->assertNotSame( 0, $this->gate( $sha, self::PLUGIN )['status'] );
	}

	/**
	 * A symlink elsewhere in the repository is irrelevant to the selected
	 * component, and a sibling component's link does not fail this one.
	 */
	public function test_symlink_outside_the_selected_component_is_ignored() {
		symlink( 'style.css', $this->repo . '/' . self::PLUGIN . '/link.php' );
		symlink( 'README', $this->repo . '/root-link' );
		$sha = $this->commit();

		$this->assertSame( 0, $this->gate( $sha, self::THEME )['status'] );
		$this->assertNotSame( 0, $this->gate( $sha, self::PLUGIN )['status'] );
	}

	/**
	 * The gate reads the tagged commit, not the working tree: a link added
	 * later, and never committed, does not fail; a committed one cannot be
	 * hidden by deleting it from the working tree.
	 */
	public function test_gate_reads_the_commit_not_the_working_tree() {
		$clean = $this->commit();
		symlink( 'style.css', $this->repo . '/' . self::THEME . '/late.css' );
		$this->assertSame( 0, $this->gate( $clean, self::THEME )['status'] );

		$this->git( 'add -A' );
		$this->git( '-c user.name=t -c user.email=t@t commit -q -m link' );
		$linked = trim( $this->git( 'rev-parse HEAD' ) );
		unlink( $this->repo . '/' . self::THEME . '/late.css' );

		$this->assertNotSame( 0, $this->gate( $linked, self::THEME )['status'] );
	}

	/**
	 * Fail closed on a component that is absent, an unknown commit, or bad usage.
	 */
	public function test_it_fails_closed_on_missing_component_unknown_commit_and_bad_usage() {
		$sha = $this->commit();

		$this->assertNotSame( 0, $this->gate( $sha, 'wordpress/wp-content/themes/absent' )['status'] );
		$this->assertNotSame( 0, $this->gate( str_repeat( '0', 40 ), self::THEME )['status'] );
		$this->assertNotSame( 0, $this->gate( '', self::THEME )['status'] );
		$this->assertNotSame( 0, $this->gate( $sha, '' )['status'] );
	}

	/**
	 * Run the gate against the fixture repository.
	 *
	 * @param string $sha    Commit to inspect.
	 * @param string $source Component directory.
	 * @return array{status:int,stdout:string,stderr:string}
	 */
	private function gate( $sha, $source ) {
		$script  = dirname( __DIR__, 2 ) . '/tools/release/check-no-symlinks.sh';
		$process = proc_open(
			array( 'bash', $script, $sha, $source ),
			array(
				1 => array( 'pipe', 'w' ),
				2 => array( 'pipe', 'w' ),
			),
			$pipes,
			$this->repo
		);
		$stdout  = stream_get_contents( $pipes[1] );
		$stderr  = stream_get_contents( $pipes[2] );
		return array(
			'status' => proc_close( $process ),
			'stdout' => (string) $stdout,
			'stderr' => (string) $stderr,
		);
	}

	/**
	 * Commit everything and return the commit SHA.
	 *
	 * @return string
	 */
	private function commit() {
		$this->put( 'README', "r\n" );
		$this->git( 'add -A' );
		$this->git( '-c user.name=t -c user.email=t@t commit -q -m fixture' );
		return trim( $this->git( 'rev-parse HEAD' ) );
	}

	/**
	 * Write a fixture file.
	 *
	 * @param string $relative Path from the repository root.
	 * @param string $content  File content.
	 */
	private function put( $relative, $content ) {
		$path = $this->repo . '/' . $relative;
		if ( ! is_dir( dirname( $path ) ) ) {
			mkdir( dirname( $path ), 0777, true );
		}
		file_put_contents( $path, $content );
	}

	/**
	 * Run git in the fixture repository.
	 *
	 * @param string $args Arguments.
	 * @return string
	 */
	private function git( $args ) {
		return (string) shell_exec( 'git -C ' . escapeshellarg( $this->repo ) . ' ' . $args . ' 2>&1' );
	}
}
