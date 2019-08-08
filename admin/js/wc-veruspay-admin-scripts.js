var storecurrency = veruspay_admin_params.storecurrency;
jQuery( function( $ ) {
	$(document).ready(function() {
		$( '#verus_chain_tools_version' ).insertAfter('#woocommerce_veruspay_verus_gateway_access_code').css('display','inline-block');
		$( '.wc_veruspay_section_heading' ).next().find( 'tbody' ).addClass( 'wc_veruspay_section_body' );
		$( '.wc_veruspay_addresses-toggle' ).next().find( 'td' ).css( 'width','75%' );
		$( '.wc_veruspay_tab-container' ).next( 'table' ).remove();
		$( '.wc_veruspay_noinput' ).closest( 'tr' ).addClass( 'wc_veruspay_titleonly_row' );
		$( '.wc_veruspay_title-sub_normal' ).closest( 'tr' ).addClass ( 'wc_veruspay_title-sub_normal' );
		$( '.wc_veruspay_set_css' ).closest( 'div' ).addClass( 'wc_veruspay_wrapper' );
		$( '.wc_veruspay_daemon_add-button').closest('td').prev().hide();
		$( '.wc_veruspay_daemon_add-title' ).hide();
		$( '.wc_veruspay_daemon_add-status').hide();
		$( '.wc_veruspay_daemon_add-fn,.wc_veruspay_daemon_add-ip,.wc_veruspay_daemon_add-ssl,.wc_veruspay_daemon_add-code' ).closest('tbody').hide();
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
		if ( $( '#message' ).hasClass( 'updated' ) ) {
			var savedURL = location.href + '&veruspay_settings_saved';
			location.href = savedURL;
			location.reload();
		}
		if ( $('.wc_veruspay_togglewallet').hasClass( 'wallet_updated' ) ) {
			location.reload();
		}
		$( '.wc_veruspay_hostedsettings-toggle' ).closest('tbody').toggleClass('wc_veruspay_set_css');
		$( '.wc_veruspay_hostedsettings-toggle' ).toggleClass('wc_veruspay_set_css');
		$( '.wc_veruspay_toggledaemon' ).toggleClass( 'wc_veruspay_active_tab' );
		$( '.wc_veruspay_walletsettings-toggle' ).closest('tbody').toggleClass('wc_veruspay_set_css');
		$( '.wc_veruspay_walletsettings-toggle' ).toggleClass('wc_veruspay_set_css');
		$( '.wc_veruspay_addresses-toggle' ).closest('tbody').toggleClass('wc_veruspay_set_css');
		$( '.wc_veruspay_addresses-toggle' ).toggleClass('wc_veruspay_set_css');
		$( '.wc_veruspay_customization-toggle' ).closest('tr').toggleClass('wc_veruspay_set_css');
		$( '.wc_veruspay_options-toggle' ).closest('tr').toggleClass('wc_veruspay_set_css');

		$('.wc_veruspay_toggledaemon').click(function(e) {
			if ( ! ( $( this ).hasClass( 'wc_veruspay_active_tab' ) ) ) {
				$( this ).toggleClass( 'wc_veruspay_active_tab' );
				$( '.wc_veruspay_daemonsettings-toggle' ).closest('tbody').toggleClass('wc_veruspay_set_css');
				$( '.wc_veruspay_daemonsettings-toggle' ).toggleClass('wc_veruspay_set_css');
			}
		});
		$('.wc_veruspay_togglewallet').click(function(e) {
			if ( ! ( $( this ).hasClass( 'wc_veruspay_active_tab' ) ) ) {
				$( this ).toggleClass( 'wc_veruspay_active_tab' );
				$( '.wc_veruspay_walletsettings-toggle' ).closest('tbody').toggleClass('wc_veruspay_set_css');
				$( '.wc_veruspay_walletsettings-toggle' ).toggleClass('wc_veruspay_set_css');
			}
		});
		$( '.wc_veruspay_toggleaddr' ).click(function(e) {
			if ( ! ( $( this ).hasClass( 'wc_veruspay_active_tab' ) ) ) {
				$( this ).toggleClass( 'wc_veruspay_active_tab' );
				$( '.wc_veruspay_addresses-toggle' ).closest('tbody').toggleClass('wc_veruspay_set_css');
				$( '.wc_veruspay_addresses-toggle' ).toggleClass('wc_veruspay_set_css');
			}
		});
		$('.wc_veruspay_togglecust').click(function(e) {
			if ( ! ( $( this ).hasClass( 'wc_veruspay_active_tab' ) ) ) {
				$( this ).toggleClass( 'wc_veruspay_active_tab' );
				$( '.wc_veruspay_customization-toggle' ).closest('tr').toggleClass('wc_veruspay_set_css');
			}
		});
		$('.wc_veruspay_toggleoptions').click(function(e) {
			if ( ! ( $( this ).hasClass( 'wc_veruspay_active_tab' ) ) ) {
				$( this ).toggleClass( 'wc_veruspay_active_tab' );
				$( '.wc_veruspay_options-toggle' ).closest('tr').toggleClass('wc_veruspay_set_css');
			}
		});
		$('.wc_veruspay_togglehosted').click(function(e) {
			// NOT YET ENABLED
			if ( ! ( $( this ).hasClass( 'wc_veruspay_active_tab' ) ) ) {
				//$( '.wc_veruspay_hostedsettings-toggle' ).closest('tbody').toggleClass('wc_veruspay_set_css');
				//$( '.wc_veruspay_hostedsettings-toggle' ).toggleClass('wc_veruspay_set_css');
			}
		});

		// Refresh prices
		var lastPrice = function() {
			$( '.wc_veruspay_fiat_rate' ).each(function(){
				var bkcolor = 'transparent';
				var newval = '';
				var coin = $( this ).attr( 'data-coin' );
				var lastval = $( this ).text();
				$.ajax({
					type: 'post',
					url: 'https://veruspay.io/api/',
					data: {currency: storecurrency, ticker: coin},
					success: function(response){
						$( this ).hide();
						newval = response;
						if ( lastval > newval ) {
							var value = newval.toString();
							$( this ).html(value).css('background-color','red');
							$( this ).fadeIn(800).queue( function(next){
								$( this ).css( 'background-color', 'transparent' ); 
								next();
							});
						}
						if ( lastval < newval ) {
							var value = newval.toString();
							$( this ).html(value).css('background-color','green');
							$( this ).fadeIn(800).queue( function(next){
								$( this ).css( 'background-color', 'transparent' ); 
								next();
							});					
						}
						else {
							var value = newval.toString();
							$( this ).html(value).css('background-color','green');
							$( this ).fadeIn(800).queue( function(next){
								$( this ).css( 'background-color', 'transparent' ); 
								next();
							});				
						}						
					}
				});
			});
		}
				
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

		// Run process intervals
		setInterval( function() {
			lastPrice();
			var nowtime = $.now();
		}, (60000));

		setInterval( function() {
			refreshBalance();
			var nowtime = $.now();
		}, (15000));
		// - //

		$('.wc_veruspay_daemon_add-button').click(function() {
			$('.wc_veruspay_daemon_add-title:first').removeClass('wc_veruspay_daemon_add-title').show();
			$('.wc_veruspay_daemon_add-status:first').removeClass('wc_veruspay_daemon_add-status').show();
			$('.wc_veruspay_daemon_add-fn:first').removeClass('wc_veruspay_daemon_add-fn').closest('tbody').show();
			$('.wc_veruspay_daemon_add-ip:first').removeClass('wc_veruspay_daemon_add-ip').closest('tbody').show();
			$('.wc_veruspay_daemon_add-ssl:first').removeClass('wc_veruspay_daemon_add-ssl').closest('tbody').show();
			$('.wc_veruspay_daemon_add-code:first').removeClass('wc_veruspay_daemon_add-code').closest('tbody').show();
		});
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