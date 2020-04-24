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
    public function add_coin_price_placeholder( $price, $obj  ){
        global $product;
        $settings = CsPaymentGateway::get_product_page_options();
        if( isset($settings['show_live_price'] ) && $settings['show_live_price'] == 'yes' ){
            
            //add crypto-price placeholder
            return $price . "&#160;" .  $this->get_crypto_price_placeholder( $this->cs_get_product_price( $obj ), $obj );
            // return $price ."&#160;/&#160;" . $coin_price;
            

            $crypto_price = (new CsWapgCoinCal())->getCryptoLivePrices( $settings['show_live_coin_list'], $this->cs_get_product_price( $obj ), $settings );
            //DEBUG: 
            // print_r( $crypto_price );  
            
            if( empty( $crypto_price ) || ! is_array($crypto_price) ){
                return $price; //return just original product price
            }

            if( isset($crypto_price['showPriceRange'] ) && true === $crypto_price['showPriceRange'] ){
                $coin_price = implode(' ', array_map(function($el){ return $el['html']; }, $crypto_price));
            }else{
                $coin_price = implode('/ ', array_map(function($el){ return $el['html']; }, $crypto_price));
            }

            if( count($crypto_price) > 2 || ( isset($crypto_price['showPriceRange'] ) && true === $crypto_price['showPriceRange'] ) ){
                return $price .'<br/>'. $coin_price;
            }
            
            return "/&#160;". $coin_price;
        }
        return $price;
    }

    /**
     * Show coin price
     *
     * @return void
     */
    public static function show_coin_price( $data ){
        $productData = $data['form_data'];
        $price = $productData['minPrice'];
        if( isset($productData['maxPrice']) && !empty($productData['maxPrice']) ) {
            $price = array(
                'min' => $productData['minPrice'],
                'max' => $productData['maxPrice']
            );
        }
        $settings = CsPaymentGateway::get_product_page_options();
        
        $crypto_price = (new CsWapgCoinCal())->getCryptoLivePrices( $settings['show_live_coin_list'], $price, $settings );
        
        if( empty( $crypto_price ) ){
            return false;
        }

        if( isset($crypto_price['showPriceRange'] ) && true === $crypto_price['showPriceRange'] ){
            $coin_price = implode(' ', array_map(function($el){ return $el['html']; }, $crypto_price));
        }else{
            $coin_price = implode(' / ', array_map(function($el){ return $el['html']; }, $crypto_price));
        }

        $cryptoPrice =  '<br/> ' . $coin_price;
        

        wp_send_json(array(
            'product_id' => $productData['productID'],
            'priceHtml' => $cryptoPrice
        ));
        
        // sleep( 3 );
        // return print_r( $crypto_price );
    }

    /**
     * get current product price
     *
     * @return String | Array
     */
    private function cs_get_product_price( $currentProductObj = false  ){
        if( false === $currentProductObj ){
            global $product;
            $currentProductObj = $product;
        }
        
        if( $currentProductObj->is_type( 'variable' ) ) {
            return array(
                'min' => (float) $currentProductObj->get_variation_price(),
                'max' => (float) $currentProductObj->get_variation_price('max'),
            );
        }
        else {
            return (float) $currentProductObj->get_sale_price();
        }
    }

    /**
     * get crypto placeholder
     *
     * @param [type] $product_prices
     * @param [type] $productObj
     * @return void
     */
    private function get_crypto_price_placeholder( $product_prices, $productObj ){
        if( empty( $product_prices ) ){
            return false;
        }
        $product_id = $productObj->get_id();
        if( is_array( $product_prices ) ){
            $min = isset( $product_prices['min']) ? $product_prices['min'] : 0;
            $max = isset( $product_prices['max']) ? $product_prices['max'] : 0;
            return '<span id="csCrypprice_'.$product_id.'" class="csCryptoPrice" data-product_id="'.$product_id.'" data-min="'.$min.'" data-max="'.$max.'"></span>';
        }else{
            return '<span id="csCrypprice_'.$product_id.'" class="csCryptoPrice" data-product_id="'.$product_id.'" data-min="'.$product_prices.'" data-max="" ></span>';
        }
    }
    
    
    
}




