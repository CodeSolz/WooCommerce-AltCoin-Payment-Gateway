<?php

/**
 * @wordpress-plugin
 * Plugin Name:       WooCommerce AltCoin Payment Gateway
 * Plugin URI:        https://wordpresspremiumplugins.com/plugins/woo-altcoin-payment-gateway
 * Description:       Woocommerce payment gateway to accept crypto currency in your store.
 * Version:           1.0.6
 * Author:            CodeSolz
 * Author URI:        https://www.codesolz.net
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl.txt
 * Domain Path:       /languages
 * Text Domain:       woo-altcoin-payment-gateway
 * Requires PHP: 5.4
 * Requires At Least: 4.0
 * Tested Up To: 4.9.8
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    /**
     * Define current version
     */
    define( 'CS_WAPG_VERSION', '1.0.6' );
    
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

function cs_wapg_plugin_textdomain() {
    load_plugin_textdomain( 'woo-altcoin-payment-gateway', FALSE, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'cs_wapg_plugin_textdomain' );

require_once __DIR__ . '/lib/vendor/autoload.php';
require_once __DIR__ . '/lib/core/admin/hooks/hooks.php';



