import Backbone from 'Backbone';
import _ from 'underscore';
import sortable from 'jquery-ui/ui/widgets/sortable';
import ColumnModel from '../models/column'
import EditForm from './globals/edit-form';
import ElementsPopup from './globals/elements-popup';
import Element from './element';
import resizable from 'jquery-ui/ui/widgets/resizable';
import Common from '../common/functions';
import Helper from './globals/helper';
import ElementTabs from './element-tabs';

export default class Column extends Backbone.View {

	initialize( column = { settings: {}, 'elements' : {}, resizabled: false } ) {
		this.column = column;

		this.events = {
			'click .pa-delete-column'						: '_deleteColumnHandler',
			'click .pa-add-element'							: '_renderElementsPopup',
			'click .pa-clone:not(.pa-clone-row)'			: '_cloneHandler',
			'click .pa-clone.pa-clone-row'					: '_cloneElementRowHandler',
			'click .pa-edit-column'							: '_editHandler',
			'contextmenu'									: '_openHelperHandler',
			'resize'										: ( e, ui ) => {
				if ( ui.element.cid !== this.column.cid ) {
					return;
				}
				new Promise( ( resolve, reject ) => {
					let screen = this.column.get( 'screen' );
					let columns = 12;
					let fullWidth = this.$el.parent().innerWidth();
					let columnWidth = fullWidth / columns;
					let target = ui.element;
			        let next = target.next();

					let currentCol = Math.round( target.width() / columnWidth );
	        		let nextColumnCount = Math.round( next.width() / columnWidth );
	        		currentCol = currentCol ? currentCol : 1;

	        		let responsive = { ...this.column.get( 'responsive' ) };
	        		let percent = ( ui.size.width * 100 ) / fullWidth;
	        		responsive[screen] = {
	        			cols: currentCol,
	        			styles: {
	        				width: percent.toFixed( 5 )
	        			}
	        		};
	        		resolve( responsive );
				} ).then( ( responsive = {} ) => {
        			this.column.set( 'responsive', responsive );
				} );
			},
			// trigger save next column when resize events
			'trigger_save_next_column'			: ( e, data = { cid: '', responsive: {} } ) => {
				if ( data.cid == this.column.cid ) {
					let responsive = { ...this.column.get( 'responsive' ) };
					let screen = this.column.get( 'screen' );
					responsive[screen] = data.responsive;
					this.column.set( {
						// reRender: true,
						responsive: responsive
					} );
				}
			}
		};

		this.listenTo( this.column, 'destroy', this.remove );
		// re-render html layout
		// because if is index > 0, we need resize column control
		this.listenTo( this.column, 'change:reRender', this._reRender );
		// this.listenTo( this.column, 'change:editing', this._renderEditColumnForm );

		this.listenTo( this.column.get( 'elements' ), 'remove', this._onRemoveElement );
		this.listenTo( this.column.get( 'elements' ), 'add', this.addElement );

		// delegate event
		// this.delegateEvents();
	}

