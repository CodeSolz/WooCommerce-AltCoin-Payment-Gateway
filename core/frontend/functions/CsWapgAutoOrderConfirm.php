<?php namespace WooGateWayCoreLib\frontend\functions;
/**
 * Front End Functions
 * 
 * @package WAPG FE 
 * @since 1.2.3
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    exit;
}

use WooGateWayCoreLib\lib\Util;
use WooGateWayCoreLib\lib\cartFunctions;
use WooGateWayCoreLib\admin\functions\CsAdminQuery;
use WooGateWayCoreLib\admin\functions\CsAutomaticOrderConfirmationSettings;

class CsWapgAutoOrderConfirm {
    
    /**
     * Hold Trxid validator api url
     *
     * @var type 
     */
    private $tracking_api_url = 'https://api.coinmarketstats.online/trxid_validator/v1/%s/%s/%s/%s/%s/%s';
    
    /**
     * Force end
     */
    private $force_end_api_url = 'https://api.coinmarketstats.online/trxid_validator/v1/%s/%s/%s/%s/%s/%s/%s';

    
    public function track_coin( $raw_data ){
        global $woocommerce;
        
        $raw_data = $raw_data['form_data'];
        $data = [];
        parse_str( $raw_data, $data );
        $premade_order_id = $data['premade_order_id'];
        
        if( isset( $data['trxid']) && empty($trxid = $data['trxid'])){
            return wp_send_json( Util::notice_html(array(
                'error' => true,
                'response' => __( 'Please enter your coin transaction ID. Make sure you have fill up all the required fields marked as (*)', 'woo-altcoin-payment-gateway' )
            )));
        }
        
        if( isset( $data['secret_word']) && empty($secret_word = $data['secret_word'])){
            return wp_send_json(Util::notice_html(array(
                'error' => true,
                'response' => __( 'Please enter a secret word. Make sure you have fill up all the required fields marked as (*).', 'woo-altcoin-payment-gateway' )
            )));
        }
        
        //check transaction was successful - return true if success
        // if( cartFunctions::get_transaction_successful_log( $premade_order_id ) == 'success' ){
        //     return wp_send_json(Util::notice_html(array(
        //         'success' => true,
        //         'response' => __( 'Thank you! Transaction completed successfully. Your order is processing right now!', 'woo-altcoin-payment-gateway' )
        //     )));
        // }
        
        //check first time
        $trxid_validator = cartFunctions::temp_update_trx_info( $trxid, $secret_word, $premade_order_id );
        if( false === $trxid_validator ){
            return wp_send_json(Util::notice_html(array(
                'error' => true,
                'response' => __( 'We are unable to match your payment information. Make sure you have entered the correct "secret word" used first time with this transaction id.', 'woo-altcoin-payment-gateway' )
            )));
        }
        
        
        $config = CsAutomaticOrderConfirmationSettings::get_order_confirm_settings_data();
        if( isset($config['api_key']) && empty( $api_key = $config['api_key'])){
            return wp_send_json(Util::notice_html(array(
                'error' => true,
                'response' => __( 'Api key not found! Please contact site administrator.', 'woo-altcoin-payment-gateway' )
            )));
        }
        
        $cart_info = cartFunctions::get_current_cart_payment_info( $premade_order_id );
        if( empty($cart_info)){
            return wp_send_json(Util::notice_html(array(
                'error' => true,
                'response' => __( 'Something went wrong. Please refresh the page and try again.', 'woo-altcoin-payment-gateway' )
            )));
        }
        

        $cart_info = array_map( 'trim', $cart_info);
        $cartTotal = empty($cart_info['cartTotalAfterDiscount']) ? $cart_info['cartTotal'] : $cart_info['cartTotalAfterDiscount'];
        $api_url = sprintf( $this->tracking_api_url, 
            $api_key, $cart_info['coinName'], $cart_info['coinAddress'], Util::check_evil_script($trxid), 
            $cart_info['totalCoin'], trim($cartTotal)
        );

        
        $response = Util::remote_call( $api_url );
        $response = json_decode( $response );
        
        if( is_object( $response ) ){
            if( isset($response->error) && true === $response->error ){
                //remove temp transaction data
                cartFunctions::temp_remove_trx_info( $trxid, $premade_order_id );
                return wp_send_json(Util::notice_html(array(
                    'error' => true,
                    'response' => isset($response->response) ? $response->response : $response->message
                )));
            }
            elseif( isset($response->success) && false === $response->success ){
                
                $response_msg = '';
                if( isset($response->response) ){
                    $response_msg = $response->response;
                }
                elseif( isset($response->message) ){
                    $response_msg = $response->message;
                }else{
                    $confirmation = 0;
                    if( isset( $response->confirmation ) && $response->confirmation > 0 ){
                        $confirmation = $response->confirmation;
                    }
                    
                    $con_count = isset($config['confirmation_count']) && !empty( $config['confirmation_count'] ) ?
                            $config['confirmation_count'] : 6;
                    
                    //force successful confirmation
                    if( $confirmation >= $con_count ){
                        
                        //send status
                        $api_url = sprintf( $this->force_end_api_url, 
                            $api_key, 'forceend', $address, Util::check_evil_script($trxid), 
                            $amount, $cartTotal, 'completed'
                        );
                        
                        $response1 = Util::remote_call( $api_url );
                        $response1 = json_decode( $response1 );
                        
                        if( is_object( $response1 ) ){
                            if( isset($response1->error) && true === $response1->error ){
                                
                                return wp_send_json(Util::notice_html(array(
                                    'success' => false,
                                    'response' => $response->response
                                )));
                                
                            }else{
                                //save payment was successful for this cart
                                cartFunctions::save_transaction_successful_log( $premade_order_id );
                                //remove temp transaction data
                                cartFunctions::temp_remove_trx_info( $trxid, $premade_order_id );
                                //log checkout type
                                cartFunctions::save_temp_log_checkout_type( 2, $premade_order_id );

                                return wp_send_json( Util::notice_html( array(
                                    'success' => true,
                                    'response' => __( 'Thank you! Transaction completed successfully. Your order is processing right now!', 'woo-altcoin-payment-gateway' )
                                )));
                            }
                        }
                    }
                    
                    $response_msg = __( 'Your order is processing. Successful transaction confirmation count on ' ) . $confirmation ."/{$con_count}";
                }
                
                return wp_send_json(Util::notice_html(array(
                    'success' => false,
                    'response' => $response_msg
                )));
            }
            elseif( isset($response->success) && true === $response->success && true === $response->is_valid_for_order ){
                //save payment was successful for this cart
                cartFunctions::save_transaction_successful_log( $premade_order_id );
                //remove temp transaction data
                cartFunctions::temp_remove_trx_info( $trxid, $premade_order_id );
                //log checkout type
                cartFunctions::save_temp_log_checkout_type( 2, $premade_order_id );
                
                return wp_send_json(Util::notice_html(array(
                    'success' => true,
                    'response' => __( 'Thank you! Transaction completed successfully. Your order is processing right now!', 'woo-altcoin-payment-gateway' )
                )));
            }else{
                return wp_send_json(Util::notice_html(array(
                    'success' => false,
//                    'response' => __( 'Transaction on processing. Getting confirmation data..', 'woo-altcoin-payment-gateway' )
                    'response' => implode(' - ', (array)$response)
                )));
            }
        }
        else{
            return wp_send_json(Util::notice_html(array(
                'error' => true,
                'response' => __( 'Something went wrong! Please refresh the page and try again.', 'woo-altcoin-payment-gateway' )
            )));
        }
        
        
    }
    
}

?>