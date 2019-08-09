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
    echo '<div id="wc_veruspay_admin_menu"></div>';
}
// Check for access code and connectivity
$wc_veruspay_daemon_code_1 = $this->get_option( 'daemon_code_1' );
$wc_veruspay_daemon_ip_1 = $this->get_option( 'daemon_ip_1' );
if ( empty( $wc_veruspay_daemon_code_1 ) || empty( $wc_veruspay_daemon_ip_1 ) ) {
    $this->form_fields = apply_filters( 'wc_veruspay_form_fields', wc_veruspay_setup() );
    $this->update_option( 'enabled', 'no' );
}
else {
    // Setup vars and arrays
    $_statsArray = array('1' => array('','',),'2' => array('','',), '3' => array('','',), '4' => array('','',), '5' => array('','',), '6' => array('','',), '7' => array('','',),);
    $_hostedArray = array('','',);
    $_classArray = array('','','','','','',);
    $_daemonsArray = array(
        '2' => $this->get_option( 'daemon_ip_2' ),
        '3' => $this->get_option( 'daemon_ip_3' ),
        '4' => $this->get_option( 'daemon_ip_4' ),
        '5' => $this->get_option( 'daemon_ip_5' ),
        '6' => $this->get_option( 'daemon_ip_6' ),
        '7' => $this->get_option( 'daemon_ip_7' ),
    );
    if ( $this->get_option( 'hosted_enable' ) == 'yes' ) {
        // NOT YET IMPLEMENTED - FUTURE
    }
    $_proto1 = 'http://';
    if ( $this->get_option( 'daemon_ssl_1' ) == 'yes' ) {
        $_proto1 = 'https://';
    }
    $wc_veruspay_daemon_fullip_1 = $_proto1 . $wc_veruspay_daemon_ip_1;
    $wc_veruspay_global['chains'] = wc_veruspay_go( $wc_veruspay_daemon_code_1, $wc_veruspay_daemon_fullip_1, '_stat_', 'chainlist' );
    // Begin checking
    if ( empty( $wc_veruspay_global['chains'] ) ) {
        $_statsArray['1'][0] = ' - Status: <span style="color:red;font-size:16px;font-style:italic">UNREACHABLE</span>';
        if ( ! array_filter( $_daemonsArray ) ) {
            $this->update_option( 'enabled', 'no' );
        }
    }
	else if ( $wc_veruspay_global['chains'] === '_no_chains_found_' ) {
        $_statsArray['1'][0] = ' - Status: <span style="color:orange;font-size:16px;font-style:italic">OFFLINE</span>';
        if ( ! array_filter( $_daemonsArray ) ) {
            $this->update_option( 'enabled', 'no' );
        }
	}
    else {
        $_statsArray['1'][0] = ' - Status: <span style="color:green;font-size:16px;font-style:italic">ONLINE</span>';
        // Add primary daemon chains to global array
        foreach ( $wc_veruspay_global['chains'] as $key => $item ) {
            $chain_up = strtoupper( $key );
            $wc_veruspay_global['chains'][$chain_up]['S'] = $this->get_option( 'daemon_fn_1' );
            $wc_veruspay_global['chains'][$chain_up]['IP'] = $wc_veruspay_daemon_fullip_1;
            $wc_veruspay_global['chains'][$chain_up]['DC'] = $wc_veruspay_daemon_code_1;
            $wc_veruspay_global['chains'][$chain_up]['ST'] = json_decode( wc_veruspay_go( $wc_veruspay_daemon_code_1, $wc_veruspay_daemon_fullip_1, $chain_up, 'test' ), TRUE )['stat'];
            if ( $wc_veruspay_global['chains'][$chain_up]['ST'] == 1 ) {
                $_stat = 'border-color: #13f413';
                $_tooltip = $chain_up . ': ONLINE';
            }
            else {
                $_stat = 'border-color:#f92a2a;opacity:0.6;';
                $_tooltip = $chain_up . ': OFFLINE';
            }
            $_statsArray['1'][1] = $_statsArray['1'][1] . '<span title="' . $_tooltip . '" class="wc_veruspay_coinlist" style="background-image: url(' . $wc_veruspay_global['coinimg'] . $chain_up .'.png);'.$_stat.'"></span>';
        }
        $_statsArray['1'][1] = '<span>' . $_statsArray['1'][1] . '</span>';
        // Iterate through any other live daemons and add to global array and set classes        
        foreach ( $_daemonsArray as $key => $item ) {
            if ( empty( $item ) ) {
                $_classArray[$key] = 'wc_veruspay_daemon_add';
            }
            else {
                if ( $item === $wc_veruspay_daemon_ip_1 ) {
                    $this->update_option( 'daemon_ip_' . $key, 'duplicate daemon' );
                    header("Refresh: 0");
                }
                $_code = $this->get_option( 'daemon_code_' . $key );
                $_proto = 'http://';
                if ( $this->get_option( 'daemon_ssl_' . $key ) == 'yes' ) {
                    $_proto = 'https://';
                }
                $_ip = $_proto . $item;
                if ( empty( $_code ) ) {
                    $_statsArray[$key][0] = '<span style="color:red">Access Code Required</span>';
                }
                else {
                    $_list = wc_veruspay_go( $_code, $_ip, '_stat_', 'chainlist' );
                    if ( empty( $_list ) || strpos( $_list, 'ERR:' ) !== false || $item === 'duplicate daemon' || strpos( $_list, 'cURL error' ) !== false ) {
                        $_statsArray[$key][0] = ' - Status: <span style="color:red;font-size:16px;font-style:italic">UNREACHABLE</span>';
                    }
                    else if ( $_list === '_no_chains_found_' ) {
                        $_statsArray[$key][0] = ' - Status: <span style="color:orange;font-size:16px;font-style:italic">OFFLINE</span>';
                    }
                    else {
                        $_statsArray[$key][0] = ' - Status: <span style="color:green;font-size:16px;font-style:italic">ONLINE</span>';
                        foreach ( $_list as $_key => $_item ) {
                            $chain_up = strtoupper( $_key );
                            $_list[$chain_up]['S'] = $this->get_option( 'daemon_fn_' . $key );
                            $_list[$chain_up]['IP'] = $_ip;
                            $_list[$chain_up]['DC'] = $_code;
                            $_list[$chain_up]['ST'] = json_decode( wc_veruspay_go( $_code, $_ip, $chain_up, 'test' ), TRUE )['stat'];
                            if ( $_list[$chain_up]['ST'] == 1 ) {
                                $_stat = 'border-color: #13f413';
                                $_tooltip = $chain_up . ': ONLINE';
                            }
                            else {
                                $_stat = 'border-color:#f92a2a;opacity:0.6;';
                                $_tooltip = $chain_up . ': OFFLINE';
                            }
                            $_statsArray[$key][1] = $_statsArray[$key][1] . '<span title="' . $_tooltip . '" class="wc_veruspay_coinlist" style="background-image: url(' . $wc_veruspay_global['coinimg'] . $chain_up .'.png);'.$_stat.'"></span>';
                        }
                        $_statsArray[$key][1] = '<span>' . $_statsArray[$key][1] . '</span>';
                        $wc_veruspay_global['chains'] = array_merge( $wc_veruspay_global['chains'], $_list );
                    }
                }
            }
        }
        /**
         * Each element is assigned id with "woocommerce_veruspay_verus_gateway_" prepended to item name below
         */
        if ( $this->get_option( 'test_mode' ) == 'yes' ) {
            $_testmsg = '<span style="color:red">TEST MODE ENABLED</span>';
        }
        else {
            $_testmsg = 'TEST MODE';
        }
        $this->form_fields = apply_filters( 'wc_veruspay_form_fields', array(
        // Used for feedback and settings within admin
            '_top_of_form' => array(
                'title'	=> '',
                'type' => 'title',
                'class' => 'wc_veruspay_set_css wc_veruspay_form_top'
            ),
            'hidden_set_css' => array(
                'title'	=> '',
                'type' => 'title',
                'class' => 'wc_veruspay_set_css',
            ),
            // Enable/Disable the VerusPay gateway
            'enabled' => array(
                'title' => __( 'ENABLE VERUSPAY', 'veruspay-verus-gateway' ),
                'type' => 'checkbox',
                'label' => __( ' ', 'veruspay-verus-gateway' ),
                'default' => 'yes',
                'class' => 'wc_veruspay_top_options wc_veruspay_enable-check',
            ),
            // Enable/Disable Testmode
            'test_mode' => array(
                'title' => __( $_testmsg, 'veruspay-verus-gateway' ),
                'type' => 'checkbox',
                'label' => ' ',
                'default' => 'no',
                'class' => 'wc_veruspay_top_options wc_veruspay_testmode-check',
            ),
            // Show/Hide Tabs Headings
            // Daemon Server Title (interactive to show/hide Daemon section)
            'daemon_settings_show' => array(
                'title' => __( '<span class="wc_veruspay_tab-title">Daemons</span>', 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_tab-container wc_veruspay_admin_section wc_veruspay_toggledaemon wc_veruspay_pointer',
            ),
            // Wallet Management Title (interactive to show or hide the Wallet section)
            'wallet_settings_show' => array(
                'title' => __( '<span class="wc_veruspay_tab-title">Wallets</span>', 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_tab-container wc_veruspay_admin_section wc_veruspay_togglewallet wc_veruspay_pointer',
            ),
            // Addresses Title
            'addr_show' => array(
                'title' => __( '<span class="wc_veruspay_tab-title">Addresses</span>', 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_tab-container wc_veruspay_admin_section wc_veruspay_toggleaddr wc_veruspay_pointer',
            ),
            // Content title, interactive (show or hide content customization fields)
            'cust_show' => array(
                'title' => __( '<span class="wc_veruspay_tab-title">Content</span>', 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_tab-container wc_veruspay_admin_section wc_veruspay_togglecust wc_veruspay_pointer',
            ),
            // Store Option title, interactive (show or hide options)
            'options_show' => array(
                'title' => __( '<span class="wc_veruspay_tab-title">Options</span>', 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_tab-container wc_veruspay_admin_section wc_veruspay_toggleoptions wc_veruspay_pointer',
            ),
            // Hosted title
            'hosted_settings_show' => array(
                'title' => __( '<span class="wc_veruspay_tab-title">VerusPay Hosted</span>', 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_title-deactive wc_veruspay_tab-container wc_veruspay_admin_section wc_veruspay_togglehosted wc_veruspay_pointer',
            ),
            // Hosted VerusPay Service
            'hosted_heading' => array(
                'title' => __( 'VerusPay Hosted - Status: coming soon' . $_hostedArray[0], 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_title-sub wc_veruspay_title-sub-toggle-heading wc_veruspay_hostedsettings-toggle',
            ),
            'hosted_stat' => array(
                'title' => __( 'Configured Coins: ' . $_hostedArray[1], 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_section_heading wc_veruspay_title-sub wc_veruspay_title-sub-toggle-under wc_veruspay_hostedsettings-toggle',
            ),
            'hosted_enable' => array(
                'title' => __( 'Enable', 'veruspay-verus-gateway' ),
                'type' => 'checkbox',
                'label' => __( 'Enable VerusPay Hosted Daemon', 'veruspay-verus-gateway' ),
                'description' => '',
                'default' => 'yes',
                'class' => 'wc_veruspay_hostedsettings-toggle',
            ),
            'hosted_access' => array(
                'title' => __( 'Access Code', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'label' => __( 'Access Code', 'veruspay-verus-gateway' ),
                'class' => 'wc_veruspay_hostedsettings-toggle',
            ),
            
            // Deamon Paths
            // Primary Daemon Server
            // css id: woocommerce_veruspay_verus_gateway_primary_daemon_heading
            'daemon_heading_1' => array(
                'title' => __( 'Primary Daemon Server Settings' . $_statsArray['1'][0], 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_title-sub wc_veruspay_title-sub-toggle-heading wc_veruspay_daemonsettings-toggle',
            ),
            'daemon_stat_1' => array(
                'title' => __( 'Configured Coins: ' . $_statsArray['1'][1], 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_section_heading wc_veruspay_title-sub wc_veruspay_title-sub-toggle-under wc_veruspay_daemonsettings-toggle',
            ),
            'daemon_fn_1' => array(
                'title' => __( 'Custom Name', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'description' => __( 'Give this daemon server a custom name, e.g. "VRSC and ARRR Server"', 'veruspay-verus-gateway' ),
                'default' => 'My Primary Daemon Server',
                'desc_tip' => TRUE,
                'class' => 'wc_veruspay_daemonsettings-toggle',
            ),
            'daemon_ip_1' => array(
                'title' => __( 'Server IP/Path', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'description' => __( 'Enter the IP address of your primary daemon server. If on this server, enter the folder (must be a folder located at your root web folder)', 'veruspay-verus-gateway' ),
                'default' => 'IP or local folder name (e.g. enter just verustools if at /var/www/html/verustools)',
                'desc_tip' => TRUE,
                'class' => 'wc_veruspay_daemonsettings-toggle',
            ),
            'daemon_ssl_1' => array(
                'title' => __( 'Enable SSL?', 'veruspay-verus-gateway' ),
                'type' => 'checkbox',
                'label' => __( 'Enable SSL connection', 'veruspay-verus-gateway' ),
                'description' => '',
                'default' => 'yes',
                'class' => 'wc_veruspay_daemonsettings-toggle',
            ),
            'daemon_code_1' => array(
                'title' => __( 'Access Code', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'label' => __( 'Access Code', 'veruspay-verus-gateway' ),
                'class' => 'wc_veruspay_daemonsettings-toggle',
            ),
            // Daemon 2
            'daemon_heading_2' => array(
                'title' => __( 'Daemon Server 2 Settings' . $_statsArray['2'][0], 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_title-sub wc_veruspay_title-sub-toggle-heading wc_veruspay_daemonsettings-toggle ' . $_classArray['2'] . '-title',
            ),
            'daemon_stat_2' => array(
                'title' => __( 'Configured Coins: ' . $_statsArray['2'][1], 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_section_heading wc_veruspay_title-sub wc_veruspay_title-sub-toggle-under wc_veruspay_daemonsettings-toggle ' . $_classArray['2'] . '-status',
            ),
            'daemon_fn_2' => array(
                'title' => __( 'Custom Name', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'description' => __( 'Give this daemon server a custom name, e.g. "VRSC and ARRR Server"', 'veruspay-verus-gateway' ),
                'default' => 'Daemon Server 2',
                'desc_tip' => TRUE,
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['2'] . '-fn',
            ),
            'daemon_ip_2' => array(
                'title' => __( 'Server IP/Path', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'description' => __( 'Enter the IP address of your primary daemon server. If on this server, enter the folder (must be a folder located at your root web folder)', 'veruspay-verus-gateway' ),
                'default' => 'IP or local folder name (e.g. enter just verustools if at /var/www/html/verustools)',
                'desc_tip' => TRUE,
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['2'] . '-ip',
            ),
            'daemon_ssl_2' => array(
                'title' => __( 'Enable SSL?', 'veruspay-verus-gateway' ),
                'type' => 'checkbox',
                'label' => __( 'Enable SSL connection', 'veruspay-verus-gateway' ),
                'description' => '',
                'default' => 'yes',
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['2'] . '-ssl',
            ),
            'daemon_code_2' => array(
                'title' => __( 'Access Code', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'label' => __( 'Access Code', 'veruspay-verus-gateway' ),
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['2'] . '-code',
            ),
            // Daemon 3
            'daemon_heading_3' => array(
                'title' => __( 'Daemon Server 3 Settings' . $_statsArray['3'][0], 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_title-sub wc_veruspay_title-sub-toggle-heading wc_veruspay_daemonsettings-toggle ' . $_classArray['3'] . '-title',
            ),
            'daemon_stat_3' => array(
                'title' => __( 'Configured Coins: ' . $_statsArray['3'][1], 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_section_heading wc_veruspay_title-sub wc_veruspay_title-sub-toggle-under wc_veruspay_daemonsettings-toggle ' . $_classArray['3'] . '-status',
            ),
            'daemon_fn_3' => array(
                'title' => __( 'Custom Name', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'description' => __( 'Give this daemon server a custom name, e.g. "VRSC and ARRR Server"', 'veruspay-verus-gateway' ),
                'default' => 'Daemon Server 3',
                'desc_tip' => TRUE,
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['3'] . '-fn',
            ),
            'daemon_ip_3' => array(
                'title' => __( 'Server IP/Path', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'description' => __( 'Enter the IP address of your primary daemon server. If on this server, enter the folder (must be a folder located at your root web folder)', 'veruspay-verus-gateway' ),
                'default' => 'IP or local folder name (e.g. enter just verustools if at /var/www/html/verustools)',
                'desc_tip' => TRUE,
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['3'] . '-ip',
            ),
            'daemon_ssl_3' => array(
                'title' => __( 'Enable SSL?', 'veruspay-verus-gateway' ),
                'type' => 'checkbox',
                'label' => __( 'Enable SSL connection', 'veruspay-verus-gateway' ),
                'description' => '',
                'default' => 'yes',
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['3'] . '-ssl',
            ),
            'daemon_code_3' => array(
                'title' => __( 'Access Code', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'label' => __( 'Access Code', 'veruspay-verus-gateway' ),
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['3'] . '-code',
            ),
            // Daemon 4
            'daemon_heading_4' => array(
                'title' => __( 'Daemon Server 4 Settings' . $_statsArray['4'][0], 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_title-sub wc_veruspay_title-sub-toggle-heading wc_veruspay_daemonsettings-toggle ' . $_classArray['4'] . '-title',
            ),
            'daemon_stat_4' => array(
                'title' => __( 'Configured Coins: ' . $_statsArray['4'][1], 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_section_heading wc_veruspay_title-sub wc_veruspay_title-sub-toggle-under wc_veruspay_daemonsettings-toggle ' . $_classArray['4'] . '-status',
            ),
            'daemon_fn_4' => array(
                'title' => __( 'Custom Name', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'description' => __( 'Give this daemon server a custom name, e.g. "VRSC and ARRR Server"', 'veruspay-verus-gateway' ),
                'default' => 'Daemon Server 4',
                'desc_tip' => TRUE,
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['4'] . '-fn',
            ),
            'daemon_ip_4' => array(
                'title' => __( 'Server IP/Path', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'description' => __( 'Enter the IP address of your primary daemon server. If on this server, enter the folder (must be a folder located at your root web folder)', 'veruspay-verus-gateway' ),
                'default' => 'IP or local folder name (e.g. enter just verustools if at /var/www/html/verustools)',
                'desc_tip' => TRUE,
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['4'] . '-ip',
            ),
            'daemon_ssl_4' => array(
                'title' => __( 'Enable SSL?', 'veruspay-verus-gateway' ),
                'type' => 'checkbox',
                'label' => __( 'Enable SSL connection', 'veruspay-verus-gateway' ),
                'description' => '',
                'default' => 'yes',
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['4'] . '-ssl',
            ),
            'daemon_code_4' => array(
                'title' => __( 'Access Code', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'label' => __( 'Access Code', 'veruspay-verus-gateway' ),
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['4'] . '-code',
            ),
            // Daemon 5
            'daemon_heading_5' => array(
                'title' => __( 'Daemon Server 5 Settings' . $_statsArray['5'][0], 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_title-sub wc_veruspay_title-sub-toggle-heading wc_veruspay_daemonsettings-toggle ' . $_classArray['5'] . '-title',
            ),
            'daemon_stat_5' => array(
                'title' => __( 'Configured Coins: ' . $_statsArray['5'][1], 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_section_heading wc_veruspay_title-sub wc_veruspay_title-sub-toggle-under wc_veruspay_daemonsettings-toggle ' . $_classArray['5'] . '-status',
            ),
            'daemon_fn_5' => array(
                'title' => __( 'Custom Name', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'description' => __( 'Give this daemon server a custom name, e.g. "VRSC and ARRR Server"', 'veruspay-verus-gateway' ),
                'default' => 'Daemon Server 5',
                'desc_tip' => TRUE,
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['5'] . '-fn',
            ),
            'daemon_ip_5' => array(
                'title' => __( 'Server IP/Path', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'description' => __( 'Enter the IP address of your primary daemon server. If on this server, enter the folder (must be a folder located at your root web folder)', 'veruspay-verus-gateway' ),
                'default' => 'IP or local folder name (e.g. enter just verustools if at /var/www/html/verustools)',
                'desc_tip' => TRUE,
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['5'] . '-ip',
            ),
            'daemon_ssl_5' => array(
                'title' => __( 'Enable SSL?', 'veruspay-verus-gateway' ),
                'type' => 'checkbox',
                'label' => __( 'Enable SSL connection', 'veruspay-verus-gateway' ),
                'description' => '',
                'default' => 'yes',
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['5'] . '-ssl',
            ),
            'daemon_code_5' => array(
                'title' => __( 'Access Code', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'label' => __( 'Access Code', 'veruspay-verus-gateway' ),
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['5'] . '-code',
            ),
            // Daemon 6
            'daemon_heading_6' => array(
                'title' => __( 'Daemon Server 6 Settings' . $_statsArray['6'][0], 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_title-sub wc_veruspay_title-sub-toggle-heading wc_veruspay_daemonsettings-toggle ' . $_classArray['6'] . '-title',
            ),
            'daemon_stat_6' => array(
                'title' => __( 'Configured Coins: ' . $_statsArray['6'][1], 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_section_heading wc_veruspay_title-sub wc_veruspay_title-sub-toggle-under wc_veruspay_daemonsettings-toggle ' . $_classArray['6'] . '-status',
            ),
            'daemon_fn_6' => array(
                'title' => __( 'Custom Name', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'description' => __( 'Give this daemon server a custom name, e.g. "VRSC and ARRR Server"', 'veruspay-verus-gateway' ),
                'default' => 'Daemon Server 6',
                'desc_tip' => TRUE,
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['6'] . '-fn',
            ),
            'daemon_ip_6' => array(
                'title' => __( 'Server IP/Path', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'description' => __( 'Enter the IP address of your primary daemon server. If on this server, enter the folder (must be a folder located at your root web folder)', 'veruspay-verus-gateway' ),
                'default' => 'IP or local folder name (e.g. enter just verustools if at /var/www/html/verustools)',
                'desc_tip' => TRUE,
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['6'] . '-ip',
            ),
            'daemon_ssl_6' => array(
                'title' => __( 'Enable SSL?', 'veruspay-verus-gateway' ),
                'type' => 'checkbox',
                'label' => __( 'Enable SSL connection', 'veruspay-verus-gateway' ),
                'description' => '',
                'default' => 'yes',
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['6'] . '-ssl',
            ),
            'daemon_code_6' => array(
                'title' => __( 'Access Code', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'label' => __( 'Access Code', 'veruspay-verus-gateway' ),
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['6'] . '-code',
            ),
            // Daemon 7 
            'daemon_heading_7' => array(
                'title' => __( 'Daemon Server 7 Settings' . $_statsArray['7'][0], 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_title-sub wc_veruspay_title-sub-toggle-heading wc_veruspay_daemonsettings-toggle ' . $_classArray['7'] . '-title',
            ),
            'daemon_stat_7' => array(
                'title' => __( 'Configured Coins: ' . $_statsArray['7'][1], 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_section_heading wc_veruspay_title-sub wc_veruspay_title-sub-toggle-under wc_veruspay_daemonsettings-toggle ' . $_classArray['7'] . '-status',
            ),
            'daemon_fn_7' => array(
                'title' => __( 'Customwc_veruspay_chainsName', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'description' => __( 'wc_veruspay_chainsive this daemon server a custom name, e.g. "VRSC and ARRR Server"', 'veruspay-verus-gateway' ),
                'default' => 'Daemon Swc_veruspay_chainsrver 7',
                'desc_tip' => TRUE,
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['7'] . '-fn',
            ),
            'daemon_ip_7' => array(
                'title' => __( 'Server IP/Path', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'description' => __( 'Enter the IP address of your primary daemon server. If on this server, enter the folder (must be a folder located at your root web folder)', 'veruspay-verus-gateway' ),
                'default' => 'IP or local folder name (e.g. enter just verustools if at /var/www/html/verustools)',
                'desc_tip' => TRUE,
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['7'] . '-ip',
            ),
            'daemon_ssl_7' => array(
                'title' => __( 'Enable SSL?', 'veruspay-verus-gateway' ),
                'type' => 'checkbox',
                'label' => __( 'Enable SSL connection', 'veruspay-verus-gateway' ),
                'description' => '',
                'default' => 'yes',
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['7'] . '-ssl',
            ),
            'daemon_code_7' => array(
                'title' => __( 'Access Code', 'veruspay-verus-gateway' ),
                'type' => 'text',
                'label' => __( 'Access Code', 'veruspay-verus-gateway' ),
                'class' => 'wc_veruspay_daemonsettings-toggle ' . $_classArray['7'] . '-code',
            ),
            // Add Daemon Button
            'add_daemon' => array(
                'title' => __( '+ Add Daemon Server (7 max)', 'veruspay-verus-gateway' ),
                'type' => 'title',
                'class' => 'wc_veruspay_daemonsettings-toggle wc_veruspay_daemon_add-button',
            ),
            // ***************************
            // Wallet Management Spliced in
            // ***************************
            'wallet_insert' => array(
                'title' => __( '', 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_noshow',
            ),
            // ***************************
            // Address Settings Spliced in
            // ***************************
            'addr_insert' => array(
                'title' => __( '', 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_noshow',
            ),
            // Content Customizations
            'content_title' => array(
                'title' => __( 'Store Content Customizations', 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_section_heading wc_veruspay_title-sub wc_veruspay_title-sub-toggle-heading wc_veruspay_customization-toggle',
            ),
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
            // Store options
            'options_title' => array(
                'title' => __( 'VerusPay Gateway Store Options', 'veruspay-verus-gateway' ),
                'type' => 'title',
                'description' => '',
                'class' => 'wc_veruspay_section_heading wc_veruspay_title-sub wc_veruspay_title-sub-toggle-heading wc_veruspay_options-toggle',
            ),
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
                'type' => 'select',
                'description'	=> __( 'Choose the max size of QR images generated during customer checkout', 'veruspay-verus-gateway' ),
                'default' => 'Large',
                'desc_tip' => TRUE,
                'options' => array(
                    '8' => __( 'Small', 'veruspay-verus-gateway' ),
                    '10' => __( 'Medium', 'veruspay-verus-gateway' ),
                    '12' => __( 'Large', 'veruspay-verus-gateway' ),
                ),
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
        ));
        // Splice wallet settings and address settings using foreach
        foreach( $wc_veruspay_global['chains'] as $key => $item ) {
            $chain_up = strtoupper( $key );
            $chain_lo = strtolower( $key );
            $wc_veruspay_set_t = 0;
            $wc_veruspay_set_z = 0;
            if ( $wc_veruspay_global['chains'][$chain_up]['ST'] == 1 ) {
                $wc_veruspay_wallet_stat = '<span style="color:green">ONLINE</span>';
            }
            else {
                $wc_veruspay_wallet_stat = '<span style="color:red">OFFLINE</span>';
            }
            // Add to Wallet Settings array
            $wc_veruspay_add_wallet_data = array(
                $chain_lo.'_wallet_title' => array(
                    'title' => __( '<img style="margin: 0 10px 0 0;" src="' . $wc_veruspay_global['coinimg'] . $chain_up .'.png" />' . $chain_up . ' ' . $item['FN'] . ' Wallet - Fiat Price: ' . get_woocommerce_currency_symbol() . '<span class="wc_veruspay_fiat_rate" data-coin="'.$chain_up.'">' . wc_veruspay_price( $chain_up,  get_woocommerce_currency() ) . '</span>', 'veruspay-verus-gateway' ),
                    'type' => 'title',
                    'description' => '',
                    'class' => 'wc_veruspay_section_heading wc_veruspay_title-sub wc_veruspay_title-sub-toggle-heading wc_veruspay_walletsettings-toggle',
                ),
                $chain_lo.'_status' => array(
                    'title' => __( 'Wallet Status: ' . $wc_veruspay_wallet_stat, 'veruspay-verus-gateway' ),
                    'type' => 'title',
                    'description' => '',
                    'class' => 'wc_veruspay_section_heading wc_veruspay_title-sub wc_veruspay_title-sub-toggle-under wc_veruspay_walletsettings-toggle',
                ),
                $chain_lo.'_enable' => array(
                    'title' => __( 'Enable '.$item['FN'].' Payments', 'veruspay-verus-gateway' ),
                    'type' => 'checkbox',
                    'label' => 'Enable Receiving '.$item['FN'].' Payments?',
                    'description' => '',
                    'default' => 'yes',
                    'class' => 'wc_veruspay_setwalletip wc_veruspay_walletsettings-toggle',
                ),
            );
            // Sapling Enforce (if applicable)
            if ( $item['TX'] == 0 ) { // If both transparent and sapling/private
                $wc_veruspay_set_t = 1;
                $wc_veruspay_set_z = 1;
                $wc_veruspay_add_wallet_data[$chain_lo.'_sapling']	= array(
                    'title' => __( $chain_up.' Privacy Only', 'veruspay-verus-gateway' ),
                    'type' => 'checkbox',
                    'label' => __( 'Enforce '.$item['FN'].' Sapling Privacy Payments', 'veruspay-verus-gateway' ),
                    'description' => '',
                    'default' => 'no',
                    'class' => 'wc-veruspay-sapling-option wc_veruspay_walletsettings-toggle',
                );
                $wc_veruspay_sapling_settings = array(
                    'ZC' => 1,
                    'SP' => $this->get_option( $chain_lo.'_sapling' ),
                    'TC' => 1,
                );
            }
            else if ( $item['TX'] == 2 ) { // If only sapling/private
                $wc_veruspay_set_z = 1;
                $wc_veruspay_add_wallet_data[$chain_lo.'_sapling']	= array(	
                    'title' => __( $chain_up.' Privacy', 'veruspay-verus-gateway' ),
                    'type' => 'checkbox',
                    'label' => __( $chain_up.' Sapling Privacy Enforced by Design', 'veruspay-verus-gateway' ),
                    'description' => '',
                    'default' => 'yes',
                    'class' => 'wc-veruspay-sapling-option wc_veruspay_walletsettings-toggle wc_veruspay_hidden',
                );
                $wc_veruspay_sapling_settings = array(
                    'ZC' => 1,
                    'SP' => $this->get_option( $chain_lo.'_sapling' ),
                    'TC' => 0,
                );
            }
            else if ( $item['TX'] == 1 ) { // If only transparent
                $wc_veruspay_set_t = 1;
                $wc_veruspay_add_wallet_data[$chain_lo.'_sapling']	= array(
                    'title' => '',
                    'type' => 'text',
                    'description' => '',
                    'class' => 'wc-veruspay-sapling-option wc_veruspay_walletsettings-toggle wc_veruspay_hidden',
                );
                $this->update_option( $chain_lo.'_sapling', 'no' );
                $wc_veruspay_sapling_settings = array(
                    'ZC' => 0,
                    'TC' => 1,
                );
            }
            if ( isset( $item['GS'] ) || isset( $item['GM'] ) ) {
                if ( isset( $item['GS'] ) && $item['GS'] == 1 ) {
                    $wc_veruspay_add_wallet_data[$chain_lo.'_stake_enable'] = array(
                        'title' => __( 'Enable Staking', 'veruspay-verus-gateway' ),
                        'type' => 'checkbox',
                        'label' => 'Enable Staking',
                        'description' => '',
                        'default' => 'no',
                        'class' => 'wc_veruspay_enable_mining wc_veruspay_walletsettings-toggle',
                    );
                }
                if ( isset( $item['GM'] ) && $item['GM'] == 1 ) {
                    $wc_veruspay_add_wallet_data[$chain_lo.'_mine_enable'] = array(
                        'title' => __( 'Enable Mining', 'veruspay-verus-gateway' ),
                        'type' => 'checkbox',
                        'label' => 'Enable Mining',
                        'description' => '',
                        'default' => 'no',
                        'class' => 'wc_veruspay_enable_mining wc_veruspay_walletsettings-toggle',
                    );
                    $wc_veruspay_add_wallet_data[$chain_lo.'_mine_value'] = array(
                        'title' => __( 'Mining Threads', 'veruspay-verus-gateway' ),
                        'type' => 'text',
                        'description' => __( 'Enter the number of threads to dedicate to mining this coin', 'veruspay-verus-gateway' ),
                        'default' => '0',
                        'desc_tip' => TRUE,
                        'class' => 'wc_veruspay_enable_mining wc_veruspay_walletsettings-toggle',
                    );
                }
            }
            // Add to Wallet Addresses array
            $wc_veruspay_add_address_data = array();
            if ( $item['TX'] == 0 || $item['TX'] == 1 ) {
                $wc_veruspay_add_address_data[$chain_lo.'_addresses_title'] = array(
                    'title' => __( '<img style="margin: 0 10px 0 0;" src="' . $wc_veruspay_global['coinimg'] . $chain_up .'.png" />' . $item['FN'] . ' ' . $chain_up . ' Transparent Backup Addresses', 'veruspay-verus-gateway' ),
                    'type' => 'title',
                    'description' => '',
                    'class' => 'wc_veruspay_section_heading wc_veruspay_title-sub wc_veruspay_title-sub-toggle-heading wc_veruspay_addresses-toggle',
                );
                // Store address fields, unused and used
                $wc_veruspay_add_address_data[$chain_lo.'_storeaddresses'] = array(
                    'title' => __( 'Store ' . $chain_up . ' Addresses', 'veruspay-verus-gateway' ),
                    'type' => 'textarea',
                    'description' => __( 'Enter ' . $chain_up . ' addresses you own. If your store has a lot of traffic, we recommend 500 min.  These will also act as a fallback payment method in case there are issues with the wallet for Live stores.', 'veruspay-verus-gateway' ),
                    'default' => '',
                    'desc_tip' => TRUE,
                    'class' => 'wc_veruspay_addresses-toggle',
                );
                $wc_veruspay_add_address_data[$chain_lo.'_usedaddresses'] = array(	
                    'title' => __( 'Used ' . $chain_up . ' Addresses', 'veruspay-verus-gateway' ),
                    'type' => 'textarea',
                    'description'	=> __( 'These are manually entered ' . $chain_up . ' addresses which have been used', 'veruspay-verus-gateway' ),
                    'default' => '',
                    'desc_tip' => TRUE,
                    'class' => 'wc-veruspay-disabled-input wc_veruspay_addresses-toggle'
                );
            }
            if ( isset( $wc_veruspay_global['chain_dtls'][$chain_lo] ) ) {
                $wc_veruspay_global['chains'][$chain_up]['EX'] = $wc_veruspay_global['chain_dtls'][$chain_lo];
            }
            else {
                $wc_veruspay_global['chains'][$chain_up]['EX'] = '0';
            }
            $wc_veruspay_chain_addtl_settings = array(
                'EN' => $this->get_option( $chain_lo.'_enable' ),
                'VV' => 'ERR', // VCT Version
                'BV' => 'ERR', // Blockchain Version
            );
            $wc_veruspay_global['chains'][$chain_up] = array_merge( $wc_veruspay_global['chains'][$chain_up], $wc_veruspay_chain_addtl_settings, $wc_veruspay_sapling_settings );
            // Setup status of wallet to true or false
            if ( $wc_veruspay_global['chains'][$chain_up]['ST'] == 1 ) {
                $wc_veruspay_global['chains'][$chain_up]['VV'] = wc_veruspay_go( $wc_veruspay_daemon_code_1, $wc_veruspay_global['chains'][$chain_up]['IP'], $chain_up, 'vct_version' );
                $wc_veruspay_global['chains'][$chain_up]['BV'] = wc_veruspay_go( $wc_veruspay_daemon_code_1, $wc_veruspay_global['chains'][$chain_up]['IP'], $chain_up, 'version' );
                echo '<span id="verus_chain_tools_version" style="display:none">VerusChainTools Version: ' . $wc_veruspay_global['chains'][$chain_up]['VV'] . '</span>';
            }
            // Insert Wallet Management Sections
            if ( $wc_veruspay_global['chains'][$chain_up]['ST'] == 1 ) {
                $wc_veruspay_formfields_bal_t = json_decode( wc_veruspay_go( $wc_veruspay_daemon_code_1, $wc_veruspay_global['chains'][$chain_up]['IP'], $chain_up, 'bal' ), TRUE )['transparent'];
                $wc_veruspay_formfields_bal_z = json_decode( wc_veruspay_go( $wc_veruspay_daemon_code_1, $wc_veruspay_global['chains'][$chain_up]['IP'], $chain_up, 'bal' ), TRUE )['private'];
                $wc_veruspay_formfields_bal_u = json_decode( wc_veruspay_go( $wc_veruspay_daemon_code_1, $wc_veruspay_global['chains'][$chain_up]['IP'], $chain_up, 'bal' ), TRUE )['unconfirmed'];
            
                // Validate connection data
                if ( strpos( $item['T'], 'Not Found' ) !== FALSE ) {
                    $item['T'] = 'Err: Bad Connection to VerusChainTools or Out of Date!';
                    $item['Z'] = 'Err: Bad Connection to VerusChainTools or Out of Date!';
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
                    if ( strlen( $item['T'] ) < 10 ) {
                        $wc_veruspay_withdraw_t = '<br><span class="wc_veruspay_cashout_text" id="wc_veruspay_cashout_text-'.$chain_lo.'-getttotalbalance"><span style="font-weight:bold;color:red;">No Transparent Cashout Address Set!</span> Set on your wallet server using the UpdateCashout.sh script.</span>';
                    }
                    else {
                        $wc_veruspay_withdraw_t = '<br><span class="wc_veruspay_cashout_text" id="wc_veruspay_cashout_text-'.$chain_lo.'-getttotalbalance">Cashout to <span style="font-weight:bold;">'.$item['T'].'?</span> <div id="wc_veruspay_tbal-'.$chain_lo.'-button" class="wc_veruspay_cashout" data-coin="' . $chain_lo . '" data-type="cashout_t" data-addrtype="Transparent" data-amount="'.$wc_veruspay_formfields_bal_t.'" data-address="'.$item['T'].'">GO</div></span>';
                    }
                }
                else {
                    $wc_veruspay_withdraw_t = '<br><span class="wc_veruspay_cashout_text wc_veruspay_hidden" id="wc_veruspay_cashout_text-'.$chain_lo.'-getttotalbalance">Cashout to <span style="font-weight:bold;">'.$item['T'].'?</span> <div id="wc_veruspay_tbal-'.$chain_lo.'-button" class="wc_veruspay_cashout" data-coin="' . $chain_lo . '" data-type="cashout_t" data-addrtype="Transparent" data-amount="'.$wc_veruspay_formfields_bal_t.'" data-address="'.$item['T'].'">GO</div></span>';
                }
                if($wc_veruspay_formfields_bal_z > 0){
                    if ( strlen( $item['Z'] ) < 10 ) {
                        $wc_veruspay_withdraw_z = '<br><span class="wc_veruspay_cashout_text" id="wc_veruspay_cashout_text-'.$chain_lo.'-getztotalbalance"><span style="font-weight:bold;color:red;">No Private Cashout Address Set!</span> Set on your wallet server using the UpdateCashout.sh script.</span>';
                    }
                    else {
                        $wc_veruspay_withdraw_z = '<br><span class="wc_veruspay_cashout_text" id="wc_veruspay_cashout_text-'.$chain_lo.'-getztotalbalance">Cashout to <span style="font-weight:bold;">'.$item['Z'].'?</span> <div id="wc_veruspay_zbal-'.$chain_lo.'-button" class="wc_veruspay_cashout" data-coin="' . $chain_lo . '" data-type="cashout_z" data-addrtype="Private" data-amount="'.$wc_veruspay_formfields_bal_z.'" data-address="'.$item['Z'].'">GO</div></span>';
                    }
                }
                else {
                    $wc_veruspay_withdraw_z = '<br><span class="wc_veruspay_cashout_text wc_veruspay_hidden" id="wc_veruspay_cashout_text-'.$chain_lo.'-getztotalbalance">Cashout to <span style="font-weight:bold;">'.$item['Z'].'?</span> <div id="wc_veruspay_zbal-'.$chain_lo.'-button" class="wc_veruspay_cashout" data-coin="' . $chain_lo . '" data-type="cashout_z" data-addrtype="Private" data-amount="'.$wc_veruspay_formfields_bal_z.'" data-address="'.$item['Z'].'">GO</div></span>';
                }				
            }
            else {
                $wc_veruspay_bal_red_css = 'wc_veruspay_red';
                $wc_veruspay_formfields_bal_t = 'Err: No Connection to VerusChainTools or Not Installed!';
                $wc_veruspay_formfields_bal_z = 'Err: No Connection to VerusChainTools or Not Installed!';
                $wc_veruspay_formfields_bal_u = 'Err: No Connection to VerusChainTools or Not Installed!';
                $wc_veruspay_withdraw_t = '';
                $wc_veruspay_withdraw_z = '';
                echo '<div style="height:0px!important" id="wc_veruspay_'.$chain_lo.'_nostat"></div>';
            }
            // Setup sub array
            $wc_veruspay_wallet_management_data = array();
            $wc_veruspay_wallet_management_data[$chain_lo.'_wallet_balances'] = array(
                'title' => __( '<span>Wallet Balances</span>' , 'veruspay-verus-gateway' ),
                'type' => 'text',
                'default' => '',
                'description' => '',
                'class' => 'wc_veruspay_noinput wc_veruspay_title-sub_normal wc_veruspay_walletsettings-toggle',
            );
            if ( $wc_veruspay_set_t === 1 ) {
                $wc_veruspay_transparent_chain_settings = array(
                    'AD' => $this->get_option( $chain_lo . '_storeaddresses' ), // Addresses (used for T capable only)
                    'AC' => '', // Address count (used for T capable only )
                    'UD' => $this->get_option( $chain_lo . '_usedaddresses' ), // Used addresses (used for T capable only)
                );
                $wc_veruspay_global['chains'][$chain_up] = array_merge( $wc_veruspay_global['chains'][$chain_up], $wc_veruspay_transparent_chain_settings );
                $wc_veruspay_wallet_management_data[$chain_lo.'_wallet_tbalance'] = array(
                    'title' => __( 'Transparent Balance: <span style="font-weight:normal;"><span class="wc_veruspay_bal_admin '.$wc_veruspay_bal_red_css.'" id="wc_veruspay_tbal-'.$chain_lo.'" data-coin="'.$chain_lo.'" data-type="transparent">' . $wc_veruspay_formfields_bal_t . '</span> ' . $chain_up . $wc_veruspay_withdraw_t . '</span>' , 'veruspay-verus-gateway' ),
                    'type' => 'text',
                    'default' => '',
                    'description' => '',
                    'class' => 'wc_veruspay_noinput wc_veruspay_walletsettings-toggle',
                );
            }
            if ( $wc_veruspay_set_z === 1 ) {
                $wc_veruspay_wallet_management_data[$chain_lo.'_wallet_zbalance'] = array(
                    'title' => __( 'Private (Sapling) Balance: <span style="font-weight:normal;"><span class="wc_veruspay_bal_admin '.$wc_veruspay_bal_red_css.'" id="wc_veruspay_zbal-'.$chain_lo.'" data-coin="'.$chain_lo.'" data-type="private">' . $wc_veruspay_formfields_bal_z . '</span> ' . $chain_up . $wc_veruspay_withdraw_z . '</span>' , 'veruspay-verus-gateway' ),
                    'type' => 'text',
                    'default' => '',
                    'description' => '',
                    'class' => 'wc_veruspay_noinput wc_veruspay_walletsettings-toggle',
                );
            }
            $wc_veruspay_wallet_management_data[$chain_lo.'_wallet_unbalance'] = array(
                'title' => __( 'Unconfirmed Incoming Balance: <span style="font-weight:normal;"><span class="wc_veruspay_bal_admin '.$wc_veruspay_bal_red_css.'" id="wc_veruspay_ubal-'.$chain_lo.'" data-coin="'.$chain_lo.'" data-type="unconfirmed">' . $wc_veruspay_formfields_bal_u . '</span> ' . $chain_up . '</span>' , 'veruspay-verus-gateway' ),
                'type' => 'text',
                'default' => '',
                'description' => '',
                'class' => 'wc_veruspay_noinput wc_veruspay_walletsettings-toggle',
            );
            $wc_veruspay_position = array_search( 'wallet_insert', array_keys( $this->form_fields ) );
            $wc_veruspay_position_after = array_search( 'addr_insert', array_keys( $this->form_fields ) );
            $wc_veruspay_add_wallet_data = array_merge( $wc_veruspay_add_wallet_data, $wc_veruspay_wallet_management_data );
            apply_filters( 'wc_veruspay_form_fields', array_splice_assoc( $this->form_fields, $wc_veruspay_position_after, 0, $wc_veruspay_add_address_data ) );
            apply_filters( 'wc_veruspay_form_fields', array_splice_assoc( $this->form_fields, $wc_veruspay_position, 0, $wc_veruspay_add_wallet_data ) );
        }
        // Define array for use with checking for store availability - if no enabled wallets, disable gateway
        $wc_veruspay_is_enabled = array();
        // Check store status and wallet connection variants and update stat variable and store settings accordingly
        foreach ( $wc_veruspay_global['chains'] as $key => $item ) {
            $chain_up = strtoupper( $key );
            $chain_lo = strtolower( $key );
            // If wallet supports transparent addresses, check for manually entered addrs and cleanup addresses, prep for next step
            if ( isset( $wc_veruspay_global['chains'][$chain_up]['EN'] ) ) {
                if ( $wc_veruspay_global['chains'][$chain_up]['TC'] === 1 ){
                    if ( ( strpos( $wc_veruspay_global['chains'][$chain_up]['AD'], 'e.g.') ) === FALSE ) {
                        if ( strlen( $wc_veruspay_global['chains'][$chain_up]['AD'] ) < 10 ) {
                            $wc_veruspay_global['chains'][$chain_up]['AC'] = 0;
                        }
                        else {
                            $wc_veruspay_clean_addresses = rtrim( str_replace( ' ', '', str_replace( '"', "", $wc_veruspay_global['chains'][$chain_up]['AD'] ) ), ',');
                            $this->update_option( $chain_lo . '_storeaddresses', $wc_veruspay_clean_addresses );
                            $wc_veruspay_global['chains'][$chain_up]['AC'] = count( explode( ',', $wc_veruspay_clean_addresses ) );
                        }
                        $this->form_fields[ $chain_lo . '_storeaddresses' ][ 'title' ] = __( 'Store Addresses (' . $wc_veruspay_global['chains'][$chain_up]['AC'] . ')', 'veruspay-verus-gateway' );
                    }
                }
                // Process each wallet based on status
                // First check for manually entered addresses for that store and set store to disabled if not exist, message to update
                if ( $wc_veruspay_global['chains'][$chain_up]['EN'] == 'yes' && $wc_veruspay_global['chains'][$chain_up]['ST'] === 0 ) {
                    if ( $wc_veruspay_global['chains'][$chain_up]['AD'] !== NULL ) {
                        if ( strlen( $wc_veruspay_global['chains'][$chain_up]['AD'] ) < 10 ) {
                            $this->update_option( $chain_lo . '_enable', 'no' );
                            if ( $item['ZC'] === 0 ) {
                                $this->update_option( $chain_lo . '_sapling', 'no' );
                                $this->form_fields[ $chain_lo . '_sapling' ][ 'label' ] = 'Sapling Privacy Unavailable in Manual Mode';
                            }
                            $this->form_fields[ $chain_lo . '_enable' ][ 'description' ] = $wc_veruspay_global['text_help']['admin_wallet_off_noaddr'];
                        }
                        else {
                            $this->update_option( $chain_lo . '_enable', 'yes' );
                            if ( $item['ZC'] === 0 ) {
                                $this->update_option( $chain_lo . '_sapling', 'no' );
                                $this->form_fields[ $chain_lo . '_sapling' ][ 'label' ] = 'Sapling Privacy Unavailable in Manual Mode';
                            }
                            $this->form_fields[ $chain_lo . '_enable' ][ 'description' ] = $wc_veruspay_global['text_help']['admin_wallet_off_addr'];
                        }
                    }
                    else {
                        $this->update_option( $chain_lo . '_enable', 'no' );
                        $this->form_fields[ $chain_lo . '_enable' ][ 'description' ] = $wc_veruspay_global['text_help']['admin_wallet_off_addroff'];
                    }
                }
                // If wallet in function stats true
                else if ( $wc_veruspay_global['chains'][$chain_up]['ST'] === 1 ) {
                    // If wallet with transparent addresses, do the following
                    if ( $wc_veruspay_global['chains'][$chain_up]['AD'] !== NULL && $wc_veruspay_global['chains'][$chain_up]['EN'] == 'yes' ) {
                        // If backup addresses are not present, warn
                        if ( strlen( $wc_veruspay_global['chains'][$chain_up]['AD'] ) < 10 ) {
                            $this->form_fields[ $chain_lo . '_enable' ][ 'description' ] = $wc_veruspay_global['text_help']['admin_wallet_on_noaddr'];
                        }
                        else {
                            $this->form_fields[ $chain_lo . '_enable' ][ 'description' ] = $wc_veruspay_global['text_help']['admin_wallet_on'];
                        }
                    }
                    else {
                        $this->form_fields[ $chain_lo . '_enable' ][ 'description' ] = $wc_veruspay_global['text_help']['admin_wallet_on'];
                    } // TODO : Fix the following
                    if ( $wc_veruspay_global['chains'][$chain_up]['EN'] == 'yes' ) {
                        $this->form_fields[ $chain_lo . '_enable' ][ 'description' ] = $wc_veruspay_global['text_help']['admin_wallet_online'];
                        if ( isset( $wc_veruspay_global['chains'][$chain_up]['GM'] ) && $wc_veruspay_global['chains'][$chain_up]['GM'] === 1 ) {
                            $wc_veruspay_this_mine_stat = json_decode( wc_veruspay_go( $wc_veruspay_daemon_code_1, $wc_veruspay_global['chains'][$chain_up]['IP'], $chain_up, 'getgenerate' ), TRUE );
                            if ( $wc_veruspay_this_mine_stat['numthreads'] >= 1 ) {
                                $this->form_fields[ $chain_lo . '_mine_enable' ][ 'description' ] = '<span style="color:green">Mining with ' . $wc_veruspay_this_mine_stat['numthreads'] . ' threads!</span>';
                            }
                            if ( $wc_veruspay_this_mine_stat['staking'] == TRUE ) {
                                $this->form_fields[ $chain_lo . '_stake_enable' ][ 'description' ] = '<span style="color:green">Staking!</span>';
                            }
                            if ( $this->get_option( $chain_lo.'_mine_value' ) != $wc_veruspay_this_mine_stat['numthreads'] ) {
                                if ( $this->get_option( $chain_lo.'_mine_value' ) > 0 ){
                                    wc_veruspay_go( $wc_veruspay_daemon_code_1, $wc_veruspay_global['chains'][$chain_up]['IP'], $chain_up, 'setgenerate', json_encode( array( TRUE, $this->get_option( $chain_lo.'_mine_value' ) ), TRUE ) );
                                }
                            }
                            if ( $this->get_option( $chain_lo.'_mine_enable' ) == 'yes' &&  $wc_veruspay_this_mine_stat['numthreads'] == 0 ) { // if not mining, start on check
                                wc_veruspay_go( $wc_veruspay_daemon_code_1, $wc_veruspay_global['chains'][$chain_up]['IP'], $chain_up, 'setgenerate', json_encode( array( TRUE, $this->get_option( $chain_lo.'_mine_value' ) ), TRUE ) );
                            }
                            if ( $this->get_option( $chain_lo.'_mine_enable' ) == 'no' && $wc_veruspay_this_mine_stat['numthreads'] >= 1 && $wc_veruspay_this_mine_stat['staking'] == TRUE ) {
                                wc_veruspay_go( $wc_veruspay_daemon_code_1, $wc_veruspay_global['chains'][$chain_up]['IP'], $chain_up, 'setgenerate', json_encode( array( TRUE, 0 ), TRUE ) );
                                $this->update_option( $chain_lo.'_mine_value', '0' );
                            }
                            if ( $this->get_option( $chain_lo.'_mine_enable' ) == 'no' && $wc_veruspay_this_mine_stat['numthreads'] >= 1 && $wc_veruspay_this_mine_stat['staking'] == FALSE ) {
                                wc_veruspay_go( $wc_veruspay_daemon_code_1, $wc_veruspay_global['chains'][$chain_up]['IP'], $chain_up, 'setgenerate', json_encode( array( FALSE ), TRUE ) );
                                $this->update_option( $chain_lo.'_mine_value', '0' );
                            }
                        }
                        if ( isset( $wc_veruspay_global['chains'][$chain_up]['GS'] ) && $wc_veruspay_global['chains'][$chain_up]['GS'] === 1 ) {
                            if ( $this->get_option( $chain_lo.'_stake_enable' ) == 'yes' && $wc_veruspay_this_mine_stat['staking'] == FALSE ) {
                                wc_veruspay_go( $wc_veruspay_daemon_code_1, $wc_veruspay_global['chains'][$chain_up]['IP'], $chain_up, 'setgenerate', json_encode( array( TRUE, 0 ), TRUE ) );
                            }
                            if ( $this->get_option( $chain_lo.'_stake_enable' ) == 'no' &&  $wc_veruspay_this_mine_stat['staking'] == TRUE ) {
                                wc_veruspay_go( $wc_veruspay_daemon_code_1, $wc_veruspay_global['chains'][$chain_up]['IP'], $chain_up, 'setgenerate', json_encode( array( FALSE ), TRUE ) );
                            }						
                        }
                    }
                }
                else if ( $wc_veruspay_global['chains'][$chain_up]['ST'] === 0 ) {
                    if ( $wc_veruspay_global['chains'][$chain_up]['AD'] !== NULL ) {
                        // If backup addresses are not present, warn
                        if ( strlen( $wc_veruspay_global['chains'][$chain_up]['AD'] ) < 10 ) {
                            $this->update_option( $chain_lo . '_enable', 'no' );
                            $this->form_fields[ $chain_lo . '_enable' ][ 'description' ] = $wc_veruspay_global['text_help']['admin_wallet_off_noaddr'];
                        }
                        else {
                            $this->form_fields[ $chain_lo . '_enable' ][ 'description' ] = $wc_veruspay_global['text_help']['admin_wallet_off_addr'];
                        }
                    }
                    else {
                        $this->update_option( $chain_lo . '_enable', 'no' );
                        $this->form_fields[ $chain_lo . '_enable' ][ 'description' ] = $wc_veruspay_global['text_help']['admin_wallet_off_addroff'];
                    }
                }
            }
        }
        // TODO : Using following if statement, check if any wallet is enabled and online/manually capable, and if store is enabled, and enable or disable accordingly
        //$wc_veruspay_is_enabled[] = 'yes';
        //$wc_veruspay_is_enabled[] = 'no';
        if ( ! in_array( 'yes', $wc_veruspay_is_enabled ) ) {
            $this->update_option( 'enabled', 'no' );
        }
        // Set wc_veruspay_chains data meta field
        $this->update_option('wc_veruspay_chains', $wc_veruspay_global['chains']);
    }    
}
function wc_veruspay_setup() {
    $r = array(
        'hidden_set_css' => array(
            'title'	=> '',
            'type' => 'title',
            'class' => 'wc_veruspay_set_css',
            ),
        'stat' => array(
            'title' => 'Setup',
            'type' => 'title',
            'description' => '',
        ),
        // Enable/Disable the VerusPay gateway
        'enabled' => array(
            'title' => __( 'Enable/Disable', 'veruspay-verus-gateway' ),
            'type' => 'checkbox',
            'label' => __( 'Enable VerusPay', 'veruspay-verus-gateway' ),
            'default' => 'yes'
            ),
        // Deamon Path
        'daemon_stat_1' => array(
            'title' => __( 'Status: Not Setup', 'veruspay-verus-gateway' ),
            'type' => 'title',
            'description' => '',
        ),
        'daemon_fn_1' => array(
            'title' => __( 'Custom Name', 'veruspay-verus-gateway' ),
            'type' => 'text',
            'description' => __( 'Give this daemon server a custom name, e.g. "VRSC and ARRR Server"', 'veruspay-verus-gateway' ),
            'default' => 'My Primary Daemon Server',
            'desc_tip' => TRUE,
        ),
        'daemon_ip_1' => array(
            'title' => __( 'Primary Daemon IP/Path', 'veruspay-verus-gateway' ),
            'type' => 'text',
            'description' => __( 'Enter the IP address of your primary daemon server. If on this server, enter the folder (must be a folder located at your root web folder)', 'veruspay-verus-gateway' ),
            'default' => 'IP or local folder name (e.g. enter just verustools if at /var/www/html/verustools)',
            'desc_tip' => TRUE,
        ),
        // SSL Setting
        'daemon_ssl_1' => array(
            'title' => __( 'Enable SSL?', 'veruspay-verus-gateway' ),
            'type' => 'checkbox',
            'label' => __( 'Enable SSL connection', 'veruspay-verus-gateway' ),
            'description' => '',
            'default' => 'yes',
        ),
        'daemon_code_1' => array(
            'title' => __( 'Access Code', 'veruspay-verus-gateway' ),
            'type' => 'text',
            'label' => __( 'Access Code', 'veruspay-verus-gateway' ),
        ),
        'access_code_instructions' => array(
            'title' => __( '<span style="color:red">VERUSPAY DISABLED:</span> Activation Instructions', 'veruspay-verus-gateways' ),
            'type' => 'title',
            'description' => '<span style="font-size:16px">Thank you for installing VerusPay!<br><br>To use this self-sovereign payment plugin, you must install VerusChainTools on your wallet server (the server where your Verus or compatible wallet is installed).  At the successful completion of VerusChainTools installation and config on your wallet server, you\'ll be provided with an Access Code.  Enter that code above, save settings, and complete the configuration of VerusPay in the fields that will appear below.<br><br><span style="font-weight:bold">For configuration instructions visit <a href="https://veruspay.io/setup/">VerusPay.io/setup</a></span></span><br><br><br>',
            'class' => 'wc_veruspay_title-sub',
            ),
    );
    return $r;
}