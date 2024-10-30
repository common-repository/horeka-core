<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Woocommerce;

use HorekaCore\Base\BaseController;

/**
* 
*/
class ParentCategoryRestriction extends BaseController
{

    public function register() 
    {
        // If this option is active we use Horeka Core plugin only for importing orders to our API
        if( $this->activated( 'only_import_orders' ) ) {
            return;
        }
        
        if( !$this->activated( 'same_parent_category' ) ) {
            return;
        }

        add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'cartValidation' ), 1, 3 );
        add_action( 'wp_ajax_nopriv_checking_cart_items', array( $this, 'getVariablesFromSession' ) );
        add_action( 'wp_ajax_checking_cart_items', array( $this, 'getVariablesFromSession' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'parentCategoryRestrictionScripts' ) );
        add_action( 'wp_footer', array( $this, 'parentCategoryRestrictionValidation' ), 99 );
        add_action( 'wp_footer', array( $this, 'parentCategoryRestrictionV2' ), 99 );
    }

    /*
    ** Check if exists products in the Cart with the same parent or empty the cart
    */ 
    public function cartValidation( $passed, $product_id, $quantity ) 
    {
        if( $passed ) {
            $parentId = 0;
            $productCategories = get_the_terms( $product_id, 'product_cat' );
            
            foreach( $productCategories as $category ) {
                $parentId = $category->parent;
            }
            
            if( $parentId > 0 && ! WC()->cart->is_empty() ) {
                foreach ( WC()->cart->get_cart() as $cart_item ) {
                    $productId = $cart_item['product_id'];
                    $currentProductCategories = get_the_terms( $productId, 'product_cat' );
                    foreach( $currentProductCategories as $category ) {
                        if( $parentId != $category->parent ){
                            if (session_status() == PHP_SESSION_NONE) session_start(); 
                            $_SESSION['showMessage'] = 'true';
                            $_SESSION['currentCategoryName'] = get_term($parentId)->name;
                            WC()->cart->empty_cart();
                        }
                    }
                }
            }
        }

        return $passed;
    }

    /*
    ** Wordpress Ajax: Get different cart items count
    */
    public function getVariablesFromSession() 
    {
        if (session_status() == PHP_SESSION_NONE) session_start(); 	
        
        $response = array();
        
        if( isset($_SESSION['showMessage']) && $_SESSION['showMessage'] == 'true' ){
            $response['status'] = true;
        }else{
            $response['status'] = false;
        }
        
        if( isset($_SESSION['currentCategoryName']) ) 
            $response['currentCategoryName'] = $_SESSION['currentCategoryName'];
        
        if( ! empty($response) ){
            echo json_encode( $response );
            
            unset($_SESSION['showMessage']);
            unset($_SESSION['currentCategoryName']);
        }
        
        die(); // To avoid server error 500
    }

    /*
    ** Enqueue needed scripts
    */
    public function parentCategoryRestrictionScripts()
	{
        wp_enqueue_script('sweetalert2', $this->plugin_url . 'assets/js/woocommerce/sweetalert2.all.min.js', array(), false, true);
		wp_enqueue_script('polyfill', $this->plugin_url . 'assets/js/woocommerce/polyfill.min.js', array(), false, true);
	}

    /*
    ** The Jquery script
    */
    public function parentCategoryRestrictionValidation() 
    {   
        $category_banner = __( 'This item belongs to the category ', 'rpd-restaurant-solution' );
        $info_banner = __( 'Previous order has been deleted.', 'rpd-restaurant-solution' );
        ?>
        <script type="text/javascript">
        jQuery( function($){
            // The Ajax function
            $(document.body).on('added_to_cart', function() {
                $.ajax({
                    type: 'POST',
                    url: wc_add_to_cart_params.ajax_url,
                    data: {
                        'action': 'checking_cart_items',
                        'added' : 'yes'
                    },
                    success: function (response) {
                        var ajaxResponse = JSON.parse(response);
                        var categoryName = ( ajaxResponse.currentCategoryName ? ajaxResponse.currentCategoryName : "" );
                        if( ajaxResponse.status === true ){
                            swal.fire(
                                <?php echo '"' . $category_banner . '"'; ?> + categoryName + '.',
                                <?php echo '"' . $info_banner . '"'; ?>,
                                'error'
                            );
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }

    public function parentCategoryRestrictionV2()
    {
        if( !$this->isActiveCheckoutV2() ) {
            return;
        }
        
        if( !isset( $_SESSION ) || !isset( $_SESSION['showMessage'] ) || !isset( $_SESSION['currentCategoryName'] ) ) {
            return;
        } 
        
        $category_banner = __( 'This item belongs to the category ', 'rpd-restaurant-solution' );
        $info_banner = __( 'Previous order has been deleted.', 'rpd-restaurant-solution' );
        ?>

        <script type="text/javascript">
            jQuery(function($){
                $.ajax({
                    type: 'POST',
                    url: '/wp-admin/admin-ajax.php',
                    data: {
                        'action': 'checking_cart_items',
                        'added' : 'yes'
                    },
                    success: function (response) {
                        var ajaxResponse = JSON.parse(response);
                        var categoryName = ( ajaxResponse.currentCategoryName ? ajaxResponse.currentCategoryName : "" );
                        if( ajaxResponse.status === true ){
                            swal.fire(
                                <?php echo '"' . $category_banner . '"'; ?> + categoryName + '.',
                                <?php echo '"' . $info_banner . '"'; ?>,
                                'error'
                            );
                        }
                    }
                });
            });
        </script>
    <?php }

}