<?php namespace WooGateWayCoreLib\Actions;

/**
 * Class: Woocommerce Default Hooks
 * 
 * @package Admin
 * @since 1.0.0
 * @author CodeSolz <customer-support@codesolz.net>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    die();
}

use WooGateWayCoreLib\lib\Util;
use WooGateWayCoreLib\admin\functions\CsOrderDetails;
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
        add_action( 'woocommerce_order_details_after_order_table', array( $this, 'wapg_order_summary'), 20 );
        
        add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'wapg_special_discount_offer_box'), 10 );
        
        /*** Adding Meta container in admin shop_order page ***/
        add_action( 'add_meta_boxes', array( $this, 'wapg_order_coin_details_metabox' ) );
        
        /*** crypto price after product price ***/
        add_filter( 'woocommerce_get_price_html', array( $this, 'wapg_wc_price_html' ), 10, 2 );
        
        /*** instance of user order details ***/
        $this->Thank_You_Page = new CsWapgCustomTy();
        
        /*** instance of admin order detail ***/
        $this->Cs_Order_Detail = new CsOrderDetails();
    }
    
    /**
     * 
     * @return typeReturn order summery in thank you page
     */
    public function wapg_order_summary( $order ){
        return $this->Thank_You_Page->order_summary( $order );
    }
    
    /**
     * Special discount box
     * 
     * @return type
     */
    public function wapg_special_discount_offer_box(){
        return CsWapgCustomBlocks::special_discount_offer_box();
    }
    
    /**
     * 
     * @global type $post
     * @return stringCoin detail on admin order page
     * 
     * @return string
     */
    public function wapg_order_coin_details_metabox(){
        global $post;
        
        if( isset( $post->post_type ) && $post->post_type != 'shop_order' ){
            return;
        }
        
        $order_id = isset( $post->ID ) ? $post->ID : Util::check_evil_script($_GET['post']);
        // Get an instance of the WC_Order object
        $order = wc_get_order( $order_id );
        if( $order->get_payment_method() != 'wapg_altcoin_payment' ){
            return;
        }
        
        add_meta_box( 'cs_coin_detail', sprintf( __( ' %s - Coin Details', 'woo-altcoin-payment-gateway' ), $order->get_payment_method_title() ), array($this->Cs_Order_Detail, 'order_metabox_coin_details'), 'shop_order', 'normal', 'core' );
    }
    
    /**
     * add crypto price after product price
     * 
     * @since 1.2.8
     * @return string Description
     */
    public function wapg_wc_price_html( $price, $obj ){
        $CsMiscellaneous = CsMiscellaneous::getInstance();
        return $CsMiscellaneous->show_coin_price( $price, $obj );
    }
    
}
