<?php namespace WooGateWayCoreLib\frontend\functions;
/**
 * Front End Functions
 * 
 * @package WAPG FE 
 * @since 1.2.8
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    exit;
}

use WooGateWayCoreLib\admin\functions\CsPaymentGateway;
use WooGateWayCoreLib\frontend\functions\CsWapgCoinCal;

class CsMiscellaneous {

    // Hold the class instance.
    private static $instance = null;

    // only if the class has no instance.
    public static function getInstance(){
        if (self::$instance == null){
            self::$instance = new CsMiscellaneous();
        }
        return self::$instance;
    }

    /**
     * show live coin price
     * 
     * @return string
     */    
    public function show_coin_price( $price, $obj  ){
        global $product;
        $settings = CsPaymentGateway::get_product_page_options();
        if( isset($settings['show_live_price'] ) && $settings['show_live_price'] == 'yes' ){
            $crypto_price = (new CsWapgCoinCal())->getCryptoLivePrices( $settings['show_live_coin_list'], $this->cs_get_product_price(), $settings );
            if( empty( $crypto_price ) || ! is_array($crypto_price) ){
                return $price; //return just original product price
            }

            if( isset($crypto_price['showPriceRange'] ) && true === $crypto_price['showPriceRange'] ){
                $coin_price = implode(' ', array_map(function($el){ return $el['html']; }, $crypto_price));
            }else{
                $coin_price = implode('/ ', array_map(function($el){ return $el['html']; }, $crypto_price));
            }

            if( count($crypto_price) > 1 || ( isset($crypto_price['showPriceRange'] ) && true === $crypto_price['showPriceRange'] ) ){
                return $price .'<br/>'. $coin_price;
            }
            
            return $price ."&#160;/&#160;". $coin_price;
        }
        return $price;
    }

    /**
     * get current product price
     *
     * @return String | Array
     */
    private function cs_get_product_price(){
        global $product;
        if ($product->is_type( 'simple' )) {
            return $product->get_sale_price();
        }
        elseif( $product->is_type( 'variable' ) ) {
            return array(
                'min' => $product->get_variation_price(),
                'max' => $product->get_variation_price('max'),
            );
        }
    }
    
}




