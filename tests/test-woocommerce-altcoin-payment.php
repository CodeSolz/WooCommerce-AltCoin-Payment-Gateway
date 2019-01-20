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
        $this->class_instance = new Woocommerce_Altcoin_payment();
    }
    
    public function test_init_activation(){
        $this->assertEquals( true );
    }
    
}
