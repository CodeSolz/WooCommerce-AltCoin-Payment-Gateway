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
    
}
