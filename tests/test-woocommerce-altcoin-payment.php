<?php
/**
 * Class Test_Woocommerce_Altcoin_Payment
 *
 * @package Woo_Altcoin_Payment_Gateway
 */

class WoocommerceAltcoinPaymentTest extends WP_UnitTestCase {
    
    public function setUp()
    {
        parent::setUp();
        $this->WAP = new Woocommerce_Altcoin_payment();
    }

    /**
     * check plugin doesn't return any error
     */
    public function testPluginLoadedSuccessfully(){
        $this->assertTrue( true );
    }
    
}
