import $ from 'jquery';
import FontSelector from './fonts';
import colorpicker from 'bootstrap-colorpicker';
import 'bootstrap-colorpicker/dist/css/bootstrap-colorpicker';
import iconpicker from 'fontawesome-iconpicker';
import 'fontawesome-iconpicker/dist/css/fontawesome-iconpicker.css';
import ionRangeSlider from 'ion-rangeslider';
import 'ion-rangeslider/css/ion.rangeSlider';
import 'ion-rangeslider/css/ion.rangeSlider.skinNice';

$(() => {
	// font selector
	new FontSelector( '.select-google-font' );
	// colorPicker
	let inputs = $( '.pa-colorpicker-input' );
	for ( let i = 0; i < inputs.length; i++ ) {
		let input = inputs[i];
		$( input ).colorpicker();
	}

	// iconpicker
	$( '.pa-iconpicker-input' ).iconpicker();

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
});