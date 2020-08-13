<?php namespace WooGateWayCoreLib\admin\builders;

/**
 * From Builder
 *
 * @package WAPG Admin
 * @since 1.0.0
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
	exit;
}

use WooGateWayCoreLib\admin\functions\CsAdminQuery;


class CsWapgForm {

	/**
	 * Admin Settings Form
	 *
	 * @param type $obj
	 * @return type
	 */
	public static function getAdminSettings( $obj ) {
		return $obj->form_fields = array(
			'enabled'     => array(
				'title'   => __( 'Enable / Disable', 'woo-altcoin-payment-gateway' ),
				'label'   => __( 'Enable AltCoin payment gateway', 'woo-altcoin-payment-gateway' ),
				'type'    => 'checkbox',
				'default' => 'no',
			),
			'title'       => array(
				'title'    => __( 'Title', 'woo-altcoin-payment-gateway' ),
				'type'     => 'text',
				'desc_tip' => __( 'Payment title of checkout process.', 'woo-altcoin-payment-gateway' ),
				'default'  => __( 'AltCoin', 'woo-altcoin-payment-gateway' ),
			),
			'description' => array(
				'title'    => __( 'Description', 'woo-altcoin-payment-gateway' ),
				'type'     => 'textarea',
				'desc_tip' => __( 'Payment title of checkout process.', 'woo-altcoin-payment-gateway' ),
				'default'  => __( 'Make your payment directly into our AltCoin address. Your order won’t be shipped until the funds have cleared in our account.', 'woo-altcoin-payment-gateway' ),
				'css'      => 'max-width:450px;',
			),
		);
	}

	/**
	 * Generate Custom Form
	 *
	 * @param type $refObj
	 */
	public static function customForm( $refObj ) {
		global $wp;

		$active_coins = CsAdminQuery::get_coins( array( 'where' => ' c.status = 1 ' ) );
		if ( empty( $active_coins ) ) {
			_e( 'Sorry! No AltCoin is activate! Please contact administration for more information.', 'woo-altcoin-payment-gateway' );
			return;
		}

		if ( ( isset( $refObj->description ) && ! empty( $description = $refObj->description ) ) ||
			( isset( $refObj->defaultOptn['description'] ) && ! empty( $description = $refObj->defaultOptn['description'] ) ) ) {
			echo wpautop( wptexturize( $description ) );
		}
		$fields = array();

		$label          = isset( $refObj->select_box_lebel ) && ! empty( $refObj->select_box_lebel ) ? $refObj->select_box_lebel : __( 'Please select coin you want to pay:', 'woo-altcoin-payment-gateway' );
		$default_fields = array(
			'alt-con' => '<p class="form-row form-row-wide altCoinSelect">
                        <label for="' . esc_attr( $refObj->id ) . '-alt-name">' . $label . ' <span class="required">*</span></label>' .
					self::getActiveAltCoinSelect( $refObj, $active_coins )
			. '</p><div class="coin-detail"><!--coin calculation--></div>',
		);

		$fields = wp_parse_args( $fields, apply_filters( 'woocommerce_altcoin_form_fields', $default_fields, $refObj->id ) );

		$premade_order_id = isset( $wp->query_vars['order-pay'] ) ? $wp->query_vars['order-pay'] : 0;
		?>
		<fieldset id="wc-<?php echo esc_attr( $refObj->id ); ?>-cc-form" class='wc-altcoin-form wc-payment-form'>
				<input type="hidden" name="is_premade_order" id="is_premade_order" value="<?php echo $premade_order_id; ?>" />
				<?php do_action( 'woocommerce_altcoin_form_start', $refObj->id ); ?>
				<?php
				foreach ( $fields as $field ) {
						echo $field;
				}
				?>
				<?php do_action( 'woocommerce_altcoin_form_end', $refObj->id ); ?>
				<div class="clear"></div>
		</fieldset>
		<?php
	}

	/**
	 * Output field name HTML
	 *
	 * Gateways which support tokenization do not require names - we don't want the data to post to the server.
	 *
	 * @since  2.6.0
	 * @param  string $name
	 * @return string
	 */
	public static function field_name( $id, $name ) {
		return ' name="' . esc_attr( $id . '-' . $name ) . '" ';
	}

	/**
	 * Active altCoins List - checkout page
	 *
	 * @param type $refObj
	 * @return type
	 */
	public static function getActiveAltCoinSelect( $refObj = false, $active_coins ) {
		$altCoin  = '<select name="altcoin" id="CsaltCoin" class="select">';
		$lebel    = isset( $refObj->select_box_option_lebel ) && ! empty( $refObj->select_box_option_lebel ) ? $refObj->select_box_option_lebel : __( 'Please Select An AltCoin', 'woo-altcoin-payment-gateway' );
		$altCoin .= '<option value="0">' . $lebel . '</option>';
		foreach ( $active_coins as $coin ) {
			$altCoin .= '<option value="' . $coin->cid . '">' . $coin->name . '(' . $coin->symbol . ')</option>';
		}
		return $altCoin .= '</select>';
	}
}

