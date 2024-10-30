<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Woocommerce;

use \WC_CART;
use \DateTime;
use \WC_Coupon;
use \WC_Discounts;
use \DateTimeZone;
use HorekaCore\Base\ErrorLog;
use HorekaCore\Base\BaseController;
use HorekaCore\Api\PushNotification;

/**
* 
*/
class Functions extends BaseController
{
    
    public function register() 
    {
        // If this option is active we use Horeka Core plugin only for importing orders to our API
        if( $this->activated( 'only_import_orders' ) ) {
            return;
        }

        /**
         * Shortcodes
         */
        add_shortcode( 'discount_banner', array( $this, 'printDiscountBanner' ) );
        
        /**
         * Actions
         */
        add_action( 'woocommerce_checkout_process', array( $this, 'setDefaultShippingCountry' ), 1 );
        add_action( 'woocommerce_checkout_process', array( $this, 'checkOrderAmountOnCheckout' ) );
        add_action( 'woocommerce_before_cart', array( $this, 'checkOrderAmount' ) );
        add_action( 'woocommerce_cart_calculate_fees', array( $this, 'applyOnlineDiscount' ), 1, 1 );
        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'addFakeEmailAddress' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'dequeueStrengthMeter' ), 99 );
        add_action( 'woocommerce_receipt_mobilpaycard', array( $this, 'updateDefaultEmailAddress' ) );
        add_action( 'wp_ajax_woo_get_ajax_data', array( $this, 'addBillingUpsToSession' ) );
        add_action( 'wp_ajax_nopriv_woo_get_ajax_data', array( $this, 'addBillingUpsToSession' ) );
        add_action( 'woocommerce_checkout_update_order_review', array( $this, 'refreshShippingMethods' ) );
        add_action( 'wp_footer', array( $this, 'triggerUpdateCheckout' ), 99 );
        add_action( 'woocommerce_after_checkout_validation', array( $this, 'calculateShippingCostPerDistance' ) );
        add_action( 'woocommerce_product_query', array( $this, 'updatePostsPerPageArgument' ) );
        add_action( 'wp_footer', array( $this, 'displayDeliveryTime' ), 99 );
        add_action( 'woocommerce_checkout_process', array( $this, 'validateApiHours' ) );
        add_action( 'wp_footer', array( $this, 'displayMenuDot' ) );
        add_action( 'woocommerce_checkout_process', array( $this, 'validatePhoneNumber' ), 10, 1 );
        add_action( 'woocommerce_email_after_order_table', array( $this, 'insertTrakingLink' ) );
        add_action( 'woocommerce_cart_calculate_fees', array( $this, 'applyDeliveryMethodDiscount' ), 99, 1 ); 
        add_action( 'woocommerce_cart_calculate_fees', array( $this, 'applyDeliveryPointDiscount' ), 99, 1 );
        add_action( 'wp_footer', array( $this, 'addDelayForAddressValidation' ) );
        add_action( 'wp_footer', array( $this, 'insertAjaxCouponScript' ) );
        add_action( 'wp_ajax_apply_coupon_via_ajax', array( $this, 'applyCouponViaAjax' ) ); 
        add_action( 'wp_ajax_nopriv_apply_coupon_via_ajax', array( $this, 'applyCouponViaAjax' ) );
        add_action( 'woocommerce_email_after_order_table', array( $this, 'insertDeliveryPoint' ), 10, 1 );
        add_action( 'woocommerce_email_after_order_table', array( $this, 'insertDeliveryMethod' ), 10, 1 );
        add_action( 'wp_footer', array( $this, 'insertErrorBoxHtml' ), 10, 1 );
        add_action( 'woocommerce_order_details_after_order_table', array( $this, 'insertDeliveryDetails'), 10, 1 );
        add_action( 'woocommerce_checkout_process', array( $this, 'changeDeliveryTime' ) );
        add_action( 'wp_footer', array( $this, 'hidePickupPointField' ) );
        add_action( 'woocommerce_checkout_process', array( $this, 'removeDeliveryPoint' ), 1 );
        add_action( 'woocommerce_checkout_process', array( $this, 'changeAddressFieldStructure' ) );
        add_action( 'woocommerce_email_after_order_table', array( $this, 'insertDeliveryHourDetails' ), 10, 1 );
        add_action( 'woocommerce_after_delivery_method', array( $this, 'insertDeliveryHourDetailsThankyouPage' ), 10, 1 );
        add_action( 'wp_footer', array( $this, 'insertMiniCartValidationScript' ) );
        add_action( 'woocommerce_after_checkout_validation', array( $this, 'validateCategoriesHours' ), 10, 2 );
        add_action( 'wp_footer', array( $this, 'onlineOrderPreset' ), 99 );
        add_action( 'wp_footer', array( $this, 'removeOnlineOrderCookies' ), 99 );
        add_action( 'before_add_to_cart_template', array( $this, 'forceAppUpdate' ) );
        add_action( 'before_add_to_cart_template', array( $this, 'clearPhoneOrderCookies' ) );
        add_action( 'horeka_review_order_before_shipping', array( $this, 'insertSubtotalProductsPrice' ) );
        add_action( 'horeka_review_order_before_shipping_thank_you_page', array( $this, 'insertSubtotalProductsPriceThankyouPage' ), 10, 1 );
        add_action( 'horeka_review_order_before_shipping_email_order_details', array( $this, 'insertSubtotalProductsPriceEmailOrderDetails' ), 10, 1 );
		add_action( 'woocommerce_checkout_before_order_review', array( $this, 'applyNewCustomerCoupon' ), 10, 1 );

        /**
         * Filters
         */
        add_filter( 'woocommerce_locate_template', array( $this, 'overrideWoocommerceTemplatesPath' ), 1, 3 );
        add_filter( 'woocommerce_checkout_fields', array( $this, 'unsetCheckoutFields' ) );
        add_filter( 'woocommerce_billing_fields', array( $this, 'addAutoFocus' ) );
        add_filter( 'woocommerce_checkout_fields', array( $this, 'populateDefaultCity' ) );
        add_filter( 'woocommerce_form_field', array( $this, 'removeOptionalLabel' ) );
        add_filter( 'woocommerce_cart_shipping_method_full_label', array( $this, 'rewriteShippingLabel' ), 10, 2 );
        add_filter( 'woocommerce_package_rates', array( $this, 'addCustomShippingCost' ), 90, 2 );
        add_filter( 'pre_get_posts', array( $this, 'addCategoryNameToSearchQuery' ) );
        add_filter( 'woocommerce_get_catalog_ordering_args', array( $this, 'overwriteOrderByArgs' ) );
        add_filter( 'woocommerce_package_rates', array( $this, 'hideShippingWhenFreeIsAvailable' ), 100 );
        add_filter( 'woocommerce_billing_fields', array( $this, 'makeEmailAddressOptional' ), 10, 2 );
        add_filter( "woocommerce_rest_product_object_query", array( $this, 'filterWoocommerceRestTagExclude' ), 10, 2 );
        add_filter( "woocommerce_coupon_is_valid", array( $this, 'couponsForAuthenticatedUsersOnly' ), 100, 2 );
        add_filter( 'woocommerce_package_rates', array( $this, 'customShippingCosts'), 99, 2 );
    }

    private function createTimeRange( $start, $end, $interval = '30 mins', $format = '24' )
    {
        $startTime = strtotime($start); 
        $endTime   = strtotime($end);

        if( $startTime >= $endTime ) {
            return;
        }

        $returnTimeFormat = ($format == '12')?'g:i A':'G:i';
    
        $current   = time(); 
        $addTime   = strtotime('+'.$interval, $current); 
        $diff      = $addTime - $current;
    
        $times = array(); 
        while ($startTime < $endTime) { 
            $times[] = date($returnTimeFormat, $startTime); 
            $startTime += $diff; 
        }
        $times[] = date($returnTimeFormat, $startTime); 
        
        return $times; 
    }

    private function getDeliveryTime()
    {
        $route = '/venue/integration/getvenuedetails';
        $response = PushNotification::makeNotification( $route );

        if( $response->status === 200 ) {
            return $response->data->deliveryTime;
        }

        return false;
    }

    private function apply_coupon( $coupon_code ) {
        // Coupons are globally disabled.
        if ( ! wc_coupons_enabled() ) {
            return false;
        }

        global $woocommerce;

        $response = array(
            'status' => false,
            'message' => sprintf( __('Coupon %s has been already applied.', 'rpd-restaurant-solution' ), $coupon_code )
        );
    
        // Sanitize coupon code.
        $coupon_code = wc_format_coupon_code( $coupon_code );
    
        // Get the coupon.
        $the_coupon = new WC_Coupon( $coupon_code );
    
        // Prevent adding coupons by post ID.
        if ( $the_coupon->get_code() !== $coupon_code ) {
            $the_coupon->set_code( $coupon_code );

            return $response;
        }
    
        // Check it can be used with cart.
        if ( !$the_coupon->is_valid() ) {     
            $response['message'] = sprintf( __('Coupon %s is invalid for your cart items.', 'rpd-restaurant-solution' ), $coupon_code );

            return $response;
        }

        // Check if applied.
        if ( WC()->cart->has_discount( $coupon_code ) ) {
            $response['message'] = sprintf( __('Coupon %s has been already applied.', 'rpd-restaurant-solution' ), $coupon_code );
            
            return $response;
        }
    
        // If its individual use then remove other coupons.
        if ( $the_coupon->get_individual_use() ) {
            $coupons_to_keep = apply_filters( 'woocommerce_apply_individual_use_coupon', array(), $the_coupon, $woocommerce->cart->get_applied_coupons() );
    
            foreach ( $woocommerce->cart->get_applied_coupons() as $applied_coupon ) {
                $keep_key = array_search( $applied_coupon, $coupons_to_keep, true );
                if ( false === $keep_key ) {
                    WC()->cart->remove_coupon( $applied_coupon );
                } else {
                    unset( $coupons_to_keep[ $keep_key ] );
                }
            }
    
            if ( ! empty( $coupons_to_keep ) ) {
                $woocommerce->cart->set_applied_coupons( $coupons_to_keep );
            }
        }
    
        // Check to see if an individual use coupon is set.
        if ( $woocommerce->cart->get_applied_coupons() ) {
            foreach ( $woocommerce->cart->get_applied_coupons() as $code ) {
                $coupon = new WC_Coupon( $code );
    
                if ( $coupon->get_individual_use() && false === apply_filters( 'woocommerce_apply_with_individual_use_coupon', false, $the_coupon, $coupon, $this->applied_coupons ) ) {
    
                    // Reject new coupon.
                    $response['message'] = sprintf( __('Coupon %s has been already applied.', 'rpd-restaurant-solution' ), $coupon_code );

                    return $response;
                }
            }
        }

        $woocommerce->cart->set_applied_coupons( array($coupon_code) );

        do_action( 'woocommerce_applied_coupon', $coupon_code );
    
        // Choose free shipping.
        if ( $the_coupon->get_free_shipping() ) {
            $packages                = WC()->shipping()->get_packages();
            $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
    
            foreach ( $packages as $i => $package ) {
                $chosen_shipping_methods[ $i ] = 'free_shipping';
            }
    
            WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
        }

        $response['status'] = true;
        $response['message'] = sprintf( __('Coupon %s has been applied.', 'rpd-restaurant-solution' ), $coupon_code );

        return $response;
    }

    private function removeCookie( $cookie_name )
    {
        if( isset($_COOKIE[ $cookie_name ]) ) {
            unset($_COOKIE[ $cookie_name ]); 
            setcookie( $cookie_name, null, -1, '/' );
            return true;
        } else {
            return false;
        }
    }

    public function setDefaultShippingCountry()
    {
        if( WC()->customer->get_shipping_country() == '' ) {
            WC()->customer->set_country('RO');
            WC()->customer->set_shipping_country('RO');
        }
    }

    public function printDiscountBanner()
    {
        $onlineDiscount = (int)$this->plugin_options["online_discount"];

        $before_discount = esc_html__('Order online and you have', 'rpd-restaurant-solution');
        $after_discount = esc_html__('% discount', 'rpd-restaurant-solution');
 
        if( $onlineDiscount > 0 ) {
            return '<div class="online-discount"><p>' . $before_discount . ' <span>' . $onlineDiscount . $after_discount .'</span></p></div>';
        }
    }

    public function overrideWoocommerceTemplatesPath( $template, $template_name, $template_path )
    {
        global $woocommerce;

        $_template = $template;

        if ( ! $template_path ) {
            $template_path = $woocommerce->template_url;
        }
    
        $plugin_path  = $this->plugin_path . 'woocommerce-templates/';

        $template_names = array(
            $template_path . $template_name,
            $template_name
        );

        // Look within passed path within the child theme - this is priority
        foreach( $template_names as $template_name ) {
            $template = '';
            
            if ( ! $template_name ) {
                continue;
            }

            if ( file_exists( STYLESHEETPATH . '/' . $template_name ) ) {
                $template = STYLESHEETPATH . '/' . $template_name;
                break;
            } elseif ( file_exists( ABSPATH . WPINC . '/theme-compat/' . $template_name ) ) {
                $template = ABSPATH . WPINC . '/theme-compat/' . $template_name;
                break;
            }
        }

        if( !$template && file_exists( $plugin_path . $template_name ) ) {
            $template = $plugin_path . $template_name;
        }
        
        if ( ! $template ) {
            $template = $_template;
        }

        return $template;
    }

    public function unsetCheckoutFields( $fields ) 
    {
        unset($fields['billing']['billing_company']);
        unset($fields['billing']['billing_postcode']);
        unset($fields['billing']['billing_state']);
        
        return $fields;
    }

    public function addAutoFocus( $fields ) 
    {
        $fields['billing_first_name']['autofocus'] = true;

        return $fields;
    }

    private function checkOrderMinimumAmount()
    {
        if( isset($_POST['delivery_method']) && $_POST['delivery_method'] !== 'livrare-la-domiciliu' ) {
            return;
        }
        
        $minimum = (int)$this->plugin_options['minim_amount_per_order'];
        $onlineDiscount = (int)$this->plugin_options['online_discount'];
        $cart_amount = WC()->cart->subtotal;

        if( $minimum > 0 ) {
            $cart_amount = $cart_amount - ( $cart_amount * $onlineDiscount / 100 );

            if( WC()->cart->get_cart_discount_total() > 0 ) {
                $cart_amount = $cart_amount - WC()->cart->get_cart_discount_total();
            }
            
            if ( $cart_amount < $minimum ) {
                if( is_cart() ) {
                    wc_print_notice(
                        sprintf( esc_html__('The minimum amount for an order is %s, the amount of products you added to the order is %s.', 'rpd-restaurant-solution' ) , 
                            wc_price( $minimum ), 
                            wc_price( $cart_amount )
                        ), 'error' 
                    );
                } else {
                    wc_clear_notices();
                    wc_add_notice(
                        sprintf( esc_html__('The minimum amount for an order is %s, the amount of products you added to the order is %s.', 'rpd-restaurant-solution' ) , 
                            wc_price( $minimum ), 
                            wc_price( $cart_amount )
                        ), 'error' 
                    );
                }
            }
        }
    }

    public function checkOrderAmountOnCheckout() 
    {
        if( isset($_POST['delivery_method']) && $_POST['delivery_method'] === 'livrare-la-domiciliu' ) {
            $this->checkOrderMinimumAmount();
        }
    }

    public function checkOrderAmount() 
    {
        $this->checkOrderMinimumAmount();
    }

    public function applyOnlineDiscount( $cart ) 
    {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            return;
        }

        if( $cart->is_empty() ) {
            return;
        }

        $discount = array();
        $default_discount = 0;
        $online_discount = (float)$this->plugin_options['online_discount'];
        $shipping_cost = (float)$cart->shipping_total;
        $fee_total = $cart->get_fee_total();

        if( !WC()->cart->is_empty() ) {

            if( $this->activated( 'category_discount' ) ) {
                $discount = $this->calculateTaxonomyDiscount( 'product_cat', 'category_discount' );
            } elseif( $this->activated( 'tag_discount' ) ) {
                $discount = $this->calculateTaxonomyDiscount( 'product_tag', 'tag_discount' );
            }
						
            if( !empty( $discount ) && $discount['total_discount'] != NULL ) {
                $cart->add_fee( __( "Discount", "rpd-restaurant-solution" ), -$discount['total_discount'], false );
            } else if( $online_discount > 0 ) {
                $default_discount = (float) $cart->subtotal * $online_discount / 100;
                
                $cart->add_fee( __( "Online discount ", "rpd-restaurant-solution" ) . $online_discount . '%', -$default_discount, false );
                $cart->total = $default_discount;

                if( $cart->get_cart_discount_total() > 0 ) {
                    if( ($cart->total - $cart->get_cart_discount_total()) <= 0 ) {
                        wc_clear_notices(); 
                        wc_add_notice( __('The discount could not be applied for the selected products.', 'rpd-restaurant-solution' ), 'error' );
                        if( !empty( $cart->get_applied_coupons() ) ) {
                            $last_coupon = end( $cart->get_applied_coupons() );
                            WC()->cart->remove_coupon( $last_coupon );
                        }
                    } else {
                        $cart->total = $cart->total - $cart->get_cart_discount_total();
                    }
                }

                if( $shippingCost > 0 ) {
                    $cart->total = $cart->total + $shipping_cost;
                }

                if( $fee_total != 0 ) {
                    $cart->total = $cart->total + $fee_total;
                }

            }
        }
    }

    public function addFakeEmailAddress( $order_id ) 
    {
        if ( $_POST['billing_email'] == "" ) {
            $_POST['billing_email'] = 'no-reply@demo.com';
            update_post_meta( $order_id, '_billing_email', sanitize_email($_POST['billing_email']));
        }
    }

    public function populateDefaultCity( $fields ) 
    {
        if( $this->activated( 'display_areas' ) ) {
            return $fields;
        }

        global $woocommerce;
        
        $defaultCity = get_option('woocommerce_store_city');
    
        if( !is_null( $defaultCity ) ) {
            $fields['billing']['billing_city']['default'] = $defaultCity;
        }
        
        return $fields;
    }

    public function dequeueStrengthMeter()
    {
        wp_dequeue_script( 'wc-password-strength-meter' );
    }

    public function removeOptionalLabel( $field )
    {
        if( is_checkout() && ! is_wc_endpoint_url() ) {
            $optional = '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'woocommerce' ) . ')</span>';
            $field = str_replace( $optional, '', $field );
        }

        return $field;
    }

    public function rewriteShippingLabel( $label, $method ) 
    {
        $has_cost  = 0 < $method->cost;
        $hide_cost = ! $has_cost && in_array( $method->get_method_id(), array( 'free_shipping', 'local_pickup' ), true );

        if ( $has_cost && ! $hide_cost ) 
            $label = wc_price( $method->cost );
        
        $label = str_replace('Cost', esc_html__('Free', 'rpd-restaurant-solution'), $label);
        
        return $label;
    }

    public function updateDefaultEmailAddress( $order )
    {
        if( get_post_meta($order, '_billing_email', true) == NULL || get_post_meta($order, '_billing_email', true) == "" ){
            add_post_meta($order, '_billing_email', 'no-replay@' . str_replace(array('http://', 'https://', 'http://www.', 'https://www.'), '', home_url()));
        }
    }

    public function addBillingUpsToSession() 
    {
        if( WC()->session->__isset('delivery_distance') ) {
            WC()->session->__unset('delivery_distance');
        }

        if( WC()->session->__isset('delivery_duration') ) {
            WC()->session->__unset('delivery_duration');
        }

        if( WC()->session->__isset('customer_address_lat') ) {
            WC()->session->__unset('customer_address_lat');
        }

        if( WC()->session->__isset('customer_address_lng') ) {
            WC()->session->__unset('customer_address_lng');
        }
        
        if (!empty($_POST['delivery_method'])) {
            WC()->session->set('rwb_delivery_method', sanitize_text_field($_POST['delivery_method']));
        }

        if ( $_POST['billing_ups'] == '1' ) {
            WC()->session->set('billing_ups', '1' );
        } else {
            WC()->session->set('billing_ups', '0' );
        }

        echo json_encode( WC()->session->get('rwb_delivery_method' ) );
        die();
    }

    public function addCustomShippingCost( $rates, $package ) 
    {
        $change_price = false;

        if (WC()->session->get('billing_ups') == '1') {
            $change_price = 0;
        } else {
            if (WC()->session->get('override_shipping_price')) {
                $change_price = WC()->session->get('override_shipping_price');
            }
        }

        if ($change_price !== false && $change_price != 0) {
            foreach ($rates as $rate_key => $rate_values) {
                $rates[$rate_key]->cost = $change_price;

                $taxes = array();
                foreach ($rates[$rate_key]->taxes as $key => $tax)
                    if ($rates[$rate_key]->taxes[$key] > 0) // set the new tax cost
                        $taxes[$key] = 0;

                $rates[$rate_key]->taxes = $taxes;
            }
        }

        return $rates;
    }

    public function refreshShippingMethods( $post_data )
    {
        $this->calculateShippingCostPerDistance();
        $bool = true;
        if (WC()->session->get('delivery_method') == 'ridicare-personala' || WC()->session->get('delivery_method') == 'servire-la-restaurant') $bool = false;

        // Mandatory to make it work with shipping methods
        foreach (WC()->cart->get_shipping_packages() as $package_key => $package) {
            WC()->session->set('shipping_for_package_' . $package_key, $bool);
        }
        WC()->cart->calculate_shipping();
    }

    public function triggerUpdateCheckout() 
    { ?>
        <script type="text/javascript">
            jQuery(function ($) {
                // update cart on delivery location checkbox option
                var checkoutWrapper = '.woocommerce-checkout-review-order, .order-procced';

                $('#pickup_point').on('change', function () {
                    $('body').trigger('update_checkout');
                });

                $('#delivery_method').on('change', function () {
                    var checked = 0;
                    var delivery_metod = $('#delivery_method').val();
                    if (delivery_metod == 'ridicare-personala' || delivery_metod == 'servire-la-restaurant')
                        checked = 1;

                    $(checkoutWrapper).block({
                        message: null,
                        overlayCSS: {
                            background: "#fff",
                            opacity: .6
                        }
                    });
                    
                    setTimeout( function() { 
                        $.ajax({
                            type: 'POST',
                            url: wc_checkout_params.ajax_url,
                            data: {
                                'action': 'woo_get_ajax_data',
                                'billing_ups': checked,
                                'delivery_method': delivery_metod,
                            },
                            success: function (result) {
                                $(checkoutWrapper).unblock();
                                $('body').trigger('update_checkout');
                                console.log(result);
                            },
                            error: function (error) {
                                $(checkoutWrapper).unblock();
                                console.log(error); // just for testing
                            }
                        });
                    }, 1000);
                });

                $( '#delivery_method .blue-cards label' ).on('click', function(){
					var checked = 0;
					var delivery_metod = $(this).find('input').val();
					if (delivery_metod == 'ridicare-personala' || delivery_metod == 'servire-la-restaurant')
                        checked = 1;
					
                    $(checkoutWrapper).block({
                        message: null,
                        overlayCSS: {
                            background: "#fff",
                            opacity: .6
                        }
                    });
                    
                    setTimeout(function(){ 
                        $.ajax({
                            type: 'POST',
                            url: wc_checkout_params.ajax_url,
                            data: {
                                'action': 'woo_get_ajax_data',
                                'billing_ups': checked,
                                'delivery_method': delivery_metod,
                            },
                            success: function (result) {
                                $(checkoutWrapper).unblock();
                                $('body').trigger('update_checkout');
                            },
                            error: function (error) {
                                $(checkoutWrapper).unblock();
                                console.log(error); // just for testing
                            }
                        });
                    }, 1000);
				});
            });
        </script>
        <?php
    }

    public function addCategoryNameToSearchQuery($query) 
    {
        if ( !$query->is_search ) {
            return $query;
        }

        if( !isset($_GET['post_type']) ) {
            return $query;
        }
        
        global $wpdb;
        $keyword = '';

        if( isset( $_GET['s'] ) || $_GET['s'] != '' ) {
            $keyword = sanitize_text_field($_GET['s']);
        }
        
        if( $keyword == '' ) {
            return $query;
        }

        $term = get_term_by('name', $keyword, 'product_cat');

        if( $term ) { 

            $results = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM {$wpdb->prefix}posts WHERE ( post_title LIKE %s OR post_content LIKE  %s ) AND post_status = 'publish' AND post_type = 'product'", '%' . $keyword . '%', '%' . $keyword . '%')
            );

            if( count($results) > 0 ) {
                return $query;
            }
                
            $query->set('s','');

            $taxquery = array(
                'relation' => 'AND',
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => $term->slug,
                    'operator'=> 'AND'
                )
            );
            
            $query->set( 'tax_query', $taxquery );

        }
        
        return $query;
    }

    public function calculateShippingCostPerDistance()
    {
        if( !$this->activated('distance_manager') ) {
            return;
        }

        if( !$this->plugin_options['google_maps_api_key'] || $this->plugin_options['google_maps_api_key'] == '' ) {
            return;
        }

        if( !$this->plugin_distance_options || empty($this->plugin_distance_options) ) {
            return;
        }
        
        $distance = $minimum = 0;
        $notDeliverDistance = 0;
        $shippingPrice = 0;
        $deliveryMethod = $fullAddress = '';
        $billingBuilding = $billingFlat = $billingScale = $billingFloor = '';
        $distanceExceeded = false;
        $data = array();
        $args = array();
        $apiUrl = 'https://maps.googleapis.com/maps/api/directions/json';

        $googleMapsKey = $this->plugin_options['google_maps_api_key'];
        $cost_distances = $this->plugin_distance_options;

        if ( isset($_POST['post_data']) ) {
            parse_str($_POST['post_data'], $post_data);
        } else {
            $post_data = $_POST;
        }
        
        $homeUrl = get_home_url();
        $storeCity = get_option("woocommerce_store_city");
        $storeAddress = get_option("woocommerce_store_address") . ', ' . $storeCity;
        $billingAddress = wc_clean($post_data['billing_address_1']);
        $billingCity = wc_clean($post_data['billing_city']);
        $billingArea = wc_clean($post_data['areas']);

        if( $billingAddress == '' ) {
            return;
        }
        
        if( isset( $post_data['billing_bloc'] ) ) {
            $billingBuilding = wc_clean($post_data['billing_bloc']);
        }

        if( isset( $post_data['billing_apartament'] ) ) {
            $billingFlat = wc_clean($post_data['billing_apartament']);
        }

        if( isset( $post_data['billing_scara'] ) ) {
            $billingScale = wc_clean($post_data['billing_scara']);
        }

        if( isset( $post_data['billing_etaj'] ) ) {
            $billingFloor = wc_clean($post_data['billing_etaj']);
        }
        
        $cartTotal = WC()->cart->total;

        if ( WC()->session->get('rwb_delivery_method') ) {
            $deliveryMethod = WC()->session->get('rwb_delivery_method');
        } else {
            if( isset($post_data['delivery_method']) && $post_data['delivery_method'] != NULL ) {
                $deliveryMethod = $post_data['delivery_method'];
            }
        }

        foreach( $cost_distances as $cost_distance ) {
            if( (int)$cost_distance['maximum_interval'] > $notDeliverDistance ) {
                $notDeliverDistance = (int)$cost_distance['maximum_interval'];
            }
        }

        if( $notDeliverDistance == 0 ) {
            wc_clear_notices();
            wc_add_notice( sprintf( __('The delivery distances were not set correctly in the admin area. Please contact the administrator to continue.', 'rpd-restaurant-solution') ), 'error' );
            return;
        }

        if( $billingAddress != '' && $deliveryMethod == 'livrare-la-domiciliu' ) {

            $fullAddress =  ( $billingBuilding && $billingBuilding != '' ? 'bloc ' . $billingBuilding : '' ) . ( $billingFlat && $billingFlat != '' ? ', apartament ' . $billingFlat : '' ) . ( $billingScale && $billingScale != '' ? ', scara ' . $billingScale : '' ) . ( $billingFloor && $billingFloor != '' ? ', etaj ' . $billingFloor : '' );

            if( substr($fullAddress, 0, 2) == ', ' ) {
                $fullAddress = substr($fullAddress, 2);
            }

            $destination = ( $fullAddress != '' ? $fullAddress . ', ' : '' ) . $billingAddress . ( $billingArea && $billingArea != '' ? ', ' . $billingArea : '' ) . ', ' . $billingCity;

            $data = array(
                'origin' => $storeAddress,
                'destination' => $destination,
                'alternatives' => false,
                'sensor' => true,
                'mode' => 'DRIVING',
                'key' => $googleMapsKey
            );

            $data = http_build_query($data);
            
            $apiUrl = $apiUrl . '?' . $data;

            $response = wp_remote_get( $apiUrl );

            if( $response['response']['code'] === 200 ) {
                $response = json_decode($response['body']);

                $distance = $response->routes[0]->legs[0]->distance->value;
                $duration = $response->routes[0]->legs[0]->duration->value;
                $customer_address_lat = $response->routes[0]->legs[0]->end_location->lat;
                $customer_address_lng = $response->routes[0]->legs[0]->end_location->lng;

                if( $distance == null ){
                    wc_clear_notices();
                    wc_add_notice( sprintf( esc_html__('Sorry, we cannot deliver to this address. Please enter a new address.', 'rpd-restaurant-solution') ), 'error' );
                    return;
                }
            } else {
                wc_add_notice( sprintf( 'Request Error:' . $response['response']['message'] ), 'error' );
                return;
            }

            if( $distance != null && $distance > 0 ) {
                $distance = $distance / 1000; // Converting meters to kilometers
                
                if( $distance > $notDeliverDistance ) {
                    wc_clear_notices();
                    wc_add_notice( sprintf( esc_html__('Sorry, we cannot deliver to this address. Please enter a new address.', 'rpd-restaurant-solution') ), 'error' );
                    return;
                }
    
                WC()->session->set('delivery_distance', $distance);
                WC()->session->set('delivery_duration', $duration);
                WC()->session->set('customer_address_lat', $customer_address_lat);
                WC()->session->set('customer_address_lng', $customer_address_lng);

                foreach( $cost_distances as $cost_distance ) {
                    if( $distance >= (float)$cost_distance['minimum_interval'] && $distance < (float)$cost_distance['maximum_interval'] ) {
                        if( $cost_distance['cost_interval'] ) {
                            $shippingPrice = (float)$cost_distance['cost_interval'];
                        }

                        if( $cost_distance['minimum_amount_interval'] ) {
                            $minimum = (float)$cost_distance['minimum_amount_interval'];
                        }
                    }
                }
                
                if( $shippingPrice != 0 ) {
                    $cartTotal = $cartTotal - $shippingPrice;
                    WC()->session->set('override_shipping_price', $shippingPrice);
                } else {
                    WC()->session->__unset('override_shipping_price');
                }

                if( $cartTotal < $minimum ) {
                    wc_clear_notices();
                    wc_add_notice(
                        sprintf( esc_html__('The minimum amount for an order in this area is %s.', 'rpd-restaurant-solution'),
                            wc_price($minimum)
                        ), 'error'
                    );
                }
            } else {
                wc_clear_notices();
                wc_add_notice( sprintf( __('Something went wrong. Please try again in a few seconds.', 'rpd-restaurant-solution') ), 'error' );
                return;
            }
        }
    }

    public function overwriteOrderByArgs( $args ) 
    {
        if( $this->isActiveCheckoutV2() ) {
            return $args;
        }
        
        return array(
            'orderby'  => '',
            'order'    => '',
            'meta_key' => '',
        );
    }

    public function updatePostsPerPageArgument( $q ) 
    {
        if ( isset( $_GET['orderby'] ) && ! is_admin() ) {
            $q->set( 'posts_per_page', sanitize_text_field($_GET['orderby']) );
        }
    }

    public function displayDeliveryTime()
    {
        if( !is_checkout() ) {
            return;
        }
        
        $startTime = '10:00';
        $endTime = '22:00';
        $parentCategoryId = 0;
        $deliveryTime = 60;
        $lapse = 15;
        $allowDates = $allowedTimes = array();
        $isCustomInterval = false;
        
        $date = new DateTime();
        $date->setTimezone( new DateTimeZone('Europe/Bucharest') );
        $currentTime = $date->format('d/m/Y H:i');
        $currentDate = $date->format('d/m/Y');
        $currentHour = $date->format('H:i');
        $customNextDay = '';

        $route = '/venue/integration/getvenuedetails';
        $result = PushNotification::makeNotification( $route );

        if( ! WC()->cart->is_empty() ){
            foreach ( WC()->cart->get_cart() as $cart_item ) {
                $productId = $cart_item['product_id'];
                $currentProductCategories = get_the_terms( $productId, 'product_cat' );
                $parentCategoryId = $currentProductCategories[0]->parent;
            }
        }

        if( get_term_meta( $parentCategoryId , 'category_delivery_time', true ) == '1' && get_term_meta( $parentCategoryId , 'start_hour', true ) && get_term_meta( $parentCategoryId , 'end_hour', true ) ) { // Next day
            $startTime = get_term_meta( $parentCategoryId , 'start_hour', true );
            $endTime = get_term_meta( $parentCategoryId , 'end_hour', true );

            if( get_term_meta( $parentCategoryId , 'time_slot', true ) ) {
                $lapse = get_term_meta( $parentCategoryId , 'time_slot', true );
            }

            if( get_term_meta( $parentCategoryId , 'custom_interval_time', true ) == 'on' ) {
                $isCustomInterval = true;
            }

            $nextDay = $date->modify('+1 day')->format('d/m/Y');
            $allowDates[] = $nextDay;

            $allowedTimes = $this->createTimeRange( $startTime, $endTime, $lapse . ' mins' );
        } else if( get_term_meta( $parentCategoryId , 'category_delivery_time', true ) == '2' && get_term_meta( $parentCategoryId , 'start_hour', true ) && get_term_meta( $parentCategoryId , 'end_hour', true ) ) { // Daily
            $startTime = get_term_meta( $parentCategoryId , 'start_hour', true );
            $endTime = get_term_meta( $parentCategoryId , 'end_hour', true );

            if( get_term_meta( $parentCategoryId , 'time_slot', true ) ) {
                $lapse = get_term_meta( $parentCategoryId , 'time_slot', true );
            }

            if( $result->status === 200 && $result->data->deliveryTime !== null ) {
                $deliveryTime = (int)$result->data->deliveryTime;
            }

            if( strtotime($currentHour) > strtotime($startTime) ) {
                $startTime = $currentHour;
            }

            if( get_term_meta( $parentCategoryId , 'custom_interval_time', true ) == 'on' ) {
                $isCustomInterval = true;
            }

            $startTime = date('H:i', strtotime( (string)'+'.$deliveryTime.' minutes', strtotime($startTime)));

            $startTime = $this->roundHourToQuarter($startTime);

            $allowDates[] = $currentDate;

            if( strtotime($currentHour) >= strtotime($endTime) ) {
                $allowedTimes = [];
            } else {
                $allowedTimes = $this->createTimeRange( $startTime, $endTime, $lapse . ' mins' );
                if( !empty( $allowedTimes ) ) {
                    if( strtotime(end( $allowedTimes )) > strtotime($endTime) ) {
                        $index = count( $allowedTimes ) - 1;
                        $value = $allowedTimes[$index];
                        $allowedTimes[$index] = preg_replace( "/,\ $/", "", $endTime );
                    }
                }
            }

        } else if( get_term_meta( $parentCategoryId , 'category_delivery_time', true ) == '3' && get_term_meta( $parentCategoryId , 'custom_dates', true ) && get_term_meta( $parentCategoryId , 'start_hour', true ) && get_term_meta( $parentCategoryId , 'end_hour', true ) ) { // Custom dates
            if( get_term_meta( $parentCategoryId , 'custom_dates', true ) != '' ) {
                $startTime = get_term_meta( $parentCategoryId , 'start_hour', true );
                $endTime = get_term_meta( $parentCategoryId , 'end_hour', true );
                if( get_term_meta( $parentCategoryId , 'time_slot', true ) ) {
                    $lapse = get_term_meta( $parentCategoryId , 'time_slot', true );
                }

                if( get_term_meta( $parentCategoryId , 'custom_interval_time', true ) == 'on' ) {
                    $isCustomInterval = true;
                }

                $allowDates = explode(',', get_term_meta( $parentCategoryId , 'custom_dates', true ));
                $allowedTimes = $this->createTimeRange( $startTime, $endTime, $lapse . ' mins' );
            }
        } else if( get_term_meta( $parentCategoryId , 'category_delivery_time', true ) == '4' && get_term_meta( $parentCategoryId , 'start_hour', true ) && get_term_meta( $parentCategoryId , 'end_hour', true ) ) { // Exclude today
            $startTime = get_term_meta( $parentCategoryId , 'start_hour', true );
            $endTime = get_term_meta( $parentCategoryId , 'end_hour', true );
            if( get_term_meta( $parentCategoryId , 'time_slot', true ) ) {
                $lapse = get_term_meta( $parentCategoryId , 'time_slot', true );
            }

            if( get_term_meta( $parentCategoryId , 'custom_interval_time', true ) == 'on' ) {
                $isCustomInterval = true;
            }

            $allowDates = [];
            $customNextDay = $date->modify('+1 day')->format('d/m/Y');
            $allowedTimes = $this->createTimeRange( $startTime, $endTime, $lapse . ' mins' );
        } else if( $result->status === 200 ) {

            if( $result->data->daysIsActive == true ) {

                $route = '/venue/integration/getalloweddays?category=' . $parentCategoryId;
                $result = PushNotification::makeNotification( $route );

                if( $result->status == 200 ){	

                    if( !empty((array)$result->data->holidayDays) ) {
				    
						$holidayDays = (array)$result->data->holidayDays;
						$currentHour = $this->roundHourToQuarter( $currentHour );

						foreach( $holidayDays as $days ){
							$allowDates[] = $days; 
						}
						
					} else if( !empty((array)$result->data->allowedDays) ) {
                        
                        $allowedDays = (array)$result->data->allowedDays;
                        $currentHour = $this->roundHourToQuarter( $currentHour );

                        foreach( $allowedDays as $days ){
                            $allowDates[] = $days->day;

                            foreach( $days->dayInterval as $dayInterval ){
                                if( strtotime($currentHour) > strtotime($dayInterval->start) && $currentDate == $days->day ) 
                                    $dayInterval->start = $currentHour;

                                $allowedTimes = $this->createTimeRange( $dayInterval->start, $dayInterval->end, '15 mins' );
                            }
                            
                            if( ! empty($allowedTimes) )
                                horeka_core_set_cookie_for_php_version((string)$days->day, json_encode($allowedTimes));

                        }

                    } else {
                        wc_clear_notices();
                        wc_add_notice( sprintf( esc_html__('Orders cannot be taken at this time.', 'rpd-restaurant-solution') ), 'error' );
                        return;
                    }

                }

            } else {
                
                $allowDates[] =  $currentDate;
                $currentHour = (int)$date->format('H');
                $currentMinute = (int)$date->format('i');
                
                $currentHour = $currentHour . ':' . $currentMinute;

                if( $result->status === 200 ){

                    $websiteSetup = json_decode($result->data->websiteSetup);
                    $interval = $websiteSetup->interval;
                    
                    setlocale(LC_ALL, 'en_GB.UTF-8');
                    $currentDay = strftime("%A");
                    
                    if( $interval->{"Start".$currentDay} != NULL )
                        $startTime = $interval->{"Start".$currentDay};

                    if( $interval->{"End".$currentDay} != NULL )
                        $endTime = $interval->{"End".$currentDay};
                    
                    if( $result->data->deliveryTime != NULL )
                        $deliveryTime = (int)$result->data->deliveryTime;

                    if( strtotime($currentHour) < strtotime($startTime) ) 
                        $currentHour = $startTime;

                    if( strtotime($currentHour) > strtotime($endTime) ) 
                        $currentHour = $endTime;
                    
                } else {
                    $log_data['error_title'] = 'Get Venue Details Error (/venue/integration/getvenuedetails)';
                    $log_data['error_content'] = 'An error has occurred during the connection between WordPress and API.';

                    $log_meta['customer_ip'] = ( !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '' );
                    $log_meta['user_id'] = ( get_current_user_id() ? get_current_user_id() : "" );
                    $log_meta['status'] = ( $response->status != NULL ? $response->status : "" );
                    
                    ErrorLog::log($log_data, $log_meta);
                }

                $currentHour = date('H:i', strtotime( (string)'+'.$deliveryTime.' minutes', strtotime($currentHour)));
                $currentHour = $this->roundHourToQuarter( $currentHour );

                $allowedTimes = $this->createTimeRange( $currentHour, $endTime, '15 mins' );
                
            }

        } else {
            $allowDates[] = $currentDate;

            if( strtotime($currentHour) > strtotime($startTime) ) {
                $startTime = $currentHour;
            }

            $startTime = date('H:i', strtotime( (string)'+'.$deliveryTime.' minutes', strtotime($startTime)));
            $startTime = $this->roundHourToQuarter( $startTime );
                                
            $allowedTimes = $this->createTimeRange( $startTime, $endTime, '15 mins' );
        }
        
        ?>

        <script>
            jQuery( document ).ready(function($){

                $('.page-loader').css('display', 'none');
                $('#delivery_time, #delivery_hour').attr('readonly', true);
                $('#delivery_hour').find('option').remove().end();

                var allowedTimes = <?php echo json_encode($allowedTimes); ?>;
                var lapse = <?php echo $lapse; ?>;
                var isCustomInterval = <?php echo json_encode($isCustomInterval); ?>;

                if( getCookie(<?php echo json_encode($allowDates, JSON_UNESCAPED_SLASHES); ?>[0]) )
                    allowedTimes = JSON.parse(getCookie(<?php echo json_encode($allowDates[0], JSON_UNESCAPED_SLASHES); ?>));

                if( isCustomInterval ) {
                    $.each(allowedTimes, function(key, value) {  
                        var newDate = new Date( '01/01/1970 ' + value);
                        var dateWithLapse = new Date(newDate.getTime() + lapse*60000);
                        $('#delivery_hour')
                            .append(
                                $("<option></option>")
                                .attr("value", value)
                                .text(value + ' - ' + dateWithLapse.getHours() + ':' + ( dateWithLapse.getMinutes() === 0 ? '00' : dateWithLapse.getMinutes() ) )
                        );
                    });
                } else {
                    $.each(allowedTimes, function(key, value) {  
                        $('#delivery_hour')
                            .append(
                                $("<option></option>")
                                .attr("value", value)
                                .text(value)
                        );
                    });
                }
                
                $('#delivery_time').datetimepicker({
                    timepicker: false,
                    value: <?php echo ( !empty($allowDates) ? json_encode($allowDates, JSON_UNESCAPED_SLASHES) : json_encode(array($customNextDay), JSON_UNESCAPED_SLASHES) ); ?>[0],
                    allowDates: <?php echo json_encode($allowDates, JSON_UNESCAPED_SLASHES); ?>,
                    format: 'd/m/Y',
                    formatDate:'d/m/Y',
                    dayOfWeekStart: 1,
                    //disabledWeekDays: [0,6],
                    disabledDates: <?php echo ( $customNextDay != '' ? json_encode(array($currentDate), JSON_UNESCAPED_SLASHES) : json_encode(array(), JSON_UNESCAPED_SLASHES) ) ?>,
                    scrollMonth : false,
                    minDate: 0,
                    startDate: <?php echo ( $customNextDay != '' ? json_encode(array($customNextDay), JSON_UNESCAPED_SLASHES) : json_encode(array(), JSON_UNESCAPED_SLASHES) ); ?>[0],
                    onChangeDateTime:function(dp,$input){
                        if( getCookie($input.val()) ){
                            var allowedTimesCookie = JSON.parse(getCookie($input.val()));
                            $('#delivery_hour').find('option').remove().end();
                            $.each(allowedTimesCookie, function(key, value) {   
                                $('#delivery_hour')
                                    .append($("<option></option>")
                                        .attr("value", value)
                                        .text(value));
                            });
                        }
                    }
                });
            });
        </script>
    <?php }

    public function validateApiHours()
    {
        // Default category ID
        $parentCategoryId = 0;
        $deliveryDateTime = '';
        
        // Set and get current time
        $date = new DateTime();
        $date->setTimezone(new DateTimeZone('Europe/Bucharest'));
        $currentTime = $date->format('d/m/Y H:i');

        // Get venue details from API
        $route = '/venue/integration/getvenuedetails';
        $result = PushNotification::makeNotification( $route );

        // Get current category ID from cart
        if( ! WC()->cart->is_empty() ){
            foreach ( WC()->cart->get_cart() as $cart_item ) {
                $productId = $cart_item['product_id'];
                $currentProductCategories = get_the_terms( $productId, 'product_cat' );
                $parentCategoryId = $currentProductCategories[0]->parent;
            }
        }

        if( $result->status === 200 ){
            if( $result->data->daysIsActive == true ) { // JSON is active

                $route = '/venue/integration/getalloweddays?category=' . $parentCategoryId;
                $result_v2 = PushNotification::makeNotification( $route );

                if( !empty((array)$result_v2->data->holidayDays) ) {
                    return;
                }

                if( $result_v2->status == 200 && empty((array)$result_v2->data->allowedDays) ){ // The orders are not allowed right now
                    wc_clear_notices();
                    wc_add_notice( sprintf( esc_html__('Orders cannot be taken at this time.', 'rpd-restaurant-solution') ), 'error' );
                } else { 
                    if( isset($_POST['delivery_time']) && $_POST['delivery_time'] != "" && isset($_POST['delivery_hour']) && $_POST['delivery_hour'] != "" ) {
                        $deliveryDateTime = sanitize_text_field($_POST['delivery_time']) . ' ' . sanitize_text_field($_POST['delivery_hour']);
                    } else {
                        $deliveryDateTime = $currentTime;
                    }
                    
                    $route = '/venue/integration/checkvalidityhoursstatus?date='.urlencode($deliveryDateTime).'&category='.$parentCategoryId;
                    $result_v3 = PushNotification::makeNotification( $route );
    
                    if( $result_v3 == false || $result_v3->data == false ) { // Check if the selected time/current time is still available
                        wc_clear_notices();
                        wc_add_notice( sprintf( esc_html__('Orders cannot be taken at this time.', 'rpd-restaurant-solution') ), 'error' );
                    }
                }
                
            }
        }
    }

    public function displayMenuDot()
    {
        if( function_exists('horeka_core_get_daily_link') ){
            if( horeka_core_get_daily_link() != NULL ) {
                echo '<div class="orange-bullet"><a class="daily-menu" href=" ' . horeka_core_get_daily_link() . '">' . esc_html__('Menu of the day', 'rpd-restaurant-solution') . '</a></div>';
            }
        }
    }

    public function validatePhoneNumber()
    {
        if( $_POST['billing_phone'] && $_POST['billing_phone'] !== '' ) {
            $phone_number = wc_sanitize_phone_number($_POST['billing_phone']);
            
            if( strlen($phone_number) < 9 || strlen($phone_number) > 15 ) {
                wc_add_notice( sprintf( esc_html__('Please insert a valid phone number.', 'rpd-restaurant-solution') ), 'error' ); 
            }
        }
    }

    public function insertTrakingLink( $order )
    {   
        $language = 'ro';
        $plugin_options = $this->getPluginOptions();
        $api_key = ( isset($plugin_options['api_key']) && $plugin_options['api_key'] !== '' ? $plugin_options['api_key'] : '' );
        $order_id = ( !is_null($plugin_options['reference_id_prefix']) && $plugin_options['reference_id_prefix'] !== '' ? $plugin_options['reference_id_prefix'] . '-' : '' ) . $order->get_order_number();
        $current_language = apply_filters( 'wpml_current_language', NULL );
        $home_url = home_url();

        if ( $home_url != null && substr($home_url, -1) == '/' ) {
            $home_url = substr( $home_url, 0, -1 );
        }

        if( $current_language && $current_language != 'ro' ) {
            $language = $current_language;
        }

        if( $api_key === '' ) {
            return;
        }

        printf( 
            __('<p>Click <a href="%1$s/tracking-page?apiKey=%2$s&orderId=%3$s&language=%4$s">here</a> to check your order status.</p>', 'rpd-restaurant-solution'),
            $home_url,
            $api_key,
            $order_id,
            $language
        );
    }

    public function hideShippingWhenFreeIsAvailable( $rates )
    {
        $free = array();
        
        foreach ( $rates as $rate_id => $rate ) {
            if ( 'free_shipping' === $rate->method_id ) {
                $free[ $rate_id ] = $rate;
                break;
            }
        }

        return ! empty( $free ) ? $free : $rates;
    }

    public static function isDiscountActive()
    {
        $discount = array();

        if( (new self)->activated( 'category_discount' ) ) {
            $discount = (new self)->calculateTaxonomyDiscount( 'product_cat', 'category_discount' );
        } elseif( (new self)->activated( 'tag_discount' ) ) {
            $discount = (new self)->calculateTaxonomyDiscount( 'product_tag', 'tag_discount' );
        }

        return !empty( $discount ) && (float)$discount['total_discount'] > 0;
    }

    public function applyDeliveryMethodDiscount( $cart )
    {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            return;
        }

        global $woocommerce;
        
        $curentDeliveryMethod = '';
        $deliveryMethods = $this->getDeliveryMethods();
        $online_discount = 0;
        $fee = 0;
        
        if ( isset($_POST['post_data']) ) {
            parse_str($_POST['post_data'], $post_data);
        } else {
            $post_data = $_POST;
        }

        if( isset( $post_data['delivery_method'] ) && $post_data['delivery_method'] != '' ) {
            $curentDeliveryMethod = wc_clean($post_data['delivery_method']);
        }
        
        if( $curentDeliveryMethod != '' && isset($deliveryMethods) && !empty( $deliveryMethods ) ) {
            if( isset($deliveryMethods[$curentDeliveryMethod]['method_discount']) ) {
                $delivery_method_discount = $deliveryMethods[$curentDeliveryMethod]['method_discount'];
                $online_discount = (float)$this->plugin_options['online_discount'];

                if( $online_discount > 0 ) {
                
                    $default_discount = $cart->subtotal - ( $cart->subtotal * $online_discount / 100 );
                    
                    $cart->total = $default_discount;

                    if( $cart->get_cart_discount_total() > 0 ) {
                        $cart->total = $cart->total - $cart->get_cart_discount_total();
                    }

                    if( $shippingCost > 0 ) {
                        $cart->total = $cart->total + $shipping_cost;
                    }

                    if( $fee_total != 0 ) {
                        $cart->total = $cart->total + $fee_total;
                    }

                }

                if (strpos($delivery_method_discount, '%') !== false) { // Percentage discount
                    $delivery_method_discount = (float)str_replace( '%', '', $delivery_method_discount );
                    if( $delivery_method_discount > 0 ) {
                        $cart_total = $woocommerce->cart->cart_contents_total;
                        $fee = $cart_total * $delivery_method_discount / 100;
                    }
                } else { // Fixed discount
                    $delivery_method_discount = (float)$delivery_method_discount;
                    if( $delivery_method_discount > 0 ) {
                        $fee = $delivery_method_discount;
                    }
                }

                if( $fee > 0 ) {
                    $cart->add_fee( __( "Discount", "rpd-restaurant-solution" ), -$fee, false );
                }                
            }
        }
    }
    
    public function applyDeliveryPointDiscount( $cart )
    {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            return;
        }

        $curentDeliveryPoint = '';
        $deliveryPoints = $this->getDeliveryPoints();
        $online_discount = 0;
        $fee = 0;
        
        if ( isset($_POST['post_data']) ) {
            parse_str($_POST['post_data'], $post_data);
        } else {
            $post_data = $_POST;
        }

        if( $post_data['delivery_method'] != 'ridicare-personala' ) {
            return;
        }

        if( isset( $post_data['pickup_point'] ) && $post_data['pickup_point'] != '' ) {
            $curentDeliveryPoint = wc_clean($post_data['pickup_point']);
        }
        
        if( $curentDeliveryPoint != '' && isset($deliveryPoints) && !empty( $deliveryPoints ) ) {
            if( isset($deliveryPoints[$curentDeliveryPoint]['method_discount']) ) {
                $delivery_point_discount = $deliveryPoints[$curentDeliveryPoint]['method_discount'];

                $online_discount = (float)$this->plugin_options['online_discount'];

                if( $online_discount > 0 ) {
                
                    $default_discount = $cart->subtotal - ( $cart->subtotal * $online_discount / 100 );
                    
                    $cart->cart_contents_total = $default_discount;

                    if( $cart->get_cart_discount_total() > 0 ) {
                        $cart->cart_contents_total = (float)$cart->cart_contents_total - $cart->get_cart_discount_total();
                    }

                    if( $shippingCost > 0 ) {
                        $cart->cart_contents_total = (float)$cart->cart_contents_total + $shipping_cost;
                    }

                    if( $fee_total != 0 ) {
                        $cart->cart_contents_total = (float)$cart->cart_contents_total + $fee_total;
                    }

                }

                if (strpos($delivery_point_discount, '%') !== false) { // Percentage discount
                    $delivery_point_discount = (float)str_replace( '%', '', $delivery_point_discount );
                    if( $delivery_point_discount > 0 ) {
                        $cart_total = (float)$cart->cart_contents_total;
                        $fee = $cart_total * $delivery_point_discount / 100;
                    }
                } else { // Fixed discount
                    $delivery_point_discount = (float)$delivery_point_discount;
                    if( $delivery_point_discount > 0 ) {
                        $fee = $delivery_point_discount;
                    }
                }

                if( $fee > 0 ) {
                    $cart->add_fee( __( "Discount", "rpd-restaurant-solution" ), -$fee, false );
                }                
            }
        }
    }

    public function addDelayForAddressValidation()
    { 
        if( !$this->activated('distance_manager') ) {
            return;
        }

        if( !$this->plugin_options['google_maps_api_key'] || $this->plugin_options['google_maps_api_key'] == '' ) {
            return;
        }

        if( !$this->plugin_distance_options || empty($this->plugin_distance_options) ) {
            return;
        }
        
        ?>
            <script>
                jQuery( document ).ready(function($){
                    setTimeout( function() {
                        $('#billing_address_1_field').removeClass( 'address-field' );
                    }, 500 );
                    
                    $('#billing_address_1').keyup( function( event ){
                        setTimeout( function() {
                            $( 'body' ).trigger( 'update_checkout' );
                        }, 4000 );
                    });
                });
            </script>
        <?php 
    }

    public function insertAjaxCouponScript()
    {
        ?>
            <script>
                jQuery( document ).ready(function($){
                    
                    $(document).on('click','input[name="apply_coupon"]',function( e ){

                    e.preventDefault();

                    var coupon_value = jQuery('input[name="coupon_code"]').val();

                    if( coupon_value != '' ) {
                        var data= {
                            'action':'apply_coupon_via_ajax',
                            'code':jQuery('input[name="coupon_code"]').val()
                        };
                        
                        jQuery.post('/wp-admin/admin-ajax.php', data, function(response) {
                            var custom_class = 'red';

                            if( response.status == true ) {
                                custom_class = 'green';

                                $( 'body' ).trigger( 'update_checkout' );
                                $( 'input[name="coupon_code"]' ).val("");
                            }

                            if( response.message != "" ) {
                                $('.checkout_coupon > p:last-child').append('<p class="coupon_message ' + custom_class + '">' + response.message + '</p>');
                                setTimeout( function() { $('p.coupon_message').fadeOut() }, 3000 );
                            }
                        }, 'json');
                    }
                    
                    });
                });
            </script>
        <?php
    }

    public function applyCouponViaAjax()
    {
        $coupon_code = strtolower( trim( wc_sanitize_coupon_code($_POST['code']) ) );
        
        $coupon = new WC_Coupon( $coupon_code ); 
        $coupon_post = get_post( $coupon->id );

        $response = array(
            'status' => false,
            'message' => sprintf( __('Coupon %s does not exist.', 'rpd-restaurant-solution' ), $coupon_code )
        );

        if( $this->activated( 'auto_apply_coupon_for_new_customers' ) && $this->activated('auto_apply_coupon_for_new_customers_app_mobile_only') && $this->getPluginOptions()['coupon_code_for_new_customers'] && !WC()->session->get('showNavbar') ) {
            $response = array(
                'status' => false,
                'message' => sprintf( __('Coupon %s is invalid for your cart items.', 'rpd-restaurant-solution' ), $coupon_code )
            );
        } else if( !empty($coupon_post) && $coupon_post != null && $coupon_post->post_status == 'publish' ) {
            $response = $this->apply_coupon( $coupon_code );
        }

        echo json_encode($response);
        exit();
    }

    public function insertDeliveryPoint( $order )
    {
        $order_id = $order->get_order_number();

        if( get_post_meta( $order_id, 'pickup_point', true ) ) {
            $pickup_point = ucfirst(str_replace( '-', ' ', get_post_meta( $order_id, 'pickup_point', true )));
            
            echo '<p>';
                echo '<strong>';
                    _e('Pickup point:', 'rpd-restaurant-solution');
                echo '</strong> ';
                echo $pickup_point;
            echo '<p>';
        }
    }

    public function insertDeliveryMethod( $order )
    {
        $order_id = $order->get_order_number();

        $delivery_method = '';

        if( get_post_meta( $order_id, 'delivery_method', true ) ) {

            $delivery_method_slug = get_post_meta( $order_id, 'delivery_method', true );

            $delivery_options = get_option('rpd_delivery_methods');

            foreach( $delivery_options as $key => $value ) {
                if( $key == $delivery_method_slug ) {
                    $delivery_method = __( $value['method_name'] , 'rpd-restaurant-solution' );
                }
            }
            
            if( $delivery_method != '' ) {
                echo '<p>';
                    echo '<strong>';
                        _e('Delivery method:', 'rpd-restaurant-solution');
                    echo '</strong> ';
                    echo $delivery_method;
                echo '<p>';
            }
        }
    }

    public function insertErrorBoxHtml()
    {
        if( !is_checkout() ) {
            return;
        }

        echo '<div class="errors-box"><ul></ul></div>';
    }

    public function insertDeliveryDetails( $order )
    {
        $distance = null !== WC()->session->get('delivery_distance') ? WC()->session->get('delivery_distance') : 0;
        $duration = null !== WC()->session->get('delivery_distance') ? WC()->session->get('delivery_duration') : 0;
        $customer_address_lat = null !== WC()->session->get('delivery_distance') ? WC()->session->get('customer_address_lat') : 0;
        $customer_address_lng = null !== WC()->session->get('delivery_distance') ? WC()->session->get('customer_address_lng') : 0;

        if( $distance > 0 ) {
            if( update_post_meta( $order->get_id(), 'delivery_distance', $distance ) ) {
                WC()->session->__unset('delivery_distance');
            }

            if( $duration > 0 ) {
                if( update_post_meta( $order->get_id(), 'delivery_duration', $duration ) ) {
                    WC()->session->__unset('delivery_duration');
                }
            }

            if( $customer_address_lat > 0 ) {
                if( update_post_meta( $order->get_id(), 'customer_address_lat', $customer_address_lat ) ) {
                    WC()->session->__unset('customer_address_lat');
                }
            }
            
            if( $customer_address_lat > 0 ) {
                if( update_post_meta( $order->get_id(), 'customer_address_lng', $customer_address_lng ) ) {
                    WC()->session->__unset('customer_address_lng');
                }
            }
        }
    }

    public function changeDeliveryTime()
    {
        $delivery_time = $this->getDeliveryTime();

        if( $delivery_time ) {
            if( isset($_POST['delivery_method']) && $_POST['delivery_method'] == 'ridicare-personala' && isset($_POST['delivery_hour_on']) && $_POST['delivery_hour_on'] == 'now' ) {
                $current_hour = date('H:i', strtotime( (string)'+60 minutes', strtotime(current_time('H:i'))));
                $_POST['delivery_hour'] = date('H:i', strtotime( (string)'+'.$delivery_time.' minutes', strtotime($current_hour)));
            }
        }
    }
    
    public function hidePickupPointField()
    {
        // Only on checkout page
        if( ! ( is_checkout() && ! is_wc_endpoint_url() ) ) return;

        $pickup_method = 'ridicare-personala';
        ?>
        <script>
            jQuery(function($){
                // Choosen shipping method selectors slug
                var shipMethod = 'select[name^="delivery_method"]';

                // Function that shows or hide imput select fields
                function showHide( actionToDo='show', selector='' ){
                    if( actionToDo == 'show' )
                        $(selector).show( 200, function(){
                            $(this).addClass("validate-required");
                        });
                    else
                        $(selector).hide( 200, function(){
                            $(this).removeClass("validate-required");
                        });
                    $(selector).removeClass("woocommerce-validated");
                    $(selector).removeClass("woocommerce-invalid woocommerce-invalid-required-field");
                }

                if( $(shipMethod).val() != '<?php echo $pickup_method; ?>' ) {
                    showHide('hide','#pickup_point_field' );
                } else if( $(shipMethod).val() == undefined ) {
                    shipMethod = 'input[name^="delivery_method"]:checked';
                    if( $(shipMethod).val() != '<?php echo $pickup_method; ?>' ) {
                        showHide('hide','#pickup_point_field' );
                    }
                }
                    
                // Live event (When shipping method is changed)
                
                $( 'form.checkout' ).on( 'change', shipMethod, function() {
                    if( $(shipMethod).val() != '<?php echo $pickup_method; ?>' ) {
                        showHide('hide','#pickup_point_field');
                    } else {                
                        showHide('show','#pickup_point_field');
                    }
                });
            });
        </script>
        <?php
    }

    public function removeDeliveryPoint()
    {
        if( $this->activated('ignore_pickup_points') ) {
            return;
        }
        
        if( isset($_POST['delivery_method']) && $_POST['delivery_method'] != 'ridicare-personala' ) {
            if( isset($_POST['pickup_point']) ) {
                $_POST['pickup_point'] = '';
            }
        }
    }

    public function changeAddressFieldStructure()
    {
        if( $this->isActiveCheckoutV2() ) {
            return;
        }

        if( isset( $_POST['delivery_method'] ) && $_POST['delivery_method'] != 'livrare-la-domiciliu' ) {
            $_POST['billing_address_1'] = '-';
            if( isset( $_POST['billing_city'] ) ) {
                $_POST['billing_city'] = '-';
            }
            if( isset( $_POST['areas'] ) ) {
                $_POST['areas'] = '-';
            }
        }
    }

    public function insertDeliveryHourDetails( $order )
    {
        $order_id = $order->get_id();

        $is_asap = false;

        if( get_post_meta( $order_id, 'delivery_hour_on', true ) == 'now' ) {
            $is_asap = true;
        }

        if( $is_asap ) {
            _e( '<p>' );
                _e( 'Delivery date and time: ', 'rpd-restaurant-solution' );
                _e( 'As soon as posible', 'rpd-restaurant-solution' );
            _e( '</p>' );
        } else if( get_post_meta( $order_id, 'delivery_time', true ) && get_post_meta( $order_id, 'delivery_hour', true ) ) {
            _e( '<p>' );
                _e( 'Delivery date and time: ', 'rpd-restaurant-solution' );
                echo wp_kses_post( get_post_meta( $order_id, 'delivery_time', true ) ) . __( ' around ', 'rpd-restaurant-solution' ) . wp_kses_post( get_post_meta( $order_id, 'delivery_hour', true ) );
            _e( '</p>' );
        } else if( get_post_meta( $order_id, 'delivery_time', true ) ) {
            _e( '<p>' );
                _e( 'Delivery date: ', 'rpd-restaurant-solution' );
                echo wp_kses_post( get_post_meta( $order_id, 'delivery_time', true ) );
            _e( '</p>' );
        } else if( get_post_meta( $order_id, 'delivery_hour', true ) ) {
            _e( '<p>' );
                _e( 'Delivery hour: ', 'rpd-restaurant-solution' );
                echo wp_kses_post( get_post_meta( $order_id, 'delivery_hour', true ) );
            _e( '</p>' );
        }
    }

    public function insertDeliveryHourDetailsThankyouPage( $order )
    {
        $order_id = $order->get_id();

        $is_asap = false;

        if( get_post_meta( $order_id, 'delivery_hour_on', true ) == 'now' ) {
            $is_asap = true;
        }

        if( $is_asap ) {
            _e( '<li class="woocommerce-order-overview__payment-method method">' );
                _e( 'Delivery date and time: ', 'rpd-restaurant-solution' );
                _e( '<strong>' );
                    _e( 'As soon as posible', 'rpd-restaurant-solution' );
                _e( '</strong>' );
            _e( '</li>' );
        } else if( get_post_meta( $order_id, 'delivery_time', true ) && get_post_meta( $order_id, 'delivery_hour', true ) ) {
            _e( '<li class="woocommerce-order-overview__payment-method method">' );
                _e( 'Delivery date and time: ', 'rpd-restaurant-solution' );
                _e( '<strong>' );
                    echo wp_kses_post( get_post_meta( $order_id, 'delivery_time', true ) ) . __( ' around ', 'rpd-restaurant-solution' ) . wp_kses_post( get_post_meta( $order_id, 'delivery_hour', true ) );
                _e( '</strong>' );
            _e( '</li>' );
        } else if( get_post_meta( $order_id, 'delivery_time', true ) ) {
            _e( '<li class="woocommerce-order-overview__payment-method method">' );
                _e( 'Delivery date: ', 'rpd-restaurant-solution' );
                _e( '<strong>' );
                    echo wp_kses_post( get_post_meta( $order_id, 'delivery_time', true ) );
                _e( '</strong>' );
            _e( '</li>' );
        } else if( get_post_meta( $order_id, 'delivery_hour', true ) ) {
            _e( '<li class="woocommerce-order-overview__payment-method method">' );
                _e( 'Delivery hour: ', 'rpd-restaurant-solution' );
                _e( '<strong>' );
                    echo wp_kses_post( get_post_meta( $order_id, 'delivery_hour', true ) );
                _e( '</strong>' );
            _e( '</li>' );
        }
    }
	
	public function insertMiniCartValidationScript()
	{ ?>
		<script>
			jQuery( document.body ).on( 'removed_from_cart', function( response, cart_hash ) {
				if( cart_hash["div.cart-contents"] != undefined && cart_hash["div.cart-contents"] != null ) {
					var productCounter = cart_hash["div.cart-contents"].match(/(\d+)/)[0];

					if( productCounter != undefined && productCounter != null ) {
						if( parseInt( productCounter ) < 1 ) {
							jQuery('.mini-cart-header').toggleClass('active');
							jQuery('body').toggleClass('mini-cart-active');

							window.location.href = window.location.href;
						}
					}
				}
			} );
		</script>
	<?php }

    public function validateCategoriesHours( $data, $errors )
    {
        // Default category ID
        $parentCategoryId = 0;
        
        // Set and get current time
        $date = new DateTime();
        $date->setTimezone(new DateTimeZone('Europe/Bucharest'));
        $currentTime = $date->format('H:i');

        // Get current category ID from cart
        if( ! WC()->cart->is_empty() ){
            foreach ( WC()->cart->get_cart() as $cart_item ) {
                $productId = $cart_item['product_id'];
                $currentProductCategories = get_the_terms( $productId, 'product_cat' );
                $parentCategoryId = $currentProductCategories[0]->parent;
            }
        }
        
        if( get_term_meta( $parentCategoryId , 'category_delivery_time', true ) == '2' && get_term_meta( $parentCategoryId , 'start_hour', true ) && get_term_meta( $parentCategoryId , 'end_hour', true ) ) {
            $startTime = get_term_meta( $parentCategoryId , 'start_hour', true );
            $endTime = get_term_meta( $parentCategoryId , 'end_hour', true );

            if( strtotime($currentTime) < strtotime($startTime) || strtotime($currentTime) > strtotime($endTime) ) {
                // Remove all validation errors
                foreach( $errors->get_error_codes() as $code ) {
                    $errors->remove( $code );
                }

                $errors->add( 'terms-email', __('Orders cannot be taken at this time.', 'rpd-restaurant-solution') );
            }
        }

    }

    public function onlineOrderPreset()
    {
        if( !is_checkout() || is_user_logged_in() ) {
            return;
        }

        echo '
            <script>
                function getCookie(cname) {
                    let name = cname + "=";
                    let decodedCookie = decodeURIComponent(document.cookie);
                    let ca = decodedCookie.split(";");
                    for(let i = 0; i <ca.length; i++) {
                    let c = ca[i];
                    while (c.charAt(0) == " ") {
                        c = c.substring(1);
                    }
                    if (c.indexOf(name) == 0) {
                        return c.substring(name.length, c.length);
                    }
                    }
                    return "";
                }
				function capitalizeFirstLetterOfEachWord( string ) {
					if( string != undefined && string != "" ) {
						var area_cookie = string.split(" ");
						for (var i = 0; i < area_cookie.length; i++) {
							area_cookie[i] = area_cookie[i].charAt(0).toUpperCase() + area_cookie[i].slice(1);
						}
						
						return area_cookie.join(" ");
					}
					
					return "";	
				}
                jQuery( document ).ready( function() {
                    setTimeout( function() {
                        if( getCookie( "online_order_delivery_method" ) != "" ) {
                            var delivery_method = getCookie( "online_order_delivery_method" );
                            if( delivery_method == "livrare-la-domiciliu" ) {
                                jQuery( "input[value=livrare-la-domiciliu]" ).parent().trigger("click");
                            } else if( delivery_method == "ridicare-personala" ) {
                                jQuery( "input[value=ridicare-personala]" ).parent().trigger("click");
                            } else if( delivery_method == "servire-la-restaurant" ) {
                                jQuery( "input[value=servire-la-restaurant]" ).parent().trigger("click");
                            }
                        }

                        if( getCookie( "online_order_phone" ) != "" ) {
                            var phone = getCookie( "online_order_phone" );
                            if( phone != undefined && phone != "" ) {
                                var replacedPhone = phone.replace(/[+]/g,""); 
                                jQuery( "#billing_phone" ).val( replacedPhone );
                            }
                        }

                        if( getCookie( "online_order_firstname" ) != "" ) {
                            var billing_first_name = getCookie( "online_order_firstname" );
                            if( billing_first_name != undefined && billing_first_name != "" ) {
								var replacedFirstName = billing_first_name.replace(/[+]/g," "); 
								replacedFirstName = capitalizeFirstLetterOfEachWord( replacedFirstName );
								jQuery( "#billing_first_name" ).val( replacedFirstName );
							}
                        }

                        if( getCookie( "online_order_lastname" ) != "" ) {
                            var online_order_lastname = getCookie( "online_order_lastname" );
                            if( online_order_lastname != undefined && online_order_lastname != "" ) {
								var replacedLastName = online_order_lastname.replace(/[+]/g," "); 
								replacedLastName = capitalizeFirstLetterOfEachWord( replacedLastName );
								jQuery( "#billing_last_name" ).val( replacedLastName );
							}
                        }

                        if( getCookie( "online_order_email" ) != "" ) {
                            var email = getCookie( "online_order_email" );
                            if( email != undefined && email != "" ) {
                                var replacedEmail = email.replace(/[+]/g,""); 
                                jQuery( "#billing_email" ).val( replacedEmail );
                            }
                        }

                        if( getCookie( "online_order_city" ) != "" && getCookie( "online_order_city" ) != undefined ) {
							var city = getCookie( "online_order_city" ).toLowerCase();
                            jQuery( "#billing_city" ).val( city );
                        }

                        if( getCookie( "online_order_area" ) != "" ) {
							var area = capitalizeFirstLetterOfEachWord( getCookie( "online_order_area" ) );
                            jQuery( "select[name=areas]" ).val( area );
                        }

                        if( getCookie( "online_order_address" ) != "" ) {
							var address = getCookie( "online_order_address" );
							if( address != undefined && address != "" ) {
								var replacedAddress = address.replace(/[+]/g," "); 
								replacedAddress = capitalizeFirstLetterOfEachWord( replacedAddress );
								jQuery( "#billing_address_1" ).val( replacedAddress );
							}
                        }

                        if( getCookie( "online_order_building" ) != "" ) {
                            var building = getCookie( "online_order_building" );
							if( building != undefined && building != "" ) {
								var replacedBuilding = building.replace(/[+]/g," "); 
								replacedBuilding = capitalizeFirstLetterOfEachWord( replacedBuilding );
								jQuery( "#billing_bloc" ).val( replacedBuilding );
							}
                        }

                        if( getCookie( "online_order_flat_nr" ) != "" ) {
                            var flat = getCookie( "online_order_flat_nr" );
							if( flat != undefined && flat != "" ) {
								var replacedFlat = flat.replace(/[+]/g," "); 
								replacedFlat = capitalizeFirstLetterOfEachWord( replacedFlat );
								jQuery( "#billing_apartament" ).val( replacedFlat );
							}
                        }

                        if( getCookie( "online_order_flat_staircase" ) != "" ) {
                            var staircase = getCookie( "online_order_flat_staircase" );
							if( staircase != undefined && staircase != "" ) {
								var replacedStaircase = staircase.replace(/[+]/g," "); 
								replacedStaircase = capitalizeFirstLetterOfEachWord( replacedStaircase );
								jQuery( "#billing_scara" ).val( replacedStaircase );
							}
                        }

                        if( getCookie( "online_order_flat_floor" ) != "" ) {
                            var floor = getCookie( "online_order_flat_floor" );
							if( floor != undefined && floor != "" ) {
								var replacedFloor = floor.replace(/[+]/g," "); 
								replacedFloor = capitalizeFirstLetterOfEachWord( replacedFloor );
								jQuery( "#billing_etaj" ).val( replacedFloor );
							}
                        }

                        if( getCookie( "online_order_delivery_date" ) != "" ) {
							jQuery( "#delivery_hour_on_choose_date" ).trigger( "click" );
                            jQuery( "#delivery_time" ).val( getCookie( "online_order_delivery_date" ) );
                        }

                        if( getCookie( "online_order_delivery_hour" ) != "" ) {
							jQuery( "#delivery_hour_on_choose_date" ).trigger( "click" );
                            jQuery( "#delivery_hour" ).val( getCookie( "online_order_delivery_hour" ) );
                        }

                    }, 500 )
                } );
            </script>
        ';
    }

    public function makeEmailAddressOptional( $fields )
    {
        if( !is_checkout() || is_user_logged_in() ) {
            return $fields;
        }

        if( isset($_COOKIE['online_order_delivery_method']) && ( !isset($_COOKIE['online_order_email']) || $_COOKIE['online_order_email'] == '' ) ) {
            $fields['billing_email']['required'] = false;
        }

        return $fields;
    }

    public function removeOnlineOrderCookies()
    {
        if ( empty( is_wc_endpoint_url('order-received') ) ) {
            return;
        }

        $this->removeCookie('online_order_delivery_method');
        $this->removeCookie('online_order_phone');
        $this->removeCookie('online_order_firstname');
        $this->removeCookie('online_order_lastname');
        $this->removeCookie('online_order_email');
        $this->removeCookie('online_order_city');
        $this->removeCookie('online_order_area');
        $this->removeCookie('online_order_address');
        $this->removeCookie('online_order_building');
        $this->removeCookie('online_order_flat_nr');
        $this->removeCookie('online_order_flat_staircase');
        $this->removeCookie('online_order_flat_floor');
        $this->removeCookie('online_order_delivery_date');
        $this->removeCookie('online_order_delivery_hour');
    }

    public function filterWoocommerceRestTagExclude( $args, $request )
    {
        if( $this->getPluginOptions()[ 'exclude_tags_ids' ] && $this->getPluginOptions()[ 'exclude_tags_ids' ] != '' ) {
            $tags_ids = explode(',', str_replace( ' ', '', $this->getPluginOptions()[ 'exclude_tags_ids' ]));
            
            if( !empty( $tags_ids ) ) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'product_tag',
                        'terms' => $tags_ids,
                        'field' => 'term_id',
                        'operator' => 'NOT IN'
                    ),
                );
            }
        }
                
        return $args;
    }

    public function forceAppUpdate()
    {
        if( !$this->activated( 'force_app_update' ) ) {
            return;
        }

        $android_link = $ios_link = '';

        if( get_option('options_google_play_app') ) {
            $android_link = get_option('options_google_play_app');
        }

        if( get_option('options_appstore_app') ) {
            $ios_link = get_option('options_appstore_app');
        }

        if( !isset( $_GET[ 'isNewDesign' ] ) ) {

            echo '<!DOCTYPE html><html><head><title></title><meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1"><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800&display=swap" rel="stylesheet"></head><body>';

                echo '<a href="#" onclick="(function(){ if(window.webkit){ window.webkit.messageHandlers.cordova_iab.postMessage(JSON.stringify({\'my_message\' : \'back\'}));}})();" style="position: absolute; right: 15px; top: 15px; background: #F7F7F7; border-radius: 50px; padding: 3px 5px;"><img style="position: relative; top: 2px;" src="' . $this->plugin_url . 'assets/images/close-button.png"></a>';

                echo '<div style="margin: 0;position: absolute; top: 50%; left: 0; -ms-transform: translateY(-50%); transform: translateY(-50%);">';

                    echo '<p style="text-align: center;">';
                        echo '<img src="' . $this->plugin_url . 'assets/images/update.png" alt="update" />';
                    echo '</p>';
                
                    echo '<p style="text-align: center; font-size: 20px; line-height: 26px; padding: 0 15px 0; margin: 0 0 30px; font-family: Nunito; color: #333333;">';
                        _e( 'Sorry, but you are using an old version of the application which is no longer supported. In order to complete the order you must update to the new version.', 'rpd-restaurant-solution' );
                    echo '</p>';

                    if( $android_link != '' ) {
                        echo '<p style="text-align: center; padding: 0 15px; margin-bottom: 20px;">';
                            echo '<a href="' . $android_link . '" style="background: #29D687; border-radius: 10px; width: 100%; color: #fff; text-decoration: none; display: block; font-family: Nunito; font-weight: 800;font-size: 16px; line-height: 22px; padding: 8px 0 12px 0;"><img style="position: relative; top: 5px; right: 10px;" src="' . $this->plugin_url . 'assets/images/android.png" alt="android" />Update Android</a>';
                        echo '</p>';
                    }

                    if( $ios_link != '' ) {
                        echo '<p style="text-align: center; padding: 0 15px;">';
                            echo '<a href="' . $ios_link . '" style="background: #333333; border-radius: 10px; width: 100%; color: #fff; text-decoration: none; display: block; font-family: Nunito; font-weight: 800; font-size: 16px; line-height: 22px; padding: 8px 0 12px 0;"><img style="position: relative; top: 5px; right: 10px;" src="' . $this->plugin_url . 'assets/images/ios.png" alt="ios" />Update iOS</a>';
                        echo '</p>';
                    }

                echo '</div>';

            echo '</body></html>';

            die();
        }
    }

    public function clearPhoneOrderCookies()
    {
        $this->removeCookie('online_order_delivery_method');
        $this->removeCookie('online_order_phone');
        $this->removeCookie('online_order_firstname');
        $this->removeCookie('online_order_lastname');
        $this->removeCookie('online_order_email');
        $this->removeCookie('online_order_city');
        $this->removeCookie('online_order_area');
        $this->removeCookie('online_order_address');
        $this->removeCookie('online_order_building');
        $this->removeCookie('online_order_flat_nr');
        $this->removeCookie('online_order_flat_staircase');
        $this->removeCookie('online_order_flat_floor');
        $this->removeCookie('online_order_delivery_date');
        $this->removeCookie('online_order_delivery_hour');
    }

    public function couponsForAuthenticatedUsersOnly( $is_valid, $coupon )
    {   
        if( !$this->getPluginOptions()['coupons_for_authenticated_users_only'] || empty($this->getPluginOptions()['coupons_for_authenticated_users_only']) ) {
            return $is_valid;    
        }

        $coupons_codes = explode( ',', $this->getPluginOptions()['coupons_for_authenticated_users_only'] );

        if ( in_array( $coupon->get_code() , $coupons_codes) && ! is_user_logged_in() ) {
            return false;
        }
    
        return $is_valid;
    }

    public function insertSubtotalProductsPrice()
    {
        if( !$this->isAnyDiscountActive() ) {
            return;
        }

        if( WC()->session->get('override_shipping_price') == NULL || (float) WC()->session->get('override_shipping_price') == 0 ) {
            return;
        }

        $products_total = (float) WC()->cart->total;

        $products_total = $products_total - (float) WC()->session->get('override_shipping_price');

        echo '<tr class="fee">';
            echo '<th>' . __( 'Products total', 'rpd-restaurant-solution' ) . '</th>';
            echo '<td><span class="woocommerce-Price-amount amount">' . wc_price( $products_total ) . '</span></td>';
        echo '</tr>';
    }

    public function insertSubtotalProductsPriceThankyouPage( $order )
    {
        if( !$this->isAnyDiscountActive() ) {
            return;
        }
        
        if( $order->get_shipping_total() == 0 ) {
            return;
        }

        $products_total = (float) $order->get_total();
        $products_total = $products_total - (float) $order->get_shipping_total();

        echo '<tr class="fee">';
            echo '<th>' . __( 'Products total', 'rpd-restaurant-solution' ) . '</th>';
            echo '<td><span class="woocommerce-Price-amount amount">' . wc_price( $products_total ) . '</span></td>';
        echo '</tr>';
    }

    public function insertSubtotalProductsPriceEmailOrderDetails( $order )
    {
        if( !$this->isAnyDiscountActive() ) {
            return;
        }

        if( $order->get_shipping_total() == 0 ) {
            return;
        }

        $products_total = (float) $order->get_total();
        $products_total = $products_total - (float) $order->get_shipping_total();

        echo '<tr class="fee">';
            echo '<th class="td" scope="row" colspan="2" style="text-align: left;">' . __( 'Products total', 'rpd-restaurant-solution' ) . '</th>';
            echo '<td class="td" style="text-align: left;">' . wc_price( $products_total ) . '</td>';
        echo '</tr>';
    }
	
    public function applyNewCustomerCoupon()
    {			
		if( !$this->activated( 'auto_apply_coupon_for_new_customers' ) ) {
            return;
        }

        if( !$this->getPluginOptions()['coupon_code_for_new_customers'] || $this->getPluginOptions()['coupon_code_for_new_customers'] == '' ) {
            return;
        }

        if( !$this->getPluginOptions()['number_of_orders_for_new_users_discount'] || $this->getPluginOptions()['number_of_orders_for_new_users_discount'] == '' ) {
            return;
        }

        if( !is_user_logged_in() ) {
            return;
        }
		
        if( $this->activated('auto_apply_coupon_for_new_customers_app_mobile_only') ) {
            if( !WC()->session->get('showNavbar') ) {
                return;
            }
        }

		global $woocommerce;
        
        $coupon_code = $this->getPluginOptions()['coupon_code_for_new_customers'];
        
		$coupon = new WC_Coupon( $coupon_code );
		$discounts = new WC_Discounts( WC()->cart );
		$is_valid = $discounts->is_coupon_valid( $coupon );
		
		if( !$is_valid ) {
			return;
		}
		
		$max_number_of_orders = (int) $this->getPluginOptions()['number_of_orders_for_new_users_discount'];
        $user_id = get_current_user_id();
		
        $customer_orders = get_posts(
            array(
                'numberposts' => -1,
                'meta_key' => '_customer_user',
                'orderby' => 'date',
                'order' => 'DESC',
                'meta_value' => $user_id,
                'post_type' => 'shop_order',
                'post_status' => array('wc-processing', 'wc-pending', 'wc-completed')
            )
        );

        if( count($customer_orders) >= $max_number_of_orders ) {
            return;
        }

        try{
            $woocommerce->cart->add_discount( sanitize_text_field( $coupon_code ));
        } catch (\Exception $ex) {}
    }

    public function customShippingCosts( $rates, $package )
    {
        if ( isset($_POST['post_data']) ) {
            parse_str($_POST['post_data'], $post_data);
        } else {
            $post_data = $_POST;
        }
        
        $new_cost = 0;
        
        if( $post_data['delivery_method'] != 'livrare-la-domiciliu' ) {
            foreach( $rates as $rate_key => $rate ){
                $rates[$rate_key]->cost = $new_cost;
            }
        }

        return $rates;
    }

}