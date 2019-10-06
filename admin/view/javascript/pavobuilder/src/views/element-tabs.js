import _ from 'underscore';
import Backbone from 'Backbone';
import TabTitle from './tabs/tab-title';
import TabContent from './tabs/tab-content';
import Row from './row';
import sortable from 'jquery-ui/ui/widgets/sortable';

export default class ElementTabs extends Backbone.View {

	initialize( element = {} ) {
		this.element = element;

		this.events = {
			'click .add-section' : '_addSectionHandler'
		}

		this.first = true;
		this.listenTo( this.element.get('tabs'), 'add', this._addOne );
		this.listenTo( this.element.get('tabs'), 'remove', this._removeOne );
	}

	render() {
		new Promise((resolved, reject) => {
			let data = this.element.toJSON();
			data.cid = this.element.cid;
			let template = $( '#pa-tabs-element-template' );
			this.template = _.template( template.html(), { variable: 'data' } )( data );

			this.setElement(this.template);
			// init tabs content in side element
			this.initTabs();

			resolved();
		}).then(() => {
			// sortable
			this.$( '> .pa-section-wrap > .pa-tabs-labels-group' ).sortable({
				connectWith : '.pa-tabs-labels-group',
				items 		: '.section-label:not(.add-section)',
				handle 		: '.sort-handler',
				cursor 		: 'move',
				placeholder : 'pa-sortable-placeholder',
				start 		: this._start.bind( this ),
				update 		: this._update.bind( this ),
				tolerance	: 'pointer',
				sort 		: ( event, ui ) => {
					ui.helper.width( $(ui.item[0]).width() );
					ui.helper.height( $(ui.item[0]).height() );
					$( ui.helper ).offset({
						top 	: event.pageY,
						left 	: event.pageX + (ui.helper/2)
					});
				}
			}).disableSelection();
		})
		return this;
	}

	_reRender() {
		this.first = false;
		this.$el.replaceWith( this.render().el );
	}

	// init tabs
	initTabs() {
		// this is a collection
		let tabs = this.element.get('tabs');
		if (tabs) {
			this.$('.pa-tabs-labels-group').append('<div class="add-section section-label"><i class="fa fa-plus"></i></div>');

			let firtModel = this.element.get('tabs').at(0);
			this.element.get('tabs').setActive(firtModel);
			tabs.map( (tab, key) => {
				this.renderTabTitle( tab );
				this.renderTabContent( tab );
			});
			this.first = false;
		}
	}

	_addOne( tab ) {
		this.renderTabTitle( tab );
		this.renderTabContent( tab );
	}

	_removeOne( tab ) {
		let index = this.element.get('tabs').indexOf( tab );
		let prevModel = this.element.get('tabs').at( index );
		prevModel.set( 'active', true );
	}

	renderTabTitle(tab) {
		let tabtitle = new TabTitle(tab);
		this.$('.pa-tabs-labels-group .add-section').before( tabtitle.render().el );
	}

	renderTabContent( tab ) {
		let tabcontent = new TabContent( tab );
		this.$('.pa-tabs-contents-group').append( tabcontent.render().el );
	}

	// add tab handler
	_addSectionHandler(e) {
		e.preventDefault();
		let model = this.element.get('tabs').add({
			title: '',
			row: {
				settings: {},
				columns: [
					{
						settings: {
							element: 'column'
						},
						elements: [],
						responsive: {
							lg: {
								cols: 12
							},
							md: {
								cols: 12
							},
							sm: {
								cols: 12
							},
							xs: {
								cols: 12
							}
						}
					}
				]
			}
		});
		this.element.get('tabs').setActive(model);
		return false;
	}

	/**
	 * Start drag event
	 * set indexStart, elements - collection data, element - model
	 */
	_start( e, ui ) {
		ui.item.indexStart = ui.item.index();
        ui.item.elements = this.element.get( 'tabs' );
        ui.item.element = this.element.get( 'tabs' ).at( ui.item.indexStart );
	}

	/**
	 * Update when sortable
	 * just useful when update elements inside drop event
	 */
	_update( e, ui ) {

		if ( ui.item.elements !== this.element.get( 'tabs' ) ) {
			return;
		}
		let index = ui.item.index();
		// resort elements collection
		this.element.get( 'tabs' ).moveItem( ui.item.indexStart, index );
		this.element.get( 'tabs' ).setActive( ui.item.element );
		// trigger drop event element
		ui.item.trigger( 'drop', index );
	}

}