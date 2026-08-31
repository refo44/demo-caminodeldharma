<?php
/**
 * Migration importer (ADR 0032 §8.2, ADR 0033): validate / plan / import /
 * verify over a versioned payload. Dry-run by default, explicit apply,
 * idempotent, create-missing-only, stable source keys and hashes, wp-admin
 * edits never overwritten, nothing ever deleted, production guarded.
 *
 * @package Camino_Del_Dharma_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Imports the migration payload into WordPress.
 */
final class Cdd_Core_Importer {

	const SOURCE_KEY_META  = '_cdd_source_key';
	const SOURCE_HASH_META = '_cdd_source_hash';

	/**
	 * Collections imported as WordPress objects (video_embeds travel
	 * inside page content; gallery_images are term relations on media).
	 */
	const OBJECT_COLLECTIONS = array( 'blog_authors', 'media', 'gallery_albums', 'events', 'posts', 'pages' );

	/**
	 * Payload array.
	 *
	 * @var array
	 */
	private $payload;

	/**
	 * Repo root holding the payload's static tree.
	 *
	 * @var string
	 */
	private $source_root;

	/**
	 * Importer options (environment, confirm_production, backup_evidence).
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Source path => attachment URL map built during the media step.
	 *
	 * @var array
	 */
	private $media_urls = array();

	/**
	 * Constructor.
	 *
	 * @param array  $payload     Decoded payload.
	 * @param string $source_root Repo root (parent of the payload's static/).
	 * @param array  $options     environment / confirm_production / backup_evidence.
	 */
	public function __construct( array $payload, string $source_root, array $options = array() ) {
		$this->payload     = $payload;
		$this->source_root = rtrim( $source_root, '/' );
		$this->options     = array_merge(
			array(
				'environment'        => wp_get_environment_type(),
				'confirm_production' => false,
				'backup_evidence'    => '',
			),
			$options
		);
	}

	/**
	 * Validates the payload against its schema and the source tree.
	 * Returns a list of human-readable issues; empty means valid.
	 */
	public function validate(): array {
		$issues = array();

		if ( Cdd_Core_Payload_Builder::SCHEMA !== ( $this->payload['schema'] ?? '' ) ) {
			$issues[] = 'Unknown payload schema: ' . (string) ( $this->payload['schema'] ?? '(none)' );
		}
		foreach ( array( 'version', 'commit', 'root' ) as $field ) {
			if ( '' === (string) ( $this->payload['source'][ $field ] ?? '' ) ) {
				$issues[] = "Payload source.{$field} is missing.";
			}
		}

		foreach ( (array) ( $this->payload['counts'] ?? array() ) as $collection => $count ) {
			$actual = count( (array) ( $this->payload[ $collection ] ?? array() ) );
			if ( $actual !== (int) $count ) {
				$issues[] = "Collection {$collection} declares {$count} objects but carries {$actual}.";
			}
		}

		foreach ( $this->collection( 'media' ) as $media ) {
			if ( ! file_exists( $this->source_file( $media['file'] ) ) ) {
				$issues[] = 'Media file missing on disk: ' . $media['file'];
			}
		}

		$author_slugs = array_column( $this->collection( 'blog_authors' ), 'slug' );
		foreach ( $this->collection( 'posts' ) as $post ) {
			foreach ( (array) ( $post['authors'] ?? array() ) as $author_slug ) {
				if ( ! in_array( $author_slug, $author_slugs, true ) ) {
					$issues[] = "Post {$post['slug']} cites unknown author {$author_slug}.";
				}
			}
		}

		foreach ( array( 'pages', 'events', 'posts', 'blog_authors' ) as $collection ) {
			foreach ( $this->collection( $collection ) as $object ) {
				if ( '' === (string) ( $object['_source_key'] ?? '' ) || '' === (string) ( $object['_source_hash'] ?? '' ) ) {
					$issues[] = "Object without source key/hash in {$collection}.";
				}
			}
		}

		return $issues;
	}

	/**
	 * The create/skip plan per collection (what apply would do).
	 */
	public function plan(): array {
		$plan = array();
		foreach ( self::OBJECT_COLLECTIONS as $collection ) {
			$create = array();
			$skip   = array();
			foreach ( $this->collection( $collection ) as $object ) {
				$key = $object['_source_key'];
				if ( null === $this->find_object( $collection, $key ) ) {
					$create[] = $key;
				} else {
					$skip[] = $key;
				}
			}
			$plan[ $collection ] = array(
				'create' => $create,
				'skip'   => $skip,
			);
		}

		return $plan;
	}

