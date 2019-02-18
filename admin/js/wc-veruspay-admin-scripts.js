phpexttools = veruspay_admin_params.phpexttools,
storecurrency = veruspay_admin_params.storecurrency;
    
var lastPrice = function() {
    var bkcolor = "transparent";
    var newval = "";
    var lastval = jQuery( '.wc_veruspay_fiat_rate' ).text();
    jQuery.ajax({
        type: 'post',
        url: phpexttools,
        data: {exec: 'price', hash: storecurrency},
        success: function(response){
            newval = response;
            if ( lastval > newval ) {
                bkcolor = "red";
            }
            if ( lastval < newval ) {
                bkcolor = "green";
            }
            jQuery( '.wc_veruspay_fiat_rate' ).hide();
            jQuery( '.wc_veruspay_fiat_rate' ).text(newval).css('background-color',bkcolor);
            jQuery('.wc_veruspay_fiat_rate').fadeIn(800).queue( function(next){ 
                jQuery(this).css('background-color','transparent'); 
                next(); 
              });
        }
    });
    
}
setInterval( function() {
	lastPrice();
	var nowtime = jQuery.now();
}, (60000));
jQuery( function( $ ) {

	$( '.wc_veruspay_set_css' ).closest( 'div' ).addClass( 'wc_veruspay_wrapper' );
		// Conditional fields for discount/fee section
	if ( $( '.wc-gateway-veruspay-setdiscount' ).is( ':checked' ) ) {
		$( '.wc-gateway-veruspay-discount-toggle' ).closest('tr').show();
	}
	else { $( '.wc-gateway-veruspay-discount-toggle' ).closest('tr').hide();
	}
	$( '.wc-gateway-veruspay-setdiscount' ).change( function( event ) {
		if ( $( '.wc-gateway-veruspay-setdiscount' ).is( ':checked' ) ) {
			$( '.wc-gateway-veruspay-discount-toggle' ).closest('tr').show();
		}
		if ( ! $( '.wc-gateway-veruspay-setdiscount' ).is( ':checked' ) ) {
			$( '.wc-gateway-veruspay-discount-toggle' ).closest('tr').hide();
		}
	});
		// Conditional fields for RPC Settings
	$(document).ready(function() {
		if ( $('.wc-veruspay-gateway-togglerpc').hasClass( 'rpc_updated' ) ) {
			location.reload();
		}
		$( '.wc-gateway-veruspay-rpcsettings-toggle' ).closest('tr').addClass('wc_veruspay_set_css');
		$( '.wc-gateway-veruspay-addresses-toggle' ).closest('tr').toggleClass('wc_veruspay_set_css');
		$( '.wc-gateway-veruspay-customization-toggle' ).closest('tr').toggleClass('wc_veruspay_set_css');
		$( '.wc-gateway-veruspay-options-toggle' ).closest('tr').toggleClass('wc_veruspay_set_css');
		
		$( '.wc-veruspay-gateway-toggleaddr' ).click(function(e) {
			$( '.wc-gateway-veruspay-addresses-toggle' ).closest('tr').toggleClass('wc_veruspay_set_css');
		});
		$('.wc-veruspay-gateway-togglerpc').click(function(e) {
			$( '.wc-gateway-veruspay-rpcsettings-toggle' ).closest('tr').toggleClass('wc_veruspay_set_css');
		});
		$('.wc-veruspay-gateway-togglecust').click(function(e) {
			$( '.wc-gateway-veruspay-customization-toggle' ).closest('tr').toggleClass('wc_veruspay_set_css');
		});
		$('.wc-veruspay-gateway-toggleoptions').click(function(e) {
			$( '.wc-gateway-veruspay-options-toggle' ).closest('tr').toggleClass('wc_veruspay_set_css');
		});		
	});
});