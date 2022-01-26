<?php namespace WooGateWayCoreLib\frontend\functions;

/**
 * Front End Functions
 *
 * @package WAPG FE
 * @since 1.2.3
 * @author CoinMarketStats <support@coinmarketstats.online>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
	exit;
}

use WooGateWayCoreLib\lib\Util;
use WooGateWayCoreLib\lib\cartFunctions;
use WooGateWayCoreLib\admin\functions\CsAdminQuery;
use WooGateWayCoreLib\admin\functions\CsAutomaticOrderConfirmationSettings;

class CsWapgAutoOrderConfirm {

	/**
	 * Hold Trxid validator api url
	 *
	 * @var type
	 */
	
	private $api_config = array(
		'api_base' => 'https://api.coinmarketstats.online',
		'api_slug' => 'trxid_validator',
		'api_version' => 'v1'
	);


	public function track_coin( $raw_data ) {
		global $woocommerce;

		$raw_data = $raw_data['form_data'];
		$data     = array();
		parse_str( $raw_data, $data );
		$premade_order_id = $data['premade_order_id'];

		if ( isset( $data['trxid'] ) && empty( $trxid = $data['trxid'] ) ) {
			return wp_send_json(
				Util::notice_html(
					array(
						'error'    => true,
						'response' => __( 'Please enter your coin transaction ID. Make sure you have fill up all the required fields marked as (*)', 'woo-altcoin-payment-gateway' ),
					)
				)
			);
		}

		if ( isset( $data['secret_word'] ) && empty( $secret_word = $data['secret_word'] ) ) {
			return wp_send_json(
				Util::notice_html(
					array(
						'error'    => true,
						'response' => __( 'Please enter a secret word. Make sure you have fill up all the required fields marked as (*).', 'woo-altcoin-payment-gateway' ),
					)
				)
			);
		}

		// check first time
		$trxid_validator = cartFunctions::temp_update_trx_info( $trxid, $secret_word, $premade_order_id );
		if ( false === $trxid_validator ) {
			return wp_send_json(
				Util::notice_html(
					array(
						'error'    => true,
						'response' => __( 'We are unable to match your payment information. Make sure you have entered the correct "secret word" used first time with this transaction id.', 'woo-altcoin-payment-gateway' ),
					)
				)
			);
		}

		$config = CsAutomaticOrderConfirmationSettings::get_order_confirm_settings_data();
		if ( isset( $config['api_key'] ) && empty( $api_key = $config['api_key'] ) ) {
			return wp_send_json(
				Util::notice_html(
					array(
						'error'    => true,
						'response' => __( 'Api key not found! Please contact site administrator.', 'woo-altcoin-payment-gateway' ),
					)
				)
			);
		}

		$cart_info = cartFunctions::get_current_cart_payment_info( $premade_order_id );
		if ( empty( $cart_info ) ) {
			return wp_send_json(
				Util::notice_html(
					array(
						'error'    => true,
						'response' => __( 'Cart information not found! Please uninstall the this plugin and install a fresh copy then upgrade your WooCommerce store. ', 'woo-altcoin-payment-gateway' ),
					)
				)
			);
		}

		$cart_info = array_map( 'trim', $cart_info );
		$cartTotal = empty( $cart_info['cartTotalAfterDiscount'] ) ? $cart_info['cartTotal'] : $cart_info['cartTotalAfterDiscount'];

		$con_count = isset( $config['confirmation_count'] ) && ! empty( $config['confirmation_count'] ) ?
							$config['confirmation_count'] : 6;

		$api_url = $this->api_url_builder(array(
			$api_key,
			$cart_info['coinName'],
			$cart_info['coinAddress'],
			! isset( $config['coin_percentage'] ) || empty( $config['coin_percentage'] ) ? 100 : $config['coin_percentage'],
			Util::check_evil_script( $trxid ),
			$cart_info['totalCoin'],
			(float) $cartTotal,
			$secret_word,
			isset( $config['confirmation_count'] ) && ! empty( $config['confirmation_count'] ) ?
							$config['confirmation_count'] : 6
		));

		$response = Util::remote_call( $api_url );
		$response = json_decode( $response );


		if ( is_object( $response ) ) {
			if ( isset( $response->error ) && true === $response->error ) {
				// remove temp transaction data
				// cartFunctions::temp_remove_trx_info( $trxid, $premade_order_id );

				return wp_send_json(
					Util::notice_html(
						array(
							'error'    => true,
							'response' => isset( $response->response ) ? $response->response : $response->message,
						)
					)
				);
			} elseif ( isset( $response->success ) && false === $response->success ) {

				$response_msg = '';
				if ( isset( $response->response ) ) {
					$response_msg = $response->response;
				} elseif ( isset( $response->message ) ) {
					$response_msg = $response->message;
				} else {
					$confirmation = 0;
					if ( isset( $response->confirmation ) && $response->confirmation > 0 ) {
						$confirmation = $response->confirmation;
					}
					$response_msg = __( 'Your order is processing. Successful transaction confirmation count on ' ) . $confirmation . "/{$con_count}";
				}

				return wp_send_json(
					Util::notice_html(
						array(
							'success'  => false,
							'response' => $response_msg,
						)
					)
				);
			} elseif ( isset( $response->success ) && true === $response->success && true === $response->is_valid_for_order ) {

				// remove temp transaction data
				cartFunctions::temp_remove_trx_info( $trxid, $premade_order_id );

				return wp_send_json(
					Util::notice_html(
						array(
							'success'  => true,
							'response' => __( 'Thank you! Transaction completed successfully. Your order is processing right now!', 'woo-altcoin-payment-gateway' ),
						)
					)
				);
			} else {
				return wp_send_json(
					Util::notice_html(
						array(
							'success'              => false,
							// 'response' => __( 'Transaction on processing. Getting confirmation data..', 'woo-altcoin-payment-gateway' )
										'response' => implode( ' - ', (array) $response ),
						)
					)
				);
			}

		} else {
			return wp_send_json(
				Util::notice_html(
					array(
						'error'    => true,
						'response' => __( 'Unable to retrieve rest api data from server. Please make sure your server allow to call rest api.', 'woo-altcoin-payment-gateway' ),
					)
				)
			);
		}

	}


	/**
	 * Build rest api url
	 *
	 * @param array $params
	 * @return void
	 */
	private function api_url_builder( $params = [] ){
		if( empty( $params ) ){
			return false;
		}
		$sep = '/';
		$params = \array_map( 'trim', $params );

		return \implode( $sep, $this->api_config ) 
			. $sep . \implode( $sep, $params );

	}

}


