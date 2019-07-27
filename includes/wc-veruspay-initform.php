<?php
// No Direct Access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Enqueue Admin JS and CSS
if ( is_admin() ) {
    wp_register_script( 'wc_veruspay_admin_scripts', plugins_url( 'admin/js/wc-veruspay-admin-scripts.js', dirname( __FILE__ ) ) );
    wp_localize_script( 'wc_veruspay_admin_scripts', 'veruspay_admin_params', array( 'storecurrency' => get_woocommerce_currency() ) );
    wp_enqueue_style( 'veruspay_admin_css', plugins_url( 'admin/css/wc-veruspay-admin.css', dirname( __FILE__ ) ) );
    wp_enqueue_script( 'wc_veruspay_admin_scripts' );
}
// Build array of form field data
$this->form_fields = apply_filters( 'wc_veruspay_form_fields', array(
// Used for feedback and settings within admin
    'test_msg' => array(
        'title'	=> '',
        'type' => 'title',
        'class' => 'wc_veruspay_set_css'
    ),
    'hidden_set_css' => array(
        'title'	=> '',
        'type' => 'title',
        'class' => 'wc_veruspay_set_css',
    ),
    // Enable/Disable the VerusPay gateway
    'enabled' => array(
        'title' => __( 'Enable/Disable', 'veruspay-verus-gateway' ),
        'type' => 'checkbox',
        'label' => __( 'Enable VerusPay', 'veruspay-verus-gateway' ),
        'default' => 'yes'
    ),
    'access_code' => array(
        'title' => __( 'VerusChainTools Access Code', 'veruspay-verus-gateway' ),
        'type' => 'text',
        'label' => __( 'VerusChainTools Access Code', 'veruspay-verus-gateway' ),
    ),
    // Daemon Server Title (interactive to show/hide Daemon section)
    'daemon_settings_show' => array(
        'title' => __( 'Daemon Management', 'veruspay-verus-gateway' ),
        'type' => 'title',
        'description' => '',
        'class' => 'wc_veruspay_admin_section wc_veruspay_toggledaemon wc_veruspay_pointer',
    ),
    // Deamon Path
    'primary_daemon_ip' => array(
        'title' => __( 'Primary Daemon IP/Path', 'veruspay-verus-gateway' ),
        'type' => 'text',
        'description' => __( 'Enter the IP address of your primary daemon server. If on this server, enter the folder (must be a folder located at your root web folder)', 'veruspay-verus-gateway' ),
        'default' => 'IP or local folder name (e.g. enter just verustools if at /var/www/html/verustools)',
        'desc_tip' => TRUE,
        'class' => 'wc_veruspay_setdaemonip-toggle wc_veruspay_daemonsettings-toggle',
    ),
    // SSL Setting
    'primary_daemon_ssl' => array(
        'title' => __( 'Enable SSL?', 'veruspay-verus-gateway' ),
        'type' => 'checkbox',
        'label' => __( 'Enable SSL connection', 'veruspay-verus-gateway' ),
        'description' => '',
        'default' => 'yes',
        'class' => 'wc_veruspay_daemonsettings-toggle',
    ),
    // Wallet Management Title (interactive to show or hide the Wallet section)
    'wallet_settings_show' => array(
        'title' => __( 'Wallet Management', 'veruspay-verus-gateway' ),
        'type' => 'title',
        'description' => '',
        'class' => 'wc_veruspay_admin_section wc_veruspay_togglewallet wc_veruspay_pointer',
    ),
    // Wallet Management Spliced in
    // Addresses Title
    'addr_show' => array(
        'title' => __( 'Store Addresses', 'veruspay-verus-gateway' ),
        'type' => 'title',
        'description' => '',
        'class' => 'wc_veruspay_admin_section wc_veruspay_toggleaddr wc_veruspay_pointer',
    ),
    // Address Settings Spliced in
    // Content title, interactive (show or hide content customization fields)
    'cust_show' => array(
        'title' => __( 'Message and Content Customizations', 'veruspay-verus-gateway' ),
        'type' => 'title',
        'description' => '',
        'class' => 'wc_veruspay_admin_section wc_veruspay_togglecust wc_veruspay_pointer',
    ),
    // Content Customizations
    'description' => array(
        'title' => __( 'Checkout Message', 'veruspay-verus-gateway' ),
        'type' => 'textarea',
        'description' => __( 'A message and any instructions which the customer will see at checkout.', 'veruspay-verus-gateway' ),
        'default' => __( 'After you place your order you will see a screen with a valid cryptocurrency store wallet address to send your cryptocurrency payment within the time allowed.', 'veruspay-verus-gateway' ),
        'desc_tip' => TRUE,
        'class' => 'wc_veruspay_customization-toggle',
    ),
    'msg_before_sale' => array(
        'title' => __( 'Payment Page Message', 'veruspay-verus-gateway' ),
        'type' => 'textarea',
        'description' => __( 'VerusPay-specific message that will be added to the payment page and email, just below the payment address.', 'veruspay-verus-gateway' ),
        'default' => 'Some additional message to your customer at payment page',
        'desc_tip' => TRUE,
        'class' => 'wc_veruspay_customization-toggle',
    ),
    'msg_after_sale' => array(
        'title' => __( 'Payment Complete Message', 'veruspay-verus-gateway' ),
        'type' => 'textarea',
        'description' => __( 'VerusPay-specific message that will be added to the payment completed page and email, the final Thank You page', 'veruspay-verus-gateway' ),
        'default' => 'Some additional Thank You message to your customer after payment completes!',
        'desc_tip' => TRUE,
        'class' => 'wc_veruspay_customization-toggle',
    ),
    'msg_cancel' => array(
        'title' => __( 'Order Timeout Cancel Message', 'veruspay-verus-gateway' ),
        'type' => 'textarea',
        'description'	=> __( 'Text to display to an online shopper who waits too long to send crypto during the purchase', 'veruspay-verus-gateway' ),
        'default' => 'It looks like your purchase timed-out waiting for payment in the correct amount.  Sorry for any inconvenience this may have caused and please use the order details below to review your order and place a new one.',
        'desc_tip' => TRUE,
        'class' => 'wc_veruspay_customization-toggle',
    ),
    'email_order' => array(
        'title' => __( 'Custom Email Message When Order is Placed', 'veruspay-verus-gateway' ),
        'type' => 'textarea',
        'description'	=> __( 'Text to send to the customer when they place an order.', 'veruspay-verus-gateway' ),
        'default' => 'Text to send to the customer upon order.',
        'desc_tip' => TRUE,
        'class' => 'wc_veruspay_customization-toggle',
      ),
    'email_cancelled' => array(
        'title' => __( 'Custom Email Message When Order is Cancelled', 'veruspay-verus-gateway' ),
        'type' => 'textarea',
        'description'	=> __( 'Text to send to the customer when an order cancels (usually due to non-payment within timeframe).', 'veruspay-verus-gateway' ),
        'default' => 'Text to send to the customer when an order cancels.',
        'desc_tip' => TRUE,
        'class' => 'wc_veruspay_customization-toggle',
    ),
    'email_completed' => array(
        'title' => __( 'Custom Email Message When Order is Completed', 'veruspay-verus-gateway' ),
        'type' => 'textarea',
        'description'	=> __( 'Text to send to the customer when their order is complete (all blocks confirmed).', 'veruspay-verus-gateway' ),
        'default' => 'Text to send to the customer after all blocks are confirmed and order is complete.',
        'desc_tip' => TRUE,
        'class' => 'wc_veruspay_customization-toggle',
    ),
    // Store Option title, interactive (show or hide options)
    'options_show' => array(
        'title' => __( 'Store Options', 'veruspay-verus-gateway' ),
        'type' => 'title',
        'description' => '',
        'class' => 'wc_veruspay_admin_section wc_veruspay_toggleoptions wc_veruspay_pointer',
    ),
    // Store options
    'decimals' => array(
        'title' => __( 'Crypto Decimals', 'veruspay-verus-gateway' ),
        'type' => 'select',
        'description'	=> __( 'Choose the max decimals to use for crypto prices (up to 8).', 'veruspay-verus-gateway' ),
        'default' => '4',
        'desc_tip' => TRUE,
        'options' => array(
            '1'	=> __( '1', 'veruspay-verus-gateway' ),
            '2'	=> __( '2', 'veruspay-verus-gateway' ),
            '3'	=> __( '3', 'veruspay-verus-gateway' ),
            '4'	=> __( '4', 'veruspay-verus-gateway' ),
            '5'	=> __( '5', 'veruspay-verus-gateway' ),
            '6'	=> __( '6', 'veruspay-verus-gateway' ),
            '7'	=> __( '7', 'veruspay-verus-gateway' ),
            '8'	=> __( '8', 'veruspay-verus-gateway' ),
        ),
        'class' => 'wc_veruspay_options-toggle wc-enhanced-select',
    ),
    'pricetime' => array(
        'title' => __( 'Price timeout', 'veruspay-verus-gateway' ),
        'type' => 'text',
        'description' => __( 'Set the time (in minutes) before realtime crypto calculated price expires at checkout.', 'veruspay-verus-gateway' ),
        'default'	=> '5',
        'desc_tip' => TRUE,
        'class' => 'wc_veruspay_options-toggle',
    ),
    'orderholdtime'	=> array(
        'title' => __( 'Order Wait Time', 'veruspay-verus-gateway' ),
        'type' => 'select',
        'description'	=> __( 'Set the time (in minutes) to wait for the customer to make payment before cancelling the order. Does not impact already placed/pending orders.', 'veruspay-verus-gateway' ),
        'default' => '20',
        'desc_tip' => TRUE,
        'options' => array(
            '20' => __( '20', 'veruspay-verus-gateway' ),
            '25' => __( '25', 'veruspay-verus-gateway' ),
            '30' => __( '30', 'veruspay-verus-gateway' ),
            '45' => __( '45', 'veruspay-verus-gateway' ),
            '60' => __( '60', 'veruspay-verus-gateway' ),
        ),
        'class' => 'wc_veruspay_options-toggle wc-enhanced-select',
    ),
    'confirms' => array(
        'title' => __( 'Confirmations Required', 'veruspay-verus-gateway' ),
        'type' => 'text',
        'description' => __( 'Set the number of block confirmations required before an order is considered complete.', 'veruspay-verus-gateway' ),
        'default'	=> '20',
        'desc_tip' => TRUE,
        'class' => 'wc_veruspay_options-toggle',
    ),
    'qr_max_size' => array(
        'title' => __( 'Default QR Max Size', 'veruspay-verus-gateway' ),
        'type' => 'text',
        'description'	=> __( 'Enter a number for the max size of QR images generated during customer checkout', 'veruspay-verus-gateway' ),
        'default' => '400',
        'desc_tip' => TRUE,
        'class' => 'wc_veruspay_options-toggle',
    ),
    'qr_invoice_memo' => array(
        'title' => __( 'Invoice QR Memo Text', 'veruspay-verus-gateway' ),
        'type' => 'text',
        'description'	=> __( 'Enter a message or store identification to display in the Invoice memo', 'veruspay-verus-gateway' ),
        'default' => 'Thank you for using our Verus-enabled store!',
        'desc_tip' => TRUE,
        'class' => 'wc_veruspay_options-toggle',
    ),
    'qr_invoice_image' => array(
        'title' => __( 'Invoice QR Image', 'veruspay-verus-gateway' ),
        'type' => 'text',
        'description'	=> __( 'Enter url to store logo image', 'veruspay-verus-gateway' ),
        'default' => 'https://veruscoin.io/img/VRSClogo.svg',
        'desc_tip' => TRUE,
        'class' => 'wc_veruspay_options-toggle',
      ),
    // Discount or Fee Options
    'discount_fee' => array(
        'title' => __( 'Set Discount/Fee?', 'veruspay-verus-gateway' ),
        'type' => 'checkbox',
        'label' => 'Set a discount or fee for using crypto as payment method?',
        'description' => __( 'Setup a discount or fee, in %, applied to purchases during checkout for using crypto payments.', 'veruspay-verus-gateway' ),
        'desc_tip' => TRUE,
        'class' => 'wc_veruspay_setdiscount wc_veruspay_options-toggle',
    ),
    'disc_title' => array(
        'title' => __( 'Title (visible in checkout)', 'veruspay-verus-gateway' ),
        'type' => 'text',
        'description'	=> __( 'This is the title, seen in checkout, of your discount or fee (should be short)', 'veruspay-verus-gateway' ),
        'default' => __( 'This is the title, seen in checkout, of your discount or fee (should be short)', 'veruspay-verus-gateway' ),
        'desc_tip' => TRUE,
        'class' => 'wc_veruspay_discount-toggle wc_veruspay_options-toggle',
    ),  
    'disc_type'	=> array(
        'title' => __( 'Type (Discount or Fee?)', 'veruspay-verus-gateway' ),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'description'	=> __( 'Choose whether to discount or charge an extra fee for using Verus', 'veruspay-verus-gateway' ),
        'default' => 'Discount',
        'desc_tip' => TRUE,
        'options' => array(
            '-' => __( 'Discount', 'veruspay-verus-gateway' ),
            '+'	=> __( 'Fee', 'veruspay-verus-gateway' ),
        ),
        'class' => 'wc_veruspay_discount-toggle wc_veruspay_options-toggle',
    ),
    'disc_amt' => array(
        'title' => __( 'Amount (%)', 'veruspay-verus-gateway' ),
        'type' => 'text',
        'description'	=> __( 'Amount to discount or charge as a fee for using Crypto (in a number representing %)', 'veruspay-verus-gateway' ),
        'default' => __( 'Amount to charge or discount as a % e.g. for 10% enter simple 10', 'veruspay-verus-gateway' ),
        'desc_tip' => TRUE,
        'class' => 'wc_veruspay_discount-toggle wc_veruspay_options-toggle',
    ),
    'test_mode' => array(
        'title' => __( 'Test VerusPay', 'veruspay-verus-gateway' ),
        'type' => 'checkbox',
        'label' => 'Enable Test Mode?',
        'description' => __( 'Test Mode shows VerusPay only for logged in Admins', 'veruspay-verus-gateway' ),
        'desc_tip' => TRUE,
        'default' => 'no',
        'class' => 'wc_veruspay_options-toggle',
    ),
));
$wc_veruspay_access_code = $this->get_option( 'access_code' );
$wc_veruspay_global['chains'] = json_decode( wc_veruspay_go( $wc_veruspay_accesscode, $this->get_option( 'primary_daemon_ip' ), '_conf_', 'listchains' ), TRUE );
// Setup available daemon and wallets array
$wc_veruspay_wallets = array();
// TODO: Change entire structure, allow for multiple daemons
// TODO: Wallets section is dynamic based on config read via VCT API
// Splice wallet settings and address settings using foreach
foreach( $wc_veruspay_global['chains'] as $key => $item ) {
    $key = strtolower( $key );
    $wc_veruspay_set_t = 0;
    $wc_veruspay_set_z = 0;
    $wc_veruspay_set_s = 0;
    $wc_veruspay_set_m = 0;
    $wc_veruspay_set_e = array();
    // Add to Wallet Settings array
    $wc_veruspay_add_wallet_data = array();
    $wc_veruspay_add_wallet_data[$key.'_wallet_title'] = array(
        'title' => __( '<img style="margin: 0 10px 0 0;" src="' . plugins_url( 'public/img/wc-'.strtolower($item['fn']).'-icon-16x.png', dirname( __FILE__ ) ) .'" />' . $item['fn'] . ' Wallet - Fiat Price: ' . get_woocommerce_currency_symbol() . '<span class="wc_veruspay_fiat_rate" data-coin="'.$key.'">' . wc_veruspay_price( $key,  get_woocommerce_currency() ) . '</span><span class="wc_veruspay_title-sub-small">Updates every 1 min</span>', 'veruspay-verus-gateway' ),
        'type' => 'title',
        'description' => '',
        'class' => 'wc_veruspay_title-walletssub wc_veruspay_walletsettings-toggle',
    );
    // Daemon Section
    $wc_veruspay_add_wallet_data[$key.'_wallet_daemon_settings'] = array(
        'title' => __( $item['fn'] . ' Daemon Settings', 'veruspay-verus-gateway' ),
        'type' => 'title',
        'description' => '',
        'class' => 'wc_veruspay_title-sub wc_veruspay_title-sub-toggle-heading wc_veruspay_walletsettings-toggle',
    );
    // Wallet enable
    $wc_veruspay_add_wallet_data[$key.'_enable'] = array(
        'title' => __( 'Enable '.$item['fn'].' Payments', 'veruspay-verus-gateway' ),
        'type' => 'checkbox',
        'label' => 'Enable '.$item['fn'].' Coin Payments?',
        'description' => '',
        'default' => 'yes',
        'class' => 'wc_veruspay_setwalletip wc_veruspay_walletsettings-toggle',
    );
    // Deamon Path
    $wc_veruspay_add_wallet_data[$key.'_ip'] = array(
        'title' => __( $item['fn'].' Wallet IP/Path', 'veruspay-verus-gateway' ),
        'type' => 'text',
        'description' => __( 'Enter the IP address of your '.$item['fn'].' Wallet server (or leave localhost if on this server)', 'veruspay-verus-gateway' ),
        'default' => 'localhost',
        'desc_tip' => TRUE,
        'class' => 'wc_veruspay_setwalletip-toggle wc_veruspay_walletsettings-toggle',
    );
    // SSL Setting
    $wc_veruspay_add_wallet_data[$key.'_ssl']	= array(
        'title' => __( 'Enable SSL?', 'veruspay-verus-gateway' ),
        'type' => 'checkbox',
        'label' => __( 'Enable SSL connection (remote only - recommended)', 'veruspay-verus-gateway' ),
        'description' => '',
        'default' => 'yes',
        'class' => 'wc_veruspay_walletsettings-toggle',
    );
    // Sapling Enforce (if applicable)
    if ( $item['tx'] == 0 ) { // If both transparent and sapling/private
        $wc_veruspay_set_t = 1;
        $wc_veruspay_set_z = 1;
        $wc_veruspay_add_wallet_data[$key.'_sapling']	= array(
            'title' => __( strtoupper($key).' Privacy Only', 'veruspay-verus-gateway' ),
            'type' => 'checkbox',
            'label' => __( 'Enforce '.$item['fn'].' Sapling Privacy Payments', 'veruspay-verus-gateway' ),
            'description' => '',
            'default' => 'no',
            'class' => 'wc-veruspay-sapling-option wc_veruspay_walletsettings-toggle',
        );
    }
    else if ( $item['tx'] == 2 ) { // If only sapling/private
        $wc_veruspay_set_z = 1;
        $wc_veruspay_add_wallet_data[$key.'_sapling']	= array(	
            'title' => __( strtoupper($key).' Privacy', 'veruspay-verus-gateway' ),
            'type' => 'checkbox',
            'label' => __( strtoupper($key).' Sapling Privacy Enforced by Design', 'veruspay-verus-gateway' ),
            'description' => '',
            'default' => 'yes',
            'class' => 'wc-veruspay-sapling-option wc_veruspay_walletsettings-toggle wc_veruspay_hidden',
        );
    }
    else if ( $item['tx'] == 1 ) { // If only transparent
        $wc_veruspay_set_t = 1;
        $wc_veruspay_add_wallet_data[$key.'_sapling']	= array(
            'title' => '',
            'type' => 'title',
            'description' => '',
            'class' => 'wc-veruspay-sapling-option wc_veruspay_walletsettings-toggle wc_veruspay_hidden',
        );
        $this->update_option( $key.'_sapling', 'no' );
    }
    // Add to Wallet Addresses array
    $wc_veruspay_add_address_data = array();
    if ( $item['tx'] == 0 || $item['tx'] == 1 ) {
        $wc_veruspay_add_address_data[$key.'_addresses_title'] = array(
            'title' => __( '<img style="margin: 0 10px 0 0;" src="' . plugins_url( 'public/img/wc-'.strtolower($item['fn']).'-icon-16x.png', dirname( __FILE__ ) ) . '" />' . $item['fn'] . ' ' . strtoupper($key) . ' Transparent Backup Addresses', 'veruspay-verus-gateway' ),
            'type' => 'title',
            'description' => '',
            'class' => 'wc_veruspay_addresses-toggle wc_veruspay_title-sub',
        );
        // Store address fields, unused and used
        $wc_veruspay_add_address_data[$key.'_storeaddresses'] = array(
            'title' => __( 'Store ' . strtoupper($key) . ' Addresses', 'veruspay-verus-gateway' ),
            'type' => 'textarea',
            'description' => __( 'Enter ' . strtoupper($key) . ' addresses you own. If your store has a lot of traffic, we recommend 500 min.  These will also act as a fallback payment method in case there are issues with the wallet for Live stores.', 'veruspay-verus-gateway' ),
            'default' => '',
            'desc_tip' => TRUE,
            'class' => 'wc_veruspay_addresses-toggle',
        );
        $wc_veruspay_add_address_data[$key.'_usedaddresses'] = array(	
            'title' => __( 'Used ' . strtoupper($key) . ' Addresses', 'veruspay-verus-gateway' ),
            'type' => 'textarea',
            'description'	=> __( 'These are manually entered ' . strtoupper($key) . ' addresses which have been used', 'veruspay-verus-gateway' ),
            'default' => '',
            'desc_tip' => TRUE,
            'class' => 'wc-veruspay-disabled-input wc_veruspay_addresses-toggle'
        );
    }
    if ( isset( $item['gs'] ) || isset( $item['gm'] ) ) {
        if ( isset( $item['gs'] ) && $item['gs'] == 1 ) {
            $wc_veruspay_set_s = 1;
            $wc_veruspay_generate[$key.'_stake_enable'] = array(
                'title' => __( 'Enable Staking', 'veruspay-verus-gateway' ),
                'type' => 'checkbox',
                'label' => 'Enable Staking',
                'description' => '',
                'default' => 'no',
                'class' => 'wc_veruspay_enable_mining wc_veruspay_walletsettings-toggle',
            );
        }
        if ( isset( $item['gm'] ) && $item['gm'] == 1 ) {
            $wc_veruspay_set_m = 1;
            $wc_veruspay_generate[$key.'_mine_enable'] = array(
                'title' => __( 'Enable Mining', 'veruspay-verus-gateway' ),
                'type' => 'checkbox',
                'label' => 'Enable Mining',
                'description' => '',
                'default' => 'no',
                'class' => 'wc_veruspay_enable_mining wc_veruspay_walletsettings-toggle',
            );
            $wc_veruspay_generate[$key.'_mine_value'] = array(
                'title' => __( 'Mining Threads', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'description' => __( 'Enter the number of threads to dedicate to mining this coin', 'veruspay-verus-gateway' ),
                'default' => '0',
                'desc_tip' => TRUE,
                'class' => 'wc_veruspay_enable_mining wc_veruspay_walletsettings-toggle',
            );
        }
        $wc_veruspay_position_aft = array_search( 'vrsc_wallet_daemon_settings', array_keys( $wc_veruspay_add_wallet_data ) );
        apply_filters( 'wc_veruspay_form_fields', array_splice_assoc( $wc_veruspay_add_wallet_data, $wc_veruspay_position_aft, 0, $wc_veruspay_generate ) );
    }
    $wc_veruspay_position = array_search( 'addr_show', array_keys( $this->form_fields ) );
    $wc_veruspay_position_after = array_search( 'cust_show', array_keys( $this->form_fields ) );
    apply_filters( 'wc_veruspay_form_fields', array_splice_assoc( $this->form_fields, $wc_veruspay_position_after, 0, $wc_veruspay_add_address_data ) );
    apply_filters( 'wc_veruspay_form_fields', array_splice_assoc( $this->form_fields, $wc_veruspay_position, 0, $wc_veruspay_add_wallet_data ) );
    if ( $this->get_option($key.'_ssl') == 'yes' ) {
        if ( strpos ( $this->get_option($key.'_ip'), 'localhost' ) !== FALSE ) {
            $this->update_option( $key.'_ssl', 'no' );
            $wc_veruspay_proto = 'http';
        }
        else {
            $wc_veruspay_proto = 'https';
        }
    }
    else {
        $wc_veruspay_proto = 'http';
    }
    if ( $wc_veruspay_set_t === 1 ) {
        $wc_trnsaddr_setting = $this->get_option( $key.'_storeaddresses' );
        $wc_usedaddr_setting = $this->get_option( $key.'_usedaddresses' );
    }
    else {
        $wc_trnsaddr_setting = NULL;
        $wc_usedaddr_setting = NULL;
    }
    if ( isset( $wc_veruspay_global['chain_dtls'][$key] ) ) {
        $wc_veruspay_set_e = $wc_veruspay_global['chain_dtls'][$key];
    }
    $wc_veruspay_wallets[$key] = array(
        'enabled' => $this->get_option( $key.'_enable' ),
        'private' => $wc_veruspay_set_z,
        'transparent' => $wc_veruspay_set_t,
        'name' => $item['fn'].' Coin ('.strtoupper($key).')',
        'ip' => $wc_veruspay_proto . '://' . $this->get_option( $key.'_ip' ),
        'vct_version' => 'ERR',
        'version' => 'ERR',
        'explorer' => $wc_veruspay_set_e,
        'stat' => json_decode( wc_veruspay_go( $wc_veruspay_access_code, $wc_veruspay_wallets[$key]['ip'], $key, 'test' ), TRUE )['stat'],
        'sapling' => $this->get_option( $key.'_sapling' ),
        'addresses' => $wc_trnsaddr_setting,
        'addrcount' => '',
        'usedaddresses' => $wc_usedaddr_setting,
        'mining' => $wc_veruspay_set_m,
        'staking' => $wc_veruspay_set_s,
    );
    // Setup status of wallet to true or false
    if ( $wc_veruspay_wallets[$key]['stat'] == 1 ) {
        $wc_veruspay_wallets[$key]['vct_version'] = wc_veruspay_go( $wc_veruspay_access_code, $wc_veruspay_wallets[$key]['ip'], $key, 'vct_version' );
        $wc_veruspay_wallets[$key]['version'] = wc_veruspay_go( $wc_veruspay_access_code, $wc_veruspay_wallets[$key]['ip'], $key, 'version' );
        echo '<span id="verus_chain_tools_version" style="display:none">VerusChainTools Version: ' . $wc_veruspay_wallets['vct_version'] . '</span>';
    }
    // Insert Wallet Management Sections

    // First check for access code and stat of each coin, get balances and set vars
    if ( isset( $wc_veruspay_access_code ) && ! empty( $wc_veruspay_access_code ) ) {
        if ( $wc_veruspay_wallets[$key]['stat'] == 1 ) {
            $wc_veruspay_formfields_bal_t = json_decode( wc_veruspay_go( $wc_veruspay_access_code, $wc_veruspay_wallets[$key]['ip'], $key, 'bal' ), TRUE )['transparent'];
            $wc_veruspay_formfields_bal_z = json_decode( wc_veruspay_go( $wc_veruspay_access_code, $wc_veruspay_wallets[$key]['ip'], $key, 'bal' ), TRUE )['private'];
            $wc_veruspay_formfields_bal_u = json_decode( wc_veruspay_go( $wc_veruspay_access_code, $wc_veruspay_wallets[$key]['ip'], $key, 'bal' ), TRUE )['unconfirmed'];
            $wc_veruspay_show_taddr = wc_veruspay_go( $wc_veruspay_access_code, $wc_veruspay_wallets[$key]['ip'], $key, 'show_taddr' );
            $wc_veruspay_show_zaddr = wc_veruspay_go( $wc_veruspay_access_code, $wc_veruspay_wallets[$key]['ip'], $key, 'show_zaddr' );
        
            // Validate connection data
            if ( strpos( $wc_veruspay_show_taddr, 'Not Found' ) !== FALSE ) {
                $wc_veruspay_show_taddr = 'Err: Bad Connection to VerusChainTools or Out of Date!';
                $wc_veruspay_show_zaddr = 'Err: Bad Connection to VerusChainTools or Out of Date!';
            }
            if ( strpos( $wc_veruspay_formfields_bal_t, 'Not Found' ) !== FALSE || strpos( $wc_veruspay_formfields_bal_z, 'Not Found' ) !== FALSE ) {
                $wc_veruspay_bal_red_css = 'wc_veruspay_red';
                $wc_veruspay_formfields_bal_t = 'Err: Bad Connection to VerusChainTools or Not Installed!';
                $wc_veruspay_formfields_bal_z = 'Err: Bad Connection to VerusChainTools or Not Installed!';
                $wc_veruspay_formfields_bal_u = 'Err: Bad Connection to VerusChainTools or Not Installed!';
            }
            else if ( number_format( $wc_veruspay_formfields_bal_t, 8) == NULL || number_format( $wc_veruspay_formfields_bal_z, 8) == NULL ) {
                $wc_veruspay_bal_red_css = 'wc_veruspay_red';
                $wc_veruspay_formfields_bal_t = 'Err: Wallet Unreachable';
                $wc_veruspay_formfields_bal_z = 'Err: Wallet Unreachable';
                $wc_veruspay_formfields_bal_u = 'Err: Wallet Unreachable';
            }
            else {
                $wc_veruspay_bal_red_css = '';
                $wc_veruspay_formfields_bal_t = number_format( $wc_veruspay_formfields_bal_t, 8 );
                $wc_veruspay_formfields_bal_z = number_format( $wc_veruspay_formfields_bal_z, 8 );
                $wc_veruspay_formfields_bal_u = number_format( $wc_veruspay_formfields_bal_u, 8 );
            }
            if($wc_veruspay_formfields_bal_t > 0){
                if ( strlen( $wc_veruspay_show_taddr ) < 10 ) {
                    $wc_veruspay_withdraw_t = '<br><span class="wc_veruspay_cashout_text" id="wc_veruspay_cashout_text-'.$key.'-getttotalbalance"><span style="font-weight:bold;color:red;">No Transparent Cashout Address Set!</span> Set on your wallet server using the UpdateCashout.sh script.</span>';
                }
                else {
                    $wc_veruspay_withdraw_t = '<br><span class="wc_veruspay_cashout_text" id="wc_veruspay_cashout_text-'.$key.'-getttotalbalance">Cashout to <span style="font-weight:bold;">'.$wc_veruspay_show_taddr.'?</span> <div id="wc_veruspay_tbal-'.strtolower($key).'-button" class="wc_veruspay_cashout" data-coin="' . strtolower($key) . '" data-type="cashout_t" data-addrtype="Transparent" data-amount="'.$wc_veruspay_formfields_bal_t.'" data-address="'.$wc_veruspay_show_taddr.'">GO</div></span>';
                }
            }
            else {
                $wc_veruspay_withdraw_t = '<br><span class="wc_veruspay_cashout_text wc_veruspay_hidden" id="wc_veruspay_cashout_text-'.$key.'-getttotalbalance">Cashout to <span style="font-weight:bold;">'.$wc_veruspay_show_taddr.'?</span> <div id="wc_veruspay_tbal-'.strtolower($key).'-button" class="wc_veruspay_cashout" data-coin="' . strtolower($key) . '" data-type="cashout_t" data-addrtype="Transparent" data-amount="'.$wc_veruspay_formfields_bal_t.'" data-address="'.$wc_veruspay_show_taddr.'">GO</div></span>';
            }
            if($wc_veruspay_formfields_bal_z > 0){
                if ( strlen( $wc_veruspay_show_zaddr ) < 10 ) {
                    $wc_veruspay_withdraw_z = '<br><span class="wc_veruspay_cashout_text" id="wc_veruspay_cashout_text-'.$key.'-getztotalbalance"><span style="font-weight:bold;color:red;">No Private Cashout Address Set!</span> Set on your wallet server using the UpdateCashout.sh script.</span>';
                }
                else {
                    $wc_veruspay_withdraw_z = '<br><span class="wc_veruspay_cashout_text" id="wc_veruspay_cashout_text-'.$key.'-getztotalbalance">Cashout to <span style="font-weight:bold;">'.$wc_veruspay_show_zaddr.'?</span> <div id="wc_veruspay_zbal-'.strtolower($key).'-button" class="wc_veruspay_cashout" data-coin="' . strtolower($key) . '" data-type="cashout_z" data-addrtype="Private" data-amount="'.$wc_veruspay_formfields_bal_z.'" data-address="'.$wc_veruspay_show_zaddr.'">GO</div></span>';
                }
            }
            else {
                $wc_veruspay_withdraw_z = '<br><span class="wc_veruspay_cashout_text wc_veruspay_hidden" id="wc_veruspay_cashout_text-'.$key.'-getztotalbalance">Cashout to <span style="font-weight:bold;">'.$wc_veruspay_show_zaddr.'?</span> <div id="wc_veruspay_zbal-'.strtolower($key).'-button" class="wc_veruspay_cashout" data-coin="' . strtolower($key) . '" data-type="cashout_z" data-addrtype="Private" data-amount="'.$wc_veruspay_formfields_bal_z.'" data-address="'.$wc_veruspay_show_zaddr.'">GO</div></span>';
            }				
        }
        else {
            $wc_veruspay_bal_red_css = 'wc_veruspay_red';
            $wc_veruspay_formfields_bal_t = 'Err: No Connection to VerusChainTools or Not Installed!';
            $wc_veruspay_formfields_bal_z = 'Err: No Connection to VerusChainTools or Not Installed!';
            $wc_veruspay_formfields_bal_u = 'Err: No Connection to VerusChainTools or Not Installed!';
            $wc_veruspay_withdraw_t = '';
            $wc_veruspay_withdraw_z = '';
            echo '<div style="height:0px!important" id="wc_veruspay_'.$key.'_nostat"></div>';
        }
        // Setup sub array
        $wc_veruspay_wallet_management_data = array();
        $wc_veruspay_wallet_management_data[$key.'_wallet_management'] = array(
            'title' => __( '' . $item['fn'] . ' Wallet Management', 'veruspay-verus-gateways' ),
            'type' => 'title',
            'description' => '',
            'class' => 'wc_veruspay_title-sub wc_veruspay_title-sub-toggle-heading wc_veruspay_walletsettings-toggle',
        );
        if ( $wc_veruspay_set_t === 1 ) {
            $wc_veruspay_wallet_management_data[$key.'_wallet_tbalance'] = array(
                'title' => __( '<span style="padding-left:30px">Transparent Balance: <span style="font-weight:normal;"><span class="wc_veruspay_bal_admin '.$wc_veruspay_bal_red_css.'" id="wc_veruspay_tbal-'.strtolower($key).'" data-coin="'.strtolower($key).'" data-type="transparent">' . $wc_veruspay_formfields_bal_t . '</span> ' . strtoupper($key) . $wc_veruspay_withdraw_t . '</span></span>' , 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_title-sub_normal wc_veruspay_walletsettings-toggle',
            );
        }
        if ( $wc_veruspay_set_z === 1 ) {
            $wc_veruspay_wallet_management_data[$key.'_wallet_zbalance'] = array(
                'title' => __( '<span style="padding-left:30px">Private (Sapling) Balance: <span style="font-weight:normal;"><span class="wc_veruspay_bal_admin '.$wc_veruspay_bal_red_css.'" id="wc_veruspay_zbal-'.strtolower($key).'" data-coin="'.strtolower($key).'" data-type="private">' . $wc_veruspay_formfields_bal_z . '</span> ' . strtoupper($key) . $wc_veruspay_withdraw_z . '</span></span>' , 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_title-sub_normal wc_veruspay_walletsettings-toggle',
            );
        }
        $wc_veruspay_wallet_management_data[$key.'_wallet_unbalance'] = array(
            'title' => __( '<span style="padding-left:30px">Unconfirmed Incoming Balance: <span style="font-weight:normal;"><span class="wc_veruspay_bal_admin '.$wc_veruspay_bal_red_css.'" id="wc_veruspay_ubal-'.strtolower($key).'" data-coin="'.strtolower($key).'" data-type="unconfirmed">' . $wc_veruspay_formfields_bal_u . '</span> ' . strtoupper($key) . '</span></span>' , 'veruspay-verus-gateway' ),
            'type' => 'title',
            'description' => '',
            'class' => 'wc_veruspay_title-sub_normal wc_veruspay_walletsettings-toggle',
        );
        $wc_veruspay_position_pre = array_search( $key.'_wallet_daemon_settings', array_keys( $this->form_fields ) );
        apply_filters( 'wc_veruspay_form_fields', array_splice_assoc( $this->form_fields, $wc_veruspay_position_pre, 0, $wc_veruspay_wallet_management_data ) );
    }
    else {
        $wc_veruspay_wallet_management_data['access_code_instructions'] = array(
            'title' => __( '<span style="color:red">VERUSPAY DISABLED:</span> Activation Instructions', 'veruspay-verus-gateways' ),
            'type' => 'title',
            'description' => '<span style="font-size:16px">Thank you for installing VerusPay!<br><br>To use this self-sovereign payment plugin, you must install VerusChainTools on your wallet server (the server where your Verus or compatible wallet is installed).  At the successful completion of VerusChainTools installation and config on your wallet server, you\'ll be provided with an Access Code.  Enter that code above, save settings, and complete the configuration of VerusPay in the fields that will appear below.<br><br><span style="font-weight:bold">For configuration instructions visit <a href="https://veruspay.io/setup/">VerusPay.io/setup</a></span></span><br><br><br>',
            'class' => 'wc_veruspay_title-sub',
        );
        echo '<style>.wc_veruspay_admin_section{display:none!important;}#woocommerce_veruspay_verus_gateway_access_code_instructions + p{display:block;max-width:715px;}</style>';
        $this->update_option( 'enabled', 'no' );
        $wc_veruspay_position_pre = array_search( $key.'_wallet_daemon_settings', array_keys( $this->form_fields ) );
        apply_filters( 'wc_veruspay_form_fields', array_splice_assoc( $this->form_fields, $wc_veruspay_position_pre, 0, $wc_veruspay_wallet_management_data ) );
        break;
    }			
}

// Define array for use with checking for store availability - if no enabled wallets, disable gateway
$wc_veruspay_is_enabled = array();
// Check store status and wallet connection variants and update stat variable and store settings accordingly
foreach ( $wc_veruspay_wallets as $key => $item ) {
    // If wallet supports transparent addresses, check for manually entered addrs and cleanup addresses, prep for next step
    if ( isset( $wc_veruspay_wallets[$key]['enabled'] ) ) {
        if ( $wc_veruspay_wallets[$key]['transparent'] === 1 ){
            if ( (\strpos( $wc_veruspay_wallets[$key]['addresses'], 'e.g.') ) === FALSE ) {
                if ( strlen( $wc_veruspay_wallets[$key]['addresses'] ) < 10 ) {
                    $wc_veruspay_wallets[$key]['addrcount'] = 0;
                }
                else {
                    $wc_veruspay_clean_addresses = rtrim(str_replace(' ', '', str_replace('"', "", $wc_veruspay_wallets[$key]['addresses'])), ',');
                    $this->update_option( $key . '_storeaddresses', $wc_veruspay_clean_addresses );
                    $wc_veruspay_wallets[$key]['addrcount'] = count( explode( ',', $wc_veruspay_clean_addresses ) );
                }
                $this->form_fields[ $key . '_storeaddresses' ][ 'title' ] = __( 'Store Addresses (' . $wc_veruspay_wallets[$key]['addrcount'] . ')', 'veruspay-verus-gateway' );
            }
        }
    // Process each wallet based on status
    // First check for manually entered addresses for that store and set store to disabled if not exist, message to update
        if ( $wc_veruspay_wallets[$key]['enabled'] == 'yes' && $wc_veruspay_wallets[$key]['stat'] === 0 ) {
            if ( $wc_veruspay_wallets[$key]['addresses'] !== NULL ) {
                if ( strlen( $wc_veruspay_wallets[$key]['addresses'] ) < 10 ) {
                    $this->update_option( $key . '_enable', 'no' );
                    $wc_veruspay_is_enabled[] = 'no';
                    //$this->form_fields[ $key . '_sapling' ][ 'class' ] = 'wc_veruspay_hidden wc_veruspay_walletsettings-toggle';
                    if ( $item['private'] === 1 ) {
                        $this->update_option( $key . '_sapling', 'no' );
                        $this->form_fields[ $key . '_sapling' ][ 'label' ] = 'Sapling Privacy Unavailable in Manual Mode';
                    }
                    $this->form_fields[ $key . '_enable' ][ 'description' ] = $wc_veruspay_global['text_help']['admin_wallet_off_noaddr'];
                }
                else {
                    $this->update_option( $key . '_enable', 'yes' );
                    $wc_veruspay_is_enabled[] = 'yes';
                    //$this->form_fields[ $key . '_sapling' ][ 'class' ] = 'wc_veruspay_hidden wc_veruspay_walletsettings-toggle';
                    if ( $item['private'] === 1 ) {
                        $this->update_option( $key . '_sapling', 'no' );
                        $this->form_fields[ $key . '_sapling' ][ 'label' ] = 'Sapling Privacy Unavailable in Manual Mode';
                    }
                    $this->form_fields[ $key . '_enable' ][ 'description' ] = $wc_veruspay_global['text_help']['admin_wallet_off_addr'];
                }
            }
            else {
                $this->update_option( $key . '_enable', 'no' );
                $wc_veruspay_is_enabled[] = 'no';
                $this->form_fields[ $key . '_enable' ][ 'description' ] = $wc_veruspay_global['text_help']['admin_wallet_off_addroff'];
            }
        }
        // If wallet in function stats true
        else if ( $wc_veruspay_wallets[$key]['stat'] === 1 ) {
            // If wallet with transparent addresses, do the following
            if ( $wc_veruspay_wallets[$key]['addresses'] !== NULL && $wc_veruspay_wallets[$key]['enabled'] == 'yes' ) {
                // If backup addresses are not present, warn
                if ( strlen( $wc_veruspay_wallets[$key]['addresses'] ) < 10 ) {
                    $this->form_fields[ $key . '_enable' ][ 'description' ] = $wc_veruspay_global['text_help']['admin_wallet_on_noaddr'];
                }
                else {
                    $this->form_fields[ $key . '_enable' ][ 'description' ] = $wc_veruspay_global['text_help']['admin_wallet_on'];
                }
            }
            else {
                $this->form_fields[ $key . '_enable' ][ 'description' ] = $wc_veruspay_global['text_help']['admin_wallet_on'];
            }
            if ( $wc_veruspay_wallets[$key]['enabled'] == 'yes' ) {
                $wc_veruspay_is_enabled[] = 'yes';
                $this->form_fields[ $key . '_enable' ][ 'description' ] = $wc_veruspay_global['text_help']['admin_wallet_online'];
                if ( $wc_veruspay_wallets[$key]['mining'] === 1 ) {
                    $wc_veruspay_this_mine_stat = json_decode( wc_veruspay_go( $wc_veruspay_access_code, $wc_veruspay_wallets[$key]['ip'], $key, 'getgenerate' ), TRUE );
                    if ( $wc_veruspay_this_mine_stat['numthreads'] >= 1 ) {
                        $this->form_fields[ $key . '_mine_enable' ][ 'description' ] = '<span style="color:green">Mining with ' . $wc_veruspay_this_mine_stat['numthreads'] . ' threads!</span>';
                    }
                    if ( $wc_veruspay_this_mine_stat['staking'] == TRUE ) {
                        $this->form_fields[ $key . '_stake_enable' ][ 'description' ] = '<span style="color:green">Staking!</span>';
                    }
                    if ( $this->get_option( $key.'_mine_value' ) != $wc_veruspay_this_mine_stat['numthreads'] ) {
                        if ( $this->get_option( $key.'_mine_value' ) > 0 ){
                            wc_veruspay_go( $wc_veruspay_access_code, $wc_veruspay_wallets[$key]['ip'], $key, 'setgenerate', json_encode( array( TRUE, $this->get_option( $key.'_mine_value' ) ), TRUE ) );
                        }
                    }
                    if ( $this->get_option( $key.'_mine_enable' ) == 'yes' &&  $wc_veruspay_this_mine_stat['numthreads'] == 0 ) { // if not mining, start on check
                        wc_veruspay_go( $wc_veruspay_access_code, $wc_veruspay_wallets[$key]['ip'], $key, 'setgenerate', json_encode( array( TRUE, $this->get_option( $key.'_mine_value' ) ), TRUE ) );
                    }
                    if ( $this->get_option( $key.'_mine_enable' ) == 'no' && $wc_veruspay_this_mine_stat['numthreads'] >= 1 && $wc_veruspay_this_mine_stat['staking'] == TRUE ) {
                        wc_veruspay_go( $wc_veruspay_access_code, $wc_veruspay_wallets[$key]['ip'], $key, 'setgenerate', json_encode( array( TRUE, 0 ), TRUE ) );
                        $this->update_option( $key.'_mine_value', '0' );
                    }
                    if ( $this->get_option( $key.'_mine_enable' ) == 'no' && $wc_veruspay_this_mine_stat['numthreads'] >= 1 && $wc_veruspay_this_mine_stat['staking'] == FALSE ) {
                        wc_veruspay_go( $wc_veruspay_access_code, $wc_veruspay_wallets[$key]['ip'], $key, 'setgenerate', json_encode( array( FALSE ), TRUE ) );
                        $this->update_option( $key.'_mine_value', '0' );
                    }
                }
                if ( $wc_veruspay_wallets[$key]['staking'] === 1 ) {
                    if ( $this->get_option( $key.'_stake_enable' ) == 'yes' && $wc_veruspay_this_mine_stat['staking'] == FALSE ) {
                        wc_veruspay_go( $wc_veruspay_access_code, $wc_veruspay_wallets[$key]['ip'], $key, 'setgenerate', json_encode( array( TRUE, 0 ), TRUE ) );
                    }
                    if ( $this->get_option( $key.'_stake_enable' ) == 'no' &&  $wc_veruspay_this_mine_stat['staking'] == TRUE ) {
                        wc_veruspay_go( $wc_veruspay_access_code, $wc_veruspay_wallets[$key]['ip'], $key, 'setgenerate', json_encode( array( FALSE ), TRUE ) );
                    }						
                }
            }
        }
        else if ( $wc_veruspay_wallets[$key]['stat'] === 0 ) {
            if ( $wc_veruspay_wallets[$key]['addresses'] !== NULL ) {
                // If backup addresses are not present, warn
                if ( strlen( $wc_veruspay_wallets[$key]['addresses'] ) < 10 ) {
                    $this->update_option( $key . '_enable', 'no' );
                    $wc_veruspay_is_enabled[] = 'no';
                    $this->form_fields[ $key . '_enable' ][ 'description' ] = $wc_veruspay_global['text_help']['admin_wallet_off_noaddr'];
                }
                else {
                    $this->form_fields[ $key . '_enable' ][ 'description' ] = $wc_veruspay_global['text_help']['admin_wallet_off_addr'];
                }
            }
            else {
                $this->update_option( $key . '_enable', 'no' );
                $wc_veruspay_is_enabled[] = 'no';
                $this->form_fields[ $key . '_enable' ][ 'description' ] = $wc_veruspay_global['text_help']['admin_wallet_off_addroff'];
            }
        }
    }
}

// Check if Test Mode is enabled and update title and CSS
if ( $this->get_option( 'test_mode' ) == 'yes' ) {
    $this->form_fields[ 'test_msg' ][ 'title' ] = '<span style="color:red;">TEST MODE</span>';
    $this->form_fields[ 'test_msg' ][ 'class' ] = '';
}
else {
    $this->form_fields[ 'test_msg' ][ 'title' ] = '';
    $this->form_fields[ 'test_msg' ][ 'class' ] = 'wc_veruspay_set_css';
}
if ( ! in_array( 'yes', $wc_veruspay_is_enabled ) ) {
    $this->update_option( 'enabled', 'no' );
}
// Set wc_veruspay_wallets data meta field
$this->update_option('wc_veruspay_wallets', $wc_veruspay_wallets);