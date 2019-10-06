// import GoogleMap from './src/google-map';
// import VideoResponsive from './src/video-responsive';

// $( () => {

// 	let google_maps = $( '.pa_google_map' );
// 	for ( let i = 0; i < google_maps.length; i++ ) {
// 		new GoogleMap( google_maps[i] );
// 	}

// 	let videos = $( '.pa-bg-video' );
// 	for ( let i = 0; i < videos.length; i++ ) {
// 		let video = new VideoResponsive( videos );

// 		let resizing = false;
// 		$( window ).resize( function() {

// 			if ( resizing ) return;

// 			resizing = new Promise( ( resolve, reject ) => {
// 				setTimeout( () => {
// 					resolve();
// 				}, 1000 );
// 			} ).then( () => {
// 				video.setSize();
// 				resizing = false;
// 			} );
// 		})
// 	}

// } );

( function($) {

	$.fn.pavo_google_map = function() {

		for ( var i = 0; i < this.length; i++ ) {
			init( this[i] );
		}

		function init ( el ) {
			if ( typeof google === 'undefined' ) return;

			var data = $( el ).data();
			if ( data.lat && data.lng ) {
				data.center = {
					lat: data.lat,
					lng: data.lng
				}
			}
			data.mapTypeControl = data.maptypecontrol;
			data.zoomControl = data.zoomcontrol;
			data.mapTypeId = data.maptypeid;

			var map = new google.maps.Map( el, data );
			/**
			 * create marker
			 */
			createMarker( map, data );
		}

		function createMarker( map, data ) {
			// set bounds
			var options = {
		     	 	map 		: map,
		          	title 		: data.place_name,
		          	position	: data.center
		        };
	        var marker = new google.maps.Marker( options );

	        var infowindow = new google.maps.InfoWindow({
			    content: marker.getTitle()
			});

	        marker.addListener('click', ( e ) => {
				infowindow.open( map, marker );
			});
		}
	}

	/**
	 * background video
	 */
	$.fn.pavo_bg_video = function() {
		for ( var i = 0; i < this.length; i++ ) {
			var video = init( this[i] );
		}

		function init( el ) {
			var iframe = '<iframe src="' + $( el ).data( 'video' ) + '" class="pa-video-bg-iframe" width="100%" height="100%" frameborder="0" allowfullscreen="0"></iframe>';
			$( el ).append( iframe );
			/**
			 * set iframe size
			 */
			setSize( el );

			var resizing = false;
			$( window ).resize( function() {

				if ( resizing ) return;

				resizing = new Promise( ( resolve, reject ) => {
					setTimeout( () => {
						resolve();
					}, 500 );
				} ).then( () => {
					setSize();
					resizing = false;
				} );
			})
		}

		function setSize( el ) {
			var iframe = $( el ).find( '.pa-video-bg-iframe' );
			var eleWidth = $( el ).outerWidth();
			var eleHeight = $( el ).outerHeight();
			var ratio = eleWidth / eleHeight;

			var videoWidth = iframe.outerWidth();
			var videoHeight = iframe.outerHeight();

			if ( ratio > 16 / 9 ) {
				videoHeight = eleWidth * 9 / 16;
				var margin = ( videoHeight - eleHeight ) / 2;
				iframe.css({ width: eleWidth, height: videoHeight, 'margin-top': - margin });
			} else {
				videoWidth = eleHeight * 16 / 9;
				var margin = ( videoWidth - eleWidth ) / 2;
				iframe.css({ width: eleWidth, height: videoHeight, 'margin-left': - margin });
				iframe.css({ width: videoWidth, height: eleHeight });
			}
		}
	}

	$.fn.pavo_searchBox = function() {
		var searchBoxs = $( this );
		for ( var i = 0; i < searchBoxs.length; i++ ) {
			init( $( searchBoxs[i] ) );
		}

		function init( searchBox ) {

			searchBox.find( '.btn' ).on( 'click', function( e ) {
				e.preventDefault();
				open( searchBox );
				return false;
			} );

			searchBox.on( 'click', function(e) {
				var button = $( e.target );
				if ( $.contains( e.target, searchBox.find( '.quick-search-form' ).get(0) ) ) {
					close( searchBox );
				}
				return false;
			} );
			searchBox.on( 'click', '.close', function(e){
				e.preventDefault();
				close( searchBox );
				return false;
			} );

			searchBox.keyup( 'input[name="search"]', function(e) {
				if ( e.keyCode == 13 ) {
					searchBox.find( '.btn' ).click();
				}
			} );
		}

		function open( searchBox ) {
			searchBox.addClass( 'active' );
			$( 'body' ).addClass( 'overflow-y-hidden' );
			setTimeout( function(){
				searchBox.find( 'input[name="search"]' ).focus();
			}, 300 );
		}

		function close( searchBox ) {
			$( 'body' ).removeClass( 'overflow-y-hidden' );
			searchBox.removeClass( 'active' );
		}
	}

	$( document ).on( 'submit', '.pa-contact-form', function(e) {
		e.preventDefault();
		var form = $( e.target );

		$.ajax({
			url: form.attr('action'),
			type: 'POST',
			data: form.serializeArray(),
			beforeSend: function() {
				form.find('.text-danger, .text-success').remove();
				form.addClass( 'processing' );
			}
		}).always(function() {
			form.removeClass( 'processing' );
			form.parent( '.form-group' ).removeClass( 'has-error' );
		}).done(function(res) {
			if ( res.status === false && res.errors !== undefined && res.errors ) {
				$.each( res.errors, function( name, message ) {
					form.find( '[name="'+name+'"]' ).parent( '.form-group:first' ).addClass( 'has-error' );
					form.find( '[name="'+name+'"]' ).parent().append('<div class="text-danger">'+message+'</div>');
				} );
			}

			if ( res.success !== undefined ) {
				form.find('.buttons').prepend('<div class="text-success">'+res.success+'</div>');
			}
		}).fail(function( xhr ) {
			form.append('<div class="text-danger">'+xhr.responseText+'</div>');
		});
		return false;
	} );

	/**
	 * document ready
	 */
	$( document ).ready( function() {
		// google maps
		$( '.pa_google_map' ).pavo_google_map();
		// background videos
		$( '.pa-bg-video' ).pavo_bg_video();
		// search boxs
		$( '.pavo-popup-search' ).pavo_searchBox();

		$( '[data-owlcarousel-options]' ).each(function(){
			var that = $( this );
			var options = that.data('owlcarousel-options');
			options = $.extend({}, {
				rtl: $('html').hasClass('rtl')
			}, options);
			that.on( 'initialized.owl.carousel translated.owl.carousel', function(event) {
				$(event.target).find('.owl-item').removeClass('last-child first-child');
			    var index = event.item.index === null ? 0 : event.item.index - 1;
			    index = index + parseInt( event.page.size );
			    $(event.target).find( '.owl-item' ).eq( index ).addClass('last-child');
			    $(event.target).find( '.owl-item' ).eq( event.item.index ).addClass('first-child');
			} ).owlCarousel( options );
		});
	} );

} )(jQuery);