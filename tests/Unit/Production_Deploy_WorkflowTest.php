<?php
/**
 * Level 1: the WordPress production deploy workflow contract (ADR 0048).
 *
 * Written RED-first. A component tag deploys that component to the canonical
 * production WordPress. The workflow is the only thing that may write there,
 * so its trigger, permissions, target and forbidden operations are pinned
 * here as text invariants.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Release cluster: `.github/workflows/deploy-production.yml`.
 */
final class Production_Deploy_WorkflowTest extends TestCase {

	/**
	 * Component tags start a run. A manual run may repeat an existing
	 * component tag. A branch, pull request, schedule or static `v*` tag does not.
	 */
	public function test_it_triggers_on_component_tags_or_a_tag_dispatch() {
		$on = $this->section( 'on' );

		$this->assertStringContainsString( "  push:\n    tags:\n      - 'theme-v*'\n      - 'plugin-v*'\n", $on );
		$this->assertStringContainsString( "  workflow_dispatch:\n    inputs:\n      tag:\n", $on );
		$this->assertStringContainsString( 'required: true', $on );
		$this->assertDoesNotMatchRegularExpression( '/branches|pull_request|schedule|repository_dispatch|workflow_run/', $on );
		$this->assertStringNotContainsString( "'v*'", $on );
	}

	/**
	 * The default token is read-only; no job widens it.
	 */
	public function test_permissions_are_read_only() {
		$workflow = $this->workflow();

		$this->assertStringContainsString( "\npermissions:\n  contents: read\n", $workflow );
		$this->assertDoesNotMatchRegularExpression( '/:\s*write\b/', $workflow );
		$this->assertStringNotContainsString( 'id-token', $workflow );
	}

	/**
	 * Only the deploy job holds the production environment.
	 */
	public function test_only_the_deploy_job_uses_the_production_environment() {
		$workflow = $this->workflow();

		$this->assertSame( 1, preg_match_all( '/^\s+environment:/m', $workflow ) );
		$this->assertStringContainsString( "    environment:\n      name: production\n", $workflow );

		$deploy = $this->job( 'deploy' );
		$this->assertStringContainsString( 'environment:', $deploy );
		foreach ( array( 'validate', 'php', 'css' ) as $job ) {
			$this->assertStringNotContainsString( 'environment:', $this->job( $job ), $job );
			$this->assertStringNotContainsString( 'secrets.', $this->job( $job ), $job );
		}
	}

	/**
	 * Deployments are serialized and never cancelled halfway through a sync.
	 */
	public function test_production_deploys_are_serialized_and_not_cancelled() {
		$deploy = $this->job( 'deploy' );

		$this->assertStringContainsString( "concurrency:\n      group: production-wordpress-deploy\n      cancel-in-progress: false\n", $deploy );
	}

	/**
	 * Nothing deploys until validation and both quality jobs pass.
	 */
	public function test_deploy_waits_for_validation_and_both_gates() {
		$this->assertStringContainsString( 'needs: [validate, php, css]', $this->job( 'deploy' ) );
		$this->assertStringContainsString( 'needs: validate', $this->job( 'php' ) );
		$this->assertStringContainsString( 'needs: validate', $this->job( 'css' ) );
	}

	/**
	 * The quality gate is the one in `test.yml`: same runtimes, same commands.
	 */
	public function test_quality_gate_mirrors_the_test_workflow() {
		$workflow = $this->workflow();
		$test     = $this->read( '.github/workflows/test.yml' );

		preg_match_all( '/^\s+- run: (.+)$/m', $test, $commands );
		$this->assertNotEmpty( $commands[1] );
		foreach ( $commands[1] as $command ) {
			$this->assertStringContainsString( '- run: ' . $command, $workflow, $command );
		}

		preg_match_all( '/^\s+(?:php|node)-version: .+$/m', $test, $runtimes );
		$this->assertCount( 2, $runtimes[0] );
		foreach ( $runtimes[0] as $runtime ) {
			$this->assertStringContainsString( trim( $runtime ), $workflow, $runtime );
		}
	}

	/**
	 * Quality jobs test the tagged commit. The deploy job keeps the workflow
	 * commit so a manual run of an older tag still uses the current guard.
	 */
	public function test_quality_jobs_use_the_tag_and_deploy_keeps_the_workflow_commit() {
		$workflow = $this->workflow();

		$this->assertSame( 4, substr_count( $workflow, 'actions/checkout@v5' ) );
		$this->assertStringContainsString( 'refs/tags/${TAG}^{commit}', $workflow );
		$this->assertStringContainsString( 'sha: ${{ steps.commit.outputs.sha }}', $this->job( 'validate' ) );
		$this->assertStringContainsString( 'tag: ${{ steps.commit.outputs.tag }}', $this->job( 'validate' ) );
		$this->assertStringContainsString( 'ref: ${{ github.sha }}', $this->job( 'validate' ) );
		$this->assertStringContainsString( 'ref: ${{ github.sha }}', $this->job( 'deploy' ) );
		foreach ( array( 'php', 'css' ) as $job ) {
			$this->assertStringContainsString( 'ref: ${{ needs.validate.outputs.sha }}', $this->job( $job ), $job );
		}
		$this->assertStringContainsString( 'TAGGED_SHA: ${{ needs.validate.outputs.sha }}', $this->job( 'deploy' ) );
		$this->assertStringContainsString( 'TAG: ${{ needs.validate.outputs.tag }}', $this->job( 'deploy' ) );
	}

