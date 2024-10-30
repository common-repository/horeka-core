<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Base;

use \DateTime;
use \stdClass;
use \SoapClient;
use \DateTimeZone;

class BaseController
{
	public $plugin_path;

	public $plugin_url;

	public $plugin_basename;
    
    public $plugin;

	public $plugin_front_templates_path;

	public $plugin_scripts_version;

	public $plugin_options;

	public $plugin_distance_options;

	public $plugin_delivery_methods;

    protected $woocommerce_netopiapayments_settings;

	public $checkbox_options_manager = array();

	public $text_options_manager = array();

	public $textarea_options_manager = array();

	public $social_options_manager = array();

	public $api_options_manager = array();

    public $netopia_options_manager = array();

    const ERR_CODE_OK = 0x00;

	public function __construct() 
	{
		$this->plugin_scripts_version = '1.0.2';

		$this->plugin_path = plugin_dir_path( dirname( __FILE__, 2 ) );
		$this->plugin_url = plugin_dir_url( dirname( __FILE__, 2 ) );
        $this->plugin_basename = plugin_basename( dirname( __FILE__, 3 ) );
		$this->plugin = plugin_basename( dirname( __FILE__, 3 ) ) . '/rpd-restaurant-solution.php';
		$this->plugin_front_templates_path = plugin_dir_path( dirname( __FILE__, 2 ) ) . 'page-templates/';
		$this->plugin_options = get_option( 'rpd_restaurant_solution' );
		$this->plugin_distance_options = get_option( 'rpd_manage_distances' );
        $this->plugin_delivery_methods = get_option( 'rpd_delivery_methods' );
        $this->plugin_delivery_points = get_option( 'rpd_delivery_points' );
        $this->woocommerce_netopiapayments_settings = get_option( 'woocommerce_netopiapayments_settings' );
        
		$this->checkbox_options_manager = array(
			'distance_manager' => esc_html__('Distance Manager', 'rpd-restaurant-solution'),
			'display_areas' => esc_html__('Display Areas', 'rpd-restaurant-solution'),
            'category_discount' => esc_html__('Discount for Categories', 'rpd-restaurant-solution'),
            'tag_discount' => esc_html__('Discount for Tags', 'rpd-restaurant-solution'),
            'only_light_version' => esc_html__('Only Lite Website/Mobile App', 'rpd-restaurant-solution'),
            'custom_checkout' => esc_html__('Redirect to Custom Checkout (Lite version)', 'rpd-restaurant-solution'),
            'checkout_v2' => esc_html__('Checkout V2', 'rpd-restaurant-solution'),
            'only_import_orders' => esc_html__('Only Import Orders', 'rpd-restaurant-solution'),
            'same_parent_category' => esc_html__('Buy from the same parent category only', 'rpd-restaurant-solution'),
            'custom_implementation' => esc_html__('Custom Implementation', 'rpd-restaurant-solution'),
            'crm_products_sync' => esc_html__('CRM Products Sync', 'rpd-restaurant-solution'),
            'force_app_update' => esc_html__('Force App Update', 'rpd-restaurant-solution'),
            'ignore_pickup_points' => esc_html__('Ignore Pickup Points', 'rpd-restaurant-solution'),
            'auto_apply_coupon_for_new_customers' => esc_html__('Auto apply coupon for the new customers', 'rpd-restaurant-solution'),
            'auto_apply_coupon_for_new_customers_app_mobile_only' => esc_html__('Auto apply coupon for the new customers app mobile only', 'rpd-restaurant-solution'),
            'diplay_city_in_checkout' => esc_html__('Hide City in the Checkout Page', 'rpd-restaurant-solution'),
            'display_delivery_time' => esc_html__('Display Delivery Time in the Checkout Page', 'rpd-restaurant-solution'),
            'display_delivery_methods' => esc_html__('Display Delivery Methods in the Checkout Page', 'rpd-restaurant-solution'),
            'display_payment_methods' => esc_html__('Display Payment Methods in the Checkout Page', 'rpd-restaurant-solution'),
            'display_keep_information' => esc_html__('Display Save User Details in the Checkout Page', 'rpd-restaurant-solution'),
            'display_terms_and_conditions' => esc_html__('Display Terms & Conditions Checkbox in the Checkout Page', 'rpd-restaurant-solution'),
            'company_discount' => esc_html__('Discount for company domains', 'rpd-restaurant-solution'),
            'company_discount_app_mobile_only' => esc_html__('Discount for company domains app mobile only', 'rpd-restaurant-solution'),
			'display_delivery_points' => esc_html__('Display Pickup Points', 'rpd-restaurant-solution'),
            'custom_pickup_points' => esc_html__('Custom Pickup Points', 'rpd-restaurant-solution'),
            'custom_delivery_and_payment' => esc_html__('Custom delivery and payment (Ciresca Oradea)', 'rpd-restaurant-solution'),
		);

		$this->text_options_manager = array(
			'minim_amount_per_order' => esc_html__('Minimum amount per order', 'rpd-restaurant-solution'),
			'online_discount' => esc_html__('Online discount (%)', 'rpd-restaurant-solution'),
            'exclude_tags_ids' => esc_html__('Exclude tags ids from Woo Rest API', 'rpd-restaurant-solution'),
            'coupon_code_for_new_customers' => esc_html__('Coupon code for the new customers', 'rpd-restaurant-solution'),
            'number_of_orders_for_new_users_discount' => esc_html__('Max. number of orders for new users discount', 'rpd-restaurant-solution'),
            'company_discount_coupon_code' => esc_html__('Company discount code ', 'rpd-restaurant-solution'),
		);

		$this->textarea_options_manager = array(
			'top_header_text' => esc_html__('Top header text', 'rpd-restaurant-solution'),
			'reservation_banner_text' => esc_html__('Reservation banner text', 'rpd-restaurant-solution'),
            'cutlery_text' => esc_html__('Cutlery checkout option (text)', 'rpd-restaurant-solution'),
			'gmaps_link' => esc_html__('Google maps link', 'rpd-restaurant-solution'),
            'allowed_links' => esc_html__('Allowed URLs for LITE and Mobile App', 'rpd-restaurant-solution'),
            'checkout_banner' => esc_html__('Checkout banner HTML', 'rpd-restaurant-solution'),
            'coupons_for_authenticated_users_only' => esc_html__('Coupons codes for authenticated users only', 'rpd-restaurant-solution')
		);

		$this->social_options_manager = array(
			'mobile_number' => esc_html__('Mobile', 'rpd-restaurant-solution'),
			'phone_number' => esc_html__('Phone', 'rpd-restaurant-solution'),
			'email_address' => esc_html__('E-mail', 'rpd-restaurant-solution'),
			'facebook_url' => esc_html__('Facebook URL', 'rpd-restaurant-solution'),
			'instagram_url' => esc_html__('Instagram URL', 'rpd-restaurant-solution'),
			'google_maps_api_key' => esc_html__('Google Maps Api Key', 'rpd-restaurant-solution')
		);

		$this->api_options_manager = array(
			'api_url' => esc_html__('API Url', 'rpd-restaurant-solution'),
			'api_key' => esc_html__('API Key', 'rpd-restaurant-solution'),
            'reference_id_prefix' => esc_html__('Reference ID Prefix', 'rpd-restaurant-solution')
		);

        $this->netopia_options_manager = array(
			'netopia_username' => esc_html__('Username', 'rpd-restaurant-solution'),
			'netopia_password' => esc_html__('Password', 'rpd-restaurant-solution')
		);

	}
	
