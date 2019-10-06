import $ from 'jquery';
import iconpicker from 'fontawesome-iconpicker';
import 'fontawesome-iconpicker/dist/css/fontawesome-iconpicker.css';
import _ from 'underscore';
import Backbone from 'Backbone';
import uniqid from 'uniqid';
import datepicker from 'bootstrap-datepicker';
import 'bootstrap-datepicker/dist/css/bootstrap-datepicker.min';
import ionRangeSlider from 'ion-rangeslider';
import 'ion-rangeslider/css/ion.rangeSlider';
import 'ion-rangeslider/css/ion.rangeSlider.skinNice';
import datetimepicker from 'bootstrap-datetime-picker';
import 'bootstrap-datetime-picker/css/bootstrap-datetimepicker';
import colorpicker from 'bootstrap-colorpicker';
import 'bootstrap-colorpicker/dist/css/bootstrap-colorpicker';

import GoogleMap from '../views/globals/google-map';

function toJSON( data = {}, ignores = [], uniqid_id_rand = false ) {
	if ( ignores.length == 0 ) {
		ignores = [ 'specifix_id' ];
	}
	if ( data instanceof Backbone.Collection ) {
		let newData = [];
		data = data.models;
		_.map( data, ( model, index ) => {
			newData[index] = { ...toJSON( model, ignores, uniqid_id_rand ) };
			if ( newData[index].settings.uniqid_id !== undefined && uniqid_id_rand ) {
				delete newData[index].settings.uniqid_id;
				newData[index].settings.uniqid_id = uniqid.process();
			}
		} );
		return newData;
	} else {
		let newData = {};
		if ( data instanceof Backbone.Model ) {
			let cid = data.cid !== undefined ? data.cid : false;
			data = { ...data.toJSON() };
			if ( data.settings.uniqid_id !== undefined && uniqid_id_rand ) {
				delete data.settings.uniqid_id;
				data.settings.uniqid_id = uniqid.process();
			}
		}

		_.map( data, ( value, name ) => {
			if ( ignores.indexOf( name ) == -1 ) {
				if ( value instanceof Object || value instanceof Array ) {
					newData[name] = toJSON( value, ignores, uniqid_id_rand  );
				} else if ( name === 'uniqid_id' && uniqid_id_rand ) {
					newData[name] = uniqid.process();
				} else if ( value !== '' ) {
					newData[name] = value;
				}
			}
		} );
		return newData;
	}
}

/**
 * init thirparty script
 * ex: colorpicker, datepicker
 */
function init_thirdparty_scripts( model ) {
	// colorPicker
	let inputs = $( '.pa-colorpicker-input' );
	for ( let i = 0; i < inputs.length; i++ ) {
		let input = inputs[i];
		$( input ).colorpicker();
	}

	// maps
	let maps = $( '.pa_google_map.form-horizontal' );
	for ( let i = 0; i < maps.length; i++ ) {
		let map = maps[i];
		new GoogleMap( $( map ), model ).render();
	}

	// iconpicker
	$( '.pa-iconpicker-input' ).iconpicker({
		hideOnSelect: true
	});

	// datepicker
	let dateInputs = $( '.pa-datepicker-input' )
	for ( let i = 0; i < dateInputs.length; i++ ) {
		let input = $( dateInputs[i] );
		input.datepicker({
			'format'	: input.data( 'format' ) ? input.data( 'format' ) : 'mm/dd/yyyy'
		}).on('changeDate', (e) => {
		    $(e.target).datepicker('hide');
		});
	}

	// timepicker
	let dateTimeInputs = $( '.pa-datetimepicker-input' );
	for ( let i = 0; i < dateTimeInputs.length; i++ ) {
		let input = $( dateTimeInputs[i] );
		input.datetimepicker({
			format: input.data( 'format' ) ? input.data( 'format' ) : 'mm/dd/yyyy - hh:ii',
	        autoclose: true,
	        todayBtn: true,
	        pickerPosition: 'top-right'
		});
	}

	let rangeslider =  $( '.pa-rangeslider-input' );
	for ( let i = 0; i < rangeslider.length; i++ ) {
		let range = $( rangeslider[i] );
		let options = {
			grid: range.data( 'grid' ),
			min: range.data( 'min' ) !== undefined ? range.data( 'min' ) : 0,
			max: range.data( 'max' ) !== undefined ? range.data( 'max' ) : 1000,
			step: range.data( 'step' ) !== undefined ? range.data( 'step' ) : 1,
		};
		let prefix = range.data( 'prefix' );
		if ( prefix !== undefined && prefix ) {
			options.prefix = prefix;
		}
		if ( range.data( 'double' ) ) {
			options.type = 'double';
		}
		range.ionRangeSlider( options );
	}

	$( '.image-select .image-responsive' ).each( ( key, image ) => {
		let src = $( image ).attr( 'src' );
		let cloneImage = $( `<img class="pa-image-hover" src="`+src+`" width="200" height="200" />` );
		$( image ).mouseenter( ( e ) => {
			let x = e.pageX - ( $( cloneImage ).width() / 2 );
			let y = e.pageY + 20;
			$( cloneImage ).appendTo( 'body' );
			$( cloneImage ).css({ top: y, left: x });
		}).mousemove( ( e ) => {
			let x = e.pageX - ( $( cloneImage ).width() / 2 );
			let y = e.pageY + 20;
			$( cloneImage ).css({ top: y, left: x });
		}).mouseleave( ( e ) => {
			$( cloneImage ).remove();
		});
	} );

	$( '.image-select label' ).on( 'click', ( e ) => {
		e.preventDefault();
		let button = $( e.target );
		$( '.image-select label' ).removeClass( 'active' );
		if ( $.contains( $( e.target ).parent().get( 0 ), e.target ) ) {
			button = $( e.target ).parents( 'label:first' );
		}
		button.addClass( 'active' );
		button.find( 'input' ).attr( 'checked', true );
		return false;
	} );
}

export default {
	toJSON,
	init_thirdparty_scripts
}