	/**
	 * Render html
	 */
	render() {
		new Promise( ( resolve, reject ) => {
			let data = this.column.toJSON();
			data.cid = this.column.cid;

			this.template = _.template( $( '#pa-column-template' ).html(), { variable: 'data' } )( data );
			this.setElement( this.template );
			if ( this.column.get( 'elements' ) && this.column.get( 'elements' ).length > 0 ) {
				this.column.get( 'elements' ).map( ( element, at, collection ) => {
					// map element models and add it as Element to ColumnView
					this.addElement( element, collection, { at: at } );
				} );
			} else {
				this.$el.addClass( 'empty-element' );
			}

			resolve();
		} ).then( () => {
			// sortable
			this.$( '> .pa-element-wrapper > .pa-column-container' ).sortable({
				connectWith : '.pa-column-container',
				items 		: '.pa-element-content',
				handle 		: '.pa-reorder, .pa-reorder:not(.pa-reorder-row), .pa-reorder.pa-reorder-row',
				cursor 		: 'move',
				placeholder : 'pa-sortable-placeholder',
				receive 	: this._receive.bind( this ),
				start 		: this._start.bind( this ),
				tolerance	: 'pointer',
				update 		: this._update.bind( this ),
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
			    	let model = this.column.get( 'elements' ).get( { cid: cid } );
			    	let data = Common.toJSON( model.toJSON() );

			    	if ( data.widget !== undefined && PA_PARAMS.element_mask[data.widget] !== undefined ) {
			    		data = { ...data, ...PA_PARAMS.element_mask[data.widget] };
			    	}
					let template = _.template( $( '#pa-element-template' ).html(), { variable: 'data' } )( data );
					return $( template );
			    }
			}).disableSelection();

			// resizable
			if ( this.column.get( 'resizabled' ) ) {
				if ( this.$el.parent().length === 0 ) return;
				let fullWidth = this.$el.parent().innerWidth();
				let columnWidth = fullWidth / 12;
				let screen = this.column.get( 'screen' );

				this.$el.resizable({
				    handles: 'e',
				    start: ( event, ui ) => {
				      	let target = ui.element;
				        let next = target.next();
				        let row = $( target ).parents( '.pa-row:first' );
				        let columns = row.find( '> .pa-column' );

				        ui.element.cid = $( target ).data( 'cid' );
				      	ui.size.currentOriginWidth = target.get( 0 ).getBoundingClientRect().width;// target.outerWidth();
				      	ui.size.nextOriginWidth = next.length > 0 ? next.get(0).getBoundingClientRect().width : 0;//next.outerWidth();
				      	target.resizable( 'option', 'minWidth', columnWidth );

				      	let maxWidth = ui.size.currentOriginWidth + ui.size.nextOriginWidth;//target.outerWidth() + next.outerWidth();
				      	if ( screen !== 'lg' ) {
				      		let oldWidth = 0;
				      		for ( let i = 0; i <= this.$el.index(); i++ ) {
				      			if ( maxWidth === fullWidth ) maxWidth = 0;
				      			if ( target.index() !== i ) {
					      			let bounding = columns.get( i ).getBoundingClientRect();
					      			if ( Math.ceil( oldWidth ) >= Math.ceil( fullWidth ) ) {
					      				oldWidth = 0;
					      			} else {
					      				oldWidth = oldWidth + bounding.width;
					      			}
				      			}
				      		}
				      		maxWidth = fullWidth;// - oldWidth;
				      	} else {
				      		maxWidth = maxWidth - columnWidth;
				      	}
			      		target.resizable( 'option', 'maxWidth', maxWidth );
				    },
				    resize: ( event, ui ) => {
				      	let target = ui.element;
				        let next = target.next();
				        let row = $( target ).parents( '.pa-row:first' );
				        let columns = row.find( '> .pa-column' );
				        let targetWidth = target.get(0).getBoundingClientRect().width;// target.outerWidth()
				        let nextWidth = next.length > 0 ? next.get(0).getBoundingClientRect().width : 0;// ext.outerWidth();
				        let maxWidth = targetWidth + nextWidth;

				      	maxWidth = Math.ceil( maxWidth / columnWidth ) * columnWidth;

				      	let widthPercent = ( ( targetWidth / fullWidth ) * 100 ).toFixed( 0 );
			        	if ( target.find( '.pa-resizing-width' ).length > 0 ) {
			        		target.find( '.pa-resizing-width' ).remove();
			        	}
			        	target.find( '.pa-element-wrapper .pa-column-container' ).append( _.template( $( '#pa-resizing-column' ).html(), { 'variable': 'data' } )( { width: widthPercent } ) );

				    	target.addClass( 'resizing' );
				    	next.addClass( 'resizing' );
				      	if ( screen !== 'lg' ) {

				      	} else {

				        	let width = false;
							if ( ui.size.width > ui.originalSize.width ) {
								width = ui.size.nextOriginWidth - ( ui.size.width - ui.originalSize.width );
				        		next.width( width );
				        	} else {
				        		width = ui.size.nextOriginWidth + ( ui.originalSize.width - ui.size.width );
				        		next.width( width );
				        	}

				        	let widthPercent = ( ( next.get( 0 ).getBoundingClientRect().width / fullWidth ) * 100 ).toFixed( 0 );
				        	if ( next.find( '.pa-resizing-width' ).length > 0 ) {
				        		next.find( '.pa-resizing-width' ).remove();
				        	}
				        	next.find( '.pa-element-wrapper .pa-column-container' ).append( _.template( $( '#pa-resizing-column' ).html(), { 'variable': 'data' } )( { width: widthPercent } ) );
				      	}
				    },
				    stop: ( event, ui ) => {
				    	let target = ui.element;
				        let next = target.next();
				        // remove class 'resizing'
				        target.removeClass( 'resizing' );
				        next.removeClass( 'resizing' );
				        target.find( '.pa-resizing-width' ).remove();
				        next.find( '.pa-resizing-width' ).remove();

				        // if ( next.length == 0 ) return;

		        		new Promise( ( resolve, reject ) => {
		        			if ( next.length > 0 ) {
		        				let currentCol = Math.round( next.get( 0 ).getBoundingClientRect().width / columnWidth );//Math.round( next.width() / columnWidth );
		        				currentCol = currentCol ? currentCol : 1;
				        		// trigger save next column
				        		let percent = ( next.get( 0 ).getBoundingClientRect().width * 100 ) / fullWidth;//( next.outerWidth() * 100 ) / fullWidth;
				        		let responsive = {
				        			cols : currentCol,
				        			styles: {
				        				width: percent.toFixed( 5 )
				        			}
				        		};

				        		next.trigger( 'trigger_save_next_column', {
				        			cid: next.data( 'cid' ),
				    				responsive: responsive
				        		} );
		        			}

			        		// small screen
			        		if ( screen !== 'lg' ) {
			        			let targetWidth = target.get(0).getBoundingClientRect().width;
			        			let cols = Math.round( targetWidth / columnWidth );
			        			let responsive = { ...this.column.get( 'responsive' ) };
			        			responsive[screen] = {
			        				cols: cols
			        			}

			        			this.column.set( 'responsive', responsive );
			        			this.column.set( 'reRender', true );
			        		}
			        		resolve();
		        		} ).then( () => {
				    		// this.column.set( 'reRender', true );
		        		} );
				    }
		  		});
			}
		} );

