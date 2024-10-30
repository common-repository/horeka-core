<?php

// Add custom image size for products list
add_image_size( 'product_image_custom', 300, 300, true );

// Remove Coupon Form from the checkout page header
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

if ( ! function_exists( 'woocommerce_subcategory_thumbnail' ) ) {
	/**
	 * Show subcategory thumbnails.
	 *
	 * @param mixed $category Category.
	 */
	function woocommerce_subcategory_thumbnail( $category ) { 
		$small_thumbnail_size = apply_filters( 'subcategory_archive_thumbnail_size', 'woocommerce_thumbnail' );
		$dimensions           = wc_get_image_size( $small_thumbnail_size );
		$thumbnail_id         = get_term_meta( $category->term_id, 'thumbnail_id', true );
		if ( $thumbnail_id ) {
			$image        = wp_get_attachment_image_src( $thumbnail_id, $small_thumbnail_size );
			$image        = $image[0];
			$image_srcset = function_exists( 'wp_get_attachment_image_srcset' ) ? wp_get_attachment_image_srcset( $thumbnail_id, $small_thumbnail_size ) : false;
			$image_sizes  = function_exists( 'wp_get_attachment_image_sizes' ) ? wp_get_attachment_image_sizes( $thumbnail_id, $small_thumbnail_size ) : false;
		} else {
			$parentCategory = get_the_category_by_ID($category->parent);
			if( !is_wp_error($parentCategory) && strtolower( $parentCategory ) == "bauturi" ){
				$image = get_stylesheet_directory_uri() . '/images/wine-bottle.png';
			}else{
				$image        = wc_placeholder_img_src();
				$image_srcset = false;
				$image_sizes  = false;
			}
		}

		if ( $image ) {
			// Prevent esc_url from breaking spaces in urls for image embeds.
			// Ref: https://core.trac.wordpress.org/ticket/23605.
			$image = str_replace( ' ', '%20', $image );

			// Add responsive image markup if available.
			if ( $image_srcset && $image_sizes ) {
				echo '<img src="' . esc_url( $image ) . '" alt="' . sanitize_text_field( $category->name ) . '" width="' . sanitize_text_field( $dimensions['width'] ) . '" height="' . sanitize_text_field( $dimensions['height'] ) . '" srcset="' . sanitize_text_field( $image_srcset ) . '" sizes="' . sanitize_text_field( $image_sizes ) . '" />';
			} else {
				echo '<img src="' . esc_url( $image ) . '" alt="' . sanitize_text_field( $category->name ) . '" width="' . sanitize_text_field( $dimensions['width'] ) . '" height="' . sanitize_text_field( $dimensions['height'] ) . '" />';
			}
		}
	}
}

// Overrite woocommerce_get_product_thumbnail function
if ( ! function_exists( 'woocommerce_get_product_thumbnail' ) ) {
	function woocommerce_get_product_thumbnail( $size = 'woocommerce_thumbnail', $deprecated1 = 0, $deprecated2 = 0 ) { 
		
		global $product;

		$productCategories = get_the_terms( $product->get_id(), 'product_cat' );
		$isDrinksChild = false;
		foreach ($productCategories as $category) {
			
			if( $category->parent > 0 && strtolower(get_the_category_by_ID($category->parent)) == "bauturi" )
				$isDrinksChild = true;
		}

		if( $isDrinksChild && $product->get_image_id() == "" ){

			return '<img width="300" height="300" src="' . get_stylesheet_directory_uri() . '/images/wine-bottle.png' . '" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="Demo" title="Demo" sizes="(max-width: 300px) 100vw, 300px">';

		} else {
			$image_size = apply_filters( 'single_product_archive_thumbnail_size', $size );

			$attr = array(
				'alt' => $product->get_title(),
				'title' => $product->get_title(),
			);
			
			return $product ? $product->get_image( $image_size, $attr ) : '';
		}

		
	}
}

