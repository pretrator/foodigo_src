import Backbone from 'Backbone';
import _ from 'underscore';
import $ from 'jquery';

export default class Import extends Backbone.View {

	ajax = false

	initialize( data ) {
		this.data = data;
		this.template = $( '#tmpl-import-manager' ).html();
		this.events = {
			'click #pav-install:not(.disabled)' 		: '_installHandler',
			'click #pav-mass-install:not(.disabled)' 	: '_massInstallHandler'
		}
	}

	render() {
		this.setElement( _.template( this.template, { variable: 'data' } )( this.data ) );
		return this;
	}

	_installHandler( e ) {
		e.preventDefault();

		this._importRequest( PAV_PARAMS.liveinstall );

		return false;
	}

	_massInstallHandler( e ) {
		e.preventDefault();

		if ( confirm( PAV_PARAMS.confirm_mass_import ) ) {
			this._importRequest( PAV_PARAMS.massinstall );
		}

		return false;
	}

	_importRequest( url = '' ) {
		this.$( '.progress-bar' ).removeClass( 'progress-bar-danger progress-bar-success' ).width(0);
		if ( this.ajax ) {
			this.ajax.abort();
			this.ajax = false;
		}

		this.ajax = $.ajax({
			url: url,
			type: 'POST',
			data: this.data,
			beforeSend: () => {
				this.$( '#progress-install' ).addClass( 'active' );
				this.$( '.progress-text' ).html( PAV_PARAMS.text_make_dir );
				this.$( '.progress-bar' ).width( '10%' );
				this.$( '#pav-install, #pav-mass-install' ).addClass( 'disabled' ).attr( 'disabled', true );
				this.$( '.mfp-close' ).addClass( 'hide' );
			}
		}).always( () => {

		} ).done( ( res ) => {
			if ( typeof res.error !== 'undefined' )  {
				this.$( '.progress-text' ).html('<span class="text-danger">' + res.error + '</span>');
				this.$( '.progress-bar' ).addClass( 'progress-bar-danger' );
				this.$( '#pav-install, #pav-mass-install' ).removeClass( 'disabled' ).attr( 'disabled', false );
				this.$( '.mfp-close' ).removeClass( 'hide' );
			}

			if ( typeof res.text !== 'undefined' ) {
				this.$( '.progress-text' ).html( res.text );
			}

			if ( typeof res.next !== 'undefined' ) {
				this._ajaxRequest( res.next, res );
			}
		} ).fail( ( xhr, ajaxOptions, thrownError ) => {
			this.$( '#progress-install' ).removeClass( 'active' );
			this.$( '.mfp-close' ).removeClass( 'hide' );
			this.$( '.progress-text' ).html('<span class="text-danger">' + xhr.responseText + '</span>');
			this.$( '#pav-install, #pav-mass-install' ).removeClass( 'disabled' ).attr( 'disabled', false );
		} );
	}

	_ajaxRequest( url = '', data = {} ) {

		if ( this.ajax ) {
			this.ajax.abort();
			this.ajax = false;
		}

		this.ajax = $.ajax({
			url: url,
			data: this.data,
			beforeSend: () => {
				if ( typeof data.progress_text !== 'undefined' ) {
					this.$( '.progress-text' ).html( PAV_PARAMS.text_make_dir );
				}
				if ( typeof data.progress_percent !== 'undefined' ) {
					this.$( '.progress-bar' ).width( data.progress_percent + '%' );
				}
			}
		}).always( () => {

		} ).done( ( res ) => {
			if ( typeof res.error !== 'undefined' ) {
				this.$( '.mfp-close' ).removeClass( 'hide' );
				this.$( '.progress-text' ).html('<span class="text-danger">' + res.error + '</span>');
				this.$( '.progress-bar' ).addClass( 'progress-bar-danger' );
				this.$( '#pav-install, #pav-mass-install' ).removeClass( 'disabled' ).attr( 'disabled', false );
			}

			if ( typeof res.text !== 'undefined' ) {
				this.$( '.progress-text' ).html( res.text );
			}

			if ( typeof res.success !== 'undefined' ) {
				this.$( '.mfp-close' ).removeClass( 'hide' );
				this.$( '.progress-bar' ).addClass( 'progress-bar-success' );
				this.$( '.progress-bar' ).width( '100%' );
				this.$( '.progress-text' ).html('<span class="text-success">' + res.success + '</span>');
				this.$( '#pav-install, #pav-mass-install' ).addClass( 'disabled' ).attr( 'disabled', false );
			}

			if ( typeof res.next !== 'undefined' ) {
				this._ajaxRequest( res.next, res );
			}

          	if ( typeof res.refresh !== undefined && res.refresh ) {
          		$.ajax({
          			url: res.refresh,
          			type: 'GET'
          		}).done((res) => {

          		}).fail(() => {

          		});
          	}
		} ).fail( ( xhr, ajaxOptions, thrownError ) => {
			this.$( '.mfp-close' ).removeClass( 'hide' );
			this.$( '#pav-install, #pav-mass-install' ).removeClass( 'disabled' ).attr( 'disabled', false );
			this.$( '.progress-text' ).html('<span class="text-danger">' + xhr.responseText + '</span>');
		} );
	}

}