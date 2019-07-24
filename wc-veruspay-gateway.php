<?php
/**
 * Plugin Name: VerusPay Verus Gateway
 * Plugin URI: https://wordpress.org/plugins/veruspay-verus-gateway/
 * Description: Accept Verus Coin (VRSC), Pirate (ARRR), and Komodo (KMD) cryptocurrencies in your online WooCommerce store for physical or digital products.
 * Version: 0.4.0-alpha
 * Author: Oliver Westbrook
 * Author URI: https://profiles.wordpress.org/veruspay/
 * Copyright: (c) 2019 John Oliver Westbrook (johnwestbrook@pm.me)
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: veruspay-verus-gateway
 * Domain Path: /i18n/languages/
 * Tested up to: 5.2.1
 * WC requires at least: 3.5.6
 * WC tested up to: 3.6.3
 */
// No Direct Access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// TODO: Remove this array (all backend functions are built to support storing and polling the config file for this data)
// Array for adding new coins - chaintools must also be updated to include transparent-addr related explorer data
$wc_veruspay_available_coins = array(
	'vrsc' => array(
		'name' => 'Verus',
		'private' => 1,												// 1 for True; 2 for False
		'transparent' => 1,										// 1 for True; 2 for False
		'mining' => 1,										// 1 for True; 2 for False
		'staking' => 1,										// 1 for True; 2 for False
	),
	'arrr' => array(
		'name' => 'Pirate',
		'private' => 1,												// 1 for True; 2 for False
		'transparent' => 0,										// 1 for True; 2 for False
		'mining' => 0,										// 1 for True; 2 for False
		'staking' => 0,										// 1 for True; 2 for False
	),
	'kmd' => array(
		'name' => 'Komodo',
		'private' => 0,												// 1 for True; 2 for False
		'transparent' => 1,										// 1 for True; 2 for False
		'mining' => 0,										// 1 for True; 2 for False
		'staking' => 0,										// 1 for True; 2 for False
	),
);
// Include VerusPay ChainTools script for blockchain integration with Verus ChainTools
require_once ( plugin_dir_path( __FILE__ ) . 'includes/wc-veruspay-chaintools.php' );
// Get store language
$wc_veruspay_store_language = get_locale();
$wc_veruspay_language_file = ( plugin_dir_path( __FILE__ ) . 'languages/' . $wc_veruspay_store_language . '_helper_text.php' );
// If non-en_US, get file otherwise use en_US for language
if(file_exists($wc_veruspay_language_file)) {
	require_once ( $wc_veruspay_language_file );
}
else { require_once ( plugin_dir_path( __FILE__ ) . 'languages/en_US_helper_text.php' ); }

// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters('active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}
/**
 * Add the VerusPay gateway to WC Available Gateways
 * 
 * @since 0.1.0
 * @param array $gateways
 * @return array $gateways
 */
add_filter( 'woocommerce_payment_gateways', 'wc_veruspay_add_to_gateways' );
function wc_veruspay_add_to_gateways( $gateways ) {
	$gateways[] = 'WC_Gateway_VerusPay';
	return $gateways;
}
/**
 * Add plugin links
 * 
 * @since 0.1.0
 * @param array $links
 * @return array $links
 */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_veruspay_plugin_links' );
function wc_veruspay_plugin_links( $links ) {
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=veruspay_verus_gateway' ) . '">' . __( 'Configure', 'veruspay-verus-gateway' ) . '</a>'
	);
	return array_merge( $plugin_links, $links );
}
/**
 * Add plugin menu item for easier access to VerusPay settings
 * 
 * @since 0.1.1-a
 * 
 */
