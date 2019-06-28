<?php namespace WooGateWayCoreLib\install;
/**
 * Installation Functions
 * 
 * @package DB
 * @since 1.0.8
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
   exit;
}

use WooGateWayCoreLib\admin\functions\CsAdminQuery;
use WooGateWayCoreLib\admin\functions\CsPaymentGateway;
use WooGateWayCoreLib\admin\functions\WooFunctions;

class Activate{
    
    /**
     * On install Create table
     * 
     * @global type $wpdb
     */
    public static function on_activate(){
        global $wpdb, $wapg_tables;
        $charset_collate = $wpdb->get_charset_collate();
        
        $sqls = array(
            "CREATE TABLE IF NOT EXISTS `{$wapg_tables['coins']}`(
            `id` int(11) NOT NULL auto_increment,
            `name` varchar(56),
            `coin_web_id` varchar(56),
            `symbol` varchar(20),
            `checkout_type` char(1),
            `status` char(1),
            PRIMARY KEY ( `id`)
            ) $charset_collate",
            "CREATE TABLE IF NOT EXISTS `{$wapg_tables['addresses']}`(
            `id` int(11) NOT NULL auto_increment,
            `coin_id` int(11),
            `address` varchar(1024),
            `lock_status` char(1),  
            PRIMARY KEY ( `id`)
            ) $charset_collate",
            "CREATE TABLE IF NOT EXISTS `{$wapg_tables['offers']}`(
            `id` int(11) NOT NULL auto_increment,
            `coin_id` int(11),
            `offer_amount` int(11),
            `offer_type` char(1),
            `offer_status` char(1),  
            `offer_show_on_product_page` char(1),  
            `offer_start` datetime,  
            `offer_end` datetime,  
            PRIMARY KEY ( `id`)
            ) $charset_collate",
            "CREATE TABLE IF NOT EXISTS `{$wapg_tables['coin_trxids']}`(
            `id` bigint(20) NOT NULL auto_increment,
            `cart_hash` varchar(128),
            `transaction_id` varchar(1024),
            `secret_word` varchar(1024),
            `used_in` datetime,  
            PRIMARY KEY ( `id`)
            ) $charset_collate"
        );
        
        foreach ( $sqls as $sql ) {
            if ( $wpdb->query( $sql ) === false ){
                continue;
            }
        }    
        
        //add db version to db
        add_option( 'wapg_db_version', CS_WAPG_DB_VERSION );
        
    }
    
    /**
     * check db status
     * 
     * @global type $wapg_current_db_version
     */
    public static function check_db_status(){
        global $wapg_current_db_version, $wpdb, $wapg_tables;
        $get_installed_db_version = get_site_option( 'wapg_db_version' );
        if( empty( $get_installed_db_version ) ){
            self::on_activate();
        }elseif( $get_installed_db_version != $wapg_current_db_version){
            
            $update_sqls = [];
            
            $import_coin_symbol = false;
            if( \version_compare( $get_installed_db_version, '1.0.2', '<=' ) ){
                $update_sqls = array(
                    "ALTER TABLE `{$wapg_tables['coins']}` ADD COLUMN symbol varchar(20) AFTER coin_web_id"        
                );
                $import_coin_symbol = true;
            }
            
            if( \version_compare( $get_installed_db_version, '1.0.1', '<=' ) ){
                $update_sqls = $update_sqls + array(
                    "ALTER TABLE `{$wapg_tables['addresses']}` CHANGE address address varchar(1024)",
                    "CREATE TABLE IF NOT EXISTS `{$wapg_tables['coin_trxids']}`(
                    `id` bigint(20) NOT NULL auto_increment,
                    `cart_hash` varchar(128),
                    `transaction_id` varchar(1024),
                    `secret_word` varchar(1024),
                    `used_in` datetime,  
                     PRIMARY KEY ( `id`)
                    ) $charset_collate"
                );
            }
            
                
            //update db
            foreach ( $update_sqls as $sql ) {
                if ( $wpdb->query( $sql ) === false ){
                    continue;
                }
            } 
                
            //add db version to db
            update_option( 'wapg_db_version', CS_WAPG_DB_VERSION );
            
            if( true === $import_coin_symbol ){
                self::import_coin_symbol();
            }
            
        }
    }

    /**
     * import coin symbol
     * 
     * @return boolean
     */
    public static function import_coin_symbol(){
        global $wapg_current_db_version, $wpdb, $wapg_tables;
        
        $CsAdminQuery = new CsAdminQuery();
        
        $get_coins = $wpdb->get_results( " select * from `{$wapg_tables['coins']}` " );
        if($get_coins){
            foreach( $get_coins as $coin){
                
                $currencies = $CsAdminQuery->get_all_coins_list(array(
                    'ticker' => $coin->coin_web_id
                ));
                
                if( true === $currencies['success'] ){
                    foreach( $currencies['data'] as $cur ){
                        if( $cur->slug == $coin->coin_web_id ){
                            $wpdb->update( $wapg_tables['coins'], array( 'symbol' => $cur->symbol ), array( 'coin_web_id' => $coin->coin_web_id ) );
                            break;
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Import old settings
     */
    public static function import_old_settins(){
        $get_installed_plugin_version = get_site_option( 'wapg_plugin_version' );
        
        //update plugin version
        update_option( 'wapg_plugin_version', CS_WAPG_VERSION );
        
        if( \version_compare( $get_installed_plugin_version, '1.2.4', '>=') ){
            return; //already imported old settings
        }else{
            
            //init gateway settings
            $WcFuncInstance = new WooFunctions();
            $default_settings = get_option($WcFuncInstance->get_altcoin_gateway_settings_id());

            //check new page options settings
            $checkout_options = CsPaymentGateway::get_checkout_page_options();
            if( empty($checkout_options ) ){
                $array = array(
                    'select_box_lebel' => isset($default_settings['select_box_lebel']) ? $default_settings['select_box_lebel'] : '',
                    'select_box_option_lebel' => isset($default_settings['select_box_option_lebel']) ? $default_settings['select_box_option_lebel'] : '',
                    'price_section_title' => isset($default_settings['price_section_title']) ? $default_settings['price_section_title'] : '',
                    'loader_gif_url' => isset($default_settings['loader_gif_url']) ? $default_settings['loader_gif_url'] : '',
                    'autotracking_gif_url' => isset($default_settings['autotracking_gif_url']) ? $default_settings['autotracking_gif_url'] : ''
                );
                CsPaymentGateway::save_checkout_page_optn_from_old( $array );
            }

            $checkout_options = CsPaymentGateway::get_product_page_options();
            if( empty($checkout_options ) ){
                $array = array(
                    'select_box_lebel' => isset($default_settings['select_box_lebel']) ? $default_settings['select_box_lebel'] : '',
                    'select_box_option_lebel' => isset($default_settings['select_box_option_lebel']) ? $default_settings['select_box_option_lebel'] : '',
                    'price_section_title' => isset($default_settings['price_section_title']) ? $default_settings['price_section_title'] : '',
                    'loader_gif_url' => isset($default_settings['loader_gif_url']) ? $default_settings['loader_gif_url'] : '',
                    'autotracking_gif_url' => isset($default_settings['autotracking_gif_url']) ? $default_settings['autotracking_gif_url'] : ''
                );
                CsPaymentGateway::save_product_page_optn_from_old( $array );
            }
        }
        
        
    }
    
}

