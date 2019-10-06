import Backbone from 'Backbone';
import _ from 'underscore';
import Row from './row';
import $ from 'jquery';
import EditForm from './globals/edit-form';
import Helper from './globals/helper';

export default class Element extends Backbone.View {

	initialize( element = {} ) {
		this.element = element;

		this.events = {
			'click .pa-delete:not(.pa-delete-row)'	: '_removeHandler',
			'click .pa-edit:not(.pa-edit-row)'		: '_editHandler',
			'click .pa-edit-column-num'				: '_changeColumnsInnerHandler',
			'click .pa-reorder'						: () => {
				return false;
			},
			'contextmenu'							: '_openHelperHandler'
		};
		this.listenTo( this.element, 'destroy', this.remove );
		this.listenTo( this.element, 'change:reRender', this.reRender );
		this.listenTo( this.element.get( 'row' ), 'destroy', () => {
			this.element.destroy();
		} );
		this.listenTo( this.element, 'change:create_edit_form', ( model, val ) => {
			if ( val === true ) {
				this.editForm = new EditForm( this.element, PA_PARAMS.languages.entry_edit_element_text );
				model.set( 'editing', true );
				model.unset( 'create_edit_form' );
			}
		} );
		// this.editForm = new EditForm( this.element, PA_PARAMS.languages.entry_edit_element_text );
	}

	/**
	 * render html
	 */
	render() {
		let data = this.element.toJSON();
		data.cid = this.element.cid;
		if ( this.element.get( 'row' ) !== undefined ) {
			this.template = '<div class="pa-element-content pa_row" data-cid="' + data.cid + '" data-confirm="' + PA_PARAMS.languages.confirm_element_column + '"></div>';
			this.setElement( this.template );

			this.$el.html( new Row( this.element.get( 'row' ) ).render().el );
			this.$( '.pa-row-container' ).addClass( 'disable-sortable' );
		} else {
			let widget = this.element.get( 'widget' );
			if ( widget && PA_PARAMS.element_mask[widget] !== undefined ) {
				data = { ...data, ...PA_PARAMS.element_mask[widget] };
			}

			if ( widget && PA_PARAMS.element_fields[widget] !== undefined ) {
				let mask_desc = {};
				_.map( PA_PARAMS.element_fields, ( groups, widgetName ) => {
					_.map( groups, ( groupFi, group ) => {
						_.map( groupFi.fields, ( field, index ) => {
							if ( typeof field.mask !== undefined && field.mask && data.settings[field.name] ) {
								mask_desc[field.name] = field.label + ':' + data.settings[field.name];
							}
						} );
					} )
				} );
				data.mask_desc = Object.values( mask_desc ).join( ', ' );
			}

			this.template = _.template( $( '#pa-element-template' ).html(), { variable: 'data' } )( data );
			this.setElement( this.template );
		}
		return this;
	}

	/**
	 * Remove click handler
	 */
	_removeHandler( e ) {
		e.preventDefault();
		if ( confirm( this.$el.data( 'confirm' ) ) ) {
			this.element.destroy();
		}
		return false;
	}

	/**
	 * Edit click handler
	 */
	_editHandler( e ) {
		e.preventDefault();
		if ( this.element.get( 'editing' ) !== true ) {
			this.editForm = new EditForm( this.element, PA_PARAMS.languages.entry_edit_element_text );
			this.element.set( 'editing', true );
		}
		return false;
	}

	/**
	 * re-render if model has changed
	 */
	reRender( model, old ) {
		if ( model.get( 'reRender' ) === true ) {
			this.$el.replaceWith( this.render().el );
			this.element.set( 'reRender', false );
		}
	}

	_changeColumnsInnerHandler( e ) {
		e.preventDefault();
		if ( ! this.element.get( 'widget' ) || this.element.get( 'widget' ) != 'pa_row' )
			return false;
		let button = $( e.target );
		let columns_count = button.data('columns');
		let classWrapper = 'pa-col-sm-' + Math.floor( 12 / parseInt( columns_count ) );

		let newColumnsObject = [];
		for ( let i = 0; i < columns_count; i++ ) {
			newColumnsObject.push({
				class: classWrapper
			});
		}

		if ( newColumnsObject.length >= this.element.get( 'row' ).get( 'columns' ).length ) {
			// current columns < columns number selected
			for ( let i = 0; i < newColumnsObject.length; i++ ) {
				let model = this.element.get( 'row' ).get( 'columns' ).at( i );
				if ( typeof model !== 'undefined' ) {
					let settings = model.get( 'settings' );
					settings.class = newColumnsObject[i].class;
					model.set( 'settings', settings );
					model.set( 'reRender', true );
				} else {
					let newModel = {
						settings: {
							class: newColumnsObject[i].class,
							elements: []
						}
					};
					this.element.get( 'row' ).get( 'columns' ).add( newModel );
				}
			}
		} else {
			// current columns > columns number selected
			var elements = [];
			var lastest_column_index = false;
			this.element.get( 'row' ).get( 'columns' ).map( ( model, index ) => {
				if ( typeof newColumnsObject[index] !== 'undefined' ) {
					let settings = model.get( 'settings' );
					settings.class = newColumnsObject[index].class;
					model.set( 'settings', settings );
					model.set( 'reRender', true );

					// lastest index if columns collection
					lastest_column_index = index;
				} else if ( lastest_column_index !== false ) {
					new Promise(function(resolve, reject) {
						var cloneModel = model;
						// check elements inside column if > 0, we will add it to lastest column
						if ( typeof cloneModel.get( 'elements' ) !== 'undefined' && cloneModel.get( 'elements' ).length > 0 ) {
							elements.push( cloneModel.get( 'elements' ).toJSON() );
						}

						if ( index == lastest_column_index ) {
							this.element.get( 'row' ).get( 'columns' ).at( lastest_column_index ).set( 'elements', elements );
						}

						// call destroy method after update columns collection
						resolve();
				    }).then( () => {
				    	model.destroy();
				    });
				}
			} );
		}
		return false;
	}

	_openHelperHandler(e) {
		e.preventDefault();
		this.element.set( 'helper-mode', true );
		this.helper = new Helper( this.element, e ).render();
		return false;
	}

}