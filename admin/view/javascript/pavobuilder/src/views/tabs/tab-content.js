import Backbone from 'Backbone';
import _ from 'underscore';
import Row from '../row';
import EditForm from '../globals/edit-form';

export default class TabContent extends Backbone.View {

	initialize( tab = {} ) {
		this.tab = tab;
		this.events = {
			'click > .pa-controls .pa-delete' : '_deleteHandler',
			'click > .pa-controls  .pa-edit' : '_editHandler'
		}
		this.listenTo( this.tab, 'change:active', this._activeToggle );
		this.listenTo( this.tab, 'destroy', this.remove );
		this.delegateEvents();
	}

	render() {
		let data = this.tab.toJSON();
		data.cid = this.tab.cid;
		this.template = $('#pa-tab-element-content').html();
		this.template = _.template( this.template, { variable: 'data' } )( data );
		this.setElement(this.template);

		let row = this.tab.get( 'row' );
		row = new Row( row );
		this.$('.pa-tab-elements').append( row.render().el );

		return this;
	}

	_deleteHandler(e) {
		e.preventDefault();
		if ( confirm( this.$el.data( 'confirm' ) ) ) {
			this.tab.destroy();
		}
		return false;
	}

	_activeToggle() {
		let active = this.tab.get('active');
		if ( active !== undefined && active ) {
			this.$el.addClass( 'active in' );
		} else {
			this.$el.removeClass( 'active in' );
		}
	}

	_editHandler(e) {
		e.preventDefault();
		if ( this.tab.get( 'editing' ) !== true ) {
			this.editForm = new EditForm( this.tab, '', PA_PARAMS.element_fields.pa_tabs );
			this.tab.set( 'editing', true );
		}
		return false;
	}

}