<?php
/**
 * The WordPress installer's demo content never reaches visitors
 * (D-02 / OWN-024, issue #10).
 *
 * Two seams, deliberately different in force:
 *
 * - taking over an install (activation, or the versioned upgrade pass)
 *   **unpublishes** whatever demo content the installer left behind. It is
 *   reversible, it deletes nothing, and it means a skipped provisioning
 *   step can no longer publish «Hello world!» on staging or production;
 * - `wp cdd-core demo purge --apply` **removes** it. Deleting is an
 *   explicit operator action, dry-run by default and guarded in production,
 *   never a side effect of activation (ADR 0033).
 *
 * Neither seam ever touches imported content: an object carrying the
 * importer's source key is production content by definition.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The demo posts the installer left in this site, as WP_Post objects.
 */
function cdd_core_installer_demo_posts(): array {
	$policy = new Cdd_Core_Installer_Demo_Content();

	$candidates = get_posts(
		array(
			'post_type'        => $policy->post_types(),
			'post_status'      => Cdd_Core_Installer_Demo_Content::INSTALLER_STATUSES,
			'post_name__in'    => $policy->default_slugs(),
			'numberposts'      => -1,
			'orderby'          => 'ID',
			'order'            => 'ASC',
			'suppress_filters' => false,
		)
	);

	$demo = array();
	foreach ( $candidates as $post ) {
		$descriptor = array(
			'post_type'   => $post->post_type,
			'slug'        => $post->post_name,
			'status'      => $post->post_status,
			'is_imported' => '' !== (string) get_post_meta( $post->ID, Cdd_Core_Importer::SOURCE_KEY_META, true ),
		);

		if ( $policy->is_installer_demo( $descriptor ) ) {
			$demo[] = $post;
		}
	}

	return $demo;
}

/**
 * Unpublishes the installer's demo content. Runs when this plugin takes
 * over an install (activation) and on the versioned upgrade pass, so an
 * environment provisioned without the manual clean-up step still cannot
 * list demo content publicly. Returns the ids it demoted.
 */
function cdd_core_unpublish_installer_demo_content(): array {
	$unpublished = array();

	foreach ( cdd_core_installer_demo_posts() as $post ) {
		if ( 'publish' !== $post->post_status ) {
			continue;
		}

		wp_update_post(
			array(
				'ID'          => $post->ID,
				'post_status' => 'draft',
			)
		);
		$unpublished[] = $post->ID;
	}

	return $unpublished;
}

/**
 * Removes the installer's demo content for good.
 *
 * Dry-run by default and production-guarded, the same contract as the
 * importer (ADR 0033). Only objects the policy recognises as installer
 * demos are deleted, so this is safe on a site that already holds imported
 * production content — unlike deleting posts 1, 2 and 3 by hand.
 *
 * @param bool  $apply   Actually delete; false = dry run.
 * @param array $options environment / confirm_production / backup_evidence.
 */
function cdd_core_purge_installer_demo_content( bool $apply, array $options = array() ): array {
	$options = array_merge(
		array(
			'environment'        => wp_get_environment_type(),
			'confirm_production' => false,
			'backup_evidence'    => '',
		),
		$options
	);

	$report = array(
		'dry_run' => ! $apply,
		'found'   => array(),
		'removed' => array(),
		'error'   => '',
	);

	$demo_posts = cdd_core_installer_demo_posts();
	foreach ( $demo_posts as $post ) {
		$report['found'][] = array(
			'id'        => $post->ID,
			'post_type' => $post->post_type,
			'slug'      => $post->post_name,
			'status'    => $post->post_status,
			'title'     => $post->post_title,
		);
	}

	if ( ! $apply ) {
		return $report;
	}

	if ( 'production' === $options['environment']
		&& ! ( $options['confirm_production'] && '' !== trim( (string) $options['backup_evidence'] ) ) ) {
		$report['error'] = 'Refusing to delete in a production environment without --confirm-production and --backup-evidence (ADR 0033).';

		return $report;
	}

	$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );

	foreach ( $demo_posts as $post ) {
		if ( wp_delete_post( $post->ID, true ) ) {
			$report['removed'][] = $post->ID;
		}
	}

	// WordPress points this option at the installer's draft; the reference
	// must not dangle once that draft is gone. Repointing it at the real
	// /privacidad Page is a separate editorial decision (ADR 0039).
	if ( in_array( $privacy_page_id, $report['removed'], true ) ) {
		update_option( 'wp_page_for_privacy_policy', 0 );
	}

	return $report;
}
