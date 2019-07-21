<?php
/**
 * Blockchain Functions (Methods) Available to VerusPay
 * 
 * Following is a list of all whitelisted methods and custom methods 
 * defined for VerusPay within VerusChainTools API (VCT) v0.4.0+. 
 * The VCT API must be configured and running on the wallet server 
 * you will use in conjuction with this installation of VerusPay.
 * To learn more, visit: https://github.com/joliverwestbrook/VerusChainTools
 * 
 * ===================
 * =   WHITELISTED   =
 * ===================
 *  getinfo
 *  setgenerate
 *  getgenerate
 *  getnewaddress
 *  z_getnewaddress
 *  z_getbalance
 *  getunconfirmedbalance
 *  getaddressesbyaccount
 *  z_listaddresses
 *  getreceivedbyaddress
 * 
 * +++++++++++++++++++
 * ++ NEW For PBaaS ++
 * +++++++++++++++++++
 *  definechain
 *  getchaindefinition
 *  getdefinedchains
 * 
 * ===================
 * =     CUSTOM      =
 * ===================
 *  test        returns status of blockchain daemon
 *  type        returns an integer representing the type of transactions capable by the blockchain (0 = transparent + private, 1 = transparent only, 2 = private only)
 *  version     returns version # of blockchain daemon
 *  lowest      returns the lowest confirm for the given address
 *  t_count     returns the number of T addresses created
 *  z_count     returns the number of Z addresses created
 *  bal         returns the balance of each address created and totals for Transparent, Interest, Private and total wallet
 *  show_taddr  returns the Cashout Transparent address set by the store owner
 *  show_zaddr  returns the Cashout Private address set by the store owner
 *  cashout_t   sends the total transparent balance, minus tx fee of 0.0001, to the Cashout Transparent address
 *  cashout_z   sends the total private balance, minus tx fee of 0.0001 per address, to the Cashout Private address
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wc_veruspay_phpextconfig;
$wc_veruspay_phpextconfig = array(
    // VRSC Iquidus Explorer Support
	'vrsc_address' => 'https://explorer.veruscoin.io/address/',
	'vrsc_getaddress' => 'https://explorer.veruscoin.io/ext/getaddress/',
	'vrsc_gettx' => 'https://explorer.veruscoin.io/api/getrawtransaction?txid=',
	'vrsc_txname' => 'last_txs',
	'vrsc_txlevel' => 'addresses',
    'vrsc_txtrail' => '&decrypt=1',
    'vrsc_htname' => 'height',
    'vrsc_info' => 'https://vrsc.explorer.dexstats.info/insight-api-komodo/status?q=getinfo',
    'vrsc_blocks' => 'blocks',
	'vrsc_difficulty' => 'difficulty',
    'vrsc_supply' => 'https://explorer.veruscoin.io/ext/getmoneysupply',
    // KMD Insight Explorer Support
	'kmd_address' => 'https://kmd.explorer.dexstats.info/address/',
	'kmd_getaddress' => 'https://kmd.explorer.dexstats.info/insight-api-komodo/addr/',
	'kmd_gettx' => 'https://kmd.explorer.dexstats.info/insight-api-komodo/tx/',
	'kmd_txname' => 'transactions',
	'kmd_txlevel' => null,
    'kmd_txtrail' => null,
    'kmd_htname' => 'blockheight',
    'kmd_info' => 'https://kmd.explorer.dexstats.info/insight-api-komodo/status?q=getinfo',
    'kmd_blocks' => 'blocks',
	'kmd_difficulty' => 'difficulty',
    'kmd_supply' => 'https://kmd.explorer.dexstats.info/api/supply',
    // ZEC Insight Explorer Support
    'zec_address' => 'https://zcash.blockexplorer.com/address/',
	'zec_getaddress' => 'https://zcash.blockexplorer.com/api/addr/',
	'zec_gettx' => 'https://zcash.blockexplorer.com/api/tx/',
	'zec_txname' => 'transactions',
	'zec_txlevel' => null,
    'zec_txtrail' => null,
    'zec_htname' => 'blockheight',
    'zec_info' => 'https://zcash.blockexplorer.com/api/status?q=getinfo',
	'zec_blocks' => 'blocks',
	'zec_difficulty' => 'difficulty',
    'zec_supply' => null,
    // Fiat API Data
	'fiat_api'  => 'https://veruspay.io/api/',
	'currency'  => 'USD',
);
/**
 * Test wallet connection - Return status, 0 means not running, anything else is running even if errors occur.
 */
function wc_veruspay_stat( $wc_veruspay_accesscode, $wc_veruspay_wallet, $coin ) {
    // Create new wallet connection to compatible Daemon
    $url = $wc_veruspay_wallet[ 'ip' ];
    // Get response data
    $response = wp_remote_post( $url, array(
        'sslverify' => false,
        'headers'     => array(),
        'body' => array(
                    'access' => $wc_veruspay_accesscode,
                    'exec' => 'test',
                    'coin' => $coin
                ),
        'method' => 'POST',
	    'timeout' => 120,
    ));
    // Handle response and any errors
    if ( is_wp_error( $response ) ) {
        $error_message = $response->get_error_message();
        if ( strpos( $error_message, 'Connection refused' ) !== false ) {
            return 0;
        }
        else {
            return $error_message;
        }
    } else {
        if ( json_decode($response['response']['code'], true) == '200' ){
            return json_decode($response['body'], true);
        }
        else {
            return 0;
        }
    }
}
// Function to access blockchain using wordpress built-in wp_remote_post 
function wc_veruspay_go( $wc_veruspay_accesscode, $wc_veruspay_wallet, $coin, $exec, $hash, $amt ){
    $url = $wc_veruspay_wallet[ 'ip' ];
    $body = array(
        'access' => $wc_veruspay_accesscode,
        'coin' => $coin,
        'exec' => $exec,
        'hash' => $hash,
        'amt' => $amt,
    );
    // Get blockchain data from method and params
    $response = wp_remote_post( $url, array(
        'sslverify' => false,
        'headers' => array(),
	    'body' => $body,
	    'method' => 'POST',
	    'timeout' => 120,
	) );
    // Handle response and any errors
    if ( is_wp_error( $response ) ) {
        $error_message = $response->get_error_message();
        if ( strpos( $error_message, 'Connection refused' ) !== false ) {
            return 0;
        }
        else {
            return $error_message;
        }
    }
    else {
        $data = json_decode($response['body'], true);
        if ( $data['error'] !== null ) {
	        return $data['error']['code'];
        }
        else {
            return $response['body'];
        }
    }
}
/**
 * Primary data and exec function for external apis
 */
