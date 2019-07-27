<?php
/**
 	* VerusPay Payment Gateway
 	*
 	* Main class provides a payment gateway for accepting cryptocurrencies - cryptos supported: VRSC, ARRR
 	* We load it later to ensure WC is loaded first since we're extending it.
 	*
 	* @class 		WC_Gateway_VerusPay
 	* @extends		WC_Payment_Gateway
 	* @since		0.1.0
 	* @package		WooCommerce/Classes/Payment
 	* @author 		Oliver Westbrook
 	* @param global $wc_veruspay_global
 	*/
class WC_Gateway_VerusPay extends WC_Payment_Gateway {
    /**
     * Gateway constructor
     * 
     * @access public
     * @param global $wc_veruspay_global
     */
    public function __construct() {
        global $wc_veruspay_global;
        // Begin plugin definitions and define primary method title and description
        $this->id                 = 'veruspay_verus_gateway';
        $this->icon               = apply_filters( 'woocommerce_veruspay_icon', plugins_url( '/public/img/wc-veruspay-icon-32x.png', __FILE__ ) );
        $this->has_fields         = true;
        $this->method_title       = __( 'VerusPay', 'veruspay-verus-gateway' );
        $this->method_description = __( $wc_veruspay_global['text_help']['method_desc'], 'veruspay-verus-gateway' );
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
     * @param global $wc_veruspay_global
     */
    public function init_form_fields() {
        global $wc_veruspay_global;
        require_once( $wc_veruspay_global['init_path'] );
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
     * @param global $wc_veruspay_global
     * @param WC_Payment_Gateway $wc_veruspay_price
     */
    public function payment_fields() {
        global $wc_veruspay_global;
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
        echo '<div class="wc_veruspay_processing-address" id="wc_veruspay_generate_order">'.$wc_veruspay_global['text_help']['msg_modal_gen_addr'].'</div>';
        echo '<div class="wc_veruspay_processing-address" id="wc_veruspay_updating_price">'.$wc_veruspay_global['text_help']['msg_modal_update_price'].'</div>';

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
                                <label><input id="veruspay_sapling" type="checkbox" class="checkbox" name="wc_veruspay_sapling" value="yes" checked>'.$wc_veruspay_global['text_help']['msg_sapling_label'].'</label>
                                <span class="wc_veruspay_sapling_tooltip-text">'.$wc_veruspay_global['text_help']['msg_sapling_tooltip'].'</span>
                                </div></div>';
        }
        else if( is_checkout() && $wc_veruspay_payment_method == 'veruspay_verus_gateway' && $this->wallets[$wc_veruspay_coin]['stat'] === 1 && $this->wallets[$wc_veruspay_coin]['private'] == 1 && $this->wallets[$wc_veruspay_coin]['sapling'] == 'yes' ) {
            echo '<input id="veruspay_enforce_sapling" type="hidden" name="wc_veruspay_sapling" value="yes">';
        }
        
        // Include checkout
        require_once( $wc_veruspay_global['chkt_path'] );
        
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
     * @param global $wc_veruspay_global
     * @return array
     */
    public function process_payment( $order_id ) {
        global $wc_veruspay_global;
        $order = wc_get_order( $order_id );
        // On process set the order to on-hold and reduce stock
        $order->update_status( 'on-hold', __( $wc_veruspay_global['text_help']['awaiting_payment'], 'veruspay-verus-gateway' ) );
        $order->reduce_order_stock();
        WC()->cart->empty_cart();
        return array(
            'result' 	=> 'success',
            'redirect'	=> $this->get_return_url( $order )
        );
    }
}