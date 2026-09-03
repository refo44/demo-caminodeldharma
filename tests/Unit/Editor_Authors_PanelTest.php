<?php
/**
 * Level 1: the shipped «Autores del blog» editor script and the ADR 0042
 * invariant it exists to satisfy (META-001).
 *
 * There is no JS runner in this repository (ADR 0038: no @wordpress/scripts,
 * no webpack, no browser test runner), so these tests do not pretend to
 * execute the panel. They pin the two things a PHP suite can still reach
 * and that META-001 is about: the script writes the relationship through
 * `core/editor` (never only into the DOM), and the plugin ships no classic
 * metabox that could bypass that transport. Whether the panel *renders*
 * is a manual check against the local Docker editor, recorded in the PR.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: the editor transport contract of the authors relation.
 */
final class Editor_Authors_PanelTest extends TestCase {

	/**
	 * Protects META-001 (ADR 0042): the panel hands the relationship to the
	 * editor store, so Publicar/Actualizar carries `meta.authors` in the
	 * same REST body the guard reads — the revistalogos #30 failure.
	 */
	public function test_the_panel_writes_the_relation_through_the_editor_store() {
		$script = $this->panel_script();

		$this->assertStringContainsString( "dispatch( 'core/editor' )", $script );
		$this->assertStringContainsString( 'editPost(', $script );
		$this->assertMatchesRegularExpression( '/meta:\s*\S/', $script );
		$this->assertStringContainsString( 'authors', $script );
	}

	/**
	 * Protects ADR 0037 §6: the picker searches the REST collection of
	 * *published* fichas from two characters on, and never asks for the
	 * whole catalogue.
	 */
	public function test_the_panel_searches_published_profiles_from_two_characters() {
		$script = $this->panel_script();

		$this->assertStringContainsString( '/wp/v2/blog_author', $script );
		$this->assertStringContainsString( 'status=publish', $script );
		$this->assertMatchesRegularExpression( '/MIN_SEARCH_LENGTH\s*=\s*2\b/', $script );
		$this->assertStringNotContainsString( 'per_page=-1', $script );
	}

	/**
	 * Protects ADR 0042: no classic metabox anywhere in the plugin. A
	 * metabox that only mutates the DOM is exactly the transport META-001
	 * forbids, and «it looked filled» is not evidence that it saved.
	 */
	public function test_the_plugin_ships_no_classic_metabox() {
		$sources = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $this->plugin_dir() ) );

		foreach ( $sources as $file ) {
			if ( ! $file->isFile() || 'php' !== $file->getExtension() ) {
				continue;
			}

			$this->assertStringNotContainsString(
				'add_meta_box',
				(string) file_get_contents( $file->getPathname() ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local repo file in a unit test without WordPress loaded.
				$file->getFilename() . ' registers a classic metabox (ADR 0042).'
			);
		}
	}

	/**
	 * The production editor script.
	 */
	private function panel_script(): string {
		$path = $this->plugin_dir() . '/assets/js/authors-panel.js';

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
