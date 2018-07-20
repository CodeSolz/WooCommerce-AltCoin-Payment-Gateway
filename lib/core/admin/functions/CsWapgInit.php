<?php namespace WooGateWayCoreLib\admin\functions;
/**
 * Welcome Info
 * 
 * @package WAPG Admin 
 * @since 1.0.0
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    exit;
}

class CsWapgInit{
    
    var $welcome_slug = 'welcome-to-woocommerce-altcoin-payment-gateway';
    
    function __construct() {
        //admin page
        add_action('admin_menu', array( $this, 'welcome_screen_page'));
        
        //remove submenu
        add_action( 'admin_head', array( $this, 'remove_menu_entry' ));
        
    }
    
    /**
     * Welcome screen page
     */
    public function welcome_screen_page(){
        $hook_suffix = add_dashboard_page('Welcome', 'Welcome', 'read', $this->welcome_slug, array( $this, 'welcome_page' ));
        add_action( 'load-' . $hook_suffix , array( $this, 'Wpag_load_function' ));
    }
    
    /**
     * Load function
     */
    public function Wpag_load_function(){
        add_action( 'admin_footer', array( $this, 'wpag_welcome_css'));
    }
    
    public function wpag_welcome_css(){
        ?>
        <style type="text/css">
            .welcome-wpag{text-align: center;}
            .welcome-wpag img{ border: 2px solid black; padding: 5px;}
        </style>
        <?php
    }
    /**
     * Welcome page design
     */
    function welcome_page() {
      ?>
      <div class="wrap welcome-wpag" >
        <h1><?php echo sprintf(__( 'Welcome to %s %s', CS_WAPG_TEXTDOMAIN ), CS_WAPG_PLUGIN_NAME, CS_WAPG_VERSION );?></h1>
        <p>
            <?php echo sprintf( __( 'Your plugin is ready to use. <a href="%s">Click here</a> for go to settings.'), admin_url('admin.php?page=wc-settings&tab=checkout&section=wapg_altcoin_payment') ); ?>
        </p>
        <img src="<?php echo CS_WAPG_PLUGIN_ASSET_URI; ?>/img/bitcoin-cash-bitcoin.jpg" />
      </div>
      <?php
    }
    
    /**
     * Remove menu entry
     */
    public function remove_menu_entry(){
        remove_submenu_page( 'index.php', $this->welcome_slug );
    }
}


?>