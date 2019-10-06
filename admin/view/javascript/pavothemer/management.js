class Management {

	doc = $( document )

	// extension list
	extensions_area = $( '#extension-list' )

	tabs = {}

	// ajax processing
	ajax = false

	constructor() {
		this.doc.on( 'click', '#pavothemer-puchased-code', this.enterPurchasedCode.bind( this ) );
		this.doc.on( 'click', '.extension-tabs a', this.switchTabs.bind( this ) );
		this.doc.on( 'click', '.btn-download-extension', this.downloadExtension.bind( this ) );
		this.doc.on( 'click', '.toggleActivate', this.toggleActivateExtension.bind( this ) );
		$('.extension-tabs a:first').click();
	}

	enterPurchasedCode( e ) {
		e.preventDefault();
		let button = $( e.target );
		if ( $.contains( button.parent().get( 0 ), e.target ) ) {
			button = $( $( '#pavothemer-puchased-code' ).get( 0 ) );
		}
        let url = button.attr('href');
        if ( this.ajax ) {
          	this.ajax.abort();
          	this.ajax = false;
          	button.find('.fa').removeClass( 'fa-circle-o-notch' ).removeClass( 'fa-spin' ).addClass( 'fa-filter' );
        }

        this.ajax = $.ajax({
          	url: button.attr( 'href' ),
          	type: 'POST',
          	data: {
            	purchased_code: $('input[name="purchased-code"]').val()
          	},
          	beforeSend: () => {
	            button.find('.fa').removeClass( 'fa-filter' ).addClass( 'fa-circle-o-notch fa-spin' );
	            $('#prucahsed-error-notice').remove();
          	}
        }).always( () => {
          	button.find('.fa').removeClass( 'fa-circle-o-notch' ).removeClass( 'fa-spin' ).addClass( 'fa-filter' );
        }).done( ( res ) => {
          	if ( typeof res.status == 'undefined' ) return;

          	if ( typeof res.extension_list != 'undefined' ) {
            	$('.extension-tabs a').removeClass( 'active' );
            	$('.extension-tabs a:first').addClass( 'active' );
            	$( '#extension-list' ).html( res.extension_list );
          	}

          	if ( typeof res.message !== 'undefined' && res.message ) {
	            if ( res.status === false ) {
	              	$('.well').after('<div class="row" id="prucahsed-error-notice"><div class="col-md-12 col-xs-12"><div class="alert alert-danger">' + res.message + '<button type="button" class="close" data-dismiss="alert">&times;</button></div></div></div>');
	            } else {
	              	this.tabs = {};
	              	$( 'input[name="purchased-code"]' ).val('');
	              	$('.well').after('<div class="row" id="prucahsed-error-notice"><div class="col-md-12 col-xs-12"><div class="alert alert-info">' + res.message + '<button type="button" class="close" data-dismiss="alert">&times;</button></div></div></div>');
	            }
          	}

        }).fail( (xhr, ajaxOptions, thrownError) => {
          	this.tabs[url] = false;
          	$('.well').after('<div class="row" id="prucahsed-error-notice"><div class="col-md-12 col-xs-12"><div class="alert alert-danger alert-dismissible">' + thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText + '<button type="button" class="close" data-dismiss="alert">&times;</button></div></div></div>');
        });

        return false;
	}

	/**
	 * switch tab
	 */
	switchTabs( e ) {
		e.preventDefault();
		let button = $( e.target );
        let url = button.attr( 'href' );
        if ( typeof this.tabs[url] !== 'undefined' && this.tabs[url] ) {
          	$( '.extension-tabs' ).find( 'a' ).removeClass( 'active' );
          	button.addClass( 'active' );
          	this.extensions_area.html( this.tabs[url] );
        } else {

          	if ( this.ajax ) {
	            this.ajax.abort();
	            this.ajax = false;
	            $( '.extension-tabs a .fa' ).remove();
	            $( '.extension-tabs a[href="'+url+'"]' ).removeClass( 'active' );
          	}

          	this.ajax = $.ajax({
	            url: url,
	            type: 'POST',
	            beforeSend: () => {
	              button.prepend( '<i class="fa fa-circle-o-notch fa-spin"></i> ' );
	            }
          	}).always( () =>{
            	button.find( '.fa' ).remove();
          	}).done( ( res ) => {

	            if ( typeof res.status == 'undefined' ) return;

	            if ( typeof res.html != 'undefined' ) {
	              	this.tabs[url] = res.html;
	              	$( '.extension-tabs' ).find( 'a' ).removeClass( 'active' );
	              	button.addClass( 'active' );
	              	if ( res.status ) {
		                this.extensions_area.html( res.html );
	              	} else {
		                this.tabs[url] = '<div class="row"><div class="col-md-12 col-xs-12"><div class="alert alert-danger">' + res.html + '</div></div></div>';
		                this.extensions_area.html( this.tabs[url] );
	              	}
	            }
          	}).fail( (xhr, ajaxOptions, thrownError) => {
            	this.tabs[url] = false;
            	this.extensions_area.html('<div class="row"><div class="col-md-12 col-xs-12"><div class="alert alert-danger">' + thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText + '</div></div></div>');
          	});
        }
		return false;
	}

	/**
	 * download extension
	 */
	downloadExtension( e ) {
		e.preventDefault();
		let button = $( e.target );

		if ( this.ajax ) {
			this.ajax.abort();
			this.ajax = false;
		}

		this.ajax = $.ajax({
			url: PA_MANAGEMENT.liveinstall_url,//button.attr( 'href' ),
			type: 'POST',
			data: button.data(),
			beforeSend: () => {
				button.prepend( '<i class="fa fa-circle-o-notch fa-spin"></i>' )
			}
		}).always( () => {
			button.find( 'i' ).remove();
		} ).done( ( res ) => {
			if ( res.status === true ) {
				button.removeClass( 'btn-download-extension' ).addClass( 'toggleActivate btn-success' );
				button.replaceWith( '<a href="" class="toggleActivate btn btn-success pull-right" data-type="'+res.type+'" data-code="'+res.code+'" data-id="'+res.id+'">'+PA_MANAGEMENT.activate_text+'</a>' );
			}
		} ).fail( (xhr, ajaxOptions, thrownError) => {
        	// this.tabs[url] = false;
        	$('#extension-list').html('<div class="row"><div class="col-md-12 col-xs-12"><div class="alert alert-danger">' + thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText + '</div></div></div>');
      	});

		return false;
	}

	/**
	 * toggle activate extension
	 */
	toggleActivateExtension( e ) {
		e.preventDefault();
		let button = $( e.target );
		if ( $.contains( button.parent().get( 0 ), e.target ) ) {
			button = $( $( '.toggleActivate' ).get( 0 ) );
		}

		if ( this.ajax ) {
			this.ajax.abort();
			this.ajax = false;
		}

		this.ajax = $.ajax({
			url: button.hasClass( 'btn-success' ) ? PA_MANAGEMENT.activate_url : PA_MANAGEMENT.deactivate_url,
			type: 'POST',
			data: button.data(),
			beforeSend: () => {
				button.prepend( '<i class="fa fa-circle-o-notch fa-spin"></i>' );
				$( '#prucahsed-error-notice' ).remove();
			}
		}).always( () => {
			button.find( 'i' ).remove();
		} ).done( ( res ) => {
			if ( res.type == 'theme' ) {
				let toggleActivateButtons = this.extensions_area.find( '.toggleActivate' );
				for ( let i = 0; i < toggleActivateButtons.length; i++ ) {
					let newButton = $( toggleActivateButtons[i] );
					if ( res.activated ) {
						newButton.removeClass( 'btn-success' ).addClass( 'btn-warning' );
						newButton.html( PA_MANAGEMENT.deactivate_text );
					} else {
						newButton.removeClass( 'btn-warning' ).addClass( 'btn-success' );
						newButton.html( PA_MANAGEMENT.activate_text );
					}
				}
			} else if ( res.type == 'module' ) {
				if ( res.activated ) {
					button.removeClass( 'btn-success' ).addClass( 'btn-warning' );
					button.html( PA_MANAGEMENT.deactivate_text );
				} else {
					button.removeClass( 'btn-warning' ).addClass( 'btn-success' );
					button.html( PA_MANAGEMENT.activate_text );
				}
			}

			if ( typeof res.message !== 'undefined' && res.message ) {
	            if ( res.activated === false ) {
	              	$('.well:last').after('<div class="row" id="prucahsed-error-notice"><div class="col-md-12 col-xs-12"><div class="alert alert-danger">' + res.message + '<button type="button" class="close" data-dismiss="alert">&times;</button></div></div></div>');
	            } else {
	              	$('.well:last').after('<div class="row" id="prucahsed-error-notice"><div class="col-md-12 col-xs-12"><div class="alert alert-info">' + res.message + '<button type="button" class="close" data-dismiss="alert">&times;</button></div></div></div>');
	            }
          	}
		} ).fail( (xhr, ajaxOptions, thrownError) => {
        	// this.tabs[url] = false;
        	$('#extension-list').html('<div class="row"><div class="col-md-12 col-xs-12"><div class="alert alert-danger">' + thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText + '</div></div></div>');
      	});
		return false;
	}

}

$( document ).ready( () => {
	new Management();
} );