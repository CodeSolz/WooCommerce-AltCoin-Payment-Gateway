<?php namespace WooGateWayCoreLib\Actions;

/**
 * Class: Register custom menu
 * 
 * @package Admin
 * @since 1.0.0
 * @author CodeSolz <customer-support@codesolz.net>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    die();
}


use WooGateWayCoreLib\Admin\options\Options_Pages;
use WooGateWayCoreLib\Admin\options\Scripts_Settings;
use WooGateWayCoreLib\admin\functions\WooFunctions;

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

    public function __construct() {
        //call wordpress admin menu hook
        add_action( 'admin_menu', array( $this, 'cs_register_wapg_menu' ) );
    }
    
    /**
     * Init current screen
     * 
     * @return type
     */
    public function init_current_screen(){
        $this->current_screen = get_current_screen();
        return $this->current_screen;
    }

    /**
     * Create plugins menu
     */
    public function cs_register_wapg_menu(){
        global $altcoin_menu;
        add_menu_page( 
            __( 'WooAltCoin Payment', 'woo-altcoin-payment-gateway' ),
            "AltCoin Payment",
            'manage_options',
            CS_WAPG_PLUGIN_IDENTIFIER,
            'cs-woo-altcoin-gateway',
            CS_WAPG_PLUGIN_ASSET_URI .'img/icon-24x24.png',
            57
        ); 
        
        $altcoin_menu['default_settings'] = add_submenu_page( 
            CS_WAPG_PLUGIN_IDENTIFIER,
            __( 'Settings', 'woo-altcoin-payment-gateway' ),
            "Settings",
            'manage_options',
            'cs-woo-altcoin-gateway-settings',
            array( $this, 'load_settings_page' )
        ); 
        $altcoin_menu['add_new_coin'] = add_submenu_page( 
            CS_WAPG_PLUGIN_IDENTIFIER,
            __( 'Add New Coin', 'woo-altcoin-payment-gateway' ),
            "Add New Coin",
            'manage_options',
            'cs-woo-altcoin-add-new-coin',
            array( $this, 'load_add_new_product_page' )
        ); 
        $altcoin_menu['all_coins_list'] = add_submenu_page( 
            CS_WAPG_PLUGIN_IDENTIFIER,
            __( 'All Coins', 'woo-altcoin-payment-gateway' ),
            "All Coins",
            'manage_options',
            'cs-woo-altcoin-all-coins',
            array( $this, 'load_all_product_page' )
        ); 
        
        //load script
        add_action( "load-{$altcoin_menu['default_settings']}", array( $this, 'register_admin_settings_scripts' ) );
        add_action( "load-{$altcoin_menu['add_new_coin']}", array( $this, 'register_admin_settings_scripts' ) );
        add_action( "load-{$altcoin_menu['all_coins_list']}", array( $this, 'register_admin_settings_scripts' ) );
        
        remove_submenu_page( CS_WAPG_PLUGIN_IDENTIFIER, CS_WAPG_PLUGIN_IDENTIFIER );
        
        //init pages
        $this->pages = new Options_Pages();
        
        //init gateway settings
        $this->WcFuncInstance = new WooFunctions();
    }
    
    /**
     * 
     * @return type
     */
    public function load_settings_page(){
        return $this->pages->app_settings( $this->WcFuncInstance->get_payment_info() );
    }
    
    /**
     * Load about us page
     * 
     * @return type
     */
    public function load_add_new_product_page(){
        return $this->pages->add_new_coin();
    }
    
    /**
     * load all products page
     */
    public function load_all_product_page(){
        return $this->pages->all_coins();
    }
    
    /**
     * load funnel builder scripts
     */
    public function register_admin_settings_scripts(){
        
        //register scripts
        add_action( 'admin_enqueue_scripts', array( $this, 'wapg_load_settings_scripts') );
        
        //init current screen
        $this->init_current_screen();
        
        //load all admin footer script
        add_action( 'admin_footer', array( $this, 'wapg_load_admin_footer_script') );
    }
    
    /**
     * Load admin scripts
     */
    public function wapg_load_settings_scripts( $page_id ){
        Scripts_Settings::load_admin_settings_scripts( $page_id );
    }
    
    /**
     * load custom scripts on admin footer
     */
    public function wapg_load_admin_footer_script(){
        Scripts_Settings::load_admin_footer_script( $this->current_screen->id );
    }
    
}
