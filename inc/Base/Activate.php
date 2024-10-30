<?php
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Base;

class Activate
{
	public static function activate() 
	{
		flush_rewrite_rules();

		$default = array();

		$delivery_methods = array(
			'livrare-la-domiciliu' => array(
				'slug' => 'livrare-la-domiciliu',
				'method_name' => esc_html__('Delivery', 'rpd-restaurant-solution'), 
				'method_discount' => 0,
				'method_status' => 1
			),
			'ridicare-personala' => array(
				'slug' => 'ridicare-personala',
				'method_name' => esc_html__('Take away', 'rpd-restaurant-solution'), 
				'method_discount' => 0,
				'method_status' => 1
			),
			'servire-la-restaurant' => array(
				'slug' => 'servire-la-restaurant',
				'method_name' => esc_html__('Eat on place', 'rpd-restaurant-solution'),
				'method_discount' => 0,
				'method_status' => 1
			)
		);

		if ( ! get_option( 'rpd_restaurant_solution' ) ) {
			update_option( 'rpd_restaurant_solution', $default );
		}

		if ( ! get_option( 'rpd_manage_distances' ) ) {
			update_option( 'rpd_manage_distances', $default );
		}

		if ( ! get_option( 'rpd_delivery_methods' ) ) {
			update_option( 'rpd_delivery_methods', $delivery_methods );
		}
	}
}