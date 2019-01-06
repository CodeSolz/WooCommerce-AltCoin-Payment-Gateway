<?php
/**
 * @wordpress-plugin
 * Plugin Name:       WooCommerce AltCoin Payment Gateway
 * Plugin URI:        https://wordpresspremiumplugins.com/download/woocommerce-altcoin-payment-gateway/
 * Description:       Woocommerce payment gateway to accept crypto currency in your store.
 * Version:           1.1.2
 * Author:            CodeSolz
 * Author URI:        https://www.codesolz.net
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl.txt
 * Domain Path:       /languages
 * Text Domain:       woo-altcoin-payment-gateway
 * Requires PHP: 5.4
 * Requires At Least: 4.0
 * Tested Up To: 5.0.2
 * WC requires at least: 3.0
 * WC tested up to: 3.5.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Woocommerce_Altcoin_payment' ) ){
    
    class Woocommerce_Altcoin_payment {
        
        /**
         * Hold actions hooks
         *
         * @var type 
         */
        private static $wapg_hooks = [];
        
        /**
         * Hold version
         * 
         * @var String 
         */
        private static $version = '1.1.2';
        
        /**
         * Hold version
         * 
         * @var String 
         */
        private static $db_version = '1.0.0';

        /**
         * Hold namespace
         *
         * @var type 
         */
        private static $namespace = 'WooGateWayCoreLib';
                
        
        function __construct(){
            
            //load plugins constant
            self::set_constant();
            
            //load core files
            self::load_core_framework();
            
            //load init
            self::load_action_files();
            
            //during activation
            self::init_activation();
            
            /**load textdomain */
            add_action( 'plugins_loaded', array( __CLASS__, 'init_textdomain' ), 15 );
            
            /**load gateway*/
            add_action( 'plugins_loaded', array( __CLASS__, 'check_db' ), 17 );
            
            /**load gateway*/
            add_action( 'plugins_loaded', array( __CLASS__, 'init_gateway' ), 19 );
        }
        
        /**
         * Set constant data
         */
        private static function set_constant(){
            /**
             * Define current version
             */
            define( 'CS_WAPG_VERSION', self::$version );
            
            /**
             * Define current db version
             */
            define( 'CS_WAPG_DB_VERSION', self::$db_version );

            /**
            * Hold plugins base dir path
            */
            define( 'CS_WAPG_BASE_DIR_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/' ) ;
            
            /**
             * Define asset uri
             */
            define( 'CS_WAPG_PLUGIN_ASSET_URI', plugin_dir_url( __FILE__ ) . 'assets/' );

            /**
             * Library uri
             */
            define( 'CS_WAPG_PLUGIN_LIB_URI', plugin_dir_url( __FILE__ ) . 'lib/' );

            /**
             * plugins identifier
             */
            define( 'CS_WAPG_PLUGIN_IDENTIFIER', plugin_basename( __FILE__ ) );

            /**
             * Plugin name
             */
            define( 'CS_WAPG_PLUGIN_NAME', 'WooCommerce AltCoin Payment Gateway' );
        }
        
        /**
         * load core framework
         */
        private static function load_core_framework(){
            require_once CS_WAPG_BASE_DIR_PATH . '/vendor/autoload.php';
        }
        
        /**
         * load action files
         */
        private static function load_action_files(){
            foreach ( glob( CS_WAPG_BASE_DIR_PATH . "core/actions/*.php") as $cs_action_file ) {
                $class_name = basename( $cs_action_file, '.php' );
                $class =  self::$namespace . '\\actions\\'. $class_name; 
                if ( ! array_key_exists( $class, self::$wapg_hooks ) ) { //check class doesn't load multiple time
                    self::$wapg_hooks[ $class ] = new $class();
                }
            }
            
        }
        
        /**
         * init activation hook
         */
        private static function init_activation(){
            //load config
            require_once CS_WAPG_BASE_DIR_PATH . '/core/install/wapg_config.php';
            //register hook
            register_activation_hook( __FILE__, array( 'WooGateWayCoreLib\\install\\Activate', 'on_activate' ) );
            
            return true;
        }

        /**
         * init textdomain
         */
        public static function init_textdomain(){
            load_plugin_textdomain( 'woo-altcoin-payment-gateway', false, CS_WAPG_BASE_DIR_PATH . '/languages/' );
        }
        
        /**
         * Init payment gateway
         * 
         * @return \WooGateWayCoreLib\admin\settings\CsGateWaySettingsInit Gateway
         */
        public static function init_gateway(){
            if ( class_exists( 'WC_Payment_Gateway' ) ){
                return new WooGateWayCoreLib\admin\settings\CsGateWaySettings();
            }else{
                new WooGateWayCoreLib\admin\functions\CsWccpgNotice( 1 );
            }
            
        }

        /**
         * Check db status
         */
        public static function check_db(){
            WooGateWayCoreLib\install\Activate::check_db_status();
        }
        
    }
    
    global $WAPG;
    $WAPG = new Woocommerce_Altcoin_payment();
}