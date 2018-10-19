<?php namespace WooGateWayCoreLib\Actions;

/**
 * Class: Custom ajax call
 * 
 * @package Admin
 * @since 1.0.0
 * @author CodeSolz <customer-support@codesolz.net>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    die();
}

class CustomAjax {
    
    function __construct() {
        add_action( 'wp_ajax_cs_wapg_ajax', array( $this, '_cs_wapg_custom_call' ));
        add_action( 'wp_ajax_nopriv_cs_wapg_ajax', array( $this,'_cs_wapg_custom_call' ) );
    }
    
    /**
     * custom ajax call
     */
    public function _cs_wapg_custom_call(){
        if( ! isset( $_REQUEST['token'] ) || false === check_ajax_referer( SECURE_AUTH_SALT, 'token') ){
            _e( 'Sorry! Invalid token in your request!', 'woo-altcoin-payment-gateway');
            die();
        }
        
        if( ! isset( $_REQUEST['data'] ) && isset($_POST['method']) ){
            $data = $_POST;
        }else{
            $data = $_REQUEST['data'];
        }
        
        if( empty($method = $data['method'] ) || strpos( $method, '@') === false ){
            _e( 'Method parameter missing / invalid!', 'woo-altcoin-payment-gateway');
            die();
        }
        $method = explode( '@', $method ); 
        $class_path = str_replace( '\\\\', '\\', '\\WooGateWayCoreLib\\'.$method[0]);
        if( !class_exists( $class_path ) ){
            echo sprintf( __( 'Library Class "%s" not found! ', 'woo-altcoin-payment-gateway' ), $class_path );
            die();
        }
        
        if( ! method_exists( $class_path, $method[1]) ){
            echo sprintf( __( 'Method "%s" not found in Class "%s" not found! ', 'woo-altcoin-payment-gateway' ), $method[1], $class_path );
            die();
        }
        
        echo (new $class_path())->{$method[1]}( $data );
        exit;
    }
    
}
