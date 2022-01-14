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

		add_action( 'admin_enqueue_scripts', array( $this, 'wapg_enqueue_admin_script' ), 15 );
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

	/**
	 * Form Submitter
	 *
	 * @param [type] $hook
	 * @return void
	 */
	public function wapg_enqueue_admin_script( $page_id ){
		global $altcoin_menu;

		//menu id
		$apply_script_on = array(
			'add_new_coin', 'default_settings', 'register_automatic_order', 
			'checkout_options_settings', 'product_page_options_settings', 
			'widget_options_settings'
		);

		$add_script_on = apply_filters( 'wapg_add_form_submitter_script', $apply_script_on );

		// load form submit script on footer
		if( $add_script_on ){
			foreach( $add_script_on as $menu_id ){
				if( isset( $altcoin_menu[ $menu_id ] ) && empty( $get_menu_id = $altcoin_menu[ $menu_id ]) ){
					continue;
				}
				if( $page_id == $get_menu_id ){
					// pre_print('hi dolly');

					wp_enqueue_script( 'wapg-form-submitter', CS_WAPG_PLUGIN_ASSET_URI . 'js/form.submitter.min.js', array(), CS_WAPG_VERSION, true );
					wp_localize_script(
						'wapg-form-submitter',
						'wapg_form_vars',
						array(
							'lgif' => CS_WAPG_PLUGIN_ASSET_URI . 'img/loading-timer.gif',
						)
					);
				}
			}
		}
		

	}



}