// Override default sorting
if ( ! function_exists( 'woocommerce_catalog_ordering' ) ) {

	/**
	 * Output the product sorting options.
	 */
	function woocommerce_catalog_ordering() {
		if ( ! wc_get_loop_prop( 'is_paginated' ) || ! woocommerce_products_will_display() ) {
			return;
		}

		$show_default_orderby = '12' === apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', '12' ) );
		
		$catalog_orderby_options = apply_filters(
			'woocommerce_catalog_orderby',
			array(
				'12' => __( 'Show 12 products', 'rpd-restaurant-solution' ),
				'24' => __( 'Show 24 products', 'rpd-restaurant-solution' ),
				'36' => __( 'Show 36 products', 'rpd-restaurant-solution' ),
				'-1' => __( 'Show all products', 'rpd-restaurant-solution' ),
			)
		);

		$default_orderby = wc_get_loop_prop( 'is_search' ) ? '12' : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', '' ) );
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : $default_orderby;
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( wc_get_loop_prop( 'is_search' ) ) {
			$catalog_orderby_options = array_merge( array( '12' => __( 'Show 12 products', 'rpd-restaurant-solution' ) ), $catalog_orderby_options );

			#unset( $catalog_orderby_options['menu_order'] );
		}

		if ( ! array_key_exists( $orderby, $catalog_orderby_options ) ) {
			$orderby = current( array_keys( $catalog_orderby_options ) );
		}

		wc_get_template(
			'loop/orderby.php',
			array(
				'catalog_orderby_options' => $catalog_orderby_options,
				'orderby'                 => $orderby,
				'show_default_orderby'    => $show_default_orderby,
			)
		);
	}
}

// Save all the client information in Cookie
function horeka_core_save_cookie_details($orderID) { 
	global $wpdb;
	$rows = $wpdb->get_results("SELECT a.id, b.meta_value as telefon, e.meta_value as nume, f.meta_value as prenume, h.meta_value as adresa, i.meta_value as email
													FROM wp_posts as a   
													INNER JOIN wp_postmeta as b
													on a.ID = b.post_id
                                                    inner join wp_postmeta as e 
                                                    on a.ID = e.post_id
                                                    inner join wp_postmeta as f 
                                                    on a.ID = f.post_id
													inner join wp_postmeta as h
                                                    on a.ID = h.post_id
                                                    inner join wp_postmeta as i
                                                    on a.ID = i.post_id
                                                    AND e.meta_key = '_billing_first_name'
                                                    AND f.meta_key = '_billing_last_name'
													AND b.meta_key = '_billing_phone'
													AND h.meta_key = '_billing_address_1'
                                                    AND i.meta_key = '_billing_email'
													WHERE a.id = $orderID");
	foreach($rows as $row){
		setcookie('date_salvate', 'true' , time()+31556926, "/"); // 1 an
		setcookie('nume', str_replace( ' ', ',', $row->nume)	, time()+31556926, "/"); // 1 an
		setcookie('prenume', str_replace( ' ', ',', $row->prenume)	, time()+31556926, "/"); // 1 an
		setcookie('telefon', str_replace( ' ', ',', $row->telefon)	, time()+31556926, "/"); // 1 an
		setcookie('adresa', str_replace( ' ', ',', $row->adresa)	, time()+31556926, "/"); // 1 an
		setcookie('email', str_replace( ' ', ',', $row->email)	, time()+31556926, "/"); // 1 an
	}
}

// Clear information about customer
function horeka_core_clear_cookie_details() {
	setcookie('date_salvate', ''	, time()+10, "/"); 
	setcookie('nume'		, ''	, time()+10, "/"); 
	setcookie('prenume'		, ''	, time()+10, "/"); 
	setcookie('telefon'		, ''	, time()+10, "/"); 
	setcookie('adresa'		, ''	, time()+10, "/"); 
	setcookie('email'		, ''	, time()+10, "/"); 
}

// Get current daily menu link
function horeka_core_get_daily_link() {
	global $wpdb;
	// setting local zone
	setlocale(LC_ALL, 'ro_RO.UTF-8');
	// get the current day and remove diacritics
	$currentDay = str_replace('ț', 't', strftime("%A"));
	$currentDay = str_replace('ă', 'a', $currentDay);
	$myposts = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_title LIKE '%s' AND post_status = 'publish'", '%'. $wpdb->esc_like( $currentDay )) );
	$singleDailyMenu = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_title LIKE %s AND post_status = 'publish'", '%' . $wpdb->esc_like( 'meniul zilei' ) . '%') );

	if( $myposts != NULL ){
		return $myposts[0]->guid;
	} elseif( $singleDailyMenu != NULL ) {
		return $singleDailyMenu[0]->guid;
	} else {
		return;
	}
}

// Set cookie for different PHP Versions
function horeka_core_set_cookie_for_php_version($name, $value, $hours = 1) {
	if( !is_int($hours) ) {
		throw new Exception("Provide an integer value for the third parameter.");
	}

	// Set cookie for the second checkout ( App Mobile )
	$cookie_args = array(
		'expires' => time() + $hours*60*3600,
		'path' => '/',
		'samesite' => 'Lax'
	);

	if( (float)phpversion() >= 7.3 ){
		setcookie((string) $name, $value, $cookie_args);
	} else {
		setcookie((string) $name, $value, time() + $hours*60*3600, '/');
	}
}

function is_active_discount()
{
	return \HorekaCore\Woocommerce\Functions::isDiscountActive();
}