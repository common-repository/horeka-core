<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Woocommerce;

use HorekaCore\Base\BaseController;
use HorekaCore\Api\PushNotification;

/**
* 
*/
class ProductActions extends BaseController
{

    public function register() 
    {
        // If this option is active we use Horeka Core plugin only for importing orders to our API
        if( $this->activated( 'only_import_orders' ) ) {
            return;
        }

        if( !$this->activated( 'crm_products_sync' ) ) {
            return;
        }

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueueScripts' ), 10 );
        add_action( 'save_post', array( $this, 'addEditProduct' ), 10, 2 );
        add_action( 'wp_trash_post', array( $this, 'deleteProduct' ), 10, 1 );
        add_action( 'admin_footer', array( $this, 'showModal' ) );

        add_filter( 'bulk_actions-edit-product', array( $this, 'disbleBulkEdit' ), 10, 1 );
        add_filter( 'post_row_actions', array( $this, 'removeQuickEdite' ), 99, 2 );
    }

    public function enqueueScripts()
    {
        wp_enqueue_style( 'rpd-product-actions', $this->plugin_url . 'assets/css/woocommerce/rpd-product-actions.min.css' );
        wp_enqueue_script('rpd-product-actions', $this->plugin_url . 'assets/js/woocommerce/rpd-product-actions.min.js' );
    }

    public function addEditProduct( $postid, $post )
    {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }

        if ( $post->post_type != 'product' || $post->post_status == 'auto-draft' ) {
            return;
        }

        if ( !$current_product = wc_get_product( $postid ) ) {
            return;
        }

        $product = array();
        $product_tags = array();
        $product_categories = (object) array();
        $product_addons = array();
        $product_integrators_prices = array();
        $product_id = $current_product->get_id();

        $route = '/product/integration/updatecrmproduct';
                
        $product['OperationType'] = 'ADD-UPDATE';
        $product['Id'] = $product_id;
        $product['Name'] = $current_product->get_name();
        $product['Description'] = $current_product->get_description();
        $product['ShortDescription'] = $current_product->get_short_description();
        $product['Photo'] = ( $current_product->get_image_id() != '' ? wp_get_attachment_image_src($current_product->get_image_id(), 'full')[0] : '' );
        $product['Price'] = ( $current_product->get_price() ? $current_product->get_price() : 0 );
        $product['RegularPrice'] = ( $current_product->get_regular_price() ? $current_product->get_regular_price() : 0 );
        $product['Status'] = $current_product->get_status();
        $product['StockStatus'] = $current_product->get_stock_status();
        $product['DateModified'] = $current_product->get_date_modified()->date( 'Y-m-d H:m:s' ); // 2021-12-22 13:07:58.000000
        $product['ProductCategoryId'] = $current_product->get_category_ids()[0];
        $product['RelatedIds'] = wc_get_related_products( $product_id );

        if( $current_product->get_meta( 'tazz_price' ) ) {
            $product_integrators_prices['tazz_price'] = $current_product->get_meta( 'tazz_price' );
        }

        if( $current_product->get_meta( 'glovo_price' ) ) {
            $product_integrators_prices['glovo_price'] = $current_product->get_meta( 'glovo_price' );
        }
        
        if( $current_product->get_meta( 'takeaway_price' ) ) {
            $product_integrators_prices['takeaway_price'] = $current_product->get_meta( 'takeaway_price' );
        }

        if( $current_product->get_meta( 'foodpanda_price' ) ) {
            $product_integrators_prices['foodpanda_price'] = $current_product->get_meta( 'foodpanda_price' );
        }

        if( $current_product->get_meta( 'taptasty_price' ) ) {
            $product_integrators_prices['taptasty_price'] = $current_product->get_meta( 'taptasty_price' );
        }

        if( $current_product->get_meta( 'pos_id' ) ) {
            $product_integrators_prices['pos_id'] = $current_product->get_meta( 'pos_id' );
        }

        if( $current_product->get_meta( 'add_default_to_cart' ) ) {
            $product_integrators_prices['add_default_to_cart'] = $current_product->get_meta( 'add_default_to_cart' );
        }

        $product['IntegratorsPrices'] = $product_integrators_prices;

        if( !empty($current_product->get_tag_ids()) ) {
            $counter = 0;
            foreach( $current_product->get_tag_ids() as $tag_id ) {
                if( $tag = get_term_by('id', $tag_id, 'product_tag') ) {
                    $product_tags[$counter]['id'] = $tag->term_id;
                    $product_tags[$counter]['name'] = $tag->name;
                    $product_tags[$counter]['slug'] = $tag->slug;
    
                    $counter++;
                }
            }
        }

        $product['Tags'] = $product_tags;

