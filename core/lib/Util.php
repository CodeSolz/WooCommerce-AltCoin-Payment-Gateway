<?php namespace WooGateWayCoreLib\lib;
/**
 * Util Functions
 * 
 * @package Library
 * @since 1.0.0
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
   exit;
}

class Util {
    
    /**
     * Encode Html Entites
     * 
     * @param type $str
     * @return type
     */
    public static function encode_html_chars( $str ){
        return esc_html( $str );
    }
    
    /**
     * Wp remote call 
     * 
     * @param type $url
     * @param type $method
     * @return type
     */
    public static function remote_call( $url, $method = 'GET' ){
        if( $method == 'GET' ){
            $response = wp_remote_get( $url, 
                array( 'timeout' => 120, 'httpversion' => '1.1' ) 
            );
        }

        if ( is_wp_error( $response ) ) {
            return array(
                'error' => true,
                'response' => $response->get_error_message()
            );
        }

        return wp_remote_retrieve_body( $response );
    }
    
    /**
     * markup tagline
     * 
     * @param type $tagline
     */
    public static function markup_tag( $tagline ){
        echo sprintf( "\n<!--%s - %s-->\n", CS_WAPG_PLUGIN_NAME, $tagline );
    }
    
    /**
     * 
     * @param type $datetime
     * @param type $format
     * @return typeget formated date time
     */
    public static function get_formated_datetime( $datetime, $format = 'Y-m-d H:i:s' ){
        self::set_wp_timezone();
        return date( $format, strtotime( $datetime ) );
    }
    
    /**
     * Set wordpress time zone
     */
    public static function set_wp_timezone(){
        //set plugins time zone to wordpress timezone
        if ( empty( $default_timeZone = get_option('timezone_string') ) ){
            date_default_timezone_set( 'UTC' );
        }else{
            date_default_timezone_set( $default_timeZone );
        }
    }
    
    /**
     * Get current datetime
     * 
     * @return type
     */
    public static function get_current_datetime( $format = 'Y-m-d H:i:s'){
        self::set_wp_timezone();
        return date( $format );
    }

    /**
     * Get checkout type
     * 
     * @param type $type_id
     * @return string
     */
    public static function get_checkout_type( $type_id ){
        if( $type_id == 1 ){
            return '<span class="badge badge-warning"> Manual</span>';
        }else{
            
        }
    }
    
    /**
     * get coin status
     * 
     * @param type $type_id
     * @return string
     */
    public static function get_coin_status( $type_id ){
        if( $type_id == 1 ){
            return '<span class="badge badge-success">Active</span>';
        }else{
            return '<span class="badge badge-warning">Inactive</span>';
        }
    }
    
    /**
     * get coin status
     * 
     * @param type $type_id
     * @return string
     */
    public static function get_offer_status( $type_id ){
        if( $type_id == 1 ){
            return '<span class="badge badge-success">Active</span>';
        }else{
            return '<span class="badge badge-warning">None</span>';
        }
    }
    
    /**
     * get coin status
     * 
     * @param type $type_id
     * @return string
     */
    public static function get_offer_type( $type_id ){
        if( $type_id == 1 ){
            return 'percent(%)';
        }else{
            return 'Flat Amount( Fiat Currency )';
        }
    }
    
    /**
     * Special discount msg
     * 
     * @param type $currency_symbol
     * @param type $offer_obj
     * @return type
     */
    public static function special_discount_msg( $currency_symbol, $offer_obj ){
        $msg = __( 'You have got %s special discount of total amount!', 'woo-altcoin-payment-gateway');
        $con = __( 'Congratulation!', 'woo-altcoin-payment-gateway');
        $discount = '';
        if( $offer_obj->offer_type == 1 ){
            $discount = $offer_obj->offer_amount . '%';
            $offer_msg = sprintf( $msg, $discount );
        }elseif( $offer_obj->offer_type == 2 ){
            $discount = $currency_symbol . $offer_obj->offer_amount;
            $offer_msg = sprintf( $msg, $discount );
        }
        return array( 
                'discount' => $discount,
                'msg' => '<div class="special-discount-msg"> <span class="con blink">'.$con.'</span> ' . $offer_msg . '</div>' 
            );
    }
    
    /**
     * Get discount type
     * 
     * @param type $amount
     * @param type $type
     * @return type
     */
    public static function get_discount_type( $amount, $type ){
        if( $type == 1 ){
            return $amount . '%';
        }elseif( $type == 2 ){
            return get_woocommerce_currency_symbol() . $amount;
        }
    }
    
    /**
     * Check Evil Script Into User Input
     * 
     * @param array|string $user_input
     * @return type
     */
    public static function check_evil_script( $user_input, $textarea = false ){
        if( is_array( $user_input )){
            if( $textarea === true){
                $user_input = array_map( 'sanitize_textarea_field', $user_input);
            }else{
                $user_input = array_map( 'sanitize_text_field', $user_input);
            }
            $user_input = array_map( 'stripslashes_deep', $user_input);
        }else{
            if( $textarea === true){
                $user_input = sanitize_textarea_field( $user_input );
            }else{
                $user_input = sanitize_text_field( $user_input );
            }
            $user_input = stripslashes_deep( $user_input );
            $user_input = trim($user_input);
        }
        return $user_input;
    }
}