add_action('admin_menu', 'wc_veruspay_settings_menu');
function wc_veruspay_settings_menu(){
	add_menu_page( 'WooCommerce Settings', 'VerusPay', 'administrator', 'wc-settings&tab=checkout&section=veruspay_verus_gateway', 'wc_veruspay_init', plugins_url( '/public/img/wc-verus-icon-16x.png', __FILE__ ) );
}
add_action( 'plugins_loaded', 'wc_veruspay_init', 11 );
function wc_veruspay_init() {
	global $wc_veruspay_text_helper;
	/**
 	* VerusPay Payment Gateway
 	*
 	* Main class provides a payment gateway for accepting cryptocurrencies - cryptos supported: VRSC, ARRR
 	* We load it later to ensure WC is loaded first since we're extending it.
 	*
 	* @class 		WC_Gateway_VerusPay
 	* @extends		WC_Payment_Gateway
 	* @since			0.1.0
 	* @package		WooCommerce/Classes/Payment
 	* @author 		Oliver Westbrook
 	* @param global $wc_veruspay_text_helper
 	*/
	class WC_Gateway_VerusPay extends WC_Payment_Gateway {
		/**
		 * Gateway constructor
		 * 
		 * @access public
		 * @param global $wc_veruspay_text_helper
		 */
		public function __construct() {
			global $wc_veruspay_text_helper;
			// Begin plugin definitions and define primary method title and description
			$this->id                 = 'veruspay_verus_gateway';
			$this->icon               = apply_filters( 'woocommerce_veruspay_icon', plugins_url( '/public/img/wc-veruspay-icon-32x.png', __FILE__ ) );
			$this->has_fields         = true;
			$this->method_title       = __( 'VerusPay', 'veruspay-verus-gateway' );
			$this->method_description = __( $wc_veruspay_text_helper['method_desc'], 'veruspay-verus-gateway' );
			// Define supported types (products allows all woocommerce product types)
			$this->supports = array(
				'products'
			);
			
			// Initialize form fields and settings
			if ( is_admin() ) {
				$this->init_form_fields();
			}
			//$this->init_settings();

			// Check if Test Mode is enabled - change title for admin to be aware during testing
			$this->enabled = $this->get_option( 'enabled' );
			$this->access_code = $this->get_option( 'access_code' );
			$this->test_mode = 'yes' === $this->get_option( 'test_mode' );
			if ( $this->test_mode ) {
				$this->title = __( 'TEST MODE', 'veruspay-verus-gateway' );
			}
			else {
				$this->title = __( 'VerusPay', 'veruspay-verus-gateway' );
			}
			// Define user set variables
			$this->verusQR = '0.1.0'; // For Invoice QR codes
			$this->coin = 'VRSC'; // Coin symbol for Invoice
			$this->store_inv_msg = $this->get_option( 'qr_invoice_memo' );  // Store Invoice message or product name (for single product checkout stores)
			$this->store_img = $this->get_option( 'qr_invoice_image' );   // Store Logo or product logo 
			// Define various store owner-defined messages and content
			$this->description  = $this->get_option( 'description' );
			$this->msg_before_sale = $this->get_option( 'msg_before_sale' );
			$this->msg_after_sale = $this->get_option( 'msg_after_sale' );
			$this->msg_cancel = $this->get_option( 'msg_cancel' );
			$this->email_order = $this->get_option( 'email_order' );
			$this->email_cancelled = $this->get_option( 'email_cancelled' );
			$this->email_completed = $this->get_option( 'email_completed' );
			// Define wallet options
			// Clean and count store addresses for backup / manual use
			$wc_veruspay_wallets_temp = $this->get_option('wc_veruspay_wallets');
			foreach ( $wc_veruspay_wallets_temp as $key => $item ) {
				$wc_veruspay_store_data = $this->get_option( $key . '_storeaddresses' );
				$wc_veruspay_wallets_temp[$key]['addresses'] = preg_replace( '/\s+/', '', $wc_veruspay_store_data );
				if ( strlen( $wc_veruspay_store_data ) < 10 ) {
					$wc_veruspay_wallets_temp[$key]['addrcount'] = 0;
				}
				else if ( strlen( $wc_veruspay_store_data ) > 10 ) {
					$wc_veruspay_wallets_temp[$key]['addresses'] = explode( ',', $wc_veruspay_wallets_temp[$key]['addresses'] );
					$wc_veruspay_wallets_temp[$key]['addrcount'] = count( $wc_veruspay_wallets_temp[$key]['addresses'] );
				}
				$wc_veruspay_wallets_temp[$key]['usedaddresses'] = explode( ',', $this->get_option( $key . '_usedaddresses' ));
			}
			$this->wallets = $wc_veruspay_wallets_temp;

			// Define various store options 
			$this->decimals = $this->get_option( 'decimals' ); 
			$this->pricetime = $this->get_option( 'pricetime' );
			$this->orderholdtime = $this->get_option( 'orderholdtime' );
			$this->confirms = $this->get_option( 'confirms' );
			$this->qr_max_size = $this->get_option( 'qr_max_size' );
			// Define fee/discount options
			$this->discount_fee = 'yes' === $this->get_option( 'discount_fee' );
			$this->verus_dis_title = $this->get_option( 'disc_title' );
			$this->verus_dis_type = $this->get_option( 'disc_type' );
			if ( is_numeric( $this->get_option( 'disc_amt' ) ) ) {
				$this->verus_dis_amt = ( $this->get_option( 'disc_amt' ) / 100 );
			}
						
			// Add actions for payment gateway, scripts, thank you page, and emails
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
			add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );

			if ( is_admin() ) {
			// Admin GET listeners - add admin only
			if ( isset( $_POST['veruspayajax'] ) && sanitize_text_field( $_POST['veruspayajax'] ) == "1" ) {
				// If command to Cashout
				if ( sanitize_text_field( $_POST['veruspaycommand'] ) == 'cashout' ) {
					// Do Cashout for coin and type
					$vtype = sanitize_text_field( $_POST['type'] );
					$vcoin = sanitize_text_field( $_POST['coin'] );
					$vcoinupper = strtoupper($vcoin);
					if ( $vtype == 'cashout_t' ) {
						$wc_veruspay_cashout_results = wc_veruspay_go( $this->access_code, $this->wallets[$vcoin], $vcoin, $vtype, null, null);
						echo '<h4>'.$vcoinupper.' Transparent Cashout Results</h4>
									<p><span style="font-weight:bold">Transaction ID: </span>'.$wc_veruspay_cashout_results.'</p>';
					}
					if ( $vtype == 'cashout_z' ) {
						$wc_veruspay_cashout_results = json_decode( wc_veruspay_go( $this->access_code, $this->wallets[$vcoin], $vcoin, $vtype, null, null), true );
						echo '<h4>'.$vcoinupper.' Private Cashout Results</h4>
									<p><span style="font-weight:bold">Your successful private cashouts are listed below with each opid:</span></p>';
						foreach($wc_veruspay_cashout_results as $key=>$item) {
							echo '<p><span style="font-weight:bold">Store Address: </span>'.$key.'</p>
										<p><span style="font-weight:bold">Cashout Address: </span>'.$item['cashout_address'].'</p>
										<p><span style="font-weight:bold">Amount: </span>'.$item['amount'].'</p>
										<p><span style="font-weight:bold">Opid: </span>'.$item['opid'].'</p>
										<p style="border: solid 1px #000;"></p>';
						}
					}
					die();
				}
				// If command to check balances
				if ( sanitize_text_field( $_POST['veruspaycommand'] ) == 'balance' ) {
					// Do Balance Refreshes
					$ctype = sanitize_text_field( $_POST['type'] );
					$ccoin = sanitize_text_field( $_POST['coin'] );
					$wc_veruspay_balance_refresh = wc_veruspay_go( $this->access_code, $this->wallets[$ccoin], $ccoin, $ctype, null, null);
					if ( strpos( $wc_veruspay_balance_refresh, 'Not Found' ) !== false ) {
						echo 'Err: Bad Connection to VerusChainTools or Not Installed!';
					}
					else if ( number_format( $wc_veruspay_balance_refresh, 8) == null ) {
						echo 'Err: Wallet Unreachable';
					}
					else {
						echo number_format( $wc_veruspay_balance_refresh, 8);
					}
					die();
				}
			}
				// Set admin modal
			echo '<div class="wc_veruspay_cashout-modalback" style="display:none">
						<div class="wc_veruspay_cashout-modalinner">
							<h4>Verify Before Proceeding</h4>
							<p>You are about to send <span class="wc_veruspay_modal_amount"></span> <span class="wc_veruspay_modal_coin"></span> to your <span class="wc_veruspay_modal_coin"></span> <span class="wc_veruspay_modal_type"></span> address.  Please verify the coin, amount, and type (Private or Transparent) are correct, and that you have access to/own the receive address, before you continue:</p>
							<p>Coin: <span class="wc_veruspay_modal_coin"></span></p>
							<p>Amount: <span class="wc_veruspay_modal_amount"></span></p>
							<p>Type: <span class="wc_veruspay_modal_type"></span></p>
							<p>Receive Address: <span class="wc_veruspay_modal_address"></span></p>
							<p></p>
							<p><span class="wc_veruspay_modal_button" id="wc_veruspay_modal_button-cancel">Cancel</span><span class="wc_veruspay_modal_button" id="wc_veruspay_modal_button-cashout">Cashout</span></p>
						</div>
						</div>
						<div class="wc_veruspay_cashout_processing-modalback" style="display:none;">
						<div class="wc_veruspay_cashout_processing-modalinner">
							<h4>Processing...</h4>
						</div>
						</div>
						<div class="wc_veruspay_cashout_complete-modalback" style="display:none;">
						<div class="wc_veruspay_cashout_complete-modalinner">
						<div class="wc_veruspay_cashout_complete-modalcontent"></div>
						<p><span class="wc_veruspay_modal_button" id="wc_veruspay_modal_complete_button-close">Close</span></p>
						</div>
						</div>';
				}
		}
		/**
		 * Initialize Gateway Settings Form Fields
		 * 
		 * @access public
		 * @param global $wc_veruspay_text_helper
		 */
		public function init_form_fields() {
			global $wc_veruspay_text_helper;
			global $wc_veruspay_available_coins;
			require_once( plugin_dir_path( __FILE__ ) . 'includes/wc-veruspay-initform.php' );
		}
		/**
		 * Enqueue JS and localize data and data paths
		 * 
		 * @access public
		 */
		public function payment_scripts() {
			// Only initialize javascript on checkout and cart
			if ( ! is_cart() && ! is_checkout() && ! isset( $_GET[ 'pay_for_order' ] ) ) {
				return;
			}
			// Do not enqueue if plugin is disabled
			if ( $this->enabled == 'no' ) {
				return;
			}
			// Setup fee or discount if it exists
			if ( $this->discount_fee ) {
				$wc_veruspay_discount = WC()->cart->subtotal * $this->verus_dis_amt;
				WC()->cart->add_fee( __( $this->verus_dis_title, 'veruspay-verus-gateway' ) , $this->verus_dis_type . $wc_veruspay_discount );
			}
			// Enqueue the scripts and styles for store/front end use
			add_action( 'wp_enqueue_scripts', 'so_enqueue_scripts' );
			wp_register_script( 'wc_veruspay_scripts', plugins_url( 'public/js/wc-veruspay-scripts.js', __FILE__ ) );
			wp_localize_script( 'wc_veruspay_scripts', 'veruspay_params', array(
				'pricetime'	 => $this->pricetime
			) );
			wp_enqueue_style( 'veruspay_css', plugins_url( 'public/css/wc-veruspay.css', __FILE__ ) );
			wp_enqueue_script( 'wc_veruspay_scripts' );
		}
		/**
		 * Customize checkout experience and allow for postbacks by js ajax during checkout
		 * 
		 * @access public
		 * @param global $wc_veruspay_text_helper
		 * @param WC_Payment_Gateway $wc_veruspay_price
		 */
		public function payment_fields() {
			global $wc_veruspay_text_helper;
			// Get cart total including tax and shipping where applicable 
			$wc_veruspay_price = WC_Payment_Gateway::get_order_total();
			
			// Get payment method and store currency and symbol
			$wc_veruspay_payment_method = WC()->session->get('chosen_payment_method');

			// Check if coin has been selected, if not attempt to activate VRSC
			if ( ! empty( $_POST['wc_veruspay_coin'] ) ) {
				$wc_veruspay_coin = sanitize_text_field( $_POST['wc_veruspay_coin'] );
				echo '<script>window.location = "#payment";</script>';
				WC()->session->set( 'wc_veruspay_coin', $wc_veruspay_coin );
			}
			else if ( ! empty( WC()->session->get( 'wc_veruspay_coin' ) ) ) {
				$wc_veruspay_coin = WC()->session->get( 'wc_veruspay_coin' );
			}
			else {
				// Check stat on each enabled coin
				foreach ( $this->wallets as $key => $item ) {
					if ( $item['enabled'] == 'yes' ) {
						if ( wc_veruspay_stat( $this->access_code, $this->wallets[$key], $key ) == '404' ) {
							$this->wallets[$key]['stat'] = 1;
						}
						else {
							$this->wallets[$key]['stat'] = 0;
						}
					}
				}
				$this->update_option( 'wc_veruspay_wallets', $this->wallets);
				// Try to default to Verus if no post data
				if ( $this->wallets['vrsc']['enabled'] == 'yes' && $this->wallets['vrsc']['stat'] === 1 ) {
					$wc_veruspay_coin = 'vrsc';
				}
				else {
					// Check for another available coin if Verus is not enabled, set first available as default
					foreach ( $this->wallets as $key => $item ) {
						if ( $item['enabled'] == 'yes' && $item['stat'] === 1 ) {
							$wc_veruspay_coin = $key;
							break;
						}
					}
				}
				if ( ! isset( $wc_veruspay_coin ) | empty( $wc_veruspay_coin ) ) {
					$this->update_option( 'enabled', 'no' );
					header("Refresh: 0");
				}
			}

			// Get the current rate of selected coin from the phpext script and api call
			$wc_veruspay_rate = wc_veruspay_price( $wc_veruspay_coin, get_woocommerce_currency() );

			// Calculate the total cart in selected crypto 
			$wc_veruspay_price = number_format( ( $wc_veruspay_price / $wc_veruspay_rate ), $this->decimals );

			// Calculate order times and timeouts
			$wc_veruspay_time_start = strtotime(date("Y-m-d H:i:s", time())); // Get time now, used in calculating countdown
			$wc_veruspay_time_end = strtotime('+'.$this->pricetime.' minutes', $wc_veruspay_time_start); // Setup countdown target time using order hold time data
			$wc_veruspay_sec_remaining = $wc_veruspay_time_end - $wc_veruspay_time_start; // Get difference between expire time and now in seconds        
			$wc_veruspay_time_remaining = gmdate("i:s", $wc_veruspay_sec_remaining); // Format time-remaining for view
			$wc_veruspay_pricetimesec = ($this->pricetime * 60);

			// Setup refresh to occur on store-owner-defined price timeout
			header("Refresh:".$wc_veruspay_pricetimesec);
			
			// Hidden divs for price and address generating feedback on click
			echo '<div class="wc_veruspay_processing-address" id="wc_veruspay_generate_order">'.$wc_veruspay_text_helper['msg_modal_gen_addr'].'</div>';
			echo '<div class="wc_veruspay_processing-address" id="wc_veruspay_updating_price">'.$wc_veruspay_text_helper['msg_modal_update_price'].'</div>';

			// Create hidden fields for price and time updated data
			echo '<input type="hidden" name="wc_veruspay_address" value="">
				<input type="hidden" name="wc_veruspay_price" value="' . $wc_veruspay_price . '">
				<input type="hidden" name="wc_veruspay_rate" value="' . $wc_veruspay_rate . '">
				<input type="hidden" name="wc_veruspay_pricestart" value="' . $wc_veruspay_time_start . '">
				<input type="hidden" name="wc_veruspay_pricetime" value="' . $this->pricetime . '">
				<input type="hidden" name="wc_veruspay_orderholdtime" value="' . $this->orderholdtime . '">
				<input type="hidden" name="wc_veruspay_confirms" value="' . $this->confirms . '">
				<input type="hidden" name="wc_veruspay_status" value="order">
				<input type="hidden" name="wc_veruspay_memo" value="' . $this->store_inv_msg . '">
				<input type="hidden" name="wc_veruspay_img" value="' . $this->store_img . '">'; 
			
			// Setup Sapling checkbox if Sapling is not enforced by store owner setting, unless enforced by coin (ARRR)
			$wc_veruspay_sapling_option = '';
			if( is_checkout() && $wc_veruspay_payment_method == 'veruspay_verus_gateway' && $this->wallets[$wc_veruspay_coin]['stat'] === 1 && $this->wallets[$wc_veruspay_coin]['private'] == 1 && $this->wallets[$wc_veruspay_coin]['sapling'] == 'no' ) {
				$wc_veruspay_sapling_option = '<div class="wc_veruspay_sapling-option">
									<div class="wc_veruspay_sapling-checkbox wc_veruspay_sapling_tooltip">
									<label><input id="veruspay_sapling" type="checkbox" class="checkbox" name="wc_veruspay_sapling" value="yes" checked>'.$wc_veruspay_text_helper['msg_sapling_label'].'</label>
									<span class="wc_veruspay_sapling_tooltip-text">'.$wc_veruspay_text_helper['msg_sapling_tooltip'].'</span>
									</div></div>';
			}
			else if( is_checkout() && $wc_veruspay_payment_method == 'veruspay_verus_gateway' && $this->wallets[$wc_veruspay_coin]['stat'] === 1 && $this->wallets[$wc_veruspay_coin]['private'] == 1 && $this->wallets[$wc_veruspay_coin]['sapling'] == 'yes' ) {
				echo '<input id="veruspay_enforce_sapling" type="hidden" name="wc_veruspay_sapling" value="yes">';
			}
			
			// Include checkout html 
			require_once( plugin_dir_path( __FILE__ ) . 'includes/wc-veruspay-checkout.php');
			
			// Include optional description set by store owner
			if ( $this->description ) {
				echo wpautop( wp_kses_post( $this->description ) );
			}
		}
		/**
		 * Output for the order received page.
		 * @param int $order_id
		 */
		public function thankyou_page( $order_id ) {
			$order = wc_get_order( $order_id );
		}
	
		/**
		 * Process the payment and return the result
		 * 
		 * @param int $order_id
		 * @param global $wc_veruspay_text_helper
		 * @return array
		 */
		public function process_payment( $order_id ) {
			global $wc_veruspay_text_helper;
			$order = wc_get_order( $order_id );
			// On process set the order to on-hold and reduce stock
			$order->update_status( 'on-hold', __( $wc_veruspay_text_helper['awaiting_payment'], 'veruspay-verus-gateway' ) );
			$order->reduce_order_stock();
			WC()->cart->empty_cart();
			return array(
				'result' 	=> 'success',
				'redirect'	=> $this->get_return_url( $order )
			);
		}
  }
}

