<?php namespace WooGateWayCoreLib\admin\notices;
/**
 * Admin Notice
 * 
 * @package Notices 
 * @since 1.3.8
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    exit;
}

use WooGateWayCoreLib\lib\Util;
use WooGateWayCoreLib\admin\notices\wapgNotice;

class wapgAdminNotice {

    public static $pro_url = 'https://coinmarketstats.online/product/woocommerce-bitcoin-altcoin-payment-gateway';

    /**
     * Upgrade Notice
     *
     * @return String
     */
    public static function upgrade(){
        $notice = wapgNotice::get_instance();
        $message = __( 'You are using free manual version. There is a pro version which can track your order and coin transfer automatically. Let\'s check the  %s trial version of pro plugin%s', 'woo-altcoin-payment-gateway' );
        $register_link = Util::cs_generate_admin_url('cs-woo-altcoin-automatic-order-confirmation-settings');
        $message = sprintf( $message, '<a href="'.$register_link.'"><strong>', '</strong></a>' );
        $notice->info( $message, 'upgrade' );
    }

    /**
     * WooCommerce Core Plugin 
     * Missing Notice
     *
     * @return string
     */
    public static function woocommerce_missing(){
        $notice = wapgNotice::get_instance();
        $message = sprintf( __( 'In order to use %s' . CS_WAPG_PLUGIN_NAME . '%s plugin at first you need to install WooCommerce plugin.', 'woo-altcoin-payment-gateway' ), '<strong>', '</strong>' );
        $notice->error( $message );
    }

    /**
     * show If trial period has ended
     *
     * @return string
     */
    public static function is_trial_ended(){
        if ( get_option( "wapg_pro_trial_ended" ) ) {
            $notice = wapgNotice::get_instance();
            $message = __( 'Your API has been expired. Automatic coin tracking not working anymore on your store.  If you were using trial version %1$sPurchase pro plan%2$s to re-activate automatic tracking or %1$sPurchase new plan%2$s to extend validity if you were already using pro package.
            To continue using free version, %3$sdelete the coins%2$s which has been listed as automatic from %3$sall coins list%2$s. ', 'woo-altcoin-payment-gateway' );
            
            $all_coins_link = Util::cs_generate_admin_url('cs-woo-altcoin-all-coins');
            $message = sprintf( $message, '<a href="' . self::$pro_url . '"><strong>', '</strong></a>', '<a href="' . $all_coins_link . '"><strong>' );
            $notice->error( $message, 'trial_ended' );
        }
        return;
    }
    
}