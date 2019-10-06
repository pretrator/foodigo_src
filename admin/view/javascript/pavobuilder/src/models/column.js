import Backbone from 'Backbone';
import _ from 'underscore';
import ElementsCollection from '../collections/elements';
import uniqid from 'uniqid';

export default class ColumnModel extends Backbone.Model {

	initialize( data = { settings: { styles: {} }, elements: [] } ) {
		// data = this.toJSON();
		let settings = { ...this.toJSON().settings };
		if ( settings.uniqid_id === undefined || ! settings.uniqid_id ) {
			settings.uniqid_id = uniqid.process();
		}

		// stylesheet relationship
		let selectors = {};
		_.map( PA_PARAMS.element_fields.pa_column, ( options, group ) => {
			_.map( options.fields, ( field ) => {
				let name = field.name !== undefined ? field.name : '';
				let sltor = field.selector !== undefined && field.selector ? field.selector : '';
				let css_attr = field.css_attr !== undefined ? field.css_attr : '';
				if ( css_attr ) {
					selectors[name] = {
						selectors: sltor,
						css_attr: css_attr
					};
				}
			} );
		} );
		settings.selectors = selectors;
		// end stylesheet relationship

		this.set( 'settings', settings );

		this.set( 'elements', new ElementsCollection( data.elements ) );
		this.on( 'change:screen', this._switchScreenMode );
	}

	defaults() {
		return {
			settings : {
				element : 'pa_column',
				uniqid_id : uniqid.process()
			},
			responsive : {
				lg : {
					cols: 12,
					styles: {
						width : 100,
					}
				},
				md : {
					cols: 12
				},
				sm : {
					cols: 12
				},
				xs : {
					cols: 12
				}
			},
			elements : new ElementsCollection(),
			editing : false,
			adding : false,
			element_type : 'widget',
			reRender: false,
			widget : 'pa_column',
			screen: 'lg'
		};
	}

	_switchScreenMode( model, old ) {
		let screen = this.get( 'screen' );
		if ( screen != 'lg' ) {
			this.set( 'resizabled', true );
		} else {
			// let resizabled = ( index + 1 ) < model.get( 'columns' ).length;
			this.set( 'resizabled', this.collection.indexOf( model ) + parseInt( 1 ) < this.collection.length );
		}

		this.get( 'elements' ).map( ( element, index ) => {
			let row = element.get( 'row' );
			let screen = model.get( 'screen' );
			if ( row !== undefined ) {
				row.set( 'screen', screen );
			} else {
				element.set( 'screen', screen );
			}
		} );
		this.set( 'reRender', true );
	}

}