// ========================================== //
// 		            FUNCTIONS 			            //
// ========================================== //	
/**
 * Add discount or fee for VerusPay payment use - if option enabled in store
 */
add_action( 'woocommerce_cart_calculate_fees', 'wc_veruspay_order_total_update', 1, 1 );
function wc_veruspay_order_total_update() {
	$wc_veruspay_class = new WC_Gateway_VerusPay();
	$wc_veruspay_payment_method = WC()->session->get('chosen_payment_method');
	if ( is_admin() && ! defined( 'DOING_AJAX' ) )
    return;
	
	if( is_checkout() && $wc_veruspay_payment_method == 'veruspay_verus_gateway' && $wc_veruspay_class->discount_fee ) {
        	$wc_veruspay_discount = WC()->cart->subtotal * $wc_veruspay_class->verus_dis_amt;
			WC()->cart->add_fee( __( $wc_veruspay_class->verus_dis_title, 'veruspay-verus-gateway' ) , $wc_veruspay_class->verus_dis_type . $wc_veruspay_discount );
    }
}
/**
 * Check if price Apis are working (disable VerusPay if not) and set VerusPay payment gateway button text
 * 
 * @param string[] $available_gateways
 * @param global $wc_veruspay_text_helper
 * @return string[] $available_gateways
 */
add_filter( 'woocommerce_available_payment_gateways', 'wc_veruspay_button_text' );
function wc_veruspay_button_text( $available_gateways ) {
	global $wc_veruspay_text_helper;
	$wc_veruspay_class = new WC_Gateway_VerusPay();
	
	if (! is_checkout() ) return $available_gateways;

	// Check if result from price API
	if ( wc_veruspay_price( 'VRSC', get_woocommerce_currency() ) === 0 ) {
		unset($available_gateways['veruspay_verus_gateway']);
	}
	// Otherwise set button text
	else if ( array_key_exists('veruspay_verus_gateway',$available_gateways) && ! $wc_veruspay_class->test_mode ) {
		$available_gateways['veruspay_verus_gateway']->order_button_text = __( $wc_veruspay_text_helper['payment_button'], 'woocommerce' );
	}
	// If test mode and not admint, disable gateway
	else if ( $wc_veruspay_class->test_mode && ! current_user_can('administrator') ) {
		unset($available_gateways['veruspay_verus_gateway']);
	}
	return $available_gateways;
}
/**
 * Save Order meta data 
 * 
 * @param string[] $order_id
 */
