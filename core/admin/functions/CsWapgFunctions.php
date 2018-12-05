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

use WooGateWayCoreLib\admin\builders\CsWapgForm;
use WooGateWayCoreLib\frontend\scripts\CsWapgScript;
use WooGateWayCoreLib\lib\cartFunctions;

class CsWapgFunctions extends \WC_Payment_Gateway{
    
    /**
     * Custom fields Option Key
     *
     * @var type 
     */
    public $cs_altcoin_fields = 'cs_altcoin_fields';
    
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
        $this->method_description = __( "AltCoin Payment Gateway Plugin for WooCommerce. If you want to remove any active altcoin, just make the address field blank and save it.", 'woo-altcoin-payment-gateway' );

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


        add_action( 'admin_footer', array( $this, 'custom_script' ) );
                
        // Save settings
        if ( is_admin() ) {
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ), 5 );
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_custom_options' ), 10 );
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
        $payment_confirm = $this->validate_text_field( false, $_POST['payment_confirm']);
        $payment_info = $this->validate_text_field( false, $_POST['payment_info']);
        $user_alt_address = $this->validate_text_field( false, $_POST['user_alt_address']);
        if( empty($payment_info)){
            wc_add_notice( __( 'Sorry! Something went wrong. Please refresh this page and try again.', 'woo-altcoin-payment-gateway'), 'error' );
            return false;
        }
        $payment_info = explode( '__', $payment_info);
        if( empty( $user_alt_address ) ){
            wc_add_notice( sprintf(__( 'Please enter your %s address.', 'woo-altcoin-payment-gateway'), $payment_info[ 2 ] ), 'error' );
            return false;
        }
        if( empty($payment_confirm)){
            wc_add_notice( __( 'Please confirm the coin transfer!', 'woo-altcoin-payment-gateway'), 'error' );
            return false;
        }

        //save order info
        cartFunctions::save_payment_info( $order_id, array(
            'cart_total' => $order->get_order_item_totals()['order_total']['value'],
            'coin_name' => $payment_info[2],
            'total_coin' => $payment_info[1],
            'coin_price' => $payment_info[4],
            'user_address' => $user_alt_address,
            'your_address' => $payment_info[3]
        ));
        
        
        $note = sprintf(__( 'Order Info: Total Payment : %s has made on : %s at your address : %s . Coin price was: $%s. User %s address: %s . User confirmed that coin transfer was successfull!', 'woo-altcoin-payment-gateway'), $payment_info[1], $payment_info[2], $payment_info[3], $payment_info[4], $payment_info[2], $user_alt_address);
        
        $order->add_order_note( $note );
        $order->update_status('on-hold', __( 'Awaiting for admin payment confirmation checking.', 'woo-altcoin-payment-gateway' ) );

        // Reduce stock levels
        $order->reduce_order_stock();

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
            return CS_WAPG_PLUGIN_ASSET_URI .'/img/crypto-currency.jpg';
        }
    }
    
    /**
     * Get Icon
     * 
     * @return type
     */
    public function get_loader_url(){
        if( isset($this->defaultOptn['payment_icon_url']) && !empty($this->defaultOptn['loader_gif_url'])){
            return trim($this->defaultOptn['loader_gif_url']);
        }else{
            return CS_WAPG_PLUGIN_ASSET_URI .'/img/calc_hand.gif';
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
        $custom_fields = get_option( $this->cs_altcoin_fields );
        $custom_fields = empty($custom_fields) ? '' : json_decode($custom_fields);
        echo is_checkout();
     ?>
         <table class="form-table">
             <?php $this->generate_settings_html(); ?>
             <tbody id="add_extra_fields"><!--add more options--></tbody>
             <tbody>
                 <tr>
                     <td></td>
                     <td style='text-align: left;'><a  class='button-secondary btn-add-more-coin'>+ Add More Coin</a></td>
                 </tr>
             </tbody>
             <input type="hidden" id="more_field_count" name="more_field_count" value="<?php echo empty($custom_fields) ? 1 : (empty($custom_fields->count) ? 1 : $custom_fields->count); ?>" />
         </table> 
         <?php
    }
    
    /**
    * Admin Custom Footer Script
    * 
    * @return String
    */
    public function custom_script(){
        ?>
            <script>
                var module = {
                    addMoreField : function( id ){
                        var mt = '';
                        if( id > 2 ) { mt = 'block-mt'; }
                        return '<tr valign="top" id="altname_'+id+'" class="more-coin-fields '+mt+'">'+
                                        '<th scope="row" class="titledesc">'+
                                                '<label for=""><?php _e( 'Select AltCoin ', 'woo-altcoin-payment-gateway' ); ?></label>'+
                                                '<span class="woocommerce-help-tip"></span>'+
                                        '</th>'+
                                        '<td class="forminp">'+
                                                '<fieldset>'+
                                                        '<legend class="screen-reader-text"><span>select altcoin</span></legend>'+
                                                        '<select id="woocommerce_wapg_altcoin_payment_altCoinName_'+id+'" name="woocommerce_wapg_altcoin_payment_altCoinName_'+id+'" data-coinid="'+id+'" class="select alt-coin">'+
                                                        '<?php echo CsWapgForm::getAltCoinsSelect( 'html' ); ?>'+
                                                "</select>"+
                                                '<br><span class="description">Please wait a moment to appear the coin list after click.</span>'+
                                                '</fieldset>'+
                                        '</td>'+
                                        "<td><a data-rowid='"+id+"' class='remove'><span class='dashicons dashicons-trash'></span></a></td>"+
                                '</tr>'+
                                '<tr valign="top" id="altaddress_'+id+'" class="more-coin-fields">'+
                                        '<th scope="row" class="titledesc">'+
                                                '<label for=""><?php _e( 'Enter ', 'woo-altcoin-payment-gateway' ); ?><span class="altCoinVallabel_'+id+'"><?php _e( ' altcoin ', 'woo-altcoin-payment-gateway' ); ?></span><?php _e( ' address:', 'woo-altcoin-payment-gateway' ); ?></label>'+
                                        '</th>'+
                                        '<td class="forminp" colspan="2">'+
                                                '<fieldset>'+
                                                        '<legend class="screen-reader-text"><span>select altcoin</span></legend>'+
                                                        '<input class="input-text regular-input" type="text" name="woocommerce_wapg_altcoin_payment_altCoinAddress_'+id+'" id="woocommerce_wapg_altcoin_payment_altCoinAddress_'+id+'" style="" placeholder="<?php _e( 'enter here your altcon address', 'woo-altcoin-payment-gateway' ); ?>" />'+
                                                '</fieldset>'+
                                        '</td>'+
                                '</tr>';
                    }
                };
                
                jQuery(document).ready(function(){
                
                console.log( 'hi' );
                    jQuery("table").on( 'change', ".alt-coin" ,function(){
                        var val = jQuery(this).val();
                        if( parseInt(val) !== 0 ){
                            var id = jQuery(this).data('coinid');
                            var name= jQuery(this).find("option:selected").text(); 
                            jQuery(".altCoinVallabel_"+id).text( name );
                            jQuery(".altCoinPlaceHolder_"+id).text( name );
                        }
                    });
                    
                    jQuery('.btn-add-more-coin').on( 'click', function(){
                        var idCount = jQuery("#more_field_count").val();
                        idCount++;
                        jQuery("#add_extra_fields").append( module.addMoreField( idCount ) ).fadeIn('slow');
                        jQuery("#more_field_count").val( idCount );
                    });
                    
                    jQuery('table').on( 'click', '.remove', function(){
                        var rowid = jQuery(this).data("rowid");
                        jQuery("#altname_"+rowid).css({'background':'orangered'}).fadeTo("slow", 0.33).fadeOut('blind');
                        jQuery("#altaddress_"+rowid).css({'background':'orangered'}).fadeTo("slow", 0.33).fadeOut('blind');;
                    });
                    
                });
            </script>
            <style type="text/css">
                .btn-add-more-coin{ cursor: pointer; }
                .more-coin-fields{ background: aliceblue; }
                .more-coin-fields th{ padding-left: 15px; }
                .block-mt{ border-top: 5px solid #fff;}
                .remove{ color: red; cursor: pointer; }
            </style>
        <?php
    }
    
    
    /**
     * save custom options
     * 
     * @return boolean
     */
    public function process_admin_custom_options(){
        $more_field_count = isset($_POST['more_field_count']) ? $this->validate_text_field( false, $_POST['more_field_count']) : '';
        if( ! empty( $more_field_count ) ) {
            $getDefaultOptn = get_option( $this->get_option_key() );
            $name_id = "woocommerce_{$this->id}_altCoinName_";
            $address_id = "woocommerce_{$this->id}_altCoinAddress_";
            $options = array();
            $field_count = 0;
            for( $i=1; $i<=$more_field_count; $i++ ){
                if( isset($_POST["{$name_id}{$i}"])  && !empty ($address = $this->validate_text_field( false, $_POST["{$address_id}{$i}"] )) ){
                    $options[] = array(
                        'id' => $this->validate_text_field( false, $_POST["{$name_id}{$i}"] ),
                        'address' => $address
                    );
                    unset($getDefaultOptn["altCoinAddress_{$i}"]);    
                    unset($getDefaultOptn["altCoinName_{$i}"]);    
                    $field_count++;
                }
            }
            $options['count'] = $field_count;
            update_option( $this->get_option_key(), $getDefaultOptn);
            return update_option( $this->cs_altcoin_fields, json_encode( $options ) );
        }
        return;
    }
}
