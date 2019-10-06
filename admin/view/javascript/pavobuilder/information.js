$.urlParam = function(name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results==null){
       return null;
    }
    else{
       return decodeURI(results[1]) || 0;
    }
}

$(window).load( function (){  
		$.ajax({
		  	url: "index.php?route=extension/module/pavobuilder/buttons&user_token="+$.urlParam('user_token'),
		  	context: document.body
		}).done(function( data ) {
			$('#form-information .note-editor' ).each( function(){
			  	var parent = $(this).parent();
				var button =  $('<a class="btn btn-danger" href="#">Block Builder</a>');
				// $( parent  ).prepend( button );

				var re = /blockbuilder\s+id=(\d+)\s+(breadcrumb=(\d+))/; // alert( $('textarea', parent).val() );

				m = re.exec(  $('textarea', parent).val() );
		
				 console.log( m ); 
				$(parent).prepend( '<p class="pavo-buttons">'+data+'</p>' );

				if( m ){
					$('select#blockbuilder-id option',parent).each( function(){
						if( $(this).val() == m[1] ){
							$(this).attr('selected','selected');
						}else {
							$(this).removeAttr('selected');
						}
					} );
					$('div.note-editor', parent).hide();
					if( m[3] ){
						$('select#showbreabcrumb option',parent).each( function(){
							if( $(this).val() == m[3] ){
								$(this).attr('selected','selected');
							}else {
								$(this).removeAttr('selected');
							}
						} );
					}
				}


				$('select#blockbuilder-id',parent).change( function(){
					// alert( $(this).val() );
					if( $(this).val() > 0 ){
						$('textarea', parent).val( '[blockbuilder id='+$(this).val()+' breadcrumb='+$("#showbreabcrumb").val()+"]" );
						$('div.note-editor', parent).hide();
					}else {
						$('textarea', parent).val( '' );
						$('div.note-editor', parent).show();
					}

				} );

				$('select#showbreabcrumb',parent).change( function(){
					if( $('select#blockbuilder-id',parent).val() > 0 ) { 
						$('textarea', parent).val( '[blockbuilder id='+$('select#blockbuilder-id',parent).val()+' breadcrumb='+$("#showbreabcrumb").val()+"]" );
					}  
				} );
				
			});
		} ); 
} );