	/**
	 * The version check reads the tagged tree, not whatever branch started a
	 * manual run.
	 */
	public function test_version_resolution_reads_an_archive_of_the_tag() {
		$validate = $this->job( 'validate' );

		$archive = strpos( $validate, 'git archive "${sha}"' );
		$resolve = strpos( $validate, 'tools/release/resolve-release.sh "${TAG}"' );

		$this->assertNotFalse( $archive );
		$this->assertNotFalse( $resolve );
		$this->assertLessThan( $resolve, $archive );
	}

	/**
	 * Main is fetched explicitly and the tagged commit must belong to it.
	 */
	public function test_main_ancestry_is_explicit_and_fail_closed() {
		$validate = $this->job( 'validate' );

		$fetch    = strpos( $validate, 'git fetch --no-tags origin main:refs/remotes/origin/main' );
		$verify   = strpos( $validate, 'git rev-parse --verify origin/main' );
		$ancestor = strpos( $validate, 'git merge-base --is-ancestor "${TAGGED_SHA}" origin/main' );

		$this->assertNotFalse( $fetch );
		$this->assertNotFalse( $verify );
		$this->assertNotFalse( $ancestor );
		$this->assertLessThan( $verify, $fetch );
		$this->assertLessThan( $ancestor, $verify );
		$this->assertStringContainsString( 'exit 1', substr( $validate, $ancestor, 200 ) );
	}

	/**
	 * Validation order: tag commit, version from that tree, ancestry, symlinks.
	 */
	public function test_validation_steps_run_in_the_documented_order() {
		$validate = $this->job( 'validate' );

		$order = array(
			'refs/tags/${TAG}^{commit}',
			'git archive "${sha}"',
			'tools/release/resolve-release.sh',
			'git merge-base --is-ancestor',
			'tools/release/check-no-symlinks.sh',
		);
		$last  = -1;
		foreach ( $order as $needle ) {
			$position = strpos( $validate, $needle );
			$this->assertNotFalse( $position, $needle );
			$this->assertGreaterThan( $last, $position, $needle );
			$last = $position;
		}
	}

	/**
	 * Symlinks are refused before any artifact, key or server exists.
	 */
	public function test_symlinks_are_refused_and_not_synced() {
		$workflow = $this->workflow();

		$this->assertStringContainsString( 'bash tools/release/check-no-symlinks.sh "${TAGGED_SHA}" "${SOURCE_DIR}"', $this->job( 'validate' ) );
		$this->assertStringNotContainsString( '--links', $workflow );
	}

	/**
	 * Validation is delegated to the tested scripts.
	 */
	public function test_it_uses_the_production_release_scripts() {
		$workflow = $this->workflow();

		$this->assertStringContainsString( 'tools/release/resolve-release.sh', $workflow );
		$this->assertStringContainsString( 'tools/release/check-production-target.sh', $workflow );
		$this->assertStringNotContainsString( 'check-staging-target.sh', $workflow );
		$this->assertStringNotContainsString( 'teal-woodpecker', $workflow );
		$this->assertStringNotContainsString( 'STAGING_', $workflow );
	}

	/**
	 * Host keys are verified, and rsync is limited to the component directory.
	 */
	public function test_transport_is_verified_and_component_scoped() {
		$workflow = $this->workflow();

		$this->assertStringContainsString( 'echo "  StrictHostKeyChecking yes"', $workflow );
		$this->assertDoesNotMatchRegularExpression( '/StrictHostKeyChecking[ =](no|off|accept-new)/i', $workflow );
		$this->assertStringNotContainsString( 'UserKnownHostsFile=/dev/null', $workflow );
		$this->assertSame( 1, preg_match_all( '/^\s+rsync /m', $workflow ) );
		$this->assertStringContainsString( '"${STAGE_DIR}/" "${SSH_USER}@${SSH_HOST}:${target_dir}/"', $workflow );
		$this->assertStringNotContainsString( 'wordpress/.htaccess', $workflow );
	}

	/**
	 * LiteSpeed cache is purged after the sync and before verification.
	 */
	public function test_the_page_cache_is_purged_after_the_sync_and_before_verification() {
		$workflow = $this->workflow();

		$sync   = strpos( $workflow, 'name: Sync the component to production' );
		$purge  = strpos( $workflow, 'name: Purge the production page cache' );
		$verify = strpos( $workflow, 'name: Post-deploy verification' );

		$this->assertNotFalse( $purge, 'The purge step is missing.' );
		$this->assertLessThan( $purge, $sync );
		$this->assertLessThan( $verify, $purge );
		$this->assertStringContainsString( 'litespeed-purge all', $workflow );
	}

