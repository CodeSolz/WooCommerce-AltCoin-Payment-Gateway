<?php

namespace WooGateWayCoreLib\admin\functions;

/**
 * Retrive Settings Data
 *
 * @package WAPG Admin
 * @since 1.0.0
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
	exit;
}

use WooGateWayCoreLib\lib\Util;
use WooGateWayCoreLib\admin\functions\WooFunctions;

class CsPaymentGateway {


	private static $checkout_page_options_id = 'cswapg_checkout_page_optn';
	private static $product_page_options_id  = 'cswapg_product_page_optn';

	/**
	 * Get woocommmerce / altcoin payment gateway info
	 *
	 * @return type
	 */
	public function save_general_settings() {
		$altcoin_id = WooFunctions::get_altcoin_gateway_settings_id();
		$settings   = Util::check_evil_script( $_POST['cs_altcoin_config'] );
		array_walk( $settings, 'sanitize_text_field' );
		update_option( $altcoin_id, $settings, 'yes' );

		return wp_send_json(
			array(
				'status' => true,
				'title'  => __( 'Success', 'woo-altcoin-payment-gateway' ),
				'text'   => __( 'Your settings have been saved.', 'woo-altcoin-payment-gateway' ),
			)
		);
	}


	/**
	 * get settings value
	 *
	 * @return type
	 */
	public static function get_settings_options() {
		 $altcoin_id = WooFunctions::get_altcoin_gateway_settings_id();
		return get_option( $altcoin_id );
	}

	/**
	 * Save checkout page options
	 *
	 * @return array
	 */
	public function save_checkout_page_options() {
		$settings = Util::check_evil_script( $_POST['cs_altcoin_config'] );

		update_option( self::$checkout_page_options_id, $settings, 'yes' );

		return wp_send_json(
			array(
				'status' => true,
				'title'  => __( 'Success', 'woo-altcoin-payment-gateway' ),
				'text'   => __( 'Your settings have been saved.', 'woo-altcoin-payment-gateway' ),
			)
		);
	}

	/**
	 * import settings from old version
	 */
	public static function save_checkout_page_optn_from_old( $data ) {
		update_option( self::$checkout_page_options_id, $data, 'yes' );
	}

	/**
	 * get checkout page options
	 */
	public static function get_checkout_page_options() {
		return get_option( self::$checkout_page_options_id );
	}


	/**
	 * Save checkout page options
	 *
	 * @return array
	 */
	public function save_product_page_options() {
		$settings       = Util::check_evil_script( $_POST['cs_altcoin_config'] );
		$selected_coins = Util::check_evil_script( $_POST['show_live_coin_list'] );

		$config = $settings + array( 'show_live_coin_list' => $selected_coins );
		update_option( self::$product_page_options_id, $config, 'yes' );

		return wp_send_json(
			array(
				'status' => true,
				'title'  => __( 'Success', 'woo-altcoin-payment-gateway' ),
				'text'   => __( 'Your settings have been saved.', 'woo-altcoin-payment-gateway' ),
			)
		);
	}

	/**
	 * import settings from old version
	 */
	public static function save_product_page_optn_from_old( $data ) {
		update_option( self::$product_page_options_id, $data, 'yes' );
	}

	/**
	 * get checkout page options
	 */
	public static function get_product_page_options() {
		 return get_option( self::$product_page_options_id );
	}

	/**
	 * Get option for checkout
	 */
	public static function get_wapg_options() {
		 $checkout_options = self::get_checkout_page_options();
		$altcoin_id        = WooFunctions::get_altcoin_gateway_settings_id();
		$default_option    = get_option( $altcoin_id );

		return array_merge_recursive( $default_option, $checkout_options );
	}
}