	/**
	 * Imports the payload. Dry-run unless $apply is true.
	 *
	 * @param bool $apply Actually write (default is dry-run, ADR 0033).
	 */
	public function import( bool $apply ): array {
		$issues = $this->validate();
		if ( ! empty( $issues ) ) {
			return array( 'error' => 'Payload does not validate: ' . implode( ' | ', $issues ) );
		}

		if ( $apply ) {
			$guard = $this->production_guard();
			if ( null !== $guard ) {
				return $guard;
			}
		}

		$plan   = $this->plan();
		$report = array(
			'dry_run'     => ! $apply,
			'collections' => array(),
		);

		foreach ( self::OBJECT_COLLECTIONS as $collection ) {
			$created = 0;
			$skipped = count( $plan[ $collection ]['skip'] );
			foreach ( $this->collection( $collection ) as $object ) {
				$existing = $this->find_object( $collection, $object['_source_key'] );
				if ( null !== $existing ) {
					if ( 'media' === $collection ) {
						$this->media_urls[ '/' . $object['file'] ] = wp_get_attachment_url( $existing );
					}
					continue; // Create-missing-only: existing objects are never touched.
				}
				if ( $apply ) {
					$this->create_object( $collection, $object );
				}
				++$created;
			}
			$report['collections'][ $collection ] = array(
				'created' => $created,
				'skipped' => $skipped,
			);
		}

		$report['collections']['gallery_images'] = array(
			'created' => 0,
			'applied' => $apply ? $this->assign_albums() : 0,
		);

		if ( $apply ) {
			$report['settings'] = $this->apply_settings();
			$report['site_seo'] = $this->apply_site_seo();
		}

		return $report;
	}

	/**
	 * Seeds only the Media Library collection (owner-approved command name
	 * `seed`, OWN-009-img): real content, no fixture marker, no teardown.
	 *
	 * @param bool $apply Actually write (default is dry-run).
	 */
	public function seed( bool $apply ): array {
		$issues = $this->validate();
		if ( ! empty( $issues ) ) {
			return array( 'error' => 'Payload does not validate: ' . implode( ' | ', $issues ) );
		}
		if ( $apply ) {
			$guard = $this->production_guard();
			if ( null !== $guard ) {
				return $guard;
			}
		}

		$created = 0;
		$skipped = 0;
		foreach ( $this->collection( 'media' ) as $media ) {
			if ( null !== $this->find_object( 'media', $media['_source_key'] ) ) {
				++$skipped;
				continue;
			}
			if ( $apply ) {
				$this->create_attachment( $media );
			}
			++$created;
		}

		return array(
			'dry_run' => ! $apply,
			'media'   => array(
				'created' => $created,
				'skipped' => $skipped,
			),
		);
	}

	/**
	 * Verifies the imported objects: expected vs found per collection and
	 * the list of missing source keys.
	 */
	public function verify(): array {
		$collections = array();
		$missing     = array();

		foreach ( self::OBJECT_COLLECTIONS as $collection ) {
			$objects = $this->collection( $collection );
			$found   = 0;
			foreach ( $objects as $object ) {
				if ( null !== $this->find_object( $collection, $object['_source_key'] ) ) {
					++$found;
				} else {
					$missing[] = $object['_source_key'];
				}
			}
			$collections[ $collection ] = array(
				'expected' => count( $objects ),
				'found'    => $found,
			);
		}

		return array(
			'collections' => $collections,
			'missing'     => $missing,
		);
	}

	/**
	 * Refuses to write against production without explicit confirmation
	 * plus backup evidence (ADR 0033). Null when the write may proceed.
	 */
	private function production_guard(): ?array {
		if ( 'production' !== $this->options['environment'] ) {
			return null;
		}
		if ( $this->options['confirm_production'] && '' !== trim( (string) $this->options['backup_evidence'] ) ) {
			return null;
		}

		return array(
			'error' => 'Refusing to write to a production environment without --confirm-production and --backup-evidence (ADR 0033).',
		);
	}

