<?php

namespace WooGateWayCoreLib\frontend\functions\widgets;

/**
 * Frontend Widgets Functions
 *
 * @package Admin
 * @since 1.5.9
 * @author CoinMarketStats <support@coinmarketstats.online>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
	exit;
}

use WooGateWayCoreLib\lib\Util;
use WooGateWayCoreLib\admin\options\functions\CsSaveWidgetOptions;
use WooGateWayCoreLib\admin\functions\CsAutomaticOrderConfirmationSettings as AutoConfirm;

class CsWidgets{

    /**
     * Show live prices on widgets
     *
     * @param [type] $user_data
     * @return void
     */
    public function widget_get_live_coin_prices( $user_data ){

        //get widget options 
		$widget_options = CsSaveWidgetOptions::get_widget_options();

        if(
            isset( $widget_options['altcoin_display_coin_prices']['show_coins'] ) && 
            !empty( $coins = $widget_options['altcoin_display_coin_prices']['show_coins'] ) && 
            !empty( $apiBase = $user_data['form_data']['api'] )
        ){

            $tag_cloud_url = '#';
            if ( false === AutoConfirm::hasPaid() ) { 
                $tag_cloud_url = 'https://coinmarketstats.online/product/woocommerce-bitcoin-altcoin-payment-gateway';
            }

            $coinPrices = [];

            foreach( $coins as $coin ){
                $coin = \explode( '___', $coin );
                if( !isset( $coin[0] ) && empty( $coin[0] )) {
                    continue;
                }

                $response = Util::remote_call( $apiBase . '/' . $coin[0] );
                if ( ! isset( $response['error'] ) ) {

                    $response = \json_decode( $response);
                    if( isset($response->code) && !empty( $response->code ) ){
                        continue;
                    }

                    $coinPrices = array_merge_recursive( $coinPrices, array( array(
                            'label' => ( $coin[1] ) .' $'. (float) $response[0]->price_usd,
                            'url' => $tag_cloud_url,
                            'target' => ''
                    )));
                    
                }
            }

            return wp_send_json( $coinPrices );
        }
        return false;
		
	}


}