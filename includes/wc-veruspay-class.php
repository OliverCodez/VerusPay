<?php
// No Direct Access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
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
        $this->id = 'veruspay_verus_gateway';
        $this->icon = apply_filters( 'woocommerce_veruspay_icon', $wc_veruspay_global['paths']['public']['img'] . 'wc-veruspay-icon-32x.png' );
        $this->has_fields = TRUE;
        $this->method_title = __( 'VerusPay', 'veruspay-verus-gateway' );
        $this->method_description = __( $wc_veruspay_global['text_help']['method_desc'], 'veruspay-verus-gateway' );
        $this->supports = array(
            'products'
        );
        if ( is_admin() ) {
            $this->init_form_fields();
            // Possible future use: $this->init_settings();
        }
        $this->enabled = $this->get_option( 'enabled' );
        $this->test_mode = 'yes' == $this->get_option( 'test_mode' );
        if ( $this->test_mode ) {
            $this->title = __( 'TEST MODE', 'veruspay-verus-gateway' );
        }
        else {
            $this->title = __( 'VerusPay', 'veruspay-verus-gateway' );
        }
        $this->verusQR = '0.1.0'; // For Invoice QR codes
        // TODO : Change to VRSC before release
        $this->coin = 'VRSCTEST';
        $this->store_inv_msg = $this->get_option( 'qr_invoice_memo' );
        $this->store_img = $this->get_option( 'qr_invoice_image' );
        $this->description  = $this->get_option( 'description' );
        $this->msg_before_sale = $this->get_option( 'msg_before_sale' );
        $this->msg_after_sale = $this->get_option( 'msg_after_sale' );
        $this->msg_cancel = $this->get_option( 'msg_cancel' );
        $this->email_order = $this->get_option( 'email_order' );
        $this->email_cancelled = $this->get_option( 'email_cancelled' );
        $this->email_completed = $this->get_option( 'email_completed' );
        // Clean and count store addresses for backup / manual use
        $wc_veruspay_chains_temp = $this->get_option('wc_veruspay_chains');
        if ( ! empty( $wc_veruspay_chains_temp ) ) {
            foreach ( $wc_veruspay_chains_temp as $key => $item ) {
                $wc_veruspay_store_data = $this->get_option( $key . '_storeaddresses' );
                $wc_veruspay_chains_temp[$key]['AD'] = preg_replace( '/\s+/', '', $wc_veruspay_store_data );
                if ( strlen( $wc_veruspay_store_data ) < 10 ) {
                    $wc_veruspay_chains_temp[$key]['AC'] = 0;
                }
                else if ( strlen( $wc_veruspay_store_data ) > 10 ) {
                    $wc_veruspay_chains_temp[$key]['AD'] = explode( ',', $wc_veruspay_chains_temp[$key]['AD'] );
                    $wc_veruspay_chains_temp[$key]['AC'] = count( $wc_veruspay_chains_temp[$key]['AD'] );
                }
                $wc_veruspay_chains_temp[$key]['UD'] = explode( ',', $this->get_option( $key . '_usedaddresses' ));
            }
        }
        $this->chains = $wc_veruspay_chains_temp;
        $this->decimals = $this->get_option( 'decimals' ); 
        $this->pricetime = $this->get_option( 'pricetime' );
        $this->orderholdtime = $this->get_option( 'orderholdtime' );
        $this->confirms = $this->get_option( 'confirms' );
        $this->qr_max_size = $this->get_option( 'qr_max_size' );
        $this->discount_fee = 'yes' == $this->get_option( 'discount_fee' );
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
            if ( isset( $_POST['veruspayajax'] ) && sanitize_text_field( $_POST['veruspayajax'] ) == "1" ) {
                // If command to Cashout
                if ( sanitize_text_field( $_POST['veruspaycommand'] ) == 'cashout' ) {
                    $vtype = sanitize_text_field( $_POST['type'] );
                    $_chain_up = strtoupper( sanitize_text_field( $_POST['coin'] ) );
                    $_chain_lo = strtolower( $_chain_up );
                    if ( $vtype == 'cashout_t' ) {
                        $wc_veruspay_cashout_results = wc_veruspay_go( $this->chains[$_chain_up]['DC'], $this->chains[$_chain_up]['IP'], $_chain_up, $vtype );
                        require_once( $wc_veruspay_global['paths']['admin_modal-0'] );
                    }
                    if ( $vtype == 'cashout_z' ) {
                        $wc_veruspay_cashout_results = json_decode( wc_veruspay_go( $this->chains[$_chain_up]['DC'], $this->chains[$_chain_up]['IP'], $_chain_up, $vtype ), TRUE );
                        require_once( $wc_veruspay_global['paths']['admin_modal-1'] );
                        foreach( $wc_veruspay_cashout_results as $key => $item ) {
                            require_once( $wc_veruspay_global['paths']['admin_modal-2'] );
                        }
                    }
                    die();
                }
                // If command to check balances
                if ( sanitize_text_field( $_POST['veruspaycommand'] ) == 'balance' ) {
                    $ctype = sanitize_text_field( $_POST['type'] );
                    $_chain_up = strtoupper( sanitize_text_field( $_POST['coin'] ) );
                    $_chain_lo = strtolower( $_chain_up );
                    $wc_veruspay_balance_refresh = json_decode( wc_veruspay_go( $this->chains[$_chain_up]['DC'], $this->chains[$_chain_up]['IP'], $_chain_up, 'bal' ), TRUE )[$ctype];
                    if ( strpos( $wc_veruspay_balance_refresh, 'Not Found' ) !== FALSE ) {
                        echo 'Err: ' . $wc_veruspay_global['text_help']['admin_0'];
                    }
                    else if ( number_format( $wc_veruspay_balance_refresh, 8) == NULL ) {
                        echo 'Err: ' . $wc_veruspay_global['text_help']['admin_1'];
                    }
                    else {
                        echo number_format( $wc_veruspay_balance_refresh, 8);
                    }
                    die();
                }

                // If mining on
                if ( sanitize_text_field( $_POST['veruspaycommand'] ) == 'generate' ) {
                    // TODO : AJAX Mining and Staking control and stat - Coming Soon
                    die();
                }
            }
            // Set admin modal
            require_once( $wc_veruspay_global['paths']['admin_modal-3'] );
        }
    }
    /**
     * Init Form Fields
     * Initialize form fields
     * @access public
     * @param global $wc_veruspay_global
     */
    public function init_form_fields() {
        global $wc_veruspay_global;
        require_once( $wc_veruspay_global['paths']['init_path'] );
    }
    /**
     * Payment Scripts
     * Enqueue JS and localize data and data paths
     * @access public
     * @param global $wc_veruspay_global
     */
    public function payment_scripts() {
        global $wc_veruspay_global;
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
        wp_register_script( 'wc_veruspay_scripts', $wc_veruspay_global['paths']['public']['js'] . 'wc-veruspay-scripts.js' );
        wp_localize_script( 'wc_veruspay_scripts', 'veruspay_params', array( 'pricetime' => $this->pricetime ) );
        wp_enqueue_style( 'veruspay_css', $wc_veruspay_global['paths']['public']['css'] . 'wc-veruspay.css' );
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
        $wc_veruspay_payment_method = WC()->session->get( 'chosen_payment_method' );

        // Check if coin has been selected, if not attempt to activate VRSC
        if ( ! empty( $_POST['wc_veruspay_coin'] ) ) {
            $_chain_up = strtoupper( sanitize_text_field( $_POST['wc_veruspay_coin'] ) );
            echo '<script>window.location = "#payment";</script>';
            WC()->session->set( 'wc_veruspay_coin', $_chain_up );
        }
        else if ( ! empty( WC()->session->get( 'wc_veruspay_coin' ) ) ) {
            $_chain_up = strtoupper( WC()->session->get( 'wc_veruspay_coin' ) );
        }
        else {
            // Try to default to Verus if no post data
            if ( $this->chains[$this->coin]['EN'] == 'yes' ) {
                $_chain_up = strtoupper( $this->coin );
            }
            else {
                // Check for another available coin if Verus is not enabled, set first available as default
                foreach ( $this->chains as $key => $item ) {
                    if ( $item['EN'] == 'yes' ) {
                        $_chain_up = strtoupper( $key );
                        break;
                    }
                }
            }
            if ( ! isset( $_chain_up ) || empty( $_chain_up ) ) {
                $this->update_option( 'enabled', 'no' );
                header("Refresh: 0");
            }
        }
        $_chain_lo = strtolower( $_chain_up );
        // Get the current rate of selected coin from the phpext script and api call
        $wc_veruspay_rate = wc_veruspay_price( $_chain_up, get_woocommerce_currency() );

        // Calculate the total cart in selected crypto 
        $wc_veruspay_price = number_format( ( $wc_veruspay_price / $wc_veruspay_rate ), $this->decimals );

        // Calculate order times and timeouts
        $wc_veruspay_time_start = strtotime( date( 'Y-m-d H:i:s', time() ) ); // Get time now, used in calculating countdown
        $wc_veruspay_time_end = strtotime( '+' . $this->pricetime . ' minutes', $wc_veruspay_time_start ); // Setup countdown target time using order hold time data
        $wc_veruspay_sec_remaining = $wc_veruspay_time_end - $wc_veruspay_time_start; // Get difference between expire time and now in seconds        
        $wc_veruspay_time_remaining = gmdate( 'i:s', $wc_veruspay_sec_remaining ); // Format time-remaining for view
        $wc_veruspay_pricetimesec = ( $this->pricetime * 60 );

        // Setup refresh to occur on store-owner-defined price timeout
        header( 'Refresh:' . $wc_veruspay_pricetimesec );
        
        // Hidden divs for price and address generating feedback on click
        echo '<div class="wc_veruspay_processing-address" id="wc_veruspay_generate_order">' . $wc_veruspay_global['text_help']['msg_modal_gen_addr'] . '</div>';
        echo '<div class="wc_veruspay_processing-address" id="wc_veruspay_updating_price">' . $wc_veruspay_global['text_help']['msg_modal_update_price'] . '</div>';

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
        if( is_checkout() && $wc_veruspay_payment_method == 'veruspay_verus_gateway' && $this->chains[$_chain_up]['ST'] == 1 && $this->chains[$_chain_up]['ZC'] == 1 && $this->chains[$_chain_up]['SP'] == 'no' ) {
            $wc_veruspay_sapling_option = '<div class="wc_veruspay_sapling-option"><div class="wc_veruspay_sapling-checkbox wc_veruspay_sapling_tooltip"><label><input id="veruspay_sapling" type="checkbox" class="checkbox" name="wc_veruspay_sapling" value="yes" checked>' . $wc_veruspay_global['text_help']['msg_sapling_label'] . '</label><span class="wc_veruspay_sapling_tooltip-text">' . $wc_veruspay_global['text_help']['msg_sapling_tooltip'] . '</span></div></div>';
        }
        else if ( is_checkout() && $wc_veruspay_payment_method == 'veruspay_verus_gateway' && $this->chains[$_chain_up]['ST'] == 1 && $this->chains[$_chain_up]['ZC'] == 1 && $this->chains[$_chain_up]['SP'] == 'yes' ) {
            echo '<input id="veruspay_enforce_sapling" type="hidden" name="wc_veruspay_sapling" value="yes">';
        }
        require_once( $wc_veruspay_global['paths']['chkt_path'] );
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