add_action( 'woocommerce_checkout_update_order_meta', 'wc_veruspay_save_custom_meta' );
function wc_veruspay_save_custom_meta( $order_id ) {
	$wc_veruspay_class = new WC_Gateway_VerusPay();
	$wc_veruspay_payment_method = WC()->session->get('chosen_payment_method');
	if ( $wc_veruspay_payment_method == 'veruspay_verus_gateway' ){
		if ( ! empty( $_POST['wc_veruspay_coin'] ) ) {
			update_post_meta( $order_id, '_wc_veruspay_coin', sanitize_text_field( $_POST['wc_veruspay_coin'] ) );
		}
		if ( ! empty( $_POST['wc_veruspay_address'] ) ) {
			update_post_meta( $order_id, '_wc_veruspay_address', sanitize_text_field( $_POST['wc_veruspay_address'] ) );
		}
		if ( ! empty( $_POST['wc_veruspay_price'] ) ) {
			update_post_meta( $order_id, '_wc_veruspay_price', sanitize_text_field( $_POST['wc_veruspay_price'] ) );
		}
		if ( ! empty( $_POST['wc_veruspay_rate'] ) ) {
			update_post_meta( $order_id, '_wc_veruspay_rate', sanitize_text_field( $_POST['wc_veruspay_rate'] ) );
		}
		if ( ! empty( $_POST['wc_veruspay_pricestart'] ) ) {
			update_post_meta( $order_id, '_wc_veruspay_pricestart', sanitize_text_field( $_POST['wc_veruspay_pricestart'] ) );
		}
		if ( ! empty( $_POST['wc_veruspay_pricetime'] ) ) {
			update_post_meta( $order_id, '_wc_veruspay_pricetime', sanitize_text_field( $_POST['wc_veruspay_pricetime'] ) );
		}
		if ( ! empty( $_POST['wc_veruspay_orderholdtime'] ) ) {
			update_post_meta( $order_id, '_wc_veruspay_orderholdtime', sanitize_text_field( $_POST['wc_veruspay_orderholdtime'] ) );
		}
		if ( ! empty( $_POST['wc_veruspay_confirms'] ) ) {
			update_post_meta( $order_id, '_wc_veruspay_confirms', sanitize_text_field( $_POST['wc_veruspay_confirms'] ) );
		}
		if ( ! empty( $_POST['wc_veruspay_sapling'] ) ) {
			update_post_meta( $order_id, '_wc_veruspay_sapling', sanitize_text_field( $_POST['wc_veruspay_sapling'] ) );
		}
		if ( ! empty( $_POST['wc_veruspay_status'] ) ) {
			update_post_meta( $order_id, '_wc_veruspay_status', sanitize_text_field( $_POST['wc_veruspay_status'] ) );
		}
		if ( ! empty( $_POST['wc_veruspay_order_block'] ) ) {
			update_post_meta( $order_id, '_wc_veruspay_order_block', sanitize_text_field( $_POST['wc_veruspay_order_block'] ) );
		}
		if ( ! empty( $_POST['wc_veruspay_img'] ) ) {
			update_post_meta( $order_id, '_wc_veruspay_img', sanitize_text_field( $_POST['wc_veruspay_img'] ) );
		}
		if ( ! empty( $_POST['wc_veruspay_memo'] ) ) {
			update_post_meta( $order_id, '_wc_veruspay_memo', sanitize_text_field( $_POST['wc_veruspay_memo'] ) );
		}
	}
}
/**
 * Display Crypto Address and amount owed or received on Admin order edit page  
 * 
 * @param string[] $order
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'wc_veruspay_display_crypto_address_in_admin', 10, 1 );
function wc_veruspay_display_crypto_address_in_admin( $order ) {
	global $wc_veruspay_phpextconfig;
	$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
	$wc_veruspay_payment_method = $order->get_payment_method();
	if ( $wc_veruspay_payment_method == 'veruspay_verus_gateway' ){
		if ( $order->has_status( 'completed' ) ) {
			$wc_veruspay_payment_status = 'Received';
			$wc_veruspay_price = get_post_meta( $order_id, '_wc_veruspay_paid', true );
		}
		else { 
			$wc_veruspay_payment_status = 'Pending'; 
			$wc_veruspay_price = get_post_meta( $order_id, '_wc_veruspay_price', true ); 
		}
		$wc_veruspay_address = get_post_meta( $order_id, '_wc_veruspay_address', true );
		$wc_veruspay_coin = get_post_meta( $order_id, '_wc_veruspay_coin', true );
		echo '<style>.wc-order-totals-items{height:3rem!important}.wc-order-totals-items:after{content: "' . $wc_veruspay_coin . ' ' . $wc_veruspay_payment_status . ': ' . $wc_veruspay_price . '"!important;position:relative;font-size:1rem;font-weight:bold;color:#007bff!important;top:0;float:right;width:200px;height:30px;}</style>';
		echo '<p><strong>'.__($wc_veruspay_coin . ' Price', 'woocommerce').':</strong>' . $wc_veruspay_price . ' with exchange rate of ' . get_post_meta( $order_id, '_wc_veruspay_rate', true ) . '</p>';
		if ( substr($wc_veruspay_address, 0, 2) !== 'zs' ) {
			echo '<p><strong>'.__($wc_veruspay_coin . ' Address', 'woocommerce').':</strong> <a target="_BLANK" href="' . $wc_veruspay_phpextconfig[$wc_veruspay_coin . '_address'] . $wc_veruspay_address . '">' . $wc_veruspay_address . '</a></p>';
		}
		else {
			echo '<p><strong>'.__($wc_veruspay_coin . ' Address', 'woocommerce').':</strong> '.$wc_veruspay_address.'</p';
		}
	}
}			
/**
 * Add 'Crypto' column header to 'Orders' page immediately after 'Total' column.
 * 
 * @param string[] $columns
 * @return string[] $wc_veruspay_new_columns
 */
add_filter( 'manage_edit-shop_order_columns', 'wc_veruspay_add_order_column_header', 20 );
function wc_veruspay_add_order_column_header( $columns ) {
    $wc_veruspay_new_columns = array();
    foreach ( $columns as $column_name => $column_info ) {
		$wc_veruspay_new_columns[ $column_name ] = $column_info;
		if ( 'order_status' === $column_name ) {
			$wc_veruspay_new_columns['verus_addr'] = __( 'Crypto Address', 'veruspay-verus-gateway' );
		}
        if ( 'order_total' === $column_name ) {
            $wc_veruspay_new_columns['verus_total'] = __( 'Crypto Total', 'veruspay-verus-gateway' );
        }
    }
    return $wc_veruspay_new_columns;
}
/** 
 * Add 'VRSC' column content to 'Orders' page immediately after 'Total' column.
 * 
 * @param string[] $column
 */
add_action( 'manage_shop_order_posts_custom_column', 'wc_veruspay_add_order_column_content' );
function wc_veruspay_add_order_column_content( $column ) {
		global $post;
		global $wc_veruspay_phpextconfig;
		$order = wc_get_order( $post->ID );
		if ( $order->get_payment_method() == 'veruspay_verus_gateway' ){
			if ( 'verus_addr' === $column ) {
				$order_id = $post->ID;
				$wc_veruspay_coin = get_post_meta( $order_id, '_wc_veruspay_coin', true );
				$wc_veruspay_address = get_post_meta( $order_id, '_wc_veruspay_address', true );
				if ( substr( $wc_veruspay_address, 0, 2 ) !== 'zs' ) {
					echo '<span style="color:#007bff !important;"><a target="_BLANK" href="' . $wc_veruspay_phpextconfig[$wc_veruspay_coin . '_address'] . $wc_veruspay_address . '">' . $wc_veruspay_address . '</a></span>'; 
				} 
				else {
					echo '<span style="color:#007bff !important;">' . $wc_veruspay_address . '</span>';
				}
			}
			if ( 'verus_total' === $column ) {
				$order_id = $post->ID;
				$wc_veruspay_coin = get_post_meta( $order_id, '_wc_veruspay_coin', true );
				if ( get_post_meta( $order_id, '_wc_veruspay_status', true ) == 'paid' ) {
					echo '<span style="color:#252525 !important;">' . get_post_meta( $order_id, '_wc_veruspay_paid', true ) . ' ' . $wc_veruspay_coin . '</span>';
				}
				else if ( $order->has_status( 'completed' ) ) {
					echo '<span style="color:#007bff !important;">' . get_post_meta( $order_id, '_wc_veruspay_paid', true ) . ' ' . $wc_veruspay_coin . '</span>';
				}
				else {
					echo '<span style="font-style:italic;color:#252525 !important;">' . get_post_meta( $order_id, '_wc_veruspay_price', true ) . ' ' . $wc_veruspay_coin . '</span>';
				}
			}
		}
}
/**update_post_meta( $order_id, '_wc_veruspay_paid', sanitize_text_field( $wc_veruspay_balance ) );
 * Adjust the style for the Crypto Address column
 */
add_action( 'admin_print_styles', 'wc_veruspay_address_column_style' );
function wc_veruspay_address_column_style() {

	$wc_veruspay_css = '.column-order_status{width:6ch!important;}
			.column-verus_addr{width:16ch!important;}
			.column-order_total{width:4ch!important;}
			.column-verus_total{width:8ch!important;text-align:right!important;}';
    wp_add_inline_style( 'woocommerce_admin_styles', $wc_veruspay_css );
}
/** 
 * Build order - Get unused crypto payment address in selected coin
 * 
 * @param string[] $order_id
 * @param global $wc_veruspay_text_helper
 */
