<?php namespace WooGateWayCoreLib\admin\options;

/**
 * Class: Admin Menu Scripts
 * 
 * @package Admin
 * @since 1.0.8
 * @author CodeSolz <customer-service@codesolz.net>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    exit;
}

use WooGateWayCoreLib\lib\Util;

class Scripts_Settings {
    
    /**
     * load admin settings scripts
     */
    public static function load_admin_settings_scripts( $page_id ){
        global $altcoin_menu;
        
        
        wp_enqueue_style  ( 'sweetalert', CS_WAPG_PLUGIN_ASSET_URI . 'plugins/sweetalert/dist/sweetalert.css', false ); 
        wp_enqueue_script ( 'sweetalert', CS_WAPG_PLUGIN_ASSET_URI . 'plugins/sweetalert/dist/sweetalert.min.js', false ); 

        if( $page_id == $altcoin_menu['add_new_coin'] ){
            wp_enqueue_style ( 'jquery-typehead', CS_WAPG_PLUGIN_ASSET_URI . 'plugins/jquery-typeahead/jquery.typeahead.css', false ); 
            wp_enqueue_script ( 'jquery-typehead-js', CS_WAPG_PLUGIN_ASSET_URI . 'plugins/jquery-typeahead/jquery.typeahead.js', false ); 
            wp_enqueue_style ( 'jquery-date-time-picker', CS_WAPG_PLUGIN_ASSET_URI . 'plugins/jquery-date-time-picker/jquery.datetimepicker.min.css', false ); 
            wp_enqueue_script ( 'jquery-date-time-picker', CS_WAPG_PLUGIN_ASSET_URI . 'plugins/jquery-date-time-picker/jquery.datetimepicker.full.min.js', false ); 
        }
        
        wp_enqueue_media();
        wp_enqueue_script ( 'media-uploader', CS_WAPG_PLUGIN_ASSET_URI . 'js/wp_media_uploader.js', false ); 
        wp_enqueue_style  ( 'wapg', CS_WAPG_PLUGIN_ASSET_URI . 'css/style.css', false ); 
    }
    
    /**
     * admin footer script processor
     * 
     * @global array $altcoin_menu
     * @param string $page_id
     */
    public static function load_admin_footer_script( $page_id ){
        global $altcoin_menu;
        
        Util::markup_tag( __( 'admin footer script start', 'woo-altcoin-payment-gateway' ) );
        
        //load typehead script
        if( $page_id == $altcoin_menu['add_new_coin'] ){
            self::load_jquery_typehead();
            
            //add payment type changer
            if( has_filter('filter_cs_wapg_payment_type_changer_script') ){
                apply_filters( 'filter_cs_wapg_payment_type_changer_script', '' );
            }else{
                self::load_payment_type_changer();
            }
            
            //load jquery datetime picker
            self::load_jquery_date_time_picker();
        }
        
        //load form submit script on footer
        if( $page_id == $altcoin_menu['add_new_coin'] ||
            $page_id == $altcoin_menu['default_settings'] ){
            self::form_submitter();
        }
        
        Util::markup_tag( __( 'admin footer script end', 'woo-altcoin-payment-gateway' ) );
    }
    
    /**
     * load admin scripts to footer
     */
    public static function form_submitter(){
        ?>
            <script type="text/javascript">
                jQuery(document).ready(function( $ ){
                    $("form").submit(function(e){
                        e.preventDefault();
                        var $this = $(this);
                        var formData = new FormData( $this[0] );
                        formData.append( "action", "_cs_wapg_custom_call" );
                        formData.append( "method", $this.find('#method').val() );
                        swal({ title: $this.find('#swal_title').val(), text: 'Please wait a while...', timer: 200000, imageUrl: '<?php echo CS_WAPG_PLUGIN_ASSET_URI .'img/loading-timer.gif'; ?>', showConfirmButton: false, html :true });
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: formData,
                            contentType: false,
                            cache: false,
                            processData: false
                        })
                        .done(function( data ){
                            console.log(  data );
                            if( true === data.status ){
                                swal( { title: data.title, text: data.text, type : "success", html: true, timer: 5000 });
                                if( typeof data.redirect_url !== 'undefined' ){
                                    window.location.href = data.redirect_url;
                                }
                            }else if( false === data.status ){
                                swal( { title: data.title, text: data.text, type : "error", html: true, timer: 5000 });
                            }else{
                                swal( { title: 'OOPS!', text: 'Something went wrong! Please try again by refreshing the page.', type : "error", html: true, timer: 5000 });
                            }
                        })
                        .fail(function( errorThrown ){
                            console.log( 'Error: ' + errorThrown.responseText );
                            swal( 'Response Error', errorThrown.responseText + '('+errorThrown.statusText +') ' , "error");
                        });
                        return false;
                    });
                    
                });
            </script>
        <?php
    }
    

    /**
     * @return string
     */
    public static function load_jquery_typehead(){
        ?>
            <script type="text/javascript">
                jQuery(document).ready(function( $ ){
                    $.typeahead({
                        input: ".coin_name",
                        maxItem: 5,
                        order: "asc",
                        hint: true,
                        cancelButton: false,
                        emptyTemplate: "No results for {{query}}",
                        searchOnFocus: true,
                        dynamic: true,
                        source: {
                            ajax: {
                                url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                                type: 'POST',
                                data: {
                                    action: '_cs_wapg_custom_call',
                                    method: 'admin\\functions\\CsAdminQuery@get_coin_name',
                                    cs_token: '<?php echo wp_create_nonce( SECURE_AUTH_SALT ); ?>',
                                    query : '{{query}}'
                                },
                                success: function( res ){
//                                    console.log(  res );
                                }
                            }
                        }
                    });
                });
            </script>    
            
        <?php
    }
    
    /**
     * load payment type changer
     */
    public static function load_payment_type_changer(){
        ?>
            <script type="text/javascript">
                jQuery(document).ready(function( $ ){
                    var form = {
                        payment_type : function( type ){
                            if( 1 === type ){
                                $(".manual_payment_address").slideDown('slow');
                            }else{
                                $(".manual_payment_address").slideUp('slow');
                            }
                            $("#hidden_block_1").slideUp('slow');
                        }
                    };
                    
                    $("#cs_field_1").on( 'change', function(){
                        var type_id = parseInt($(this).val());
                        form.payment_type( type_id );
                        if( 1 === type_id ) { return; }
                        var data = {
                            action: '_cs_wapg_custom_call',
                            method: 'admin\\builders\\CsFormHelperLib@get_order_confirm_type_status',
                            cs_token: '<?php echo wp_create_nonce( SECURE_AUTH_SALT ); ?>',
                            type_id: type_id
                        };
                        $.post( ajaxurl, data, function( res ){
                            console.log( res );
                            if( false === res.status){
                                $("#hidden_block_1").html( res.text ).slideDown('slow');
                            }
                        });
                    });
                    
                });
            </script>    
        <?php
    }
    
    /**
     * load jquery date time picker
     */
    private static function load_jquery_date_time_picker(){
        ?>
            <script type="text/javascript">
                jQuery(document).ready(function( ){
                    jQuery('.date-time-picker').datetimepicker({
                        startDate:'+1971-05-01',//or 1986/12/08
                        format: 'Y-m-d h:i A',
        //                format:'unixtime'
                    });
                });
            </script>    
        <?php
    }
}