		return this;
	}

	/**
	 * Add element
	 */
	addElement( model = {}, collection = {}, data = {} ) {
		let widget = model.get('widget');
		let template = new Element( model ).render().el;
		if (widget === 'pa_tabs') {
			template = new ElementTabs( model ).render().el;
		}
		if ( this.$( '> .pa-element-wrapper > .pa-column-container > .pa-element-content' ).length > data.at && data.at ) {
			$( this.$( '> .pa-element-wrapper > .pa-column-container > .pa-element-content' ).get( parseInt( data.at ) - 1 ) ).after( template );
		} else if ( data.at == 0 ) {
			this.$( '> .pa-element-wrapper > .pa-column-container' ).prepend( template );
		} else {
			this.$( '> .pa-element-wrapper > .pa-column-container' ).append( template );
		}
	}

	/**
	 * ReRender html layout
	 */
	_reRender( model, old ) {
		if ( this.column.get( 'reRender' ) === true ) {
			new Promise( ( resolved, reject ) => {
				this.$el.replaceWith( this.render().el );
				resolved();
			} ).then( () => {
				this.column.set( 'reRender', false );
			} );
		}
	}

	/**
	 * Delete Column Handler
	 */
	_deleteColumnHandler( e ) {
		e.preventDefault();
		if ( confirm( this.$( '.pa-delete-column' ).data( 'confirm' ) ) ) {
			this.column.destroy();
		}
		return false;
	}

	/**
	 * Render Elements Popup list
	 */
	_renderElementsPopup( e ) {
		e.preventDefault();
		let button = $( e.target );
		let controls = button.parents( '.column-controls:first' );
		if ( controls.hasClass( 'top' ) ) {
			this.column.set( 'adding_position', 0 );
		} else {
			this.column.set( 'adding_position', this.column.get( 'elements' ).length );
		}

		// toggle 'adding'
		this.column.set( 'adding', true );
		// elements for select
		this.elementsList = new ElementsPopup( this.column );
		return false;
	}

	/**
	 * Clone click handler
	 */
	_cloneHandler( e ) {
		e.preventDefault();

		let button = $( e.target ).parents( '.pa-element-content:first' );
		let cid = button.data( 'cid' );
		let model = this.column.get( 'elements' ).get( { cid: cid } );
		let index = this.column.get( 'elements' ).indexOf( model );
		let data = Common.toJSON( model.toJSON(), [], true );
		if ( data.settings !== undefined && data.settings.uniqid_id !== undefined ) {
			delete data.settings.uniqid_id;
		}
		this.column.get( 'elements' ).add( data, { at: parseInt( index ) + 1 } );
		return false;
	}

	/**
	 * Clone element 'pa_row' handler
	 */
	_cloneElementRowHandler( e ) {
		e.preventDefault();
		let target = $( e.target ).parents( '.pa-element-content.pa_row' );
		let cid = target.data( 'cid' );
		let model = this.column.get( 'elements' ).get( { cid: cid } );
		let index = this.column.get( 'elements' ).indexOf( model );

		let data = Common.toJSON( model.toJSON(), [], true );
		if ( data.settings !== undefined && data.settings.uniqid_id !== undefined ) {
			delete data.settings.uniqid_id;
		}
		if ( data.row !== undefined && data.row.settings !== undefined && data.row.settings.uniqid_id !== undefined ) {
			delete data.row.settings.uniqid_id;
		}
		this.column.get( 'elements' ).add( data, { at: parseInt( index ) + 1 } );
		return false;
	}

	/**
	 * on remove element
	 */
	_onRemoveElement() {
		if ( this.column.get( 'elements' ).length === 0 ) {
			this.$el.addClass( 'empty-element' );
		}
	}

	/**
	 * Edit column handler
	 */
	_editHandler( e ) {
		e.preventDefault();

		// row edit form
		let editForm = new EditForm( this.column, PA_PARAMS.languages.entry_edit_column_text, PA_PARAMS.element_fields.pa_column );
		this.column.set( 'editing', true );
		return false;
	}

	_openHelperHandler(e) {
		e.preventDefault();
		this.column.set( 'helper-mode', true );
		this.helper = new Helper( this.column, e ).render();
		return false;
	}

	/**
	 * Received element from other column
	 */
	_receive( e, ui ) {
		new Promise( ( resolve, reject ) => {
			let index = ui.item.index();
			let model = Common.toJSON( ui.item.element.toJSON() );
			this.column.get( 'elements' ).add( model, { at: index } );
			resolve();
		} ).then( () => {
			ui.item.element.destroy();
		} );
	}

	/**
	 * Start drag event
	 * set indexStart, elements - collection data, element - model
	 */
	_start( e, ui ) {
		ui.item.indexStart = ui.item.index();
        ui.item.elements = this.column.get( 'elements' );
        ui.item.element = this.column.get( 'elements' ).at( ui.item.indexStart );
	}

	/**
	 * Update when sortable
	 * just useful when update elements inside drop event
	 */
	_update( e, ui ) {

		if ( ui.item.elements !== this.column.get( 'elements' ) ) {
			return;
		}
		let index = ui.item.index();
		// resort elements collection
		this.column.get( 'elements' ).moveItem( ui.item.indexStart, index );
		// trigger drop event element
		ui.item.trigger( 'drop', index );
	}

	/**
	 * render edit form if 'editing' is true
	 */
	_renderEditColumnForm( model ) {
		if ( model.get( 'editing' ) === true ) {
			// row edit form
			let editForm = new EditForm( model, PA_PARAMS.languages.entry_edit_column_text, PA_PARAMS.element_fields.pa_column );
		}
	}

}