<?php namespace WooGateWayCoreLib\admin\functions;
/**
 * Settings
 * 
 * @package WAPG Admin 
 * @since 1.0.0
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    exit;
}

use WooGateWayCoreLib\lib\Util;
use WooGateWayCoreLib\admin\builders\CsWapgForm;
use WooGateWayCoreLib\frontend\scripts\CsWapgScript;
use WooGateWayCoreLib\lib\cartFunctions;

class CsWapgFunctions extends \WC_Payment_Gateway{
    
    /**
     * Hold the loader icon url
     */
    public $loader_icon;
    
    /**
     * Hold Default option value
     *
     * @var type 
     */
    public $defaultOptn;
    
    function __construct() {
        // global ID
        $this->id = "wapg_altcoin_payment";

        // Show Title
        $this->method_title = __( "AltCoin Payment", 'woo-altcoin-payment-gateway' );

        // Show Description
        $this->method_description = __( "AltCoin Payment Gateway Plugin for WooCommerce.", 'woo-altcoin-payment-gateway' );

        // vertical tab title
        $this->title = __( "AltCoin Payment", 'woo-altcoin-payment-gateway' );

        //get icons
        $this->icon = $this->get_icon_url();
        $this->loader_icon = $this->get_loader_url();
        
        $this->defaultOptn = get_option( $this->get_option_key() );

        
        $this->has_fields = true;

        // setting defines
        CsWapgForm::getAdminSettings( $this );

        // load time variable setting
        $this->init_settings();

        // Turn these settings into variables we can use
        foreach ( $this->settings as $setting_key => $value ) {
                $this->$setting_key = $value;
        }

        // Save settings
        if ( is_admin() ) {
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ), 5 );
        }		
    } // Here is the  End __construct()
 
    /**
     * Process payment
     * 
     * @param type $order_id
     * @return boolean
     */
    public function process_payment( $order_id ) {
        $order = wc_get_order( $order_id );
        $payment_confirm = isset($_POST['payment_confirm']) ? $this->validate_text_field( false, $_POST['payment_confirm']) : '';
        $payment_info = $this->validate_text_field( false, $_POST['payment_info'] );
        $reference_trxid = $this->validate_text_field( false, $_POST['user_alt_address']);
        
        if( empty($payment_info)){
            wc_add_notice( __( 'Sorry! Something went wrong. Please refresh this page and try again.', 'woo-altcoin-payment-gateway'), 'error' );
            return false;
        }
        $payment_info = explode( '__', $payment_info);
        if( empty( $reference_trxid ) ){
            wc_add_notice( sprintf(__( 'Please enter your %s transaction reference or trxID.', 'woo-altcoin-payment-gateway'), $payment_info[ 2 ] ), 'error' );
            return false;
        }
        if( empty($payment_confirm)){
            wc_add_notice( __( 'Please click the confirmation checkbox that you have already transfered the coin successfully!', 'woo-altcoin-payment-gateway'), 'error' );
            return false;
        }

        //save order info
        cartFunctions::save_payment_info( $order_id, array(
            'coin_db_id' => sanitize_text_field($_POST['altcoin']),
            'special_discount' => isset($_POST['special_discount_amount']) ? $_POST['special_discount_amount'] : '',
            'cart_total' => $order->get_order_item_totals()['order_total']['value'],
            'coin_name' => $payment_info[2],
            'total_coin' => $payment_info[1],
            'coin_price' => $payment_info[4],
            'ref_trxid' => $reference_trxid,
            'your_address' => $payment_info[3],
            'datetime' => Util::get_current_datetime()
        ));
        
        $order->update_status('on-hold', __( 'Awaiting for admin payment confirmation checking.', 'woo-altcoin-payment-gateway' ) );

        // Reduce stock levels
        wc_reduce_stock_levels( $order_id );

        // Remove cart
        WC()->cart->empty_cart();

        // Return thankyou redirect
        return array(
                'result' 	=> 'success',
                'redirect'	=> $this->get_return_url( $order )
        );
    }    
    
    /**
     * Get Icon
     * 
     * @return type
     */
    public function get_icon_url(){
        if( isset($this->defaultOptn['payment_icon_url']) && !empty($this->defaultOptn['payment_icon_url'])){
            return trim($this->defaultOptn['payment_icon_url']);
        }else{
            return CS_WAPG_PLUGIN_ASSET_URI .'img/icon-24x24.png';
        }
    }
    
    /**
     * Get Icon
     * 
     * @return type
     */
    public function get_loader_url(){
        if( isset($this->defaultOptn['loader_gif_url']) && !empty($this->defaultOptn['loader_gif_url'])){
            return trim($this->defaultOptn['loader_gif_url']);
        }else{
            return CS_WAPG_PLUGIN_ASSET_URI .'img/calc_hand.gif';
        }
    }
    
    /**
     * Generate payment form
     * 
     * @return String
     */
    public function payment_fields(){
        new CsWapgScript( $this );
        return CsWapgForm::customForm( $this );
    }
    
    /**
     * Admin Options Generator
     * 
     * @return String
     */
    function admin_options() {
        echo '<h2>' . esc_html( $this->get_method_title() ) . '</h2>';
        echo wp_kses_post( wpautop( $this->get_method_description() ) );
        echo is_checkout();
     ?>
         <table class="form-table">
             <?php $this->generate_settings_html(); ?>
             <tbody>
                 <tr>
                     <td></td>
                     <td style='text-align: left;'><a href="<?php echo admin_url('admin.php?page=cs-woo-altcoin-gateway-settings'); ?>" class='button-secondary'> <?php _e( 'Advance Settings', 'woo-altcoin-payment-gateway' ); ?>  >></a></td>
                 </tr>
             </tbody>
         </table> 
         <?php
    }
    
}