function wc_veruspay_get( $coin, $command, $hash, $amt ) {
    global $wc_veruspay_phpextconfig;

// Execute commands availabel for to interact with compatible Daemon
    switch ( $command ) {
        case 'getbalance':
        if ( ! isset( $hash ) ) {
            return "Error 2 - Hash Call";
        }
        else {
            $results = json_decode( wc_veruspay_wp_get_curl( $wc_veruspay_phpextconfig[ strtolower($coin).'_getaddress' ] . $hash ), true );
            $results = $results['balance'];
            return $results;
        }
        break;
        case 'lowestconfirm':
        if ( ! isset( $hash ) ) {
            return "Error 2 - Hash";
        }
        else {
            $results = json_decode( wc_veruspay_wp_get_curl( $wc_veruspay_phpextconfig[strtolower($coin).'_getaddress'] . $hash ), true );
            $results = $results[ $wc_veruspay_phpextconfig[strtolower($coin).'_txname'] ];
            $wc_veruspay_confirmations = array();
            foreach ( $results as $item ) {
                usleep(500);
                if ( $wc_veruspay_phpextconfig[strtolower($coin).'_txlevel'] === null ) {
                    $txhash = $item;
                }
                else {
                    $txhash = $item[ $wc_veruspay_phpextconfig[strtolower($coin).'_txlevel'] ];
                }
                $txresults = json_decode( wc_veruspay_wp_get_curl( $wc_veruspay_phpextconfig[strtolower($coin).'_gettx'] . $txhash . $wc_veruspay_phpextconfig[strtolower($coin).'_txtrail'] ), true );
                if ( $txresults[$wc_veruspay_phpextconfig[strtolower($coin).'_htname']] > 1 ) {
                    $wc_veruspay_confirmations[$txhash] = $txresults['confirmations'];
                }
            }
            return min($wc_veruspay_confirmations);
        }
        break;
        case 'getrawtx':
        if ( ! isset( $hash ) ) {
            return "Error 2 - Hash Call";
        }
        else {
            $results = json_decode( wc_veruspay_wp_get_curl( $wc_veruspay_phpextconfig[strtolower($coin).'_gettx'] . $hash . $wc_veruspay_phpextconfig[strtolower($coin).'_txtrail'] ), true );
            return $results['confirmations'];
        }
        break;
        case 'getblockcount':
            $results = json_decode( wc_veruspay_wp_get_curl( $wc_veruspay_phpextconfig[strtolower($coin).'_info'] ), true );
            return $results['info'][$wc_veruspay_phpextconfig[strtolower($coin).'_blocks']];

        break;
        case 'getdifficulty':
            $results = json_decode( wc_veruspay_wp_get_curl( $wc_veruspay_phpextconfig[strtolower($coin).'_info'] ), true );
            return $results['info'][$wc_veruspay_phpextconfig[strtolower($coin).'_difficulty']];
        break;
        case 'getsupply':
            return wc_veruspay_wp_get_curl( $wc_veruspay_phpextconfig[strtolower($coin).'_supply'] );
    }
}

/**
 * Real-time Price Data. Supported coins: VRSC, ARRR, KMD, ZEC
 */
function wc_veruspay_price( $coin, $currency ) {
    global $wc_veruspay_phpextconfig;
    $currency = strtoupper($currency);
    $coin = strtoupper($coin);
    if ( !isset( $coin ) ) {
        $coin = 'VRSC';
    }
    return wc_veruspay_wp_get_curl( $wc_veruspay_phpextconfig['fiat_api'] . '?currency=' . $currency . '&ticker=' . $coin );
}
/**
 * Create QR Code using the explorer
 */
function wc_veruspay_qr( $qraddress, $size ) {
    $alt_text = 'Send VRSC to ' . $qraddress;
    return "\n" . '<img src="https://chart.googleapis.com/chart?chld=H|2&chs='.$size.'x'.$size.'&cht=qr&chl=' . $qraddress . '" alt="' . $alt_text . '" />' . "\n";
}
/**
 * Function for wp_remote_get CURL calls
 */
function wc_veruspay_wp_get_curl( $url ) {
    // Get response data
    $response = wp_remote_get( $url, array(
        'headers'     => array( 'Content-Type' => 'application/json; charset=utf-8' ),
        'method' => 'GET',
	    'timeout' => 120,
        'httpversion' => '1.1',
    ));
    // Handle response and any errors
    if ( is_wp_error( $response ) ) {
        $error_message = $response->get_error_message();
        return $error_message;
    }
    else {
        return $response['body'];
    }
}