	/**
	 * One collection, defaulting to empty.
	 *
	 * @param string $name Collection name.
	 */
	private function collection( string $name ): array {
		return (array) ( $this->payload[ $name ] ?? array() );
	}

	/**
	 * Absolute path of a payload-relative source file.
	 *
	 * @param string $file Repo-relative file under the payload root.
	 */
	private function source_file( string $file ): string {
		return $this->source_root . '/' . ( $this->payload['source']['root'] ?? 'static' ) . '/' . $file;
	}

	/**
	 * Finds an imported object by source key: term ID for albums, post ID
	 * otherwise. Null when absent.
	 *
	 * @param string $collection Collection name.
	 * @param string $source_key Stable source key.
	 */
	private function find_object( string $collection, string $source_key ) {
		if ( 'gallery_albums' === $collection ) {
			$terms = get_terms(
				array(
					'taxonomy'   => 'gallery_album',
					'hide_empty' => false,
					'meta_key'   => self::SOURCE_KEY_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- importer lookup on a small catalog.
					'meta_value' => $source_key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- importer lookup on a small catalog.
				)
			);

			return ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? (int) $terms[0]->term_id : null;
		}

		$found = get_posts(
			array(
				'post_type'   => 'any',
				'post_status' => 'any',
				'numberposts' => 1,
				'fields'      => 'ids',
				'meta_key'    => self::SOURCE_KEY_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- importer lookup on a small catalog.
				'meta_value'  => $source_key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- importer lookup on a small catalog.
			)
		);

		return empty( $found ) ? null : (int) $found[0];
	}

	/**
	 * Creates one payload object.
	 *
	 * @param string $collection     Collection name.
	 * @param array  $payload_object Payload object.
	 */
	private function create_object( string $collection, array $payload_object ) {
		switch ( $collection ) {
			case 'blog_authors':
				$this->create_post(
					$payload_object,
					array(
						'post_type'    => 'blog_author',
						'post_title'   => $payload_object['name'],
						'post_name'    => $payload_object['slug'],
						'post_content' => (string) $payload_object['bio'],
						'post_status'  => 'publish',
					)
				);
				break;

			case 'media':
				$this->create_attachment( $payload_object );
				break;

			case 'gallery_albums':
				$term = wp_insert_term( $payload_object['title'], 'gallery_album', array( 'slug' => $payload_object['slug'] ) );
				if ( ! is_wp_error( $term ) ) {
					add_term_meta( $term['term_id'], self::SOURCE_KEY_META, $payload_object['_source_key'], true );
					add_term_meta( $term['term_id'], self::SOURCE_HASH_META, $payload_object['_source_hash'], true );
				}
				break;

			case 'events':
				$event_id = $this->create_post(
					$payload_object,
					array(
						'post_type'    => 'event',
						'post_title'   => $payload_object['title'],
						'post_name'    => $payload_object['slug'],
						'post_status'  => 'publish',
						'post_excerpt' => (string) $payload_object['excerpt'],
						'post_content' => $this->wrap_content( $payload_object['content_html'] ),
						'meta_input'   => array_merge(
							array_filter(
								array(
									'event_date'           => (string) ( $payload_object['start'] ?? '' ),
									'event_end'            => (string) ( $payload_object['end'] ?? '' ),
									'event_place'          => (string) $payload_object['place'],
									'event_modality'       => (string) $payload_object['modality'],
									'event_status'         => (string) $payload_object['status'],
									'event_featured'       => ! empty( $payload_object['featured'] ),
									'event_signup_url'     => (string) ( $payload_object['signup_url'] ?? '' ),
									'event_calendar_dates' => (array) $payload_object['calendar_dates'],
								),
								static function ( $value ): bool {
									return array() !== $value && '' !== $value;
								}
							),
							self::share_meta( $payload_object ),
							self::seo_meta( $payload_object ),
							array_filter(
								array(
									'event_attendance_mode' => (string) ( $payload_object['attendance_mode'] ?? '' ),
									'seo_jsonld_extra' => array() !== (array) ( $payload_object['jsonld_extra'] ?? array() )
										? (string) wp_json_encode( $payload_object['jsonld_extra'] )
										: '',
								)
							)
						),
					)
				);
				$this->set_named_terms( $event_id, 'event_type', array_filter( array( $payload_object['type'] ) ) );
				$this->set_named_terms( $event_id, 'event_city', (array) $payload_object['cities'] );
				$this->attach_thumbnail( $event_id, (string) $payload_object['poster'] );
				break;

			case 'posts':
				$post_id = $this->create_post(
					$payload_object,
					array(
						'post_type'    => 'post',
						'post_title'   => $payload_object['title'],
						'post_name'    => $payload_object['slug'],
						'post_status'  => 'publish',
						'post_date'    => $payload_object['date'] . ' 12:00:00',
						'post_excerpt' => (string) $payload_object['deck'],
						'post_content' => $this->wrap_content( $payload_object['content_html'] ),
						'meta_input'   => array_merge(
							array( 'authors' => $this->author_ids( (array) $payload_object['authors'] ) ),
							self::share_meta( $payload_object ),
							self::seo_meta( $payload_object )
						),
					)
				);
				$this->attach_thumbnail( $post_id, (string) $payload_object['thumbnail'] );
				break;

			case 'pages':
				$parent    = (string) ( $payload_object['parent'] ?? '' );
				$parent_id = '' !== $parent ? ( get_page_by_path( $parent )->ID ?? 0 ) : 0;
				$this->create_post(
					$payload_object,
					array(
						'post_type'    => 'page',
						'post_title'   => $payload_object['title'],
						'post_name'    => basename( $payload_object['slug'] ),
						'post_parent'  => $parent_id,
						'post_status'  => 'publish',
						'post_content' => $this->wrap_content( $payload_object['content_html'] ),
						'meta_input'   => self::seo_meta( $payload_object ),
					)
				);
				break;
		}
	}