	public function test_a_failed_purge_warns_and_does_not_fail_the_deploy() {
		$step = $this->step( 'Purge the production page cache' );

		$this->assertStringContainsString( '::warning::', $step );
		$this->assertStringNotContainsString( 'exit 1', $step );
	}

	/**
	 * A runner-side 403 warns. Any other HTTP failure still fails the deploy.
	 */
	public function test_a_runner_side_403_only_warns_in_the_smoke_probe() {
		$step = $this->step( 'Post-deploy verification' );

		$this->assertMatchesRegularExpression( '/"\$\{code\}" (=|-eq) "?403"?/', $step );
		$this->assertStringContainsString( '::warning::production answered HTTP 403', $step );
		$this->assertStringContainsString( '::error::production answered HTTP ${code}', $step );
	}

	/**
	 * The remote root must be the canonical production path. An empty guard
	 * cannot fall through to rsync.
	 */
	public function test_preflight_and_sync_require_the_canonical_production_root() {
		foreach ( array( 'Production preflight', 'Sync the component to production' ) as $step_name ) {
			$step = $this->step( $step_name );

			$this->assertStringContainsString( 'REQUIRED_REAL_ROOT', $step, $step_name );
			$this->assertStringContainsString( 'production root guard is empty', $step, $step_name );
		}
		$this->assertStringContainsString(
			'resolved root is not the canonical production document root',
			$this->step( 'Production preflight' )
		);
		$this->assertStringContainsString( 'https://caminodeldharma.org', $this->workflow() );
		$this->assertStringContainsString( '= "production"', $this->step( 'Production preflight' ) );
		$this->assertStringContainsString( '= "production"', $this->step( 'Post-deploy verification' ) );
	}

	/**
	 * A code deploy touches no content and creates no tag.
	 */
	public function test_forbidden_operations_are_absent() {
		$workflow = $this->workflow();

		$forbidden = array(
			'--confirm-production',
			'cdd-core migrate',
			'cdd-core seed',
			'cdd-core contact',
			'cdd-core demo',
			'wp db ',
			'wp import',
			'git tag',
			'git push',
			'gh release',
			'gh api',
		);
		foreach ( $forbidden as $needle ) {
			$this->assertStringNotContainsString( $needle, $workflow, $needle );
		}
	}

	/**
	 * Secrets and variables come from the production environment only.
	 */
	public function test_connection_settings_are_production_named() {
		preg_match_all( '/(?:secrets|vars)\.([A-Z0-9_]+)/', $this->workflow(), $names );

		$names = array_values( array_unique( $names[1] ) );
		sort( $names );

		$this->assertSame(
			array(
				'PRODUCTION_SSH_HOST',
				'PRODUCTION_SSH_KNOWN_HOSTS',
				'PRODUCTION_SSH_PORT',
				'PRODUCTION_SSH_PRIVATE_KEY',
				'PRODUCTION_SSH_USER',
				'PRODUCTION_WP_ROOT',
			),
			$names
		);
	}

	/**
	 * Body of one workflow step, from its `name:` line to the next step.
	 *
	 * @param string $name Step name.
	 * @return string
	 */
	private function step( $name ) {
		$workflow = $this->workflow();
		$start    = strpos( $workflow, 'name: ' . $name );
		if ( false === $start ) {
			return '';
		}
		$next = strpos( $workflow, "\n      - name:", $start );

		return substr( $workflow, $start, false === $next ? null : $next - $start );
	}

	/**
	 * Read the deploy workflow.
	 *
	 * @return string
	 */
	private function workflow() {
		return $this->read( '.github/workflows/deploy-production.yml' );
	}

	/**
	 * Read a repository file.
	 *
	 * @param string $relative Path from the repository root.
	 * @return string
	 */
	private function read( $relative ) {
		$path = dirname( __DIR__, 2 ) . '/' . $relative;
		$this->assertFileExists( $path );
		return (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local repo file in a unit test without WordPress loaded.
	}

	/**
	 * The body of a top-level key, up to the next top-level key.
	 *
	 * @param string $key Top-level YAML key.
	 * @return string
	 */
	private function section( $key ) {
		$this->assertSame( 1, preg_match( '/^' . preg_quote( $key, '/' ) . ':\n((?:(?: .*)?\n)*)/m', $this->workflow(), $match ), $key );
		return $match[1];
	}

	/**
	 * The body of one job under `jobs:`.
	 *
	 * @param string $name Job id.
	 * @return string
	 */
	private function job( $name ) {
		$this->assertSame( 1, preg_match( '/^  ' . preg_quote( $name, '/' ) . ':\n((?:(?:    .*)?\n)*)/m', $this->workflow(), $match ), $name );
		return $match[1];
	}
}
