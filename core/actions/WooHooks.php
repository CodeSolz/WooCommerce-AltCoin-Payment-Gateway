<?php namespace WooGateWayCoreLib\Actions;

/**
 * Class: Woocommerce Default Hooks
 * 
 * @package Admin
 * @since 1.0.0
 * @author CodeSolz <customer-support@codesolz.net>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    die();
}

use WooGateWayCoreLib\frontend\functions\CsWapgCustomTy;

class WooHooks {
    
    private $Thank_You_Page;
            
    function __construct() {
        add_action( 'woocommerce_order_details_after_order_table', array( $this, 'wapg_order_summary'), 20 );
        
        $this->Thank_You_Page = new CsWapgCustomTy();
    }
    
    /**
     * 
     * @return typeReturn order summery in thank you page
     */
    public function wapg_order_summary(){
        return $this->Thank_You_Page->order_summary();
    }
    
}
