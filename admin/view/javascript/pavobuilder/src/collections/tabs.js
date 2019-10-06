import Backbone from 'Backbone';
import Tab from '../models/tab';

export default class Tabs extends Backbone.Collection {

	initialize( tabs = [] ) {
		this.model = Tab;
	}

	setActive( activeModel ) {
		this.map( ( model ) => {
			model.set( 'active', activeModel === model );
		} );
	}

	/**
	 * Move item sort models
	 */
	moveItem( fromIndex = 0, toIndex = 0 ) {
		this.models.splice( toIndex, 0, this.models.splice( fromIndex, 1 )[0] );
        this.trigger( 'move' );
	}

}