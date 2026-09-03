/**
 * Camino del Dharma Core — SEO and event structured-data panels
 * (META-002 / META-003, ADR 0042; docs/15-assets-strategy.md §12).
 *
 * The published head copy and the event's machine-readable data are meta
 * an editor rewrites from wp-admin (ADR 0034, OWN-007), never strings a
 * template regenerates. These native `PluginDocumentSettingPanel`s are how
 * that editing happens in the block editor:
 *
 * 1. «SEO y buscadores» — for post, page, event and blog_author: the head
 *    title, description, keywords, Open Graph copy and the related-URL that
 *    `includes/seo.php` prints at request time.
 * 2. «Datos del evento (schema.org)» — for an event only: the dates, place,
 *    schedule, attendance mode and sign-up data the JSON-LD `Event` node
 *    and the generated `/eventos/ical/{slug}.ics` already read. No new
 *    domain key is invented here.
 *
 * Every field is written with `dispatch( 'core/editor' ).editPost( { meta } )`,
 * so Publicar/Actualizar carries the edited keys in the same REST `meta`
 * body the server persists — a panel that only fills the DOM saves nothing
 * (ADR 0042 · META-005). The JSON-LD of a blog_author stays `Thing`
 * (doc 15 §12.4): a profile gets a richer head, never a promoted @type.
 *
 * Handwritten for the WordPress script packages (ADR 0038: no build step,
 * no JSX, no bundler).
 */
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.plugins || ! wp.data || ! wp.element || ! wp.components || ! wp.i18n ) {
		return;
	}

	// The panel moved from `wp.editPost` to `wp.editor`; support both.
	var PluginDocumentSettingPanel =
		( wp.editor && wp.editor.PluginDocumentSettingPanel ) ||
		( wp.editPost && wp.editPost.PluginDocumentSettingPanel );

	if ( ! PluginDocumentSettingPanel ) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useSelect = wp.data.useSelect;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var SelectControl = wp.components.SelectControl;
	var ToggleControl = wp.components.ToggleControl;
	var Button = wp.components.Button;
	var __ = wp.i18n.__;

	var TEXT_DOMAIN = 'camino-del-dharma-core';

	// The public types whose head is editable copy (includes/seo.php).
	var HEAD_TYPES = [ 'post', 'page', 'event', 'blog_author' ];
	var EVENT_TYPE = 'event';

	// Head meta keys — the exact set `cdd_core_register_seo_meta()` registers
	// and `cdd_core_seo_singular_context()` reads.
	var SEO_TITLE = 'seo_title';
	var SEO_DESCRIPTION = 'seo_description';
	var SEO_KEYWORDS = 'seo_keywords';
	var OG_TITLE = 'og_title';
	var OG_DESCRIPTION = 'og_description';
	var SEO_RELATED_URL = 'seo_related_url';

	// Event structured-data keys — read by `cdd_core_seo_event_node()` and
	// `cdd_core_event_calendar_payload()`.
	var EVENT_DATE = 'event_date';
	var EVENT_END = 'event_end';
	var EVENT_PLACE = 'event_place';
	var EVENT_MODALITY = 'event_modality';
	var EVENT_ATTENDANCE_MODE = 'event_attendance_mode';
	var EVENT_STATUS = 'event_status';
	var EVENT_SIGNUP_URL = 'event_signup_url';
	var EVENT_SIGNUP_PAYMENT = 'event_signup_payment';
	var EVENT_FEATURED = 'event_featured';
	var EVENT_CALENDAR_DATES = 'event_calendar_dates';

	var DESCRIPTION_LIMIT = 155;

	/**
	 * Writes one meta key through the editor store, so the next save sends
	 * it as `meta[key]` in the same REST request (ADR 0042).
	 *
	 * @param {Object} meta  Current edited meta.
	 * @param {string} key   Meta key.
	 * @param {*}      value New value.
	 */
	function commit( meta, key, value ) {
		var nextMeta = Object.assign( {}, meta );

		nextMeta[ key ] = value;

		wp.data.dispatch( 'core/editor' ).editPost( { meta: nextMeta } );
	}

	/**
	 * Plain text of an HTML/blocks string: what the front would print if no
	 * head copy were stored. Mirrors `cdd_core_seo_derive_description()`.
	 *
	 * @param {string} html Raw excerpt or content.
	 * @return {string} Collapsed plain text.
	 */
	function toPlainText( html ) {
		var holder = document.createElement( 'div' );

		holder.innerHTML = String( html || '' );

		return ( holder.textContent || holder.innerText || '' ).replace( /\s+/g, ' ' ).trim();
	}

	/**
	 * Trims to a length on a word boundary, with an ellipsis.
	 *
	 * @param {string} text  Source text.
	 * @param {number} limit Character budget.
	 * @return {string} Trimmed text.
	 */
	function truncate( text, limit ) {
		if ( text.length <= limit ) {
			return text;
		}

		var clipped = text.slice( 0, limit );
		var lastSpace = clipped.lastIndexOf( ' ' );

		if ( lastSpace > 0 ) {
			clipped = clipped.slice( 0, lastSpace );
		}

		// Mirror PHP `rtrim( $clipped, " \t\n\r\0\x0B.,;:" )`: drop trailing
		// spaces and sentence punctuation with a linear scan, no backtracking
		// regex.
		var trailing = ' \t\n\r\x00\x0B.,;:';
		var end = clipped.length;

		while ( end > 0 && trailing.indexOf( clipped.charAt( end - 1 ) ) !== -1 ) {
			end -= 1;
		}

		return clipped.slice( 0, end ) + '…';
	}

	/**
	 * The description the publish-time backfill would store for this post,
	 * shown as a preview while `seo_description` is empty.
	 *
	 * @param {string} excerpt Edited excerpt.
	 * @param {string} content Edited content.
	 * @return {string} Derived description, or ''.
	 */
	function derivedDescription( excerpt, content ) {
		var source = toPlainText( excerpt );

		if ( '' === source ) {
			source = toPlainText( content );
		}

		if ( '' === source ) {
			return '';
		}

		return truncate( source, DESCRIPTION_LIMIT );
	}

	/**
	 * A plain-text meta field bound to the editor store.
	 *
	 * @param {Object} meta  Current edited meta.
	 * @param {string} key   Meta key.
	 * @param {string} label Field label.
	 * @param {Object} extra Extra control props.
	 * @return {Object} Element.
	 */
	function metaText( meta, key, label, extra ) {
		var props = {
			label: label,
			value: meta[ key ] || '',
			onChange: function ( value ) {
				commit( meta, key, value );
			},
			__nextHasNoMarginBottom: true,
			__next40pxDefaultSize: true
		};

		return el( TextControl, Object.assign( props, extra || {} ) );
	}

	/**
	 * A multi-line meta field bound to the editor store.
	 *
	 * @param {Object} meta  Current edited meta.
	 * @param {string} key   Meta key.
	 * @param {string} label Field label.
	 * @param {string} help  Help text.
	 * @return {Object} Element.
	 */
	function metaTextarea( meta, key, label, help ) {
		return el( TextareaControl, {
			label: label,
			help: help || undefined,
			value: meta[ key ] || '',
			onChange: function ( value ) {
				commit( meta, key, value );
			},
			__nextHasNoMarginBottom: true
		} );
	}

	/**
	 * A single-choice meta field bound to the editor store. The first option
	 * is the value used when the meta is unset.
	 *
	 * @param {Object} meta    Current edited meta.
	 * @param {string} key     Meta key.
	 * @param {string} label   Field label.
	 * @param {Array}  options `{ value, label }` choices.
	 * @param {Object} extra   Extra control props (e.g. `help`).
	 * @return {Object} Element.
	 */
	function metaSelect( meta, key, label, options, extra ) {
		var props = {
			label: label,
			value: meta[ key ] || options[ 0 ].value,
			options: options,
			onChange: function ( value ) {
				commit( meta, key, value );
			},
			__nextHasNoMarginBottom: true,
			__next40pxDefaultSize: true
		};

		return el( SelectControl, Object.assign( props, extra || {} ) );
	}

	/**
	 * A boolean meta field bound to the editor store.
	 *
	 * @param {Object} meta  Current edited meta.
	 * @param {string} key   Meta key.
	 * @param {string} label Field label.
	 * @param {string} help  Help text.
	 * @return {Object} Element.
	 */
	function metaToggle( meta, key, label, help ) {
		return el( ToggleControl, {
			label: label,
			help: help || undefined,
			checked: !! meta[ key ],
			onChange: function ( value ) {
				commit( meta, key, !! value );
			},
			__nextHasNoMarginBottom: true
		} );
	}

	/**
	 * The «SEO y buscadores» panel: the editable head of any public type.
	 *
	 * @return {Object|null} Element, or null outside the head types.
	 */
	function HeadPanel() {
		var postType = useSelect( function ( select ) {
			return select( 'core/editor' ).getCurrentPostType();
		}, [] );
		var meta = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
		}, [] );
		var excerpt = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'excerpt' ) || '';
		}, [] );
		var content = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'content' ) || '';
		}, [] );

		if ( -1 === HEAD_TYPES.indexOf( postType ) ) {
			return null;
		}

		var preview = '' === ( meta[ SEO_DESCRIPTION ] || '' ) ? derivedDescription( excerpt, content ) : '';
		var descriptionHelp = preview
			? __( 'Si lo dejas vacío, al publicar se guardará: ', TEXT_DOMAIN ) + '“' + preview + '”'
			: __( 'Resumen de una o dos frases. Es la descripción que ven los buscadores.', TEXT_DOMAIN );

		return el(
			PluginDocumentSettingPanel,
			{
				name: 'cdd-core-seo-head',
				title: __( 'SEO y buscadores', TEXT_DOMAIN ),
				className: 'cdd-core-seo-head'
			},
			el(
				'p',
				{ className: 'cdd-core-seo-head__note' },
				__( 'El texto publicado de esta página en buscadores y redes. Si lo dejas vacío se usa el contenido real de la página, nunca texto inventado.', TEXT_DOMAIN )
			),
			metaText( meta, SEO_TITLE, __( 'Título SEO', TEXT_DOMAIN ), {
				help: __( 'Si lo dejas vacío se usa el título de la página más el nombre del sitio.', TEXT_DOMAIN )
			} ),
			metaTextarea( meta, SEO_DESCRIPTION, __( 'Descripción SEO', TEXT_DOMAIN ), descriptionHelp ),
			metaText( meta, SEO_KEYWORDS, __( 'Palabras clave', TEXT_DOMAIN ), {
				help: __( 'Separadas por comas. Opcional.', TEXT_DOMAIN )
			} ),
			metaText( meta, OG_TITLE, __( 'Título para redes (Open Graph)', TEXT_DOMAIN ), {
				help: __( 'Si lo dejas vacío se usa el título SEO.', TEXT_DOMAIN )
			} ),
			metaTextarea( meta, OG_DESCRIPTION, __( 'Descripción para redes (Open Graph)', TEXT_DOMAIN ), __( 'Si lo dejas vacío se usa la descripción SEO.', TEXT_DOMAIN ) ),
			metaText( meta, SEO_RELATED_URL, __( 'URL relacionada', TEXT_DOMAIN ), {
				type: 'url',
				inputMode: 'url',
				help: __( 'Enlace canónico relacionado (rel=related). Opcional.', TEXT_DOMAIN )
			} )
		);
	}

	/**
	 * The list of schedule sessions the calendar marks (`event_calendar_dates`).
	 *
	 * @param {Object} meta Current edited meta.
	 * @return {Object} Element.
	 */
	function renderCalendarDates( meta ) {
		var dates = Array.isArray( meta[ EVENT_CALENDAR_DATES ] ) ? meta[ EVENT_CALENDAR_DATES ] : [];

		function write( next ) {
			commit( meta, EVENT_CALENDAR_DATES, next );
		}

		return el(
			'div',
			{ className: 'cdd-core-seo-event__dates' },
			el(
				'p',
				{ className: 'cdd-core-seo-event__label' },
				__( 'Sesiones del cronograma', TEXT_DOMAIN )
			),
			el(
				'p',
				{ className: 'cdd-core-seo-event__note' },
				__( 'Una fila por sesión. El archivo .ics lleva un VEVENT por cada una; si lo dejas vacío, el calendario usa el rango de fechas.', TEXT_DOMAIN )
			),
			dates.map( function ( value, index ) {
				return el(
					'div',
					{ key: index, className: 'cdd-core-seo-event__date' },
					el( TextControl, {
						type: 'date',
						label: __( 'Sesión', TEXT_DOMAIN ) + ' ' + ( index + 1 ),
						hideLabelFromVision: true,
						value: value || '',
						onChange: function ( next ) {
							var copy = dates.slice();
							copy[ index ] = next;
							write( copy );
						},
						__nextHasNoMarginBottom: true,
						__next40pxDefaultSize: true
					} ),
					el( Button, {
						variant: 'link',
						isDestructive: true,
						size: 'small',
						onClick: function () {
							write( dates.filter( function ( item, i ) {
								return i !== index;
							} ) );
						}
					}, __( 'Quitar', TEXT_DOMAIN ) )
				);
			} ),
			el( Button, {
				variant: 'secondary',
				size: 'small',
				onClick: function () {
					write( dates.concat( [ '' ] ) );
				}
			}, __( 'Añadir sesión', TEXT_DOMAIN ) )
		);
	}

	/**
	 * The «Datos del evento (schema.org)» panel: the machine-readable data
	 * of an event, complementing its native title, content and poster.
	 *
	 * @return {Object|null} Element, or null outside an event.
	 */
	function EventPanel() {
		var postType = useSelect( function ( select ) {
			return select( 'core/editor' ).getCurrentPostType();
		}, [] );
		var meta = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
		}, [] );

		if ( EVENT_TYPE !== postType ) {
			return null;
		}

		return el(
			PluginDocumentSettingPanel,
			{
				name: 'cdd-core-seo-event',
				title: __( 'Datos del evento (schema.org)', TEXT_DOMAIN ),
				className: 'cdd-core-seo-event'
			},
			metaText( meta, EVENT_DATE, __( 'Fecha de inicio', TEXT_DOMAIN ), { type: 'date' } ),
			metaText( meta, EVENT_END, __( 'Fecha de fin', TEXT_DOMAIN ), { type: 'date' } ),
			metaText( meta, EVENT_PLACE, __( 'Lugar', TEXT_DOMAIN ), {
				help: __( 'Dirección o sede. Aparece como LOCATION en el archivo .ics.', TEXT_DOMAIN )
			} ),
			metaText( meta, EVENT_MODALITY, __( 'Modalidad (texto publicado)', TEXT_DOMAIN ), {
				help: __( 'Texto descriptivo tal como se publica. No cambia el JSON-LD.', TEXT_DOMAIN )
			} ),
			metaSelect( meta, EVENT_ATTENDANCE_MODE, __( 'Modalidad para schema.org', TEXT_DOMAIN ), [
				{ value: '', label: __( 'Sin especificar', TEXT_DOMAIN ) },
				{ value: 'offline', label: __( 'Presencial', TEXT_DOMAIN ) },
				{ value: 'online', label: __( 'Virtual', TEXT_DOMAIN ) },
				{ value: 'mixed', label: __( 'Híbrida', TEXT_DOMAIN ) }
			] ),
			metaSelect( meta, EVENT_STATUS, __( 'Estado editorial', TEXT_DOMAIN ), [
				{ value: 'vigente', label: __( 'Vigente', TEXT_DOMAIN ) },
				{ value: 'finalizado', label: __( 'Finalizado', TEXT_DOMAIN ) },
				{ value: 'cancelado', label: __( 'Cancelado', TEXT_DOMAIN ) }
			], {
				help: __( 'El estado real se recalcula por fecha en cada visita (OWN-013); solo «cancelado» tiene efecto editorial.', TEXT_DOMAIN )
			} ),
			metaText( meta, EVENT_SIGNUP_URL, __( 'URL de inscripción', TEXT_DOMAIN ), {
				type: 'url',
				inputMode: 'url'
			} ),
			metaToggle( meta, EVENT_SIGNUP_PAYMENT, __( 'La inscripción tiene un pago', TEXT_DOMAIN ) ),
			metaToggle( meta, EVENT_FEATURED, __( 'Evento destacado en el inicio', TEXT_DOMAIN ) ),
			renderCalendarDates( meta )
		);
	}

	/**
	 * Both panels, so a single plugin registration covers head and event.
	 *
	 * @return {Object} Element.
	 */
	function SeoPanels() {
		return el( Fragment, null, el( HeadPanel, null ), el( EventPanel, null ) );
	}

	wp.plugins.registerPlugin( 'cdd-core-seo-panels', { render: SeoPanels } );
} )( window.wp );