	/**
	 * Inserts a post with the source bookkeeping meta.
	 *
	 * @param array $payload_object Payload object.
	 * @param array $post_args      wp_insert_post arguments.
	 */
	private function create_post( array $payload_object, array $post_args ): int {
		$post_args['meta_input'] = array_merge(
			(array) ( $post_args['meta_input'] ?? array() ),
			array(
				self::SOURCE_KEY_META  => $payload_object['_source_key'],
				self::SOURCE_HASH_META => $payload_object['_source_hash'],
			)
		);
		if ( isset( $post_args['post_content'] ) ) {
			$post_args['post_content'] = $this->rewrite_media_urls( $post_args['post_content'] );
		}

		return (int) wp_insert_post( wp_slash( $post_args ) );
	}

	/**
	 * The share message templates of one payload object as meta (WU-08A).
	 * Empty when the object publishes no share copy — the dialog then
	 * falls back to title + URL instead of an empty message.
	 *
	 * @param array $payload_object Event or post payload object.
	 */
	public static function share_meta( array $payload_object ): array {
		$share = (array) ( $payload_object['share'] ?? array() );
		$meta  = array();

		foreach ( Cdd_Core_Share_Extractor::PLATFORMS as $platform ) {
			$template = (string) ( $share[ $platform ] ?? '' );
			if ( '' === $template ) {
				continue;
			}
			$meta[ 'share_' . $platform ] = $template;
		}

		return $meta;
	}

	/**
	 * The published head copy of one payload object as meta (WU-08B).
	 * Empty fields are dropped, never written as empty strings: an
	 * absent key is what lets the theme fall back to real data, and what
	 * keeps `convert` add-only.
	 *
	 * @param array $payload_object Page, post or event payload object.
	 */
	public static function seo_meta( array $payload_object ): array {
		$seo = (array) ( $payload_object['seo'] ?? array() );

		$meta = array(
			'seo_title'       => (string) ( $seo['title'] ?? '' ),
			'seo_description' => (string) ( $seo['description'] ?? '' ),
			'seo_keywords'    => (string) ( $seo['keywords'] ?? '' ),
			'og_title'        => (string) ( $seo['og_title'] ?? '' ),
			'og_description'  => (string) ( $seo['og_description'] ?? '' ),
			'seo_related_url' => (string) ( $seo['related'] ?? '' ),
		);

		return array_filter(
			$meta,
			static function ( string $value ): bool {
				return '' !== $value;
			}
		);
	}

