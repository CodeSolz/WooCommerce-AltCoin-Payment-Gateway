<?php namespace WooGateWayCoreLib\actions;

/**
 * Class: Enqueue Scripts
 *
 * @package Admin
 * @since 1.5.9
 * @author CoinMarketStats <support@coinmarketstats.online>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
	die();
}


class WapgEnqueueScripts{

    function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'wapg_frontend_scripts' ), 15 );
	}

    /**
     * Register scripts
     *
     * @return void
     */
    public function wapg_frontend_scripts(){

        wp_enqueue_script( 'wapg-ajax', CS_WAPG_PLUGIN_ASSET_URI . 'js/wapg_ajax.js', array( 'jquery' ), CS_WAPG_VERSION );
		wp_localize_script(
			'wapg-ajax',
			'wapg_ajax',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'cs_token' => wp_create_nonce( SECURE_AUTH_SALT ),
				'action' => '_cs_wapg_custom_call',
				'pl_gif' => CS_WAPG_PLUGIN_ASSET_URI . 'img/price-loader.gif',
				'lp' => array(
					'cpM' => 'frontend\\functions\\CsMiscellaneous@get_crypto_prices',
					'wLcP' => 'frontend\\functions\\widgets\\CsWidgets@widget_get_live_coin_prices'
				)
			)
		);

		wp_enqueue_script( 'wapg-app-main', CS_WAPG_PLUGIN_ASSET_URI . 'js/wapg_app.min.js', array(), CS_WAPG_VERSION, true );

        wp_enqueue_script( 'wapg-app-widgets', CS_WAPG_PLUGIN_ASSET_URI . 'js/cs.widgets.min.js', array(), CS_WAPG_VERSION, true );

        wp_enqueue_style( 'wapg-widgets',
            CS_WAPG_PLUGIN_ASSET_URI . '/css/widgets.min.css',
            array(),
            CS_WAPG_VERSION
        );

    }



}