import { Collection, Model } from 'Backbone';
import _ from 'underscore';

export default class GroupCollection extends Collection {

	initialize ( items = {} ) {
		this.model = Model;
	}

}