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
use WooGateWayCoreLib\admin\builders\CsFormBuilder;
use WooGateWayCoreLib\admin\builders\CsAdminPageBuilder;
use WooGateWayCoreLib\admin\functions\CsAutomaticOrderConfirmationSettings;

class AutoOrderSettings {
    
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
        
    }
    
    /**
     * Generate add new coin page
     * 
     * @param type $args
     * @return type
     */
    public function generate_settings( $args ){
        
        $settings_data = CsAutomaticOrderConfirmationSettings::get_order_confirm_settings_data();
        $fields = array(
            'cs_altcoin_config[cms_username]'=> array(
                'title'            => __( 'CoinMarketStats Username', 'woo-altcoin-payment-gateway' ),
                'type'             => 'text',
                'class'            => "form-control",
                'required'         => true,
                'value'            => CsFormBuilder::get_value( 'cms_username', $settings_data, ''),
                'placeholder'      => __( 'Enter your username', 'woo-altcoin-payment-gateway' ),
                'desc_tip'         => __( 'Enter your username used in the registration.', 'woo-altcoin-payment-gateway' ),
            ),
            'cs_altcoin_config[cms_pass]'=> array(
                'title'            => __( 'CoinMarketStats Password', 'woo-altcoin-payment-gateway' ),
                'type'             => 'password',
                'class'            => "form-control",
                'required'         => true,
                'value'            => CsFormBuilder::get_value( 'cms_pass', $settings_data, ''),
                'placeholder'      => __( 'Enter your password', 'woo-altcoin-payment-gateway' ),
                'desc_tip'         => __( 'Enter your password used in the registration.', 'woo-altcoin-payment-gateway' ),
            ),
            'cs_altcoin_config[api_key]'=> array(
                'title'            => __( 'API Key', 'woo-altcoin-payment-gateway' ),
                'type'             => 'text',
                'class'            => "form-control",
                'required'         => true,
                'value'            => CsFormBuilder::get_value( 'api_key', $settings_data, ''),
                'placeholder'      => __( 'Enter your API key', 'woo-altcoin-payment-gateway' ),
                'desc_tip'         => sprintf( __( 'Enter your API key. You can find your API key in the "My API Keys" menu in %s myportal area %s .', 'woo-altcoin-payment-gateway' ), "<a href='https://myportal.coinmarketstats.online/dashboard/api' target='_blank'>", '</a>'),
            ),
            'st1' => array(
                'type' => 'section_title',
                'title'         => __( 'Order Confirmation Settings', 'woo-altcoin-payment-gateway' ),
                'desc_tip'         => __( 'Please set the following information for order confirmation and status', 'woo-altcoin-payment-gateway' ),
            ),
            'cs_altcoin_config[confirmation_count]'     => array(
                'title'                     => __( 'Minimum Confirmation For Transaction', 'woo-altcoin-payment-gateway' ),
                'type'                      => 'select',
                'class'                     => "form-control coin-type-select",
                'required'                  => true,
                'placeholder'               => __( 'Please select confirmation count', 'woo-altcoin-payment-gateway' ),
                'options'                   => array(
                    1 => 1, 2 => 2, 3 => 3, 4 => 4,5 => 5,6 => 6
                ),
                'value'                     => CsFormBuilder::get_value( 'confirmation_count', $settings_data, 6 ),
                'desc_tip'                  => __( 'Select how many confirmation will be treated as a successful transaction e.g : Standard is: 6, 3 is enough for payments $1,000 - $10,000', 'woo-altcoin-payment-gateway' ),
            ),
            'cs_altcoin_config[order_status]'     => array(
                'title'                     => __( 'Order Status', 'woo-altcoin-payment-gateway' ),
                'type'                      => 'select',
                'class'                     => "form-control coin-type-select",
                'required'                  => true,
                'placeholder'               => __( 'Please select order status', 'woo-altcoin-payment-gateway' ),
                'options'                   => array(
                    'on-hold' => 'On Hold', 'processing' => 'Processing', 'completed' => 'Completed'
                ),
                'value'                     => CsFormBuilder::get_value( 'order_status', $settings_data, 'completed'),
                'desc_tip'                  => __( 'Please select order status after successful transaction e.g : Completed', 'woo-altcoin-payment-gateway' ),
            ),
        );
        
        $args['content'] = $this->Form_Generator->generate_html_fields( $fields );

        $hidden_fields = array(
            'method'=> array(
                'id'   => 'method',
                'type'  => 'hidden',
                'value' => "admin\\functions\\CsAutomaticOrderConfirmationSettings@save_settings"
            ),
            'swal_title'=> array(
                'id' => 'swal_title',
                'type'  => 'hidden',
                'value' => 'Settings Updating'
            ),
            'cs_altcoin_config[cms_refferer]'=> array(
                'id' => 'cs_altcoin_config[cms_refferer]',
                'type'  => 'hidden',
                'value' => site_url()
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
                        Please register here - <a href='https://myportal.coinmarketstats.online/register' target=\"_blank\">https://myportal.coinmarketstats.online</a> for your API Key.
                    </li>
                    <li>
                        After login to your dashboard, go to 'API Keys' menu. From bottom of the page you can generate your API key. 
                    </li>
                    <li>
                        You can purchase pro package for unlimited automatic order confirmation from your dashboard. Free package included 5 automatic order confirmation.
                    </li>
                </ol>
            </li>
        </ul>";
        
        
        return $this->Admin_Page_Generator->generate_page( $args );
    }
    
}