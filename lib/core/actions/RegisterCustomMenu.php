<?php namespace CsFunnelBuilder\Actions;

/**
 * Class: Register Admin Menu
 * 
 * @package CsFunnelBuilder
 * @since 1.0.0
 * @author CodeSolz <customer-service@codesolz.net>
 */

if ( ! defined( 'CS_SFB_VERSION' ) ) {
    exit;
}

use CsFunnelBuilder\lib\admin\builders\pages;

class RegisterCustomMenu {
    
    /**
     * Hold pages
     *
     * @var type 
     */
    private $pages;

    public function __construct() {
        //call wordpress admin menu hook
        add_action( 'admin_menu', array( $this, 'cs_register_sfb_menu' ) );
        
    }
    
    /**
     * Create plugins menu
     */
    public function cs_register_sfb_menu(){
        add_menu_page( 
            __( 'Cs Funnel Builder', CS_SFB_TEXTDOMAIN ),
            "Funnel Builder",
            'read',
            CS_SFB_PLUGINS_INDEX_FILE,
            'cs-sfb-custom-funnel-builder',
            CS_SFB_PLUGINS_DIR_URI .'images/funnel-icon.png',
            30.1
        ); 
        
        $settings_page = add_submenu_page( 
            CS_SFB_PLUGINS_INDEX_FILE,
            __( 'Settings', CS_SFB_TEXTDOMAIN ),
            "App Settings",
            'read',
            'cs-sfb-funnel-builder-settings',
            array( $this, 'load_settings_page' )
        ); 
        //load script
        add_action( "load-{$settings_page}", array( $this, 'register_admin_settings_scripts' ) );
        
        
        $about_us_page = add_submenu_page( 
            CS_SFB_PLUGINS_INDEX_FILE,
            __( 'About Us', CS_SFB_TEXTDOMAIN ),
            "About Us",
            'read',
            'cs-sfb-about-us',
            array( $this, 'load_about_us_page' )
        ); 
        
        add_action( "load-{$about_us_page}", array( $this, 'register_admin_settings_scripts' ) );
        
        remove_submenu_page( CS_SFB_PLUGINS_INDEX_FILE, CS_SFB_PLUGINS_INDEX_FILE );
        
        //init pages
        $this->pages = new pages();
    }
    
    /**
     * 
     * @return type
     */
    public function load_settings_page(){
        return $this->pages->app_settings();
    }
    
    /**
     * Load about us page
     * 
     * @return type
     */
    public function load_about_us_page(){
        return $this->pages->about_us();
    }
    
    /**
     * load funnel builder scripts
     */
    public function register_admin_settings_scripts(){
        add_action( 'admin_enqueue_scripts', array( $this, 'load_settings_scripts') );
        
        //load on footer section
        add_action( 'admin_footer', array( $this, 'load_settngs_custom_script') );
    }
    
    /**
     * Load admin scripts
     */
    public function load_settings_scripts(){
        SettingScripts::load_admin_settings_scripts();
    }
    
    /**
     * load custom script on seetings
     */
    public function load_settngs_custom_script(){
        SettingScripts::load_admin_settings_custom_scripts();
    }
    
    /**
     * Add plugins action link
     * 
     * @param type $links
     * @param type $file
     * @return type
     */
    public function add_action_link( $links, $file ){
        return $this->pages->add_action_link( $links, $file );
    }
}
