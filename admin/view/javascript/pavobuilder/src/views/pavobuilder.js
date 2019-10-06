import Backbone from 'Backbone';
import _ from 'underscore';
import Rows from './rows';
import RowsCollection from '../collections/rows';
import Helper from './globals/helper';

export default class Builder extends Backbone.View {

	initialize( rows = [] ) {
		// set data is collection of row, it will pass to Rows View
		this.$el = $( '#pavohomebuilder-layout-edit' );

		// let collection = new RowsCollection( rows );
		this.model = new Backbone.Model({
			rows: new RowsCollection( rows )
		});
		// events
		this.events = {
			'click #pa-add-element' 				: 'addRowHandler',
			'click #pa-paste' 						: 'pasteRowHandler',
			'click .button-alignments .btn-default'	: '_switchScreen'
		}

		this.listenTo( this.model, 'change:screen', this.switchScreen );
		// add event
		this.delegateEvents();
		this.render();
	}

	/**
	 * Render html rows template
	 */
	render() {
		// set rows data
		this.$( '#pa-footer' ).before( new Rows( this.model.get( 'rows' ) ).render().el );
		return this;
	}

	/**
	 * Add row event handler
	 * add empty row
	 */
	addRowHandler( e ) {
		e.preventDefault();
		// add row model to collection
		let row = {
			settings: {},
			screen: $( '.button-alignments .btn.active' ).data( 'screen' ),
			columns: [
				{
					settings: {
						element: 'pa_column'
					}
				}
			],
			responsive: {
				lg: { cols: 12 },
				md: { cols: 12 },
				sm: { cols: 12 },
				xs: { cols: 12 }
			}
		};
		this.model.get( 'rows' ).add( row );
		return false;
	}

	/**
	 * paste row handler
	 */
	pasteRowHandler(e) {
		e.preventDefault();
		let helper = new Helper();
		let row = helper._getlocalStorage( 'pavobuilder', false );
		if ( row === false || row === null ) {
			alert( PA_PARAMS.entry_none_copied_text );
		} else {
			this.model.get( 'rows' ).add( JSON.parse( row ) );
		}
		return false;
	}

	/**
	 * switch screen
	 */
	_switchScreen( e ) {
		let target = e.target;
		let button = $( e.target );
		if ( target.nodeName !== 'BUTTON' ) {
			button = $( target ).parent();
		}
		this.$( '.button-alignments .btn-default' ).removeClass( 'active' );
		button.addClass( 'active' );

		this.model.set( 'screen', button.data( 'screen' ) );
	}

	switchScreen( model ) {
		// this.$( '#pa-content' ).attr( 'data-screen', model.get( 'screen' ) );
		this.model.get( 'rows' ).map( ( row ) => {
			row.set( 'screen', model.get( 'screen' ) );
		} );
	}

}