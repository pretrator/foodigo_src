import Backbone from 'Backbone';
import _ from 'underscore';
import ColumnsCollection from '../collections/columns';
import uniqid from 'uniqid';

export default class RowModel extends Backbone.Model {

	initialize( data = { settings: {}, columns: [], editing: false } ) {
		let settings = { ...data.settings };
		if ( settings.uniqid_id === undefined || ! settings.uniqid_id ) {
			settings.uniqid_id = uniqid.process();
		}

		// stylesheet relationship
		let selectors = {};
		_.map( PA_PARAMS.element_fields.pa_row, ( options, group ) => {
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

		let collection = new ColumnsCollection( data.columns );
		this.set( 'columns', collection );
		this.on( 'change:screen', this._switchScreenMode );
	}

	defaults() {
		return {
			settings: {
				element: 'pa_row',
				uniqid_id : uniqid.process()
			},
			columns: new ColumnsCollection(),
			editing: false,
			element_type: 'widget',
			widget : 'pa_row',
			screen : 'lg'
		}
	}

	_switchScreenMode( model ) {
		let screen = model.get( 'screen' );
		model.get( 'columns' ).map( ( column, index ) => {
			// let oldScreen = column.get( 'screen' );
			column.set( 'screen', screen );
			// let responsive = column.get( 'responsive' );
			// let defaults = {
			// 	lg: {},
			// 	md: {},
			// 	sm: {},
			// 	xs: {},
			// }

			// if ( responsive[oldScreen] !== undefined ) {
			// 	_.map( defaults, ( ob, key ) => {
			// 		if ( responsive[key] === undefined || responsive[key].length === 0 ) {
			// 			responsive[key] = responsive[oldScreen];
			// 		}
			// 	} );
			// }

			// column.set( 'responsive', responsive );
		} );
	}

}