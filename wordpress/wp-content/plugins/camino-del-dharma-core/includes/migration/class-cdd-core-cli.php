<?php
/**
 * WP-CLI surface of the migration importer (ADR 0032 §8.2): `wp cdd-core
 * migrate <validate|plan|import|verify>` plus the owner-approved media
 * command `wp cdd-core seed` (OWN-009-img). Dry-run by default; writes
 * only with --apply; production additionally requires
 * --confirm-production and --backup-evidence.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Migration commands for camino-del-dharma-core.
 */
final class Cdd_Core_CLI {

	/**
	 * Registers the commands (only under WP-CLI).
	 */
	public static function register() {
		WP_CLI::add_command( 'cdd-core migrate', array( self::class, 'migrate' ) );
		WP_CLI::add_command( 'cdd-core seed', array( self::class, 'seed' ) );
		WP_CLI::add_command( 'cdd-core contact', array( self::class, 'contact' ) );
	}

	/**
	 * Runs a migrate subcommand.
	 *
	 * ## OPTIONS
	 *
	 * <action>
	 * : validate, plan, import, verify or convert.
	 *
	 * [--payload=<path>]
	 * : Path to migration/payload.json. Required for every action except
	 *   convert, which operates on the imported content; convert accepts
	 *   it to seed the published share templates on objects imported
	 *   before that copy travelled (WU-08A).
	 *
	 * [--source-root=<path>]
	 * : Repo root holding the payload's static tree (default: two levels
	 *   above the payload file).
	 *
	 * [--apply]
	 * : Actually write (import only). Without it, import is a dry run.
	 *
	 * [--confirm-production]
	 * : Required, together with --backup-evidence, to write in production.
	 *
	 * [--backup-evidence=<text>]
	 * : Reference to the verified backup taken before a production write.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args.
	 */
	public static function migrate( array $args, array $assoc_args ) {
		$action = $args[0] ?? '';

		if ( 'convert' === $action ) {
			$service = new Cdd_Core_Convert_Service(
				array(
					'confirm_production' => isset( $assoc_args['confirm-production'] ),
					'backup_evidence'    => (string) ( $assoc_args['backup-evidence'] ?? '' ),
					'payload'            => self::optional_payload( $assoc_args ),
				)
			);
			$report  = $service->run( isset( $assoc_args['apply'] ) );
			self::render_json( $report );
			if ( isset( $report['error'] ) ) {
				WP_CLI::error( $report['error'] );
			}
			WP_CLI::success( ! empty( $report['dry_run'] ) ? 'Dry run only — nothing written (use --apply).' : 'Conversion applied.' );

			return;
		}

		$importer = self::importer( $assoc_args );

		switch ( $action ) {
			case 'validate':
				$issues = $importer->validate();
				if ( empty( $issues ) ) {
					WP_CLI::success( 'Payload valid.' );

					return;
				}
				foreach ( $issues as $issue ) {
					WP_CLI::warning( $issue );
				}
				WP_CLI::error( 'Payload does not validate.' );
				break;

			case 'plan':
				self::render_json( $importer->plan() );
				break;

			case 'import':
				$report = $importer->import( isset( $assoc_args['apply'] ) );
				self::render_json( $report );
				if ( isset( $report['error'] ) ) {
					WP_CLI::error( $report['error'] );
				}
				WP_CLI::success( ! empty( $report['dry_run'] ) ? 'Dry run only — nothing written (use --apply).' : 'Import applied.' );
				break;

			case 'verify':
				$verification = $importer->verify();
				self::render_json( $verification );
				if ( ! empty( $verification['missing'] ) ) {
					WP_CLI::error( count( $verification['missing'] ) . ' objects missing.' );
				}
				WP_CLI::success( 'All payload objects present.' );
				break;

			default:
				WP_CLI::error( 'Unknown action. Use validate, plan, import or verify.' );
		}
	}

