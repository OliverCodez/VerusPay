<?php 
// No Direct Access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>
<div id="wc-<?php echo esc_attr( $this->id ); ?>-cc-form" class="wc-payment-form" style="background:transparent;">
    <div class="woocommerce-Price-amount" style="height:100px;">
        <div class="wc_veruspay_coin_select">
            <span class="wc_veruspay_logo_<?php echo $wc_veruspay_coin; ?>"></span>
            <select name="wc_veruspay_coin" id="wc_veruspay_coin" class="" aria-hidden="true" onchange="this.form.submit()">
            <?php
                foreach ( $this->chains as $key => $item ) {
                    if ( $item['enabled'] == 'yes' ) {
                        if ( $key == $wc_veruspay_coin ) {
                            echo '<option value="' . $key . '" selected="selected">' . $item['name'] . '</option>';
                        }
                        else {
                            echo '<option value="' . $key . '">' . $item['name'] . '</option>';
                        }
                    }
                }
            ?>
            </select>
        </div>
        <div class="wc_veruspay_price">
            <?php echo strtoupper( $wc_veruspay_coin ) . ' ' . $wc_veruspay_global['text_help']['price']; ?>: <span class="vrsc-total-price"><?php echo $wc_veruspay_price; ?></span>
            <span class="wc_veruspay_icon-sml">
                <img id="wc_veruspay_icon-price" class="wc_veruspay_icon-sml" src="<?php echo plugins_url( '../public/img/wc-veruspay-refresh.png', __FILE__ ) ?>" border="0" alt="Refresh" /><noscript><button type="submit" class="button alt wc_veruspay_noscript" name="woocommerce_checkout_update_totals" value="Refresh <?php echo strtoupper( $wc_veruspay_coin ); ?> Price"><?php echo $wc_veruspay_global['text_help']['refresh']; ?></button></noscript>
            </span>
        </div>
        <div class="wc_veruspay_small_note">
            <span class="wc_veruspay_checkout-vsml wc_veruspay_checkout_rate_detail">
                <?php echo $wc_veruspay_global['text_help']['exchange_rate']; ?>: <?php echo get_woocommerce_currency_symbol(); ?><span class="vrsc-rate"><?php echo $wc_veruspay_rate; ?></span> / <?php echo strtoupper( $wc_veruspay_coin ); ?> - <?php echo $wc_veruspay_global['text_help']['msg_valid_for']; ?>: <span id="vrsc_last_updated_time" class="wc_veruspay_price_time" data-expiretime="<?php echo $wc_veruspay_time_end; ?>"><?php echo $wc_veruspay_time_remaining; ?> min</span>
            </span>
            <span class="wc_veruspay_checkout-vvsml wc_veruspay_poweredby">
                <i><a href="https://veruspay.io/api/" target="_BLANK"><?php echo $wc_veruspay_global['text_help']['price_powered_by']; ?></a></i>
            </span>
        </div>
        <?php echo $wc_veruspay_sapling_option; ?>
        <p class="wc_veruspay_custom_msg">
            <noscript><?php echo $wc_veruspay_global['text_help']['msg_noscript_refresh']; ?></noscript>
        </p>
        <p class="wc_veruspay_custom_msg">
            <?php echo $wc_veruspay_global['text_help']['msg_send_on_purchase']; ?> <?php echo $wc_veruspay_price; ?> <?php echo strtoupper( $wc_veruspay_coin ); ?> within <?php echo $this->orderholdtime; ?> min
        </p>
    </div>
</div>