	protected function roundHourToQuarter( $hour )
    {
        $currentHourArray = explode(':', $hour);
        $currentHour = (int)$currentHourArray[0];
        $currentMinute = (int)$currentHourArray[1];
    
        if( $currentHour >= 0 && $currentMinute < 15 ){
            $currentMinute = ':15';
        }else if( $currentMinute >= 15 && $currentMinute < 30 ){
            $currentMinute = ':30';
        }else if( $currentMinute >= 30 && $currentMinute < 45 ){
            $currentMinute = ':45';
        } else {
            $currentHour = $currentHour + 1;
            $currentMinute = ':00';
        }
    
        return $currentHour . $currentMinute;
    }

    public function getPickupPointDetails( $slug )
    {
        $pickup_points = get_option('rpd_delivery_points');

        if( !$pickup_points || empty( $pickup_points ) ) {
            return;
        }

        foreach( $pickup_points as $key => $value ) {
            if( $slug == $key ) {
                return $value;
            }
        }
    }

	public function getPluginOptions()
	{
		return $this->plugin_options;
	}

    public function getCheckboxOptions()
	{
		return $this->checkbox_options_manager;
	}

	public function activated( string $key )
	{
		$option = get_option( 'rpd_restaurant_solution' );

        return isset($option[ $key ]) ? $option[ $key ] : false;
	}

