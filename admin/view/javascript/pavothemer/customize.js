import ThemeCustomize from './src/theme-customize';
import common from './src/common';

$( () => {

	ThemeCustomize.render();
	/**
	 * return true iframe will be reloaded
	 */
	ThemeCustomize.customize( 'enable', ( customize, value, refresh, iframe ) => {
		return true;
	} ).customize( 'font-family-base', ( customize, value, refresh, iframe ) => {
		// on change google font
		// console.log(refresh);
		// https://fonts.googleapis.com/css?family=Sofia
		if ( value ) {
			customize._addStyle( 'google-font', 'https://fonts.googleapis.com/css?family=' + value );
			$( iframe ).find( 'body' ).css( { 'font-family' : value } );
		}
		return false;
	} );
} );