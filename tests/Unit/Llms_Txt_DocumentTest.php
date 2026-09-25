<?php
/**
 * Level 1: /llms.txt is a pure markdown document. No WordPress boot.
 *
 * The published contract is static/llms.txt. Journal identifiers, a
 * license line, and calendar downloads never belong in it (OWN-014).
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: the llms.txt document omits empty sections.
 */
final class Llms_Txt_DocumentTest extends TestCase {

	/**
	 * Protects the curated guidance: the 2019 correction and the Monday
	 * meditation rule are part of the document, and an empty group is not
	 * printed.
	 */
	public function test_document_includes_published_guidance_and_omits_empty_groups() {
		$document = Cdd_Core_Llms_Txt::format_document(
			array(
				'preamble' => Cdd_Core_Llms_Txt::preamble( 'https://example.org' ),
				'groups'   => array(
					array(
						'heading' => 'Tradición',
						'items'   => array(),
					),
					array(
						'heading' => 'Práctica',
						'items'   => array(
							array(
								'label' => 'Práctica',
								'url'   => 'https://example.org/practica',
								'note'  => 'Meditación y recitación.',
							),
						),
					),
				),
			)
		);

		$this->assertStringContainsString( 'founded in Colombia in 2019', $document );
		$this->assertStringContainsString( 'Monday marks on the Events calendar', $document );
		$this->assertStringContainsString( '**Canonical website:** https://example.org', $document );
		$this->assertStringContainsString( '## Práctica', $document );
		$this->assertStringContainsString( '- [Práctica](https://example.org/practica): Meditación y recitación.', $document );
		$this->assertStringNotContainsString( '## Tradición', $document );
	}

	/**
	 * Protects OWN-014 and the decision not to invent journal metadata.
	 */
	public function test_document_omits_journal_lines_and_calendar_downloads() {
		$document = Cdd_Core_Llms_Txt::format_document(
			array(
				'preamble' => Cdd_Core_Llms_Txt::preamble( 'https://example.org' ),
				'groups'   => array(
					array(
						'heading' => 'Actividades',
						'items'   => array(
							array(
								'label' => 'Eventos',
								'url'   => 'https://example.org/eventos',
								'note'  => 'Fuente oficial.',
							),
						),
					),
				),
			)
		);

		$this->assertStringNotContainsString( 'Próximamente', $document );
		$this->assertStringNotContainsString( 'ISSN', $document );
		$this->assertStringNotContainsString( 'Depósito Legal', $document );
		$this->assertStringNotContainsString( 'MIT', $document );
		$this->assertStringNotContainsString( 'Número actual', $document );
		$this->assertStringNotContainsString( '.ics', $document );
		$this->assertStringNotContainsString( '## Sitemap', $document );
	}

	/**
	 * A current event is one link. No current event means no extra line.
	 */
	public function test_current_event_is_title_and_permalink_only() {
		$with_event = Cdd_Core_Llms_Txt::format_document(
			array(
				'preamble' => 'preamble',
				'groups'   => array(
					array(
						'heading' => 'Actividades',
						'items'   => array(
							array(
								'label' => 'Círculos de Presencia Consciente',
								'url'   => 'https://example.org/eventos/circulos',
								'note'  => '',
							),
						),
					),
				),
			)
		);

		$without_event = Cdd_Core_Llms_Txt::format_document(
			array(
				'preamble' => 'preamble',
				'groups'   => array(
					array(
						'heading' => 'Actividades',
						'items'   => array(),
					),
				),
			)
		);

		$this->assertStringContainsString(
			'- [Círculos de Presencia Consciente](https://example.org/eventos/circulos)',
			$with_event
		);
		$this->assertStringNotContainsString( '## Actividades', $without_event );
	}
}
