import Backbone from 'Backbone';
import _ from 'underscore';
import Row from './row';
import Common from '../common/functions';

export default class Rows extends Backbone.View {

	initialize( rows = {} ) {
		// set this.rows is a collection
		this.rows = rows;
		this.setElement( '<div id="pa-content"></div>' );
		// listen to collection status
		this.listenTo( this.rows, 'add', this.addRow );
		this.events = {
			'click > .pa-row-container > .row-controls > .left-controls > .pa-clone-row' : '_cloneRowHandler'
		};

		// add event
		this.delegateEvents();
	}

	/**
	 * Render html method
	 */
	render() {
		new Promise( ( resolve, reject ) => {
			if ( this.rows.length > 0 ) {
				this.rows.map( ( model, index ) => {
					this.addRow( model );
				} );
			}

			resolve();
		} ).then( () => {

			// set sortable
			this.$el.sortable({
				items 			: '.pa-row-container:not(.disable-sortable)',
				placeholder 	: 'pa-sortable-placeholder',
				handle 			: '> .row-controls > .right-controls > .pa-reorder-row',
				// sortable updated callback
				start 			: this.dragRow,
				stop 			: this.dropRown.bind( this ),
				sort 		: ( event, ui ) => {
					ui.helper.width( 200 );
					ui.helper.height( 50 );
					$( ui.helper ).offset({
						top 	: event.pageY - 25,
						left 	: event.pageX - 100
					});
				},
			    helper 		: ( event, ui ) => {
			    	let ele = $( ui ).get( 0 );
			    	let cid = $( ele ).data( 'cid' );
			    	let model = this.rows.get( { cid: cid } );
			    	if ( model == undefined ) return ui;
			    	let data = model.toJSON();

			    	if ( data.widget !== undefined && PA_PARAMS.element_mask.pa_row !== undefined ) {
			    		data = { ...data, ...PA_PARAMS.element_mask.pa_row };
			    	}
					let template = _.template( $( '#pa-element-template' ).html(), { variable: 'data' } )( data );
					$( template ).find( '.pa-controls' ).remove();
					// event.preventDefault();
					return $( template );
			    }
			}).disableSelection();
		} );
		return this;
	}

	/**
	 * Add row view
	 */
	addRow( model = {}, collection = {}, status = {} ) {
		if ( typeof status.at === 'undefined' ) {
			this.$el.append( new Row( model ).render().el );
		} else {
			let rows = this.$( '> .pa-row-container' );
			rows.map( ( i, row ) => {
				let newIndex = parseInt( status.at ) - 1;
				if ( newIndex == i ) {
					$( rows[newIndex] ).after( new Row( collection.get( model ) ).render().el )
				}
			} );
		}
	}

	/**
	 * Clone Row
	 */
	_cloneRowHandler( e ) {
		e.preventDefault();
		let cid = $( e.target ).parents( '.pa-row-container:first' ).data( 'cid' );
		let model = this.rows.get( { cid: cid } );

		let index = this.rows.indexOf( model );
		let data = Common.toJSON( model.toJSON(), [], true );
		if ( data.settings !== undefined && data.settings.uniqid_id !== undefined ) {
			delete data.settings.uniqid_id;
		}
		this.rows.add( data, { at: parseInt( index ) + 1 } );
		return false;
	}

	/**
	 * Start Drag event row
	 */
	dragRow( event, ui ) {
		ui.item.indexStart = ui.item.index();
	}

	/**
	 * Drop row
	 */
	dropRown( event, ui ) {
		this.rows.moveItem( ui.item.indexStart, ui.item.index() );
	}

}