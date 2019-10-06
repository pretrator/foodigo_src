import Backbone from 'Backbone';
import _ from 'underscore';

export default class ElementsPopup extends Backbone.View {

	/**
	 * Initialize popup class
	 */
	initialize( column = {} ) {
		this.column = column;

		// trigger remove popup when model have been destroyed
		this.listenTo( this.column, 'destroy', this.remove );
		this.listenTo( this.column, 'change:adding', this._toggleShow );

		this.events = {
			'click .element-item'	: '_addElementHandler',
			'keyup #search-text'	: '_searchHandler'
		};

		// render after class called
		this.render();
	}

	/**
	 * Render popup
	 */
	render() {
		if ( this.column.get( 'adding' ) ) {
			this.template = $( '#pa-elements-panel' ).html();
			this.template = _.template( this.template, { variable: 'data' } )( this.column.toJSON() );
			this.setElement( this.template );

			$( 'body' ).append( this.el );
			// calculate height
			$( 'body' ).find( this.$el ).modal( 'show' );
			$( 'body' ).find( this.$el ).on( 'hidden.bs.modal', ( e ) => {
				this.column.set( 'adding', false );
			} );

			this.$el.find( '.pa-col-sm-3' ).map( ( index, element ) => {
				let clone = $( element ).clone();
				$( '#nav-elements-all' ).append( clone );
			} );
		}

		return this;
	}

	/**
	 * Toggle show popup
	 */
	_toggleShow( model ) {
		if ( ! model.get( 'adding' ) ) {
			this.remove();
		}
	}

	/**
	 * Close
	 */
	_close() {
		$( 'body' ).find( this.$el ).modal( 'hide' );
		this.column.set( 'adding', false );
	}

	/**
	 * Add element to current column
	 */
	_addElementHandler( e ) {
		e.preventDefault();
		let button = $( e.target );
		if ( e.target.nodeName !== 'A' ) {
			button = $( e.target ).parents( 'a:first' );
		}

		let settings = button.data();
		if ( settings.elements !== undefined ) {
			delete settings.elements;
		}

		if ( settings.widget === 'pa_row' ) {
			settings.row = {
				settings: {
					element: 'pa_row'
				},
				columns: [
					{
						settings: {
							element: 'pa_column'
						},
						elements: [],
						responsive: {
							lg: {
								cols: 12
							},
							md: {
								cols: 12
							},
							sm: {
								cols: 12
							},
							xs: {
								cols: 12
							}
						}
					}
				]
			};
		}

		if ( settings.widget === 'pa_tabs' ) {
			settings.tabs = {
				row: {
					settings: {},
					columns: [
						{
							settings: {
								element: 'pa_column'
							},
							elements: [],
							responsive: {
								lg: {
									cols: 12
								},
								md: {
									cols: 12
								},
								sm: {
									cols: 12
								},
								xs: {
									cols: 12
								}
							}
						}
					]
				}
			}
		}

		// add new element to column
		let newModel = this.column.get( 'elements' ).add( settings, { at: this.column.get( 'adding_position' ) } );
		if ( newModel && settings.widget !== 'pa_row' && settings.widget !== 'pa_tabs' ) {
			newModel.set( 'create_edit_form', true );
		}
		// set 'reRender' true to re-generate column
		// this.column.set( 'reRender', true );
		this.column.set( 'adding_position', false );
		// close model
		this._close();
		return false;
	}

	/**
	 * search hanlder
	 */
	_searchHandler( e ) {
		e.preventDefault();
		let input = $( e.target );
		let val = input.val().toLowerCase();

		$( 'a[href="#nav-elements-all"]' ).click();

		let elements = this.$( '.element-item' );
		for ( let i = 0; i < elements.length; i++ ) {
			let element = $( elements[i] );
			let name = element.find( 'span' ).text().toLowerCase();
			let code = element.find( 'code' ).text().toLowerCase();
			if ( name.indexOf( val ) !== -1 || code.indexOf( val ) !== -1 ) {
				element.parent().removeClass( 'hide' );
			} else {
				element.parent().addClass( 'hide' );
			}
		}
		return false;
	}

}