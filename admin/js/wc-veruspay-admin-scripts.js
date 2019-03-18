storecurrency = veruspay_admin_params.storecurrency;
var coin = ''; 
var lastPrice = function( coin ) {
    var bkcolor = "transparent";
    var newval = "";
    var lastval = jQuery( '.wc_veruspay_fiat_rate_'+coin ).text();
    jQuery.ajax({
        type: 'post',
        url: 'https://veruspay.io/api/',
        data: {currency: storecurrency, ticker: coin},
        success: function(response){
            newval = response;
            if ( lastval > newval ) {
                bkcolor = "red";
            }
            if ( lastval < newval ) {
                bkcolor = "green";
            }
            jQuery( '.wc_veruspay_fiat_rate_'+coin ).hide();
            jQuery( '.wc_veruspay_fiat_rate_'+coin ).text(newval).css('background-color',bkcolor);
            jQuery( '.wc_veruspay_fiat_rate_'+coin ).fadeIn(800).queue( function(next){ 
                jQuery(this).css( 'background-color', 'transparent' ); 
                next(); 
              });
        }
    });
    
}
setInterval( function() {
	lastPrice( 'vrsc' );
	lastPrice( 'arrr' );
	var nowtime = jQuery.now();
}, (60000));

jQuery( function( $ ) {
	$( '.wc_veruspay_set_css' ).closest( 'div' ).addClass( 'wc_veruspay_wrapper' );
		// Conditional fields for discount/fee section
	if ( $( '.wc_veruspay_setdiscount' ).is( ':checked' ) ) {
		$( '.wc_veruspay_discount-toggle' ).closest('tr').show();
	}
	else { $( '.wc_veruspay_discount-toggle' ).closest('tr').hide();
	}
	$( '.wc_veruspay_setdiscount' ).change( function( event ) {
		if ( $( '.wc_veruspay_setdiscount' ).is( ':checked' ) ) {
			$( '.wc_veruspay_discount-toggle' ).closest('tr').show();
		}
		if ( ! $( '.wc_veruspay_setdiscount' ).is( ':checked' ) ) {
			$( '.wc_veruspay_discount-toggle' ).closest('tr').hide();
		}
	});

		// Conditional fields for wallet Settings
	$(document).ready(function() {
		if ( $('.wc_veruspay_togglewallet').hasClass( 'wallet_updated' ) ) {
			location.reload();
		}
		$( '.wc_veruspay_walletsettings-toggle' ).closest('tr').toggleClass('wc_veruspay_set_css');
		$( '.wc_veruspay_walletsettings-toggle' ).toggleClass('wc_veruspay_set_css');
		$( '.wc_veruspay_addresses-toggle' ).closest('tr').toggleClass('wc_veruspay_set_css');
		$( '.wc_veruspay_addresses-toggle' ).toggleClass('wc_veruspay_set_css');
		$( '.wc_veruspay_customization-toggle' ).closest('tr').toggleClass('wc_veruspay_set_css');
		$( '.wc_veruspay_options-toggle' ).closest('tr').toggleClass('wc_veruspay_set_css');
		
		$( '.wc_veruspay_toggleaddr' ).click(function(e) {
			$( '.wc_veruspay_addresses-toggle' ).closest('tr').toggleClass('wc_veruspay_set_css');
			$( '.wc_veruspay_addresses-toggle' ).toggleClass('wc_veruspay_set_css');
		});
		$('.wc_veruspay_togglewallet').click(function(e) {
			$( '.wc_veruspay_walletsettings-toggle' ).closest('tr').toggleClass('wc_veruspay_set_css');
			$( '.wc_veruspay_walletsettings-toggle' ).toggleClass('wc_veruspay_set_css');
		});
		$('.wc_veruspay_togglecust').click(function(e) {
			$( '.wc_veruspay_customization-toggle' ).closest('tr').toggleClass('wc_veruspay_set_css');
		});
		$('.wc_veruspay_toggleoptions').click(function(e) {
			$( '.wc_veruspay_options-toggle' ).closest('tr').toggleClass('wc_veruspay_set_css');
		});
	});
});