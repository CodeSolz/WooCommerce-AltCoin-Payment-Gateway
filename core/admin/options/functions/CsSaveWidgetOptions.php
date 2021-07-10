<?php namespace WooGateWayCoreLib\admin\options\functions;

/**
 * Class: Save widget options
 *
 * @package functions
 * @since 1.5.9
 * @author CoinMarketStats <support@coinmarketstats.online>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
	die();
}

use WooGateWayCoreLib\lib\Util;

class CsSaveWidgetOptions{

    private static $cs_widget_options = 'CsWidgetsOptions';

    /**
     * Save widget options
     */
    public function save_widget_options(){
		$selected_coins = Util::check_evil_script( $_POST['show_coins'] );

		$config = array( 'altcoin_display_coin_prices' => array( 'show_coins' => $selected_coins ) );
		update_option( self::$cs_widget_options, $config, 'yes' );

		return wp_send_json(
			array(
				'status' => true,
				'title'  => __( 'Success', 'woo-altcoin-payment-gateway' ),
				'text'   => __( 'Your settings have been saved.', 'woo-altcoin-payment-gateway' ),
			)
		);
    }

    /**
     * Get widget options
     */
    public static function get_widget_options(){
        return get_option( self::$cs_widget_options );
    }

}