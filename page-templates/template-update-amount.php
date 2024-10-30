<?php   
/**
* Template Name: Change Minimum Amount per Order
*/
?>
<?php
  global $woocommerce;
  $plugin_options = get_option( 'rpd_restaurant_solution' );
?>
<html>
  <head>
    <?php wp_head(); ?>
  </head>
  <body>
    <?php $minimum = ( filter_var($_POST['sum'], FILTER_SANITIZE_STRING) != "" ? filter_var($_POST['sum'], FILTER_SANITIZE_STRING) : (int)$plugin_options['minim_amount_per_order'] ); ?>

      <?php ob_start(); ?>

      <?php
        if( $minimum > 0 ) {

          if ( WC()->cart->total < $minimum ) { 

            if( is_cart() ) {
              
              wc_print_notice( 
                sprintf( esc_html__('The minimum amount for an order is %s, the amount of products you added to the order is %s.', 'rpd-restaurant-solution'), 
                  wc_price( $minimum ), 
                  wc_price( WC()->cart->total )
                ), 'error' 
              );
              
            } else {
      
              wc_add_notice( 
                sprintf( esc_html__('The minimum amount for an order is %s, the amount of products you added to the order is %s.', 'rpd-restaurant-solution'), 
                  wc_price( $minimum ), 
                  wc_price( WC()->cart->total )
                ), 'error' 
              );
              
            }
          }
        }
      ?>

      <?php ob_get_clean(); ?>
  <?php wp_footer(); ?>
  </body>
</html>