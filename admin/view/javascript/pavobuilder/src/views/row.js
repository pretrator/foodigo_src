import Backbone from 'Backbone';
import _ from 'underscore';
import Column from './column';
import EditForm from './globals/edit-form';
import CustomRow from './globals/custom-row';
import resizable from 'jquery-ui/ui/widgets/resizable';
import Helper from './globals/helper';
import Common from '../common/functions';

export default class Row extends Backbone.View {

	/**
	 * Constructor class
	 */
	initialize( row = { settings: {}, columns: {} } ) {
		// set backbone model
		this.row = row;
		this.customModal = false;

		this.events = {
			'click > .pa-controls .pa-delete-row'					: '_deleteRowHandler',
			'click .pa-add-column'									: '_addColumnHandler',
			'click .pa-edit-column-num'								: '_changeColumnsHandler',
			'click > .pa-row-container > .row-controls > .left-controls > .pa-edit-row, > .row-controls > .left-controls > .pa-edit-row'		: '_setEditRowHandler',
			'click .pa-reorder, .pa-set-column'						: () => {
				return false;
			},
			'click > .pa-controls .pa-copy-row' 					: '_copyRowHandler',
			'click > .pa-controls  .pa-row-code' 					: '_rowCodeHandler'
		}

		// listen this.row model
		this.listenTo( this.row.get( 'columns' ), 'add', this.addColumn );
		this.listenTo( this.row, 'destroy', this.remove );
		this.listenTo( this.row, 'change:screen', ( model ) => {
			let screen = model.get( 'screen' );
			if ( screen !== 'lg' ) {
				this.$( '.pa-set-column, .pa-add-column, .pa-row-code' ).addClass( 'hide' );
			} else {
				this.$( '.pa-set-column, .pa-add-column, .pa-row-code' ).removeClass( 'hide' );
			}
		} );

		this.listenTo( this.row, 'change:custom-row-open', ( model, value ) => {
			if ( value === false ) {
				this.customModal = false;
				this.row.unset( 'custom-row-open' );
			}
		} );
	}

	/**
	 * Render html
	 */
	render() {
		let data = this.row.toJSON();
		data.cid = this.row.cid;
		this.template = _.template( $( '#pa-row-template' ).html(), { variable: 'data' } )( data );
		this.setElement( this.template );

		// each collection
		if ( this.row.get( 'columns' ) && this.row.get( 'columns' ).length > 0 ) {
			this.row.get( 'columns' ).map( ( model, index ) => {
				// model.set( 'resizabled', index < this.row.get( 'columns' ).length - 1 );
				// if ( model.get( 'screen' ) !== 'lg' ) {
				// 	model.set( 'resizabled', true );
				// }
				// map column models add add it to Row View
				this.addColumn( model );
			} );
		}

		setTimeout( () => {
			this.$el.removeClass( 'row-fade-in' );
		}, 1000 );
		return this;
	}

	/**
	 * Add Column
	 */
	addColumn( model = {} ) {
		let settings = { ...model.get( 'settings' ) };
		// if ( settings.disable_column_padding_margin !== undefined && settings.disable_column_padding_margin ) {
			// settings.disable_padding_margin = 0;
			model.set( 'settings', settings );
		// }
		let column = new Column( model ).render().el;
		this.$( '> .pa-element-wrapper > .pav-row-container' ).append( column );
	}

	/**
	 * Delete row handler
	 */
	_deleteRowHandler( e ) {
		e.preventDefault();
		if ( confirm( this.$( '.pa-delete-row' ).data( 'confirm' ) ) ) {
			this.row.destroy();
		}
		return false;
	}

	/**
	 * Add column handler
	 */
	_addColumnHandler( e ) {
		e.preventDefault();
		// current screen
		let currentScreen = this.row.get( 'screen' );
		let RowWidth = this.$el.innerWidth();

		if ( this.row.get( 'columns' ).length == 12 ) {
			alert( PA_PARAMS.languages.entry_column_is_maximum );
		} else {
			this.row.get( 'columns' )._addNewColumn( currentScreen, RowWidth );
		}

		return false;
	}

