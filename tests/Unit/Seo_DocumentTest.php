<?php
/**
 * Level 1: the pure head document and JSON-LD builders (WU-08B).
 *
 * Written RED-first. These classes own *what* the head says; the theme
 * owns how it is printed. The rules under test come from
 * docs/15-assets-strategy.md §12 and from the master prompt §10.2:
 * completed events use EventCompleted and drop the signup offer, author
 * profiles are `Thing`, the publisher is always the site Organization,
 * and no optional field is ever invented.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * SEO cluster: head tag document and schema.org graph.
 */
final class Seo_DocumentTest extends TestCase {

	/**
	 * The head prints the published copy, in the published order, with
	 * canonical, Open Graph and Twitter all pointing at the same URL.
	 */
	public function test_document_lists_title_description_canonical_and_social_cards() {
		$tags = Cdd_Core_Seo_Document::tags(
			array(
				'title'          => 'Título | Camino del Dharma',
				'description'    => 'Descripción publicada.',
				'keywords'       => 'budismo, colombia',
				'robots'         => 'index,follow,max-image-preview:large',
				'canonical'      => 'https://example.test/pagina',
				'og_type'        => 'website',
				'og_title'       => 'Título social',
				'og_description' => 'Descripción social.',
				'site_name'      => 'Camino del Dharma',
				'locale'         => 'es_CO',
				'image'          => 'https://example.test/og.jpg',
				'image_alt'      => 'Alt de la imagen',
				'image_width'    => '1200',
				'image_height'   => '630',
				'twitter_card'   => 'summary_large_image',
			)
		);

		$this->assertSame( 'Título | Camino del Dharma', $this->title_of( $tags ) );
		$this->assertSame( 'Descripción publicada.', $this->meta_named( $tags, 'description' ) );
		$this->assertSame( 'budismo, colombia', $this->meta_named( $tags, 'keywords' ) );
		$this->assertSame( 'index,follow,max-image-preview:large', $this->meta_named( $tags, 'robots' ) );
		$this->assertSame( 'https://example.test/pagina', $this->link_rel( $tags, 'canonical' ) );

		$this->assertSame( 'website', $this->meta_property( $tags, 'og:type' ) );
		$this->assertSame( 'Título social', $this->meta_property( $tags, 'og:title' ) );
		$this->assertSame( 'Descripción social.', $this->meta_property( $tags, 'og:description' ) );
		$this->assertSame( 'https://example.test/pagina', $this->meta_property( $tags, 'og:url' ) );
		$this->assertSame( 'Camino del Dharma', $this->meta_property( $tags, 'og:site_name' ) );
		$this->assertSame( 'es_CO', $this->meta_property( $tags, 'og:locale' ) );
		$this->assertSame( 'https://example.test/og.jpg', $this->meta_property( $tags, 'og:image' ) );
		$this->assertSame( '1200', $this->meta_property( $tags, 'og:image:width' ) );

		$this->assertSame( 'summary_large_image', $this->meta_named( $tags, 'twitter:card' ) );
		$this->assertSame( 'Título social', $this->meta_named( $tags, 'twitter:title' ) );
		$this->assertSame( 'https://example.test/og.jpg', $this->meta_named( $tags, 'twitter:image' ) );
		$this->assertSame( 'Alt de la imagen', $this->meta_named( $tags, 'twitter:image:alt' ) );
	}

	/**
	 * Empty context values print nothing at all: a head with an empty
	 * `og:description` is worse than a head without one.
	 */
	public function test_empty_values_print_no_tag() {
		$tags = Cdd_Core_Seo_Document::tags(
			array(
				'title'       => 'Solo título',
				'description' => '',
				'keywords'    => '',
				'canonical'   => 'https://example.test/x',
				'image'       => '',
			)
		);

		$this->assertNull( $this->meta_named( $tags, 'description' ) );
		$this->assertNull( $this->meta_named( $tags, 'keywords' ) );
		$this->assertNull( $this->meta_property( $tags, 'og:image' ) );
		$this->assertNull( $this->meta_named( $tags, 'twitter:image' ) );
	}

