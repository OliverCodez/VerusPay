<?php
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
global $wpdb;
// Delete scheduled hooks.
wp_clear_scheduled_hook( 'woocommerce_cancel_unpaid_submitted' );
// Delete options.
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'woocommerce_veruspay_%';" );
wp_cache_flush();