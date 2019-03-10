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

use WooGateWayCoreLib\lib\Util;

class CsAdminQuery {
    
    private $coin_name_arr;

    /**
     * Add new coin to payment gateway
     * 
     * @global type $wpdb
     */
    public function add_new_coin( $user_data ){
        global $wpdb, $wapg_tables;
//        $coin_info = $_POST['cs_add_new'];
        $coin_info = Util::check_evil_script( $user_data['cs_add_new'] );
        
        if( empty($coin_info['coin_address']) || empty( $coin_info['checkout_type'] ) || empty( $coin_info['coin_name'] ) ){
            wp_send_json( array(
                'status' => false,
                'title' => __( 'Error', 'woo-altcoin-payment-gateway' ),
                'text' => __( 'One or more required field is empty', 'woo-altcoin-payment-gateway' ),
            ));
        }
        
        if( empty( $coin_web_id = $this->get_coin_id( $coin_info['coin_name'] ) ) ){
            wp_send_json( array(
                'status' => false,
                'title' => __( 'Error', 'woo-altcoin-payment-gateway' ),
                'text' => __( 'Coin is not in service. Please make sure you have selected the coin name from the appeared dropdown list when you have typed coin name. Still problem : please contact support@codesolz.net for more information.', 'woo-altcoin-payment-gateway' ),
            ));
        }
        
        
        $get_coin_info = array(
            'name' => sanitize_text_field( $coin_info['coin_name'] ),
            'coin_web_id' => $coin_web_id,
            'checkout_type' => $coin_info['checkout_type'],
            'status' => isset( $coin_info['coin_status'] ) ? 1 : 0
        );
        $check_coin_exists = $wpdb->get_var( $wpdb->prepare( " select id from {$wapg_tables['coins']} where coin_web_id = %s ", $coin_web_id ) );
        if( $check_coin_exists ) {
            $coin_id = $check_coin_exists;
            $wpdb->update( "{$wapg_tables['coins']}", $get_coin_info, array( 'id' => $coin_id ));
        }else{
            $wpdb->insert( "{$wapg_tables['coins']}", $get_coin_info );
            $coin_id = $wpdb->insert_id;
        }
        
        $get_address_info = array(
            'coin_id' => $coin_id,
            'address' => $coin_info['coin_address'],
            'lock_status' => 0
        );
        $check_coin_address_exists = $wpdb->get_var( $wpdb->prepare( " select id from {$wapg_tables['addresses']} where coin_id = %d ", $coin_id ) );
        if( $check_coin_address_exists ) {
            $wpdb->update( "{$wapg_tables['addresses']}", $get_address_info, array( 'id' => $check_coin_address_exists ) );
        }else{
            $wpdb->insert( "{$wapg_tables['addresses']}", $get_address_info );
        }
        
        $get_offer_info = array(
            'coin_id' => $coin_id,
            'offer_amount' => isset($coin_info['offer_amount']) ? $coin_info['offer_amount'] : 0,
            'offer_type' => isset( $coin_info['offer_type']) ? $coin_info['offer_type'] : 0,
            'offer_status' => isset( $coin_info['offer_status'] ) ? 1 : 0,
            'offer_show_on_product_page' => isset( $coin_info['offer_show_on_product_page'] ) ? 1 : 0,
            'offer_start' => isset($coin_info['offer_start_date']) ? Util::get_formated_datetime( $coin_info['offer_start_date'] ) : '',
            'offer_end' => isset($coin_info['offer_end_date']) ? Util::get_formated_datetime( $coin_info['offer_end_date'] ) : ''
        );
        $check_coin_offer_exists = $wpdb->get_var( $wpdb->prepare( " select id from {$wapg_tables['offers']} where coin_id = %d ", $coin_id ) );
        
        if($check_coin_offer_exists){
            $wpdb->update( "{$wapg_tables['offers']}", $get_offer_info, array( 'id' => $check_coin_offer_exists ) );
        }else{
            $wpdb->insert( "{$wapg_tables['offers']}", $get_offer_info );
        }
        
        wp_send_json( array(
            'status' => true,
            'title' => __( 'Success', 'woo-altcoin-payment-gateway' ),
            'text' => __( 'Thank you! Coin has been added successfully.', 'woo-altcoin-payment-gateway' ),
            'redirect_url' => admin_url( 'admin.php?page=cs-woo-altcoin-all-coins')
        ));
    }
    