	/**
	 * OWN-014: the generated calendar is linked only while the event is
	 * current, and never as anything but an alternate representation.
	 */
	public function test_calendar_alternate_link_is_emitted_only_when_given() {
		$with    = Cdd_Core_Seo_Document::tags(
			array(
				'title'     => 't',
				'canonical' => 'https://example.test/e',
				'alternate' => array(
					'href'  => 'https://example.test/eventos/ical/e.ics',
					'type'  => 'text/calendar',
					'title' => 'Evento',
				),
			)
		);
		$without = Cdd_Core_Seo_Document::tags(
			array(
				'title'     => 't',
				'canonical' => 'https://example.test/e',
			)
		);

		$this->assertSame( 'https://example.test/eventos/ical/e.ics', $this->link_rel( $with, 'alternate' ) );
		$this->assertNull( $this->link_rel( $without, 'alternate' ) );
	}

	/**
	 * A current event: EventScheduled, real dates, the signup offer, and
	 * one Place per city — with addressRegion only where a region is
	 * actually known.
	 */
	public function test_current_event_graph_uses_real_data_only() {
		$node = Cdd_Core_Json_Ld::event(
			array(
				'name'           => 'Círculos de Presencia Consciente',
				'description'    => 'Curso híbrido.',
				'url'            => 'https://example.test/eventos/circulos',
				'image'          => 'https://example.test/cartel.jpg',
				'start'          => '2026-09-03',
				'end'            => '2026-10-24',
				'state'          => 'current',
				'attendance'     => 'mixed',
				'places'         => array(
					array(
						'name'   => 'Bogotá',
						'region' => 'Bogotá D.C.',
					),
					array(
						'name'   => 'Cali',
						'region' => '',
					),
				),
				'signup_url'     => 'https://forms.example/x',
				'signup_payment' => false,
				'organizer'      => array( '@id' => 'https://example.test/#organization' ),
			)
		);

		$this->assertSame( 'Event', $node['@type'] );
		$this->assertSame( 'https://schema.org/EventScheduled', $node['eventStatus'] );
		$this->assertSame( 'https://schema.org/MixedEventAttendanceMode', $node['eventAttendanceMode'] );
		$this->assertSame( '2026-09-03', $node['startDate'] );
		$this->assertSame( '2026-10-24', $node['endDate'] );
		$this->assertSame( 'https://schema.org/InStock', $node['offers']['availability'] );
		$this->assertSame( '0', $node['offers']['price'] );
		$this->assertSame( 'COP', $node['offers']['priceCurrency'] );
		$this->assertSame( 'https://forms.example/x', $node['offers']['url'] );

		$this->assertSame( 'VirtualLocation', $node['location'][0]['@type'] );
		$this->assertSame( 'Bogotá D.C.', $node['location'][1]['address']['addressRegion'] );
		$this->assertArrayNotHasKey( 'addressRegion', $node['location'][2]['address'] );
		$this->assertSame( 'CO', $node['location'][2]['address']['addressCountry'] );
		$this->assertArrayNotHasKey( 'performer', $node );
	}

	/**
	 * A completed event: EventCompleted, and the signup offer is gone
	 * even if the signup URL is still stored (master prompt §10.2).
	 */
	public function test_completed_event_drops_the_signup_offer() {
		$node = Cdd_Core_Json_Ld::event(
			array(
				'name'       => '7.º Encuentro Nacional',
				'url'        => 'https://example.test/eventos/encuentro',
				'start'      => '2026-08-07',
				'end'        => '2026-08-09',
				'state'      => 'completed',
				'attendance' => 'offline',
				'places'     => array(
					array(
						'name'   => 'Puerto Colombia',
						'region' => '',
					),
				),
				'signup_url' => 'https://forms.example/x',
			)
		);

		$this->assertSame( 'https://schema.org/EventCompleted', $node['eventStatus'] );
		$this->assertArrayNotHasKey( 'offers', $node );
		$this->assertSame( 'OfflineEventAttendanceMode', substr( $node['eventAttendanceMode'], strlen( 'https://schema.org/' ) ) );
		$this->assertSame( 'Place', $node['location'][0]['@type'] );
	}

