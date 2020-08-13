<?php namespace WooGateWayCoreLib\admin\builders;

/**
 * Class: Admin Pages
 *
 * @package Admin
 * @since 1.0.9
 * @author CodeSolz <customer-support@codesolz.net>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
	die();
}

use WooGateWayCoreLib\lib\Util;

class CsFormHelperLib {

	/**
	 * Gateway order confirmation
	 *
	 * @return type
	 */
	public static function order_confirm_options() {
		$options = array(
			'1' => __( 'Manual', 'woo-altcoin-payment-gateway' ),
			'2' => __( 'Automatic', 'woo-altcoin-payment-gateway' ),
		);
		if ( has_filter( 'filter_cs_wapg_order_confirm_options' ) ) {
			$options = apply_filters( 'filter_cs_wapg_order_confirm_options', $options );
		}

		return $options;
	}

	/**
	 * Get all active coin list
	 */
	public static function get_all_active_coins() {
		global $wapg_current_db_version, $wpdb, $wapg_tables;
		$coins = $wpdb->get_results( " select * from `{$wapg_tables['coins']}` where status = 1" );
		if ( $coins ) {
			$ret = array();
			foreach ( $coins as $coin ) {
				$ret += array(
					$coin->coin_web_id . '___' . $coin->symbol . '___' . $coin->coin_type => $coin->name . ' (' . $coin->symbol . ')',
				);
			}
			return $ret;
		}
		return array( '0' => 'Please at first add coin from "Add New Coin" Menu' );
	}

}
