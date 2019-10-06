import Backbone from 'Backbone';
import _ from 'underscore';

export default class TabTitle extends Backbone.View {

	initialize( tab = {} ) {
		this.tab = tab;
		this.events = {
			'click .tab-label' : '_switchTabsHandler'
		};

		this.listenTo( this.tab, 'change', this._reRender );
		this.listenTo( this.tab, 'destroy', this.remove );

		// this.delegateEvents();
	}

	render() {
		let data = this.tab.toJSON();
		data.cid = this.tab.cid;
		this.template = $('#pa-tab-element-title').html();
		this.template = _.template( this.template, { variable: 'data' } )( data );

		this.setElement(this.template);
		return this;
	}

	_reRender() {
		this.$el.replaceWith( this.render().el );
	}

	_switchTabsHandler(e) {
		e.preventDefault();
		this.tab.collection.setActive( this.tab );
		return false;
	}

}