	/**
	 * Seeds one media file as a real attachment: file copied into the
	 * uploads dir, derivative sizes generated by WordPress, alt text from
	 * the extracted references. Hidden media is simply unattached and
	 * unreferenced (OWN-003).
	 *
	 * @param array $media Media payload object.
	 */
	private function create_attachment( array $media ): int {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$source  = $this->source_file( $media['file'] );
		$uploads = wp_upload_dir();
		$name    = wp_unique_filename( $uploads['path'], basename( $media['file'] ) );
		$dest    = trailingslashit( $uploads['path'] ) . $name;
		copy( $source, $dest ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_copy -- CLI-side seed copying repo files into uploads.

		$filetype      = wp_check_filetype( $name );
		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => (string) $filetype['type'],
				'post_title'     => sanitize_text_field( pathinfo( $name, PATHINFO_FILENAME ) ),
				'post_status'    => 'inherit',
			),
			$dest
		);

		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $dest ) );
		update_post_meta( $attachment_id, self::SOURCE_KEY_META, $media['_source_key'] );
		update_post_meta( $attachment_id, self::SOURCE_HASH_META, $media['_source_hash'] );

		$alt = $this->alt_for( $media['file'] );
		if ( '' !== $alt ) {
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
		}

		$this->media_urls[ '/' . $media['file'] ] = wp_get_attachment_url( $attachment_id );

		return (int) $attachment_id;
	}

	/**
	 * The extracted alt text for a media file: the gallery JSON alt, the
	 * event poster alt or the post hero alt — empty when none references
	 * the file.
	 *
	 * @param string $file Repo-relative media file.
	 */
	private function alt_for( string $file ): string {
		foreach ( $this->collection( 'gallery_images' ) as $image ) {
			if ( $image['file'] === $file && '' !== (string) $image['alt'] ) {
				return (string) $image['alt'];
			}
		}
		foreach ( $this->collection( 'events' ) as $event ) {
			if ( ( $event['poster'] ?? '' ) === $file && '' !== (string) ( $event['poster_alt'] ?? '' ) ) {
				return (string) $event['poster_alt'];
			}
		}
		foreach ( $this->collection( 'posts' ) as $post ) {
			if ( ( $post['thumbnail'] ?? '' ) === $file && '' !== (string) ( $post['thumbnail_alt'] ?? '' ) ) {
				return (string) $post['thumbnail_alt'];
			}
		}

		return '';
	}

	/**
	 * Assigns album terms and stable positions to the seeded gallery
	 * attachments (idempotent by construction).
	 */
	private function assign_albums(): int {
		$applied = 0;
		foreach ( $this->collection( 'gallery_images' ) as $image ) {
			$attachment_id = $this->find_object( 'media', 'media:' . $image['file'] );
			if ( null === $attachment_id ) {
				continue;
			}
			wp_set_object_terms( $attachment_id, $image['album'], 'gallery_album', false );
			wp_update_post(
				array(
					'ID'         => $attachment_id,
					'menu_order' => (int) $image['position'],
				)
			);
			++$applied;
		}

		return $applied;
	}

	/**
	 * Site-wide SEO (WU-08B): the published social defaults, the head of
	 * the CPT archives and the home `@graph` become one option, and the
	 * published `addressRegion` of each city becomes term metadata. Both
	 * are add-only — an edited value is never rewritten (ADR 0033).
	 */
	private function apply_site_seo(): array {
		$site = (array) ( $this->payload['site'] ?? array() );
		if ( array() === $site ) {
			return array();
		}

		$applied = array();
		$regions = (array) ( $site['seo']['city_regions'] ?? array() );
		unset( $site['seo']['city_regions'] );

		if ( false === get_option( CDD_CORE_SEO_OPTION, false ) ) {
			update_option( CDD_CORE_SEO_OPTION, $site );
			$applied[] = CDD_CORE_SEO_OPTION;
		}

		foreach ( $regions as $city => $region ) {
			$term = get_term_by( 'name', (string) $city, 'event_city' );
			if ( $term instanceof WP_Term && ! metadata_exists( 'term', $term->term_id, 'cdd_region' ) ) {
				add_term_meta( $term->term_id, 'cdd_region', (string) $region, true );
				$applied[] = 'cdd_region:' . $city;
			}
		}

		return $applied;
	}

	/**
	 * Reading settings and the ADR 0008 permalink structure.
	 */
	private function apply_settings(): array {
		global $wp_rewrite;

		$applied = array();

		// OWN-013 resolves event status in America/Bogota; the site
		// timezone must say so too. (The document language is not a
		// setting the importer can write: WordPress rejects a locale
		// whose translation files are absent — see
		// cdd_core_default_locale().)
		if ( 'America/Bogota' !== get_option( 'timezone_string' ) ) {
			update_option( 'timezone_string', 'America/Bogota' );
			update_option( 'gmt_offset', '' );
			$applied[] = 'timezone_string';
		}
		$front = get_page_by_path( 'inicio' );
		$posts = get_page_by_path( 'blog' );

		if ( $front instanceof WP_Post ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $front->ID );
			$applied[] = 'page_on_front';
		}
		if ( $posts instanceof WP_Post ) {
			update_option( 'page_for_posts', $posts->ID );
			$applied[] = 'page_for_posts';
		}

		if ( '/blog/%postname%' !== get_option( 'permalink_structure' ) ) {
			update_option( 'permalink_structure', '/blog/%postname%' );
			$wp_rewrite->init();
			// Re-register the domain objects: when this process booted
			// under plain permalinks, register_post_type/register_taxonomy
			// skipped their permastructs, and flushing now would persist a
			// rule set without the CPT/taxonomy routes.
			cdd_core_register_post_types();
			cdd_core_register_taxonomies();
			cdd_core_register_rewrites();
			flush_rewrite_rules();
			$applied[] = 'permalink_structure';
		}

		// docs/11 §3.2: tag archives live under /blog/tag/{slug}. The
		// default base would publish /tag/{slug}, a URL the tree does not
		// contain.
		if ( 'blog/tag' !== get_option( 'tag_base' ) ) {
			update_option( 'tag_base', 'blog/tag' );
			$wp_rewrite->init();
			flush_rewrite_rules();
			$applied[] = 'tag_base';
		}

		return $applied;
	}

	/**
	 * Ensures named terms exist and assigns them by ID (safe for the
	 * hierarchical event_type).
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $taxonomy Taxonomy name.
	 * @param array  $names    Term names.
	 */
	private function set_named_terms( int $post_id, string $taxonomy, array $names ) {
		$ids = array();
		foreach ( $names as $name ) {
			$existing = term_exists( $name, $taxonomy );
			if ( null === $existing || 0 === $existing ) {
				$inserted = wp_insert_term( $name, $taxonomy );
				if ( is_wp_error( $inserted ) ) {
					continue;
				}
				$ids[] = (int) $inserted['term_id'];
			} else {
				$ids[] = (int) ( is_array( $existing ) ? $existing['term_id'] : $existing );
			}
		}
		if ( ! empty( $ids ) ) {
			wp_set_object_terms( $post_id, $ids, $taxonomy, false );
		}
	}

	/**
	 * Sets a seeded poster/hero as the featured image.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $file    Repo-relative media file.
	 */
	private function attach_thumbnail( int $post_id, string $file ) {
		if ( '' === $file ) {
			return;
		}
		$attachment_id = $this->find_object( 'media', 'media:' . $file );
		if ( null !== $attachment_id ) {
			set_post_thumbnail( $post_id, $attachment_id );
		}
	}

	/**
	 * The blog_author post IDs for a list of profile slugs.
	 *
	 * @param array $slugs Profile slugs.
	 */
	private function author_ids( array $slugs ): array {
		$ids = array();
		foreach ( $slugs as $slug ) {
			$id = $this->find_object( 'blog_authors', 'blog_author:' . $slug );
			if ( null !== $id ) {
				$ids[] = $id;
			}
		}

		return $ids;
	}

	/**
	 * Wraps extracted HTML in a raw-HTML block so the block editor keeps
	 * it verbatim until the WU-07 presentation pass converts it.
	 *
	 * @param string $html Extracted content HTML.
	 */
	private function wrap_content( string $html ): string {
		if ( '' === trim( $html ) ) {
			return '';
		}

		return "<!-- wp:html -->\n" . $html . "\n<!-- /wp:html -->";
	}

	/**
	 * Rewrites known static media URLs inside content to their Media
	 * Library counterparts.
	 *
	 * @param string $html Content HTML.
	 */
	private function rewrite_media_urls( string $html ): string {
		if ( empty( $this->media_urls ) || '' === $html ) {
			return $html;
		}

		return strtr( $html, $this->media_urls );
	}
}