add_action( 'woocommerce_order_status_on-hold', 'wc_veruspay_set_address' ); //Could also use a similar hook to check for status of payment
function wc_veruspay_set_address( $order_id ) {
	global $wc_veruspay_text_helper;
	$wc_veruspay_class = new WC_Gateway_VerusPay();
	// Only proceed if processing a VerusPay payment
	if ( ! empty( get_post_meta( $order_id, '_wc_veruspay_coin', true ) ) ) {
		// If store is in Native mode and reachable, get a fresh address
		$wc_veruspay_coin = get_post_meta( $order_id, '_wc_veruspay_coin', true );
		// Get this wallet status before attempting to get address
		if ( wc_veruspay_stat( $wc_veruspay_class->access_code, $wc_veruspay_class->wallets[$wc_veruspay_coin], $wc_veruspay_coin ) == '404' ) {
			$wc_veruspay_class->wallets[$wc_veruspay_coin]['stat'] = 1;
		}
		else {
			$wc_veruspay_class->wallets[$wc_veruspay_coin]['stat'] = 0;
		}
		$wc_veruspay_class->update_option( 'wc_veruspay_wallets', $wc_veruspay_class->wallets);
		if ( $wc_veruspay_class->wallets[$wc_veruspay_coin]['enabled'] == 'yes' ) {
			if ( $wc_veruspay_class->wallets[$wc_veruspay_coin]['stat'] === 1 ) {
				if ( get_post_meta( $order_id, '_wc_veruspay_sapling', true ) == 'yes' ) { // If Sapling is enabled, get a sapling address
					$wc_veruspay_address = wc_veruspay_go( $wc_veruspay_class->access_code, $wc_veruspay_class->wallets[$wc_veruspay_coin], $wc_veruspay_coin, 'getnewsapling', null, null );
					while ( wc_veruspay_go( $wc_veruspay_class->access_code, $wc_veruspay_class->wallets[$wc_veruspay_coin], $wc_veruspay_coin, 'getbalance', $wc_veruspay_address, null) > 0 ) {
						$wc_veruspay_address = wc_veruspay_go( $wc_veruspay_class->access_code, $wc_veruspay_class->wallets[$wc_veruspay_coin], $wc_veruspay_coin, 'getnewsapling', null, null );
					} //FUTURE-if ( check address format - if bad return default address; ) { update the meta } else { update the meta with gotten new address }
					update_post_meta( $order_id, '_wc_veruspay_address', sanitize_text_field( $wc_veruspay_address ) );
					update_post_meta( $order_id, '_wc_veruspay_mode', sanitize_text_field( 'live' ) ); // May not rely on this in the future, may change to live wallet uptime at checkout
					$wc_veruspay_order_time = strtotime(date("Y-m-d H:i:s", time()));
					update_post_meta( $order_id, '_wc_veruspay_ordertime', sanitize_text_field( $wc_veruspay_order_time ) );
				}
				else { // If Not Sapling, get Transparent
					$wc_veruspay_address = wc_veruspay_go( $wc_veruspay_class->access_code, $wc_veruspay_class->wallets[$wc_veruspay_coin], $wc_veruspay_coin, 'getnewaddress', null, null );
					while ( wc_veruspay_go( $wc_veruspay_class->access_code, $wc_veruspay_class->wallets[$wc_veruspay_coin], $wc_veruspay_coin, 'getbalance', $wc_veruspay_address, null ) > 0 ) {
						$wc_veruspay_address = wc_veruspay_go( $wc_veruspay_class->access_code, $wc_veruspay_class->wallets[$wc_veruspay_coin], $wc_veruspay_coin, 'getnewaddress', null, null );
					}
						//FUTURE-if ( check address format - if bad return default address; ) { update the meta } else { update the meta with gotten new address }
					update_post_meta( $order_id, '_wc_veruspay_address', sanitize_text_field( $wc_veruspay_address ) );
					update_post_meta( $order_id, '_wc_veruspay_mode', sanitize_text_field( 'live' ) );
					$wc_veruspay_order_time = strtotime(date("Y-m-d H:i:s", time()));
					update_post_meta( $order_id, '_wc_veruspay_ordertime', sanitize_text_field( $wc_veruspay_order_time ) );
				}
			}
			// If wallet stat is false (manual mode)
			else if ( $wc_veruspay_class->wallets[$wc_veruspay_coin]['stat'] === 0 && $wc_veruspay_class->wallets[$wc_veruspay_coin]['transparent'] === 1 && $wc_veruspay_class->wallets[$wc_veruspay_coin]['addrcount'] > 2 ){
				$wc_veruspay_address = reset( $wc_veruspay_class->wallets[$wc_veruspay_coin]['addresses'] );
				while ( is_numeric( wc_veruspay_get( $wc_veruspay_coin, 'getbalance', $wc_veruspay_address ) ) && wc_veruspay_get( $wc_veruspay_coin, 'getbalance', $wc_veruspay_address ) > 0 ) {
					if ( ( $wc_veruspay_key = array_search( $wc_veruspay_address, $wc_veruspay_class->wallets[$wc_veruspay_coin]['addresses'] ) ) !== false ) {
						unset( $wc_veruspay_class->wallets[$wc_veruspay_coin]['addresses'][$wc_veruspay_key] );
					}
					$wc_veruspay_class->update_option( $wc_veruspay_coin . '_storeaddresses', implode( ','.PHP_EOL, $wc_veruspay_class->wallets[$wc_veruspay_coin]['addresses'] ) );
					array_push( $wc_veruspay_class->wallets[$wc_veruspay_coin]['usedaddresses'], $wc_veruspay_address );
					$wc_veruspay_class->update_option( $wc_veruspay_coin . '_usedaddresses', trim( implode( ','.PHP_EOL, $wc_veruspay_class->wallets[$wc_veruspay_coin]['usedaddresses'] ),"," ) );
					
					$wc_veruspay_address = reset( $wc_veruspay_class->wallets[$wc_veruspay_coin]['addresses'] );
					if( strlen( $wc_veruspay_address ) < 10 ) {
						// IMPROVE THIS - Error handling
						die($wc_veruspay_text_helper['severe_error']); // Need a more elegant error
					}
				}
				if ( ( $wc_veruspay_key = array_search( $wc_veruspay_address, $wc_veruspay_class->wallets[$wc_veruspay_coin]['addresses'] ) ) !== false ) {
					unset( $wc_veruspay_class->wallets[$wc_veruspay_coin]['addresses'][$wc_veruspay_key] );
				}
				$wc_veruspay_class->update_option( $wc_veruspay_coin . '_storeaddresses', implode( ','.PHP_EOL, $wc_veruspay_class->wallets[$wc_veruspay_coin]['addresses'] ) );
				array_push( $wc_veruspay_class->wallets[$wc_veruspay_coin]['usedaddresses'], $wc_veruspay_address );
				$wc_veruspay_class->update_option( $wc_veruspay_coin . '_usedaddresses', trim( implode( ','.PHP_EOL, $wc_veruspay_class->wallets[$wc_veruspay_coin]['usedaddresses'] ),"," ) );
				update_post_meta( $order_id, '_wc_veruspay_address', sanitize_text_field( $wc_veruspay_address ) );
				update_post_meta( $order_id, '_wc_veruspay_mode', sanitize_text_field( 'manual' ) );
				$wc_veruspay_order_time = strtotime(date("Y-m-d H:i:s", time()));
				update_post_meta( $order_id, '_wc_veruspay_ordertime', sanitize_text_field( $wc_veruspay_order_time ) );
			}
			else {
				die($wc_veruspay_text_helper['severe_error']);
			}
		}
		else { 
			die($wc_veruspay_text_helper['severe_error']);
		}
	}
}
/** 
 * Setup order hold / received page - Check for payment
 * 
 * @param string[] $order
 * @param global $wc_veruspay_text_helper
 */