	/**
	 * An event with no end date, no cities and no poster omits those
	 * fields instead of filling them with placeholders.
	 */
	public function test_event_omits_what_it_does_not_know() {
		$node = Cdd_Core_Json_Ld::event(
			array(
				'name'  => 'Evento mínimo',
				'url'   => 'https://example.test/eventos/min',
				'start' => '2026-05-01',
				'state' => 'current',
			)
		);

		$this->assertArrayNotHasKey( 'endDate', $node );
		$this->assertArrayNotHasKey( 'location', $node );
		$this->assertArrayNotHasKey( 'image', $node );
		$this->assertArrayNotHasKey( 'eventAttendanceMode', $node );
		$this->assertArrayNotHasKey( 'offers', $node );
	}

	/**
	 * The published richness that WordPress cannot re-derive (performer,
	 * audience, additionalType) is editable data merged *under* the
	 * generated node: a generated field always wins, so nothing goes
	 * stale, and a completed event still drops the stored offer.
	 */
	public function test_stored_extra_never_overrides_generated_fields() {
		$base = array(
			'name'  => 'Nombre vigente',
			'url'   => 'https://example.test/eventos/x',
			'start' => '2026-09-03',
			'state' => 'completed',
			'extra' => array(
				'name'           => 'Nombre viejo',
				'additionalType' => 'https://schema.org/Course',
				'performer'      => array(
					'@type' => 'Person',
					'name'  => 'Venerable Maestro Zheng Gong',
				),
				'offers'         => array(
					'@type' => 'Offer',
					'price' => '0',
				),
			),
		);

		$node = Cdd_Core_Json_Ld::event( $base );

		$this->assertSame( 'Nombre vigente', $node['name'] );
		$this->assertSame( 'https://schema.org/Course', $node['additionalType'] );
		$this->assertSame( 'Venerable Maestro Zheng Gong', $node['performer']['name'] );
		$this->assertArrayNotHasKey( 'offers', $node );
	}

	/**
	 * ADR 0037 / §9.5: every author is a `Thing` pointing at its profile,
	 * and the publisher stays the site Organization.
	 */
	public function test_blog_posting_authors_are_things_and_publisher_is_the_organization() {
		$node = Cdd_Core_Json_Ld::blog_posting(
			array(
				'headline'    => 'Estamos conectados, pero seguimos solos',
				'description' => 'Extracto.',
				'url'         => 'https://example.test/blog/sangha',
				'image'       => 'https://example.test/hero.jpg',
				'published'   => '2026-08-01',
				'modified'    => '2026-08-02',
				'authors'     => array(
					array(
						'name' => 'Zheng Gong',
						'url'  => 'https://example.test/author/zheng-gong',
					),
					array(
						'name' => 'Comunidad',
						'url'  => 'https://example.test/author/comunidad',
					),
				),
				'publisher'   => array( '@id' => 'https://example.test/#organization' ),
				'tags'        => array( 'sangha' ),
			)
		);

		$this->assertSame( 'BlogPosting', $node['@type'] );
		$this->assertCount( 2, $node['author'] );
		$this->assertSame( 'Thing', $node['author'][0]['@type'] );
		$this->assertSame( 'https://example.test/author/zheng-gong', $node['author'][0]['@id'] );
		$this->assertSame( 'https://example.test/author/zheng-gong', $node['author'][0]['url'] );
		$this->assertSame( array( '@id' => 'https://example.test/#organization' ), $node['publisher'] );
		$this->assertSame( 'https://example.test/blog/sangha', $node['mainEntityOfPage'] );
		$this->assertArrayNotHasKey( 'url', $node );
		$this->assertSame( 'es-CO', $node['inLanguage'] );
		$this->assertSame( array( 'sangha' ), $node['keywords'] );
	}

