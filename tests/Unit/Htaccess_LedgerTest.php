<?php
/**
 * Level 1: the WordPress `.htaccess` against docs/redirect-ledger.md
 * (WU-08B).
 *
 * Written RED-first. WordPress rewrites its own `# BEGIN WordPress`
 * block on every permalink save, so the ported legacy rules must live
 * outside it or they disappear silently. The ledger is the contract:
 * every legacy entry keeps its status, nothing redirects twice, and the
 * static-only rules (DirectoryIndex, index.html rewriting) must not
 * travel to a WordPress document root.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Routing cluster: the deployable WordPress `.htaccess`.
 */
final class Htaccess_LedgerTest extends TestCase {

	/**
	 * First-party rules must sit before `# BEGIN WordPress`, which
	 * WordPress owns and overwrites.
	 */
	public function test_first_party_rules_live_outside_the_wordpress_block() {
		$htaccess = $this->htaccess();

		$begin = strpos( $htaccess, '# BEGIN WordPress' );
		$end   = strpos( $htaccess, '# END WordPress' );

		$this->assertNotFalse( $begin );
		$this->assertNotFalse( $end );
		$this->assertGreaterThan( $begin, $end );

		$owned = substr( $htaccess, $begin, $end - $begin );
		$this->assertStringNotContainsString( 'caminodeldharma.org', $owned );
		$this->assertStringNotContainsString( 'sangha-refugio-hiperconexion', $owned );

		$this->assertLessThan( $begin, strpos( $htaccess, 'sangha-refugio-hiperconexion' ) );
	}

	/**
	 * Every legacy entry of the ledger keeps its documented status.
	 */
	public function test_ledger_legacy_redirects_are_ported() {
		$htaccess = $this->htaccess();

		$this->assertMatchesRegularExpression(
			'#\^sangha-refugio-hiperconexion/\?\$ /blog/sangha-refugio-hiperconexion \[R=301,L\]#',
			$htaccess
		);
		$this->assertMatchesRegularExpression(
			'#\^encuentro-nacional-2026/\?\$ /eventos/encuentro-nacional-2026 \[R=301,L\]#',
			$htaccess
		);
		$this->assertMatchesRegularExpression(
			'#\^pausa-profunda-cali/\?\$ /eventos/pausa-profunda-cali \[R=301,L\]#',
			$htaccess
		);
		$this->assertMatchesRegularExpression( '#\^prueba/\?\$ - \[G,L\]#', $htaccess );
		$this->assertMatchesRegularExpression( '#\^site\\\\\.webmanifest\$ - \[G,L\]#', $htaccess );
		$this->assertMatchesRegularExpression( '#\^category\(/\.\*\)\?\$ /blog \[R=301,L\]#', $htaccess );
		$this->assertStringContainsString( 'page_id=10', $htaccess );
		$this->assertStringContainsString( '/comunidad?', $htaccess );
	}

	/**
	 * The manual `sitemap.xml` is deprecated (ADR 0030): the old URL must
	 * resolve to the native sitemap, not 404.
	 */
	public function test_legacy_sitemap_points_at_the_native_one() {
		$this->assertMatchesRegularExpression(
			'#\^sitemap\\\\\.xml\$ /wp-sitemap\.xml \[R=301,L\]#',
			$this->htaccess()
		);
	}

	/**
	 * HTTPS, canonical host and the ADR 0008 trailing slash, in the order
	 * that avoids chains: a legacy path is rewritten to its canonical URL
	 * before the trailing-slash rule can touch it.
	 */
	public function test_canonical_rules_are_ordered_so_no_request_redirects_twice() {
		$htaccess = $this->htaccess();

		$https    = strpos( $htaccess, 'X-Forwarded-Proto' );
		$host     = strpos( $htaccess, 'HTTP_HOST' );
		$legacy   = strpos( $htaccess, 'sangha-refugio-hiperconexion' );
		$trailing = strpos( $htaccess, '^(.+)/$' );

		$this->assertNotFalse( $https );
		$this->assertGreaterThan( $https, $host );
		$this->assertGreaterThan( $host, $legacy );
		$this->assertGreaterThan( $legacy, $trailing );
	}

	/**
	 * Static-only serving rules must not travel: on WordPress they would
	 * shadow the front controller and manufacture soft 404s.
	 */
	public function test_static_only_rules_do_not_travel() {
		$htaccess = $this->htaccess();

		$this->assertStringNotContainsString( 'DirectoryIndex index.html', $htaccess );
		$this->assertStringNotContainsString( 'index.html$', $htaccess );
		$this->assertStringNotContainsString( 'ErrorDocument 404 /404.html', $htaccess );
	}

	/**
	 * ADR 0018: HSTS stays commented out until a cutover has been stable.
	 */
	public function test_hsts_is_not_activated() {
		$this->assertDoesNotMatchRegularExpression(
			'/^\s*Header always set Strict-Transport-Security/m',
			$this->htaccess()
		);
	}

	/**
	 * Every ledger row marked as ported must appear in the file: the test
	 * reads the ledger so a new legacy rule cannot be forgotten here.
	 */
	public function test_no_ledger_entry_is_left_behind() {
		$ledger   = (string) file_get_contents( dirname( __DIR__, 2 ) . '/docs/redirect-ledger.md' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repo files in a unit test without WordPress.
		$htaccess = $this->htaccess();

		$section = substr( $ledger, (int) strpos( $ledger, 'Ya en `.htaccess`' ) );
		$section = substr( $section, 0, (int) strpos( $section, '## Sitemap' ) );

		preg_match_all( '/^\| `([^`]+)`/m', $section, $matches );
		$this->assertNotEmpty( $matches[1] );

		// The only ledger entry that deliberately does not travel: it
		// stripped index.html from static URLs, and a WordPress document
		// root has no index.html to strip (see the file's header).
		$static_only = array( '*/index.html' );

		foreach ( array_diff( $matches[1], $static_only ) as $entry ) {
			$needle = ltrim( $entry, '/' );
			$needle = str_replace( array( '?page_id=10', 'site.webmanifest' ), array( 'page_id=10', 'site\.webmanifest' ), $needle );
			$needle = rtrim( $needle, '…' );
			if ( '' === $needle ) {
				continue;
			}
			$this->assertStringContainsString( $needle, $htaccess, 'ledger entry not ported: ' . $entry );
		}
	}

	/**
	 * The HTTPS conditions must be ANDed. With `[OR]`, a request that
	 * arrives secure through a TLS-terminating proxy (HTTPS is not `on`,
	 * X-Forwarded-Proto is `https`) is redirected to a URL that satisfies
	 * the same condition again: a redirect loop.
	 */
	public function test_https_conditions_cannot_loop_behind_a_proxy() {
		$htaccess = $this->htaccess();

		$https = strpos( $htaccess, '%{HTTPS} !=on' );
		$this->assertNotFalse( $https );

		$line = substr( $htaccess, $https, strpos( $htaccess, "\n", $https ) - $https );
		$this->assertStringNotContainsString( '[OR]', $line );
	}

	/**
	 * The deployable WordPress `.htaccess`.
	 */
	private function htaccess(): string {
		$path = dirname( __DIR__, 2 ) . '/wordpress/.htaccess';
		$this->assertFileExists( $path );

		return (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- repo files in a unit test without WordPress.
	}
}
