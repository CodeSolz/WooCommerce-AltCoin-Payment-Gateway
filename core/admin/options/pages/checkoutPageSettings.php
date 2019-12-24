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

use WooGateWayCoreLib\admin\builders\CsFormBuilder;
use WooGateWayCoreLib\admin\functions\CsPaymentGateway;
use WooGateWayCoreLib\admin\builders\CsAdminPageBuilder;

class CheckoutPageSettings {
    
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
    public function generate_checkout_settings( $args ){
        $settings = CsPaymentGateway::get_checkout_page_options();
        
        $fields = array(
            'st1' => array(
                'type' => 'section_title',
                'title'         => __( 'Coin List - Select Box', 'woo-altcoin-payment-gateway' ),
                'desc_tip'         => __( 'Please use the following options if you want to change the text of Select box label and placeholder text', 'woo-altcoin-payment-gateway' ),
            ),
            'cs_altcoin_config[select_box_lebel]'=> array(
                'title'            => __( 'Select Box Lebel Text', 'woo-altcoin-payment-gateway' ),
                'type'             => 'text',
                'class'            => "form-control",
                'required'         => true,
                'value'            => CsFormBuilder::get_value( 'select_box_lebel', $settings, 'Please select coin you want to pay:'),
                'placeholder'      => __( 'Enter select box lebel', 'woo-altcoin-payment-gateway' ),
                'desc_tip'         => __( 'Enter select box lebel. It will show in checkout page. e.g : Please select coin you want to pay:', 'woo-altcoin-payment-gateway' ),
            ),
            'cs_altcoin_config[select_box_option_lebel]'=> array(
                'title'            => __( 'Select Box Placeholder Text', 'woo-altcoin-payment-gateway' ),
                'type'             => 'text',
                'class'            => "form-control",
                'required'         => true,
                'value'            => CsFormBuilder::get_value( 'select_box_option_lebel', $settings, 'Please Select An AltCoin'),
                'placeholder'      => __( 'Enter select box option lebel', 'woo-altcoin-payment-gateway' ),
                'desc_tip'         => __( 'Enter select box option lebel. It will show in checkout page. e.g : Please Select An AltCoin', 'woo-altcoin-payment-gateway' ),
            ),
            'st2' => array(
                'type' => 'section_title',
                'title'         => __( 'Coin Price Section', 'woo-altcoin-payment-gateway' ),
                'desc_tip'         => __( 'Please use the following options to change the coin price section\'s text and images.', 'woo-altcoin-payment-gateway' ),
            ),
            'cs_altcoin_config[price_section_title]'=> array(
                'title'            => __( 'Price Section Title', 'woo-altcoin-payment-gateway' ),
                'type'             => 'text',
                'class'            => "form-control",
                'required'         => true,
                'value'            => CsFormBuilder::get_value( 'price_section_title', $settings, 'You have to pay:'),
                'placeholder'      => __( 'Enter price section title', 'woo-altcoin-payment-gateway' ),
                'desc_tip'         => __( 'Enter price section title. It will show in checkout page. e.g : You have to pay:', 'woo-altcoin-payment-gateway' ),
            ),
            'cs_altcoin_config[loader_gif_url]'  => array(
                'title'                     => __( 'Calculator Gif URL', 'woo-altcoin-payment-gateway' ),
                'type'                      => 'text',
                'class'                     => "form-control coin_name",
                'required'                  => true,
                'placeholder'               => __( 'Calculator Gif URL', 'woo-altcoin-payment-gateway' ),
                'input_field_wrap_start'    => '<div class="smartcat-uploader">',
                'input_field_wrap_end'      => '</div>',
                'custom_attributes' => array(
                    'readonly'     => '',
                ),
                'value' => CsFormBuilder::get_value( 'loader_gif_url', $settings, CS_WAPG_PLUGIN_ASSET_URI .'img/calc_hand.gif' ), 
                'desc_tip'	=> __( 'Choose a gif / loading image. This gif image will show in checkout page during the live price calculation', 'woo-altcoin-payment-gateway' ),
            ),
            'cs_altcoin_config[autotracking_gif_url]'  => array(
                'title'                     => __( 'Coin Tracking Gif URL', 'woo-altcoin-payment-gateway' ),
                'type'                      => 'text',
                'class'                     => "form-control coin_name",
                'required'                  => true,
                'placeholder'               => __( 'Auto Tracking Gif URL', 'woo-altcoin-payment-gateway' ),
                'input_field_wrap_start'    => '<div class="smartcat-uploader">',
                'input_field_wrap_end'      => '</div>',
                'custom_attributes' => array(
                    'readonly'     => '',
                ),
                'value' => CsFormBuilder::get_value( 'autotracking_gif_url', $settings, CS_WAPG_PLUGIN_ASSET_URI .'img/auto-tracking.gif' ), 
                'desc_tip'	=> __( 'Choose a gif / loading image. This gif image will show during the coin tracking for automatic order confirmation.', 'woo-altcoin-payment-gateway' ),
            )
        );
        
        $args['content'] = $this->Form_Generator->generate_html_fields( $fields );
        
        $hidden_fields = array(
            'method'=> array(
                'id'   => 'method',
                'type'  => 'hidden',
                'value' => "admin\\functions\\CsPaymentGateway@save_checkout_page_options"
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
            <script type="text/javascript">
                jQuery(document).ready(function($){
                    $.wpMediaUploader( { buttonClass : '.button-secondary' } );
                });
            </script>
        <?php
    }
    
}