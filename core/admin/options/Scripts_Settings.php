<?php namespace WooGateWayCoreLib\admin\options;

/**
 * Class: Admin Menu Scripts
 *
 * @package Admin
 * @since 1.0.8
 * @author CoinMarketStats <support@coinmarketstats.online>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
	exit;
}

use WooGateWayCoreLib\lib\Util;

class Scripts_Settings {

	/**
	 * load admin settings scripts
	 */
	public static function load_admin_settings_scripts( $page_id ) {
		global $altcoin_menu;

		wp_enqueue_style( 'sweetalert', CS_WAPG_PLUGIN_ASSET_URI . 'plugins/sweetalert/dist/sweetalert.css', false );
		wp_enqueue_script( 'sweetalert', CS_WAPG_PLUGIN_ASSET_URI . 'plugins/sweetalert/dist/sweetalert.min.js', false );

		if ( isset( $altcoin_menu['add_new_coin'] ) && $page_id == $altcoin_menu['add_new_coin'] ) {
			wp_enqueue_style( 'jquery-typehead', CS_WAPG_PLUGIN_ASSET_URI . 'plugins/jquery-typeahead/jquery.typeahead.min.css', false );
			wp_enqueue_script( 'jquery-typehead-js', CS_WAPG_PLUGIN_ASSET_URI . 'plugins/jquery-typeahead/jquery.typeahead.min.js', false );
			wp_enqueue_style( 'jquery-date-time-picker', CS_WAPG_PLUGIN_ASSET_URI . 'plugins/jquery-date-time-picker/jquery.datetimepicker.min.css', false );
			wp_enqueue_script( 'jquery-date-time-picker', CS_WAPG_PLUGIN_ASSET_URI . 'plugins/jquery-date-time-picker/jquery.datetimepicker.full.min.js', false );
		}

		if ( ( isset( $altcoin_menu['product_page_options_settings'] ) && $page_id == $altcoin_menu['product_page_options_settings'] ) ||
				( isset( $altcoin_menu['widget_options_settings'] ) && $page_id == $altcoin_menu['widget_options_settings'])
			) {
			wp_enqueue_style( 'select2', CS_WAPG_PLUGIN_ASSET_URI . 'plugins/select2/dist/css/select2.min.css', false );
			wp_enqueue_script( 'select2', CS_WAPG_PLUGIN_ASSET_URI . 'plugins/select2/dist/js/select2.min.js', false );
		}

		if ( isset( $altcoin_menu['default_settings'] ) && $page_id == $altcoin_menu['default_settings'] ) { 
			wp_enqueue_media();
			wp_enqueue_script( 'wapg-media-uploader', CS_WAPG_PLUGIN_ASSET_URI . 'js/wp.media.uploader.min.js', false );
		}

		wp_enqueue_style( 'wapg', CS_WAPG_PLUGIN_ASSET_URI . 'css/style.css', false );
	}

	/**
	 * admin footer script processor
	 *
	 * @global array $altcoin_menu
	 * @param string $page_id
	 */
	public static function load_admin_footer_script( $page_id ) {
		global $altcoin_menu;

		Util::markup_tag( __( 'admin footer script start', 'woo-altcoin-payment-gateway' ) );

		// load typehead script
		if ( $page_id == $altcoin_menu['add_new_coin'] ) {
			self::load_jquery_typehead();

			// load jquery datetime picker
			self::load_jquery_date_time_picker();

			// coin type change
			self::load_coin_type_changer();
		}

		//menu id
		$apply_script_on = array(
			'add_new_coin', 'default_settings', 'register_automatic_order', 
			'checkout_options_settings', 'product_page_options_settings', 
			'widget_options_settings'
		);

		$add_script_on = apply_filters( 'wapg_add_form_submitter_script', $apply_script_on );

		// load form submit script on footer
		if( $add_script_on ){
			foreach( $add_script_on as $menu_id ){
				if( isset( $altcoin_menu[ $menu_id ] ) && empty( $get_menu_id = $altcoin_menu[ $menu_id ]) ){
					continue;
				}
				if( $page_id == $get_menu_id ){
					self::form_submitter();
				}
			}
		}

		if ( $page_id == $altcoin_menu['all_coins_list']
			) {
			self::show_more_less();
		}

		Util::markup_tag( __( 'admin footer script end', 'woo-altcoin-payment-gateway' ) );
	}

	/**
	 * load admin scripts to footer
	 */
	public static function form_submitter() {
		?>
			<script type="text/javascript">
				jQuery(document).ready(function( $ ){
					$("form").submit(function(e){
						e.preventDefault();
						var $this = $(this);
						var formData = new FormData( $this[0] );

						var action = $this.find('#cs_field_action').val();
						if( typeof action === 'undefined' || action.length == 0 ){
							action = "_cs_wapg_custom_call";
						}

						formData.append( "action", action );
						formData.append( "method", $this.find('#cs_field_method').val() );
						swal({ title: $this.find('#cs_field_swal_title').val(), text: 'Please wait a while...', timer: 200000, imageUrl: '<?php echo CS_WAPG_PLUGIN_ASSET_URI . 'img/loading-timer.gif'; ?>', showConfirmButton: false, html :true });
						$.ajax({
							url: ajaxurl,
							type: 'POST',
							data: formData,
							contentType: false,
							cache: false,
							processData: false
						})
						.done(function( data ) {
							if( true === data.status ){
								swal( { title: data.title, text: data.text, type : "success", html: true, timer: 5000 });
								if( typeof data.redirect_url !== 'undefined' ){
									window.location.href = data.redirect_url;
								}
							}else if( false === data.status ){
								swal({ title: data.title, text: data.text, type : "error", html: true, timer: 5000 });
							}else{
								swal( { title: 'OOPS!', text: 'Something went wrong! Please try again by refreshing the page.', type : "error", html: true, timer: 5000 });
							}
						})
						.fail(function( errorThrown ) {
							console.log( 'Error: ' + errorThrown.responseText );
							swal( 'Response Error', errorThrown.responseText + '('+errorThrown.statusText +') ' , "error" );
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
	public static function load_jquery_typehead() {
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
//                        filter: false,
						source: {
							ajax: function( query ){
								$(".alert").hide();
								return {
									url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
									type: 'POST',
//                                    path: "data",
									data: {
										action: '_cs_wapg_custom_call',
										method: 'admin\\functions\\CsAdminQuery@get_coin_name',
										cs_token: '<?php echo wp_create_nonce( SECURE_AUTH_SALT ); ?>',
										oc_type : $("#cs_field_1").val(),
										query : '{{query}}'
									},
									callback: {
										done: function (data) {
											if( typeof data.success !== 'undefined' && false === data.success ){
												$('<div class="alert alert-warning typehead-error"> '+ data.response + '</div>').insertAfter(".typeahead__result");
											}else{
												return data;
											}
										},
										fail: function( res ){
											console.log(  res );
										}
									}
								}
							}
						}
					});
				});
			</script>    
			
		<?php
	}


	/**
	 * load jquery date time picker
	 */
	private static function load_jquery_date_time_picker() {
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


	/**
	 * show more less
	 */
	private static function show_more_less() {
		?>
			<script type="text/javascript">
				jQuery(document).ready(function( $ ){
					$('.address').each(function( e ){
						var text = $(this).text();
						if( text.length > 30 ){
							var show = text.substr( 0, 20 );
							var hidden = text.substr( 20, parseInt( text.length) );
							var moreLink = text.length > 30 ? '<br><a class="show-more">More..</a>' : '';
							$(this).html( show +'<span class="hidden-text ht-'+e+'">'+ hidden +'</span>' + moreLink);
						}
					});
					
					$('body').on('click', '.show-more', function(){
						var $mlBtn = $(this);
						$mlBtn.parent().find('.hidden-text').toggle();
						$mlBtn.text() === 'More..' ? $mlBtn.text('..less') : $mlBtn.text('More..');
						console.log( $(this).parent() );
					});
					$(".offer-more").on('click', function(){
						var $ofrBtn = $(this);
						console.log( 'clicked');
						$ofrBtn.parent().prev('table').toggle('slow');
						$ofrBtn.text() === 'Show Offer Information..' ? $ofrBtn.text('..less') : $ofrBtn.text('Show Offer Information..');
					});
				});
			</script>    
		<?php
	}


	/**
	 * load jquery date time picker
	 */
	private static function load_coin_type_changer() {
		?>
			<script type="text/javascript">
				jQuery(document).ready(function( $ ){
					$(".coin-type-select").on( 'change', function(){
						var type = jQuery(this).val();
						var hiddenElements = jQuery( ".more_address_block input" );
						if( type == 2 ){
							$(".more_address_block").show('slow');
							hiddenElements.each( function(){
								$(this).attr( 'required', '' );
							});
						}else{
							hiddenElements.each( function(){
								$(this).removeAttr( 'required' );
							});
							$(".more_address_block").slideUp('slow');
						}
					});
					
				});
			</script>    
		<?php
	}

}
