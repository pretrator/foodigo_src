import $ from 'jquery';
import select2 from 'select2';
import 'select2/dist/css/select2';
import 'select2-bootstrap-theme/dist/select2-bootstrap';

class FontSelector {

	constructor( el = '' ) {
		this.el = el;
		this.$el = $( el );
		this.render();
	}

	render() {

		// $.fn.select2.defaults.set( "theme", "bootstrap" );
		for ( let i = 0; i < this.$el.length; i++ ) {
			let el = this.$el.get( i );
			$( el ).select2({
				theme: 'bootstrap',
				templateResult: ( state ) => {
					if (!state.id) {
					    return state.text;
				  	}
				  	let extra = state.id.toLowerCase().split( ' ' ).join('-');
				  	var $state = $(
					    '<span>'+ state.text +'</span><span class="pavo-font '+extra+'"></span>'
				  	);
				  	// console.log('<span class="pavo-font '+extra+'"></span>');
				  	return $state;
				}
			});
		}
	}

}

export default FontSelector;