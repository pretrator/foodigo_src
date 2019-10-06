// import GoogleMapsLoader from 'google-maps';

export default class GoogleMap {

	constructor( el = '' ) {
		this.el = el;
		this.$el = $( el );

		this.data = this.$el.data();
		this.render();
	}

	render() {
		if ( this.$el.length == 0 || typeof google === 'undefined' ) return;
		// GoogleMapsLoader.KEY = this.data.key !== undefined && this.data.key ? this.data.key : 'AIzaSyB9ytDC_XmgWVG7CMMPvNpvz9t58jfu4j0';
		// GoogleMapsLoader.LIBRARIES = ['geometry', 'places'];
		// GoogleMapsLoader.load( ( google ) => {
			let data = { ...this.data };
			if ( data.lat && data.lng ) {
				data.center = {
					lat: data.lat,
					lng: data.lng
				}
			}
			data.mapTypeControl = data.maptypecontrol;
			data.zoomControl = data.zoomcontrol;
			data.mapTypeId = data.maptypeid;

			this.mapData = data;
			this.map = new google.maps.Map( this.el, this.mapData );
			/**
			 * create marker
			 */
			this.createMarker();
		// } );
	}

	/**
	 * create marker center
	 */
	createMarker() {

		// set bounds
		// this.bounds = new google.maps.LatLngBounds();
		let options = {
	     	 	map 		: this.map,
	          	title 		: this.mapData.place_name,
	          	position	: this.mapData.center
	        };
        this.marker = new google.maps.Marker( options );

        let infowindow = new google.maps.InfoWindow({
		    content: this.marker.getTitle()
		});

        this.marker.addListener('click', ( e ) => {
			infowindow.open( this.map, this.marker );
		});

        // this.bounds.extend( options.position );
        // this.map.fitBounds( this.bounds );
	}

}