import $ from 'jquery';
import Builder from './src/views/pavobuilder';
import Common from './src/common/functions';
import Loader from './src/views/globals/loader';
import serializeJSON from 'jquery-serializejson';

let ctrlPress = false;
$( document ).ready(() => {
	// init view
	let HomePageBuilder = new Builder( window.PA_PARAMS.content );
	let loading = false;

	$( document ).on( 'submit', '#pavohomebuilder-layout-edit', ( e ) => {
		let textarea = $( '#pavo-home-pagebuilder-content' );
		textarea.text( JSON.stringify( Common.toJSON( HomePageBuilder.model.get( 'rows' ), [ 'resizabled', 'adding', 'editing', 'reRender', 'adding_position', 'screen' ] ) ) );
		return true;
	} );

	$( document ).on( 'keydown', ( e ) => {
		if ( e.keyCode === 91 ) {
			ctrlPress = true;
		} else if ( e.keyCode !== 83 ) {
			ctrlPress = false;
		}

		if ( e.keyCode === 83 && ctrlPress ) {
			e.preventDefault();
			if ( loading ) {
				return;
			}

			loading = true;
			let data = $( '#pavohomebuilder-layout-edit' ).serializeJSON();
			data.content = Common.toJSON( HomePageBuilder.model.get( 'rows' ), [ 'resizabled', 'adding', 'reRender', 'editing', 'adding_position', 'screen' ] );
			let pageTitle = $( 'title' ).text();
			let Loading = new Loader({
				loading: true,
				callback: () => {
					$.ajax({
						url: PA_PARAMS.editLayoutURL,
						type: 'POST',
						data: data,
						beforeSend: () => {
							$( 'title' ).text( PA_PARAMS.languages.updating_text );
						}
					}).always( () => {
						$( 'title' ).text( pageTitle );
						setTimeout( () => {
							loading = false;
							Loading.model.set( 'loading', false );
						}, 1500 );
					} ).done( ( res ) => {
						if ( res.success !== undefined ) {
							Loading.$( '#loader' ).removeClass( 'loading' );
							Loading.$( 'span' ).html( res.success );
						}
						if ( res.id !== undefined ) {
							if ( $( '#pavohomebuilder-layout-edit #module_id' ).length == 0 ) {
								$( '#pavohomebuilder-layout-edit' ).append( '<input type="hidden" id="module_id" name="module_id" value="'+res.id+'" />' );
								let href = window.location.href;
								href = href.replace( '/add', '' );
								window.history.pushState( '', '', href + '&module_id=' + res.id );
							} else {
								$( '#pavohomebuilder-layout-edit #module_id' ).val( res.id );
							}
						}
					} ).fail( () => {

					} );
				}
			});
			$( 'body' ).append( Loading.render().el );
			return false;
		}
	} ).on( 'keyup', ( e ) => {
		ctrlPress = false;
	} );
});