add_action( 'woocommerce_order_details_before_order_table', 'wc_veruspay_order_received_body' );
function wc_veruspay_order_received_body( $order ) {
	global $wc_veruspay_text_helper;
	global $wc_veruspay_phpextconfig;
	$wc_veruspay_payment_method = $order->get_payment_method();
	// Access variables from gateway 
	$wc_veruspay_class = new WC_Gateway_VerusPay();
	// Get order data
	$order_id = $order->get_id();
	$wc_veruspay_coin = get_post_meta( $order_id, '_wc_veruspay_coin', true );
	if ( function_exists( 'is_order_received_page' ) && $wc_veruspay_payment_method == 'veruspay_verus_gateway' && is_order_received_page() ) {
		//
		// On order placed, on-hold while waiting for payment - Check for payment received on page (also in cron)
		if ( $order->has_status( 'on-hold' ) ) {
			// Hide order overview if crypto payment 
			echo '<style>ul.woocommerce-thankyou-order-details,p.woocommerce-thankyou-order-received{display:none!important;}</style>';
			$wc_veruspay_address = get_post_meta( $order_id, '_wc_veruspay_address', true ); // Get the crypto payment address setup for this order at time order was placed
			$wc_veruspay_price = get_post_meta( $order_id, '_wc_veruspay_price', true ); // Get the crypto price price active at time order was placed (within the timeout period)
			$wc_veruspay_hold_time = get_post_meta( $order_id, '_wc_veruspay_orderholdtime', true );  // Get order hold timeout value active at time order was placed
			$wc_veruspay_qr_inv_array = array( // Future Feature
				'verusQR' => $wc_veruspay_class->verusQR,
				'coinTicker' => $wc_veruspay_class->coin,
				'address' => $wc_veruspay_address,
				'amount' => floor(round(str_replace(',', '', $wc_veruspay_price)*100000000)),
				'memo' => get_post_meta( $order_id, '_wc_veruspay_memo', true ),
				'image' => get_post_meta( $order_id, '_wc_veruspay_img', true ),
			);
			if ( get_post_meta( $order_id, '_wc_veruspay_sapling', true ) != 'yes' ) {
				$wc_veruspay_qr_inv_code = wc_veruspay_qr( urlencode(json_encode($wc_veruspay_qr_inv_array,true)), $wc_veruspay_class->qr_max_size); // Get QR code to match Verus invoice in VerusQR JSON format
				$wc_veruspay_qr_toggle_show = ' ';
				$wc_veruspay_qr_toggle_width = ' ';
			}
			if ( get_post_meta( $order_id, '_wc_veruspay_sapling', true ) == 'yes' ) {
				$wc_veruspay_qr_toggle_show = 'wc_veruspay_qr_block_noinv';
				$wc_veruspay_qr_toggle_width = 'wc_veruspay_qr_width_noinv';
			}
			$wc_veruspay_qr_code = wc_veruspay_qr( $wc_veruspay_address, $wc_veruspay_class->qr_max_size); // Get QR code to match payment address, size set by store owner
			$wc_veruspay_order_mode = get_post_meta( $order_id, '_wc_veruspay_mode', true );
			$wc_veruspay_confirmations = get_post_meta( $order_id, '_wc_veruspay_confirms', true ); // Get current confirm requirement count
			$wc_veruspay_order_status = get_post_meta( $order_id, '_wc_veruspay_status', true );
			// Setup time and countdown data
			$wc_veruspay_order_time = get_post_meta( $order_id, '_wc_veruspay_ordertime', true ); //strtotime($order->order_date); // Get time of order start - start time to send payment
			$wc_veruspay_time_end = strtotime('+'.$wc_veruspay_hold_time.' minutes', $wc_veruspay_order_time); // Setup countdown target time using order hold time data
			$wc_veruspay_time_start = strtotime(date("Y-m-d H:i:s", time())); // Get time now, used in calculating countdown
			$wc_veruspay_sec_remaining = $wc_veruspay_time_end - $wc_veruspay_time_start; // Get difference between expire time and now in seconds        
			$wc_veruspay_time_remaining = gmdate("i:s", $wc_veruspay_sec_remaining); // Format time-remaining for view
			
			// Get balance of order address for live or manual
			if ( $wc_veruspay_order_mode == 'live' && $wc_veruspay_order_status == 'order' ) {
				$wc_veruspay_balance = wc_veruspay_go( $wc_veruspay_class->access_code, $wc_veruspay_class->wallets[$wc_veruspay_coin], $wc_veruspay_coin, 'lowestconfirm', $wc_veruspay_address, '0' );
				if ( $wc_veruspay_balance >= $wc_veruspay_price ) {
					$wc_veruspay_balance_in = true;
					update_post_meta( $order_id, '_wc_veruspay_balance_in', sanitize_text_field( 'true' ) );
					update_post_meta( $order_id, '_wc_veruspay_status', sanitize_text_field( 'paid' ) );
					update_post_meta( $order_id, '_wc_veruspay_paid', sanitize_text_field( $wc_veruspay_balance ) );
					$wc_veruspay_blocknow = wc_veruspay_go( $wc_veruspay_class->access_code, $wc_veruspay_class->wallets[$wc_veruspay_coin], $wc_veruspay_coin, 'getblockcount', null, null );
					update_post_meta( $order_id, '_wc_veruspay_order_block', sanitize_text_field( $wc_veruspay_blocknow ) );
					$wc_veruspay_order_note = __( $wc_veruspay_price. ' ' .$wc_veruspay_text_helper['msg_received'] );
					$order->add_order_note( $wc_veruspay_order_note );
					$order->save();
					header("Refresh:0");
				}
				else {
					$wc_veruspay_balance_in = false;
					update_post_meta( $order_id, '_wc_veruspay_balance_in', sanitize_text_field( 'false' ) );
				}
			}

			if ( $wc_veruspay_order_mode == 'manual' && $wc_veruspay_order_status == 'order' ) {
				$wc_veruspay_balance = wc_veruspay_get( $wc_veruspay_coin, 'getbalance', $wc_veruspay_address );
				// If non-number data returned by explorer (case of new address) set returned balance as 0
				if ( ! is_numeric($wc_veruspay_balance) ) {
					$wc_veruspay_balance = 0;
				}
				if ( $wc_veruspay_balance >= $wc_veruspay_price ) {
					$wc_veruspay_balance_in = true;
					update_post_meta( $order_id, '_wc_veruspay_balance_in', sanitize_text_field( 'true' ) );
					update_post_meta( $order_id, '_wc_veruspay_status', sanitize_text_field( 'paid' ) );
					update_post_meta( $order_id, '_wc_veruspay_paid', sanitize_text_field( $wc_veruspay_balance ) );
					$wc_veruspay_blocknow = wc_veruspay_get( $wc_veruspay_coin, 'getblockcount' );
					update_post_meta( $order_id, '_wc_veruspay_order_block', sanitize_text_field( $wc_veruspay_blocknow ) );
					$wc_veruspay_order_note = __( $wc_veruspay_price. ' ' .$wc_veruspay_text_helper['msg_received'] );
					$order->add_order_note( $wc_veruspay_order_note );
					$order->save();
					header("Refresh:0");
				}
				if ( $wc_veruspay_balance <= 0 ) {
					$wc_veruspay_balance_in = false;
					update_post_meta( $order_id, '_wc_veruspay_balance_in', sanitize_text_field( 'false' ) );
				}
			}

			// If balance matches payment due, check confirmations and either keep on-hold or complete
			if ( $wc_veruspay_order_status == 'paid' ) {

				if ( $wc_veruspay_order_mode == 'live' ) {
					$wc_veruspay_balance = wc_veruspay_go( $wc_veruspay_class->access_code, $wc_veruspay_class->wallets[$wc_veruspay_coin], $wc_veruspay_coin, 'lowestconfirm', $wc_veruspay_address, $wc_veruspay_confirmations );
					if ( $wc_veruspay_balance >= $wc_veruspay_price ) {
						update_post_meta( $order_id, '_wc_veruspay_status', sanitize_text_field( 'completed' ) );
						echo '<div style="position: fixed;height: 100%;width: 100%;top: 0;left: 0;background-color: rgba(255, 255, 255, 0.9);z-index: 3000;"></div>';
						$order->update_status( 'completed', __( $wc_veruspay_text_helper['order_processing'], 'woocommerce') );
						header("Refresh:0");
					}
					else {
						echo '<input type="hidden" name="wc_veruspay_orderholdtime" value="' . $wc_veruspay_hold_time . '">';
						$wc_veruspay_order_block = get_post_meta( $order_id, '_wc_veruspay_order_block', true );
						$wc_veruspay_block_progress = $wc_veruspay_confirmations - (($wc_veruspay_order_block + $wc_veruspay_confirmations + 1) - wc_veruspay_go( $wc_veruspay_class->access_code, $wc_veruspay_class->wallets[$wc_veruspay_coin], $wc_veruspay_coin, 'getblockcount', null, null));
						require_once( plugin_dir_path( __FILE__ ) . 'includes/wc-veruspay-confirming.php');
					}
				}

				if ( $wc_veruspay_order_mode == 'manual' ) {
					$wc_veruspay_confirms = wc_veruspay_get( $wc_veruspay_coin, 'lowestconfirm', $wc_veruspay_address );
					if ( $wc_veruspay_confirms >= $wc_veruspay_confirmations ) {
						$order->update_status( 'completed', __( $wc_veruspay_text_helper['order_processing'], 'woocommerce') );
						update_post_meta( $order_id, '_wc_veruspay_status', sanitize_text_field( 'completed' ) );
						echo '<div style="position: fixed;height: 100%;width: 100%;top: 0;left: 0;background-color: rgba(255, 255, 255, 0.9);z-index: 3000;"></div>';
						header("Refresh:0");
					}
					else {
						echo '<input type="hidden" name="wc_veruspay_orderholdtime" value="' . $wc_veruspay_hold_time . '">';
						$wc_veruspay_order_block = get_post_meta( $order_id, '_wc_veruspay_order_block', true );
						$wc_veruspay_block_progress = $wc_veruspay_confirmations - (($wc_veruspay_order_block + $wc_veruspay_confirmations + 1) - wc_veruspay_get( $wc_veruspay_coin, 'getblockcount'));
						require_once( plugin_dir_path( __FILE__ ) . 'includes/wc-veruspay-confirming.php');
					}
				}
					
			}

			// If balance is less than payment due within timelimit, cancel the order and set the reason variable
			if ( ! isset( $wc_veruspay_balance_in ) ) {
				$wc_veruspay_balance_in = get_post_meta( $order_id, '_wc_veruspay_balance_in', true );
			}
			if ( $wc_veruspay_balance_in === false && $wc_veruspay_sec_remaining <= 0 ) {
				foreach  ( $order->get_items() as $item_key => $item_values) {                             
					$wc_veruspay_stock = get_post_meta( $item_values['variation_id'], '_manage_stock', true );                                
				}
				echo '<div style="position: fixed;height: 100%;width: 100%;top: 0;left: 0;background-color: rgba(255, 255, 255, 0.9);z-index: 3000;"></div>';
				update_post_meta( $order_id, '_wc_veruspay_status', sanitize_text_field( 'cancelled' ) );
				$order->update_status( 'cancelled', __( $wc_veruspay_text_helper['msg_order_cancel_timeout'].$wc_veruspay_hold_time.' min', 'woocommerce') );
				header("Refresh:0");
			}
			if ( $wc_veruspay_balance_in === false && $wc_veruspay_sec_remaining > 0 ) {
				// Add custom set additional post complete sale message
				if ( $wc_veruspay_class->msg_before_sale ) {
					$wc_veruspay_process_custom_msg = wpautop( wptexturize( $wc_veruspay_class->msg_before_sale ) );
				}
				echo '<input type="hidden" name="wc_veruspay_orderholdtime" value="' . $wc_veruspay_hold_time . '">';
				require_once( plugin_dir_path( __FILE__ ) . 'includes/wc-veruspay-process.php');
			}

		}
		// If order is completed it is paid in full and has all confirmations per store owner settings
		else if ( $order->has_status( 'completed' ) ) {
			echo '<style>ul.woocommerce-thankyou-order-details,p.woocommerce-thankyou-order-received{display:none!important;}</style>';
			$wc_veruspay_class = new WC_Gateway_VerusPay();
			$order_id = $order->get_id();
			$wc_veruspay_price = get_post_meta( $order_id, '_wc_veruspay_paid', true );
			echo $wc_veruspay_text_helper['msg_thank_payment_of'] . $wc_veruspay_price . $wc_veruspay_text_helper['msg_received'];
			if ( $wc_veruspay_class->msg_after_sale ) {
				echo wpautop( wptexturize( $wc_veruspay_class->msg_after_sale ) );
			}
		}
		// If order is cancelled for non-full-payment, no payment, or not enough block confirmations
		else if ( $order->has_status( 'cancelled' ) ) {
			echo '<style>ul.woocommerce-thankyou-order-details,p.woocommerce-thankyou-order-received{display:none!important;}</style>';
			echo '<p class="wc_veruspay_cancel_msg">'.$wc_veruspay_text_helper['msg_your_order_num'].$order->get_order_number() . $wc_veruspay_text_helper['msg_has_cancelled_reason'] . '</p>';
			echo '<p class="wc_veruspay_custom_cancel_msg">' . $wc_veruspay_class->msg_cancel . '</p>';
		}
	}
}
/** 
 * Change Title for Order Status
 * 
 * @param string[] $title, $id
 * @param global $wc_veruspay_text_helper
 * @return string[] $title
 */
