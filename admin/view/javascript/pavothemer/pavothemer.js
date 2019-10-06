import DemoImport from './src/demo-import';

class InstallProgress {

	ajax = false

	document = $( document )

	/**
	 * constructor class
	 */
	constructor() {
		this.document.on( 'click', '.refresh-demo', this.refeshDemo.bind( this ) );
		this.document.on( 'click', '.btn-demo', this.openModal.bind( this ) );
		// create export
		this.document.on( 'click', '#pavothemer-export', this._exportHandler.bind( this ) );
		// delete export
		this.document.on( 'click', '.pavothemer-delete-export', this._deleteExportHandler.bind( this ) );
		this.document.on( 'click', '#pavothemer-delete-all-export', this._deleteAllExportHandler.bind( this ) );
		// download export
		this.document.on( 'click', '.pavothemer-download', this._downloadExportHandler.bind( this ) );
		// import
		this.document.on( 'click', '#pavothemer-import', this._importHandler.bind( this ) );
		this.document.on( 'click', '.pavothemer-import', this._importProfileHandler.bind( this ) );
	}

	/**
	 * refresh demo
	 */
	refeshDemo( e ) {
		e.preventDefault();
		let button = $( e.target );
		if ( $.contains( $( '.refresh-demo' ).get( 0 ), e.target ) ) {
			button = $( $( '.refresh-demo' ).get( 0 ) );
		}

		let icon = button.find( 'i' );
		let href = button.attr( 'href' );

		$.ajax({
			url: href,
			type: 'GET',
			beforeSend: () => {
				icon.addClass( 'fa-spin' );
			}
		}).always( () => {
			icon.removeClass( 'fa-spin' );
		} ).done( ( html ) => {
			$( '#pavo-demos-list' ).replaceWith( html );
		} ).fail( ( xhr, ajaxOptions, thrownError ) => {
		  	alert( xhr.responseText );
		} );

		return false;
	}

	/**
	 * open modal
	 */
	openModal( e ) {
		e.preventDefault();
		let button = $( e.target );
		let demoURL = button.attr( 'href' );
		let data = button.data();
		$.magnificPopup.open({
		    // modal: true,
		    showCloseBtn: true,
		    closeOnBgClick: false,
		  	items: {
			    src: new DemoImport( button.data() ).render().el,
			    type: 'inline',
			    width: '80%'
		  	}
		});
		return false;
	}

	/**
	 * export handler
	 */
	_exportHandler( e ) {
		e.preventDefault();
		let button = $( e.target );
		if ( $.contains( $( '#pavothemer-export' ).get( 0 ), e.target ) ) {
			button = $( $( '#pavothemer-export' ).get( 0 ) );
		}

		$.ajax({
          	type: 'POST',
          	url: PAV_PARAMS.exporturl,
          	dataType: 'json',
          	cache: false,
          	contentType: false,
          	processData: false,
          	beforeSend: () => {
      			$('#progress-import').addClass( 'active' );
	            button.button( 'loading' );
	            $('#progress-text').html( '' );
	            $('#progress-bar').removeClass('progress-bar-danger progress-bar-success');
	            $('#progress-bar').css( 'width', 0 );

	            $( '#progress-text' ).html( PAV_PARAMS.text_make_dir );
				$( '#progress-bar' ).width( '16%' );
          	}
        }).always( () => {
          	button.button( 'reset' );
        }).done( ( res ) => {
          	//
          	if ( typeof res.error !== 'undefined' )  {
				$( '#progress-text' ).html('<span class="text-danger">' + res.error + '</span>');
				$( '#progress-bar' ).addClass( 'progress-bar-danger' );
			}

			if ( typeof res.text !== 'undefined' ) {
				$( '#progress-text' ).html( res.text );
			}

			if ( typeof res.success !== 'undefined' ) {
				$( '#progress-bar' ).addClass( 'progress-bar-success' );
				$( '#progress-bar' ).width( '100%' );
				$( '#progress-text' ).html('<span class="text-success">' + res.success + '</span>');
			}

			if ( typeof res.next !== 'undefined' ) {
				this._ajaxRequest( res.next, res );
			}
        }).fail( ( xhr, ajaxOptions, thrownError ) => {
  			$('#progress-import').removeClass( 'active' );
          	$('#progress-text').html('<div class="text-danger">' + thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText + '</div>');
        });
		return false;
	}

