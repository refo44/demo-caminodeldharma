/**
 * Camino del Dharma Core — «Autores del blog» panel (ADR 0037 §6, ADR 0042).
 *
 * The public byline of a blog entry is a `blog_author` ficha, not the
 * WordPress user who signed in. This panel is how an editor relates them:
 * it searches published fichas over REST from two characters on, keeps the
 * chosen order, and hands the result to `core/editor` with `editPost()` —
 * so Publicar/Actualizar carries `meta.authors` in the same REST body the
 * publication guard reads. That transport is the whole point (META-001):
 * a picker that only fills the DOM publishes a 400.
 *
 * Handwritten for the WordPress script packages (ADR 0038: no build step,
 * no JSX, no bundler).
 */
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.plugins || ! wp.data || ! wp.element || ! wp.components || ! wp.apiFetch || ! wp.i18n ) {
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
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;
	var useSelect = wp.data.useSelect;
	var Button = wp.components.Button;
	var TextControl = wp.components.TextControl;
	var Spinner = wp.components.Spinner;
	var __ = wp.i18n.__;

	var TEXT_DOMAIN = 'camino-del-dharma-core';
	var META_KEY = 'authors';
	var POST_TYPE = 'post';
	var MIN_SEARCH_LENGTH = 2;
	var SEARCH_DEBOUNCE_MS = 300;
	var RESULTS_PER_PAGE = 10;
	var PROFILES_ENDPOINT = '/wp/v2/blog_author';
	// Only published fichas are assignable (ADR 0037 §6), for every role.
	var PUBLISHED_FIELDS = '?status=publish&_fields=id,title,slug';

	/**
	 * REST path of one search. The catalogue is never preloaded: without a
	 * term of at least MIN_SEARCH_LENGTH characters no request is made.
	 *
	 * @param {string} term Search term.
	 * @return {string} REST path.
	 */
	function searchPath( term ) {
		return PROFILES_ENDPOINT + PUBLISHED_FIELDS +
			'&per_page=' + RESULTS_PER_PAGE +
			'&search=' + encodeURIComponent( term );
	}

	/**
	 * REST path that resolves the names of the fichas already related.
	 *
	 * @param {Array} ids Profile IDs.
	 * @return {string} REST path.
	 */
	function includePath( ids ) {
		return PROFILES_ENDPOINT + PUBLISHED_FIELDS +
			'&per_page=' + ids.length +
			'&include=' + ids.join( ',' );
	}

	/**
	 * The stored relation as an ordered list of unique positive IDs — the
	 * same shape the plugin sanitizes server-side.
	 *
	 * @param {*} value Raw meta value.
	 * @return {Array} Ordered unique IDs.
	 */
	function normalizeIds( value ) {
		var ids = [];

		if ( ! Array.isArray( value ) ) {
			return ids;
		}

		value.forEach( function ( candidate ) {
			var id = parseInt( candidate, 10 );

			if ( id > 0 && -1 === ids.indexOf( id ) ) {
				ids.push( id );
			}
		} );

		return ids;
	}

	/**
	 * The ficha title as text: REST renders it as HTML, and a name may
	 * carry an entity.
	 *
	 * @param {Object} profile REST record.
	 * @return {string} Decoded title.
	 */
	function decodeTitle( profile ) {
		var rendered = ( profile && profile.title && profile.title.rendered ) || '';
		var decoder = document.createElement( 'textarea' );

		decoder.innerHTML = rendered;

		return decoder.value.trim();
	}

	/**
	 * Adds REST records to the id → name index the panel renders from.
	 *
	 * @param {Object} known Current index.
	 * @param {Array}  items REST records.
	 * @return {Object} Updated index.
	 */
	function indexProfiles( known, items ) {
		var next = Object.assign( {}, known );

		( items || [] ).forEach( function ( profile ) {
			next[ profile.id ] = decodeTitle( profile );
		} );

		return next;
	}

	/**
	 * The panel itself.
	 *
	 * @return {Object|null} Element, or null outside a blog entry.
	 */
	function BlogAuthorsPanel() {
		var postType = useSelect( function ( select ) {
			return select( 'core/editor' ).getCurrentPostType();
		}, [] );
		var meta = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
		}, [] );

		var selectedIds = normalizeIds( meta[ META_KEY ] );
		var selectedKey = selectedIds.join( ',' );

		var namesState = useState( {} );
		var names = namesState[ 0 ];
		var setNames = namesState[ 1 ];

		var termState = useState( '' );
		var term = termState[ 0 ];
		var setTerm = termState[ 1 ];

		var resultsState = useState( [] );
		var results = resultsState[ 0 ];
		var setResults = resultsState[ 1 ];

		var searchingState = useState( false );
		var searching = searchingState[ 0 ];
		var setSearching = searchingState[ 1 ];

		// Resolve the names of the fichas the entry already carries.
		useEffect( function () {
			var missing = selectedIds.filter( function ( id ) {
				return undefined === names[ id ];
			} );

			if ( ! missing.length ) {
				return undefined;
			}

			var cancelled = false;

			wp.apiFetch( { path: includePath( missing ) } )
				.then( function ( items ) {
					if ( ! cancelled ) {
						setNames( function ( known ) {
							return indexProfiles( known, items );
						} );
					}
				} )
				.catch( function () {} );

			return function () {
				cancelled = true;
			};
		}, [ selectedKey ] );

		// Search, debounced, and only from MIN_SEARCH_LENGTH characters.
		useEffect( function () {
			var query = term.trim();

			if ( query.length < MIN_SEARCH_LENGTH ) {
				setResults( [] );
				setSearching( false );

				return undefined;
			}

			var cancelled = false;

			setSearching( true );

			var timer = window.setTimeout( function () {
				wp.apiFetch( { path: searchPath( query ) } )
					.then( function ( items ) {
						if ( cancelled ) {
							return;
						}
						setResults( items || [] );
						setSearching( false );
						setNames( function ( known ) {
							return indexProfiles( known, items );
						} );
					} )
					.catch( function () {
						if ( ! cancelled ) {
							setResults( [] );
							setSearching( false );
						}
					} );
			}, SEARCH_DEBOUNCE_MS );

			return function () {
				cancelled = true;
				window.clearTimeout( timer );
			};
		}, [ term ] );

		if ( POST_TYPE !== postType ) {
			return null;
		}

		/**
		 * Writes the relation through the editor store, so the next save
		 * sends it as `meta.authors` in the same REST request (META-001).
		 *
		 * @param {Array} ids Ordered profile IDs.
		 */
		function commit( ids ) {
			var nextMeta = Object.assign( {}, meta );

			nextMeta[ META_KEY ] = ids;

			wp.data.dispatch( 'core/editor' ).editPost( { meta: nextMeta } );
		}

		/**
		 * Relates one ficha, at the end of the byline.
		 *
		 * @param {Object} profile REST record.
		 */
		function attach( profile ) {
			if ( -1 !== selectedIds.indexOf( profile.id ) ) {
				return;
			}

			commit( selectedIds.concat( [ profile.id ] ) );
			setTerm( '' );
			setResults( [] );
		}

		/**
		 * Removes one ficha from the byline.
		 *
		 * @param {number} id Profile ID.
		 */
		function detach( id ) {
			commit( selectedIds.filter( function ( candidate ) {
				return candidate !== id;
			} ) );
		}

		/**
		 * Moves one ficha within the byline: the order here is the order
		 * the front prints.
		 *
		 * @param {number} index  Current position.
		 * @param {number} offset -1 up, 1 down.
		 */
		function move( index, offset ) {
			var target = index + offset;

			if ( target < 0 || target >= selectedIds.length ) {
				return;
			}

			var reordered = selectedIds.slice();

			reordered.splice( target, 0, reordered.splice( index, 1 )[ 0 ] );
			commit( reordered );
		}

		/**
		 * One row of the byline.
		 *
		 * @param {number} id    Profile ID.
		 * @param {number} index Position in the byline.
		 * @return {Object} Element.
		 */
		function renderSelected( id, index ) {
			return el(
				'li',
				{ key: id, className: 'cdd-core-blog-authors__item' },
				el( 'span', { className: 'cdd-core-blog-authors__name' }, names[ id ] || '#' + id ),
				el(
					'span',
					{ className: 'cdd-core-blog-authors__actions' },
					el( Button, {
						icon: 'arrow-up-alt2',
						size: 'small',
						label: __( 'Subir', TEXT_DOMAIN ),
						showTooltip: true,
						disabled: 0 === index,
						onClick: function () {
							move( index, -1 );
						}
					} ),
					el( Button, {
						icon: 'arrow-down-alt2',
						size: 'small',
						label: __( 'Bajar', TEXT_DOMAIN ),
						showTooltip: true,
						disabled: index === selectedIds.length - 1,
						onClick: function () {
							move( index, 1 );
						}
					} ),
					el( Button, {
						variant: 'link',
						isDestructive: true,
						size: 'small',
						onClick: function () {
							detach( id );
						}
					}, __( 'Quitar', TEXT_DOMAIN ) )
				)
			);
		}

		/**
		 * The search results, minus what is already related.
		 *
		 * @return {Object|null} Element.
		 */
		function renderResults() {
			if ( term.trim().length < MIN_SEARCH_LENGTH || searching ) {
				return null;
			}

			var available = results.filter( function ( profile ) {
				return -1 === selectedIds.indexOf( profile.id );
			} );

			if ( ! available.length ) {
				return el(
					'p',
					{ className: 'cdd-core-blog-authors__note' },
					__( 'Ninguna ficha publicada coincide con esa búsqueda.', TEXT_DOMAIN )
				);
			}

			return el(
				'ul',
				{ className: 'cdd-core-blog-authors__results' },
				available.map( function ( profile ) {
					return el(
						'li',
						{ key: profile.id },
						el( Button, {
							variant: 'link',
							onClick: function () {
								attach( profile );
							}
						}, decodeTitle( profile ) )
					);
				} )
			);
		}

		return el(
			PluginDocumentSettingPanel,
			{
				name: 'cdd-core-blog-authors',
				title: __( 'Autores del blog', TEXT_DOMAIN ),
				className: 'cdd-core-blog-authors'
			},
			el(
				'p',
				{ className: 'cdd-core-blog-authors__note' },
				__( 'La firma pública de la entrada. Es una ficha de Autores del blog, no el usuario de WordPress con el que entraste.', TEXT_DOMAIN )
			),
			selectedIds.length
				? el( 'ol', { className: 'cdd-core-blog-authors__list' }, selectedIds.map( renderSelected ) )
				: el(
					'p',
					{ className: 'cdd-core-blog-authors__note' },
					__( 'Sin autores. Para publicar hace falta al menos una ficha publicada.', TEXT_DOMAIN )
				),
			el( TextControl, {
				label: __( 'Buscar ficha de autor', TEXT_DOMAIN ),
				help: __( 'Escribe al menos dos caracteres. Solo aparecen fichas publicadas.', TEXT_DOMAIN ),
				value: term,
				onChange: setTerm,
				__nextHasNoMarginBottom: true,
				__next40pxDefaultSize: true
			} ),
			searching ? el( Spinner, null ) : null,
			renderResults()
		);
	}

	wp.plugins.registerPlugin( 'cdd-core-blog-authors', { render: BlogAuthorsPanel } );
} )( window.wp );
