<?php 
// No Direct Access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>
<div id="wc_veruspay_update_modal" style="display:none">
    <div id="wc_veruspay_update_modal-container">
        <span id="wc_veruspay_update_modal-container_close">X</span>
        <div id="wc_veruspay_update_modal-inner">
            <div id="wc_veruspay_update_modal-top">
                <h2>Update Daemon Coins</h2>
                <div id="wc_veruspay_update_modal-code_section">                    
                    <input type="text" placeholder="Enter Daemon Update Code" id="wc_veruspay_update_code">
                    <span id="wc_veruspay_update_modal-go" data-link="">Go</span>
                </div>
            </div>
            <div id="wc_veruspay_update_modal-iframe_container">
                <iframe id="wc_veruspay_update_iframe" src=""></iframe>
            </div>
        </div>
    </div>
</div>