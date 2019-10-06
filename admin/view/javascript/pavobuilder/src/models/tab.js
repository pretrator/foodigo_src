import { Model } from 'Backbone';
import RowModel from './row';
import uniqid from 'uniqid';

export default class TabModel extends Model {

	initialize( tab = { settings: {}, row: {} } ) {
		this.set( 'row', new RowModel(tab.row) );
		this.on( 'change:screen', this._switchScreenMode );
	}

	defaults() {
		return {
			settings: {
				uniqid_id : uniqid.process()
			},
			row: {}
		}
	}

	_switchScreenMode() {
		let screen = this.get( 'screen' );
		let row = this.get( 'row' );
		row.set( 'screen', screen );
	}

}