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

use WooGateWayCoreLib\frontend\functions\CsWapgCoinCal;
use WooGateWayCoreLib\admin\functions\CsPaymentGateway;

class CsMiscellaneous {

    /**
     * show live coin price
     * 
     * @return string
     */    
    public static function show_coin_price( $price, $obj  ){
        global $product;
        $settings = CsPaymentGateway::get_product_page_options();
        if( isset($settings['show_live_price'] ) && $settings['show_live_price'] == 'yes' ){
            $crypto_price = (new CsWapgCoinCal())->getCryptoLivePrices( $settings['show_live_coin_list'], $product->get_sale_price() );
            $coin_price = implode('/ ', array_map(function($el){ return $el['html']; }, $crypto_price));
            if( count($crypto_price) > 1 ){
                return $price .'<br/>'. $coin_price;
            }
            return $price ."&#160;/&#160;". $coin_price;
        }
        return $price;
    }
    
}
