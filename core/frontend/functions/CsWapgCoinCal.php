<?php

namespace WooGateWayCoreLib\frontend\functions;

/**
 * Frontend Functions
 * 
 * @package WAPG Admin 
 * @since 1.0.0
 * @author CodeSolz <customer-service@codesolz.com>
 */

if (!defined('CS_WAPG_VERSION')) {
    exit;
}

use WooGateWayCoreLib\lib\Util;
use WooGateWayCoreLib\lib\cartFunctions;
use WooGateWayCoreLib\admin\functions\CsAdminQuery;

class CsWapgCoinCal
{

    /**
     * Currency converter api
     *
     * @var type 
     */
    private $currency_converter_api_url = 'https://api.coinmarketstats.online/fiat/v1/ticker/%s';

    /**
     *
     * @var Coinmarketstats free api / upgraded from coinmarketcap to coin marketstars
     */
    private $coinmarketstats_free_api_url = "https://api.coinmarketstats.online/coin/v1/ticker/free/%s";

    /**
     * Get coin price from CoinMarketStats
     *
     * @var string
     */
    private $coinmarketstats_api_url = "https://api.coinmarketstats.online/coin/v1/ticker/%s";

    /**
     * Get Coin Price
     * 
     * @param type $coinName
     */
    public function calcualteCoinPrice()
    {
        global $woocommerce;

        $coin_id = Util::check_evil_script(($_POST['data']['coin_id']));
        if (empty($coin_id)) {
            wp_send_json(array('response' => false, 'msg' => __('Something Went Wrong! Please try again.', 'woo-altcoin-payment-gateway')));
        } else {
            $coin = CsAdminQuery::get_coin_by('id', $coin_id);

            if (empty($coin)) {
                wp_send_json(array('response' => false, 'msg' => __('Something Went Wrong! Please try again.', 'woo-altcoin-payment-gateway')));
            }

            $is_premade_order_id = isset($_POST['data']['pre_order_id']) ? Util::check_evil_script($_POST['data']['pre_order_id']) : 0;

            $coinFullName = $coin->name . '( ' . $coin->symbol . ' )';
            $coinId  = $coin->coin_web_id;
            $coinType  = $coin->coin_type;
            $coinAddress = $this->get_coin_address($coin, $is_premade_order_id);
            $coinName = $coin->coin_web_id;

            $cartOriginalTotal = 0;
            if ($is_premade_order_id > 0) {
                $pre_order = \wc_get_order($is_premade_order_id);
                $order_data = $pre_order->get_data();
                if (isset($order_data['total']) && $order_data['total'] == 0) {
                    wp_send_json(array('response' => false, 'msg' => __('Something Went Wrong! Please refresh the page and try again.', 'woo-altcoin-payment-gateway')));
                }
                $cartOriginalTotal = $order_data['total'];
                $store_currency = $order_data['currency'];
            } else {
                $cartOriginalTotal = $woocommerce->cart->total;
                $store_currency = \get_woocommerce_currency();
            }

            $cartTotal = $cartOriginalTotal;
            $currency_symbol = \get_woocommerce_currency_symbol();

            //apply special discount if active
            $special_discount = false;
            $special_discount_msg = '';
            $special_discount_amount = '';
            $cartTotalAfterDiscount = '';
            if (true === $this->is_offer_valid($coin)) {
                $cartTotalAfterDiscount = $cartTotal = $this->apply_special_discount($cartTotal, $coin);
                $special_discount = true;
                $special_discount_type = Util::special_discount_msg($currency_symbol, $coin);
                $special_discount_msg = $special_discount_type['msg'];
                $special_discount_amount = $special_discount_type['discount'];
            }

            $coin_price = $this->get_coin_martket_price($coinId, $coinType);
            if (isset($coin_price['error'])) {
                wp_send_json(array('response' => false, 'msg' => $coin_price['response']));
            }

            $altcoinPriceOfStoreCurrency = '';
            if ($store_currency != 'USD') {
                $usd_conversion = $this->store_currency_to_usd($store_currency, $cartTotal);
                if (isset($usd_conversion['error'])) {
                    wp_send_json(array('response' => false, 'msg' => $usd_conversion['response']));
                }
                $cartTotal = $usd_conversion[0];
                $altcoinPriceOfStoreCurrency = $this->convert_altcoin_price_to_store_currency($usd_conversion[1], $coin_price);
            }


            //calculate the coin
            $totalCoin = $this->get_total_coin_amount($coin_price, $cartTotal);

            //return status
            $cart_info =  array(
                'response' => true, 'cartTotal' => $cartOriginalTotal, 'cartTotalAfterDiscount' => $cartTotalAfterDiscount,
                'currency_symbol' => $currency_symbol, 'totalCoin' => $totalCoin,
                'coinPrice' => $coin_price, 'coinFullName' => $coinFullName,
                'coinName' => $coinName, 'coinAddress' => $coinAddress, 'checkoutType' => $coin->checkout_type,
                'special_discount_status' => $special_discount, 'special_discount_msg' => $special_discount_msg,
                'special_discount_amount' => $special_discount_amount,
                'nativeAltCoinPrice' => round($altcoinPriceOfStoreCurrency, 2), 'store_currency_fullname' => $this->get_full_name_of_store_currency($store_currency),
                'store_currency_shortname' => $store_currency, 'premadeOrderId' => $is_premade_order_id
            );

            //save cart info
            cartFunctions::save_current_cart_payment_info($cart_info, $is_premade_order_id);
            wp_send_json($cart_info);
        }
    }

