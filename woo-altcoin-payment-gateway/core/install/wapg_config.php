<?php
/**
 * Config
 * 
 * @package DB
 * @since 1.0.8
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
   exit;
}

global $wapg_tables, $wapg_current_db_version, $wpdb;

//assign db version globally in variable
$wapg_current_db_version = CS_WAPG_DB_VERSION;

/**
 * load custom table names
 */
if( ! isset( $wapg_tables ) ){
    $wapg_tables = array(
        'coins' => $wpdb->prefix . 'wapg_coins',
        'addresses' => $wpdb->prefix . 'wapg_coin_addresses',
        'offers' => $wpdb->prefix . 'wapg_coin_offers',
        'coin_trxids' => $wpdb->prefix . 'wapg_coin_transactions'
    );
}