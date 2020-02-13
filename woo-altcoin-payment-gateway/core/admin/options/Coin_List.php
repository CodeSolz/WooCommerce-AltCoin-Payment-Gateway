<?php namespace WooGateWayCoreLib\admin\options;

/**
 * Class: Coin LIst
 * 
 * @package Admin
 * @since 1.0.9
 * @author CodeSolz <customer-support@codesolz.net>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    die();
}

use WooGateWayCoreLib\lib\Util;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class Coin_List extends \WP_List_Table {
    var $item_per_page = 10;
    var $total_post;
    
    public function __construct() {
        parent::__construct( array(
                'singular' => __( 'coin', 'woo-altcoin-payment-gateway' ),
                'plural'   => __( 'coins', 'woo-altcoin-payment-gateway' ),
                'ajax'     => false
        ) );
    }
    
    /**
     * 
     * @return typeGenerate column
     */
    public function get_columns() {
        return array(
            'cb' => '<input type="checkbox" />',
            'name' => __( 'Coin Name', 'woo-altcoin-payment-gateway' ),
            'address' => __( 'Coin address', 'woo-altcoin-payment-gateway' ),
            'checkout_type'=> __( 'Order / Transaction Confirmation', 'woo-altcoin-payment-gateway' ),
            'status'=> __( 'Status', 'woo-altcoin-payment-gateway' ),
            'offer_info'=> __( 'Special Discount', 'woo-altcoin-payment-gateway' ),
        );
    }
    
    /**
     * Column default info
     */
    function column_default( $item, $column_name ) {
      switch( $column_name ) { 
        case 'name':
        case 'address':
        case 'checkout_type':
        case 'status':
          return $item->{$column_name};
        default:
          return '---' ; //Show the whole array for troubleshooting purposes
      }
    }
    
    /**
     * Column cb
     */
    public function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="id[]" value="%1$s" />', $item->cid );
    }
    
    public function column_name( $item ) {
        echo $item->name . '(' . $item->symbol . ')';
        $edit_link = admin_url( "admin.php?page=cs-woo-altcoin-add-new-coin&action=update&coin_id={$item->cid}"); 
        echo '<div class="row-actions"><span class="edit">';
        echo '<a href="'.$edit_link.'">Edit</a>';
        echo '</span></div>';
    }
    
    public function column_checkout_type( $item ){
        return Util::get_checkout_type( $item->checkout_type );
    }
    
    public function column_status( $item ){
        return Util::get_coin_status( $item->status );
    }
    
    public function column_offer_info( $item ){
        if( $item->offer_status > 0 ){
        ?>
            
            <table class="wp-list-table widefat fixed striped offer hidden">
                <tr>
                    <td width="20%"><?php _e( 'Status', 'woo-altcoin-payment-gateway' )?></td>
                    <td width="1%">:</td>
                    <td>
                        <?php echo Util::get_offer_status( $item->offer_status ); ?>
                    </td>
                </tr>
                <tr>
                    <td><?php _e( 'Discount', 'woo-altcoin-payment-gateway' )?></td>
                    <td>:</td>
                    <td>
                    <?php echo $item->offer_amount .' '. Util::get_offer_type( $item->offer_type ); ?>
                    </td>
                </tr>
                <tr>
                    <td><?php _e( 'Offer Start', 'woo-altcoin-payment-gateway' )?></td>
                    <td>:</td>
                    <td>
                    <?php echo Util::get_formated_datetime( $item->offer_start, 'l d M, Y h:i A' ); ?>
                    </td>
                </tr>
                <tr>
                    <td><?php _e( 'Offer End', 'woo-altcoin-payment-gateway' )?></td>
                    <td>:</td>
                    <td>
                    <?php echo Util::get_formated_datetime( $item->offer_end, 'l d M, Y h:i A' ); ?>
                    </td>
                </tr>
            </table>
            <div class="offer-more-link success-text"><a class="offer-more">Show Offer Information..</a></div>
        <?php
        }else{
            echo Util::get_offer_status( $item->offer_status );
        }
    }
            
    public function no_items() {
        _e( 'Sorry! No Coin Found!', 'woo-altcoin-payment-gateway' );
    }
    
    function get_views(){
       $all_link = admin_url( 'admin.php?page=cs-woo-altcoin-all-coins'); 
       $views['all'] = "<a href='{$all_link}' >All <span class='count'>({$this->total_post})</span></a>";
       return $views;
    }
    
    public function get_bulk_actions() {
        $actions = array(
            'delete' => __( 'Delete', 'woo-altcoin-payment-gateway' ),
        );
        return $actions;
    }
    
    /**
     * Get the data
     * 
     * @global type $wpdb
     * @return type
     */
    private function poulate_the_data(){
        global $wpdb, $wapg_tables;
        $search = '';
        if( isset($_GET['s']) && !empty( $skey = $_GET['s']) ){
            $search = " where c.name like '%{$skey}%'";
        }
        
        if( isset($_GET['order']) ){
            $order = $_GET['order'];
        }else{
            $order = 'c.id DESC';
        }
        
        $current_page = $this->get_pagenum();
        if ( 1 < $current_page ) {
                $offset =  $this->item_per_page * ( $current_page - 1 );
        } else {
                $offset = 0;
        }
        
        $data = array();
        $result = $wpdb->get_results( "SELECT *,c.id as cid, a.id as aid, o.id as oid, GROUP_CONCAT(address SEPARATOR ', ') as address from  {$wapg_tables['coins']} as c "
                . " left join {$wapg_tables['addresses']} as a on c.id = a.coin_id "
                . " left join {$wapg_tables['offers']} as o on c.id = o.coin_id "
                . "$search "
                . " group by c.name order by {$order} limit $this->item_per_page offset {$offset}");
                
        if( $result ){
            foreach ($result as $item ){
                $data[] = $item;
            }
        }
        $total = $wpdb->get_var( "select count(id) as total from {$wapg_tables['coins']} ");        
        $data['count'] = $this->total_post = $total;             
		
        return $data;
    }
    
    function process_bulk_action(){
        global $wpdb, $wapg_tables;
          // security check!
        if ( isset( $_GET['_wpnonce'] ) && ! empty( $_GET['_wpnonce'] ) ) {

            $action = 'bulk-' . $this->_args['plural'];

            if ( ! wp_verify_nonce( $_GET['_wpnonce'] , $action ) )
                wp_die( 'Nope! Security check failed!' );

            $action = $this->current_action();
            
            switch ($action):
                case 'delete':
                    $log_ids= $_GET['id'];
                    if($log_ids){
                        foreach($log_ids as $log){
                            $wpdb->delete( "{$wapg_tables['coins']}" ,array( 'id'=>$log ) );
                            $wpdb->delete( "{$wapg_tables['addresses']}" ,array( 'coin_id'=>$log ) );
                            $wpdb->delete( "{$wapg_tables['offers']}" ,array( 'coin_id'=>$log ) );
                        }
                    }
                 $this->success_admin_notice();
                break;
            endswitch;
        }
        return;
    }
    
    public function success_admin_notice() {
        ?>
        <div class="updated">
            <p><?php _e( 'Coin has been deleted successfully!', 'woo-altcoin-payment-gateway' ); ?></p>
        </div>
        <?php
    }
    
    public function prepare_items() {
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();

        
        // Column headers
        $this->_column_headers = array( $columns, $hidden, $sortable ='' );
        $this->process_bulk_action();
        
        $data = $this->poulate_the_data();
        $count = $data['count']; unset($data['count']);
        $this->items = $data;
        
         // Set the pagination
        $this->set_pagination_args( array(
                'total_items' => $count,
                'per_page'    => $this->item_per_page,
                'total_pages' => ceil( $count / $this->item_per_page )
        ) );
    }
    
}