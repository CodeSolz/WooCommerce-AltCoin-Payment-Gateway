<?php namespace WooGateWayCoreLib\admin\settings;

/**
 * Settings
 *
 * @package WAPG Admin
 * @since 1.0.0
 * @author CoinMarketStats <support@coinmarketstats.online>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
	exit;
}

use WooGateWayCoreLib\admin\functions\CsPaymentGateway;
use WooGateWayCoreLib\frontend\functions\CsWapgCoinCal;

class CsGateWaySettings {

	function __construct() {
		// class add it too WooCommerce
		add_filter( 'woocommerce_payment_gateways', array( $this, 'WAPG_authorizenet_init' ) );

		// load ajax url into frontend
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'WAPG_frontEnd_Enqueue' ) );
	}

	/**
	 * Get the initial class
	 *
	 * @param array $methods
	 * @return string
	 */
	public function WAPG_authorizenet_init( $methods ) {
		$methods[] = "WooGateWayCoreLib\\admin\\functions\\CsWapgFunctions";
		return $methods;
	}

	/**
	 * Enqueue script
	 *
	 * @return String
	 */
	public static function WAPG_frontEnd_Enqueue() {
		wp_enqueue_script( 'ajax-script', CS_WAPG_PLUGIN_ASSET_URI . 'js/wapg_ajax.js', array( 'jquery' ), 20 );


		// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
		wp_localize_script(
			'ajax-script',
			'wapg_ajax',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'cs_token' => wp_create_nonce( SECURE_AUTH_SALT ),
				'action' => '_cs_wapg_custom_call',
				'lp' => array(
					'cpM' => 'frontend\\functions\\CsMiscellaneous@get_crypto_prices'
				)
			)
		);


		if ( ( function_exists('is_shop') && is_shop() ) || 
			 ( function_exists('is_product') && is_product() )) {
			wp_enqueue_script( 'frontend.app.main', CS_WAPG_PLUGIN_ASSET_URI . 'js/wapg_app.min.js', array(), CS_WAPG_VERSION, true );
		}



	}
}
