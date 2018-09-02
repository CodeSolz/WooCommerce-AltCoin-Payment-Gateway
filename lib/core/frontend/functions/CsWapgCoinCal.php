<?php namespace WooGateWayCoreLib\frontend\functions;
/**
 * Frontend Functions
 * 
 * @package WAPG Admin 
 * @since 1.0.0
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    exit;
}

class CsWapgCoinCal {
    
    /**
     * Get Coin Price
     * 
     * @param type $coinName
     */
    public static function calcualteCoinPrice(){
        check_ajax_referer(SECURE_AUTH_SALT, 'code');
        
        global $woocommerce;
//        
//        echo "<pre>";
//        print_r( $woocommerce->cart->total );
//        exit;
        
        $coin_info = sanitize_text_field($_POST['coin_info']);
        if( empty($coin_info) ){
            echo json_encode(array('response' => false, 'msg' => 'Something Went Wrong! Please try again.'));
        }else{
            $coinFullName = sanitize_text_field( $_POST['coin_name']) ;
            $coin_info = explode( '__', $coin_info);
            $coinId  = trim($coin_info[0]);
            
            $coinNameArr = explode( '(', $coinFullName );
            $coinName = strtolower($coinNameArr[0]);
                    
            $cartTotal = (int)$woocommerce->cart->total;
            
            $getMarketPrice = \file_get_contents( "https://api.coinmarketcap.com/v1/ticker/{$coinId}" );
            if( $getMarketPrice ){
                $getMarketPrice = json_decode( $getMarketPrice );
                $coinPrice =  $getMarketPrice[0]->price_usd;
                $totalCoin = round( ( ( 1 / $coinPrice ) * $cartTotal), 8 );
                wp_send_json( array( 'response' => true, 'cartTotal' => $cartTotal, 'totalCoin' => $totalCoin, 'coinPrice' => $coinPrice, 'coinFullName' =>$coinFullName,  'coinName' => $coinName, 'coinAddress' => $coin_info[1]   ));
            }else{
                wp_send_json(array('response' => false, 'msg' => sprintf( __( '%s API couldn\'t reach! Please try again or contact customer support. %s', CS_WAPG_TEXTDOMAIN ), '<div class="woocommerce-error">', '</div>' ) ) );
            }
        }
        exit;
    }
    

    
}
