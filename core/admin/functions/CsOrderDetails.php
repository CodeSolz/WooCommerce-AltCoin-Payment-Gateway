<?php namespace WooGateWayCoreLib\admin\functions;

/**
 * Order Details
 *
 * @package WAPG Admin
 * @since 1.0.0
 * @author CoinMarketStats <support@coinmarketstats.online>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
	exit;
}

use WooGateWayCoreLib\lib\Util;
use WooGateWayCoreLib\lib\cartFunctions;

class CsOrderDetails {

	/**
	 * metabox coin details
	 *
	 * @param type $post
	 */
	public function order_metabox_coin_details( $post ) {
		$order_id        = isset( $post->ID ) ? $post->ID : Util::check_evil_script( $_GET['post'] );
		$payment_details = cartFunctions::get_payment_info( $order_id );
		?>
		<table class="cs-coin-info">
			<thead>
				<tr>
					<th><?php _e( 'Coin', 'woo-altcoin-payment-gateway' ); ?></th>
					<th><?php _e( 'Total', 'woo-altcoin-payment-gateway' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<td> 
					<?php echo $payment_details['coin_name']; ?> &times; <?php echo $payment_details['total_coin']; ?> <br>
					( 1 <?php echo $payment_details['coin_name']; ?> = &#36;<?php echo $payment_details['coin_price']; ?> )
				</td>
				<td><?php echo $payment_details['cart_total']; ?></td>
			</tbody>
			<tfoot>
				<?php if ( ! empty( $payment_details['special_discount'] ) ) { ?>
					<tr>
						<td><?php _e( 'Special Discount', 'woo-altcoin-payment-gateway' ); ?></td>
						<td> <?php echo $payment_details['special_discount']; ?></td>
					</tr>
				<?php } ?>
				<tr>
					<th><?php _e( 'Subtotal', 'woo-altcoin-payment-gateway' ); ?></th>
					<td> <?php echo $payment_details['total_coin']; ?> - <?php echo $payment_details['coin_name']; ?></td>
				</tr>
				<tr>
					<th><?php _e( 'Total', 'woo-altcoin-payment-gateway' ); ?></th>
					<td> <?php echo $payment_details['total_coin']; ?> - <?php echo $payment_details['coin_name']; ?> </td>
				</tr>
			</tfoot>
		</table>
		<div class="cs-ref-info cs-ref-info-first">
			<div class="name"><?php _e( 'Coin Sent To(your altcoin address)', 'woo-altcoin-payment-gateway' ); ?></div>
			<div>:</div>
			<div><?php echo $payment_details['your_address']; ?></div>
		</div>    
		<div class="cs-ref-info">
			<div class="name"><?php _e( 'Transaction Reference / TrxID', 'woo-altcoin-payment-gateway' ); ?></div>
			<div>:</div>
			<div><?php echo $payment_details['ref_trxid']; ?></div>
		</div>    
		<?php
		$this->metabox_style();
	}


	/**
	 * metabox styling
	 */
	private function metabox_style() {
		?>
			<style type="text/css">
				.cs-coin-info{ width:100%; text-align:left; border-collapse:collapse; }
				.cs-reference-fields{ background: #fab13f; }
				.cs-ref-info{ display: flex; }
				.cs-ref-info .name{ font-weight: bold; color: #adb5bd; }
				.cs-ref-info div:first-child{ width: 220px;}
				.cs-ref-info div:nth-child(2){ width: 8px;}
				.cs-ref-info-first{margin-top: 15px;border-top: 1px dashed;padding-top: 15px;}
			</style>    
		<?php
	}

}
