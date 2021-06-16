<?php namespace WooGateWayCoreLib\admin\functions;

/**
 * Retrive Settings Data
 *
 * @package WAPG Admin
 * @since 1.0.0
 * @author CoinMarketStats <support@coinmarketstats.online>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
	exit;
}

use WooGateWayCoreLib\lib\Util;
use WooGateWayCoreLib\admin\notices\wapgNotice;
use WooGateWayCoreLib\admin\functions\CsAutomaticOrderConfirmationSettings;

class WooFunctions {

	private $WcPg_Instance;
	public $altcoin_instance;
	private $plan_info = 'https://myportal.coinmarketstats.online/api/pro-plan-validity-check/';

	public function __construct() {

	}

	/**
	 * Get woocommmerce / payment gateway info
	 *
	 * @return type
	 */
	public function get_payment_info() {

		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return false;
		}

		if ( ! isset( $this->WcPg_Instance ) ) {
			$this->WcPg_Instance = new \WC_Payment_Gateways();
		}

		$payment_gateways = $this->WcPg_Instance->get_available_payment_gateways();

		if ( isset( $this->altcoin_instance ) ) {
			return $this->altcoin_instance;
		}

		if ( isset( $payment_gateways['wapg_altcoin_payment'] ) ) {
			$this->altcoin_instance = $payment_gateways['wapg_altcoin_payment'];
			return $this->altcoin_instance;
		}

		return false;
	}

	/**
	 *
	 * @return typeGet altcoin gateway id
	 */
	public static function get_altcoin_gateway_settings_id() {
		return 'woocommerce_wapg_altcoin_payment_settings';
	}


	/**
	 * Check plugins pro
	 * package plan info
	 *
	 * @return void
	 */
	public function wapg_get_plugins_info() {
		$isAutoOrder = CsAutomaticOrderConfirmationSettings::get_order_confirm_settings_data();

		if ( empty( $isAutoOrder ) ) {
			return;
		}

		$user_data  = array_map( array( '\WooGateWayCoreLib\lib\Util', 'check_evil_script' ), $isAutoOrder );
		$api_status = Util::remote_call(
			$this->plan_info,
			'POST',
			array(
				'body' => $user_data,
			)
		);

		if ( isset( $api_status['error'] ) ) {
			// api error
		} else {
			$api_status = json_decode( $api_status );
			if ( isset( $api_status->status ) && $api_status->status == 200 &&
				isset( $api_status->is_expired ) && true === $api_status->is_expired ) {
				\update_option( 'wapg_pro_trial_ended', 'yes' );
			}
		}

		return;
	}

}
