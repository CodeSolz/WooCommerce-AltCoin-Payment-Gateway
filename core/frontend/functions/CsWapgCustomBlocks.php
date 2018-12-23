<?php namespace WooGateWayCoreLib\frontend\functions;
/**
 * Frontend Custom Blocks
 * 
 * @package WAPG Admin 
 * @since 1.0.0
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
    exit;
}

use WooGateWayCoreLib\lib\Util;
use WooGateWayCoreLib\admin\functions\CsAdminQuery;

class CsWapgCustomBlocks {
    
    public static function special_discount_offer_box(){
        $offers = CsAdminQuery::get_offers_info();
        if( $offers ) {
        ?>
            <div class="special-discount-box">
                <b>You will get special discount, if you pay with following AltCoins</b>
                <ol>
                    <?php  foreach( $offers as $offer) {?>
                        <li><?php echo $offer->name; ?> - <?php echo Util::get_discount_type( $offer->offer_amount, $offer->offer_type ); ?></li>
                    <?php } ?>
                </ol>
            </div>    
            <style type="text/css">
                .special-discount-box{ background: aliceblue;border: 1px dashed black;padding: 10px;margin: 15px 0px; }
                .special-discount-box ol{ margin: 10px 0px 0px 10px; }
                .special-discount-box ol li{ font-style: italic; line-height: 16px; }
            </style>
        <?php
        }
    }
}
