import $ from 'jquery';
import { View, Model } from 'Backbone';
import serializeJSON from 'jquery-serializejson';
import _ from 'underscore';

/**
 * Customize class
 */
class ThemeCustomize extends View {

	_eventsCollection = {}

	ajax = false

	constructor() {
		super();
		console.debug( 'PavoTheme Customize was initialized!!!' );
		this.$el = $( document );
		this.events = {
			'click .btn-save'										: '_onSaveHandler',
			'click .btn-preview'									: 'applyPreview',
			'change .skin'											: '_onChangeSkin',
			'click .btn-clone-skin'									: '_cloneSkin',
			'click #form > .panel-group .clear'						: '_clear',
			'click #form > .panel-default .panel-body .clear'		: '_deleteProfileHandler'
		};
		this.model = false;
		this.listenTo( this.model, 'change:skin', ( model ) => {
			let skin = model.get( 'skin' );
			if ( skin ) {
				this._addStyle( 'skin', PavoCustomize.skins[skin].link );
			}
		} );
		this.delegateEvents();
	}

	render() {
		if ( ! this.model ) {
			let skinData = window.PavoCustomize !== undefined && window.PavoCustomize.current_skin ? window.PavoCustomize.skins[window.PavoCustomize.current_skin] : {};
			this.model = new Model( skinData );
			this.model.set( 'skin', window.PavoCustomize.current_skin );
		}
		if ( this.model.get( 'skin' ) ) {
			setTimeout( () => {
				this._addStyle( 'skin', window.PavoCustomize.base_skin_url + this.model.get( 'skin' ) + '.css' );
			}, 1000 );
		}
	}

	customize( type = '', callback = '' ) {

		this._eventsCollection[type] = callback;
		this.events['change [name="customize['+type+']"]'] = ( e ) => {
			this._onChange( type, $( e.target ).val() );
		};
		this.delegateEvents();

		return this;
	}

	_onSaveHandler( e ) {
		e.preventDefault();
		let button = $( e.target );
		let name = $( '.skin option:selected' ).text().trim();
		if ( name == 'None' ) {
			name = prompt( PavoCustomize.text_skin_name );
		}
		if ( name ) {

			if ( this.ajax ) {
				this.ajax.abort();
				this.ajax = false;
			}

			let data = $('#form').serializeJSON();
			data.name = name;

			this.ajax = $.ajax({
				url: PavoCustomize.update_url,
				type: 'POST',
				data: data,
				dataType:'json',
				beforeSend: () => {
				 	// button.button('loading');
				 	button.addClass( 'disabled' );
				 	button.prepend( '<i class="fa fa-circle-o-notch fa-spin"></i>' );
				}
			}).always( () => {
			 	// button.button('reset');
			 	button.removeClass( 'disabled' );
			 	button.find( 'i' ).remove();
			} ).done( ( data ) => {

				if( data.status == true ) {
					this._addStyle( 'custom-preview', data.file );
				}
				if ( data.type !== 'updated' ) {
					$('.skin')
			         	.append( $("<option></option>")
			         	.attr( "value", data.filename )
			         	.attr( 'selected', true )
			         	.text( data.name ) );
				}
			} ).fail( ( xhr, ajaxOptions, thrownError ) => {
			  	alert( xhr.responseText );
			} );
		}
		return false;
	}

	applyPreview( e ) {
		e.preventDefault();
		let button = $( e.target );
		$("#mode-change").val('preview');

		if ( this.ajax ) {
			this.ajax.abort();
			this.ajax = false;
		}

		this.ajax = $.ajax({
			url: $('#form').attr('action'),
			type: 'POST',
			data: $('#form').serialize() + "&ajax=1&mode=preview",
			dataType:'json',
			beforeSend: () => {
			 	button.addClass( 'disabled' );
			 	button.prepend( '<i class="fa fa-circle-o-notch fa-spin"></i>' );
			 	// button.button('loading');
			}
		}).always( () => {
		 	button.removeClass( 'disabled' );
		 	button.find( 'i' ).remove();
		 	// button.button('reset');
		} ).done( ( data ) => {

			if( data.status == true ) {
				this._addStyle( 'custom-preview', data.file );
			}
		} ).fail( ( xhr, ajaxOptions, thrownError ) => {
		  	alert( xhr.responseText );
		} );

		return false;
	}

