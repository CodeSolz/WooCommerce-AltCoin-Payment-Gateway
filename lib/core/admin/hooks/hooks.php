<?php namespace WooGateWayCoreLib\admin\hooks;
/**
 * WP Core Hooks
 * 
 * @package WAPG Admin 
 * @since 1.0.0
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
	exit;
}

use WooGateWayCoreLib\admin\settings\CsGateWaySettings;
use WooGateWayCoreLib\admin\functions\CsWapgNotice;
use WooGateWayCoreLib\admin\functions\CsWapgInit;
/**
 * Load plugin script
 */
add_action( 'plugins_loaded', function(){ 
    new \WooGateWayCoreLib\admin\functions\CsWapgInit();
    if ( class_exists( 'WC_Payment_Gateway' ) ){
        return new CsGateWaySettings();
    }else{
        new CsWapgNotice( 1 );
    }
});

/**
 * on plugin activate
 */
add_action( 'activated_plugin', function( $plugin ){ 
    if( CS_WAPG_PLUGIN_IDENTIFIER == $plugin ){
        $init = new CsWapgInit();
        wp_redirect(admin_url("index.php?page={$init->welcome_slug}"));
        die();
    }
});

/**
 * Add Settings link
 */
add_filter( 'plugin_action_links', function( $links, $plugin ){
    if( CS_WAPG_PLUGIN_IDENTIFIER == $plugin ){
        $manage_link = '<a href="'.admin_url('admin.php?page=wc-settings&tab=checkout&section=wapg_altcoin_payment').'">' . __( 'Settings', CS_WAPG_TEXTDOMAIN ) . '</a>';
        array_unshift( $links, $manage_link ); // before other links
    }
    return $links;
}, 10, 2 );


/**
 * Get Live Coin data
 */
add_action( 'wp_ajax_calculateCoinPrice', array( 'WooGateWayCoreLib\\frontend\\functions\\CsWapgCoinCal', 'calcualteCoinPrice') );
add_action( 'wp_ajax_nopriv_calculateCoinPrice', array( 'WooGateWayCoreLib\\frontend\\functions\\CsWapgCoinCal', 'calcualteCoinPrice') );