	/**
	 * A single author is still an array of one; an entry without tags or
	 * featured image omits both fields (doc 15 §12.4).
	 */
	public function test_blog_posting_omits_keywords_and_image_when_absent() {
		$node = Cdd_Core_Json_Ld::blog_posting(
			array(
				'headline'  => 'Entrada',
				'url'       => 'https://example.test/blog/x',
				'published' => '2026-08-01',
				'modified'  => '2026-08-01',
				'authors'   => array(
					array(
						'name' => 'Comunidad',
						'url'  => 'https://example.test/author/c',
					),
				),
				'publisher' => array( '@id' => 'https://example.test/#organization' ),
			)
		);

		$this->assertArrayNotHasKey( 'keywords', $node );
		$this->assertArrayNotHasKey( 'image', $node );
		$this->assertCount( 1, $node['author'] );
	}

	/**
	 * Breadcrumbs are positional and always start at the home page.
	 */
	public function test_breadcrumbs_are_numbered_from_the_home_page() {
		$node = Cdd_Core_Json_Ld::breadcrumbs(
			array(
				array(
					'name' => 'Inicio',
					'url'  => 'https://example.test',
				),
				array(
					'name' => 'Eventos',
					'url'  => 'https://example.test/eventos',
				),
				array(
					'name' => 'Evento',
					'url'  => 'https://example.test/eventos/x',
				),
			)
		);

		$this->assertSame( 'BreadcrumbList', $node['@type'] );
		$this->assertSame( 1, $node['itemListElement'][0]['position'] );
		$this->assertSame( 'https://example.test/eventos/x', $node['itemListElement'][2]['item'] );
	}

	/**
	 * The payload stores production URLs; staging must never publish
	 * caminodeldharma.org as its own canonical.
	 */
	public function test_rebase_rewrites_the_production_base_everywhere() {
		$graph = Cdd_Core_Json_Ld::rebase(
			array(
				'@id'    => 'https://caminodeldharma.org/#organization',
				'url'    => 'https://caminodeldharma.org',
				'sameAs' => array( 'https://www.instagram.com/camino_del_dharma/' ),
				'logo'   => array( 'url' => 'https://caminodeldharma.org/assets/images/logo.png' ),
			),
			'https://caminodeldharma.org',
			'https://staging.example'
		);

		$this->assertSame( 'https://staging.example/#organization', $graph['@id'] );
		$this->assertSame( 'https://staging.example', $graph['url'] );
		$this->assertSame( 'https://staging.example/assets/images/logo.png', $graph['logo']['url'] );
		$this->assertSame( 'https://www.instagram.com/camino_del_dharma/', $graph['sameAs'][0] );
	}

	/**
	 * The `<title>` of the document.
	 *
	 * @param array $tags Tag list.
	 */
	private function title_of( array $tags ): ?string {
		foreach ( $tags as $tag ) {
			if ( 'title' === $tag['tag'] ) {
				return $tag['text'];
			}
		}

		return null;
	}

	/**
	 * The content of a `<meta name>` tag.
	 *
	 * @param array  $tags Tag list.
	 * @param string $name Meta name.
	 */
	private function meta_named( array $tags, string $name ): ?string {
		return $this->attribute( $tags, 'meta', 'name', $name, 'content' );
	}

	/**
	 * The content of a `<meta property>` tag.
	 *
	 * @param array  $tags     Tag list.
	 * @param string $property Meta property.
	 */
	private function meta_property( array $tags, string $property ): ?string {
		return $this->attribute( $tags, 'meta', 'property', $property, 'content' );
	}

	/**
	 * The href of a `<link rel>` tag.
	 *
	 * @param array  $tags Tag list.
	 * @param string $rel  Link relation.
	 */
	private function link_rel( array $tags, string $rel ): ?string {
		return $this->attribute( $tags, 'link', 'rel', $rel, 'href' );
	}

	/**
	 * Looks up one attribute of the first matching tag.
	 *
	 * @param array  $tags   Tag list.
	 * @param string $tag    Tag name.
	 * @param string $key    Identifying attribute.
	 * @param string $value  Identifying value.
	 * @param string $wanted Attribute to return.
	 */
	private function attribute( array $tags, string $tag, string $key, string $value, string $wanted ): ?string {
		foreach ( $tags as $candidate ) {
			if ( $tag === $candidate['tag'] && ( $candidate['attr'][ $key ] ?? null ) === $value ) {
				return $candidate['attr'][ $wanted ];
			}
		}

		return null;
	}
}