	/**
	 * Seeds the Media Library collection only (owner-approved name).
	 *
	 * ## OPTIONS
	 *
	 * --payload=<path>
	 * : Path to migration/payload.json.
	 *
	 * [--source-root=<path>]
	 * : Repo root holding the payload's static tree.
	 *
	 * [--apply]
	 * : Actually write. Without it, dry run.
	 *
	 * [--confirm-production]
	 * : Required, together with --backup-evidence, to write in production.
	 *
	 * [--backup-evidence=<text>]
	 * : Reference to the verified backup taken before a production write.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args.
	 */
	public static function seed( array $args, array $assoc_args ) {
		$report = self::importer( $assoc_args )->seed( isset( $assoc_args['apply'] ) );
		self::render_json( $report );
		if ( isset( $report['error'] ) ) {
			WP_CLI::error( $report['error'] );
		}
		WP_CLI::success( ! empty( $report['dry_run'] ) ? 'Dry run only — nothing written (use --apply).' : 'Seed applied.' );
	}

	/**
	 * Provisions the contact form into Contact Form 7 (WU-09).
	 *
	 * Create-missing-only and idempotent, like the importer. Refuses while
	 * CF7 is inactive, or while the /privacidad notice still describes a
	 * form that does not submit (ADR 0041 point 3): the notice is updated
	 * by `wp cdd-core migrate convert --apply` first.
	 *
	 * ## OPTIONS
	 *
	 * <action>
	 * : provision.
	 *
	 * [--apply]
	 * : Actually create the form. Without it this is a dry run.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args.
	 */
	public static function contact( array $args, array $assoc_args ) {
		if ( 'provision' !== ( $args[0] ?? '' ) ) {
			WP_CLI::error( 'Unknown action. Use: wp cdd-core contact provision [--apply]' );
		}

		$report = cdd_core_provision_contact_form( isset( $assoc_args['apply'] ) );
		self::render_json( $report );

		if ( '' !== $report['error'] ) {
			WP_CLI::error( $report['error'] );
		}

		WP_CLI::success(
			! empty( $report['dry_run'] )
				? 'Dry run only — nothing written (use --apply).'
				: sprintf( 'Contact form provisioned (id %d).', $report['form_id'] )
		);
	}

	/**
	 * The decoded payload when the caller passed one, else an empty array
	 * (convert works without it; the share seeding is then skipped).
	 *
	 * @param array $assoc_args Named args.
	 */
	private static function optional_payload( array $assoc_args ): array {
		$payload_path = (string) ( $assoc_args['payload'] ?? '' );
		if ( '' === $payload_path ) {
			return array();
		}
		if ( ! file_exists( $payload_path ) ) {
			WP_CLI::error( 'Unreadable --payload=<path>.' );
		}

		$payload = json_decode( (string) file_get_contents( $payload_path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local payload file in a CLI command.

		return is_array( $payload ) ? $payload : array();
	}

	/**
	 * Builds the importer from CLI arguments.
	 *
	 * @param array $assoc_args Named args.
	 */
	private static function importer( array $assoc_args ): Cdd_Core_Importer {
		$payload_path = (string) ( $assoc_args['payload'] ?? '' );
		if ( '' === $payload_path || ! file_exists( $payload_path ) ) {
			WP_CLI::error( 'Missing or unreadable --payload=<path>.' );
		}

		$payload = json_decode( (string) file_get_contents( $payload_path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local payload file in a CLI command.
		if ( ! is_array( $payload ) ) {
			WP_CLI::error( 'Payload is not valid JSON.' );
		}

		$source_root = (string) ( $assoc_args['source-root'] ?? dirname( $payload_path, 2 ) );

		return new Cdd_Core_Importer(
			$payload,
			$source_root,
			array(
				'confirm_production' => isset( $assoc_args['confirm-production'] ),
				'backup_evidence'    => (string) ( $assoc_args['backup-evidence'] ?? '' ),
			)
		);
	}

	/**
	 * Prints a report as JSON.
	 *
	 * @param array $data Report data.
	 */
	private static function render_json( array $data ) {
		WP_CLI::line( (string) wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
	}
}
