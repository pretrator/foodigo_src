$( document ).ready( () => {
	$('#pavobuilder-group-search').autocomplete( { source: ( request, response ) => {
			return response( PAVOBUILDER_PARAMS.source );
		},
		minLength: 2,
		select: ( obj ) => {
			let trs = $( '#pavohomebuilder-layout .table-responsive tbody tr' );
			let val = obj.value;
			$('#pavobuilder-group-search').val( val );
			for ( let i = 0; i < trs.length; i++ ) {
				let tr = trs[i];
				tr = $( tr );
				let group = tr.data( 'group' );
				if ( val !== group ) {
					tr.addClass( 'hide' );
				} else {
					tr.removeClass( 'hide' );
				}
			}
		}
	}).on( 'keyup', ( e ) => {
		if ( $('#pavobuilder-group-search').val() === '' ) {
			$( '#pavohomebuilder-layout .table-responsive tbody tr' ).removeClass( 'hide' );
		}
	} );
} );