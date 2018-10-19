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

use WooGateWayCoreLib\lib\Util;

class CsWapgCoinCal {
    
    /**
     * Currency converter api
     *
     * @var type 
     */
    private $currency_converter_api_url = 'http://free.currencyconverterapi.com/api/v5/convert?q=%s&compact=y';
    
    /**
     *
     * @var type Coinmarketcap public api
     */
    private $coinmarketcap_api_url = "https://api.coinmarketcap.com/v1/ticker/%s";

    /**
     * Get Coin Price
     * 
     * @param type $coinName
     */
    public function calcualteCoinPrice(){
        global $woocommerce;
       
        $coin_info = sanitize_text_field($_POST['data']['coin_info']);
        if( empty($coin_info) ){
            wp_send_json(array('response' => false, 'msg' => __( 'Something Went Wrong! Please try again.', 'woo-altcoin-payment-gateway' ) ) );
        }else{
            $coinFullName = sanitize_text_field( $_POST['data']['coin_name']) ;
            $coin_info = explode( '__', $coin_info);
            $coinId  = trim($coin_info[0]);
            $coinNameArr = explode( '(', $coinFullName );
            $coinName = strtolower($coinNameArr[0]);
            $cartTotal = $woocommerce->cart->total;
            
            $store_currency = get_woocommerce_currency();
            $currency_symbol = get_woocommerce_currency_symbol();
            if( $store_currency != 'USD' ){
                $cartTotal = $this->store_currency_to_usd( $store_currency, $cartTotal );
                if( isset( $cartTotal['error' ] ) ){
                    wp_send_json( array('response' => false, 'msg' => $cartTotal['response'] ) );
                }
            }
            
            $coin_price = $this->get_coin_martket_price( $coinId );
            if( isset( $coin_price['error' ] ) ){
                wp_send_json( array('response' => false, 'msg' => $coin_price['response'] ) );
            }
            
            //calculate the coin
            $totalCoin = round( ( ( 1 / $coin_price ) * $cartTotal), 8 );
            wp_send_json( array( 'response' => true, 'cartTotal' => $woocommerce->cart->total,  'currency_symbol' => $currency_symbol, 'totalCoin' => $totalCoin, 'coinPrice' => $coin_price, 'coinFullName' =>$coinFullName,  'coinName' => $coinName, 'coinAddress' => $coin_info[1]   ));
            
        }
        
    }
    

    /**
     * Get converted store currency to usd
     */
    private function store_currency_to_usd( $store_currency, $cart_total ){
        $key = $store_currency .'_USD';
        $api_url = sprintf( $this->currency_converter_api_url , $key );
        $response = Util::remote_call( $api_url );
        if( isset( $response['error' ] ) ){
            return $response;
        }
        
        $response = json_decode( $response );
        if( is_object( $response ) ){
            return  $response->{$key}->val * $cart_total;
        }
        
        return array(
            'error' => true,
            'response' => __( 'Currency converter not working! Please contact administration.', 'woo-altcoin-payment-gateway' )
        );
    }
    
    /**
     * Get coin price from coin market cap
     */
    private function get_coin_martket_price( $coin_slug ){
        $api_url = sprintf( $this->coinmarketcap_api_url , $coin_slug );
        $response = Util::remote_call( $api_url );
        if( isset( $response['error' ] ) ){
            return $response;
        }
        
        $getMarketPrice = json_decode( $response );
        if( isset( $getMarketPrice[0]->price_usd ) ){
            return $getMarketPrice[0]->price_usd;
        }
        
        return array(
            'error' => true,
            'response' => __( 'Coinmarketcap api error. Please contact administration.', 'woo-altcoin-payment-gateway' )
        );
    }
    
}