	_onChangeSkin( e ) {
		e.preventDefault();
		let select = $( e.target );
		let val = select.val();
		this.model.set( 'skin', val );

		if ( ! val ) {
			$( '.btn-clone-skin' ).attr( 'disabled', true );
		} else {
			$( '.btn-clone-skin' ).attr( 'disabled', false );
		}
		let href = window.location.href.replace( /(&)(skin=.*?)[^&]/i, '' ) + '&skin=' + val;
		window.location.href = PavoCustomize.customize_url + '&skin=' + val;
		return false;
	}

	_cloneSkin( e ) {
		e.preventDefault();

		let button = $( e.target );
		let name = prompt( PavoCustomize.text_skin_name );
		if ( name ) {

			if ( this.ajax ) {
				this.ajax.abort();
				this.ajax = false;
			}

			let data = $('#form').serializeJSON();
			data.name = name;
			data.clone = $( '.skin' ).val();

			this.ajax = $.ajax({
				url: PavoCustomize.update_url,
				type: 'POST',
				data: data,
				dataType:'json',
				beforeSend: () => {
				 	button.addClass( 'disabled' );
				 	// button.button('loading');
				}
			}).always( () => {
			 	button.removeClass( 'disabled' );
			 	// button.button('reset');
			} ).done( ( data ) => {

				if( data.status == true ) {
					this._addStyle( 'custom-preview', data.file );
				}
				$('.skin')
		         	.append( $("<option></option>")
		         	.attr( 'value', data.filename )
		         	.attr( 'selected', true )
		         	.text( data.name ) );
			} ).fail( ( xhr, ajaxOptions, thrownError ) => {
			  	alert( xhr.responseText );
			} );
		}

		return false;
	}

	_onChange( type = '', val = '' ) {
		new Promise( ( resolve, reject ) => {
			let refresh = this.model.get( type ) !== val;
			this.model.set( type, val );
			console.log( type + ' was changed to ' + val );
			if ( this._eventsCollection[type] !== undefined ) {
				refresh = this._eventsCollection[type]( this, val, refresh, document.getElementById( 'pavo-iframe' ).contentDocument );
			}

			resolve( refresh );
		} ).then( ( refresh ) => {
			// refresh
			if ( refresh ) {
				this.refresh();
			}
		} );
	}

	_addScript( id = '', href = '' ) {

	}

	_addStyle( id = '', href = '' ) {
		id = 'pavo-preview-' + id;
		let iframe = $("#pavo-iframe").contents();
		let head = iframe.find('head');
		if ( iframe.find( '#' + id ).length > 0 ) {
			iframe.find( '#' + id ).remove();
		}
		$( head ).append( $('<link href="'+ href +'" id="'+id+'" type="text/css" rel="stylesheet" media="screen" />') );
	}

	_removeStyle( id = '' ) {
		id = 'pavo-preview-' + id;
		let iframe = $("#pavo-iframe").contents();
		if ( iframe.find( '#' + id ).length > 0 ) {
			iframe.find( '#' + id ).remove();
		}
	}

	/**
	 * Refresh Iframe
	 * @since 1.0.0
	 */
	refresh() {
		document.getElementById( 'pavo-iframe' ).contentDocument.location.reload( true );
	}

	_clear( e ) {
		e.preventDefault();
		let group = $( e.target ).parents( '.panel-body:first' );
		let input = group.find( 'input, select' );
		input.val('');
		return false;
	}

	_deleteProfileHandler( e ) {
		e.preventDefault();
		let button = $( e.target );
		let select = button.parent().find( '.skin' );
		let skin = select.val();

		if ( confirm( PavoCustomize.text_delete_skin ) ) {
			if ( this.ajax ) {
				this.ajax.abort();
			}

			this.ajax = $.ajax({
				url: PavoCustomize.delete_skin_url,
				type: 'POST',
				data: {
					skin: skin
				},
				dataType:'json',
				beforeSend: () => {
				 	select.addClass( 'disabled' );
				}
			}).always( () => {
			 	select.removeClass( 'disabled' );
			} ).done( ( data ) => {
				if ( data.status !== undefined && data.status ) {
					window.location.reload();
				} else if ( data.message !== undefined ) {
					alert( data.message );
				}
			} ).fail( ( xhr, ajaxOptions, thrownError ) => {
			  	alert( xhr.responseText );
			} );
		}
		return false;
	}

}

export default new ThemeCustomize();