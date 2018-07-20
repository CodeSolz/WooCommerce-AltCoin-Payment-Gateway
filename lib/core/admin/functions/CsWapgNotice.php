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
    }


    /**
     * install failed
     */
    public function install_failed(){
        $class = 'notice notice-error';
	$message = __( 'In order to use \'WooCommerce AltCoin Payment Gateway\' plugin at first you need to install woocommerce.', CS_WAPG_TEXTDOMAIN );
	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
    }
    
}
