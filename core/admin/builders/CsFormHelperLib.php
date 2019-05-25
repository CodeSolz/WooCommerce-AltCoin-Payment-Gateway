<?php namespace WooGateWayCoreLib\admin\builders;

/**
 * Class: Admin Pages
 * 
 * @package Admin
 * @since 1.0.9
 * @author CodeSolz <customer-support@codesolz.net>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    die();
}

use WooGateWayCoreLib\lib\Util;

class CsFormHelperLib {
    
    /**
     * Gateway order confirmation
     * 
     * @return type
     */
    public static function order_confirm_options(){
        $options = array(
            '1'  => __( 'Manual', 'woo-altcoin-payment-gateway' ),
            '2'     => __( 'Automatic', 'woo-altcoin-payment-gateway' )
        );
        if(has_filter('filter_cs_wapg_order_confirm_options')) {
            $options = apply_filters( 'filter_cs_wapg_order_confirm_options', $options );
        }
        
        return $options;
    }
    
    /**
     * Get checkout order status type
     * 
     * @param type $user_input
     */
    public function get_order_confirm_type_status( $user_input ){
        $type_id = Util::check_evil_script( $user_input['type_id'] );
        if( $type_id == 2 ){
            if( defined( 'CS_WAPGE_VERSION' ) ){
                wp_send_json(array(
                    'status' => true
                    ));
            }else{
                wp_send_json(array(
                    'status' => false,
                    'text' => __(' Extesion Required! Please visit <a href=""> To Buy</a> the extension. Contact us at <b>support@codesolz.net</b> for more information.', 'woo-altcoin-payment-gateway')
                ));
            }
        }
    }
    
}