	public function convertDateToUTC( $date, $addCurentDate = null )
    {
        $tz_from = 'Europe/Bucharest';
        $format = 'Y-m-d\TH:i:s';
        $tz_to = 'UTC';
        $deliveryDateUTC = '';

        if( isset($addCurrentDate) && $addCurrentDate ) {
            $date = date('Y-m-d') . ' ' . $date . ':00';
        }
        
        $newDeliveryDate = new DateTime($date, new DateTimeZone($tz_from));
        $newDeliveryDate->setTimeZone(new DateTimeZone($tz_to));
        $deliveryDateUTC = $newDeliveryDate->format($format);

        return $deliveryDateUTC;
	}

    protected function getNetopiaTransactionStatus( $merchant_id, $order_id )
    {
        $soap_url = '';
        $response = false;
        $log_data = array();
        $log_meta = array();
        $account = new stdClass();
        $req = new stdClass();
        $order = new stdClass();

        $log_data['error_title'] = 'getNetopiaTransactionStatus';
        $log_data['error_content'] = '';
        $log_meta['customer_ip'] = ( !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '' );
        $log_meta['user_id'] = ( get_current_user_id() ? get_current_user_id() : "" );

        $plugin_settings = $this->getPluginOptions();
        $netopia_settings = $this->getWoocommerceNetopiapaymentsSettings();

        if( $netopia_settings['enabled'] !== 'yes' ) {
            return false;
        }

        if( $netopia_settings['account_id'] === '' ) {
            return false;
        }

        if( !$plugin_settings['netopia_username'] || $plugin_settings['netopia_username'] === '' || !$plugin_settings['netopia_password'] || $plugin_settings['netopia_password'] === '' ){
            return false;
        }

        if( get_post_meta( $order_id, 'netopia_environment', true ) === 'sandbox' ) {
            return false;
        }

        $sac_id = $netopia_settings['account_id'];

        if( $netopia_settings['environment'] === 'yes' ) {
            $soap_url = 'https://sandboxsecure.mobilpay.ro/api/payment2/?wsdl';
        } else {
            $soap_url = 'https://secure.mobilpay.ro/api/payment2/?wsdl';
        }

        $soap = new SoapClient($soap_url, Array('cache_wsdl' => WSDL_CACHE_NONE));
        
        $account->id = $sac_id;
        $account->user_name = $plugin_settings['netopia_username']; 
        $order->merchant_id = $merchant_id; //your orderId. 

        $password = md5($plugin_settings['netopia_password']);
        $account->hash = strtoupper(sha1(strtoupper($password) . "{$order->merchant_id}"));

        $req->account = $account;
        $req->merchant_id = $order->merchant_id;

        try {
            $response = $soap->getInfo(Array('request' => $req));
            if (isset($response->errors) && $response->errors->code != self::ERR_CODE_OK) {
                $log_meta['status'] = $response->code . '-' . $response->message;
                $response = false;
            }
        } catch(SoapFault $e) {
            $log_meta['status'] = $e->faultstring . '-' . $e->faultcode;
            $response = false;
        }

        if( !$response ) {
            ErrorLog::log($log_data, $log_meta);
        }

        return $response;
    }

    public function isValidTransaction( $merchant_id, $order_id )
    {
        if( $this->getNetopiaTransactionStatus( $merchant_id, $order_id ) ) {
            $netopia_result = $this->getNetopiaTransactionStatus( $merchant_id, $order_id );
            if( isset($netopia_result->getInfoResult) && isset($netopia_result->getInfoResult->status) ) {
                $transaction_status = $netopia_result->getInfoResult->status;
                if( $transaction_status === 3 ) { // PAID
                    return true;
                }
            }
        }

        return false;
    }
	
