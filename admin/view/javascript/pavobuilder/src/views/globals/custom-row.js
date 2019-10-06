import { View } from 'Backbone';
import _ from 'underscore';
import serializeJSON from 'jquery-serializejson';
import sortable from 'jquery-ui/ui/widgets/draggable';

export default class CustomRow extends View {

	initialize( row ) {
		this.row = row;
		this.template = $( '#pa-custom-row-modal-template' ).html();
		this.listenTo( this.row, 'change:custom-row-open', ( model, value ) => {
			if ( value === false ) {
				this.remove();
			}
		} );

		this.events = {
			'click .close-modal'			: '_closeModalHandler',
			'submit #pa-custom-row-form'	: '_submitHandler'
		}

		// close other modal
		Backbone.on( 'pa-close-modal', ( args ) => {
			if ( args.cid !== undefined && args.cid !== this.row.cid ) {
				this._close();
			}
		} );
	}

	render() {
		// close other modal
		Backbone.trigger( 'pa-close-modal', { cid: this.row.cid } );
		this.template = _.template( this.template, { variable: 'data' } )( this.row.toJSON() );
		this.setElement( this.template );
		$( 'body' ).append( this.el );

		this.$el.draggable({
			handle: '.pa-header-container .panel-heading'
		});
		return this;
	}

	_closeModalHandler( e ) {
		e.preventDefault();
		this.row.set( 'custom-row-open', false );
		return false;
	}

	_submitHandler( e ) {
		e.preventDefault();
		let form = this.$( '#pa-custom-row-form' );
		let data = form.serializeJSON();
		let value = data.value;

		let validated = true;
		let widths = value.split( '+' );
		let percent = false;
		let cols = false;
		let total = 0;

		for ( let i = 0; i < widths.length; i++ ) {
			let width = widths[i].trim();

			if ( width.indexOf( '%' ) !== -1 ) {
				percent = true;
				total = parseFloat( total ) + parseFloat( width.replace( '%', '' ) );

				// cal
				let styleWidth = false;
				let col = false;

				if ( width.indexOf( '%' ) !== -1 ) {
					styleWidth = width.replace( '%', '' ).trim();
					col = Math.floor( ( styleWidth * 12 ) / 100 );
				}

				widths[i] = {
					styleWidth: styleWidth,
					col: col
				}
			} else if ( width.indexOf( '/' ) !== -1 ) {
				let width = widths[i].trim();
				width = width.split('/');
				let x = width[0].trim();
				let y = width[1].trim();

				total = parseFloat( total ) + parseFloat( x / y ) * 100;
				cols = true;

				// cal
				let styleWidth = false;
				let col = false;
				if ( ( x * 12 ) % y == 0 ) {
					col = ( x * 12 ) / y;
				} else {
					styleWidth = ( x / y ) * 100;
					styleWidth = styleWidth.toFixed( 5 );
					col = Math.floor( ( styleWidth / 100 ) * 12 );
				}
				widths[i] = {
					styleWidth: styleWidth,
					col: col
				}

			}

			if ( percent && cols ) {
				validated = false;
			}
		}

		if ( ! validated ) {
			alert( PA_PARAMS.entry_enter_customrow_error );
		} else if ( total > 100 ) {
			alert( 'Width is over 100%, currently is ' + total );
		} else {
			let columns = this.row.get( 'columns' );
			let screens = [ 'lg', 'md', 'sm', 'xs' ];
			new Promise( ( resolved, reject ) => {
				for ( var x = columns.length - 1; x >= 0; x-- ) {
					if ( widths[x] === undefined ) {
						let model = columns.models[x];
						model.destroy();
					}
				}
				resolved();
			} ).then( () => {
				for ( let i = 0; i < widths.length; i++ ) {
					let width = widths[i];
					let col = width.col !== undefined ? width.col : false;
					let styleWidth = width.styleWidth !== undefined ? width.styleWidth : false;
					let model = columns.at( i );
					let responsive = {};

					if ( model !== undefined ) {
						responsive = { ...model.get( 'responsive' ) };
						_.map( screens, ( sc ) => {
							if ( styleWidth && col ) {
								responsive[sc].styles = {
									width: styleWidth
								}//width = styleWidth;
								responsive[sc].cols = col;
							} else if ( col ) {
								if ( responsive[sc] !== undefined && responsive[sc].styles !== undefined && responsive[sc].styles.width !== undefined ) {
									delete responsive[sc].styles.width;
								}
								responsive[sc].cols = col;
							}
						} );

						// reChange model
						model.set( 'responsive', responsive );
						model.set( 'reRender', true );
					} else {
						let newModel = {
							settings: {
								elements: []
							},
							responsive: {}
						};

						_.map( screens, ( sc ) => {
							newModel.responsive[sc] = {
								cols: col
							}
							if ( styleWidth ) {
								newModel.responsive[sc].styles = {
									width: styleWidth
								}
							}
						} );

						// add new model
						this.row.get( 'columns' ).add( newModel );
					}
				}
				this._close();
			} );

		}

		return false;
	}

	_close() {
		this.row.set( 'custom-row-open', false );
	}

}