<?php namespace WooGateWayCoreLib\admin\builders;
/**
 * From Builder
 * 
 * @package WAPG Admin 
 * @since 1.0.0
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    exit;
}

use WooGateWayCoreLib\lib\Util;

class CsWapgForm {
    
    /**
     * Admin Settings Form
     * 
     * @param type $obj
     * @return type
     */
    public static function getAdminSettings( $obj ){
        $custom_fields = get_option( $obj->cs_altcoin_fields );
        $custom_fields =  empty( $custom_fields ) ? '' : json_decode($custom_fields);
        $customFields = array();
        if( ! empty( $custom_fields ) && $custom_fields->count > 0 ){
            $i = 1; unset($custom_fields->count);
            $allCoins = self::getAltCoinsSelect();
            foreach($custom_fields as $field){
                $customFields += array(
                    "altCoinName_{$i}" => array(
                        'title'		=> __( 'Select AltCoin', CS_WAPG_TEXTDOMAIN ),
                        'type'		=> 'select',
                        'class'                => 'alt-coin',
                        'custom_attributes' => array(
                            'data-coinid' => $i
                        ),
                        'desc_tip'	=> __( 'Select AltCoin where do you want to receive your payment', CS_WAPG_TEXTDOMAIN ),
                        'default'       => $field->id,
                        'options'       => $allCoins
                    ),
                    "altCoinAddress_{$i}" => array(
                        'title'		=> sprintf(__( 'Enter %s %s %s address.', CS_WAPG_TEXTDOMAIN ), '<span class="altCoinVallabel_'.$i.'">', $allCoins[$field->id], '</span>'),
                        'type'		=> 'text',
                        'class'         => "alt-value-{$i}",
                        'default'       => $field->address,
                        'placeholder'   => __( 'please enter here your coin address', CS_WAPG_TEXTDOMAIN )
                    )
                );
                $i++;
            }
        }else{
            $customFields = array(
                'altCoinName_1' => array(
                'title'		=> __( 'Select AltCoin', CS_WAPG_TEXTDOMAIN ),
                'type'		=> 'select',
                'class'                => 'alt-coin',
                'custom_attributes' => array(
                    'data-coinid' => 1
                ),
                'desc_tip'	=> __( 'Select AltCoin where do you want to receive your payment', CS_WAPG_TEXTDOMAIN ),
                'default'  => '0',
                'options' => self::getAltCoinsSelect()
            ),
            'altCoinAddress_1' => array(
                'title'		=> sprintf(__( 'Enter %s altcoin %s address.', CS_WAPG_TEXTDOMAIN ), '<span class="altCoinVallabel_1">', '</span>'),
                'type'		=> 'text',
                'class'            => 'alt-value-1',
                'placeholder'   => __( 'please enter here your coin address', CS_WAPG_TEXTDOMAIN )
                )
            );
        }
        
        
        return $obj->form_fields = array(
            'enabled' => array(
                    'title'		=> __( 'Enable / Disable', CS_WAPG_TEXTDOMAIN ),
                    'label'		=> __( 'Enable AltCoin payment gateway', CS_WAPG_TEXTDOMAIN ),
                    'type'		=> 'checkbox',
                    'default'	=> 'no',
            ),
            'title' => array(
                    'title'		=> __( 'Title', CS_WAPG_TEXTDOMAIN ),
                    'type'		=> 'text',
                    'desc_tip'          => __( 'Payment title of checkout process.', CS_WAPG_TEXTDOMAIN ),
                    'default'           => __( 'AltCoin', CS_WAPG_TEXTDOMAIN ),
            ),
            'description' => array(
                    'title'		=> __( 'Description', CS_WAPG_TEXTDOMAIN ),
                    'type'		=> 'textarea',
                    'desc_tip'          => __( 'Payment title of checkout process.', CS_WAPG_TEXTDOMAIN ),
                    'default'           => __( 'Make your payment directly into our AltCoin address. Your order won’t be shipped until the funds have cleared in our account.', CS_WAPG_TEXTDOMAIN ),
                    'css'		=> 'max-width:450px;'
            ),
            'payment_icon_url' => array(
                    'title'		=> __( 'Payment Icon url', CS_WAPG_TEXTDOMAIN ),
                    'type'		=> 'text',
                    'desc_tip'          => __( 'Image next to the gateway’s name', CS_WAPG_TEXTDOMAIN ),
            ),
            'loader_gif_url' => array(
                    'title'		=> __( 'Calculator Gif URL', CS_WAPG_TEXTDOMAIN ),
                    'type'		=> 'text',
                    'desc_tip'          => __( 'Calculating gif when price being calculate', CS_WAPG_TEXTDOMAIN ),
            ),
        ) + $customFields;
    }
    
    /**
     * Generate Custom Form
     * 
     * @param type $refObj
     */
    public static function customForm( $refObj ){
        if ( $description = $refObj->get_description() ) {
            echo wpautop( wptexturize( $description ) );
        }
        $fields = array();
        
        $default_fields = array(
                'alt-con' => '<p class="form-row form-row-wide altCoinSelect">
                        <label for="' . esc_attr( $refObj->id ) . '-alt-name">' . __( 'Please select coin you want to pay:', CS_WAPG_TEXTDOMAIN ) . ' <span class="required">*</span></label>'.
                        self::getActiveAltCoinSelect( $refObj )
                .'</p><div class="coin-detail"><!--coin calculation--></div>'
        );
        
        $fields = wp_parse_args( $fields, apply_filters( 'woocommerce_altcoin_form_fields', $default_fields, $refObj->id ) );
        ?>
        <fieldset id="wc-<?php echo esc_attr( $refObj->id ); ?>-cc-form" class='wc-altcoin-form wc-payment-form'>
                <?php do_action( 'woocommerce_altcoin_form_start', $refObj->id ); ?>
                <?php
                        foreach ( $fields as $field ) {
                                echo $field;
                        }
                ?>
                <?php do_action( 'woocommerce_altcoin_form_end', $refObj->id ); ?>
                <div class="clear"></div>
        </fieldset>
        <?php
    }
    
    /**
     * Output field name HTML
     *
     * Gateways which support tokenization do not require names - we don't want the data to post to the server.
     *
     * @since  2.6.0
     * @param  string $name
     * @return string
     */
    public static function field_name( $id, $name ) {
        return ' name="' . esc_attr( $id . '-' . $name ) . '" ';
    }
    
    /**
     * All Alt coin in select
     * 
     * @return string
     */
    public static function getAltCoinsSelect( $type = false ){
        $currencies = \file_get_contents(CS_WAPG_PLUGIN_ASSET_URI.'/js/currencies.json');

        if( $type == 'html' ){
            $select = '<option value="0">======== ' . __( 'Please Slect An AltCoin!', CS_WAPG_TEXTDOMAIN) . ' ========</option>';
        }else{
            $select = array( '0' => '===='.__( 'Please Slect An AltCoin!', CS_WAPG_TEXTDOMAIN).'====' );
        }
        
        foreach( \json_decode($currencies) as $currency ){
            $symbol = Util::encode_html_chars( $currency->symbol );
            $name = Util::encode_html_chars( $currency->name);
            $id = Util::encode_html_chars($currency->id);
            if( $type == 'html' ){
                $select .= '<option value="'. $id .'">'. $name .'('. $symbol . ')</option>';
            }else{
                $select += array( 
                    $id =>" {$name}({$symbol})"
                );
            }
        }
        
        return $select;
    }
    
    /**
     * Get Active altCoins
     * 
     * @param type $refObj
     * @return type
     */
    public static function getActiveAltCoinSelect( $refObj ){
        $custom_fields = get_option( $refObj->cs_altcoin_fields );
        $altCoin = '<select name="altcoin" id="CsaltCoin" class="select">';
        if( empty($custom_fields)){
            $altCoin .= '<option value="0">===='.__('Sorry! No AltCoin Payment is actived!', CS_WAPG_TEXTDOMAIN).'====</option>';
        }else{
            $altCoin .= '<option value="0">===='.__( 'Please Slect An AltCoin!', CS_WAPG_TEXTDOMAIN).'====</option>';
            $custom_fields = json_decode($custom_fields);
            $allAltCoins = self::getAltCoinsSelect();
            unset($custom_fields->count);
            foreach( $custom_fields as $field){
                $altCoin .= '<option value="'.$field->id.'__'.$field->address.'">'.$allAltCoins[$field->id].'</option>';
            }
            return $altCoin .='</select>';
        }
    }
}
