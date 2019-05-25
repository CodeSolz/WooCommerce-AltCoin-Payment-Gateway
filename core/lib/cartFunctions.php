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
    
    /**
     * get WooCommerce cart Hash as cart ID
     * 
     * @since 1.2.3
     * @return string
     */
    public static function get_cart_id(){
        global $woocommerce;
        
        $woo_version = Util::cs_get_woo_version_number();
        if( \version_compare( $woo_version,'3.6', '>=') ){
            return $woocommerce->cart->get_cart_hash();
        }else{
            return apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() );
        }
    }
    
    /**
     * Get current cart info
     * 
     * @since 1.2.3
     * @return string
     */
    public static function get_current_cart_payment_info(){
        return get_option( self::get_cart_id() .'_cart' );
    }
    
    /**
     * Save current cart info
     * 
     * @since 1.2.3
     * @return string
     */
    public static function save_current_cart_payment_info( $cart_info ){
        update_option( self::get_cart_id() . '_cart', $cart_info );
    }
    
    /**
     * Save current cart successful transaction log
     * 
     * @since 1.2.3
     * @return string
     */
    public static function save_transaction_successful_log(){
        update_option( self::get_cart_id() .'_log', 'success' );
    }
    
    /**
     * get current cart successful transaction log
     * 
     * @since 1.2.3
     * @return string
     */
    public static function get_transaction_successful_log(){
        return get_option( self::get_cart_id() .'_log' );
    }
    
    /**
     * get current cart successful transaction log
     * 
     * @since 1.2.3
     * @return string
     */
    public static function delete_transaction_successful_log(){
        return delete_option( self::get_cart_id() .'_log' );
    }
    
    /**
     * Save transaction info temporary
     * 
     * @since 1.2.3
     * @return string
     */
    public static function temp_update_trx_info( $trxid, $secret_word ){
        global $wpdb, $wapg_tables;
        $check_trxid_exists = $wpdb->get_var( $wpdb->prepare( " select id from {$wapg_tables['coin_trxids']} where transaction_id = '%s' ", $trxid ) );
        if( $check_trxid_exists ){
            $check_coin_exists = $wpdb->get_var( $wpdb->prepare( " select id from {$wapg_tables['coin_trxids']} where cart_hash = '%s' or secret_word = '%s' ", self::get_cart_id(), Util::check_evil_script($secret_word) ) );
            if( $check_coin_exists ){
                return true; 
            }
            return false;
        }else{
            $wpdb->insert( $wapg_tables['coin_trxids'], array(
                'cart_hash' =>self::get_cart_id(),
                'transaction_id' => $trxid,
                'secret_word' => $secret_word,
                'used_in' => Util::get_formated_datetime()
            ) );
            
            return true;
        }
    }
    
    /**
     * Remove transaction info temporary
     * 
     * @since 1.2.3
     * @return bolean
     */
    public static function temp_remove_trx_info( $trxid ){
        global $wpdb, $wapg_tables;
        $wpdb->delete( $wapg_tables['coin_trxids'], array( 'transaction_id' => $trxid ));
        return true;
    }
    
    /**
     * save transaction type info temporary
     * 
     * @since 1.2.3
     * @return bolean
     */
    public static function save_temp_log_checkout_type( $type ){
        update_option( self::get_cart_id() .'_ct', $type );
        return true;
    }
    
    /**
     * Get transaction type info temporary
     * 
     * @since 1.2.3
     * @return bolean
     */
    public static function get_temp_log_checkout_type(){
        return get_option( self::get_cart_id() .'_ct' );
    }
    
    /**
     * Remove transaction type info temporary
     * 
     * @since 1.2.3
     * @return bolean
     */
    public static function delete_temp_log_checkout_type(){
        delete_option( self::get_cart_id() .'_ct' );
        return true;
    }
}