	/**
	 * Change Columns of row
	 */
	_changeColumnsHandler( e ) {
		e.preventDefault();

		let button = $( e.target );
		let columns_count = button.data('columns');
		let cols = false;
		let columns = [];
		if ( Number.isInteger( columns_count ) ) {
			cols = Math.floor( 12 / parseInt( columns_count ) );
		} else {
			columns = columns_count.split( '-' );
			columns_count = columns.length;
		}
		let screen = this.row.get( 'screen' );
		let screens = [ 'lg', 'md', 'sm', 'xs' ];
		let newColumnsObject = [];

		for ( let i = 0; i < columns_count; i++ ) {
			newColumnsObject.push({
				cols: cols ? cols : columns[i]
			});
		}

		if ( newColumnsObject.length >= this.row.get( 'columns' ).length ) {
			// current columns < columns number selected
			new Promise( ( resolve, reject ) => {
				let columns = { ...this.row.get( 'columns' ).models };
				for ( let i = newColumnsObject.length - 1; i >= 0; i-- ) {
					let model = columns[i];
					if ( typeof model === 'undefined' ) {
						let newModel = {
							settings: {
								elements: []
							}
						};

						newModel.responsive = {};
						_.map( screens, ( sc ) => {
							newModel.responsive[sc] = {
								cols: newColumnsObject[i].cols
							}
						} );

						model = this.row.get( 'columns' ).add( newModel );
					}
				}
				resolve();
			} ).then( ( resolve ) => {
				columns = { ...this.row.get( 'columns' ).models };
				for ( let i = newColumnsObject.length - 1; i >= 0; i-- ) {
					let model = columns[i];
					if ( typeof model !== 'undefined' ) {
						let responsive = { ...model.get( 'responsive' ) };

						_.map( screens, ( sc ) => {
							if ( responsive[sc].styles !== undefined && responsive[sc].styles.width !== undefined ) {
								delete responsive[sc].styles.width;
							}
							responsive[sc].cols = newColumnsObject[i].cols;
						} );
						model.set( 'responsive', responsive );
						model.set( 'reRender', true );
					}
				}
			} );
		} else {
			// current columns > columns number selected
			let columns = this.row.get( 'columns' ).models;

			// for ( let i = columns.length - 1; i >= 0; i-- ) {
			// 	let column = columns[i];
			// 	if ( i > newColumnsObject.length - 1 ) {
			// 		column.destroy();
			// 	} else {
			// 		let responsive = { ...column.get( 'responsive' ) };

			// 		_.map( screens, ( sc ) => {
			// 			responsive[sc].cols = newColumnsObject[i].cols;
			// 			// delete width style
			// 			if ( responsive[sc].styles !== undefined && responsive[sc].styles.width !== undefined ) {
			// 				delete responsive[sc].styles.width;
			// 			}
			// 		} );

			// 		column.set( 'responsive', responsive );
			// 		column.set( 'reRender', true );
			// 	}
			// }

			new Promise( ( resolved, reject ) => {
				let columnIndexs = [];
				for ( let i = columns.length - 1; i >= 0; i-- ) {
					let column = columns[i];
					if ( i > newColumnsObject.length - 1 ) {
						column.destroy();
					} else {
						columnIndexs.push(i);
					}
				}
				resolved( {
					indexs: columnIndexs
				} );
			} ).then( ( res ) => {
				_.map( res.indexs, ( index ) => {
					let column = columns[index];
					let responsive = { ...column.get( 'responsive' ) };
					_.map( screens, ( sc ) => {
						responsive[sc].cols = newColumnsObject[index].cols;
						// delete width style
						if ( responsive[sc].styles !== undefined && responsive[sc].styles.width !== undefined ) {
							delete responsive[sc].styles.width;
						}
					} );

					column.set( 'responsive', responsive );
					column.set( 'reRender', true );
				} );
			} );
		}

		return false;
	}

	/**
	 * Set edit row mode
	 */
	_setEditRowHandler( e ) {
		e.preventDefault();
		// row edit form
		let editForm = new EditForm( this.row, PA_PARAMS.languages.entry_edit_row_text, PA_PARAMS.element_fields.pa_row );
		this.row.set( 'editing', true );
		return false;
	}

	/**
	 * row code handler
	 */
	_rowCodeHandler( e ) {
		e.preventDefault();
		if ( this.customModal == false ) {
			this.row.set( 'custom-row-open', true );
			this.customModal = new CustomRow( this.row ).render();
		}
		return false;
	}

	/**
	 * copy row handler
	 */
	_copyRowHandler(e) {
		e.preventDefault();
		let cloneData = Common.toJSON( this.row.toJSON(), [], true );
		let helper = new Helper();
		helper.setClipboardText( cloneData );
		// console.log(helper._getlocalStorage( 'papbuilder' ));
		return false;
	}

}