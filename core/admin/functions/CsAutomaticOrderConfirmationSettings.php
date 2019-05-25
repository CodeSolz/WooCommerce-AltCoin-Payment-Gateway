<?php namespace WooGateWayCoreLib\admin\functions;
/**
 * Automatic order confirmation
 * 
 * @package WAPG Admin 
 * @since 1.0.0
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    exit;
}

use WooGateWayCoreLib\lib\Util;

class CsAutomaticOrderConfirmationSettings {
    
    public static $settings_key = 'csAutoOrderConfirm';
    private $api_url = 'https://myportal.coinmarketstats.online/api/woo-altcoin-user-register';
    
    public function save_settings( $user_input ){
        
        $user_data = array_map( array( '\WooGateWayCoreLib\lib\Util', 'check_evil_script' ), $user_input['cs_altcoin_config'] );

        //check is valid api / credentials
        $api_status = Util::remote_call(
                $this->api_url, 
                'POST',
                array(
                    'body' => $user_data
                )
            );
        
        if( isset($api_status['error'] ) ){
            return wp_send_json(array(
                'status' => false,
                'title' => __( "Error", 'woo-altcoin-payment-gateway' ),
                'text' => $api_status['response']
            ));
        }
        
        $api_status = json_decode( $api_status );
        if( true === $api_status->success ){
            $user_data += array( 'id' => $api_status->id );
            update_option( self::$settings_key, $user_data );
            return wp_send_json(array(
                'status' => true,
                'title' => __( "Success", 'woo-altcoin-payment-gateway' ),
                'text' => __( "Your API has been successfully integrated." , 'woo-altcoin-payment-gateway' ),
            ));
        }else{
            return wp_send_json(array(
                'status' => false,
                'title' => __( "Error", 'woo-altcoin-payment-gateway' ),
                'text' => isset( $api_status->message ) ? $api_status->message : ''
            ));
        }
        
    }
    
    /**
     * Get settings data
     */
    public static function get_order_confirm_settings_data(){
        return  get_option( self::$settings_key );
    }
}
