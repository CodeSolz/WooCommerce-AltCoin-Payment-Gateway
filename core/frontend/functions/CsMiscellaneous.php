<?php namespace WooGateWayCoreLib\frontend\functions;

/**
 * Front End Functions
 *
 * @package WAPG FE
 * @since 1.2.8
 * @author CoinMarketStats <support@coinmarketstats.online>
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
	public static function getInstance() {
		if ( self::$instance == null ) {
			self::$instance = new CsMiscellaneous();
		}
		return self::$instance;
	}

	/**
	 * show live coin price
	 *
	 * @return string
	 */
	public function add_coin_price_placeholder( $price, $obj ) {
		global $product;
		$settings = CsPaymentGateway::get_product_page_options();
		if ( isset( $settings['show_live_price'] ) && $settings['show_live_price'] == 'yes' ) {
			// add crypto-price placeholder
			return $price . '&#160;' . $this->get_crypto_price_placeholder( $this->cs_get_product_price( $obj ), $obj );
		}
		return $price;
	}

	/**
	 * Get crypto live prices
	 *
	 * @return void
	 */
	public static function get_crypto_prices() {
		$settings = CsPaymentGateway::get_product_page_options();
		if ( ! isset( $settings['show_live_price'] ) || $settings['show_live_price'] != 'yes' ) {
			return wp_send_json(
				array(
					'show_live_price' => false,
				)
			);
		}

		$prices        = array();
		$CsWapgCoinCal = new CsWapgCoinCal();
		if ( ! empty( $coins = $settings['show_live_coin_list'] ) ) {

			foreach ( $coins as $coin ) {
				$coin_arr = explode( '___', $coin );
				if ( ! isset( $coin_arr[0] ) || empty( $coin_arr[0] ) || ! isset( $coin_arr[2] ) || empty( $coin_arr[2] ) ) {
					continue;
				}

				$settings = \array_merge_recursive(
					$settings,
					array(
						'crypto_prices' => array(
							$coin => $CsWapgCoinCal->get_coin_martket_price( $coin_arr[0], $coin_arr[2] ),
						),
					)
				);

			}
		}

		$store_currency = \get_woocommerce_currency();
		$settings       = \array_merge_recursive( $settings, array( 'store_currerncy' => $store_currency ) );
		if ( $store_currency != 'USD' ) {
			$usd_conversion = $CsWapgCoinCal->store_currency_to_usd( $store_currency, 1 );
			if ( ! isset( $usd_conversion['error'] ) ) {
				$settings = \array_merge_recursive( $settings, array( 'converted_usd_price' => $usd_conversion[1] ) );
			}
		}

		return wp_send_json(
			$settings
		);

	}


	/**
	 * get current product price
	 *
	 * @return String | Array
	 */
	private function cs_get_product_price( $currentProductObj = false ) {
		if ( false === $currentProductObj ) {
			global $product;
			$currentProductObj = $product;
		}

		if ( $currentProductObj->is_type( 'variable' ) ) {
			return array(
				'min' => (float) $currentProductObj->get_variation_price(),
				'max' => (float) $currentProductObj->get_variation_price( 'max' ),
			);
		} else {
			return empty( $price = $currentProductObj->get_sale_price() ) ?
							(float) $currentProductObj->get_price() :
							(float) $price;
		}
	}

	/**
	 * get crypto placeholder
	 *
	 * @param [type] $product_prices
	 * @param [type] $productObj
	 * @return void
	 */
	private function get_crypto_price_placeholder( $product_prices, $productObj ) {
		if ( empty( $product_prices ) ) {
			return false;
		}
		$product_id = $productObj->get_id();
		if ( is_array( $product_prices ) ) {
			$min = isset( $product_prices['min'] ) ? $product_prices['min'] : 0;
			$max = isset( $product_prices['max'] ) ? $product_prices['max'] : 0;
			return '<span id="csCrypprice_' . $product_id . '" class="csCryptoPrice" data-product_id="' . $product_id . '" data-min="' . $min . '" data-max="' . $max . '"><img class = "crypPriceLoader" height="26" width="20" style="height:26px;width:20px" src="' . CS_WAPG_PLUGIN_ASSET_URI . 'img/price-loader.gif"></span>';
		} else {
			return '<span id="csCrypprice_' . $product_id . '" class="csCryptoPrice" data-product_id="' . $product_id . '" data-min="' . $product_prices . '" data-max="" ><img class = "crypPriceLoader" height="26" width="20" style="height:26px;width:20px"  src="' . CS_WAPG_PLUGIN_ASSET_URI . 'img/price-loader.gif"></span>';
		}
	}



}




