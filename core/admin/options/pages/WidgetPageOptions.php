<?php namespace WooGateWayCoreLib\admin\options\pages;

/**
 * Class: Add New Coin
 *
 * @package Admin
 * @since 1.2.4
 * @author CoinMarketStats <support@coinmarketstats.online>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
	die();
}

use WooGateWayCoreLib\lib\Util;
use WooGateWayCoreLib\admin\builders\CsFormBuilder;
use WooGateWayCoreLib\admin\builders\CsFormHelperLib;
use WooGateWayCoreLib\admin\builders\CsAdminPageBuilder;
use WooGateWayCoreLib\admin\functions\CsPaymentGateway;
use WooGateWayCoreLib\admin\options\functions\CsSaveWidgetOptions;


class WidgetPageOptions {

	/**
	 * Hold page generator class
	 *
	 * @var type
	 */
	private $Admin_Page_Generator;

	/**
	 * Form Generator
	 *
	 * @var type
	 */
	private $Form_Generator;

	/**
	 * Set current page dat
	 */
	private $settings;


	public function __construct( CsAdminPageBuilder $AdminPageGenerator ) {
		$this->Admin_Page_Generator = $AdminPageGenerator;

		$this->settings = CsSaveWidgetOptions::get_widget_options();

		/*create obj form generator*/
		$this->Form_Generator = new CsFormBuilder();

		add_action( 'admin_footer', array( $this, 'default_page_scripts' ) );
	}

	/**
	 * Generate add new coin page
	 *
	 * @param type $args
	 * @return type
	 */
	public function generate_product_options_settings( $args ) {

		$fields = array(
			'st1'          => array(
				'type'     => 'section_title',
				'title'    => __( 'Widget : Display Coin Prices', 'woo-altcoin-payment-gateway' ),
				'desc_tip' => sprintf( __( 'Following options will take place on the widget %1$s Altcoin - Display Coin Prices %2$s ', 'woo-altcoin-payment-gateway' ), '<a href="' . \get_admin_url() . 'widgets.php">', '</a>' ),
			),
			'show_coins[]' => array(
				'title'       => __( 'Select Coin', 'woo-altcoin-payment-gateway' ),
				'type'        => 'select',
				'class'       => 'form-control live_price_coins',
				'multiple'    => true,
				'placeholder' => __( 'Please select coin', 'woo-altcoin-payment-gateway' ),
				'options'     => CsFormHelperLib::get_all_active_coins(),
				'value'       => CsFormBuilder::get_value( 'show_coins', $this->settings['altcoin_display_coin_prices'], '' ),
				'desc_tip'    => __( 'Select / Enter coin name to show on the widget. e.g : Bitcoin', 'woo-altcoin-payment-gateway' ),
			),

		);

		$args['content'] = $this->Form_Generator->generate_html_fields( $fields );

		$hidden_fields = array(
			'method'     => array(
				'id'    => 'method',
				'type'  => 'hidden',
				'value' => "admin\\options\\functions\\CsSaveWidgetOptions@save_widget_options",
			),
			'swal_title' => array(
				'id'    => 'swal_title',
				'type'  => 'hidden',
				'value' => 'Settings Updating',
			),

		);
		$args['hidden_fields'] = $this->Form_Generator->generate_hidden_fields( $hidden_fields );

		$args['btn_text']   = 'Save Settings';
		$args['show_btn']   = true;
		$args['body_class'] = 'no-bottom-margin';

		return $this->Admin_Page_Generator->generate_page( $args );
	}

	/**
	 * Add custom scripts
	 */
	public function default_page_scripts() {
		?>
			<script>
				jQuery(document).ready(function($) {
					$('.live_price_coins').select2();
				});
			</script>
		<?php
	}

}
