<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Base;

use HorekaCore\Base\BaseController;

/**
* 
*/
class Functions extends BaseController
{

    public function register() 
    {

        /**
         * Actions
         */
        add_action( 'plugins_loaded', array( $this, 'checkWoocommerceClassExists' ), 9 );
        add_action( 'init', array( $this, 'i18n' ) );
        add_action( 'wp', array( $this, 'removeGalleryZoom' ) );
        add_action( 'wp_head', array( $this, 'updateMiniCart' ) );
        add_action( 'horeka_core_before_head_closing_tag', array( $this, 'insertMainColor' ) );

        /**
         * Filters
         */
        add_filter( 'woocommerce_account_menu_items', array( $this, 'overwriteUserDashboard' ) );
        add_filter( 'site_transient_update_plugins', array( $this, 'removePluginsUpdatesNotification' ) );
    }

    public function adminError() {
        echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Horeka Core requires WooCommerce to be installed and active. You can download %s here.', 'rpd-restaurant-solution' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
    }

    public function checkWoocommerceClassExists()
    {
        if ( ! class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', array( $this, 'adminError' ) );
        }
    }

    public function i18n() 
    {   
        load_plugin_textdomain('rpd-restaurant-solution', false, $this->plugin_basename . "/languages/");
    }

    public function removeGalleryZoom()
    {
        remove_theme_support( 'wc-product-gallery-zoom' );
    }

    public function updateMiniCart() 
    { 
        $home_url = home_url();
        if( substr( $home_url, -1 ) == '/' ) {
            $home_url = substr($home_url, 0, -1);
        }
        ?>

        <script>

            function ajaxUpdateQuantity(key, qty, el){
				jQuery(el).parents('.mini_cart_item').addClass('active');
                var url = '<?php echo $home_url; ?>/updatecart/';
                var data = "cart_item_key="+key+"&cart_item_qty="+qty;
            
                jQuery.post( url, data ).done(function() {
                    updateCartFragment();
                });
            }
            
            function updateCartFragment() {
                $fragment_refresh = {
                    url: woocommerce_params.ajax_url,
                    type: 'POST',
                    data: { action: 'woocommerce_get_refreshed_fragments' },
                    success: function( data ) {
                        if ( data && data.fragments ) {
                            if( data.fragments["div.cart-contents"] != undefined && data.fragments["div.cart-contents"] != null ) {
                                var productCounter = data.fragments["div.cart-contents"].match(/(\d+)/)[0];

                                if( productCounter != undefined && productCounter != null ) {
                                    if( parseInt( productCounter ) < 1 ) {
                                        jQuery('.mini-cart-header').toggleClass('active');
                                        jQuery('body').toggleClass('mini-cart-active');

                                        window.location.href = window.location.href;
                                    }
                                }
                            }

                            jQuery.each( data.fragments, function( key, value ) {
                                jQuery(key).replaceWith(value);
                            });      
                                    
                            jQuery('body').trigger( 'wc_fragments_refreshed' );
                        }
                    }
                };
            
                //Always perform fragment refresh
                jQuery.ajax( $fragment_refresh );  
            }
        </script>
    
    <?php }

    public function overwriteUserDashboard() 
    {
        $dashboard = array(
            'dashboard'          => esc_html__( 'Dashboard', 'rpd-restaurant-solution' ),
            'orders'             => esc_html__( 'Orders', 'rpd-restaurant-solution' ),
            'edit-account'       => esc_html__( 'Account', 'rpd-restaurant-solution' ),
            'customer-logout'    => esc_html__( 'Logout', 'rpd-restaurant-solution' ),
        );
    
        return $dashboard;
    }

    public function removePluginsUpdatesNotification( $value ) 
    {
        if( isset($value->response['restaurant-reservations/restaurant-reservations.php'])  ) {
            unset( $value->response['restaurant-reservations/restaurant-reservations.php'] );
        }
        
        return $value;
    }

    public function insertMainColor()
    {
        if( is_page_template('template-fake-checkout.php')  || ( is_order_received_page() && WC()->session->get('fake_checkout') ) || ( isset($_GET['method']) && $_GET['method'] === 'credit_card' ) ) {
			// Do nothing
		} else {
			require_once( $this->plugin_path . 'page-templates/main-colour-style.php' );
		}
    }

}