        if( !empty( $current_product->get_category_ids() ) ) {
            foreach( $current_product->get_category_ids() as $category_id ) {
                if( $category = get_term_by('id', $category_id, 'product_cat') ) {
                    $product_categories->Id = $category->term_id;
                    $product_categories->Name = $category->name;
                    $product_categories->ParentId = $category->parent;
                    $product_categories->OrderNo = 0;
                    $product_categories->Description = $category->description;
                }
            }
        }

        $product['Category'] = $product_categories;

        if( !empty( $current_product->get_meta( '_product_addons' ) ) ) {
            $product_addons = $current_product->get_meta( '_product_addons' );
        }

        $product['Addon'] = $product_addons;

        $result = PushNotification::makeNotification( $route, $product );

        if (!isset($_SESSION)) { 
            session_start(); 
        }

        if( $result->status === 200 ) {
            $_SESSION['product_updated'] = 'yes';
        } else {
            $_SESSION['product_updated'] = 'no';
        }

        $_SESSION['message'] = $result->message;
    }

    public function deleteProduct( $postid )
    {       
        if ( !$current_product = wc_get_product( $postid ) ) {
            return;
        }

        $route = '/product/integration/updatecrmproduct';

        $product = array();
        $product_id = $current_product->get_id();
        
        $product['OperationType'] = 'DELETE';
        $product['Id'] = $product_id;

        $result = PushNotification::makeNotification( $route, $product );

        if (!isset($_SESSION)) { 
            session_start(); 
        }

        $result->status = 200;

        if( $result->status === 200 ) {
            $_SESSION['product_deleted'] = 'yes';
        } else {
            $_SESSION['product_deleted'] = 'no';
        }

        $_SESSION['message'] = $result->message;
    }

    public function showModal()
    {
        if ( !isset($_SESSION) ) { 
            session_start(); 
        }

        if( isset($_SESSION['product_updated']) || isset($_SESSION['product_deleted']) ) {
            $message = $class = '';

            if (isset($_SESSION['product_updated'])) {
                if( $_SESSION['product_updated'] == 'yes' ) {
                    if( isset( $_SESSION['message'] ) ) {
                        $message = $_SESSION['message'];
                    } else {
                        $message = __( 'Product updated.', 'rpd-restaurant-solution' );
                    }
                    ?>
                    <script>
                        jQuery( document ).ready( function( $ ) {
                            $(<?php echo $message; ?>).appendTo('#updated-deleted-product-modal');
                        } );
                    </script>
                <?php } else {
                    if( isset( $_SESSION['message'] ) ) {
                        $message = $_SESSION['message'];
                    } else {
                        $message = __( 'Product updated.', 'rpd-restaurant-solution' );
                    }
                    $class = 'red';
                    ?>
                    <script>
                        jQuery( document ).ready( function( $ ) {
                            $(<?php echo $message; ?>).appendTo('#updated-deleted-product-modal');
                        } );
                    </script>
                <?php }

                unset($_SESSION['product_updated']);
            }

            if( isset($_SESSION['product_deleted']) ) {
                if( $_SESSION['product_deleted'] == 'yes' ) {
                    if( isset( $_SESSION['message'] ) ) {
                        $message = $_SESSION['message'];
                    } else {
                        $message = __( 'Product deleted.', 'rpd-restaurant-solution' );
                    }
                    ?>
                    <script>
                        jQuery( document ).ready( function( $ ) {
                            $(<?php echo $message; ?>).appendTo('#updated-deleted-product-modal');
                        } );
                    </script>
                <?php } else {
                    if( isset( $_SESSION['message'] ) ) {
                        $message = $_SESSION['message'];
                    } else {
                        $message = __( 'Something went wrong. Please, deactivate the product manually from CRM.', 'rpd-restaurant-solution' );
                    }
                    $class = 'red';
                    ?>
                    <script>
                        jQuery( document ).ready( function( $ ) {
                            $(<?php echo $message; ?>).appendTo('#updated-deleted-product-modal');
                        } );
                    </script>
                <?php }

                unset($_SESSION['product_deleted']);
            }

            if( $message != '' ) {
                echo '<div id="updated-deleted-product-modal" class="' . $class . '">' . $message . '</div>';
            }

            unset($_SESSION['message']);
        }

    }

    public function disbleBulkEdit( $actions )
    {
        unset( $actions[ 'edit' ] );
        unset( $actions[ 'trash' ] );
        
        return $actions;
    }

    public function removeQuickEdite( $actions = array(), $post = null )
    {   
        if ( ! is_post_type_archive( 'product' ) ) {
            return $actions;
        }
    
        if ( isset( $actions['inline hide-if-no-js'] ) ) {
            unset( $actions['inline hide-if-no-js'] );
        }
    
        return $actions;
    }

}