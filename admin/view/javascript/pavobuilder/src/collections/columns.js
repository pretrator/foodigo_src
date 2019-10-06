import Backbone from 'Backbone';
import ColumnModel from '../models/column'
import _ from 'underscore';

export default class ColumnsCollection extends Backbone.Collection {

	initialize( columns = [] ) {
		_.map( columns, ( model ) => {
			model.resizabled = columns.indexOf( model ) + 1 < columns.length;
		} );
		this.model = ColumnModel;
		this.on( 'update', this._update );
		this.on( 'add', this._addParam );
		this.on( 'remove', this._calculatorColumnWidth );
	}

	/**
	 * Move item sort models
	 */
	moveItem( fromIndex = 0, toIndex = 0 ) {
		this.models.splice( toIndex, 0, this.models.splice( fromIndex, 1 )[0] );
        this.trigger( 'move' );
	}

	/**
	 * Set Editabled is TRUE
	 * And change reRender status allow view change
	 */
	_update( collection, type ) {
		if ( type.changes === undefined || type.changes.removed === undefined || type.changes.removed.length === 0 ) {
			return;
		}

		let colsNum = this.models.length;
		this.models.map( ( model, index ) => {
			// resizabled
			let screen = model.get( 'screen' );
			if ( screen !== 'lg' ) {
				// set resizable
				model.set( 'resizabled', true );
			} else {
				// new scripts
				let responsives = { ...model.get( 'responsive' ) };
				if ( 12 % colsNum === 0 ) {
					_.map( responsives, ( data, index ) => {
						if ( index === 'lg' || index === 'md' ) {
							responsives[index] = {
								cols: 12 / colsNum
							};
						} else if ( index === 'sm' ) {
							responsives[index] = {
								cols: 6
							};
						} else if ( index === 'xs' ) {
							responsives[index] = {
								cols: 12
							}
						}
					} );
				}
				model.set( 'responsive', responsives );
				// end new scripts

				let resizabled = model.get( 'resizabled' );
				let nextEditabled = index < this.length - 1;

				model.set( 'resizabled', nextEditabled );
				// if ( resizabled != nextEditabled ) {
					model.set( 'reRender', true );
				// }
			}
		} );
	}

	_addParam( model ) {
		let preIndex = this.indexOf( model ) - 1;
		if ( preIndex > -1 ) {
			this.at( preIndex ).set( 'resizabled', true );
		}
		model.set( 'resizabled', this.indexOf( model ) < this.length - 1 );
	}

	/**
	 * re-calculator column width
	 */
	_calculatorColumnWidth( model, collection, y ) {
		let numberColumn = this.length;
		let percentWidth = 100 / numberColumn;
		if ( 12 % numberColumn === 0 ) {
			this.map( ( model ) => {
				let responsive = model.get( 'responsive' );
				responsive[model.get( 'screen' )] = {
					cols: Math.floor( 12 / numberColumn ),
					// width: percentWidth
				}
				model.set( 'responsive', responsive );
				model.set( 'reRender', true );
			} );
		} else if ( model !== undefined ) {
			let responsive = model.get( 'responsive' );
			let prevModel = this.at( y.index - 1 );
			if ( prevModel !== undefined ) {
				let newResponsive = prevModel.get( 'responsive' );
				_.map( responsive, ( data, screen ) => {
					if ( screen === 'lg' ) {
						newResponsive[screen].cols = parseInt( newResponsive[screen].cols ) + parseInt( responsive[screen].cols );
						let width = 0;
						if ( responsive[screen].styles  !== undefined && responsive[screen].styles.width !== undefined ) {
							width = parseInt( width ) + parseInt( responsive[screen].styles.width );// + parseInt( responsive[screen].cols );
						}
						if ( newResponsive[screen].styles !== undefined && newResponsive[screen].styles.width  !== undefined ) {
							width = parseInt( width ) + parseInt( newResponsive[screen].styles.width );
						}
						if ( width ) {
							newResponsive[screen].width = width;
						}
					}
				} );
				prevModel.set( {
					reRender 	: true,
					responsive 	: newResponsive
				} )
			}
		}
	}

