<?php

namespace WooGateWayCoreLib\actions;

/**
 * Class: Register custom menu
 *
 * @package Admin
 * @since 1.0.0
 * @author CoinMarketStats <support@coinmarketstats.online>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
	die();
}

use WooGateWayCoreLib\lib\Util;
use WooGateWayCoreLib\admin\functions\WooFunctions;
use WooGateWayCoreLib\admin\options\Scripts_Settings;
use WooGateWayCoreLib\admin\builders\CsAdminPageBuilder;
use WooGateWayCoreLib\admin\functions\CsAutomaticOrderConfirmationSettings as AutoConfirm;

class RegisterCustomMenu {


	/**
	 * Hold pages
	 *
	 * @var type
	 */
	private $pages;

	/**
	 *
	 * @var type
	 */
	private $WcFunc;

	/**
	 *
	 * @var type
	 */
	public $current_screen;

	private static $_instance;

	public function __construct() {
		 // call WordPress admin menu hook
		add_action( 'admin_menu', array( $this, 'cs_register_wapg_menu' ) );
	}

	/**
	 * Init current screen
	 *
	 * @return type
	 */
	public function init_current_screen() {
		 $this->current_screen = get_current_screen();
		return $this->current_screen;
	}

	/**
	 * Create plugins menu
	 */
	public function cs_register_wapg_menu() {
		global $altcoin_menu;
		add_menu_page(
			__( 'WooAltCoin Payment', 'woo-altcoin-payment-gateway' ),
			'AltCoin Payment',
			'manage_options',
			CS_WAPG_PLUGIN_IDENTIFIER,
			'cs-woo-altcoin-gateway',
			CS_WAPG_PLUGIN_ASSET_URI . 'img/icon-24x24.png',
			57
		);

		$altcoin_menu['default_settings'] = add_submenu_page(
			CS_WAPG_PLUGIN_IDENTIFIER,
			__( 'Settings', 'woo-altcoin-payment-gateway' ),
			'Default Settings',
			'manage_options',
			'cs-woo-altcoin-gateway-settings',
			array( $this, 'load_settings_page' )
		);
		$altcoin_menu['all_coins_list']   = add_submenu_page(
			CS_WAPG_PLUGIN_IDENTIFIER,
			__( 'All Coins', 'woo-altcoin-payment-gateway' ),
			'All Coins',
			'manage_options',
			'cs-woo-altcoin-all-coins',
			array( $this, 'load_all_coins_list_page' )
		);
		$altcoin_menu['add_new_coin']     = add_submenu_page(
			CS_WAPG_PLUGIN_IDENTIFIER,
			__( 'Add New Coin', 'woo-altcoin-payment-gateway' ),
			'Add New Coin',
			'manage_options',
			'cs-woo-altcoin-add-new-coin',
			array( $this, 'load_add_new_coin_page' )
		);

		$altcoin_menu['product_page_options_settings'] = add_submenu_page(
			CS_WAPG_PLUGIN_IDENTIFIER,
			__( 'Product Page Options', 'woo-altcoin-payment-gateway' ),
			'Product Page Options',
			'manage_options',
			'cs-woo-altcoin-product-option-settings',
			array( $this, 'load_product_page_option_settings' )
		);

		$altcoin_menu['checkout_options_settings'] = add_submenu_page(
			CS_WAPG_PLUGIN_IDENTIFIER,
			__( 'Checkout Page', 'woo-altcoin-payment-gateway' ),
			'Checkout Page Options',
			'manage_options',
			'cs-woo-altcoin-checkout-option-settings',
			array( $this, 'load_checkout_settings_page' )
		);
		$altcoin_menu['widget_options_settings']   = add_submenu_page(
			CS_WAPG_PLUGIN_IDENTIFIER,
			__( 'Widget Options', 'woo-altcoin-payment-gateway' ),
			'Widget Options',
			'manage_options',
			'cs-woo-altcoin-widget-settings',
			array( $this, 'load_widget_options_page' )
		);
		$altcoin_menu['register_automatic_order']  = add_submenu_page(
			CS_WAPG_PLUGIN_IDENTIFIER,
			__( 'Automatic Order Confirmation Registration', 'woo-altcoin-payment-gateway' ),
			'Automatic Order',
			'manage_options',
			'cs-woo-altcoin-automatic-order-confirmation-settings',
			array( $this, 'load_automatic_order_confirmation_settings_page' )
		);

		if ( false === AutoConfirm::hasPaid() ) {
			$altcoin_menu['wapg_go_pro'] = add_submenu_page(
				CS_WAPG_PLUGIN_IDENTIFIER,
				__( 'Go Pro', 'woo-altcoin-payment-gateway' ),
				'<span class="dashicons dashicons-star-filled wapg-go-pro-link" style="font-size: 17px"></span> <span class="wapg-go-pro-link">' . __( 'Go Pro Plan', 'woo-altcoin-payment-gateway' ) . '</span>',
				'manage_options',
				'cs-wapg-go-pro',
				array( $this, 'wapg_gopro_redirects' )
			);

		}

		// load script
		add_action( "load-{$altcoin_menu['default_settings']}", array( $this, 'register_admin_settings_scripts' ) );
		add_action( "load-{$altcoin_menu['register_automatic_order']}", array( $this, 'register_admin_settings_scripts' ) );
		add_action( "load-{$altcoin_menu['add_new_coin']}", array( $this, 'register_admin_settings_scripts' ) );
		add_action( "load-{$altcoin_menu['all_coins_list']}", array( $this, 'register_admin_settings_scripts' ) );
		add_action( "load-{$altcoin_menu['checkout_options_settings']}", array( $this, 'register_admin_settings_scripts' ) );
		add_action( "load-{$altcoin_menu['product_page_options_settings']}", array( $this, 'register_admin_settings_scripts' ) );
		add_action( "load-{$altcoin_menu['widget_options_settings']}", array( $this, 'register_admin_settings_scripts' ) );

		remove_submenu_page( CS_WAPG_PLUGIN_IDENTIFIER, CS_WAPG_PLUGIN_IDENTIFIER );

		// init pages
		$this->pages = new CsAdminPageBuilder();

		// init gateway settings
		$this->WcFuncInstance = new WooFunctions();
	}

	/**
	 * Generate default settings page
	 *
	 * @return type
	 */
	public function load_settings_page() {
		// check woocommerce is loaded
		$this->isWoocommerceInstalled();

		$Default_Settings = $this->pages->DefaultSettings();
		if ( is_object( $Default_Settings ) ) {
			echo $Default_Settings->generate_default_settings(
				array_merge_recursive(
					array(
						'title'     => __( 'Gateway Default Settings', 'woo-altcoin-payment-gateway' ),
						'sub_title' => __( 'Alltcoin payment gatway defult settings. Please fill up the following information correctly.', 'woo-altcoin-payment-gateway' ),
					),
					array( 'gateway_settings' => (array) $this->WcFuncInstance->get_payment_info() )
				)
			);
		} else {
			echo $Default_Settings;
		}
	}

	/**
	 * Generate checkout settings page
	 *
	 * @return type
	 */
	public function load_checkout_settings_page() {
		 // check woocommerce is loaded
		$this->isWoocommerceInstalled();

		$Checkout_Page_Settings = $this->pages->CheckoutPageSettings();
		if ( is_object( $Checkout_Page_Settings ) ) {
			echo $Checkout_Page_Settings->generate_checkout_settings(
				array(
					'title'     => __( 'Checkout Page Options', 'woo-altcoin-payment-gateway' ),
					'sub_title' => __( 'Following options will be applied to the checkout page', 'woo-altcoin-payment-gateway' ),
				)
			);
		} else {
			echo $Checkout_Page_Settings;
		}
	}

	/**
	 * Generate product page options settings
	 *
	 * @return type
	 */
	public function load_product_page_option_settings() {
		// check woocommerce is loaded
		$this->isWoocommerceInstalled();

		$Product_PageOptions = $this->pages->ProductPageOptions();
		if ( is_object( $Product_PageOptions ) ) {
			echo $Product_PageOptions->generate_product_options_settings(
				array(
					'title'     => __( 'Product Page Options', 'woo-altcoin-payment-gateway' ),
					'sub_title' => __( 'Following options will be applied to the product\'s page', 'woo-altcoin-payment-gateway' ),
				)
			);
		} else {
			echo $Product_PageOptions;
		}
	}

	public function load_widget_options_page() {
		// check woocommerce is loaded
		$this->isWoocommerceInstalled();

		$WidgetPageOptions = $this->pages->WidgetPageOptions();
		if ( is_object( $WidgetPageOptions ) ) {
			echo $WidgetPageOptions->generate_product_options_settings(
				array(
					'title'     => __( 'Widget Options', 'woo-altcoin-payment-gateway' ),
					'sub_title' => __( 'Following options will be applied to the widgets', 'woo-altcoin-payment-gateway' ),
				)
			);
		} else {
			echo $WidgetPageOptions;
		}
	}

	/**
	 *
	 * @return type
	 */
	public function load_automatic_order_confirmation_settings_page() {
		 // check woocommerce is loaded
		$this->isWoocommerceInstalled();

		$Auto_Order_Settings = $this->pages->AutoOrderSettings();
		if ( is_object( $Auto_Order_Settings ) ) {
			echo $Auto_Order_Settings->generate_settings(
				array(
					'title'     => __( 'Automatic Order Confirmation Settings', 'woo-altcoin-payment-gateway' ),
					'sub_title' => __( 'Please complete your registration to use automatic order confirmation.', 'woo-altcoin-payment-gateway' ),
				)
			);
		} else {
			echo $Auto_Order_Settings;
		}
	}

	/**
	 * Load add new coin page
	 *
	 * @return type
	 */
	public function load_add_new_coin_page() {
		$Add_New_Coin = $this->pages->AddNewCoin();
		if ( is_object( $Add_New_Coin ) ) {
			echo $Add_New_Coin->add_new_coin(
				array(
					'title'     => __( 'Add New Coin', 'woo-altcoin-payment-gateway' ),
					'sub_title' => __( 'Please fill up the following information correctly to add new coin to payment method.', 'woo-altcoin-payment-gateway' ),
				)
			);
		} else {
			echo $Add_New_Coin;
		}
	}

	/**
	 * load all products page
	 */
	public function load_all_coins_list_page() {
		$Coin_List = $this->pages->AllCoins();
		if ( is_object( $Coin_List ) ) {
			echo $Coin_List->generate_coin_list(
				array(
					'title'     => __( 'All Coins', 'woo-altcoin-payment-gateway' ),
					'sub_title' => __( 'Following coins has been added to the payment gateway\'s coin list.', 'woo-altcoin-payment-gateway' ),
				)
			);
		} else {
			echo esc_html( $Coin_List );
		}
	}

	/**
	 * load funnel builder scripts
	 */
	public function register_admin_settings_scripts() {
		// register scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'wapg_load_settings_scripts' ) );

		// init current screen
		$this->init_current_screen();

		// load all admin footer script
		add_action( 'admin_footer', array( $this, 'wapg_load_admin_footer_script' ) );
	}

	/**
	 * Load admin scripts
	 */
	public function wapg_load_settings_scripts( $page_id ) {
		Scripts_Settings::load_admin_settings_scripts( $page_id );
	}

	/**
	 * load custom scripts on admin footer
	 */
	public function wapg_load_admin_footer_script() {
		Scripts_Settings::load_admin_footer_script( $this->current_screen->id );
	}

	/**
	 * Check woocommerce plugin installed
	 *
	 * @return boolean
	 */
	private function isWoocommerceInstalled() {
		if ( false === IS_WOOCOMMERCE_INSTALLED ) {
			?>
		<script>
			window.location.href = '<?php echo admin_url( 'plugins.php' ); ?>';
		</script>
			<?php

		}
		return true;
	}

	/**
	 * Handler expernal redirect
	 *
	 * @return void
	 */
	public function wapg_gopro_redirects() {
		if ( empty( $_GET['page'] ) ) {
			return;
		}

		if ( 'cs-wapg-go-pro' === $_GET['page'] ) {
			\wp_redirect( Util::cs_get_pro_link( 'https://coinmarketstats.online/product/woocommerce-bitcoin-altcoin-payment-gateway?utm_source=wp-menu&utm_campaign=gopro&utm_medium=wp-dash' ) );
			die;
		}
	}

	/**
	 * generate instance
	 *
	 * @return void
	 */
	public static function get_instance() {
		if ( ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

}