	public function generateOrderData( $order_id ) 
	{

		$data = array();
        $orderDiscount =  0;
        $channel = 1;
        $sendToPos = true;
        $billingArea = $deliveryDistance = $deliveryDuration = $deliveryLongitude = $deliveryLatitude = $deliveryPoint = '';
        $couponsList = $customerNote = $delivery_address = $delivery_city = $paymentMethod = '';
		$order = wc_get_order( $order_id );
        $couponsCount = count( $order->get_used_coupons() );

		$items = $order->get_items();
        $deliveryMethod = get_post_meta( $order->get_id(), 'delivery_method', true );
        $deliveryDate = get_post_meta( $order->get_id(), 'delivery_time', true );
        
        $deliveryDate = str_replace('/', '-', $deliveryDate);
        
        $deliveryDate = date("m-d-Y", strtotime($deliveryDate));
        
        $deliveryDate = str_replace('-', '/', $deliveryDate);
        
        $deliveryHour = get_post_meta( $order->get_id(), 'delivery_hour', true );
        $deliveryDate = $deliveryDate . ' ' . $deliveryHour;
        $deliveryDateUTC = $this->convertDateToUTC( $deliveryDate );

        $orderSubtotal = $order->get_subtotal();
        $orderTotal = ( get_post_meta( $order->get_id(), '_order_total', true ) ? (float) get_post_meta( $order->get_id(), '_order_total', true ) : 0 );
		
        if( $order->get_shipping_total() && (float) $order->get_shipping_total() > 0 ) {
			$orderSubtotal = (float) $orderSubtotal + (float) $order->get_shipping_total();
		}
		
        if( $orderSubtotal > $orderTotal ) {
            $orderDiscount = (float) $orderSubtotal - (float) $orderTotal;
        }

        foreach( $order->get_used_coupons() as $coupon) {
            $couponsList .=  $coupon;
            if( $i < $couponsCount )
                $couponsList .= ', ';
            $i++;
        }

        if( $couponsList != '' ) {
            $couponsList = substr($couponsList, 0, -2);
        }

        switch( $deliveryMethod ){
            case "ridicare-personala":
                $deliveryMethodId = 1;
                break;
            
            case "livrare-la-domiciliu":
                $deliveryMethodId = 2;
                break;
            
            case "servire-la-restaurant":
                $deliveryMethodId = 3;
                break;

            default:
                $deliveryMethodId = 0;
        }

        if( $deliveryMethodId == 0 && $this->activated('custom_delivery_and_payment') ) {
            if (strpos($order->get_shipping_method(), 'Livrare') !== false) {
                $deliveryMethodId = 2;
            } else if( strpos($order->get_shipping_method(), 'Ridicare') !== false ) {
                $deliveryMethodId = 1;
            }
        }
        
        $products = array();
        $counter = 0;
        $addons = [];

        foreach ( $items as $item ) {
            $extra = '';
            $addons_price = 0;
            $item_meta_data = $item->get_formatted_meta_data( '_', true );

            $product = $item->get_product();

            $product_addons = array_filter( (array) $product->get_meta( '_product_addons' ) );

            if( !empty($item_meta_data) ){

                foreach($item_meta_data as $meta) {
                    $addon_key = $meta ->key;
                    preg_match('/(.*)\s\(/', $addon_key, $output_array);
                    if(isset($output_array[1]) && !empty($product_addons)) {

                        $addon = $output_array[1];
                        $option_value = $meta ->value;

                        $addon_index = array_search($addon, array_column($product_addons, 'name'));
                        if($addon_index !== false) {

                            $option_index = array_search($option_value, array_column($product_addons[$addon_index]['options'], 'label'));
                            $addons[] = $product_addons[$addon_index]['options'][$option_index]['id'];

                        }
                    }
                    
                    if( $this->isActiveCheckoutV2() || $this->activated('custom_implementation') ) {
                        if( $product->is_type('variation') ) {
                            $product_variations = array();

                            $variation_attributes = $product->get_variation_attributes();
                            
                            foreach($variation_attributes as $attribute_taxonomy => $term_slug ) {
                                $taxonomy = str_replace('attribute_', '', $attribute_taxonomy );
                                if( taxonomy_exists($taxonomy) ) {
                                    $product_variations[] = strtolower(get_term_by( 'slug', $term_slug, $taxonomy )->name);
                                } else {
                                    $product_variations[] = strtolower($term_slug);
                                }
                            }

                            if( $product_variations ) {
                                if( !in_array( strtolower($meta->value), $product_variations ) ) {
                                    $extra .= ' + ' . ucfirst($meta->value);
                                }
                            }

                        } else {
                            $addon_key = $meta->key;
                            preg_match('/(.*)\s\(/', $addon_key, $output_array);

                            if( empty($output_array) && !empty($product_addons) ) {
                                $extra .= ' + ' . ucfirst($meta->value) . ' @#$ 0';
                            } else {
                                $addon = $output_array[1];
                                $option_value = $meta ->value;

                                $addon_index = array_search($addon, array_column($product_addons, 'name'));
                                if($addon_index !== false) {
                                    $option_index = array_search($option_value, array_column($product_addons[$addon_index]['options'], 'label'));
                                    $extra .= ' + ' . ucfirst($meta->value) . ' @#$ ' . $product_addons[$addon_index]['options'][$option_index]['price'];
                                    $addons_price += (float) $product_addons[$addon_index]['options'][$option_index]['price'];
                                }
                            }
                        }
                    } else {
                        $addon_key = $meta->key;
                        preg_match('/(.*)\s\(/', $addon_key, $output_array);

                        if( empty($output_array) && !empty($product_addons) ) {
                            $extra .= ' + ' . ucfirst($meta->value) . ' @#$ 0';
                        } else {
                            $addon = $output_array[1];
                            $option_value = $meta ->value;

                            $addon_index = array_search($addon, array_column($product_addons, 'name'));
                            if($addon_index !== false) {
                                $option_index = array_search($option_value, array_column($product_addons[$addon_index]['options'], 'label'));
                                $extra .= ' + ' . ucfirst($meta->value) . ' @#$ ' . $product_addons[$addon_index]['options'][$option_index]['price'];
                                $addons_price += (float) $product_addons[$addon_index]['options'][$option_index]['price'];
                            }
                        }
                    }
                }
            }

            if( $extra != '' ) {
                if (substr($extra, 0, 3) === ' + ') {
                    $extra = substr($extra, 3);
                }
            }

            $products[$counter]['id'] = $item->get_product_id();
            $products[$counter]['product'] = $item->get_name() . ( $extra != '' ? ' ## ' . $extra : '' );
            $products[$counter]['addons'] = $addons;
            $products[$counter]['qty'] = $item->get_quantity();
            $products[$counter]['price'] = (float) $item->get_subtotal() - (float)$item->get_quantity() * (float) $addons_price;
            $counter++;
        }

        $customerNote = $order->get_customer_note();
        
        if( get_post_meta( $order->get_id(), 'pickup_point', true ) ) {
			$deliveryPoint = ucfirst(str_replace('-', ' ', get_post_meta( $order->get_id(), 'pickup_point', true )));
            if( $customerNote != '' ) {
                $customerNote = $customerNote . '<br /> ' . __( 'Pickup point:', 'rpd-restaurant-solution' ) . $deliveryPoint;
            } else {
                $customerNote = __( 'Pickup point: ', 'rpd-restaurant-solution' ) . $deliveryPoint;
            }
        }

        if( get_post_meta( $order->get_id(), 'billing_pickup_locations', true ) ) {
			$locationPoint = ucfirst(str_replace('_', ' ', get_post_meta( $order->get_id(), 'billing_pickup_locations', true )));
            if( $customerNote != '' ) {
                $customerNote = $customerNote . '<br /> ' . __( 'Location point: ', 'rpd-restaurant-solution' ) . $locationPoint;
            } else {
                $customerNote = __( 'Location point: ', 'rpd-restaurant-solution' ) . $locationPoint;
            }
        }

        if( get_post_meta( $order->get_id(), 'wants_cutlery', true ) == '1' ) {
            if( $customerNote != '' ) {
                $customerNote = $customerNote . '<br />' . $this->getPluginOptions()['cutlery_text'];
            }else {
                $customerNote = $this->getPluginOptions()['cutlery_text'];
            }            
        }

        if( get_post_meta( $order->get_id(), '_billing_address_1', true ) ) {
            $delivery_address = get_post_meta( $order->get_id(), '_billing_address_1', true );
        }

        if( get_post_meta( $order->get_id(), '_billing_address_2', true ) ) {
            $delivery_address = $delivery_address . ' ' . get_post_meta( $order->get_id(), '_billing_address_2', true );
        }

        if( get_post_meta( $order->get_id(), '_billing_bloc', true ) ) {
            $delivery_address .= __(' Building nr.:', 'rpd-restaurant-solution') . get_post_meta( $order->get_id(), '_billing_bloc', true );
        }

        if( get_post_meta( $order->get_id(), '_billing_apartament', true ) ) {
            $delivery_address .= __(' Flat nr.:', 'rpd-restaurant-solution') . get_post_meta( $order->get_id(), '_billing_apartament', true );
        }

        if( get_post_meta( $order->get_id(), '_billing_scara', true ) ) {
            $delivery_address .= __(' Scale nr.:', 'rpd-restaurant-solution') . get_post_meta( $order->get_id(), '_billing_scara', true );
        }

        if( get_post_meta( $order->get_id(), '_billing_etaj', true ) ) {
            $delivery_address .= __(' Floor:', 'rpd-restaurant-solution') . get_post_meta( $order->get_id(), '_billing_etaj', true );
        }

        if( get_post_meta( $order->get_id(), '_billing_city', true ) ) {
            $delivery_city = get_post_meta( $order->get_id(), '_billing_city', true );
        }

        if( get_post_meta( $order->get_id(), 'billing_area', true ) ) {
            $billingArea = get_post_meta( $order->get_id(), 'billing_area', true );
        }

        if( get_post_meta( $order->get_id(), 'delivery_distance', true ) ) {
            $deliveryDistance = (float) get_post_meta( $order->get_id(), 'delivery_distance', true );
        }

        if( get_post_meta( $order->get_id(), 'delivery_duration', true ) ) {
            $deliveryDuration = get_post_meta( $order->get_id(), 'delivery_duration', true );
        }

        if( get_post_meta( $order->get_id(), 'customer_address_lat', true ) ) {
            $deliveryLatitude = get_post_meta( $order->get_id(), 'customer_address_lat', true );
        }

        if( get_post_meta( $order->get_id(), 'customer_address_lng', true ) ) {
            $deliveryLongitude = get_post_meta( $order->get_id(), 'customer_address_lng', true );
        }

        if( WC()->session->get('showNavbar') ) {
            $channel = 2;
        } else if( WC()->session->get('phoneOrder') ) {
            $channel = 5;
        }

        if( get_post_meta( $order->get_id(), 'pickup_point', true ) ) {
            $slug = get_post_meta( $order->get_id(), 'pickup_point', true );
            $pickup_point = $this->getPickupPointDetails( $slug );
            if( $pickup_point && $pickup_point["send_to_pos"] == '1' ) {
                $sendToPos = false;
            }
        }

        if( get_post_meta( $order->get_id(), '_payment_method_title', true ) ) {
            $paymentMethod = get_post_meta( $order->get_id(), '_payment_method_title', true );
        }

        if( $paymentMethod != '' && $this->activated('custom_delivery_and_payment') ) {
            if (strpos($paymentMethod, 'Plata cu cardul la livrare') !== false) {
                $paymentMethod = 'Plata cu card la livrare';
            }
        }

        $data['orderId'] = ( !is_null($this->plugin_options['reference_id_prefix']) && $this->plugin_options['reference_id_prefix'] !== '' ? $this->plugin_options['reference_id_prefix'] . '-' : '' ) . $order->get_order_number();
        $data['orderDate'] = $order->get_date_created()->format ('Y-m-d H:i:s');
        $data['deliveryMethod'] = $deliveryMethodId;
        $data['deliveryCost'] = ( get_post_meta( $order->get_id(), '_order_shipping', true ) ? (float) get_post_meta( $order->get_id(), '_order_shipping', true ) : 0 );
        $data['paymentMethod'] = $paymentMethod;
        $data['deliveryDate'] = $deliveryDateUTC;
        $data['deliveryDateFreeText'] = "";
        $data['total'] = $orderTotal;
        $data['deliveryAddress'] = $delivery_address;
        $data['DeliveryCity'] = $delivery_city;
        $data['District'] = $billingArea;
        $data['NumberOfKm'] = $deliveryDistance;
        $data['Duration'] = $deliveryDuration;
        $data['orderNote'] = $customerNote;
        $data['discountCode'] = $couponsList;
        $data['discountValue'] = $orderDiscount;
        $data['Channel'] = $channel;
        $data['SendToPos'] = $sendToPos;
        $data['DeliveryPoint'] = $deliveryPoint;
        $data['customDeliveryDate'] = ( get_post_meta( $order->get_id(), 'delivery_hour_on', true ) === 'choose_date' ? true : false );
        $data['products'] = ( !empty($products) ? $products : "" );
        $data['customer']['phone'] = ( get_post_meta( $order->get_id(), '_billing_phone', true ) ? get_post_meta( $order->get_id(), '_billing_phone', true ) : "" );
        $data['customer']['firstName'] = ( get_post_meta( $order->get_id(), '_billing_first_name', true ) ? get_post_meta( $order->get_id(), '_billing_first_name', true ) : "" );
        $data['customer']['lastName'] = ( get_post_meta( $order->get_id(), '_billing_last_name', true ) ? get_post_meta( $order->get_id(), '_billing_last_name', true ) : "" );
        $data['customer']['email'] = ( get_post_meta( $order->get_id(), '_billing_email', true ) && get_post_meta( $order->get_id(), '_billing_email', true ) != "no-reply@demo.com" ? get_post_meta( $order->get_id(), '_billing_email', true ) : "" );
        $data['customer']['password'] = ( get_post_meta( $order->get_id(), 'currentUserP', true ) ? get_post_meta( $order->get_id(), 'currentUserP', true ) : "" );
        $data['customer']['externaluserid'] = ( get_current_user_id() ? get_current_user_id() : "" );
        $data['customer']['allowSms'] = ( get_post_meta( $order->get_id(), 'gdpr_sms', true ) ? true : false );
        $data['customer']['allowEmail'] = ( get_post_meta( $order->get_id(), 'gdpr_email', true ) ? true : false );
        $data['customer']['Latitude'] = $deliveryLatitude;
		$data['customer']['Longitude'] = $deliveryLongitude;

		return $data;
	}

