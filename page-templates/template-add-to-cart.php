<?php   
/**
* Template Name: Add to Cart 
*/

$plugin_options = get_option( 'rpd_restaurant_solution' );
$home_url = get_home_url();

do_action( 'before_add_to_cart_template' );

if( isset($_GET['cartId']) && $_GET['cartId'] != ""  ) {
	
	$data['id'] = sanitize_text_field($_GET['cartId']);

	$route = '/product/integration/getvirtualcart';
	$api_url = $plugin_options["api_url"];
	$api_key = $plugin_options["api_key"];
	
	if( $api_url != '' && $api_key != '' ) {
		
		$api_url = $api_url . $route . '?' . 'id=' . $data['id'];

		$args['headers'] = array(
			'ApiKey' => $api_key,
			'Content-Type' => 'application/json',
			'Access-Control-Allow-Origin' => '*',
			'Origin' => $home_url
		);

		$response = wp_remote_get( $api_url, $args );
					
		$responseObject = json_decode($response['body']);

		if( $responseObject->status === 200 ) {

			$products = json_decode($responseObject->data->virtualCart);
			
			if( $products !== NULL && ! empty($products) ) {

				$online_order_delivery_method = isset( $_GET['delivery_method'] ) ? $_GET['delivery_method'] : '';
				$online_order_phone = isset( $_GET['phone'] ) ? $_GET['phone'] : '';
				$online_order_firstname = isset( $_GET['firstname'] ) ? $_GET['firstname'] : '';
				$online_order_lastname = isset( $_GET['lastname'] ) ? $_GET['lastname'] : '';
				$online_order_email = isset( $_GET['email'] ) ? $_GET['email'] : '';
				$online_order_city = isset( $_GET['city'] ) ? $_GET['city'] : '';
				$online_order_area = isset( $_GET['area'] ) ? $_GET['area'] : '';
				$online_order_address = isset( $_GET['address'] ) ? str_replace('+', ' ', $_GET['address']) : '';
				$online_order_building = isset( $_GET['building'] ) ? $_GET['building'] : '';
				$online_order_flat_nr = isset( $_GET['flat_nr'] ) ? $_GET['flat_nr'] : '';
				$online_order_flat_staircase = isset( $_GET['flat_staircase'] ) ? $_GET['flat_staircase'] : '';
				$online_order_flat_floor = isset( $_GET['flat_floor'] ) ? $_GET['flat_floor'] : '';
				$online_order_delivery_date = isset( $_GET['delivery_date'] ) ? $_GET['delivery_date'] : '';
				$online_order_delivery_hour = isset( $_GET['delivery_hour'] ) ? $_GET['delivery_hour'] : '';

				if( $online_order_delivery_method != '' ) {
					
					setcookie("online_order_delivery_method", $online_order_delivery_method, time()+3600, "/");

					if( $online_order_phone && $online_order_phone != '' ) {
						setcookie("online_order_phone", $online_order_phone, time()+3600, "/");
					}
	
					if( $online_order_firstname && $online_order_firstname != '' ) {
						setcookie("online_order_firstname", $online_order_firstname, time()+3600, "/");
					}
	
					if( $online_order_lastname && $online_order_lastname != '' ) {
						setcookie("online_order_lastname", $online_order_lastname, time()+3600, "/");
					}
	
					if( $online_order_email && $online_order_email != '' ) {
						setcookie("online_order_email", $online_order_email, time()+3600, "/");
					}
	
					if( $online_order_city && $online_order_city != '' ) {
						setcookie("online_order_city", $online_order_city, time()+3600, "/");
					}
	
					if( $online_order_area && $online_order_area != '' ) {
						setcookie("online_order_area", $online_order_area, time()+3600, "/");
					}
	
					if( $online_order_address && $online_order_address != '' ) {
						setcookie("online_order_address", $online_order_address, time()+3600, "/");
					}
	
					if( $online_order_building && $online_order_building != '' ) {
						setcookie("online_order_building", $online_order_building, time()+3600, "/");
					}
	
					if( $online_order_flat_nr && $online_order_flat_nr != '' ) {
						setcookie("online_order_flat_nr", $online_order_flat_nr, time()+3600, "/");
					}
	
					if( $online_order_flat_staircase && $online_order_flat_staircase != '' ) {
						setcookie("online_order_flat_staircase", $online_order_flat_staircase, time()+3600, "/");
					}
	
					if( $online_order_flat_floor && $online_order_flat_floor != '' ) {
						setcookie("online_order_flat_floor", $online_order_flat_floor, time()+3600, "/");
					}
	
					if( $online_order_delivery_date && $online_order_delivery_date != '' ) {
						setcookie("online_order_delivery_date", $online_order_delivery_date, time()+3600, "/");
					}
	
					if( $online_order_delivery_hour && $online_order_delivery_hour != '' ) {
						setcookie("online_order_delivery_hour", $online_order_delivery_hour, time()+3600, "/");
					}

					WC()->session->set('phoneOrder', 1);
				}

				WC()->cart->empty_cart();
				
				$success = '';
				$checkout_url = get_home_url() . '/checkout';
							
				if( !isset($_GET['token']) ){
					// Logout any user logged in
					if( is_user_logged_in() ) {
						wp_logout();
					} else {
						if( isset( $_SESSION['specialDiscount'] ) ) {
							unset( $_SESSION['specialDiscount'] );
						}
					}

					// Delete user from cookies
					if (isset($_COOKIE['user'])) {
						unset($_COOKIE['user']); 
						setcookie('user', null, -1, '/'); 
					}
					
					// Delete token from cookies
					if (isset($_COOKIE['token'])) {
						unset($_COOKIE['token']); 
						setcookie('token', null, -1, '/'); 
					}
				}
				
				if( isset($_GET['colorTheme']) && $_GET['colorTheme'] != "" ){
					WC()->session->set('theme', $_GET['colorTheme']);
					WC()->session->set('title', esc_html__('Checkout', 'rpd-restaurant-solution'));
					if( $_GET['showNavbar'] && $_GET['showNavbar'] != "" ){
						WC()->session->set('showNavbar', sanitize_text_field($_GET['showNavbar']));
						setcookie('showNavbar', 'true', time() + (60 * 10), "/");
						$checkout_url = add_query_arg( array(
											'theme' => sanitize_text_field($_GET['colorTheme']),
											'showNavbar' => sanitize_text_field($_GET['showNavbar'])
									), $checkout_url );
					} else {
						$checkout_url = add_query_arg( 'theme', sanitize_text_field($_GET['colorTheme']), $checkout_url );
					}
				}
				
				if( isset($_GET['isIos']) && $_GET['isIos'] == 'true' && isset($_GET['safeAreaInsetsBottom']) && $_GET['safeAreaInsetsBottom'] != "" ) {
					$checkout_url = add_query_arg( array(
											'safeAreaInsetsBottom' => sanitize_text_field($_GET['safeAreaInsetsBottom']),
											'isIos' => sanitize_text_field($_GET['isIos'])
									), $checkout_url );
				}

				if( isset($_GET['language']) && $_GET['language'] != '' ) {
					$checkout_url = str_replace( get_home_url(), get_home_url() . '/' . $_GET['language'] , $checkout_url );
				}
				
				foreach( $products as $product ){
					
					$variationsArray = array();
					$cart_meta_data = array();
									
					if( $product->selectedVariation != NULL && $product->selectedVariation != "" ){
						if( $product->selectedVariation != '0' ){
							$variation = new WC_Product_Variation((int) $product->selectedVariation);
							
							$variationDetails = $variation->get_attribute_summary();
							$variationDetails = str_replace(', ', ',', $variationDetails);
							$variationDetails = explode(',', $variationDetails);
							
							if( is_array($variationDetails) ){
								if( count($variationDetails) > 0 ){
									foreach( $variationDetails as $variationDetail ){
										$variation = explode(':', $variationDetail);
										$variationsArray[$variation[0]] = $variation[1];
									}
								}
							}
						}
					}
					
					if( $product->addons != NULL && ! empty($product->addons) ){

						$counter = 0;
						
						foreach( $product->addons as $addon ){
							$cart_meta_data['addons'][$counter]['name'] = $addon->name;
							$cart_meta_data['addons'][$counter]['value'] = $addon->value;
							$cart_meta_data['addons'][$counter]['price'] = (float) $addon->price;
							$cart_meta_data['addons'][$counter]['field_type'] = $addon->field_type;
							$cart_meta_data['addons'][$counter]['price_type'] = $addon->price_type;
							$cart_meta_data['addons'][$counter]['field_name'] = $addon->field_name;
							
							$counter++;
						}
							
					}
					
					if( ! empty($variationsArray) && ! empty($cart_meta_data) ){ // Variations & Addons
						$success = WC()->cart->add_to_cart( $product->productId, $product->qty, (int) $product->selectedVariation, $variationsArray, $cart_meta_data);
					} else if( ! empty($variationsArray) ) { // Variations
						$success = WC()->cart->add_to_cart( $product->productId, $product->qty, (int) $product->selectedVariation, $variationsArray);
					} else if( ! empty($cart_meta_data) ) { // Addons
						$success = WC()->cart->add_to_cart( $product->productId, $product->qty, NULL, array(), $cart_meta_data);
					} else { // Simple product
						$success = WC()->cart->add_to_cart( $product->productId, $product->qty );
					}				
				}
					
				if( $success != '' ){ 
					wp_redirect( $checkout_url );
					exit();
				}
				
			}
		}
	}
}