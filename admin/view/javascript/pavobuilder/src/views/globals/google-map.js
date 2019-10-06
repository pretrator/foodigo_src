import $ from 'jquery';
import Backbone from 'Backbone';
import _ from 'underscore';
import serializeJSON from 'jquery-serializejson';
import GoogleMapsLoader from 'google-maps';

export default class GoogleMap extends Backbone.View {

	defaults() {
		return {
	          	center: { lat: -33.8688, lng: 151.2195 },
	          	zoom: 13,
	          	mapTypeControl: 1,
	          	mapTypeId: 'roadmap',
	          	draggable: 1,
	          	scrollwheel: 1,
	          	zoomControl: 1
	        };
	}

	initialize ( $el, model ) {
		this.setElement( $el );

		let data = this.$( 'input, select, textarea' ).serializeJSON();
		this.model = model;

		this.events = {
			'change input[name="zoomControl"]' : ( e ) => {
				if ( ! this.map ) return false;
				this.map.setOptions({ zoomControl : $( e.target ).is( ':checked' ) });
			},
			'change input[name="zoom"]' : ( e ) => {
				if ( ! this.map ) return false;
				this.map.setZoom( parseInt( $( e.target ).val() ) );
			},
			'change input[name="scrollwheel"]' : ( e ) => {
				if ( ! this.map ) return false;
				this.map.setOptions({ scrollwheel : $( e.target ).is( ':checked' ) });
			},
			'change input[name="draggable"]' : ( e ) => {
				if ( ! this.map ) return false;
				this.map.setOptions({ draggable : $( e.target ).is( ':checked' ) });
			},
			'change select[name="mapTypeId"]'	: ( e ) => {
				if ( ! this.map ) return false;
				this.map.setMapTypeId( $( e.target ).val() );
			},
			'change input[name="mapTypeControl"]' : ( e ) => {
				if ( ! this.map ) return false;
				this.map.setOptions({ mapTypeControl : $( e.target ).is( ':checked' ) });
			}
		};
		this.delegateEvents();
	}

	/**
	 * render google map html
	 */
	render() {
		GoogleMapsLoader.KEY = PA_PARAMS.google_map_api_key ? PA_PARAMS.google_map_api_key : 'AIzaSyB9ytDC_XmgWVG7CMMPvNpvz9t58jfu4j0';
		GoogleMapsLoader.LIBRARIES = ['geometry', 'places'];
		if ( ! GoogleMapsLoader.KEY ) {
			this.$( '.pa-google-map' ).append( '<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> ' + PA_PARAMS.languages.entry_missing_google_map_key + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>' );
		} else {
			GoogleMapsLoader.load( ( google ) => {
				let settings = this.model.get( 'settings' );

				let mapData = { ...this.defaults(), ...settings };

				mapData.center = {
					lat: settings.lat !== undefined && ! isNaN( settings.lat ) && settings.lat ? parseFloat( settings.lat ) : -33.8688,
					lng: settings.lng !== undefined && ! isNaN( settings.lng ) && settings.lng ? parseFloat( settings.lng ) : 151.2195
				}

				mapData.zoom = parseInt( mapData.zoom );
				mapData.draggable = parseInt( mapData.draggable );
				mapData.scrollwheel = parseInt( mapData.scrollwheel );
				mapData.mapTypeControl = parseInt( mapData.mapTypeControl );
				mapData.zoomControl = parseInt( mapData.zoomControl );

				this.markers = [];
				this.map = new google.maps.Map( this.$( '.pa-google-map' ).get( 0 ), mapData );
				this.map.addListener( 'zoom_changed', () => {
				    this.$( 'input[name="zoom"]' ).val( this.map.getZoom() );
			  	});
			  	this.$( 'input[name="zoom"]' ).on( 'change', ( e ) => {
			  		e.preventDefault();
			  		this.map.setZoom( parseInt( $( e.target ).val() ) );
			  		return false;
			  	} );

	          	// For each place, get the icon, name and location.
	          	this.bounds = new google.maps.LatLngBounds();

				if ( settings.place_name !== undefined ) {
					// Create a marker for each place.
					let markerOptions = {
			     	 	map 		: this.map,
			          	// icon: icon,
			          	title 		: settings.place_name,
			          	position	: {
			          		lat: mapData.center.lat,
			          		lng: mapData.center.lng
			          	}
			        };
			        let marker = new google.maps.Marker( markerOptions );

			        this.bounds.extend( markerOptions.position );
			        this.markers.push( marker );

		          	// this.map.fitBounds( this.bounds );
	          		// this.map.panToBounds( this.bounds );
				}

				this.map.setZoom( mapData.zoom );
				let input = this.$( 'input[name="place_name"]' ).get( 0 );
				this.searchBox = new google.maps.places.SearchBox( input );

				// Bias the SearchBox results towards current map's viewport.
		        this.map.addListener('bounds_changed', () => {
		          	this.searchBox.setBounds( this.map.getBounds() );
		        });

		        this.searchBox.addListener( 'places_changed', () => {
		          	var places = this.searchBox.getPlaces();

		      		if ( places.length == 0 ) {
			            return;
		          	}

		          	// Clear out the old markers.
		          	this.markers.map( ( marker ) => {
			            marker.setMap( null );
		          	} );

		          	_.map( places, ( place, i ) => {
		          		if ( i === 0 )
			            	this.setMarker( place );
		          	} );
		          	this.map.fitBounds( this.bounds );
	          		// this.map.panToBounds( this.bounds );
		        });
			} );
		}
	}

	/**
	 * Set marker inside google map initialized
	 */
	setMarker( place ) {
		if ( ! place.geometry ) {
          	console.log("Returned place contains no geometry");
          	return;
        }

        var icon = {
          	url: place.icon,
          	size: new google.maps.Size( 71, 71 ),
          	origin: new google.maps.Point( 0, 0 ),
          	anchor: new google.maps.Point( 17, 34 ),
          	scaledSize: new google.maps.Size( 25, 25 )
        };

        // Create a marker for each place.
        let marker = new google.maps.Marker({
     	 	map: this.map,
          	// icon: icon,
          	title: place.name,
          	position: place.geometry.location
        });
		let lat = marker.position.lat();
		let lng = marker.position.lng();
		// set input value
		this.$( '.pa-google-map-wrapper input[name="lat"]' ).val( lat );
		this.$( '.pa-google-map-wrapper input[name="lng"]' ).val( lng );

        this.markers.push( marker );
        let infowindow = new google.maps.InfoWindow({
		    content: marker.getTitle()
		});

        marker.addListener('click', ( e ) => {
			infowindow.open( this.map, marker );
		});

	    // if ( place.geometry.viewport ) {
	    // 	this.bounds.union( place.geometry.viewport );
	    // } else {
	    	this.bounds.extend( place.geometry.location );
	    // }
	}

}