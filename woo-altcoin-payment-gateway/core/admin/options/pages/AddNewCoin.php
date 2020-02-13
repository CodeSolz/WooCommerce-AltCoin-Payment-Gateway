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
use WooGateWayCoreLib\admin\functions\CsAdminQuery;
use WooGateWayCoreLib\admin\builders\CsFormHelperLib;
use WooGateWayCoreLib\admin\builders\CsAdminPageBuilder;

class AddNewCoin {
    
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
    public function add_new_coin( $args ){
        
        $coin_data = ''; $coin_addresses = [];
        $hidden_block = ' hidden ';
        if( isset( $_GET['action'] ) && $_GET['action'] == 'update' ){
            $coin_data = CsAdminQuery::get_coin_by( 'id', Util::check_evil_script( $_GET['coin_id' ] ) );
            $coin_addresses = array_map( 'trim', explode( ',', $coin_data->address ));
            if( $coin_data->checkout_type == 2 ){
                $hidden_block = '';
            }
            $args['title'] = __( 'Update Coin', 'woo-altcoin-payment-gateway' );
            $args['prepend_btn'] = Util::generate_back_btn( 'cs-woo-altcoin-all-coins', 'btn btn-custom-submit btn-back' );
        }
        
        $fields = array(
            'cs_add_new[checkout_type]'     => array(
                'title'                     => __( 'Payment Confirmation Type', 'woo-altcoin-payment-gateway' ),
                'type'                      => 'select',
                'class'                     => "form-control coin-type-select",
                'required'                  => true,
                'disabled'                  => empty($coin_data) ? '' : true,
                'placeholder'               => __( 'Please select checkout type', 'woo-altcoin-payment-gateway' ),
                'options'                   => CsFormHelperLib::order_confirm_options(),
                'value'                     => empty($coin_data) ? '' : $coin_data->checkout_type,
                'desc_tip'                  => __( 'Select payment type. Either manual or automatic order confirmation. e.g : Manual', 'woo-altcoin-payment-gateway' ),
    //                                                    'hidden_div'                => array( 'attributes' => array( 'id' => 'hidden_block', 'class' => 'alert alert-warning hidden m-t-15'  ) )
            ),
            'cs_add_new[coin_name]'         => array(
                'title'                     => __( 'Enter Coin Name', 'woo-altcoin-payment-gateway' ),
                'type'                      => 'text',
                'class'                     => "form-control coin_name",
                'disabled'                  => empty($coin_data) ? '' : true,
                'required'                  => true,
                'placeholder'               => __( 'Please type coin name', 'woo-altcoin-payment-gateway' ),
                'input_field_wrap_start'    => '<div class="typeahead__container"><div class="typeahead__field"><div class="typeahead__query">',
                'input_field_wrap_end'      => '</div></div></div>',
                'custom_attributes' => array(
                    'autofocus'     => '',
                    'autocomplete'  => 'off',
                ),
                'value' => empty($coin_data) ? '' : $coin_data->name,
                'desc_tip'	=> __( 'Enter coin name you want to add in to your payment gateway. It will take a while to appear the coin name in the dropdown list. Please be patient. e.g : Bitcoin', 'woo-altcoin-payment-gateway' ),
            ),
            'cs_add_new[coin_address]'=> array(
    //                                                    'section'          => '<div class="manual_payment_address">', 
                'title'            => __( 'Enter Coin address', 'woo-altcoin-payment-gateway' ),
                'type'             => 'text',
                'class'            => "form-control",
                'required'         => true,
                'value'            => empty($coin_addresses) ? '' : $coin_addresses[0],
                'placeholder'      => __( 'Please enter coin address', 'woo-altcoin-payment-gateway' ),
                'desc_tip'         => __( 'Enter new generated coin address. Keep changing your coin address on a certain time to make transactions more safe. e.g : 1KPLgee6crr7u1KQxwnnu4isizufxadVPZ ', 'woo-altcoin-payment-gateway' ),
                'hidden_div'       => array( 'attributes' => array( 'id' => 'hidden_block', 'class' => "{$hidden_block} more_address_block"  ),
                                             'more_input_fields' => array( 'item' => 9, 'values' => $coin_addresses, 'attributes' => array(
                                                'type'             => 'text',
                                                'class'            => "form-control m-t-15",
                                                'placeholder'      => __( 'Please enter coin address', 'woo-altcoin-payment-gateway' ),
                                              ))
                                            )
            ),
            'cs_add_new[coin_status]'=> array(
                'title'            => __( ' Active / Deactivate', 'woo-altcoin-payment-gateway' ),
                'type'             => 'checkbox',
                'value'            => empty($coin_data) ? '' : $coin_data->status,
                'desc_tip'         => __( 'Select this checkbox if you want to activate coin', 'woo-altcoin-payment-gateway' ),
            ),
            'st1' => array(
                'type' => 'section_title',
                'title'         => __( 'Offer Settings', 'woo-altcoin-payment-gateway' ),
                'desc_tip'         => __( 'Please use the following options if you want to give special discount to customer when they use this coin on checkout. Ignore if you don\'t want add any offer', 'woo-altcoin-payment-gateway' ),
            ),
            'cs_add_new[offer_status]'=> array(
                'title'            => __( 'Offer Active / Deactivate', 'woo-altcoin-payment-gateway' ),
                'type'             => 'checkbox',
                'value'            => empty($coin_data) ? '' : $coin_data->offer_status,
                'desc_tip'         => __( 'Select this checkbox if you want to activate offer', 'woo-altcoin-payment-gateway' ),
            ),
            'cs_add_new[special_discount_coin]'  => array(
                'title'                     => __( 'Discount On Cart Total', 'woo-altcoin-payment-gateway' ),
                'type'                      => 'miscellaneous',
                'desc_tip'	=> __( 'Enter discount amount. Either percent or total flat amount. Discount will be apply to total amount. e.g : 10 percent', 'woo-altcoin-payment-gateway' ),
                'options'                   => array(
                    'cs_add_new[offer_amount]' => array(
                        'type' => 'text',
                        'class'                     => "form-control field-width-35-percent",
                        'value' => empty($coin_data) ? '' : $coin_data->offer_amount,
                        'placeholder'               => __( 'Enter discount amount', 'woo-altcoin-payment-gateway' ),
                    ),
                    'cs_add_new[offer_type]' => array(
                        'type' => 'select',
                        'class'                     => "form-control field-width-120-px",
                        'placeholder'               => __( 'Select Discount Type', 'woo-altcoin-payment-gateway' ),
                        'options' => array(
                            '1' => 'Percent(%)',
                            '2' => 'Flat Amount(fiat amount)'
                        ),
                        'value' => empty($coin_data) ? '' : $coin_data->offer_type,
                    )
                )
            ),
            'cs_add_new[offer_validity]'=> array(
                'title'            => __( 'Offer Validity', 'woo-altcoin-payment-gateway' ),
                'type'             => 'miscellaneous',
                'desc_tip'         => __( 'Please select offer start and end date', 'woo-altcoin-payment-gateway' ),
                'options'                   => array(
                    'cs_add_new[offer_start_date]' => array(
                        'type'             => 'text',
                        'class'            => "form-control width-180 date-time-picker",
                        'placeholder'      => __( 'Please select offer start date', 'woo-altcoin-payment-gateway' ),
                        'after_text'      => __( ' to ', 'woo-altcoin-payment-gateway' ),
                        'value' => empty($coin_data) ? '' : $coin_data->offer_start,
                        'custom_attributes' => array(
                            'autofocus'     => '',
                            'autocomplete'  => 'off',
                        ),
                    ), 
                    'cs_add_new[offer_end_date]' => array(
                        'type'             => 'text',
                        'class'            => "form-control width-180 date-time-picker",
                        'placeholder'      => __( 'Please select offer end date', 'woo-altcoin-payment-gateway' ),
                        'value' => empty($coin_data) ? '' : $coin_data->offer_end,
                        'custom_attributes' => array(
                            'autofocus'     => '',
                            'autocomplete'  => 'off',
                        ),
                    ), 
                ),
            ),
            'cs_add_new[offer_show_on_product_page]'=> array(
                'title'            => __( 'Offer Message Show / Hide', 'woo-altcoin-payment-gateway' ),
                'type'             => 'checkbox',
                'value' => empty($coin_data) ? '' : $coin_data->offer_show_on_product_page,
                'desc_tip'         => __( 'Select this checkbox if you want to show offer message on product page bellow product price.', 'woo-altcoin-payment-gateway' ),
            ),
        );
        
        //apply hook        
        $fields = apply_filters( 'filter_cs_wapg_add_new_coin_fields', $fields, $args );
        
        $args['content'] = $this->Form_Generator->generate_html_fields( $fields );
        
        $swal_title = __( 'Adding Coin', 'woo-altcoin-payment-gateway' );
        $btn_txt = __( 'Add Coin', 'woo-altcoin-payment-gateway' );
        $method = 'add_new_coin';
        $update_hidden_fields = [];
        if( !empty($coin_data)){
            $swal_title = __( 'Updating Coin', 'woo-altcoin-payment-gateway' );
            $btn_txt = __( 'Update Coin', 'woo-altcoin-payment-gateway' );
            $method = 'udpate_coin';
            
            $update_hidden_fields = array(
                'cs_add_new[cid]' => array(
                    'id' => 'cs_add_new[cid]',
                    'type' => 'hidden',
                    'value' => $coin_data->cid
                ),
                'cs_add_new[aid]' => array(
                    'id' => 'cs_add_new[aid]',
                    'type' => 'hidden',
                    'value' => $coin_data->aid
                ),
                'cs_add_new[oid]' => array(
                    'id' => 'cs_add_new[oid]',
                    'type' => 'hidden',
                    'value' => $coin_data->oid
                )
            );
            
        }
        
        $hidden_fields = array_merge_recursive( array(
            'method'=> array(
                'id'   => 'method',
                'type'  => 'hidden',
                'value' => "admin\\functions\\CsAdminQuery@{$method}"
            ),
            'swal_title'=> array(
                'id' => 'swal_title',
                'type'  => 'hidden',
                'value' => $swal_title
            )
        ), $update_hidden_fields );
        $args['hidden_fields'] = $this->Form_Generator->generate_hidden_fields( $hidden_fields );
        
        $args['btn_text'] = $btn_txt;
        $args['show_btn'] = true;
        $args['body_class'] = 'no-bottom-margin';
        
        return $this->Admin_Page_Generator->generate_page( $args );
    }
    
}