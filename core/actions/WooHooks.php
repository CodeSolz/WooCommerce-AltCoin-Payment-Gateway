<?php namespace WooGateWayCoreLib\actions;

/**
 * Class: Woocommerce Default Hooks
 *
 * @package Admin
 * @since 1.0.0
 * @author CoinMarketStats <support@coinmarketstats.online>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
	die();
}

use WooGateWayCoreLib\lib\Util;
use WooGateWayCoreLib\admin\functions\WooFunctions;
use WooGateWayCoreLib\admin\functions\widget\CsPriceDisplay;
use WooGateWayCoreLib\admin\functions\CsOrderDetails;
use WooGateWayCoreLib\admin\options\Scripts_Settings;
use WooGateWayCoreLib\frontend\functions\CsWapgCustomTy;
use WooGateWayCoreLib\frontend\functions\CsMiscellaneous;
use WooGateWayCoreLib\frontend\functions\CsWapgCustomBlocks;

class WooHooks {

	/**
	 * Hold user order details
	 *
	 * @var type
	 */
	private $Thank_You_Page;

	/**
	 * Hold admin order details
	 *
	 * @var type
	 */
	private $Cs_Order_Detail;

	function __construct() {

		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'wapg_order_summary' ), 20 );

		add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'wapg_special_discount_offer_box' ), 10 );

		/*** Adding Meta container in admin shop_order page */
		add_action( 'add_meta_boxes', array( $this, 'wapg_order_coin_details_metabox' ) );

		/*** crypto price after product price */
		add_filter( 'woocommerce_get_price_html', array( $this, 'wapg_wc_price_html' ), 20, 2 );


		/*** check plugins info */
		add_action( 'wp_update_plugins', array( $this, 'wapg_check_plugin_info' ) );

		/*** add settings link */
		add_filter( 'plugin_action_links_' . CS_WAPG_PLUGIN_IDENTIFIER, array( __class__, 'wapg_add_plugin_page_settings_link' ) );

		/*** add info link */
		add_filter( 'plugin_row_meta', array( __class__, 'wapg_plugin_row_meta' ), 12, 2 );

		/*** instance of user order details */
		$this->Thank_You_Page = new CsWapgCustomTy();

		/*** instance of admin order detail */
		$this->Cs_Order_Detail = new CsOrderDetails();
		
		/*** register widget */
		add_action( 'widgets_init', function(){
			register_widget( 'WooGateWayCoreLib\admin\functions\widget\CsPriceDisplay' );
		});
		
	}

	/**
	 *
	 * @return typeReturn order summery in thank you page
	 */
	public function wapg_order_summary( $order ) {
		return $this->Thank_You_Page->order_summary( $order );
	}

	/**
	 * Special discount box
	 *
	 * @return type
	 */
	public function wapg_special_discount_offer_box() {
		return CsWapgCustomBlocks::special_discount_offer_box();
	}

	/**
	 *
	 * @global type $post
	 * @return stringCoin detail on admin order page
	 *
	 * @return string
	 */
	public function wapg_order_coin_details_metabox() {
		global $post;

		if ( isset( $post->post_type ) && $post->post_type != 'shop_order' ) {
			return '';
		}

		$order_id = isset( $post->ID ) ? $post->ID : Util::check_evil_script( $_GET['post'] );
		// Get an instance of the WC_Order object
		if( ! function_exists('wc_get_order') ){
			return '';
		}
		$order = wc_get_order( $order_id );
		if ( 
			\method_exists( $order, 'get_payment_method' ) &&
			false !== $order->get_payment_method() && 
			$order->get_payment_method() != 'wapg_altcoin_payment' 
			) {
			return '';
		}

		add_meta_box( 'cs_coin_detail', sprintf( __( ' %s - Coin Details', 'woo-altcoin-payment-gateway' ), $order->get_payment_method_title() ), array( $this->Cs_Order_Detail, 'order_metabox_coin_details' ), 'shop_order', 'normal', 'core' );
	}

	/**
	 * add crypto price after product price
	 *
	 * @since 1.2.8
	 * @return string Description
	 */
	public function wapg_wc_price_html( $price, $obj ) {
		$CsMiscellaneous = CsMiscellaneous::getInstance();
		return $CsMiscellaneous->add_coin_price_placeholder( $price, $obj );
	}

	/**
	 * check plugins info
	 *
	 * @return void
	 */
	public function wapg_check_plugin_info() {
		return ( new WooFunctions() )->wapg_get_plugins_info();
	}

	/**
	 * Add settings links
	 *
	 * @param [type] $links
	 * @return void
	 */
	public static function wapg_add_plugin_page_settings_link( $links ) {
		$links[] = '<a href="' .
		Util::cs_generate_admin_url( 'cs-woo-altcoin-gateway-settings' ) .
		'">' . __( 'Settings' ) . '</a>';
		$links[] = '<a href="' .
		Util::cs_generate_admin_url( 'cs-woo-altcoin-add-new-coin' ) .
		'">' . __( 'Add New Coin' ) . '</a>';

		return $links;
	}


	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param mixed $links Plugin Row Meta.
	 * @param mixed $file  Plugin Base file.
	 *
	 * @return array
	 */
	public static function wapg_plugin_row_meta( $links, $file ) {
		if ( CS_WAPG_PLUGIN_IDENTIFIER === $file ) {
			$row_meta = array(
				'docs'    => '<a href="' . esc_url( 'https://docs.coinmarketstats.online/docs/woocommerce-bitcoin-altcoin-payment-gateway/' ) . '" target = "_blank" aria-label="' . esc_attr__( 'View documentation', 'woo-altcoin-payment-gateway' ) . '">' . esc_html__( 'Docs', 'woo-altcoin-payment-gateway' ) . '</a>',
				'support' => '<a href="' . esc_url( 'https://coinmarketstats.online' ) . '" target = "_blank" aria-label="' . esc_attr__( 'Premium support', 'woo-altcoin-payment-gateway' ) . '">' . esc_html__( 'Premium support', 'woo-altcoin-payment-gateway' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	


}
