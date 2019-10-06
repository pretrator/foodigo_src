import Backbone from 'Backbone';
import _ from 'underscore';

export default class Loader extends Backbone.View {

	initialize( model = { loading: true, callback: () => {} } ) {
		this.model = new Backbone.Model( model );

		// listen loading attribute
		this.listenTo( this.model, 'change:loading', this.render );
		this.listenTo( this.model, 'destroy', this.remove );
	}

	render() {
		if ( this.model.get( 'loading' ) ) {
			this.template = _.template( $( '#pa-loading-template' ).html(), { variable: 'data' } );
			this.setElement( this.template( this.model.toJSON() ) );
			/**
			 * callback after render loader element
			 */
			if ( typeof this.model.get( 'callback' ) == 'function' ) {
				this.model.get( 'callback' ).apply( null, [] );
			}
		} else {
			this.model.destroy();
		}

		return this;
	}

}