    /**
     * Update Coin
     * 
     * @global \WooGateWayCoreLib\admin\functions\type $wpdb
     * @global \WooGateWayCoreLib\admin\functions\type $wapg_tables
     */
    public function udpate_coin( $user_data ){
        global $wpdb, $wapg_tables;
        $coin_info = Util::check_evil_script($user_data['cs_add_new']);
        
        if( empty( $coin_id = $this->get_coin_id( $coin_info['coin_name'] ) ) ){
            wp_send_json( array(
                'status' => false,
                'title' => __( 'Error', 'woo-altcoin-payment-gateway' ),
                'text' => __( 'Ops! "'.$coin_info['coin_name'].'" coin is not in service. Please try differnt coin name', 'woo-altcoin-payment-gateway' ),
            ));
        }
        
        if( empty($coin_info['coin_address']) ){
            wp_send_json( array(
                'status' => false,
                'title' => __( 'Error', 'woo-altcoin-payment-gateway' ),
                'text' => __( 'Please enter coin address', 'woo-altcoin-payment-gateway' ),
            ));
        }
        
        $get_coin_info = array(
            'name' => $coin_info['coin_name'],
            'checkout_type' => $coin_info['checkout_type'],
            'status' => isset( $coin_info['coin_status'] ) ? 1 : 0
        );
        $wpdb->update( "{$wapg_tables['coins']}", $get_coin_info, array( 'id' => $coin_info['cid'] ));
        
        $get_address_info = array(
            'coin_id' => $coin_info['cid'],
            'address' => $coin_info['coin_address'],
            'lock_status' => 0
        );
        if( empty( $coin_info['aid'] ) ){
            $wpdb->insert( "{$wapg_tables['addresses']}", $get_address_info );
        }else{
            $wpdb->update( "{$wapg_tables['addresses']}", $get_address_info, array( 'id' => $coin_info['aid'] ) );
        }
        
        $get_offer_info = array(
            'coin_id' => $coin_info['cid'],
            'offer_amount' => $coin_info['offer_amount'],
            'offer_type' => $coin_info['offer_type'],
            'offer_status' => isset( $coin_info['offer_status'] ) ? 1 : 0,
            'offer_show_on_product_page' => isset( $coin_info['offer_show_on_product_page'] ) ? 1 : 0,
            'offer_start' => Util::get_formated_datetime( $coin_info['offer_start_date'] ),
            'offer_end' => Util::get_formated_datetime( $coin_info['offer_end_date'] )
        );
        if( empty( $coin_info['oid'] ) ){
            $wpdb->insert( "{$wapg_tables['offers']}", $get_offer_info );
        }else{
            $wpdb->update( "{$wapg_tables['offers']}", $get_offer_info, array( 'id' => $coin_info['oid'] ) );
        }
        
        wp_send_json( array(
            'status' => true,
            'title' => __( 'Success', 'woo-altcoin-payment-gateway' ),
            'text' => __( 'Thank you! Coin has been updated successfully.', 'woo-altcoin-payment-gateway' ),
            'redirect_url' => admin_url( 'admin.php?page=cs-woo-altcoin-all-coins')
        ));
    }
    
    /**
     * Get coin by field
     * 
     * @global \WooGateWayCoreLib\admin\functions\type $wpdb
     * @global type $wapg_tables
     * @param \WooGateWayCoreLib\admin\functions\type $field_name
     * @param \WooGateWayCoreLib\admin\functions\type $field_val
     * @return boolean
     */
    public static function get_coin_by( $field_name, $field_val ){
        $result = self::get_coins( array( 'where' => " c.{$field_name} = {$field_val} " ) );
        if( $result ){
            return $result[0];
        }
        
        return false;
    }
    
    /**
     * Get coins
     * 
     * @global \WooGateWayCoreLib\admin\functions\type $wpdb
     * @global \WooGateWayCoreLib\admin\functions\type $wapg_tables
     * @param type $args
     * @return boolean || array
     */
    public static function get_coins( $args ){
        global $wpdb, $wapg_tables;
        
        $where = '';
        if( isset( $args['where'] ) ){
            $where = ' where '. $args['where'];
        }
        
        $result = $wpdb->get_results( "SELECT *,c.id as cid, a.id as aid, o.id as oid from  {$wapg_tables['coins']} as c "
                . " left join {$wapg_tables['addresses']} as a on c.id = a.coin_id "
                . " left join {$wapg_tables['offers']} as o on c.id = o.coin_id "
                . " {$where} ");
                
        if( $result ){
            return $result;
        }        
        return false;
    }

    /**
     * Get offer info
     * 
     * @return boolean
     */
    public static function get_offers_info(){
        $result = self::get_coins( array( 'where' => " o.offer_status = 1 and o.offer_show_on_product_page = 1 and c.status = 1 " ) );
        if( $result ){
            return $result;
        }
        return false;
    }

    /**
     * get coin id
     */
    public function get_coin_id( $coin_name ){
        $currencies = file_get_contents(CS_WAPG_PLUGIN_ASSET_URI . 'js/currencies.json', FILE_USE_INCLUDE_PATH);
        $currencies = json_decode($currencies);
        $coin_id = '';
        foreach( $currencies as $cur ){
            if( $cur->name == $coin_name ){
                $coin_id = $cur->id;
                break;
            }
        }
        return $coin_id;
    }
    
    /**
     * get coin name by id
     */
    public static function get_coin_name_id( $coin_id ){
        $currencies = file_get_contents( CS_WAPG_PLUGIN_ASSET_URI . 'js/currencies.json', FILE_USE_INCLUDE_PATH );
        $currencies = json_decode( $currencies );
        $coin_name = '';
        foreach( $currencies as $cur ){
            if( $cur->id == $coin_id ){
                $coin_name = $cur->name;
                break;
            }
        }
        return $coin_name;
    }
    
    /**
     * Get coin name
     * 
     * @return type
     */
    public function get_coin_name(){
        if( isset( $this->coin_name_arr ) && !empty($this->coin_name_arr ) ){
            return wp_send_json($this->coin_name_arr);
        }
        $currencies = file_get_contents(CS_WAPG_PLUGIN_ASSET_URI . 'js/currencies.json', FILE_USE_INCLUDE_PATH);
        $currencies = json_decode($currencies);
        $ret = array();
        foreach( $currencies as $cur ){
            $ret[] = $cur->name;
        }
        $this->coin_name_arr = $ret;
        return wp_send_json($this->coin_name_arr);
    }
}
