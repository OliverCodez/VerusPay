<?php
/**
 * Verus PHP Tools - Functions
 *
 * @category Cryptocurrency
 * @package  VerusPHPTools
 * @author   J Oliver Westbrook <johnwestbrook@pm.me>
 * @copyright Copyright (c) 2019, John Oliver Westbrook
 * @link     https://github.com/joliverwestbrook/VerusPHPTools
 * 
 * MODIFIED: This script has been modified, disallowing indirect or ajax access to protect wallet for use within VerusPay and utilizing wp_remote_post for cURL
 * ====================
 * 
 * The MIT License (MIT)
 * 
 * Copyright (c) 2019 John Oliver Westbrook
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 * ====================
 * 
 *  Available Blockchain Functions - Requires "Verus Chain Tools" v0.2.0+ configured on wallet server:
 * 
 *      Get New Transparent Address:                    wc_veruspay_go( 'getnewaddress', null, null );
 *      Get New ZS Sapling Address:                     wc_veruspay_go( 'getnewsapling', null, null );
 *      Get Balance:                                    wc_veruspay_go( 'getbalance', 'wallet_address', null );
 *      Check Confs-to-Min-Count:                       wc_veruspay_go( 'lowestconfirm', 'wallet_address', 'min_confirm_number' );
 *      Get Current Block Height:                       wc_veruspay_go( 'getblockcount', null, null );
 *      Count Transparent Addresses in Wallet:          wc_veruspay_go( 'countaddresses', '""', null ); (if wallet is named, provide in place of "")
 *      Count ZS Sapling Addresses in Wallet:           wc_veruspay_go( 'countzaddresses', null, null );
 *      List Transparent Addresses in Wallet:           wc_veruspay_go( 'listaddresses', '""', null ); (if wallet is named, provide in place of "")
 *      List ZS Sapling Addresses in Wallet:            wc_veruspay_go( 'listzaddresses', null, null );
 *      Get Total Amount Received by Address:           wc_veruspay_go( 'totalreceivedby', 'wallet_address', null );
 *      Get Total Transparent Balance:                  wc_veruspay_go( 'getttotalbalance', null, null );
 *      Get Total Unconfirmed Balance:                  wc_veruspay_go( 'getunconfirmedbalance', null, null );
 *      Get Total ZS Sapling Balance:                   wc_veruspay_go( 'getztotalbalance', null, null );
 *      Get Total Wallet Balance:                       wc_veruspay_go( 'gettotalbalance', null, null );
 *      Show config set T-Cashout address:              wc_veruspay_go( 'show_taddr', null, null );
 *      Show config set Z-Cashout address:              wc_veruspay_go( 'show_zaddr', null, null );
 *      Cashout all T wallets to T-Cashout address:     wc_veruspay_go( 'cashout_t', null, null );
 *      Cashout all T wallets to Z-Cashout address:     wc_veruspay_go( 'cashout_z', null, null );
 * 
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
    'kmd_supply' => null,
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
function wc_veruspay_stat( $wc_veruspay_wallet, $coin ) {
    // Create new wallet connection to compatible Daemon
    $url = $wc_veruspay_wallet[ 'ip' ];
    // Get response data
    $response = wp_remote_post( $url, array(
        'sslverify' => false,
        'headers'     => array( 'Content-Type' => 'application/json; charset=utf-8' ),
        'body' => array(
                    'exec' => 'test',
                    'coin' => $coin
                ),
        'method' => 'GET',
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
function wc_veruspay_go( $wc_veruspay_wallet, $coin, $exec, $hash, $amt ){
    $url = $wc_veruspay_wallet[ 'ip' ];
    $body = array(
        'coin' => $coin,
        'exec' => $exec,
        'hash' => $hash,
        'amt' => $amt,
    );
    // Get blockchain data from method and params
    $response = wp_remote_post( $url, array(
        'sslverify' => false,
        'headers' => array( 'Content-Type' => 'application/json; charset=utf-8' ),
	    'body' => $body,
	    'method' => 'GET',
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