add_filter( 'the_title', 'wc_veruspay_title_order_received', 99, 2 );
function wc_veruspay_title_order_received( $title, $id ) {
	global $wc_veruspay_text_helper;
	if ( function_exists( 'is_order_received_page' ) && is_order_received_page() && get_the_ID() === $id ) {
		global $wp;
		$order_id  = apply_filters( 'woocommerce_thankyou_order_id', absint( $wp->query_vars['order-received'] ) );
		$order = wc_get_order( $order_id );
		if ( $order->get_payment_method() == 'veruspay_verus_gateway' && get_post_meta( $order_id, '_wc_veruspay_status', true ) == 'order' ) {
			$title = $wc_veruspay_text_helper['title_ordered'];
		}
		else if ( $order->get_payment_method() == 'veruspay_verus_gateway' && get_post_meta( $order_id, '_wc_veruspay_status', true ) == 'paid' ) {
			$title = $wc_veruspay_text_helper['title_pending'];
		}
		else if ( $order->get_payment_method() == 'veruspay_verus_gateway' && $order->has_status( 'completed' ) ) {
			$title = $wc_veruspay_text_helper['title_completed'];
		}
		else if ( $order->get_payment_method() == 'veruspay_verus_gateway' && $order->has_status( 'cancelled' ) ) {
			$title = $wc_veruspay_text_helper['title_cancelled'];
		}
	}
	return $title;
}
/** 
 * Add crypto total to order details table
 * 
 * @param string[] $total_rows, $order, $tax_display
 * @param global $wc_veruspay_text_helper
 * @return string[] $total_rows
 */
add_filter( 'woocommerce_get_order_item_totals', 'wc_veruspay_add_total', 30, 3 );
function wc_veruspay_add_total( $total_rows, $order, $tax_display ) {
	global $wc_veruspay_text_helper;
	if ( $order->get_payment_method() == 'veruspay_verus_gateway' ) {
		$order_id = $order->get_id();
		$wc_veruspay_price = get_post_meta( $order_id, '_wc_veruspay_price', true );
		$wc_veruspay_coin = get_post_meta( $order_id, '_wc_veruspay_coin', true );
		unset( $total_rows['payment_method'] );
		$total_rows['recurr_not'] = array(
			'label' => __( $wc_veruspay_text_helper['total_in'] . strtoupper( $wc_veruspay_coin ) . ' (@ '.get_woocommerce_currency_symbol() . get_post_meta( $order_id, '_wc_veruspay_rate', true ) . '/' . strtoupper( $wc_veruspay_coin ) . ') :', 'woocommerce' ),
			'value' => $wc_veruspay_price,
		);
	}
	return $total_rows;
}
/**
 * Send email based on order status change
 * @param $order_id, $checkout
 */
add_action("woocommerce_order_status_changed", "wc_veruspay_notify_order_status");
function wc_veruspay_notify_order_status($order_id, $checkout=null) {
   global $woocommerce;
   $order = new WC_Order( $order_id );
   $wc_veruspay_class = new WC_Gateway_VerusPay();
   if($order->status === 'on-hold' ) {
	$wc_veruspay_mailer = $woocommerce->mailer();
	$wc_veruspay_message_body = __( $wc_veruspay_class->email_order );
	$wc_veruspay_message = $wc_veruspay_mailer->wrap_message( sprintf( __( 'Order %s Received' ), $order->get_order_number() ), $wc_veruspay_message_body );
	$wc_veruspay_mailer->send( $order->billing_email, sprintf( __( 'Order %s Received' ), $order->get_order_number() ), $wc_veruspay_message );
 }
   if($order->status === 'completed' ) {
	   $wc_veruspay_mailer = $woocommerce->mailer();
	   $wc_veruspay_message_body = __( $wc_veruspay_class->email_completed );
	   $wc_veruspay_message = $wc_veruspay_mailer->wrap_message( sprintf( __( 'Order %s Successful' ), $order->get_order_number() ), $wc_veruspay_message_body );
	   $wc_veruspay_mailer->send( $order->billing_email, sprintf( __( 'Order %s Successful' ), $order->get_order_number() ), $wc_veruspay_message );
	}
	if($order->status === 'cancelled' ) {
		$wc_veruspay_mailer = $woocommerce->mailer();
		$wc_veruspay_message_body = __( $wc_veruspay_class->email_cancelled );
		$wc_veruspay_message = $wc_veruspay_mailer->wrap_message( sprintf( __( 'Order %s Cancelled' ), $order->get_order_number() ), $wc_veruspay_message_body );
		$wc_veruspay_mailer->send( $order->billing_email, sprintf( __( 'Order %s Cancelled' ), $order->get_order_number() ), $wc_veruspay_message );
	 }
}
/** 
 * Create New Cron Schedules
 * 
 * @param string[] $schedules
 * @return string[] $schedules
 */
