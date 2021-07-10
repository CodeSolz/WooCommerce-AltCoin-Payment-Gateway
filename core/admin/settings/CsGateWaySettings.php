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

}