    /**
     * Check offer is valid
     * 
     * @return boolean
     */
    private function is_offer_valid($customField)
    {
        if ($customField->offer_status != 1) { //offer expired
            return false;
        }

        //check if offer end date not found
        if (empty($customField->offer_end)) {
            return false;
        }
        $currDateTime = Util::get_current_datetime();

        //check offer expired
        if ($currDateTime > $customField->offer_end || $currDateTime < $customField->offer_start) {
            return false;
        }

        return true;
    }

    /**
     * Add special discount
     */
    private function apply_special_discount($cartTotal, $customField)
    {
        if ($customField->offer_type == 1) {
            //percent
            $final_amount = (float) $cartTotal - (float) (($customField->offer_amount / 100) * $cartTotal);
        } elseif ($customField->offer_type == 2) {
            //flat amount
            $final_amount = (float) $cartTotal - (float) $customField->offer_amount;
        }
        return $final_amount;
    }

    /**
     * Get converted store currency to usd
     */
    public function store_currency_to_usd($store_currency, $cart_total)
    {
        $key = strtolower($store_currency);
        $api_url = sprintf($this->currency_converter_api_url, $key);
        $response = Util::remote_call($api_url);
        if (isset($response['error'])) {
            return $response;
        }

        $response = json_decode($response);

        if (is_object($response)) {
            if ($response->data[0]->currency == $key) {
                return array(
                    (float) $response->data[0]->usd * (float) $cart_total,
                    (float) $response->data[0]->usd
                );
            } else {
                return array(
                    'error' => true,
                    'response' => __('Currency not found. Please contact support@codesolz.net to add your currency.', 'woo-altcoin-payment-gateway')
                );
            }
        }

        return array(
            'error' => true,
            'response' => __('Currency converter not working! Please contact administration.', 'woo-altcoin-payment-gateway')
        );
    }

    /**
     * Get coin price from coin market cap
     */
    private function get_coin_martket_price($coin_slug, $coinType)
    {

        if( $coinType == 1 ){
            $api_url = sprintf($this->coinmarketstats_free_api_url, $coin_slug);
        }elseif( $coinType == 2 ){
            $api_url = sprintf($this->coinmarketstats_api_url, $coin_slug);
        }

        if( !isset($api_url)){
            return array(
                'error' => true,
                'response' => __('Error Found! Please delete the current plugin and download a fresh copy and install it.', 'woo-altcoin-payment-gateway')
            );
        }

        $response = Util::remote_call($api_url);
        if (isset($response['error'])) {
            return $response;
        }

        $getMarketPrice = json_decode($response);
        
        if ( !isset($getMarketPrice->error) && isset($getMarketPrice[0])) {
            $price = (float) $getMarketPrice[0]->price_usd;
            $market_cap_usd = (float) $getMarketPrice[0]->market_cap_usd;
            if ($market_cap_usd > 0 || $coinType == 2) {
                return $price;
            } else {
                //coin doesn't have any value
                return array(
                    'error' => true,
                    'response' => __('Probably this currency is out of the market & doesn\'t have any value! Contact administration for more information..', 'woo-altcoin-payment-gateway')
                );
            }
        }

        return array(
            'error' => true,
            'response' => __('Coin not found in the exchange portal! Please contact administration.', 'woo-altcoin-payment-gateway')
        );
    }