	/**
	 * ajax request
	 */
	_ajaxRequest( url = '', data ) {

		if ( this.ajax ) {
			this.ajax.abort();
			this.ajax = false;
		}

		this.ajax = $.ajax({
			url: url,
			data: this.data,

			beforeSend: () => {
      			$('#progress-import').addClass( 'active' );
				if ( typeof data.progress_text !== 'undefined' ) {
					$( '#progress-text' ).html( PAV_PARAMS.text_make_dir );
				}
				if ( typeof data.progress_percent !== 'undefined' ) {
					$( '#progress-bar' ).width( data.progress_percent + '%' );
				}
			}
		}).always( () => {

		} ).done( ( res ) => {
			if ( typeof res.error !== 'undefined' )  {
				$( '#progress-text' ).html('<span class="text-danger">' + res.error + '</span>');
				$( '.progress-bar' ).addClass( 'progress-bar-danger' );
			}

			if ( typeof res.text !== 'undefined' ) {
				$( '#progress-text' ).html( res.text );
			}

			if ( typeof res.success !== 'undefined' ) {
				$( '#progress-bar' ).addClass( 'progress-bar-success' );
				$( '#progress-bar' ).width( '100%' );
				$( '#progress-text' ).html('<span class="text-success">' + res.success + '</span>');
			}

			if ( typeof res.next !== 'undefined' ) {
				this._ajaxRequest( res.next, res );
			}
			if ( typeof res.table !== 'undefined' ) {
                $( '#sample-histories-table' ).replaceWith( res.table );
          	}
		} ).fail( ( xhr, ajaxOptions, thrownError ) => {
			$('#progress-import').removeClass( 'active' );
			$( '.progress-text' ).html('<span class="text-danger">' + xhr.responseText + '</span>');
		} );
	}

	/**
	 * delete export profile hanlder
	 */
	_deleteExportHandler( e ) {
		e.preventDefault();
		let button = $( e.target );
		if ( $.contains( button.parent().get( 0 ), e.target ) ) {
			button = $( $( '.pavothemer-delete-export' ).get( 0 ) );
		}

		if ( confirm( PAV_PARAMS.text_confirm ) ) {
			let sample = button.data( 'sample' );
			let theme = button.data( 'theme' );

			$.ajax({
				url: button.attr( 'href' ),
				type: 'POST',
				data: {
				  	sample: sample,
				  	theme: theme
				},
				beforeSend: () =>  {
				  	button.button( 'loading' );
				  	$('#progress-import').addClass( 'active' );
				  	$('#progress-bar').css( 'width', 0 );
				  	$('#progress-bar').removeClass('progress-bar-danger');
				  	$('#progress-text').html( '' );
				}
			}).always( () => {
				button.button( 'reset' );
			}).done( ( res ) => {
				if ( typeof res.error !== 'undefined' ) {
				  	$('#progress-bar').addClass('progress-bar-danger');
				  	$('#progress-text').html('<div class="text-danger">' + res.error + '</div>');
				}
				if ( typeof res.success !== 'undefined' ) {
				  	$('#progress-bar').addClass('progress-bar-success');
				  	$('#progress-text').html('<span class="text-success">' + res.success + '</span>');
				}
				if ( typeof res.table !== 'undefined' ) {
				  	$( '#sample-histories-table' ).replaceWith( res.table );
				}
			}).fail( ( xhr, ajaxOptions, thrownError ) => {
				$('#progress-import').removeClass( 'active' );
				$('#progress-bar').addClass('progress-bar-danger');
				$('#progress-text').html('<div class="text-danger">' + thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText + '</div>');
			});
        }
		return false;
	}

	/**
	 * delete all export
	 */
	_deleteAllExportHandler( e ) {
		e.preventDefault();
		let button = $( e.target );
		if ( $.contains( button.parent().get( 0 ), e.target ) ) {
			button = $( $( '#pavothemer-delete-all-export' ).get( 0 ) );
		}
        let totals = $( '#sample-histories-table input[name^="selected"]' ).serializeArray();
        let data = [];

        if ( totals.length == 0 ) {
          	alert( PAV_PARAMS.empty_select );
        } else if ( confirm( PAV_PARAMS.text_confirm ) ) {
          	for ( let i = 0; i < totals.length; i++ ) {
            	data.push( totals[i].value );
          	}
          	$('#progress-text').removeClass( 'progress-bar-danger' ).html('');
          	if ( data.length > 0 ) {
	            let button = $( '#' + data[0] ).find( '.pavothemer-delete-export' );
	            $('#progress-bar').removeClass( 'progress-bar-danger' );
	            this.next_delete( button, 0, totals.length, data );
          	}
        }

        return false;
	}

