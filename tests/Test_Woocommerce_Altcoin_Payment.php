<?php
/**
 * Class Test_Woocommerce_Altcoin_Payment
 *
 * @package Woo_Altcoin_Payment_Gateway
 */

class Test_Woocommerce_Altcoin_Payment extends WP_UnitTestCase {
    
    public function setUp()
    {
        parent::setUp();
        $this->class_instance = new Woocommerce_Altcoin_payment();
    }
    
}
