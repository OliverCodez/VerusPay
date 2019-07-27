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
/**
 * Go VCT!
 * 
 * Function to access VCT API using wordpress built-in wp_remote_post
 */
function wc_veruspay_go( $wc_veruspay_accesscode, $url, $chain, $method, $params = NULL ){
    $body = array(
        'a' => $wc_veruspay_accesscode,
        'c' => $chain,
        'm' => $method,
        'p' => $params, // Passed as json_encode string of correct array layout of parameters in question
    );
    // Pass method and params to VCT API and get return
    $response = wp_remote_post( $url, array(
		'sslverify' => FALSE,
		'headers'     => array( 'Content-Type' => 'application/json', 'Accept' => 'application/json' ),
    	'body'        => json_encode( $body ),
    	'method'      => 'POST',
    	'data_format' => 'body',
		'blocking' => TRUE,
	    'timeout' => 120,
    	));
    // Handle return
    // TODO : Test error and success outputs
    if ( is_wp_error( $response ) ) {
        $error_message = $response->get_error_message();
        if ( strpos( $error_message, 'Connection refused' ) !== FALSE ) {
            return 0;
        }
        else {
            return $error_message;
        }
    }
    else {
        $data = json_decode( $response['body'], TRUE );
        if ( isset( $data['error'] ) ) {
	        return $data['error']['code'];
        }
        else if ( !isset( $data['result'] ) ) {
            return 'ERR: ' . $wc_veruspay_global['text_help']['vct_0'];
        }
        else if ( $data['result'] === 'error' ) {
            return 'ERR: ' . $data['return'];
        }
        else if ( $data['result'] === 'success' ) {
            return $data['return'];
        }
		else {
			return 'ERR: ' . $wc_veruspay_global['text_help']['vct_0'];
		}
    }
}
/**
 * Primary data and method function for external apis
 */
function wc_veruspay_get( $chain, $method, $params = NULL ) {
    global $wc_veruspay_global;
    $cl = strtolower( $chain );
    if( !array_key_exists( $cl, $wc_veruspay_global['chain_list'] ) ) {
        return strtoupper( $chain ) . $wc_veruspay_global['text_help']['vct_2'];
    }
    switch ( $method ) {
        case 'getbalance':
        if ( ! isset( $params ) ) {
            return $wc_veruspay_global['text_help']['vct_3'];
        }
        else {
            $results = json_decode( wc_veruspay_wp_get_curl( $wc_veruspay_global['chain_dtls'][$cl]['getaddress'] . $params ), true );
            $results = $results['balance'];
            return $results;
        }
        break;
        case 'lowestconfirm':
        if ( ! isset( $params ) ) {
            return $wc_veruspay_global['text_help']['vct_3'];
        }
        else {
            $results = json_decode( wc_veruspay_wp_get_curl( $wc_veruspay_global['chain_dtls'][$cl]['getaddress'] . $params ), true );
            $results = $results[ $wc_veruspay_global['chain_dtls'][$cl]['txname'] ];
            $wc_veruspay_confirmations = array();
            foreach ( $results as $item ) {
                usleep(500);
                if ( $wc_veruspay_global['chain_dtls'][$cl]['txlevel'] === null ) {
                    $txhash = $item;
                }
                else {
                    $txhash = $item[ $wc_veruspay_global['chain_dtls'][$cl]['txlevel'] ];
                }
                $txresults = json_decode( wc_veruspay_wp_get_curl( $wc_veruspay_global['chain_dtls'][$cl]['gettx'] . $txhash . $wc_veruspay_global['chain_dtls'][$cl]['txtrail'] ), true );
                if ( $txresults[$wc_veruspay_global['chain_dtls'][$cl]['htname']] > 1 ) {
                    $wc_veruspay_confirmations[$txhash] = $txresults['confirmations'];
                }
            }
            return min($wc_veruspay_confirmations);
        }
        break;
        case 'getrawtx':
        if ( ! isset( $params ) ) {
            return $wc_veruspay_global['text_help']['vct_3'];
        }
        else {
            $results = json_decode( wc_veruspay_wp_get_curl( $wc_veruspay_global['chain_dtls'][$cl]['gettx'] . $params . $wc_veruspay_global['chain_dtls'][$cl]['txtrail'] ), true );
            return $results['confirmations'];
        }
        break;
        case 'getblockcount':
            $results = json_decode( wc_veruspay_wp_get_curl( $wc_veruspay_global['chain_dtls'][$cl]['info'] ), true );
            return $results['info'][$wc_veruspay_global['chain_dtls'][$cl]['blocks']];
        break;
        case 'getdifficulty':
            $results = json_decode( wc_veruspay_wp_get_curl( $wc_veruspay_global['chain_dtls'][$cl]['info'] ), true );
            return $results['info'][$wc_veruspay_global['chain_dtls'][$cl]['difficulty']];
        break;
        case 'getsupply':
            return wc_veruspay_wp_get_curl( $wc_veruspay_global['chain_dtls'][$cl]['supply'] );
    }
}

/**
 * Real-time Price Data. Supported coins: VRSC, ARRR, KMD, ZEC
 */
function wc_veruspay_price( $chain, $currency ) {
    global $wc_veruspay_global;
    $currency = strtoupper($currency);
    $chain = strtoupper($chain);
    if ( !isset( $chain ) ) {
        $chain = 'VRSC';
    }
    return wc_veruspay_wp_get_curl( $wc_veruspay_global['chain_dtls']['fiat']['api'] . '?currency=' . $currency . '&ticker=' . $chain );
}
/**
 * Create QR Code using the explorer
 */
function wc_veruspay_qr( $qraddress, $size ) {
    $alt_text = 'Send VRSC to ' . $qraddress;
    return "\n" . '<img src="https://veruspay.io/qr/?size=12&address=' . $qraddress . '" alt="' . $alt_text . '" />' . "\n";
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