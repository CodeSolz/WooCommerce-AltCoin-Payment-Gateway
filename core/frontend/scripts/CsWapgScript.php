<?php namespace WooGateWayCoreLib\frontend\scripts;
/**
 * Frontend Script
 * 
 * @package WAPG Admin 
 * @since 1.0.0
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    exit;
}


class CsWapgScript {
    /**
     * Hold Main class reference
     *
     * @var type 
     */
    var $Ref;
    
    function __construct( $ref ){
        //get main ref
        $this->Ref = $ref;
        
        //load script on frontend footer
        add_action( 'wp_footer', array( $this, 'altCoinCustomScript') );
    }
    
    /**
     * cart page
     * 
     * @return string
     */
    public function altCoinCustomScript(){
        global $woocommerce;
        ?>
            <script type="text/javascript">
                var module = {
                    altCoinPayment: function( res ){
                        var sdm = ''; var sda = ''; var sdf = ''; var ctfd = '';
                        if( true === res.special_discount_status ){
                            sdm = res.special_discount_msg; 
                            sda = '<tr class="cart-discount">'+
                                '<td><?php _e( 'Special Discount', 'woo-altcoin-payment-gateway' ); ?></td>'+
                                '<td><span class="woocommerce-Price-amount amount">'+res.special_discount_amount+'</span></td>'+
                            '</tr>';
                            sdf = '<input type="hidden" name="special_discount_amount" value="'+res.special_discount_amount+'" />';
                            ctfd = '<span class="woocommerce-cart-subtotal-after-discount">' + res.currency_symbol + res.cartTotalAfterDiscount + '</span><br>';
                        }
                        
                        return sdm +
                        '<h3 id="order_review_heading"><?php _e('You have to pay:', 'woo-altcoin-payment-gateway'); ?></h3>'+
                        '<div id="order_review" class="woocommerce-checkout-review-order">'+
                            '<table class="shop_table woocommerce-checkout-review-order-table">'+
                        '<thead>'+
                            '<tr>'+
                                '<th class="product-name"><?php _e( 'Coin', 'woo-altcoin-payment-gateway' ); ?></th>'+
                                '<th class="product-total"><?php _e( 'Total', 'woo-altcoin-payment-gateway' ); ?></th>'+
                            '</tr>'+
                        '</thead>'+
                        '<tbody>'+
                            '<tr class="cart_item">'+
                                '<td class="product-name">'+
                                    res.coinFullName+'&nbsp;<strong class="product-quantity">&times; '+res.totalCoin+'</strong><br>'+
                                    '<span class="price-tag"> ( 1 '+res.coinName+' = &#36;'+res.coinPrice+') </span>'+
                                '</td>'+
                                '<td class="product-total">'+
                                    '<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">'+res.currency_symbol+'</span>'+res.cartTotal+'</span>'+
                                '</td>'+
                            '</tr>' + sda +
                        '</tbody>'+
                    '<tfoot>'+
                    '<tr class="cart-subtotal">'+
                        '<th><?php _e( 'Subtotal', 'woo-altcoin-payment-gateway' ); ?></th>'+
                        '<td>'+ ctfd +
                            '<span class="woocommerce-Price-amount amount">'+res.totalCoin+' - <span class="woocommerce-Price-currencySymbol">'+res.coinFullName+'</span> </span>'+
                        '</td>'+
                    '</tr>'+
                    '<tr class="order-total">'+
			'<th><?php _e( 'Net Payable Amount', 'woo-altcoin-payment-gateway' ); ?><span class="price-tag"><br><?php _e( '(*Transfer Fee Not Included)', 'woo-altcoin-payment-gateway' ); ?></span></th>'+
			'<td>'+
                        '<strong><span class="woocommerce-Price-amount amount">'+res.totalCoin+' - <span class="woocommerce-Price-currencySymbol">'+res.coinFullName+'</span></span></strong>'+
                        '</td>'+
                    '</tr>'+
                    '</tfoot>'+
                    '</table>'+
                    '<p class="form-row form-row-wide">'+
                        '<label for="alt-coinAddress"><?php _e( 'Please pay to following address:', 'woo-altcoin-payment-gateway' ); ?></label>'+
                    '</p>'+
                    '<div class="coinAddress-qr">'+
                        '<img src="https://chart.googleapis.com/chart?chs=225x225&cht=qr&chl='+res.coinName+':'+res.coinAddress+'?cart_total:'+res.totalCoin+'"/>'+
                        '<div class="coinAddress-info">'+
                            '<h3><strong>'+res.totalCoin+'</strong> '+res.coinFullName+'</h3>'+
                            '<input id="alt-coinAddress" class="input-text wc-altcoin-form-user-alt-coinAddress" value="'+res.coinAddress+'" type="text" /><p></p>'+
                            '<p class="form-row form-row-wide alt-info"><?php echo sprintf(__( "NB: Coin price has been calculated by %s price list. Please place the order after complete your coin transfer & don\'t foget to add the transfer fee.", 'woo-altcoin-payment-gateway' ), 'https://coinmarketcap.com'); ?><p>'+
                        '</div>'+
                    '</div>'+
                    
                    '<p class="form-row form-row-wide">'+
                        '<label for="user-alt-coinAddress"><?php _e( 'Please enter your ', 'woo-altcoin-payment-gateway' ); ?>'+res.coinName+'<?php _e( ' transaction reference or trxid:', 'woo-altcoin-payment-gateway' ); ?> <span class="required">*</span></label>'+
                        '<input id="user_alt_coinAddress" name="user_alt_address" class="input-text wc-altcoin-form-user-alt-res.coinAddress" inputmode="numeric" required  autocorrect="no" autocapitalize="no" spellcheck="no" type="text" placeholder="<?php _e('please enter here your transaction reference or trxid', 'woo-altcoin-payment-gateway');?>" />'+
                        '<input type="hidden" name="payment_info" value="'+res.cartTotal+'__'+res.totalCoin+'__'+res.coinName+'__'+res.coinAddress+'__'+res.coinPrice+'" />'+
                        sdf+
                    '</p>'+
                    '<p class="form-row form-row-wide">'+
                        '<input type="checkbox" name="payment_confirm" required=""/>'+
                        '<?php _e( 'I have completed the coin transfer successfully! ', 'woo-altcoin-payment-gateway' ); ?>'+
                    '</p>';
                    }
                };
                
                jQuery(document).ready(function(){
                    var $orderSubmitBtn = jQuery("#place_order");
                    jQuery('body').on( "change", '.select',function(){
                        var val = jQuery(this).val();
                        if( parseInt(val) === 0 ){
                            jQuery(".coin-detail").slideUp('slow').html('');
                        }else{
                            jQuery(".coin-detail").html('<div class="loader"><img src="<?php echo $this->Ref->loader_icon; ?>" /></div>').slideDown('slow');
                            $orderSubmitBtn.attr('disabled', 'disabled');
                            
                            var form_data = {
                                action : '_cs_wapg_custom_call',
                                cs_token  : '<?php echo wp_create_nonce(SECURE_AUTH_SALT); ?>',
                                data   : {
                                    method : 'frontend\\functions\\CsWapgCoinCal@calcualteCoinPrice',
                                    coin_id : val,
                                    coin_name : jQuery(this).find("option:selected").text()
                                }
                            };
                            
                            jQuery.post( wapg_ajax.ajax_url, form_data, function ( res ) {
                                console.log( res );
                                if( res.response === true ){
                                    jQuery(".coin-detail").html( module.altCoinPayment( res ) ).slideDown('slow');
                                }else{
                                    jQuery(".coin-detail").html( res.msg ).slideDown('slow');
                                }
                                $orderSubmitBtn.removeAttr('disabled');
                            });
                        }
                    });
                    
                    jQuery("body").on( 'focus', '.wc-altcoin-form-user-alt-coinAddress', function(){
                        var $this = jQuery(this);
                        $this.select();
                        document.execCommand( 'Copy', false, null );
                        $this.next("p").css('color','forestgreen').slideDown('slow').text('<?php _e( 'Address has been coppied to clipboard!', 'woo-altcoin-payment-gateway' ); ?> ');
                    }).on( 'blur', '.wc-altcoin-form-user-alt-coinAddress', function(){
                        jQuery(this).next("p").slideUp('slow');
                    });
                    
                });
            </script>
            <style type="text/css">
                .alt-info{font-style: italic;margin-bottom: 10px;border: 2px dashed #999;padding: 10px;margin-top: 25px;}
                .coin-detail{margin-bottom: 1.5em;}
                .coin-detail .loader{ text-align: center;}
                .price-tag{font-style: italic;font-size: 11px;}
                .coinAddress-qr{text-align: center;border: 2px dashed #999;padding: 16px 0px 5px 0px;margin-bottom: 15px; display: table;width: 100%; }
                .coinAddress-info{ position: relative;display: table-cell;vertical-align: top;padding: 10px 20px; width: 63%;}
                .coinAddress-info h3{ font-size: 15px; }
                .coinAddress-qr img{display: table-cell; padding: 0px 0px 11px 18px;}
                .special-discount-msg{ color: forestgreen; font-size: 15px; }
                .con{ color: red; font-weight: bold; }
                .blink{ animation: blink-animation 1s steps(5, start) infinite;-webkit-animation: blink-animation 1s steps(5, start) infinite;}
                @keyframes blink-animation {to {visibility: hidden;}}
                @-webkit-keyframes blink-animation {to{visibility: hidden;}}
            </style>
        <?php
    }
    
}
