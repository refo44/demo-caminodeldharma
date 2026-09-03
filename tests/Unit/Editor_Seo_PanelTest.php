<?php
/**
 * Level 1: the shipped SEO / event editor script and the ADR 0042
 * transport invariant it exists to satisfy (META-002 / META-003).
 *
 * There is no JS runner in this repository (ADR 0038: no @wordpress/scripts,
 * no webpack, no browser test runner), so these tests do not execute the
 * panel. They pin what a PHP suite can still reach and what META-002/003
 * are about: the script writes every head and event field through
 * `core/editor` (never only into the DOM), it covers the documented keys,
 * and it invents no schema.org type for a `blog_author`.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: the editor transport contract of the SEO / event meta.
 */
final class Editor_Seo_PanelTest extends TestCase {

	/**
	 * Protects ADR 0042: every panel write goes through the editor store, so
	 * Publicar/Actualizar carries the edited keys in the same REST meta body
	 * — a panel that only fills the DOM saves nothing.
	 */
	public function test_the_panel_writes_through_the_editor_store() {
		$script = $this->panel_script();

		$this->assertStringContainsString( "dispatch( 'core/editor' )", $script );
		$this->assertStringContainsString( 'editPost(', $script );
		$this->assertMatchesRegularExpression( '/meta:\s*\S/', $script );
		$this->assertStringContainsString( "getEditedPostAttribute( 'meta' )", $script );
		$this->assertStringContainsString( 'getCurrentPostType()', $script );
	}

	/**
	 * Protects META-002: the head panel edits exactly the keys the
	 * request-time head reads (`includes/seo.php`), no more.
	 */
	public function test_the_head_panel_covers_the_registered_head_keys() {
		$script = $this->panel_script();

		foreach ( array( 'seo_title', 'seo_description', 'seo_keywords', 'og_title', 'og_description', 'seo_related_url' ) as $key ) {
			$this->assertStringContainsString( "'" . $key . "'", $script, $key );
		}
	}

	/**
	 * Protects META-003: the event panel edits exactly the structured-data
	 * keys the JSON-LD `Event` node and the generated `.ics` already read —
	 * no invented domain key.
	 */
	public function test_the_event_panel_covers_the_event_structured_data_keys() {
		$script = $this->panel_script();

		foreach (
			array(
				'event_date',
				'event_end',
				'event_place',
				'event_modality',
				'event_attendance_mode',
				'event_status',
				'event_signup_url',
				'event_signup_payment',
				'event_featured',
				'event_calendar_dates',
			) as $key
		) {
			$this->assertStringContainsString( "'" . $key . "'", $script, $key );
		}
	}

	/**
	 * Protects the scope: the head panel renders for the four public
	 * editorial types and the event panel only for an event.
	 */
	public function test_the_panels_are_scoped_by_post_type() {
		$script = $this->panel_script();

		foreach ( array( 'post', 'page', 'event', 'blog_author' ) as $type ) {
			$this->assertStringContainsString( "'" . $type . "'", $script, $type );
		}

		$this->assertStringContainsString( 'PluginDocumentSettingPanel', $script );
		$this->assertStringContainsString( "registerPlugin( 'cdd-core-seo-panels'", $script );
	}

	/**
	 * Protects ADR 0037 / doc 15 §12.4: a `blog_author` head is richer copy,
	 * never a promotion of its JSON-LD `Thing` to `Person`/`Organization`.
	 */
	public function test_the_panel_invents_no_schema_org_type() {
		$script = $this->panel_script();

		$this->assertStringNotContainsString( "'Person'", $script );
		$this->assertStringNotContainsString( "'Organization'", $script );
	}

	/**
	 * The production editor script.
	 */
	private function panel_script(): string {
		$path = $this->plugin_dir() . '/assets/js/seo-panel.js';

		$this->assertFileExists( $path );

		return (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local repo file in a unit test without WordPress loaded.
	}

	/**
	 * Path of the plugin tree relative to the repo root.
	 */
	private function plugin_dir(): string {
		return dirname( __DIR__, 2 ) . '/wordpress/wp-content/plugins/camino-del-dharma-core';
	}
}
