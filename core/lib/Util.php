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
}