add_filter('cron_schedules','wc_veruspay_cron_schedules');
function wc_veruspay_cron_schedules($schedules){
    if(!isset($schedules["1min"])){
        $schedules["1min"] = array(
            'interval' => 1*60,
            'display' => __('1min'));
	}
	if(!isset($schedules["2min"])){
        $schedules["2min"] = array(
            'interval' => 2*60,
            'display' => __('2min'));
	}
	if(!isset($schedules["3min"])){
        $schedules["3min"] = array(
            'interval' => 3*60,
            'display' => __('3min'));
	}
	if(!isset($schedules["4min"])){
        $schedules["4min"] = array(
            'interval' => 4*60,
            'display' => __('4min'));
	}
	if(!isset($schedules["5min"])){
        $schedules["5min"] = array(
            'interval' => 5*60,
            'display' => __('5min'));
	}
	if(!isset($schedules["10min"])){
        $schedules["10min"] = array(
            'interval' => 10*60,
            'display' => __('10min'));
	}
	if(!isset($schedules["15min"])){
        $schedules["15min"] = array(
            'interval' => 15*60,
            'display' => __('15min'));
    }
    return $schedules;
}
 
if ( ! wp_next_scheduled( 'woocommerce_cancel_unpaid_submitted' ) ) {
    wp_schedule_event( time(), '1min', 'woocommerce_cancel_unpaid_submitted' );
}
/** 
 * Get list of on-hold VerusPay orders
 * 
 * @return string[] $wc_veruspay_unpaid
 */
function wc_veruspay_get_unpaid_submitted() {        
	global $wpdb;

	$wc_veruspay_unpaid = $wpdb->get_col( $wpdb->prepare( "
			SELECT posts.ID
			FROM {$wpdb->posts} AS posts
			WHERE posts.post_status = 'wc-on-hold'
			AND posts.post_date < %s
	", date( 'Y-m-d H:i:s', strtotime('-5 minutes') ) ) );
	
	return $wc_veruspay_unpaid;
}
/** 
 * Cancel or Complete orders based on status criteria of payment received, timeliness, etc
 * @param global $wc_veruspay_text_helper
 */
add_action( 'woocommerce_cancel_unpaid_submitted', 'wc_veruspay_check_order_status' );
function wc_veruspay_check_order_status() {
	global $wc_veruspay_text_helper;
	$wc_veruspay_class = new WC_Gateway_VerusPay();
	$wc_veruspay_unpaid = wc_veruspay_get_unpaid_submitted();
	if ( $wc_veruspay_unpaid ) {
		foreach ( $wc_veruspay_unpaid as $wc_veruspay_unpaid_order ) {
			// Similar function from order page                    
			$order = wc_get_order( $wc_veruspay_unpaid_order );
			$wc_veruspay_payment_method = $order->get_payment_method();
			if ( $wc_veruspay_payment_method == "veruspay_verus_gateway" ) {
				$order_id = $order->get_id();
				$wc_veruspay_coin = get_post_meta( $order_id, '_wc_veruspay_coin', true );
				$wc_veruspay_address = get_post_meta( $order_id, '_wc_veruspay_address', true );
				$wc_veruspay_price = get_post_meta( $order_id, '_wc_veruspay_price', true );
				$wc_veruspay_hold_time = get_post_meta( $order_id, '_wc_veruspay_orderholdtime', true );
				$wc_veruspay_order_mode = get_post_meta( $order_id, '_wc_veruspay_mode', true );
				$wc_veruspay_confirmations = get_post_meta( $order_id, '_wc_veruspay_confirms', true );
				$wc_veruspay_order_status = get_post_meta( $order_id, '_wc_veruspay_status', true );
				$wc_veruspay_order_time = get_post_meta( $order_id, '_wc_veruspay_ordertime', true );
				$wc_veruspay_time_end = strtotime('+'.$wc_veruspay_hold_time.' minutes', $wc_veruspay_order_time);
				$wc_veruspay_time_start = strtotime(date("Y-m-d H:i:s", time()));
				$wc_veruspay_sec_remaining = $wc_veruspay_time_end - $wc_veruspay_time_start;
				if ( $wc_veruspay_order_mode == 'live' && $wc_veruspay_order_status == 'order' ) {
					$wc_veruspay_balance = wc_veruspay_go( $wc_veruspay_class->access_code, $wc_veruspay_class->wallets[$wc_veruspay_coin], $wc_veruspay_coin, 'lowestconfirm', $wc_veruspay_address, '0' );
					if ( $wc_veruspay_balance >= $wc_veruspay_price ) {
						$wc_veruspay_balance_in = true;
						update_post_meta( $order_id, '_wc_veruspay_status', sanitize_text_field( 'paid' ) );
						update_post_meta( $order_id, '_wc_veruspay_paid', sanitize_text_field( $wc_veruspay_balance ) );
						$wc_veruspay_blocknow = wc_veruspay_go( $wc_veruspay_class->access_code, $wc_veruspay_class->wallets[$wc_veruspay_coin], $wc_veruspay_coin, 'getblockcount', null, null );
						update_post_meta( $order_id, '_wc_veruspay_order_block', sanitize_text_field( $wc_veruspay_blocknow ) );
						$wc_veruspay_order_note = __( $wc_veruspay_price. ' ' .$wc_veruspay_text_helper['msg_received'] );
						$order->add_order_note( $wc_veruspay_order_note );
						$order->save();
					}
					else {
						$wc_veruspay_balance_in = false;
					}
				}
				else if ( $wc_veruspay_order_mode == 'manual' && $wc_veruspay_order_status == 'order' ) {
					$wc_veruspay_balance = wc_veruspay_get( $wc_veruspay_coin, 'getbalance', $wc_veruspay_address );
					if ( ! is_numeric($wc_veruspay_balance) ) {
						$wc_veruspay_balance = 0;
					}
					if ( $wc_veruspay_balance >= $wc_veruspay_price ) {
						$wc_veruspay_balance_in = true;
						update_post_meta( $order_id, '_wc_veruspay_status', sanitize_text_field( 'paid' ) );
						update_post_meta( $order_id, '_wc_veruspay_paid', sanitize_text_field( $wc_veruspay_balance ) );
						$wc_veruspay_blocknow = wc_veruspay_get( $wc_veruspay_coin, 'getblockcount' );
						update_post_meta( $order_id, '_wc_veruspay_order_block', sanitize_text_field( $wc_veruspay_blocknow ) );
						$wc_veruspay_order_note = __( $wc_veruspay_price. ' ' .$wc_veruspay_text_helper['msg_received'] );
						$order->add_order_note( $wc_veruspay_order_note );
						$order->save();
					}
					if ( $wc_veruspay_balance <= 0 ) {
						$wc_veruspay_balance_in = false;
					}
				}
				if ( $wc_veruspay_order_status == 'paid' ) {
					if ( $wc_veruspay_order_mode == 'live' ) {
						$wc_veruspay_balance = wc_veruspay_go( $wc_veruspay_class->access_code, $wc_veruspay_class->wallets[$wc_veruspay_coin], $wc_veruspay_coin, 'lowestconfirm', $wc_veruspay_address, $wc_veruspay_confirmations );
						if ( $wc_veruspay_balance >= $wc_veruspay_price ) {
							$order->update_status( 'completed', __( $wc_veruspay_text_helper['order_processing'], 'woocommerce') );
							update_post_meta( $order_id, '_wc_veruspay_status', sanitize_text_field( 'completed' ) );
						}
						else {
							return;
						}
					}
					if ( $wc_veruspay_order_mode == 'manual' ) {
						$wc_veruspay_confirms = wc_veruspay_get( $wc_veruspay_coin, 'lowestconfirm', $wc_veruspay_address );
						if ( $wc_veruspay_confirms >= $wc_veruspay_confirmations ) {
							$order->update_status( 'completed', __( $wc_veruspay_text_helper['order_processing'], 'woocommerce') );
							update_post_meta( $order_id, '_wc_veruspay_status', sanitize_text_field( 'completed' ) );
						}
						else {
							return;
						}
					}
						
				}
				if ( $wc_veruspay_order_status == 'order' && $wc_veruspay_sec_remaining <= 0 ) {
					foreach  ( $order->get_items() as $item_key => $item_values) {                             
						$wc_veruspay_stock = get_post_meta( $item_values['variation_id'], '_manage_stock', true );                                
					}
					$order->update_status( 'cancelled', __( $wc_veruspay_text_helper['msg_order_cancel_timeout'].$wc_veruspay_hold_time.' min', 'woocommerce') );
					update_post_meta( $order_id, '_wc_veruspay_status', sanitize_text_field( 'cancelled' ) );
				}
			}	
		}
	}        
}

// Operational functions
function array_splice_assoc( &$input, $offset, $length, $replacement ) {
	$replacement = ( array ) $replacement;
	$key_indices = array_flip(array_keys( $input ) );
	if ( isset( $input[$offset] ) && is_string( $offset ) ) {
		$offset = $key_indices[$offset];
	}
	if ( isset( $input[$length] ) && is_string( $length ) ) {
		$length = $key_indices[$length] - $offset;
	}
	$input = array_slice( $input, 0, $offset, TRUE )
					+ $replacement
					+ array_slice( $input, $offset + $length, NULL, TRUE );
}