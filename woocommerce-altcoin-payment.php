<?php

/**
 * @wordpress-plugin
 * Plugin Name:       WooCommerce AltCoin Payment Gateway
 * Plugin URI:        https://wordpresspremiumplugins.com/plugins/woo-altcoin-payment-gateway
 * Description:       Woocommerce payment gateway to accept crypto currency in your store.
 * Version:           1.0.2
 * Author:            CodeSolz
 * Author URI:        https://www.codesolz.net
 * License:           MIT
 * Text Domain:       cs-altcoin-payment-gateway
 * Domain Path:       ./lang
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    /**
     * Define current version
     */
    define( 'CS_WAPG_VERSION', '1.5.0' );
    
    /**
     * Define textdomain
     */
    define( 'CS_WAPG_TEXTDOMAIN', 'cs-altcoin-payment-gateway' );

    /**
     * Define asset uri
     */
    define( 'CS_WAPG_PLUGIN_ASSET_URI', plugin_dir_url( __FILE__ ) . 'assets/' );
    
    /**
     * Library uri
     */
    define( 'CS_WAPG_PLUGIN_LIB_URI', plugin_dir_url( __FILE__ ) . 'lib/' );
    
    /**
     * plugins identifier
     */
    define( 'CS_WAPG_PLUGIN_IDENTIFIER', 'WooCommerce_AltCoin_Payment_Gateway/woocommerce-altcoin-payment.php' );
    
    /**
     * Plugin name
     */
    define( 'CS_WAPG_PLUGIN_NAME', 'WooCommerce AltCoin Payment Gateway' );
}

require_once __DIR__ . '/lib/vendor/autoload.php';
require_once __DIR__ . '/lib/core/admin/hooks/hooks.php';



