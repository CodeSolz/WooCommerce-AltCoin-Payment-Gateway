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
use WooGateWayCoreLib\admin\functions\CsAutomaticOrderConfirmationSettings;

class CsAdminQuery {
    
    private $auto_order_coins_list_url = 'https://myportal.coinmarketstats.online/api/auto-tracking-coin-list';
    private $all_listed_coins_url = 'https://myportal.coinmarketstats.online/api/all-listed-coins';
    private $coin_name_arr;

    /**
     * Add new coin to payment gateway
     * 
     * @global type $wpdb
     */
    public function add_new_coin( $user_data ){
        global $wpdb, $wapg_tables;
        $coin_info = Util::check_evil_script( $user_data['cs_add_new'] );
        
        if( empty($coin_info['coin_address']) || empty( $coin_info['checkout_type'] ) || empty( $coin_info['coin_name'] ) ){
            wp_send_json( array(
                'status' => false,
                'title' => __( 'Error', 'woo-altcoin-payment-gateway' ),
                'text' => __( 'One or more required field is empty', 'woo-altcoin-payment-gateway' ),
            ));
        }
        
        //check coin already exists
        $check_coin_exists = $wpdb->get_var( $wpdb->prepare( " select id from {$wapg_tables['coins']} where name = '%s' and checkout_type = %d ", $coin_info['coin_name'], $coin_info['checkout_type'] ) );
        if( $check_coin_exists ){
            wp_send_json( array(
                'status' => false,
                'title' => __( 'Error', 'woo-altcoin-payment-gateway' ),
                'text' => sprintf( __( '"%s" already added. Please check the list from "All Coins" menu.', 'woo-altcoin-payment-gateway' ), $coin_info['coin_name'] ),
            ));
        }
        
        if( empty( $coin_web_id = $this->get_coin_id( $coin_info['coin_name'], $coin_info['checkout_type'] ) ) ){
            wp_send_json( array(
                'status' => false,
                'title' => __( 'Error', 'woo-altcoin-payment-gateway' ),
                'text' => __( 'Coin is not in service. Please make sure you have selected the coin name from the dropdown list when you have typed coin name. Still problem after selecting from dropdown? please contact support@codesolz.net for more information.', 'woo-altcoin-payment-gateway' ),
            ));
        }
        
        
        $get_coin_info = array(
            'name' => sanitize_text_field( $coin_info['coin_name'] ),
            'symbol' => $coin_web_id->symbol,
            'coin_web_id' => $coin_web_id->slug,
            'checkout_type' => $coin_info['checkout_type'],
            'status' => isset( $coin_info['coin_status'] ) ? 1 : 0
        );
        $check_coin_exists = $wpdb->get_var( $wpdb->prepare( " select id from {$wapg_tables['coins']} where coin_web_id = %s ", $coin_web_id->slug ) );
        if( $check_coin_exists ) {
            $coin_id = $check_coin_exists;
            $wpdb->update( "{$wapg_tables['coins']}", $get_coin_info, array( 'id' => $coin_id ));
        }else{
            $wpdb->insert( "{$wapg_tables['coins']}", $get_coin_info );
            $coin_id = $wpdb->insert_id;
        }
        
      
        //add coin address
        if( $coin_info['checkout_type'] == 2 ){
            $coin_info['cid'] = $coin_id;
            $more_address_fields = Util::check_evil_script($user_data['more_coin_address']);
            $more_address_fields[] = $coin_info['coin_address'];
            for($i =0; $i < count($more_address_fields); $i++ ){
                $coin_info['aid'] = '';
                $coin_info['coin_address'] = $more_address_fields[$i];
                $this->coin_address_update( $coin_info );
            }
        }else{
            $this->coin_address_update( $coin_info );
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
        $more_address_fields = Util::check_evil_script($user_data['more_coin_address']);
        
        if( empty( $coin_id = $this->get_coin_id( $coin_info['coin_name'], $coin_info['checkout_type'] ) ) ){
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
        
        if( $coin_info['checkout_type'] == 2 ){
            $more_address_fields[] = $coin_info['coin_address'];
            //get coin address id
            $coin_address = $wpdb->get_results( $wpdb->prepare( "select id from {$wapg_tables['addresses']} where coin_id = %d ", $coin_info['cid'] ) );
            for($i =0; $i < count($more_address_fields); $i++ ){
                $coin_info['aid'] = empty($coin_address) ? '' : $coin_address[$i]->id;
                $coin_info['coin_address'] = $more_address_fields[$i];
                $this->coin_address_update( $coin_info );
            }
        }else{
            $this->coin_address_update( $coin_info );
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
     * coin address update
     * 
     */
    private function coin_address_update( $coin_info ){
        global $wpdb, $wapg_tables;
        $get_address_info = array(
            'coin_id' => $coin_info['cid'],
            'address' => $coin_info['coin_address'],
            'lock_status' => 0
        );
        
        if( isset($coin_info['aid']) && !empty($coin_info['aid']) ) {
            $wpdb->update( "{$wapg_tables['addresses']}", $get_address_info, array( 'id' => $coin_info['aid'] ) );
        }else{
            $wpdb->insert( "{$wapg_tables['addresses']}", $get_address_info );
        }
        
        return true;
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
        
        $result = $wpdb->get_results( "SELECT *,c.id as cid, a.id as aid, o.id as oid, GROUP_CONCAT(address SEPARATOR ', ')  as address from  {$wapg_tables['coins']} as c "
                . " left join {$wapg_tables['addresses']} as a on c.id = a.coin_id "
                . " left join {$wapg_tables['offers']} as o on c.id = o.coin_id "
                . " {$where} group by a.coin_id order by c.name asc");
                
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
    public function get_coin_id( $coin_name, $checkout_type ){
        $currencies = $this->get_all_coins_list(array(
            'ticker' => $coin_name
        ));
        
        if( isset($currencies['success']) && $currencies['success'] == true && $currencies['data'][0]->name == $coin_name ){
            if( $checkout_type == 2 && $currencies['data'][0]->is_automatic_order_paid == 1){
                return $currencies['data'][0];
            }elseif( $checkout_type == 1 ){
                return $currencies['data'][0];
            }
        }
        
        return false;
    } 
    
    /**
     * Get coin name - dropdown typehead
     * 
     * @return type
     */
    public function get_coin_name( $user_input ){
        $oc_type = $user_input['oc_type'];
        if( $oc_type == 1 ){
            $query = $user_input['query'];
            if( empty($query)){
                return wp_send_json(array(
                    'success' => false,
                    'response' => 'Please enter coin name.'
                ));
            }
            $currencies = $this->get_all_coins_list(array(
                'ticker' => $query
            ));
            
            //check is coin active
            if( false === $currencies['is_active'] ){
                return wp_send_json(array(
                    'success' => false,
                    'response' => 'This coin is not activated! Please contact support@codesolz.net to activate this coin.'
                ));
            }

            if( true === $currencies['success'] ){
                $ret = array();
                foreach( $currencies['data'] as $cur ){
                    $ret[] = $cur->name;
                }
                return wp_send_json($ret);
            }
            return wp_send_json(array(
                'success' => false,
                'response' => 'Nothing found!'
            ));
        
        }elseif( $oc_type == 2 ){
            return wp_send_json( $this->get_auto_order_coins_list() );
        }else{
            return wp_send_json(array(
                'success' => false,
                'response' => 'Please select "Payment Confirmation Type" at first!'
            ));
        }
    }
    
    /**
     * Get list of coins for automatic 
     * order confirmation
     * 
     * @return array
     */
    private function get_auto_order_coins_list(){
        //get data from portal
        $user_data = CsAutomaticOrderConfirmationSettings::get_order_confirm_settings_data();
        $api_status = Util::remote_call(
            $this->auto_order_coins_list_url, 
            'POST',
            array(
                'body' => $user_data
            )
        );

        if( isset($api_status['error'])){
            return array(
                'success' => false,
                'response' => $api_status['response']
            );
        }else{
            $api_status = json_decode( $api_status);
            if( isset( $api_status->success ) && $api_status->success == true ){
                return $api_status->coin_list;
            }else{
                return array(
                    'success' => false,
                    'response' => $api_status->message
                );
            }
        }
    }
    
    /**
     * Get list of all coins  
     * 
     * @return array
     */
    public function get_all_coins_list( $slug = [] ){
        
        $request_params = empty($slug) ? '' : '?'. http_build_query( $slug );
        
        $api_status = Util::remote_call(
            $this->all_listed_coins_url . $request_params
        );

        if( isset($api_status['error'])){
            return array(
                'success' => false,
                'response' => $api_status['response']
            );
        }else{
            $api_status = json_decode( $api_status);
            if( isset( $api_status->status ) && $api_status->status == 200 ){
                return array(
                    'success' => true,
                    'data' => $api_status->data
                );
            }else{
                return array(
                    'success' => false,
                    'response' => $api_status->response
                );
            }
        }
    }
    
    
}