    /**
     * Get total coin amount
     * 
     * @param type $coin_price
     * @param type $cartTotal
     * @return type
     */
    private function get_total_coin_amount($coin_price, $cartTotal)
    {
        return round(((1 / $coin_price) * $cartTotal), 8);
    }

    /**
     * altcoin price to store currency
     */
    private function convert_altcoin_price_to_store_currency($usd_value, $coin_price)
    {
        return (1 / $usd_value) * $coin_price;
    }

    /**
     * Get currency full name
     * 
     * @return string
     */
    private function get_full_name_of_store_currency($curr_short_name)
    {
        $all_avail_currencies = get_woocommerce_currencies();
        return isset($all_avail_currencies[$curr_short_name]) ? $all_avail_currencies[$curr_short_name] : '';
    }

    /**
     * Get coin address
     */
    private function get_coin_address($coin, $is_premade_order_id)
    {
        if ($coin->checkout_type == 2) {
            // $cart_info = cartFunctions::get_current_cart_payment_info($is_premade_order_id);
            // if (  empty($cart_info) || $coin->coin_web_id != $cart_info['coinName'] ) {
                $coin_add_arr = explode(',', $coin->address);
                return $coin_add_arr[array_rand($coin_add_arr)];
            // } else {
            //     return $cart_info['coinAddress'];
            // }
        } else {
            return $coin->address;;
        }
    }

    /**
     * Get coin price
     * 
     * @since 1.2.8
     * @return array
     */
    public function getCryptoLivePrices($coins, $product_price, $config = array() )
    {
        if (empty($coins)) return false;
        $coin_prices = [];
        $store_currency = get_woocommerce_currency();

        $c = 0;
        foreach ($coins as $coin) {
            $coin_arr = explode('___', $coin);
            if( !isset( $coin_arr[0]) || empty( $coin_arr[0]) || !isset( $coin_arr[2]) || empty( $coin_arr[2]) ){
                continue;
            }
            $coin_price = $this->get_coin_martket_price( $coin_arr[0], $coin_arr[2] );


            $showPriceRange = array();
            $coinPriceHtml = '';
            if( is_array( $product_price ) ){
                //variable product price range
                if( isset($config['variable_product_price_type']) && ( $config['variable_product_price_type'] == 'min' || $config['variable_product_price_type'] == 'max' ) ){
                    $totalCoin = $this->getCryptoLiveCoinPrice( $store_currency, $product_price[ $config['variable_product_price_type'] ], $coin_price );
                    $coinPriceHtml = \sprintf( "<span style='white-space:nowrap'>%s</span>", $totalCoin . ' %1$s' );
                }else{
                    if( $c > 0 ) {
                        $coinPriceHtml .= " <br/> ";
                    }
                    
                    $i = 0;
                    foreach( $product_price as $key => $price_range ){
                        $totalCoin = $this->getCryptoLiveCoinPrice( $store_currency, $price_range, $coin_price );
                        if( $i > 0 ) {
                            $coinPriceHtml .= " - ";
                        }
                        $coinPriceHtml .= \sprintf( "<span style='white-space:nowrap' class='cs'>%s</span>", $totalCoin . ' %1$s' );
                        $i++;
                    }
                    
                    
                    $showPriceRange = array( 'showPriceRange' => true );
                }
            }else{
                $totalCoin = $this->getCryptoLiveCoinPrice( $store_currency, $product_price, $coin_price );
                $coinPriceHtml = \sprintf( "<span style='white-space:nowrap' class='crypto-price'>%s</span>",  $totalCoin . ' %1$s' );
            }

            $coin_price_html = \sprintf( $coinPriceHtml, $coin_arr[1] );
            
            $coin_prices += array( $coin_arr[0] => array( 'price' => $totalCoin, 'html' => $coin_price_html )  ) + $showPriceRange;

            $c++;
        }
        return $coin_prices;
    }

    /**
     * get crypto price
     *
     * @param [type] $store_currency
     * @param [type] $product_price
     * @param [type] $coin_price
     * @return void
     */
    private function getCryptoLiveCoinPrice( $store_currency, $product_price, $coin_price ){
        if ($store_currency != 'USD') {
            $usd_conversion = $this->store_currency_to_usd($store_currency, $product_price);
            if (isset($usd_conversion['error'])) {
                return false;
            }
            $product_price =  $usd_conversion[0];
        }

        //calculate the coin
        return $this->get_total_coin_amount( (float) $coin_price, (float) $product_price);
    }

}
