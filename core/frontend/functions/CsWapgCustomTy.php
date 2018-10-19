<?php namespace WooGateWayCoreLib\frontend\functions;
/**
 * Frontend Functions
 * 
 * @package WAPG Admin 
 * @since 1.0.0
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    exit;
}

use WooGateWayCoreLib\lib\cartFunctions;

class CsWapgCustomTy {
    
    /**
     * Order summery in thank you page
     * 
     * @param type $order
     */
    public function order_summary( $order ){
        $payment_details = cartFunctions::get_payment_info( $order->id );
        
        ?>
        <h2><?php _e( 'Coin Details', 'woo-altcoin-payment-gateway' ); ?></h2>
        <table class="woocommerce-table shop_table coin_info">
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
        <?php
    }
    
    
 
   public function misha_view_order_and_thankyou_page( $order_id ){  
    ?>
    <h2>Gift Order</h2>
    <table class="woocommerce-table shop_table gift_info">
        <tbody>
            <tr>
                <th>Is gift?</th>
                <td><?php echo ( $is_gift = get_post_meta( $order_id, 'is_gift', true ) ) ? 'Yes' : 'No'; ?></td>
            </tr>
            <?php if( $is_gift ) : ?>
            <tr>
                <th>Gift Wrap</th>
                <td><?php echo get_post_meta( $order_id, 'gift_wrap', true ); ?></td>
            </tr>
            <tr>
                <th>Recipient name</th>
                <td><?php echo get_post_meta( $order_id, 'gift_name', true ); ?></td>
            </tr>
            <tr>
                <th>Gift message</th>
                <td><?php echo wpautop( get_post_meta( $order_id, 'gift_message', true ) ); ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php 
   }
}
