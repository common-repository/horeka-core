<?php   
/**
* Template Name: Request template for Set Quantity
* This page updates mini cart quantity for a product based on the post value
*/
?>
<html>
    <head>
        <?php wp_head(); ?>
    </head>
    <body>
        <?php
            $cartKeySanitized = filter_var($_POST['cart_item_key'], FILTER_SANITIZE_STRING);
            $cartQtySanitized = filter_var($_POST['cart_item_qty'], FILTER_SANITIZE_STRING);  
        ?>
        <?php 
            global $woocommerce;
            ob_start();
            if( $cartQtySanitized >= 0 ) $woocommerce->cart->set_quantity($cartKeySanitized,$cartQtySanitized);
            ob_get_clean();
        ?>
    <?php wp_footer(); ?>
    </body>
</html>