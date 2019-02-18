<?php
/**
 * Verus PHP Ext Tools - Functions
 *
 * @category Cryptocurrency
 * @package  VerusPHPExtTools (External Only)
 * @author   J Oliver Westbrook <johnwestbrook@pm.me>
 * @copyright Copyright (c) 2019, John Oliver Westbrook
 * @link     https://github.com/joliverwestbrook/VerusPHPTools
 * 
 * This is a stripped version of VerusPHPTools, modified to include only external / non-rpc functions
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
 */
$phpextconfig = array(
    'explorer'  => 'https://explorer.veruscoin.io',
    'fiat_api'  => 'https://veruspay.io/api/',
    'currency'  => 'USD'
);
$curl_requests = 0;
$default_app_title = 'Verus PHP Ext Tools';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	$extmethod = true;
    $exec = $_GET[ 'exec' ];
    $hash = $_GET[ 'hash' ];
    $amt  = $_GET[ 'amt' ];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$extmethod = true;
    $exec = $_POST[ 'exec' ];
    $hash = $_POST[ 'hash' ];
    $amt  = $_POST[ 'amt' ];
}

if ( $extmethod === true ) {
    if ( isset( $exec ) ) {
        if ( $exec == 'price' ) {
            if ( ! isset( $hash ) ) {
                print verusPrice( $phpextconfig['currency'] );
            }
            else {
                print verusPrice( $hash );
            }
        }
        else if ( $exec == 'currencies' ) {
            print verusPrice( $exec );
        }
        else {
            print getVerus( $exec, $hash, $amt );
        }
    }
}
/**
 * Primary data and exec function
 */
function getVerus( $command, $hash, $amt ) {
    global $phpextconfig;
    global $curl_requests;

    $verusexp_curl = curl_init();
    
// Execute commands availabel for to interact with Verus Daemon
    switch ( $command ) {
        case 'getbalance':
        if ( ! isset( $hash ) ) {
            return "Error 2 - Hash Call";
        }
        else {
            return curlRequest( $phpextconfig['explorer'] . '/ext/getbalance/' . $hash, $verusexp_curl );
        }
        break;
        case 'lowestconfirm':
        if ( ! isset( $hash ) ) {
            return "Error 2 - Hash";
        }
        else {
            $results = json_decode( curlRequest( $phpextconfig['explorer'] . '/ext/getaddress/' . $hash, $verusexp_curl, true ), true );
            $results = $results['last_txs'];
            $wc_veruspay_confirmations = array();
            foreach ( $results as $item ) {
                $r = json_decode( curlRequest( $phpextconfig['explorer'] . '/api/getrawtransaction?txid=' . $item['addresses'] . '&decrypt=1', $verusexp_curl, true ), true );
                array_push($wc_veruspay_confirmations,$r['confirmations']);
            }
            return min($wc_veruspay_confirmations);
        }
        break;
        case 'getrawtx':
        if ( ! isset( $hash ) ) {
            return "Error 2 - Hash Call";
        }
        else {
            $results = json_decode( curlRequest( $phpextconfig['explorer'] . '/api/getrawtransaction?txid=' . $hash . '&decrypt=1', $verusexp_curl, true ), true );
            return $results['confirmations'];
        }
        break;
        case 'getblockcount':
            return curlRequest( $phpextconfig['explorer'] . '/api/getblockcount', $verusexp_curl );
        break;
        case 'getdifficulty':
            return curlRequest( $phpextconfig['explorer'] . '/api/getdifficulty', $verusexp_curl );
        break;
        case 'getsupply':
            return curlRequest( $phpextconfig['explorer'] . '/ext/getmoneysupply', $verusexp_curl );
    }
}

    /**
 * Verus Real-time Price Data
 */
function verusPrice( $currency ) {
    global $phpextconfig;
    $currency = strtoupper($currency);

    if ( $currency == 'VRSC' | $currency == 'VERUS' ) {
        $results = json_decode( curlRequest( $phpextconfig['fiat_api'] . 'rawpricedata.php', curl_init(), null ), true );
        return $results['data']['avg_btc'];
    }
    else {
        return curlRequest( $phpextconfig['fiat_api'] . '?currency=' . $currency, curl_init(), null );
    } 
}
/**
 * Create QR Code using the explorer
 */
function getQRCode( $qraddress, $size ) {
    $alt_text = 'Send VRSC to ' . $qraddress;
    return "\n" . '<img src="https://chart.googleapis.com/chart?chld=H|2&chs='.$size.'x'.$size.'&cht=qr&chl=' . $qraddress . '" alt="' . $alt_text . '" />' . "\n";
}
/**
 * Wrapper function for CURL calls
 */
function curlRequest( $url, $curl_handle, $fail_on_error = false ) {
    global $curl_requests;

    if ( $curl_handle === false ) {
        return false;
    }
    if ( $fail_on_error === true ) {
        curl_setopt( $curl_handle, CURLOPT_FAILONERROR, true );
    }
    curl_setopt( $curl_handle, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $curl_handle, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $curl_handle, CURLOPT_USERAGENT, 'Verus PHP EXT Tools' );
    curl_setopt( $curl_handle, CURLOPT_URL, $url );
    $curl_requests++;
    return curl_exec( $curl_handle );
}

?>
