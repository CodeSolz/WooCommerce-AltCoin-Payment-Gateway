<?php namespace WooGateWayCoreLib\Admin\options;

/**
 * Class: Admin Pages
 * 
 * @package Admin
 * @since 1.0.9
 * @author CodeSolz <customer-support@codesolz.net>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    die();
}

use WooGateWayCoreLib\admin\builders\CsFormBuilder;
use WooGateWayCoreLib\admin\options\Coin_List;
use WooGateWayCoreLib\admin\functions\CsAdminQuery;
use WooGateWayCoreLib\lib\Util;

class Options_Pages {
    
    /**
     * Settings page
     */
    public function app_settings( $settings ){
        ?> 
        <div class="wrap"> 
            <div id="product_binder">
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="title"><?php _e('Gateway Settings', 'woo-altcoin-payment-gateway'); ?></h3>
                        <p><?php _e('Alltcoin payment gatway defult settings. Please fill up the following informaton correctly.', 'woo-altcoin-payment-gateway'); ?></p>
                    </div>
                    <form method="post">
                        <div class="panel-body bg-white no-bottom-margin">
                            <div class="well">
                                <ul>
                                    <li> <b>Basic Hints</b>
                                        <ol>
                                            <li>
                                                Followings options are the basic settings of the altcoin payment gateway.
                                            </li>
                                        </ol>
                                    </li>
                                </ul>
                            </div>
                            <div class="container">
                                <div class="row">
                                    <div class="col-8">

                                            <div class="form-group">
                                                <div class="label">
                                                    <label><?php _e('Enable / Disable', 'woo-altcoin-payment-gateway'); ?></label>
                                                </div>
                                                <div class="input-group">
                                                    <input type="checkbox" name="cs_altcoin_config[enabled]" <?php if( !empty( $settings->defaultOptn['enabled'] ) ) { echo 'checked="checked"'; } ?> value="yes" />
                                                    <p class="description"><?php _e('Enable AltCoin payment gateway', 'woo-altcoin-payment-gateway'); ?></p>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="label">
                                                    <label><?php _e('Title', 'woo-altcoin-payment-gateway'); ?></label>
                                                </div>
                                                <div class="input-group">
                                                    <input type="text" name="cs_altcoin_config[title]" placeholder="Enter your payment gateway title" class="form-control" value="<?php echo empty($settings) ? 'AltCoin Payment' : $settings->defaultOptn['title']; ?>" />
                                                    <p class="description"><?php _e('Enter your payment gateway title. It will show in checkout page. e.g : AltCoin Payment', 'woo-altcoin-payment-gateway'); ?></p>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="label">
                                                    <label><?php _e('Description', 'woo-altcoin-payment-gateway'); ?></label>
                                                </div>
                                                <div class="input-group">
                                                    <textarea name="cs_altcoin_config[description]" placeholder="Enter your payment gateway description" class="form-control"><?php echo empty($settings) ? 'Make your payment directly into our AltCoin address. Your order won’t be shipped until the funds have cleared in our account.' : $settings->defaultOptn['description']; ?></textarea>
                                                    <p class="description"><?php _e('Enter your payment gateway description.', 'woo-altcoin-payment-gateway'); ?></p>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="label">
                                                    <label><?php _e('Payment Icon url', 'woo-altcoin-payment-gateway'); ?></label>
                                                </div>
                                                <div class="input-group">
                                                    <div class="smartcat-uploader">
                                                        <input type="text" readonly="" name="cs_altcoin_config[payment_icon_url]" placeholder="payment icon url" class="form-control" value="<?php echo isset($settings->defaultOptn['payment_icon_url']) && !empty($settings->defaultOptn['payment_icon_url']) ? $settings->defaultOptn['payment_icon_url'] : ( empty($settings->icon) ? CS_WAPG_PLUGIN_ASSET_URI .'img/crypto-currency.jpg' : $settings->icon ); ?>" />
                                                    </div>
                                                    <p class="description"><?php _e('Choose payment icon. This icon will show in checkout page beside the payment gateway name', 'woo-altcoin-payment-gateway'); ?></p>
                                                </div>
                                            </div>
                                            <div class="form-group no-border">
                                                <div class="label">
                                                    <label><?php _e('Calculator Gif URL', 'woo-altcoin-payment-gateway'); ?></label>
                                                </div>
                                                <div class="input-group">
                                                    <div class="smartcat-uploader">
                                                        <input type="text" readonly="" name="cs_altcoin_config[loader_gif_url]" placeholder="calculator gif url" class="form-control" value="<?php echo isset($settings->defaultOptn['loader_gif_url']) && !empty($settings->defaultOptn['loader_gif_url']) ? $settings->defaultOptn['loader_gif_url'] : ( empty($settings->loader_icon) ? CS_WAPG_PLUGIN_ASSET_URI .'img/calc_hand.gif' : $settings->loader_icon ); ?>" />
                                                    </div>
                                                    <p class="description"><?php _e('Choose price loading gif. This gif image will show in checkout page during the live price calculation', 'woo-altcoin-payment-gateway'); ?></p>
                                                </div>
                                            </div>
                                            
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="section-submit-button">
                            <?php 
                                //add nonce field
                                wp_nonce_field( SECURE_AUTH_SALT, 'cs_token' );
                            ?>
                            <input type="hidden" name="method" id="method" value="admin\functions\CsPaymentGateway@save_general_settings" />
                            <input type="hidden" name="swal_title" id="swal_title" value="Settings Updating" />
                            <input type="submit" class="btn btn-custom-submit" value="Save Settings" />
                        </div>
                    </form>
                    <script>
                        $.wpMediaUploader( { buttonClass : '.button-secondary' } );
                    </script>
                    <?php $this->footer_copyright(); ?>
                </div>
            </div>
       </div> 
        <?php
    }
    
    /**
     * Add new product
     */
    public function add_new_coin(){
        $coin_data = '';
        
        if( isset( $_GET['action'] ) && $_GET['action'] == 'update' ){
            $coin_data = CsAdminQuery::get_coin_by( 'id', Util::check_evil_script( $_GET['coin_id' ] ) );
        }
        
        ?> 
        <div class="wrap"> 
            <div id="product_binder">
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="title"><?php if(empty($coin_data)) { ?>Add New <?php }else{ echo 'Update'; }?> Coin</h3>
                        <p><?php _e('Please fill up the following informaton correctly to add new coin to payment method.', 'woo-altcoin-payment-gateway'); ?></p>
                    </div>
                    <form method="post">
                    <div class="panel-body bg-white no-bottom-margin">
                        <div class="container">
                            <div class="row">
                                <div class="col-8">
                                    <?php
                                            $fields = array(
                                                'cs_add_new[coin_name]'         => array(
                                                    'title'                     => __( 'Enter Coin Name', 'woo-altcoin-payment-gateway' ),
                                                    'type'                      => 'text',
                                                    'class'                     => "form-control coin_name",
                                                    'required'                  => true,
                                                    'placeholder'               => __( 'Please type coin name', 'woo-altcoin-payment-gateway' ),
                                                    'input_field_wrap_start'    => '<div class="typeahead__container"><div class="typeahead__field"><div class="typeahead__query">',
                                                    'input_field_wrap_end'      => '</div></div></div>',
                                                    'custom_attributes' => array(
                                                        'autofocus'     => '',
                                                        'autocomplete'  => 'off'
                                                    ),
                                                    'value' => empty($coin_data) ? '' : $coin_data->name,
                                                    'desc_tip'	=> __( 'Enter coin name you want to add in to your payment gateway. wait a while until the coin name appear in the dropdown list e.g : Bitcoin', 'woo-altcoin-payment-gateway' ),
                                                ),
                                                'cs_add_new[checkout_type]'         => array(
                                                    'title'                     => __( 'Payment Confirmation Type', 'woo-altcoin-payment-gateway' ),
                                                    'type'                      => 'select',
                                                    'class'                     => "form-control",
                                                    'required'                  => true,
                                                    'placeholder'               => __( 'Please select checkout type', 'woo-altcoin-payment-gateway' ),
                                                    'options' => array(
                                                        '1'  => __( 'Manual', 'woo-altcoin-payment-gateway' ),
                                                        '2'     => __( 'Automatic ( extension required )', 'woo-altcoin-payment-gateway' ),
                                                        '3'     => __( 'Automatic ( coinmarketstats.online api )', 'woo-altcoin-payment-gateway' ),
                                                    ),
                                                    'value' => empty($coin_data) ? '' : $coin_data->checkout_type,
                                                    'desc_tip'	=> __( 'Select payment type. Either manual or automatic order confirmation. e.g : Manual', 'woo-altcoin-payment-gateway' ),
                                                ),
                                                'cs_add_new[coin_address]'=> array(
                                                    'section'          => '<div class="manual_payment_confirmation">', 
                                                    'title'            => __( 'Enter Coin address', 'woo-altcoin-payment-gateway' ),
                                                    'type'             => 'text',
                                                    'class'            => "form-control",
                                                    'required'         => true,
                                                    'value' => empty($coin_data) ? '' : $coin_data->address,
                                                    'placeholder'      => __( 'Please enter coin address', 'woo-altcoin-payment-gateway' ),
                                                    'desc_tip'         => __( 'Enter your coin address. e.g : ', 'woo-altcoin-payment-gateway' ),
                                                ),
                                                'alert_div' => array(
                                                    'type'  => 'alert_div',
                                                    'section_wrapper_class'  => 'automatic_payment_confirmation hide m-15',
                                                    'alert_class'  => 'alert alert-warning',
                                                    'alert_msg' => __('Sorry! This option is comming very soon. Contact us at <b>support@codesolz.net</b> for more information.', 'woo-altcoin-payment-gateway')
                                                ),
                                                'cs_add_new[coin_status]'=> array(
                                                    'title'            => __( ' Active / Deactivate', 'woo-altcoin-payment-gateway' ),
                                                    'type'             => 'checkbox',
                                                    'value' => empty($coin_data) ? '' : $coin_data->status,
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
                                                    'value' => empty($coin_data) ? '' : $coin_data->offer_status,
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
                                                        ), 
                                                        'cs_add_new[offer_end_date]' => array(
                                                            'type'             => 'text',
                                                            'class'            => "form-control width-180 date-time-picker",
                                                            'placeholder'      => __( 'Please select offer end date', 'woo-altcoin-payment-gateway' ),
                                                            'value' => empty($coin_data) ? '' : $coin_data->offer_end
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
                                        (new CsFormBuilder())->generate_html_fields( $fields );
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="section-submit-button">
                        <?php wp_nonce_field( SECURE_AUTH_SALT, 'cs_token' );
                            $swal_title = __( 'Adding Coin', 'woo-altcoin-payment-gateway' );
                            $btn_txt = __( 'Add Coin', 'woo-altcoin-payment-gateway' );
                            $method = 'add_new_coin';
                            if( !empty($coin_data)){
                                $swal_title = __( 'Updating Coin', 'woo-altcoin-payment-gateway' );
                                $btn_txt = __( 'Update Coin', 'woo-altcoin-payment-gateway' );
                                $method = 'udpate_coin';
                        ?>
                            <input type="hidden" name="cs_add_new[cid]" value="<?php echo $coin_data->cid; ?>" />
                            <input type="hidden" name="cs_add_new[aid]" value="<?php echo $coin_data->aid; ?>" />
                            <input type="hidden" name="cs_add_new[oid]" value="<?php echo $coin_data->oid; ?>" />
                        <?php } ?>
                        <input type="hidden" name="method" id="method" value="admin\functions\CsAdminQuery@<?php echo $method; ?>" />
                        <input type="hidden" name="swal_title" id="swal_title" value="<?php echo $swal_title; ?>" />
                        <?php if( !empty($coin_data)){ ?>
                            <a href="<?php echo admin_url('admin.php?page=cs-woo-altcoin-all-coins');?>" class="btn btn-custom-submit btn-back"><?php _e( '<< Back', 'woo-altcoin-payment-gateway' ); ?></a>
                        <?php } ?>
                        <input type="submit" class="btn btn-custom-submit" value="<?php echo $btn_txt; ?>" />
                    </div>
                    </form>
                    <?php $this->footer_copyright(); ?>
                </div>
            </div>
        </div> 
        <?php
    }
    
    /**
     * all products
     */
    public function all_coins(){
        
        ?> 
            <div class="wrap"> 
                <div id="product_binder">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="title"><?php _e('All Coins', 'woo-altcoin-payment-gateway'); ?></h3>
                            <p><?php _e('All added coin to the payment gateway', 'woo-altcoin-payment-gateway'); ?></p>
                        </div>
                        <div class="panel-body bg-white ">
                            <div class="container">
                                <div class="row">
                                    <div class="col-8"> 
                                        <?php
                                            // generate all product list
                                            $adCodeList = new Coin_List();
                                            $search_key = '';
                                            if (isset($_GET['s']) && !empty($_GET['s'])) {
                                                $search_key = "<span class='subtitle'>Search results for '<b>" . $_GET['s'] . "</b>'</span>";
                                            }
                                            $adCodeList->prepare_items();
                                        ?>
                                        <form id="plugins-filter" method="get"> 
                                            <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" />
                                            <?php
                                                $adCodeList->views();
                                                $adCodeList->search_box( __('Search', 'woo-altcoin-payment-gateway'), 'ad code');
                                                $adCodeList->display();
                                            ?>
                                        </form> 
                                    </div>
                                </div>
                            </div>
                        </div>
                            
                        <?php $this->footer_copyright(); ?>
                    </div>
                </div>
            </div>
        <?php
    }
  
    /**
     * Copyright link
     * 
     * Change or remove the copyright link is violation of plugins license
     */
    private function footer_copyright(){
        ?>
            <div class="panel-footer">
                <p>Thank you for choosing us! <a href="https://www.codesolz.net" target="_blank">www.codesolz.net</a></p>
            </div>
        <?php
    }
    
}