	_addNewColumn( currentScreen = '', RowWidth = false, data = {} ) {
		if ( 12 % ( parseInt( this.length ) + 1 ) !== 0 ) {
			let calcols = Math.floor( 12 / ( this.length + 1 ) );
			let columnWidth = calcols * ( RowWidth / 12 ) * 100 / RowWidth;
			let columnWidthPercent = ( RowWidth / 12 ) * 100 / RowWidth;

			new Promise( ( resolve, reject ) => {

				let screens = [ 'lg', 'md', 'sm', 'xs' ];
				let columnsData = {};
				// calculate responsive columns
				_.map( screens, ( screen ) => {
					let surplus = 0;
					let newRps = {};
					let successed = false;
					for ( let index = this.length - 1; index >= 0; index-- ) {
						if ( successed && ! surplus ) return false;
						let breakCal = false;
						columnsData[index] = columnsData[index] == undefined ? {} : columnsData[index];
						// if ( columnsData[index][screen] !== undefined ) return;
						let column = this.at( index );
						let colResponsive = column.get( 'responsive' );
						// resposive attributes
						let cols = colResponsive[screen] !== undefined && colResponsive[screen].cols !== undefined ? colResponsive[screen].cols : 1;
						let styles = colResponsive[screen] !== undefined && colResponsive[screen].styles ? colResponsive[screen].styles : false;
						let width = styles && styles.width !== undefined ? styles.width : false;

						if ( cols >= 1 || ( cols == 1 && width ) || ( cols === 1 && successed ) ) {
							switch ( screen ) {
								case 'lg':
								case 'md':
									let nosuccess = false;
									if ( successed ) {
										nosuccess = true;
									}

									let newsurplus = 0;
									if ( width ) {
										newsurplus = width - cols * columnWidthPercent;
									}
									surplus = parseFloat( surplus ) + parseFloat( newsurplus );
									// if cols > 1 always true
									if ( cols > 1 && ! successed ) {
										cols = cols - 1;
										successed = true;
									}

									if ( surplus ) {
										if ( surplus > 0 ) {
											if ( surplus > columnWidthPercent ) {
												if ( nosuccess ) {
													cols = parseInt( cols ) + 1;
													surplus = surplus - columnWidthPercent;
												} else {
													surplus = surplus - columnWidthPercent;
												}
											}
										} else {
											if ( surplus + columnWidthPercent < 0 ) {
												cols = ! nosuccess ? cols - 1 : cols;
												surplus = surplus + columnWidthPercent;
											}
										}
									}

									columnsData[index][screen] = {
										cols: cols
									};

									if ( surplus && cols >= 1 ) {
										width = parseFloat( columnWidthPercent * cols ) + surplus;
										if ( successed && cols >= 1 && width >= columnWidthPercent ) {
											columnsData[index][screen].styles = {
												width : width
											}
											surplus = false;
										}
									}

								break;

								case 'sm':

								break;

								case 'xs':

								break;
							}
						}
					}
				} );

				resolve( columnsData );
			} ).then( ( cols ) => {
				_.map( cols, ( data, index ) => {
					if ( data ) {
						let column = this.at( index );
						let responsive = { ...column.get( 'responsive' ), ...data };
						column.set( {
							reRender: true,
							responsive: responsive
						} );
					}
				} );

				let newColumnsObject = {
					screen: currentScreen,
					responsive: {
						lg: {
							cols: this.length == 0 ? 12 : 1
						},
						md: {
							cols: this.length == 0 ? 12 : 1
						},
						sm: {
							cols: 6
						},
						xs: {
							cols: 12
						}
					}
				};
				data = { ...data, ...newColumnsObject };
				this.add( data );
			} );
		} else {
			let cols = 12 / ( parseInt( this.length ) + 1 );
			let screens_delete = [ 'lg', 'md' ];
			this.map( ( column ) => {
				let responsive = { ...column.get( 'responsive' ) };
				_.map( screens_delete, ( scr ) => {
					if ( responsive[scr] === undefined ) {
						responsive[scr] = {};
					}
					responsive[scr].cols = cols;
					if ( responsive[scr].styles !== undefined && responsive[scr].styles.width !== undefined ) {
						delete responsive[scr].styles.width;
					}
				} );
				column.set( 'responsive', responsive );
				column.set( 'reRender', true );
			} );

			let newCol = {
				lg: {
					cols: cols
				},
				md: {
					cols: cols
				},
				sm: {
					cols: 6
				},
				xs: {
					cols: 12
				}
			};

			let newColumnsObject = {
				responsive: newCol
			};
			data = { ...data, ...newColumnsObject };
			this.add(data);
		}
		this._calculatorColumnWidth( this.at( this.length - 1 ), this, { index: this.length - 1 } );
	}

}