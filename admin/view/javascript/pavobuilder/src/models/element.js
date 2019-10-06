import Backbone from 'Backbone';
import _ from 'underscore';
import ColumnsCollection from '../collections/columns';
import RowModel from './row';
import uniqid from 'uniqid';
import TabsCollection from '../collections/tabs';

export default class ElementModel extends Backbone.Model {

	initialize( data = { settings: {}, row: {}, columns: {} } ) {
		if ( data.row ) {
			this.set( 'row', new RowModel( data.row ) );
			if ( data.columns ) {
				this.get( 'row' ).add( data.columns );
			}
		}

		if ( data.tabs ) {
			this.set( 'tabs', new TabsCollection( data.tabs ) );
		}

		let settings = { ...data.settings };
		if ( settings.uniqid_id === undefined || ! settings.uniqid_id ) {
			settings.uniqid_id = uniqid.process();
		}

		// stylesheet relationship
		if ( PA_PARAMS.element_fields[ this.get( 'widget' ) ] !== undefined ) {
			let fields = PA_PARAMS.element_fields[ this.get( 'widget' ) ];
			let selectors = {};
			_.map( fields, ( options, group ) => {
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
		}
		// end stylesheet relationship

		this.set( 'settings', settings );
		this.on( 'change:screen', this._switchScreenMode );
	}

	defaults() {
		return {
			settings 	: {
				uniqid_id : uniqid.process()
			},
			mask	 	: {},
			editing  	: false
		};
	}

	_switchScreenMode() {
		let screen = this.get( 'screen' );
		let tabs = this.get( 'tabs' );
		if ( tabs !== undefined ) {
			tabs.map( ( tab ) => {
				tab.set( 'screen', screen );
			} );
		}
	}

}