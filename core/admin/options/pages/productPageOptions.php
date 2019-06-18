<?php namespace WooGateWayCoreLib\admin\options\pages;

/**
 * Class: Add New Coin
 * 
 * @package Admin
 * @since 1.2.4
 * @author CodeSolz <customer-support@codesolz.net>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    die();
}

use WooGateWayCoreLib\admin\builders\CsAdminPageBuilder;
use WooGateWayCoreLib\admin\builders\CsFormBuilder;
use WooGateWayCoreLib\admin\functions\CsPaymentGateway;


class ProductPageOptions {
    
    /**
     * Hold page generator class
     *
     * @var type 
     */
    private $Admin_Page_Generator;
    
    /**
     * Form Generator
     *
     * @var type 
     */
    private $Form_Generator;
    
    
    public function __construct(CsAdminPageBuilder $AdminPageGenerator) {
        $this->Admin_Page_Generator = $AdminPageGenerator;
        
        /*create obj form generator*/
        $this->Form_Generator = new CsFormBuilder();
        
        add_action( 'admin_footer', array( $this, 'default_page_scripts'));
    }
    
    /**
     * Generate add new coin page
     * 
     * @param type $args
     * @return type
     */
    public function generate_product_options_settings( $args ){
        
        $settings = CsPaymentGateway::get_product_page_options();
        
        $fields = array(
            'st3' => array(
                'type' => 'section_title',
                'title'         => __( 'Offer Message Text', 'woo-altcoin-payment-gateway' ),
                'desc_tip'         => __( 'Please use the following options to change offer notification shown in the single product page', 'woo-altcoin-payment-gateway' ),
            ),
            'cs_altcoin_config[offer_msg_blink_text]'=> array(
                'title'            => __( 'Offer Blink Text', 'woo-altcoin-payment-gateway' ),
                'type'             => 'text',
                'class'            => "form-control",
                'required'         => true,
                'value'            => CsFormBuilder::get_value( 'offer_msg_blink_text', $settings , 'Special Offers Available!'),
                'placeholder'      => __( 'Enter offer blink text', 'woo-altcoin-payment-gateway' ),
                'desc_tip'         => __( 'Enter offer notification blink text, this text will diplay top of the add to cart button in single product page', 'woo-altcoin-payment-gateway' ),
            ),
            'cs_altcoin_config[offer_msg_text]'=> array(
                'title'            => __( 'Offer Notification Box Title Text', 'woo-altcoin-payment-gateway' ),
                'type'             => 'text',
                'class'            => "form-control",
                'required'         => true,
                'value'            => CsFormBuilder::get_value( 'offer_msg_text', $settings, 'You will get special discount, if you pay with following AltCoins'),
                'placeholder'      => __( 'Enter offer message text', 'woo-altcoin-payment-gateway' ),
                'desc_tip'         => __( 'Enter offer notification text, this text will diplay top of the add to cart button in single product page', 'woo-altcoin-payment-gateway' ),
            ),
        );
        
        $args['content'] = $this->Form_Generator->generate_html_fields( $fields );
        
        $hidden_fields = array(
            'method'=> array(
                'id'   => 'method',
                'type'  => 'hidden',
                'value' => "admin\\functions\\CsPaymentGateway@save_product_page_options"
            ),
            'swal_title'=> array(
                'id' => 'swal_title',
                'type'  => 'hidden',
                'value' => 'Settings Updating'
            ),
            
        );
        $args['hidden_fields'] = $this->Form_Generator->generate_hidden_fields( $hidden_fields );
        
        $args['btn_text'] = 'Save Settings';
        $args['show_btn'] = true;
        $args['body_class'] = 'no-bottom-margin';
        
        return $this->Admin_Page_Generator->generate_page( $args );
    }
 
    /**
     * Add custom scripts
     */
    public function default_page_scripts(){
        ?>
            <script>
                $.wpMediaUploader( { buttonClass : '.button-secondary' } );
            </script>
        <?php
    }
    
}