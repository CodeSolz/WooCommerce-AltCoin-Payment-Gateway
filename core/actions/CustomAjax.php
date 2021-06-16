<?php namespace WooGateWayCoreLib\actions;

/**
 * Class: Custom ajax call
 *
 * @package Admin
 * @since 1.0.0
 * @author CoinMarketStats <support@coinmarketstats.online>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
	die();
}

class CustomAjax {

	function __construct() {
		add_action( 'wp_ajax__cs_wapg_custom_call', array( $this, '_cs_wapg_custom_call' ) );
		add_action( 'wp_ajax_nopriv__cs_wapg_custom_call', array( $this, '_cs_wapg_custom_call' ) );
	}


	/**
	 * custom ajax call
	 */
	public function _cs_wapg_custom_call() {

		if ( ! isset( $_REQUEST['cs_token'] ) || false === check_ajax_referer( SECURE_AUTH_SALT, 'cs_token', false ) ) {
			wp_send_json(
				array(
					'status' => false,
					'title'  => __( 'Invalid token', 'woo-altcoin-payment-gateway' ),
					'text'   => __( 'Sorry! we are unable recognize your auth!', 'woo-altcoin-payment-gateway' ),
				)
			);
		}

		if ( ! isset( $_REQUEST['data'] ) && isset( $_POST['method'] ) ) {
			$data = $_POST;
		} else {
			$data = $_REQUEST['data'];
		}

		if ( empty( $method = $data['method'] ) || strpos( $method, '@' ) === false ) {
			wp_send_json(
				array(
					'status' => false,
					'title'  => __( 'Invalid Request', 'woo-altcoin-payment-gateway' ),
					'text'   => __( 'Method parameter missing / invalid!', 'woo-altcoin-payment-gateway' ),
				)
			);
		}
		$method     = explode( '@', $method );
		$class_path = str_replace( '\\\\', '\\', '\\WooGateWayCoreLib\\' . $method[0] );
		if ( ! class_exists( $class_path ) ) {
			wp_send_json(
				array(
					'status' => false,
					'title'  => __( 'Invalid Library', 'woo-altcoin-payment-gateway' ),
					'text'   => sprintf( __( 'Library Class "%s" not found! ', 'woo-altcoin-payment-gateway' ), $class_path ),
				)
			);
		}

		if ( ! method_exists( $class_path, $method[1] ) ) {
			wp_send_json(
				array(
					'status' => false,
					'title'  => __( 'Invalid Method', 'woo-altcoin-payment-gateway' ),
					'text'   => sprintf( __( 'Method "%1$s" not found in Class "%2$s"! ', 'woo-altcoin-payment-gateway' ), $method[1], $class_path ),
				)
			);
		}

		echo ( new $class_path() )->{$method[1]}( $data );
		exit;
	}

}
