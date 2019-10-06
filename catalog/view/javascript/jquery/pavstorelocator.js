

	var localtion_pluginurl = 'http://localhost/dev/pav_xstore-1/';
    /**
    * Js Maps Location opallocation-map-preview
    */
    function pavoOnloadMaps( data, url ){
        if( $('#opallocation-map-preview').length > 0 ) {

       		localtion_pluginurl = url;
            initializeLocationsMap( data );
        }
    }
    //window on load
    ///------------------------
    function initializeLocationsMap( places ) {

            // Properties Array
            var mapOptions = {
                zoom: 12,
                maxZoom: 16,
                scrollwheel: false,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                panControl: false,
                zoomControl: true,
                mapTypeControl: false,
                scaleControl: false,
                streetViewControl: true,
                overviewMapControl: false,
                zoomControlOptions: {
                    style: google.maps.ZoomControlStyle.SMALL,
                    position: google.maps.ControlPosition.RIGHT_TOP
                },
                streetViewControlOptions: {
                    position: google.maps.ControlPosition.RIGHT_TOP
                }
            };

            var map = new google.maps.Map( document.getElementById( "opallocation-map-preview" ), mapOptions );

            var bounds = new google.maps.LatLngBounds();

            // Loop to generate marker and infowindow based on places array
            var markers = new Array();

            for ( var i=0; i < places.length; i++ ) {
                var url = places[i].icon;
                var size = new google.maps.Size( 100, 200 );
                if( window.devicePixelRatio > 1.5 ) {
                    if ( places[i].retinaIcon ) {
                        url = places[i].retinaIcon;
                        size = new google.maps.Size( 83, 113 );
                    }
                }

                var image = {
                    url: url,
                    size: size,
                    scaledSize: new google.maps.Size( 32, 32 ),
                    origin: new google.maps.Point( 0, 0 ),
                    anchor: new google.maps.Point( 21, 56 )
                };

                markers[i] = new google.maps.Marker({
                    position: new google.maps.LatLng( places[i].lat, places[i].lng ),
                    map: map,
                    icon: image,
                    title: places[i].title,
                    animation: google.maps.Animation.DROP,
                    visible: true
                });

                bounds.extend( markers[i].getPosition() );

                var boxText = document.createElement( "div" );

                boxText.className = 'map-info-preview media';
                var open = '';
                if( places[i].open ){
                    open = '<div class="location-open">'+places[i].open+'</div>';
                }
                places[i].url = '#';
                boxText.innerHTML = '<div class="media-left"><span class="thumb-link" >' +
                                        '<img class="prop-thumb" src="' + places[i].thumb + '" alt="' + places[i].title + '"/>' +
                                        '</span></div>' +
                                        '<div class="info-container media-body"><h5 class="prop-title"><span class="title-link">' + places[i].title +
                                        '</span></h5><p class="prop-address"><em>' + places[i].address + '</em></p><p><i class="fa fa-phone"></i> <span class="phone-number">' + places[i].phone +
                                        '</span></p><p><i class="fa fa-envelope"></i> <span class="email">' + places[i].email +
                                        '</span></p>'+open+'</div>';

                var myOptions = {
                    content: boxText,
                    disableAutoPan: true,
                    maxWidth: 0,
                    alignBottom: true,
                    pixelOffset: new google.maps.Size( -122, -48 ),
                    zIndex: null,
                    closeBoxMargin: "0 0 -16px -16px",
                    closeBoxURL: localtion_pluginurl+"/image/catalog/marker-close.png",
                    infoBoxClearance: new google.maps.Size( 5, 5 ),
                    isHidden: false,
                    pane: "floatPane",
                    enableEventPropagation: true,
                    contextmenu: true,
                };

               

                var ib = new InfoBox( myOptions );

                attachInfoBoxToMarker( map, markers[i], ib, i );
            }


            map.fitBounds(bounds);

                 /* Marker Clusters */
                var markerClustererOptions = {
                    ignoreHidden: true,
                    maxZoom: 14,
                    styles: [{
                        textColor: '#000000',
                        url: localtion_pluginurl+"/image/catalog/cluster-icon.png",
                        height: 50,
                        width: 30
                    }]
                };

            var markerClusterer = new MarkerClusterer( map, markers, markerClustererOptions );

            function attachInfoBoxToMarker( map, marker, infoBox ,i ){
                google.maps.event.addListener( marker, 'click', function(){
                    var scale = Math.pow( 2, map.getZoom() );
                    var offsety = ( (100/scale) || 0 );
                    var projection = map.getProjection();
                    var markerPosition = marker.getPosition();
                    var markerScreenPosition = projection.fromLatLngToPoint( markerPosition );
                    var pointHalfScreenAbove = new google.maps.Point( markerScreenPosition.x, markerScreenPosition.y - offsety );
                    var aboveMarkerLatLng = projection.fromPointToLatLng( pointHalfScreenAbove );
                    google.maps.event.trigger(map, "closeAllInfoboxes");
                    map.setCenter( aboveMarkerLatLng );
                    infoBox.open( map, marker );
                });
                //Add this event listener to your infobox
                google.maps.event.addListener(map, 'closeAllInfoboxes', function () {
                    infoBox.close();
                });
                
                $(".maker-item"+i).click(function(){
                    //console.log(marker);
                    var scale = Math.pow( 2, map.getZoom() );
                    var offsety = ( (100/scale) || 0 );
                    var projection = map.getProjection();
                    var markerPosition = marker.getPosition();
                    var markerScreenPosition = projection.fromLatLngToPoint( markerPosition );
                    var pointHalfScreenAbove = new google.maps.Point( markerScreenPosition.x, markerScreenPosition.y - offsety );
                    var aboveMarkerLatLng = projection.fromPointToLatLng( pointHalfScreenAbove );
                    google.maps.event.trigger(map, "closeAllInfoboxes");
                    google.maps.event.trigger(marker, 'click');
                    map.setCenter(markerPosition);
                    map.setZoom(scale);
                });
            }

    }// end function map

    $( document ).ready( function(){
    	$('.place-gallery-content').each(function() { // the containers for all your galleries
	        $(this).magnificPopup({
	            delegate: 'a', // the selector for gallery item
	            type: 'image',
	            gallery: {
	              enabled:true
	            }
	        });
	    });

    	$('.location-youtube-button').each(function() { // the containers for all your galleries
	        $(this).magnificPopup({
	            disableOn: 700,
	            type: 'iframe',
	            mainClass: 'mfp-fade',
	            removalDelay: 160,
	            preloader: false,
	            fixedContentPos: false
	        });
	    });

        $( '.location-get-direction' ).each(function() { 
            $( this ).magnificPopup({
                disableOn: 700,
                type: 'iframe',
                mainClass: 'mfp-fade',
                removalDelay: 160,
                preloader: false,
                fixedContentPos: false
            });
        } );

        /**
         * Scrollbar
         */
        $('.store-locations .locations-inner').perfectScrollbar();
    } );
 