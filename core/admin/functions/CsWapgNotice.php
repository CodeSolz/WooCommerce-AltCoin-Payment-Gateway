<?php namespace WooGateWayCoreLib\admin\functions;
/**
 * Settings
 * 
 * @package WAPG Admin 
 * @since 1.0.0
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    exit;
}

use WooGateWayCoreLib\lib\Util;

class CsWapgNotice {

    /**
     * Construct 
     * 
     * @param type $type
     */
    function __construct( $type ) {
        if( $type === 1 ){
            //propmt notice
            add_action( 'admin_notices', array( $this, 'install_failed') );
        }
        
        if( $type === 2 ){
            //propmt notice
            add_action( 'admin_notices', array( $this, 'upgrade_to_pro') );
        }
    }


    /**
     * install failed
     */
    public function install_failed(){
        $class = 'notice notice-error';
	$message = __( 'In order to use \'WooCommerce AltCoin Payment Gateway\' plugin at first you need to install WooCommerce plugin.', 'woo-altcoin-payment-gateway' );
	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
    }
    
    /**
     * Register to automatic order confirmation
     */
    public function upgrade_to_pro(){
        $class = 'update-nag';
	$message = __( 'You can %s upgrade to pro version or use trial %s of \'WooCommerce AltCoin Payment Gateway\' to receive automatic order confirmation', 'woo-altcoin-payment-gateway' );
	$register_link = Util::cs_generate_admin_url('cs-woo-altcoin-automatic-order-confirmation-settings');
        $message = sprintf( $message, '<a href="'.$register_link.'">', '</a>' );
	printf( '<div class="%1$s" style="%3$s" >%2$s</div>', $class, $message, 'clear:both; width: 96% !important;' ); 
    }
    
}
