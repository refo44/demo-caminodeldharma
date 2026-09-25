/**
 * Camino del Dharma Core — «Compartir» panel (issue #39, ADR 0042).
 *
 * The public dialog already sends share_whatsapp / share_x / share_threads
 * (WU-08A). This panel is how an editor previews and rewrites those three
 * existing keys on a post or an event. It does not add meta, and it does
 * not edit the Open Graph link card (that stays in «SEO y buscadores»).
 *
 * One network at a time: the document sidebar is too narrow for three
 * boxes. The preview substitutes {{SHARE_URL}} the way share.js does, and
 * an empty field shows the same fallback the dialog sends. An event's
 * fallback title is the event type plus the name.
 *
 * Every field is written with `dispatch( 'core/editor' ).editPost( { meta } )`,
 * so Publicar/Actualizar carries the edited keys in the same REST `meta`
 * body the server persists (ADR 0042).
 *
 * Handwritten for the WordPress script packages (ADR 0038: no build step,
 * no JSX, no bundler).
 */
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.plugins || ! wp.data || ! wp.element || ! wp.components || ! wp.i18n ) {
		return;
	}

	var PluginDocumentSettingPanel =
		( wp.editor && wp.editor.PluginDocumentSettingPanel ) ||
		( wp.editPost && wp.editPost.PluginDocumentSettingPanel );

	if ( ! PluginDocumentSettingPanel ) {
		return;
	}

	var el = wp.element.createElement;
	var useSelect = wp.data.useSelect;
	var useState = wp.element.useState;
	var TextareaControl = wp.components.TextareaControl;
	var Button = wp.components.Button;
	var __ = wp.i18n.__;

	var TEXT_DOMAIN = 'camino-del-dharma-core';
	var SHARE_TYPES = [ 'post', 'event' ];
	var SHARE_URL = '{{SHARE_URL}}';
	var X_LIMIT = 280;

	var NETWORKS = [
		{ id: 'whatsapp', key: 'share_whatsapp', label: 'WhatsApp' },
		{ id: 'x', key: 'share_x', label: 'X' },
		{ id: 'threads', key: 'share_threads', label: 'Threads' }
	];

	/**
	 * Writes one meta key through the editor store, so the next save sends
	 * it as `meta[key]` in the same REST request (ADR 0042).
	 *
	 * @param {Object} meta  Current edited meta.
	 * @param {string} key   Meta key.
	 * @param {string} value New template.
	 */
	function commit( meta, key, value ) {
		var nextMeta = Object.assign( {}, meta );

		nextMeta[ key ] = value;

		wp.data.dispatch( 'core/editor' ).editPost( { meta: nextMeta } );
	}

	/**
	 * Replaces the placeholder with the address the dialog will send.
	 *
	 * @param {string} text Template or fallback.
	 * @param {string} url  Permalink, or the placeholder when none exists yet.
	 * @return {string} Message text.
	 */
	function injectShareUrl( text, url ) {
		if ( ! text ) {
			return url;
		}

		return String( text ).split( SHARE_URL ).join( url );
	}

	/**
	 * The title the public dialog shares. An event is named type first
	 * («Curso Círculos de Presencia Consciente»).
	 *
	 * @param {string} postType      Current post type.
	 * @param {string} title         Edited title.
	 * @param {string} eventTypeName First event_type term name, or ''.
	 * @return {string} Share title.
	 */
	function shareTitle( postType, title, eventTypeName ) {
		if ( 'event' === postType && eventTypeName ) {
			return eventTypeName + ' ' + title;
		}

		return title;
	}

	/**
	 * The message share.js puts in the intent for one network.
	 *
	 * @param {string} platform Network id.
	 * @param {Object} meta     Edited meta.
	 * @param {string} title    Dialog title.
	 * @param {string} url      Address to inject.
	 * @return {string} Resolved message.
	 */
	function previewMessage( platform, meta, title, url ) {
		var whatsapp = ( meta.share_whatsapp || '' ).trim();
		var xText = injectShareUrl( ( meta.share_x || '' ).trim() || title, url );
		var threadsText = injectShareUrl( ( meta.share_threads || '' ).trim() || xText, url );

		if ( 'whatsapp' === platform ) {
			return whatsapp ? injectShareUrl( whatsapp, url ) : ( title + ' ' + url );
		}

		if ( 'threads' === platform ) {
			return threadsText;
		}

		return xText;
	}

	/**
	 * The «Compartir» panel: one network, its template, and the message
	 * the public button will send.
	 *
	 * @return {Object|null} Element, or null outside a post or an event.
	 */
	function SharePanel() {
		var postType = useSelect( function ( select ) {
			return select( 'core/editor' ).getCurrentPostType();
		}, [] );
		var meta = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
		}, [] );
		var title = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'title' ) || '';
		}, [] );
		var permalink = useSelect( function ( select ) {
			return select( 'core/editor' ).getPermalink() || '';
		}, [] );
		var eventTypeName = useSelect( function ( select ) {
			if ( 'event' !== postType ) {
				return '';
			}

			var ids = select( 'core/editor' ).getEditedPostAttribute( 'event_type' ) || [];

			if ( ! ids.length ) {
				return '';
			}

			var term = select( 'core' ).getEntityRecord( 'taxonomy', 'event_type', ids[ 0 ] );

			return term && term.name ? term.name : '';
		}, [ postType ] );
		var platformState = useState( 'whatsapp' );
		var platform = platformState[ 0 ];
		var setPlatform = platformState[ 1 ];

		if ( -1 === SHARE_TYPES.indexOf( postType ) ) {
			return null;
		}

		var network = NETWORKS[ 0 ];
		NETWORKS.forEach( function ( candidate ) {
			if ( candidate.id === platform ) {
				network = candidate;
			}
		} );

		var url = permalink || SHARE_URL;
		var dialogTitle = shareTitle( postType, title, eventTypeName );
		var stored = ( meta[ network.key ] || '' ).trim();
		var preview = previewMessage( platform, meta, dialogTitle, url );
		var emptyNote = __( 'Vacío: se usa el título y la URL.', TEXT_DOMAIN );

		if ( 'threads' === platform && '' === stored && ( meta.share_x || '' ).trim() ) {
			emptyNote = __( 'Vacío: se usa el mensaje de X.', TEXT_DOMAIN );
		}

		var count = preview.length;

		return el(
			PluginDocumentSettingPanel,
			{
				name: 'cdd-core-share',
				title: __( 'Compartir', TEXT_DOMAIN ),
				className: 'cdd-core-share'
			},
			el(
				'p',
				{ className: 'cdd-core-share__note' },
				__( 'Opcional. Este es el texto que el visitante envía desde el botón Compartir. Si lo dejas vacío se envía el título y la URL. La ficha que aparece al pegar el enlace se edita en SEO y buscadores.', TEXT_DOMAIN )
			),
			el(
				'div',
				{
					className: 'cdd-core-share__networks',
					role: 'group',
					'aria-label': __( 'Red', TEXT_DOMAIN )
				},
				NETWORKS.map( function ( item ) {
					return el( Button, {
						key: item.id,
						variant: item.id === platform ? 'primary' : 'secondary',
						isPressed: item.id === platform,
						onClick: function () {
							setPlatform( item.id );
						}
					}, item.label );
				} )
			),
			el( TextareaControl, {
				label: network.label,
				help: __( '{{SHARE_URL}} se sustituye por la dirección de esta entrada o evento.', TEXT_DOMAIN ),
				value: meta[ network.key ] || '',
				onChange: function ( value ) {
					commit( meta, network.key, value );
				},
				__nextHasNoMarginBottom: true
			} ),
			el(
				'p',
				{ className: 'cdd-core-share__preview-label' },
				__( 'Así se enviará', TEXT_DOMAIN )
			),
			el( 'p', { className: 'cdd-core-share__preview' }, preview ),
			'' === stored ? el( 'p', { className: 'cdd-core-share__empty' }, emptyNote ) : null,
			'x' === platform ? el(
				'p',
				{ className: 'cdd-core-share__count' },
				count + ' ' + __( 'caracteres', TEXT_DOMAIN )
			) : null,
			'x' === platform && count > X_LIMIT ? el(
				'p',
				{ className: 'cdd-core-share__count-note' },
				__( 'Supera 280 caracteres. X puede recortar el texto. Se puede guardar igual.', TEXT_DOMAIN )
			) : null
		);
	}

	wp.plugins.registerPlugin( 'cdd-core-share-panel', { render: SharePanel } );
} )( window.wp );
