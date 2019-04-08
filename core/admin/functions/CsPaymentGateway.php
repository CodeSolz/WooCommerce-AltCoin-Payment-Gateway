<?php namespace WooGateWayCoreLib\admin\functions;
/**
 * Retrive Settings Data
 * 
 * @package WAPG Admin 
 * @since 1.0.0
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    exit;
}

use WooGateWayCoreLib\admin\functions\WooFunctions;

class CsPaymentGateway {

    /**
     * Get woocommmerce / altcoin payment gateway info
     * 
     * @return type
     */
    public function save_general_settings(){
        $altcoin_id = WooFunctions::get_altcoin_gateway_settings_id();
        $settings = $_POST['cs_altcoin_config'];
        array_walk( $settings, 'sanitize_text_field' );
        update_option( $altcoin_id, $settings, 'yes');
        
        return wp_send_json(array(
            'status' => true,
            'title' => __( "Success", 'woo-altcoin-payment-gateway' ),
            'text' => __( "Your settings have been saved.", 'woo-altcoin-payment-gateway' ),
        ));
    }
    
    
    /**
     * get settings value
     * 
     * @return type
     */
    public static function get_settings_options(){
        $altcoin_id = WooFunctions::get_altcoin_gateway_settings_id();
        return get_option( $altcoin_id );
    }
}
