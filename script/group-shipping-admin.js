jQuery(document).ready(function(){

    if ( jQuery('.mydatepicker').length ) {
        jQuery('.mydatepicker').datetimepicker( { timeFormat: 'hh:mm tt z' } );
    }

	if ( jQuery('.mytimepicker').length ) {
		jQuery('.mytimepicker').timepicker({ timeFormat: 'hh:mm tt z' }  );
	}

	function show_popup( group_ship ){ 	
		 var src = sw.ajaxurl +'?action=get_mailing_labels&group_ship='+group_ship+'&_ajax_nonce='+sw.nonce;
		 window.open( src, '_blank', "scrollbars=yes,resizable=yes,width=800,height=500");
	}

	jQuery('.mailing-labels-popup').click(function(){
		var group_ship = jQuery( '#group-ship' ).val();
		show_popup( group_ship );
		return false;
		jQuery.ajax({
			  type: 'POST',
			  url: myAjax.ajaxurl,
			  data : {
				  		'action': 'get_mailing_labels',
				  		'group_ship':   group_ship
			  		},
			  cache: false,
			  dataType: "html",
			  success: function(response){
			        alert('The server responded: ' + response);
			    }
		});
		
		return false;
	});
	
	
	jQuery('.print-packing-list-popup').click(function(){
		var group_ship = jQuery( this ).attr("id");
		show_print( group_ship );
		return false;
		jQuery.ajax({
			  type: 'POST',
			  url: myAjax.ajaxurl,
			  data : {
				  		'action': 'packing_list',
				  		'group_ship':   group_ship
			  		},
			  cache: false,
			  dataType: "html",
			  success: function(response){
			        alert('The server responded: ' + response);
			    }
		});
		
		return false;
		
	});
	
	function show_print( group_ship ){ 	
		 var src = sw.ajaxurl +'?action=packing_list&group_ship='+group_ship+'&_ajax_nonce='+sw.nonce;
		 window.open( src, '_blank', "scrollbars=yes,resizable=yes,width=800,height=500");
	}

	jQuery( '#all-order-ids').click( function() {
		var checked = jQuery( this ).is( ":checked" );
		jQuery( ".gs-order-id-checkbox" ).prop( 'checked', checked );
	});



});

var ppiMode = false;

jQuery(document).ready(function($) {

	jQuery( 'div.wrap > h2').click( function() {
		if ( ! ppiMode ) {
			ppiMode = true;
			jQuery('.ppi').addClass( 'ppi-mode-on' );
		} else {
			ppiMode = false;
			jQuery('.ppi').removeClass( 'ppi-mode-on' );
		}
	});

});


