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

use WooGateWayCoreLib\admin\builders\CsFormBuilder;
use WooGateWayCoreLib\admin\builders\CsFormHelperLib;
use WooGateWayCoreLib\admin\functions\CsPaymentGateway;
use WooGateWayCoreLib\admin\builders\CsAdminPageBuilder;


class ProductPageOptions {

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

		$this->settings = CsPaymentGateway::get_product_page_options();

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

		

		// pre_print(
		// 	$settings
		// );

		$fields = array(
			'st1'                                     => array(
				'type'     => 'section_title',
				'title'    => __( 'Offer Message Text', 'woo-altcoin-payment-gateway' ),
				'desc_tip' => __( 'Please use the following options to change offer notification shown in the single product page', 'woo-altcoin-payment-gateway' ),
			),
			'cs_altcoin_config[offer_msg_blink_text]' => array(
				'title'       => __( 'Offer Blink Text', 'woo-altcoin-payment-gateway' ),
				'type'        => 'text',
				'class'       => 'form-control',
				'required'    => true,
				'value'       => CsFormBuilder::get_value( 'offer_msg_blink_text', $this->settings, 'Special Offers Available!' ),
				'placeholder' => __( 'Enter offer blink text', 'woo-altcoin-payment-gateway' ),
				'desc_tip'    => __( 'Enter offer notification blink text, this text will diplay top of the add to cart button in single product page', 'woo-altcoin-payment-gateway' ),
			),
			'cs_altcoin_config[offer_msg_text]'       => array(
				'title'       => __( 'Offer Notification Box Title Text', 'woo-altcoin-payment-gateway' ),
				'type'        => 'text',
				'class'       => 'form-control',
				'required'    => true,
				'value'       => CsFormBuilder::get_value( 'offer_msg_text', $this->settings, 'You will get special discount, if you pay with following AltCoins' ),
				'placeholder' => __( 'Enter offer message text', 'woo-altcoin-payment-gateway' ),
				'desc_tip'    => __( 'Enter offer notification text, this text will diplay top of the add to cart button in single product page', 'woo-altcoin-payment-gateway' ),
			),
			'st2'                                     => array(
				'type'     => 'section_title',
				'title'    => __( 'Show Live Coin Price', 'woo-altcoin-payment-gateway' ),
				'desc_tip' => __( 'Following options will enable you to show live coins price beside your product price', 'woo-altcoin-payment-gateway' ),
			),
			'cs_altcoin_config[show_live_price]'      => array(
				'title'     => __( 'Enable / Disable', 'woo-altcoin-payment-gateway' ),
				'type'      => 'checkbox',
				'value'     => 'yes',
				'has_value' => CsFormBuilder::get_value( 'show_live_price', $this->settings, '' ),
				'desc_tip'  => __( 'Enable this option to show live coin price beside product price', 'woo-altcoin-payment-gateway' ),
			),
			'show_live_coin_list[]'                   => array(
				'title'       => __( 'Select Coin', 'woo-altcoin-payment-gateway' ),
				'type'        => 'select',
				'class'       => 'form-control live_price_coins',
				'multiple'    => true,
				'placeholder' => __( 'Please select coin', 'woo-altcoin-payment-gateway' ),
				'options'     => CsFormHelperLib::get_all_active_coins(),
				'value'       => CsFormBuilder::get_value( 'show_live_coin_list', $this->settings, '' ),
				'desc_tip'    => __( 'Select / Enter coin name to show for live price. e.g : Bitcoin', 'woo-altcoin-payment-gateway' ),
			),
			'cs_altcoin_config[variable_product_price_type]' => array(
				'title'       => __( 'Variable Product Price', 'woo-altcoin-payment-gateway' ),
				'type'        => 'select',
				'class'       => 'form-control coin-type-select',
				'required'    => true,
				'placeholder' => __( 'Select price type', 'woo-altcoin-payment-gateway' ),
				'options'     => array(
					'min'  => __( 'Show Min Price', 'woo-altcoin-payment-gateway' ),
					'max'  => __( 'Show Max Price', 'woo-altcoin-payment-gateway' ),
					'both' => __( 'Show Both Price', 'woo-altcoin-payment-gateway' ),
				),
				'value'       => CsFormBuilder::get_value( 'variable_product_price_type', $this->settings, 'both' ),
				'desc_tip'    => __( 'Please select if you want to show just min / max or both prices converted to cryptocurrencies. Default: both', 'woo-altcoin-payment-gateway' ),
			),
		);

		$args['content'] = $this->Form_Generator->generate_html_fields( $fields );

		$hidden_fields = array(
			'method'     => array(
				'id'    => 'method',
				'type'  => 'hidden',
				'value' => "admin\\functions\\CsPaymentGateway@save_product_page_options",
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

					$('.live_price_coins').val(<?php echo implode( ',', $this->settings['show_live_coin_list']); ?>).trigger('change');
				});
			</script>
		<?php
	}

}
