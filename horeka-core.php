<?php
/**
 * @package  HorekaCore
 */
/*
Plugin Name: Horeka Core
Plugin URI: https://www.roweb.ro/online-delivery-restaurant.html
Description: The complete solution for delivery, online sales/reservations/orders & marketing
Version: 2.4.2
Author: Roweb
Author URI: https://www.roweb.ro/
License: GPLv2 or later
Text Domain: rpd-restaurant-solution
Domain Path: /languages
*/

// If this file is called firectly, abort!!!
defined( 'ABSPATH' ) or die( 'Hey, what are you doing here?' );

// Require once the Composer Autoload
if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

// Require once the functions.php file
if ( file_exists( dirname( __FILE__ ) . '/inc/functions.php' ) ) {
	require_once dirname( __FILE__ ) . '/inc/functions.php';
}

/**
 * The code that runs during plugin activation
 */
function activate_rpd_restaurant_solution_plugin() {
	HorekaCore\Base\Activate::activate();
}
register_activation_hook( __FILE__, 'activate_rpd_restaurant_solution_plugin' );

/**
 * The code that runs during plugin deactivation
 */
function deactivate_rpd_restaurant_solution_plugin() {
	HorekaCore\Base\Deactivate::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_rpd_restaurant_solution_plugin' );

/**
 * Initialize all the core classes of the plugin
 */
if ( class_exists( 'HorekaCore\\Init' ) ) {
	HorekaCore\Init::register_services();
}