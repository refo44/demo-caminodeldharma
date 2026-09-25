<?php
/**
 * Level 1: the «Compartir» editor script and the ADR 0042 transport
 * it exists to satisfy (issue #39).
 *
 * There is no JS runner in this repository (ADR 0038), so these tests do
 * not execute the panel. They pin what a PHP suite can still reach: the
 * script writes the three existing share keys through `core/editor`, it
 * previews the public dialog (one network at a time, URL already
 * substituted), and the SEO panel points Open Graph at the link card.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: the editor transport contract of the share messages.
 */
final class Editor_Share_PanelTest extends TestCase {

	/**
	 * Protects ADR 0042: Publicar/Actualizar carries the edited keys in the
	 * same REST meta body. A panel that only fills the DOM saves nothing.
	 */
	public function test_the_panel_writes_share_messages_through_the_editor_store() {
		$script = $this->panel_script();

		$this->assertStringContainsString( "dispatch( 'core/editor' )", $script );
		$this->assertStringContainsString( 'editPost(', $script );
		$this->assertMatchesRegularExpression( '/meta:\s*\S/', $script );
		$this->assertStringContainsString( "getEditedPostAttribute( 'meta' )", $script );
		$this->assertStringContainsString( 'getCurrentPostType()', $script );
		$this->assertStringContainsString( "registerPlugin( 'cdd-core-share-panel'", $script );
		$this->assertStringContainsString( 'PluginDocumentSettingPanel', $script );
	}

	/**
	 * Protects the model: the panel edits the three keys the public dialog
	 * already reads, and no other share key.
	 */
	public function test_the_panel_covers_the_registered_share_keys() {
		$script = $this->panel_script();

		foreach ( array( 'share_whatsapp', 'share_x', 'share_threads' ) as $key ) {
			$this->assertStringContainsString( "'" . $key . "'", $script, $key );
		}

		$this->assertStringNotContainsString( 'share_facebook', $script );
	}

	/**
	 * Protects the scope and the labels an editor sees: posts and events,
	 * one network at a time, named like the public button.
	 */
	public function test_the_panel_is_the_compartir_control_for_posts_and_events() {
		$script = $this->panel_script();

		$this->assertStringContainsString( "'post'", $script );
		$this->assertStringContainsString( "'event'", $script );
		$this->assertStringContainsString( 'Compartir', $script );
		$this->assertStringContainsString( 'WhatsApp', $script );
		$this->assertStringContainsString( "'x'", $script );
		$this->assertStringContainsString( 'Threads', $script );
		$this->assertStringContainsString( 'Así se enviará', $script );
		$this->assertStringContainsString( '{{SHARE_URL}}', $script );
		$this->assertStringContainsString( 'Vacío: se usa el título y la URL.', $script );
		$this->assertStringContainsString( 'Opcional.', $script );
	}

	/**
	 * Protects the event fallback: an empty message uses the type and the
	 * name, the same title the public dialog shows.
	 */
	public function test_the_empty_event_preview_uses_the_event_type_and_the_name() {
		$script = $this->panel_script();

		$this->assertStringContainsString( "'event_type'", $script );
		$this->assertStringContainsString( 'getEntityRecord', $script );
	}

	/**
	 * Protects the X note: the preview counts characters and names 280
	 * without refusing the save.
	 */
	public function test_the_x_preview_counts_characters_past_280() {
		$script = $this->panel_script();

		$this->assertStringContainsString( '280', $script );
		$this->assertStringNotContainsString( 'lockPostSaving', $script );
	}

	/**
	 * Protects the distinction: Open Graph stays the link card, and the
	 * SEO panel says the button message lives in Compartir.
	 */
	public function test_the_seo_panel_points_open_graph_at_the_link_card() {
		$seo = (string) file_get_contents( $this->plugin_dir() . '/assets/js/seo-panel.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local repo file in a unit test without WordPress loaded.

		$this->assertStringContainsString( 'ficha del enlace', $seo );
		$this->assertStringContainsString( 'panel Compartir', $seo );
		$this->assertStringContainsString( 'Opcional.', $seo );
		$this->assertStringContainsString( 'Puedes publicar el evento sin rellenar estos datos.', $seo );
	}

	/**
	 * The production editor script.
	 */
	private function panel_script(): string {
		$path = $this->plugin_dir() . '/assets/js/share-panel.js';

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
