import Backbone from 'Backbone';
import _ from 'underscore';
import EditForm from './edit-form';
import Common from '../../common/functions';

export default class Helper extends Backbone.View {

	initialize( model, e ) {
		this.model = model;
		this.e = e;

		this.events = {
			'click .edit'			: '_editHandler',
			'click .copy'			: '_copyHandler',
			'click .paste'			: '_pasteHandler',
			'click .delete'			: '_deleteHandler',
			'click .duplicate'		: '_duplicateHandler',
			'mousedown'				: '_closeHelperHandler'
		}

		// close other helper
		Backbone.on( 'pa-helper-open', ( data ) => {
			if ( data.model !== undefined && data.model.cid !== this.model.cid ) {
				this.model.set( 'helper-mode', false );
			}
		} );
		this.listenTo( this.model, 'change:helper-mode', this.render );
		this.listenTo( this.model, 'destroy', this.remove );

		// this.render();
	}

	render() {
		let helper = this.model.get( 'helper-mode' );
		if ( helper !== undefined && helper ) {
			let data = this.model.toJSON();
			data.cid = this.model.cid;

			let storeaged = localStorage.getItem( 'pavobuilder' );
			data.copied = false;
			if ( storeaged !== undefined ) {
				data.copied = true;
			}
			this.template = $( '#pa-helper-options' ).html();
			this.template = _.template( this.template, { variable: 'data' } )( data );
			this.setElement( this.template );

			$( 'body' ).append( this.el );

			this._calculatePosition();
			// trigger event when create new heloer
			Backbone.trigger( 'pa-helper-open', { model: this.model } );
		} else {
			this.remove();
		}

		return this;
	}

	_calculatePosition() {
		$( 'body' ).addClass( 'pa-overflow-hidden' );

		let left = this.e.clientX;
		let top = this.e.clientY;
		let width = this.$('#pa-helper-ui').width();
		let height = this.$('#pa-helper-ui').height();
		let windowWidth = $(window).width();
		let windowHeight = $(window).height();

		let mouseLeft = 0;
		if ( ( parseInt( left ) + parseInt( width ) ) > windowWidth ) {
			mouseLeft = parseInt( left ) - width - 10;
		} else {
			mouseLeft = parseInt( left ) + 10;
		}
		this.$('#pa-helper-ui').css( { left: mouseLeft } );

		let mouseTop = 0;
		if ( ( parseInt( top ) + parseInt( height ) ) > windowHeight ) {
			mouseTop = parseInt( windowHeight ) - height - 10;
		} else {
			mouseTop = parseInt( top ) - 10;
		}
		this.$('#pa-helper-ui').css( { top: mouseTop } );
	}

	_editHandler(e) {
		e.preventDefault();
		new EditForm( this.model );
		console.log(this.model.toJSON());
		this.model.set( 'editing', true );
		this._close();
		return false;
	}

	_copyHandler(e) {
		e.preventDefault();
		let cloneData = Common.toJSON( this.model.toJSON(), [], true );
		this.setClipboardText( cloneData );
		this._close();
		return false;
	}

	_pasteHandler(e) {
		e.preventDefault();
		let data = this._getlocalStorage( 'pavobuilder' );
		if ( data === false ) {
			alert( PA_PARAMS.languages.entry_no_selected );
		} else {
			data = JSON.parse( data );
			let settings = this.model.get( 'settings' );
			let widget = settings.element;
			if ( widget === 'pa_column' && ( data.widget !== 'pa_column' && data.widget !== 'pa_row' ) ) {
				this.model.get( 'elements' ).add( data );
			} else if ( widget === 'pa_column' && data.widget === 'pa_column' ) {
				if ( this.model.collection.length == 12 ) {
					alert( PA_PARAMS.languages.entry_column_is_maximum );
				} else {
					this.model.collection._addNewColumn( this.model.get( 'screen' ), $( '.pa-row-container' ).innerWidth(), data );
				}
			} else if ( data.widget === 'column' && ( widget !== 'column' && widget !== 'row' ) ) {
				let elements = data.elements !== undefined ? data.elements : {};
				this.model.collection.add( elements );
			} else {
				this.model.collection.add( data );
			}
		}

		this._close();
		return false;
	}

	_deleteHandler(e) {
		e.preventDefault();
		this._close();
		if ( confirm( $(e.target).data( 'confirm' ) ) ) {
			this.model.destroy();
		}
		return false;
	}

	_duplicateHandler(e) {

	}

	_closeHelperHandler(e) {
		if ( $(e.target).is( $('#pa-helper-overlay') ) ) {
			e.preventDefault();
			this._close();
			return false;
		}
	}

	_close() {
		$('body').removeClass( 'pa-overflow-hidden' );
		this.model.set( 'helper-mode', false );
	}

	/**
	 * set copy and save its as storeage
	 */
	setClipboardText( obj = {} ) {
		this._setlocalStorage( 'pavobuilder', JSON.stringify( obj ) );
	}

	_setlocalStorage( name = '', data = false ) {
		if ( typeof Storage !== 'undefined' ) {
			window.localStorage.setItem( name, data );
		}
	}

	_getlocalStorage( name = '', dfault = false ) {
		let value = dfault;
		if ( typeof Storage !== 'undefined' ) {
			value = window.localStorage.getItem( name ) === undefined ? value : window.localStorage.getItem( name );
		}
		return value;
	}

}