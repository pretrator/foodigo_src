import $ from 'jquery';

export default class VideoResponsive {

	constructor( el = '' ) {
		this.$el = $( el );
		if ( this.$el.length == 0 ) return;
		this.url = this.$el.data( 'video' );

		this.render();
	}

	render() {
		let iframe = '<iframe src="' + this.url + '" class="pa-video-bg-iframe" width="100%" height="100%" frameborder="0" allowfullscreen="0"></iframe>';
		this.$el.append( iframe );

		/**
		 * set iframe size
		 */
		this.setSize();

		return this;
	}

	/**
	 * set size
	 */
	setSize() {
		let iframe = this.$el.find( '.pa-video-bg-iframe' );
		let eleWidth = this.$el.outerWidth();
		let eleHeight = this.$el.outerHeight();
		let ratio = eleWidth / eleHeight;

		let videoWidth = iframe.outerWidth();
		let videoHeight = iframe.outerHeight();

		if ( ratio > 16 / 9 ) {
			videoHeight = eleWidth * 9 / 16;
			let margin = ( videoHeight - eleHeight ) / 2;
			iframe.css({ width: eleWidth, height: videoHeight, 'margin-top': - margin });
		} else {
			videoWidth = eleHeight * 16 / 9;
			let margin = ( videoWidth - eleWidth ) / 2;
			iframe.css({ width: eleWidth, height: videoHeight, 'margin-left': - margin });
			iframe.css({ width: videoWidth, height: eleHeight });
		}
	}

}