	next_delete( button, step, totals, selecteds ) {
        step++;
        let tr = button.parents('tr:first');
        let data = [];
        data.push({
          	name: 'multiple',
          	value: true
        });
        data.push({
          	name: 'sample',
          	value: button.data('sample')
        });
        data.push({
          	name: 'theme',
          	value: button.data('theme')
        });

        $.ajax({
          	url: button.attr( 'href' ),
          	type: 'POST',
          	data: data,
          	beforeSend: () => {
          		$('#progress-import').removeClass( 'active' );
            	button.button( 'loading' );
            	// $('#progress-text').removeClass( 'progress-bar-danger' ).html('');
          	}
        }).always(() => {
          button.button( 'reset' );
        }).done( ( res ) => {
          	$('#progress-bar').css( 'width', ( parseInt( step ) / totals ) * 100 + '%' );
          	if ( typeof res.success !== 'undefined' ) {
            	$('#progress-bar').removeClass( 'progress-bar-danger' ).addClass( 'progress-bar-success' );
            	$('#progress-text').append('<span class="text-success">' + res.success + '</span><hr />');
          	} else if ( typeof res.error !== 'undefined' ) {
            	$('#progress-bar').removeClass( 'progress-bar-success' ).addClass( 'progress-bar-danger' );
            	$('#progress-text').append('<span class="text-danger">' + res.error + '</span><hr />');
          	}

          	if ( step <= totals ) {
	            let nextItem = selecteds.slice( step, step + 1 );
	            let nextButton = typeof nextItem[0] !== 'undefined' ? $( '#' + nextItem[0] ).find( '.pavothemer-delete-export' ) : false;
	            if ( res.status && nextButton && nextButton.length === 1 ) {
	              	this.next_delete( nextButton, step, totals, selecteds );
	            }
      		} else {
            	$('#sample-histories-table input[type="checkbox"]').prop( 'selected', false );
          	}
          	if ( typeof res.status !== 'undefined' && res.status === true ) {
            	tr.remove();
          	}
        }).fail( ( xhr, ajaxOptions, thrownError ) => {
        	$('#progress-import').removeClass( 'active' );
          	$('#progress-bar').addClass('progress-bar-danger');
          	$('#progress-text').append('<div class="text-danger">' + thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText + '</div><hr />');
        });
  	}

	/**
	 * download export handler
	 */
	_downloadExportHandler( e ) {
		e.preventDefault();
		let button = $( e.target );
		if ( $.contains( button.parent().get( 0 ), e.target ) ) {
			button = $( button.parent().find( '.pavothemer-download' ).get( 0 ) );
		}
        let profile = button.data( 'sample' );

        $.ajax({
          	url: button.attr( 'href' ),
          	type: 'POST',
          	beforeSend: () => {
            	button.button( 'loading' );
            	$('#progress-text').html( '' );
          	}
        }).always( () => {
          	button.button( 'reset' );
          	$('#progress-bar').removeClass('progress-bar-danger');
          	$('#progress-text').html('');
        }).done( ( res ) => {
          	if ( typeof res.url !== 'undefined' ) {
            	window.location.href = res.url;
          	}
        }).fail( ( xhr, ajaxOptions, thrownError ) => {
        	$('#progress-import').removeClass( 'active' );
          	$('#progress-bar').addClass('progress-bar-danger');
          	$('#progress-text').html('<div class="text-danger">' + thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText + '</div>');
        });

		return false;
	}

