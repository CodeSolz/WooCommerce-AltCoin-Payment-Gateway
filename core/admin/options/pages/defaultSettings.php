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

use WooGateWayCoreLib\lib\Util;
use WooGateWayCoreLib\admin\builders\CsAdminPageBuilder;
use WooGateWayCoreLib\admin\builders\CsFormBuilder;

class DefaultSettings {
    
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
    public function generate_default_settings( $args ){
        
        $settings = isset($args['gateway_settings']) ? (object)$args['gateway_settings'] : '';
        $option = isset( $settings->defaultOptn ) ? $settings->defaultOptn : '';
        
        $fields = array(
            'cs_altcoin_config[enabled]'    => array(
                'title'                     => __( 'Enable / Disable', 'woo-altcoin-payment-gateway' ),
                'type'                      => 'checkbox',
                'value'                     => 'yes',
                'has_value'                 => CsFormBuilder::get_value( 'enabled', $option, ''),
                'desc_tip'                  => __( 'Enable AltCoin payment gateway', 'woo-altcoin-payment-gateway' ),
            ),
            'cs_altcoin_config[title]'=> array(
                'title'            => __( 'Payment Gateway Name', 'woo-altcoin-payment-gateway' ),
                'type'             => 'text',
                'class'            => "form-control",
                'required'         => true,
                'value'            => CsFormBuilder::get_value( 'title', $option, 'Cryptocurrency Payment'),
                'placeholder'      => __( 'Enter your payment gateway title', 'woo-altcoin-payment-gateway' ),
                'desc_tip'         => __( 'Enter your payment gateway title. It will show in checkout page. e.g : AltCoin Payment', 'woo-altcoin-payment-gateway' ),
            ),
            'cs_altcoin_config[description]'=> array(
                'title'            => __( 'Description', 'woo-altcoin-payment-gateway' ),
                'type'             => 'textarea',
                'class'            => "form-control",
                'value'            => CsFormBuilder::get_value( 'description', $option, 'Make your payment directly into our AltCoin address. Your order won’t be shipped until the funds have cleared in our account.'), 
                'placeholder'      => __( 'Enter your payment gateway description', 'woo-altcoin-payment-gateway' ),
                'desc_tip'         => __( 'Enter your payment gateway description. It will show in checkout page.', 'woo-altcoin-payment-gateway' ),
            ),
            'cs_altcoin_config[payment_icon_url]'  => array(
                'title'                     => __( 'Payment Gateway Icon', 'woo-altcoin-payment-gateway' ),
                'type'                      => 'text',
                'class'                     => "form-control coin_name",
                'required'                  => true,
                'placeholder'               => __( 'payment icon url', 'woo-altcoin-payment-gateway' ),
                'input_field_wrap_start'    => '<div class="smartcat-uploader">',
                'input_field_wrap_end'      => '</div>',
                'custom_attributes' => array(
                    'readonly'     => '',
                ),
                'value' => CsFormBuilder::get_value( 'payment_icon_url', $option, CS_WAPG_PLUGIN_ASSET_URI .'img/icon-24x24.png' ), 
                'desc_tip'	=> __( 'Choose payment icon. This icon will show in checkout page beside the payment gateway name', 'woo-altcoin-payment-gateway' ),
            )
        );
        
        $args['content'] = $this->Form_Generator->generate_html_fields( $fields );
        
        $hidden_fields = array(
            'method'=> array(
                'id'   => 'method',
                'type'  => 'hidden',
                'value' => "admin\\functions\\CsPaymentGateway@save_general_settings"
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
        $args['well'] = "<ul>
                        <li> <b>Basic Hints</b>
                            <ol>
                                <li>
                                    Followings options are the basic settings of the altcoin payment gateway.
                                </li>
                            </ol>
                        </li>
                    </ul>";
        
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