import Backbone from 'Backbone';
import _ from 'underscore';
import ElementModel from '../models/element';

export default class ElementsCollection extends Backbone.Collection {

	initialize( elements = {} ) {
		this.model = ElementModel;
	}

	/**
	 * Move item sort models
	 */
	moveItem( fromIndex = 0, toIndex = 0 ) {
		this.models.splice( toIndex, 0, this.models.splice( fromIndex, 1 )[0] );
        this.trigger( 'move' );
	}

}