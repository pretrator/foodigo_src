/**
 * QuickView to view single product, working automatic
 *
 * @copyright Commercial License By PavoThemes.Com
 * @email pavothemes@gmail.com
 * @visit http://www.pavothemes.com
 */

(function( $ ) {

	$.fn.pavoQuickview = function( initvar ) {

		/**
		 * Create List Layers By JSON Data.
		 */
		this.price = '';
		this.ischanged = false;

		this.work = function(){
			var _this = this;

			$( "body" ).delegate( ".product-layout", "mouseover", function(){
				if( $(this).find(".pav-quickview").length > 0 ){

				} else {
					var html = $("<div class=\"pav-ownstyle pav-quickview\"><a href=\"#\"><i class=\"fa fa-eye\"></i></a></div>");
					if ( $( this ).find( '.product-thumb .image' ).length == 0 ) {
						$( this ).append( html );
					} else {
						$( this ).find( '.product-thumb .image' ).append( html );
					}
				}
			} );

			$("body").delegate( ".pav-quickview" , "click", function() {
				var tmp = $(this).parents( '.product-thumb' ).find("[onclick]").attr("onclick");
				var id = /\d+/.exec(tmp);
				if( !isNaN(id) ){
				 	_this.open( id );
				}
				return false;
			} );
 		},

 		this.open = function( id ){
 			var url = 'index.php?route=extension/module/pavquickview/show&product_id='+id;
 			$.magnificPopup.open({
			  	items: {
			    	src: url, // can be a HTML string, jQuery object, or CSS selector
			    	type: 'iframe',
			    	width:"80%"
			  	},
		      	callbacks: {
			        afterClose: function() {
			          	setTimeout( function() {
				            $('html, body' ).animate({ scrollTop: 0 }, 'slow');
				            $('#cart > ul').load('index.php?route=common/cart/info ul li');
			          	}, 200 );
			        }
		      	}
			});
 		}
		//THIS IS VERY IMPORTANT TO KEEP AT THE END
		return this;
	};

 	$(document).ready( function(){
 		$(document).pavoQuickview().work();
 	} );
})( jQuery );
/***/