    public function getWoocommerceNetopiapaymentsSettings()
    {
        return $this->woocommerce_netopiapayments_settings;
    }

    public function getDeliveryMethods()
    {
        return $this->plugin_delivery_methods;
    }

    public function getDeliveryPoints()
    {
        return $this->plugin_delivery_points;
    }

    public function isActiveCheckoutV2()
    {
        return $this->activated('checkout_v2');
    }
    
    public function get_array_depth( $array ) {
        $max_indentation = 1;
    
        $array_str = print_r($array, true);
        $lines = explode("\n", $array_str);
    
        foreach ($lines as $line) {
            $indentation = (strlen($line) - strlen(ltrim($line))) / 4;
    
            if ($indentation > $max_indentation) {
                $max_indentation = $indentation;
            }
        }
    
        return ceil(($max_indentation - 1) / 2) + 1;
    }

    public function calculateTaxonomyDiscount( $taxonomy, $discount_slug )
    {
        if( !is_cart() && !is_checkout() ) {
            return;
        }
        
        $cart_items = WC()->cart->cart_contents;
        $total_discount = $products_number = 0;
        $discount_type = 1; // Default - No discount
        $taxonomy_prices = $result = array();
        
        foreach( $cart_items as $item ) {

            $is_variable = false;
            $curent_variation = '';

            if( $item['data']->is_type( 'variation' ) && !empty($item['data']->get_attributes()) ) {
                $is_variable = true;
                foreach( $item['data']->get_attributes() as $atribute ) {
                    $curent_variation = $atribute;
                }
            }

            $productId = $item['product_id'];
            $product_terms = get_the_terms ( $productId, $taxonomy );
    
            if( is_array( $product_terms ) ){  

                foreach( $product_terms as $term ) {

                    $term_id = $term->term_id;

                    if( isset(get_term_meta($term_id)[$discount_slug]) ) {

                        $discount_type = (int) get_term_meta($term_id)[$discount_slug][0];

                        if( $discount_type > 1 ) {

                            if( $is_variable && $curent_variation != '' ) {

                                for( $i = 0; $i < $item['quantity']; $i++ ){
                                    $taxonomy_prices[$term_id][$curent_variation]['prices'][] = $item['line_total'] / $item['quantity'];
                                    $taxonomy_prices[$term_id][$curent_variation]['discount'] = $discount_type;
                                }
    
                                if( $taxonomy_prices[$term_id][$curent_variation]['productsQuantity'] == NULL ){
                                    $taxonomy_prices[$term_id][$curent_variation]['productsQuantity'] = 0;
                                }
    
                                $taxonomy_prices[$term_id][$curent_variation]['productsQuantity'] = $taxonomy_prices[$term_id][$curent_variation]['productsQuantity'] + $item['quantity'];

                            } else {

                                for( $i = 0; $i < $item['quantity']; $i++ ){
                                    $taxonomy_prices[$term_id]['prices'][] = $item['line_total'] / $item['quantity'];
                                    $taxonomy_prices[$term_id]['discount'] = $discount_type;
                                }
    
                                if( $taxonomy_prices[$term_id]['productsQuantity'] == NULL ){
                                    $taxonomy_prices[$term_id]['productsQuantity'] = 0;
                                }
    
                                $taxonomy_prices[$term_id]['productsQuantity'] = $taxonomy_prices[$term_id]['productsQuantity'] + $item['quantity'];

                            }
                            
                        }

                    }

                }

            }

        }

        if( !empty($taxonomy_prices) ){
            if( $this->get_array_depth($taxonomy_prices) == 4 ) {
                foreach( $taxonomy_prices as $taxonomy_atributes ) {
                    foreach( $taxonomy_atributes as $taxonomy_price ) {
                        if( $taxonomy_price['productsQuantity'] >= $taxonomy_price['discount'] ){
                            $batch_prices = $taxonomy_price['prices'];
                    
                            $products_number = floor($taxonomy_price['productsQuantity'] / $taxonomy_price['discount']);
                        
                            asort($batch_prices);
                                        
                            $batch_prices = array_slice($batch_prices, 0, $products_number );
                            
                            foreach( array_slice($batch_prices, 0, $products_number ) as $price ) {
                                $total_discount += $price; 
                            }
                        }
                    }
                }
            } else {
                foreach( $taxonomy_prices as $taxonomy_price ) {
                    if( $taxonomy_price['productsQuantity'] >= $taxonomy_price['discount'] ){
                        $batch_prices = $taxonomy_price['prices'];
                
                        $products_number = floor($taxonomy_price['productsQuantity'] / $taxonomy_price['discount']);
                    
                        asort($batch_prices);

                        
                                    
                        $batch_prices = array_slice($batch_prices, 0, $products_number );
                        
                        foreach( array_slice($batch_prices, 0, $products_number ) as $price ) {
                            $total_discount += $price; 
                        }
                    }
                }
            }

            $result['total_discount'] = (float) $total_discount;
            $result['products_number'] = $products_number;
        }
            
        return $result;
    }

    public function isAnyDiscountActive()
    {
        $isAnyDiscount = false;

        $online_discount = (float)$this->plugin_options['online_discount'];

        if( $this->activated( 'category_discount' ) ) {
            $discount = $this->calculateTaxonomyDiscount( 'product_cat', 'category_discount' );
        } elseif( $this->activated( 'tag_discount' ) ) {
            $discount = $this->calculateTaxonomyDiscount( 'product_tag', 'tag_discount' );
        }
						
        if( (!empty( $discount ) && $discount['total_discount'] != NULL) || $online_discount > 0 ) {
            $isAnyDiscount = true;
        }

        return $isAnyDiscount;
    }

    public function isAccountPage( $slug )
    {
		global $wp;
		$request = explode( '/', $wp->request );
		
		if( $request[1] == $slug && is_account_page() ) {
            return true;
        }

        return false;
    }

}
