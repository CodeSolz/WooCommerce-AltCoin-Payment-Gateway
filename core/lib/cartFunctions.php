<?php namespace WooGateWayCoreLib\lib;
/**
 * Cart Functions
 * 
 * @package Library
 * @since 1.0.0
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
   exit;
}

class cartFunctions {
    
    private static $payment_info_key = '_altcoin_payment_info';

    /**
     * Save payment info
     * 
     * @param type $order_id
     * @param type $payment_info
     */
    public static function save_payment_info( $order_id, $payment_info ){
        update_post_meta( $order_id, self::$payment_info_key, $payment_info );
        return true;
    }
    
    /**
     * Get payment info
     * 
     * @param type $order_id
     */
    public static function get_payment_info( $order_id ){
        $data = get_post_meta( $order_id, self::$payment_info_key );
        if( !empty($data)){
            return $data[0];
        }
        return false;
    }
    
}