	/**
	 * import handler
	 */
	_importHandler( e ) {
		e.preventDefault();
		let button = $( e.target );
		if ( $.contains( button.parent().get( 0 ), e.target ) ) {
			button = $( $( '#pavothemer-import' ).get( 0 ) );
		}

        $('#form-upload').remove();

        $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="import" /></form>');

        $('#form-upload input[name=\'import\']').trigger('click');

        let timer = false;
        if (typeof timer != 'undefined') {
          	clearInterval(timer);
        }

        timer = setInterval( () => {
          	if ($('#form-upload input[name=\'import\']').val() != '') {
	            clearInterval(timer);

	            $('#progress-import .progress-bar').attr('aria-valuenow', 0);
	            $('#progress-import .progress-bar').css('width', '0%');

	            $.ajax({
					url: button.attr( 'href' ),
					type: 'post',
					dataType: 'json',
					data: new FormData($('#form-upload')[0]),
					cache: false,
					contentType: false,
					processData: false,
					beforeSend: () => {
						button.button('loading');
	            		$('#progress-import').addClass( 'active' );
						$('#progress-text').html( PAV_PARAMS.uploading_text );
						$('#progress-bar').removeClass('progress-bar-danger progress-bar-success').css('width', 10 + '%' );
					}
	            }).always( () => {
	              button.button('reset');
	            }).done( (res) =>  {
	              	$('.alert-dismissible').remove();

	              	if ( typeof res.error !== 'undefined' ) {
		                $('#progress-bar').addClass('progress-bar-danger');
		                $('#progress-text').html('<div class="text-danger">' + res.error + '</div>');
	              	}
	              	if ( typeof res.success !== 'undefined' ) {
		                $('#progress-bar').addClass('progress-bar-success');
		                $('#progress-text').html('<span class="text-success">' + res.success + '</span>');
	              	}

					if ( typeof res.text !== 'undefined' ) {
						$( '#progress-text' ).html( res.text );
					}

	              	if ( typeof res.table !== 'undefined' ) {
	                	$( '#sample-histories-table' ).replaceWith( res.table );
	              	}
	              	if ( res.next ) {
		                this._ajaxRequest( res.next, res );
	              	}
	              	if ( typeof res.progress_percent !== 'undefined' ) {
		                $('#progress-bar').css( 'width', res.progress_percent + '%' );
	              	}
	            }).fail( (xhr, ajaxOptions, thrownError) => {
	            	$('#progress-import').removeClass( 'active' );
	              	$('#progress-text').html('<div class="text-danger">' + thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText + '</div>');
	            });
          	}
        }, 500 );
		return false;
	}

	/**
	 * profile handler
	 */
	_importProfileHandler( e ) {
		e.preventDefault();
        if ( confirm( PAV_PARAMS.confirm_import ) ) {
			let button = $( e.target );
			if ( $.contains( button.parent().get( 0 ), e.target ) ) {
				button = $( $( '.pavothemer-import' ).get( 0 ) );
			}

          	$.ajax({
	            url: button.attr('href'),
	            type: 'POST',
	            data: {
	              	folder: button.data('sample')
	            },
	            beforeSend: () => {
	              	button.button('loading');
	              	$('#progress-bar').removeClass('progress-bar-danger').css('width', 10 + '%' );
	              	$('#progress-text').html( button.data( 'loading-text' ) );
	            }
          	}).always( () => {
            	button.button('reset');
          	}).done( ( res ) => {
	            // messages
	            if ( typeof res.error !== 'undefined' ) {
	              	$('#progress-bar').addClass('progress-bar-danger');
	              	$('#progress-text').html('<div class="text-danger">' + res.error + '</div>');
	            }

				if ( typeof res.text !== 'undefined' ) {
					$( '#progress-text' ).html( res.text );
				}

	            if ( typeof res.success !== 'undefined' ) {
	              	$('#progress-bar').css( 'width', res.progress_percent + '%' );
	              	$('#progress-bar').addClass('progress-bar-success');
	              	$('#progress-text').html('<span class="text-success">' + res.success + '</span>');
	            }

	            if ( typeof res.next !== 'undefined' ) {
	              	this._ajaxRequest( res.next, res );
	            }
          	}).fail( ( xhr, ajaxOptions, thrownError ) => {
        		$('#progress-import').removeClass( 'active' );
	            $('#progress-bar').addClass('progress-bar-danger');
	            $('#progress-text').html('<div class="text-danger">' + thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText + '</div>');
          	});
        }

        return false;
	}

}

$( document ).ready( () => {
	new InstallProgress();
} )