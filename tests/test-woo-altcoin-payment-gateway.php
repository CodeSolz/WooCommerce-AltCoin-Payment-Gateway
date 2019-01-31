<?php
/**
 * Class Test_Woocommerce_Altcoin_Payment
 *
 * @package Woo_Altcoin_Payment_Gateway
 */

use WooGateWayCoreLib\admin\functions\CsAdminQuery;

class WooAltcoinPaymentGatewayTest extends WP_UnitTestCase {
    
    public function setUp()
    {
        parent::setUp();
        $this->WAP = new Woocommerce_Altcoin_Payment_Gateway();
    }

    /**
     * check plugin doesn't return any error
     */
    public function test_PluginLoadedSuccessfully(){
        $this->assertTrue( true );
    }
    
    /**
     * 
     */
    public function test_AddNewCoin(){
        $data = array(
            'cs_add_new' => array(
                'coin_name' => 'Bitcoin Scrypt',
                'coin_address' => 'sdfsdsfsfsfsdfddd',
                'checkout_type' => 1
            )
        );
        
        $CsAdminQuery = new CsAdminQuery();
        $this->assertEquals( $CsAdminQuery->add_new_coin( $data ), '{"status":true,"title":"Success","text":"Thank you! Coin has been added successfully.","redirect_url":"http:\/\/example.org\/wp-admin\/admin.php?page=cs-woo-altcoin-all-coins"}' );
    }
    
}
