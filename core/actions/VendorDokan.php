<?php namespace WooGateWayCoreLib\Actions;

/**
 * Class: Woocommerce Dokan Vendor Support
 * 
 * @package Actions
 * @since 1.4.4
 * @author CodeSolz <customer-support@codesolz.net>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    die();
}

use WooGateWayCoreLib\admin\functions\CsAdminQuery;


class VendorDokan {

    private $cs_payment_method_prefix = 'cs_dokan_withdraw_method_';
    private $cs_payment_field = 'cs_dokan_withdraw_method';
    private $cs_coin_address_placeholder = 'coin_address';
    
    function __construct(){
        add_filter( 'dokan_withdraw_methods', array( $this, 'cs_dokan_withdrawl_method'), 50 );
        add_filter( 'dokan_get_seller_active_withdraw_methods', array( $this, 'cs_dokan_seller_active_withdrawl_methods'), 10 );
        add_action( 'dokan_store_profile_saved', array( $this, 'cs_dokan_payment_settings'), 20, 2 );
    }

     /**
      * Add WooCommerce Altcoin Methods 
      *
      * @param [type] $methods
      * @return void
      */
    public function cs_dokan_withdrawl_method( $methods ){
        global $wpdb, $wapg_tables;
        $coins = $wpdb->get_results( "select * from {$wapg_tables['coins']} order by name desc" );
        if( $coins ){
            foreach ( $coins as $coin ) {
                $coinMethods = [];
                if( ! empty( $coin->name  ) && ! in_array( $coinMethods, $coin->coin_web_id) ){
                    $methods[ $coin->coin_web_id ] = array (
                        'title'    =>   $coin->name,
                        'callback' => array( $this, $this->cs_payment_method_prefix . $coin->coin_web_id )
                    );
                    $coinMethods += array( $coin->coin_web_id );
                }

            }
        }
        return $methods;
    }

    /**
     * Get seller active withdrawl methods
     *
     * @param [type] $active_payment_methods
     * @param integer $vendor_id
     * @return void
     */
    public function cs_dokan_seller_active_withdrawl_methods( $active_payment_methods, $vendor_id = 0 ){
        $vendor_id = $vendor_id ? $vendor_id : dokan_get_current_user_id();
        $payment_methods = get_user_meta( $vendor_id, 'dokan_profile_settings' );
        if( isset( $payment_methods[0]['payment'] ) ){
            foreach( $payment_methods[0]['payment'] as $payment_method => $item ){
                if( isset( $item[ $this->cs_coin_address_placeholder ] ) && !empty( $item[ $this->cs_coin_address_placeholder ] ) ){
                    array_push( $active_payment_methods, $payment_method );
                }
            }
        }
        return $active_payment_methods;
    }

    public function __call( $method, $arguments ){
        return $this->cs_dokan_withdraw_methods( $method, $arguments );
    }

    /**
     * Get Coin ID from method name
     *
     * @param [type] $string
     * @return string
     */
    private function cs_get_coin_id( $string ){
        return \trim( \str_replace( $this->cs_payment_method_prefix, '', $string ) );
    }

    /**
     * Call Payment Methods Dynamically
     *
     * @param [type] $method
     * @param [type] $store_settings
     * @return string
     */
    private function cs_dokan_withdraw_methods( $method, $store_settings ){
        if( empty( $store_settings = $store_settings[0] ) ) return;
        $coin_id = $this->cs_get_coin_id( $method );
        $address = isset( $store_settings['payment'][ $coin_id ][ $this->cs_coin_address_placeholder ] ) ? esc_attr( $store_settings['payment'][ $coin_id ][ $this->cs_coin_address_placeholder ] ) : '' ;
        ?>
        <div class="dokan-form-group cs-wapg-payment-method">
            <div class="dokan-w12">
                <div class="dokan-input-group">
                    <span class="dokan-input-group-addon"><?php esc_html_e( "Address" , 'dokan-lite' ); ?></span>
                    <input value="<?php echo esc_attr( $address ); ?>" name="<?php echo $this->cs_payment_field; ?>[<?php echo esc_attr( $coin_id ); ?>]" class="dokan-form-control" placeholder="please enter <?php echo $coin_id; ?> address" type="text">
                </div>
            </div>
        </div>
        <?php
    }


    /**
     * Save user payment settings
     *
     * @param [type] $store_id
     * @param [type] $dokan_settings
     * @return void
     */
    public function cs_dokan_payment_settings( $store_id, $dokan_settings ){
        $post_data = wp_unslash( $_POST );
        if ( isset( $post_data[ $this->cs_payment_field ] ) ) {
            foreach( $post_data[ $this->cs_payment_field ] as $coin_id => $address ){
                $dokan_settings['payment'] += array(
                    $coin_id => array(
                        $this->cs_coin_address_placeholder => sanitize_text_field( $address )
                    )
                );
            }
            //update settings
            update_user_meta( $store_id, 'dokan_profile_settings', $dokan_settings );
        }
        return true;
    }

}

?>