<?php
/**
 * Level 1: the ported front-end behavior of the theme (WU-08A).
 *
 * Written RED-first. The static mockup implements the share and
 * add-to-calendar dialogs in assets/js/share.js and assets/js/calendar.js;
 * the tooltip half of calendar.js already travelled in WU-07. These tests
 * name the DOM/behavior contract the ported scripts must keep, so a
 * rewrite that silently drops an intent URL, the copy fallback or the
 * focus return fails here first.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: share dialog, calendar dialog and mantra audio.
 */
final class Theme_BehaviorTest extends TestCase {

	/**
	 * Protects docs/12 §2: the dialogs are blocks of the theme, not
	 * markup hardcoded into a template file.
	 */
	public function test_behavior_blocks_are_registered() {
		$blocks = $this->theme_file( 'inc/blocks.php' );

		$this->assertStringContainsString( "'evento-acciones'", $blocks );
		$this->assertStringContainsString( "'entrada-compartir'", $blocks );
	}

	/**
	 * Protects the published order of the event single: the actions block
	 * closes the article, after the CTA and before the navigation.
	 */
	public function test_single_event_template_places_the_actions_after_the_cta() {
		$html = $this->theme_file( 'templates/single-event.html' );

		$cta     = strpos( $html, 'wp:camino-del-dharma/evento-cta' );
		$actions = strpos( $html, 'wp:camino-del-dharma/evento-acciones' );
		$nav     = strpos( $html, 'single-event-nav' );

		$this->assertNotFalse( $actions );
		$this->assertGreaterThan( $cta, $actions );
		$this->assertGreaterThan( $actions, $nav );
	}

	/**
	 * Protects the published order of the blog single: the share block
	 * closes the article, after the content and before the blog links.
	 */
	public function test_single_template_places_the_share_block_before_the_nav() {
		$html = $this->theme_file( 'templates/single.html' );

		$content = strpos( $html, 'wp:post-content' );
		$share   = strpos( $html, 'wp:camino-del-dharma/entrada-compartir' );
		$nav     = strpos( $html, 'blog-single-nav' );

		$this->assertNotFalse( $share );
		$this->assertGreaterThan( $content, $share );
		$this->assertGreaterThan( $share, $nav );
	}

	/**
	 * Protects the share behavior contract of the static mockup: the
	 * trigger selector, the four published intents with their real
	 * endpoints, the {{SHARE_URL}} injection, the clipboard fallback and
	 * the accessible dialog wiring (labelled dialog, backdrop close,
	 * focus back to the opener).
	 */
	public function test_share_script_keeps_the_published_behavior_contract() {
		$js = $this->theme_file( 'assets/js/share.js' );

		foreach (
			array(
				'[data-share-title]',
				'data-share-whatsapp-template',
				'data-share-x-template',
				'data-share-threads-template',
				'api.whatsapp.com/send?text=',
				'facebook.com/sharer/sharer.php?u=',
				'x.com/intent/post?text=',
				'threads.com/intent/post?text=',
				'/\\{\\{SHARE_URL\\}\\}/g',
				'navigator.clipboard',
				'execCommand',
				'aria-labelledby',
				'showModal',
				'opener.focus()',
			) as $hook
		) {
			$this->assertStringContainsString( $hook, $js, $hook );
		}
	}

	/**
	 * Protects the published Spanish copy of the share dialog (OWN-007):
	 * the strings a visitor reads are the ones production publishes.
	 */
	public function test_share_script_keeps_the_published_copy() {
		$js = $this->theme_file( 'assets/js/share.js' );

		foreach (
			array(
				'Compartir',
				'Cerrar opciones para compartir',
				'Copiar enlace',
				'Enlace copiado.',
				'(abre en nueva pestaña)',
			) as $copy
		) {
			$this->assertStringContainsString( $copy, $js, $copy );
		}
	}

	/**
	 * Protects the calendar-dialog contract of the static mockup: the
	 * trigger selector, the Google/Outlook deep links, the exclusive-end
	 * conversion Outlook needs and the two .ics entry points.
	 */
	public function test_calendar_dialog_script_keeps_the_published_behavior_contract() {
		$js = $this->theme_file( 'assets/js/calendar-dialog.js' );

		foreach (
			array(
				'[data-calendar-title]',
				'calendar.google.com/calendar/render?',
				'outlook.live.com/calendar/0/deeplink/compose?',
				'data-calendar-platform="apple"',
				'data-calendar-platform="download"',
				'exclusiveEndToInclusiveIso',
				'setAttribute(\'download\'',
				'Añadir al calendario',
				'showModal',
				'opener.focus()',
			) as $hook
		) {
			$this->assertStringContainsString( $hook, $js, $hook );
		}
	}

	/**
	 * Protects the split made in WU-07: the month-grid tooltip behavior
	 * lives in calendar-tooltips.js and is not duplicated by the dialog
	 * script (one behavior, one file, one enqueue).
	 */
	public function test_calendar_dialog_does_not_duplicate_the_tooltip_behavior() {
		$dialog = $this->theme_file( 'assets/js/calendar-dialog.js' );

		$this->assertStringNotContainsString( 'eventos-calendar-grid', $dialog );
		$this->assertStringNotContainsString( 'is-tooltip-visible', $dialog );
	}

	/**
	 * Protects the whole port: every behavior of the static calendar.js
	 * now lives in the theme, split across the two scripts.
	 */
	public function test_the_static_calendar_script_is_fully_ported() {
		$static  = file_get_contents( dirname( __DIR__, 2 ) . '/static/assets/js/calendar.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local repo file in a unit test without WordPress loaded.
		$ported  = $this->theme_file( 'assets/js/calendar-dialog.js' );
		$ported .= $this->theme_file( 'assets/js/calendar-tooltips.js' );

		foreach ( array( 'buildGoogleCalendarUrl', 'buildOutlookUrl', 'icsFilename', 'clearVisibleTooltips', 'revealOnTapQuery' ) as $behavior ) {
			$this->assertStringContainsString( $behavior, $static, $behavior );
			$this->assertStringContainsString( $behavior, $ported, $behavior );
		}
	}

	/**
	 * Protects the accessible name of the mantra players (docs/19): the
	 * native core/audio block carries no aria-label, so the theme
	 * restores the published one from the caption.
	 */
	public function test_theme_restores_the_audio_accessible_name() {
		$functions = $this->theme_file( 'inc/blocks.php' ) . $this->theme_file( 'functions.php' );

		$this->assertStringContainsString( 'render_block', $functions );
		$this->assertStringContainsString( 'aria-label', $functions );
	}

	/**
	 * One theme file's content, relative to the theme root.
	 *
	 * @param string $relative Path inside the theme.
	 */
	private function theme_file( string $relative ): string {
		$path = dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/camino-del-dharma/' . $relative;

		return is_readable( $path ) ? file_get_contents( $path ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local repo file in a unit test without WordPress loaded.
	}
}
