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
		if ( $( '#message' ).hasClass( 'updated' ) ) {
			var savedURL = location.href + '&veruspay_settings_saved';
			location.href = savedURL;
			location.reload();
		}
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

		// Refresh balances
		var refreshBalance = function() {
			$( ".wc_veruspay_bal_admin" ).each(function() {
				var ccoin = $(this).attr("data-coin");
				var ctype = $(this).attr("data-type");
				var update = $(this).attr('id');
				if ( $('#wc_veruspay_'+ccoin+'_nostat').length == 0 ) {
					$.ajax({
						type: "POST",
						data: { "veruspayajax":"1", "veruspaycommand":"balance", "coin":ccoin, "type":ctype },
						success: function(response){
							if ( response == 0 ) {
								$("#"+update).removeClass("wc_veruspay_red");
								$('#wc_veruspay_cashout_text-'+ccoin+'-'+ctype).hide();
								$("#"+update).text(response);
								$("#"+update+"-button").attr("data-amount",response);
							}
							if ( response > 0 ) {
								$("#"+update).removeClass("wc_veruspay_red");
								$('#wc_veruspay_cashout_text-'+ccoin+'-'+ctype).fadeIn();
								$("#"+update).text(response);
								$("#"+update+"-button").attr("data-amount",response);
							}
							if ( response.indexOf( "Err:" ) >= 0 ) {
								$("#"+update).addClass("wc_veruspay_red");
								$('#wc_veruspay_cashout_text-'+ccoin+'-'+ctype).hide();
								$("#"+update).text(response);
								$("#"+update+"-button").attr("data-amount",response);
							}
						}
					});
				}
			});
		}
		setInterval( function() {
			refreshBalance();
			var nowtime = $.now();
		}, (15000));
		// - //

		// 1-Click Cashout Initiated
		$('.wc_veruspay_cashout').click(function() {
			var icoin = $(this).attr("data-coin");
			var itype = $(this).attr("data-addrtype");
			var icomm = $(this).attr("data-type");
			var iamount = $(this).attr("data-amount");
			var iaddress = $(this).attr("data-address");

			$('#wc_veruspay_modal_button-cashout').attr("data-coin",icoin);
			$('#wc_veruspay_modal_button-cashout').attr("data-type",icomm);

			$('.wc_veruspay_modal_coin').text(icoin);
			$('.wc_veruspay_modal_type').text(itype);
			$('.wc_veruspay_modal_amount').text(iamount);
			$('.wc_veruspay_modal_address').text(iaddress);
			$('.wc_veruspay_cashout-modalback').fadeIn();
		});
		// - //

		// 1-Click Cashout Cancelled
		$('#wc_veruspay_modal_button-cancel').click(function() {
			$('.wc_veruspay_cashout-modalback').fadeOut();
			$('.wc_veruspay_modal_coin').text('');
			$('.wc_veruspay_modal_type').text('');
			$('.wc_veruspay_modal_amount').text('');
			$('.wc_veruspay_modal_address').text('');
		});
		// - //

		// 1-Click Cashout Confirmed
		$('#wc_veruspay_modal_button-cashout').click(function() {
			var vcoin = $(this).attr("data-coin");
			var vtype = $(this).attr("data-type");
			$('.wc_veruspay_cashout-modalback').fadeOut();
			$('.wc_veruspay_modal_coin').text('');
			$('.wc_veruspay_modal_type').text('');
			$('.wc_veruspay_modal_amount').text('');
			$('.wc_veruspay_modal_address').text('');
			$('.wc_veruspay_cashout_processing-modalback').fadeIn();
			$.ajax({
				type: "POST",
				data: { "veruspayajax":"1", "veruspaycommand":"cashout", "coin":vcoin, "type":vtype }
			}).done(function(data) {
				$('.wc_veruspay_cashout_processing-modalback').hide();
				$('.wc_veruspay_cashout_complete-modalcontent').html(data);
				$('.wc_veruspay_cashout_complete-modalback').fadeIn();
			});
			refreshBalance();
		});
		// - //
		// Cashout Confirm Close Button
		$('#wc_veruspay_modal_complete_button-close').click(function() {
			$('.wc_veruspay_cashout_complete-modalback').fadeOut();
			$('.wc_veruspay_cashout_complete-modalcontent').text('');
		});
		// - //

	});
});