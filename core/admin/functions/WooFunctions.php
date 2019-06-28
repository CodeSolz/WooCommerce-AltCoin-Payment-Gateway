<?php namespace WooGateWayCoreLib\admin\functions;
/**
 * Retrive Settings Data
 * 
 * @package WAPG Admin 
 * @since 1.0.0
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    exit;
}

class WooFunctions {

    private $WcPg_Instance;
    public $altcoin_instance;
    
    public function __construct(){
        
    }
    
    /**
     * Get woocommmerce / payment gateway info
     * 
     * @return type
     */
    public function get_payment_info(){
        
        if ( !class_exists( 'WC_Payment_Gateway' ) ){
            return false;
        }
        
        if( !isset( $this->WcPg_Instance ) ){
            $this->WcPg_Instance = new \WC_Payment_Gateways();
        }
        
        $payment_gateways = $this->WcPg_Instance->get_available_payment_gateways();
        
        if(isset($this->altcoin_instance)){
            return $this->altcoin_instance;
        }
        
        if( isset($payment_gateways['wapg_altcoin_payment']) ){
            $this->altcoin_instance = $payment_gateways['wapg_altcoin_payment'];
            return $this->altcoin_instance;
        }
        
        return false;
    }
    
    /**
     * 
     * @return typeGet altcoin gateway id
     */
    public static function get_altcoin_gateway_settings_id(){
        return 'woocommerce_wapg_altcoin_payment_settings';
    }
    
}