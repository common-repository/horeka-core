<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Base;

use HorekaCore\Base\BaseController;

/**
* 
*/
class Enqueue extends BaseController
{
	public function register() 
	{
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdmin' ) );
		
		if( $this->isActiveCheckoutV2() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueueLightVersionScripts' ), 1 );
		} else {
			add_action( 'wp_head', array( $this, 'enqueueLightVersionScripts' ), 1 );
		}
		
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueLightVersionSettingsScripts' ), 11 );
		add_action( 'wp_footer', array( $this, 'enqueuePluginScripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueDatePicker' ), 99 );
		add_action( 'wp_footer', array( $this, 'enqueueCheckoutScripts' ), 1 );
		add_action( 'wp_footer', array( $this, 'enqueueDisplayCity' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueLiteForgotPassword' ), 100 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueCheckoutV2Styles' ), 99 );
		add_action( 'wp_footer', array( $this, 'enqueueCheckoutV2Scripts' ) );
	}

	public function enqueueAdmin() 
	{
		wp_enqueue_style( 'rpd-admin', $this->plugin_url . 'assets/css/rpd-admin.min.css' );
		wp_enqueue_script( 'rpd-admin', $this->plugin_url . 'assets/js/rpd-admin.min.js' );
	}

	public function enqueueLightVersionScripts() 
	{
		if( is_admin() ) {
			return;
		}
		
		if( is_page_template('template-fake-checkout.php') || is_page_template('template-fake-checkout-wrapper.php') || ( is_order_received_page() && WC()->session->get('fake_checkout') ) || ( WC()->session->get('fake_checkout') && isset($_GET['method']) && $_GET['method'] === 'credit_card' ) ) {
			wp_enqueue_style( 'rpd-checkout-light', $this->plugin_url . 'assets/css/woocommerce/rpd-checkout-light.min.css' );
			wp_enqueue_style( 'rpd-themes-map', $this->plugin_url . 'assets/css/api/rpd-themes-map.min.css' );
		}
	}

	public function enqueueLightVersionSettingsScripts() 
	{
		if( is_page_template('template-fake-checkout.php') || is_page_template('template-fake-checkout-wrapper.php') || ( is_order_received_page() && WC()->session->get('fake_checkout') ) ) {
			wp_enqueue_script('rpd-header-scripts', $this->plugin_url . 'assets/js/api/rpd-header-scripts.min.js' );
		}
	
		if( is_page_template('template-fake-checkout.php')  || ( is_order_received_page() && WC()->session->get('fake_checkout') ) || ( isset($_GET['method']) && $_GET['method'] === 'credit_card' ) ) {
			$api_settings = array(
				'api_url' => $this->plugin_options['api_url'],
				'api_key' => $this->plugin_options['api_key']
			);
			
			$invalid_login_message = esc_html__('Please add an username and a password.', 'rpd-restaurant-solution');

			wp_enqueue_script('rpd-custom-light', $this->plugin_url . 'assets/js/woocommerce/rpd-custom-light.min.js', array(), false, true);
			wp_enqueue_script('rpd-dataservice', $this->plugin_url . 'assets/js/api/rpd-dataservice.min.js', array(), false, true);
			wp_enqueue_script('rpd-helper', $this->plugin_url . 'assets/js/api/rpd-helper.min.js', array(), false, true);
			wp_enqueue_script('rpd-settings', $this->plugin_url . 'assets/js/api/rpd-settings.min.js', array(), false, true);
			wp_localize_script( 'rpd-custom-light', 'invalid_login_message', $invalid_login_message );
			wp_localize_script( 'rpd-settings', 'api_settings', $api_settings );
		}
	
		if( is_page_template('template-fake-checkout.php') && WC()->session->get('theme') != true ) {
			wp_enqueue_script('rpd-custom-light-only-web', $this->plugin_url . 'assets/js/woocommerce/rpd-custom-light-only-web.min.js', array(), false, true);
		}
	}

	public function enqueuePluginScripts() 
	{
		$invalid_number_message = esc_html__('Please insert a valid phone number.', 'rpd-restaurant-solution');
		wp_enqueue_style( 'rpd-bookings', $this->plugin_url . 'assets/css/rpd-bookings.min.css' );
		wp_enqueue_style( 'rpd-restaurant-solution', $this->plugin_url . 'assets/css/rpd-restaurant-solution.min.css' );
		wp_enqueue_script('rpd-custom-code', $this->plugin_url . 'assets/js/woocommerce/rpd-custom-code.min.js', array(), false, true);
		wp_localize_script( 'rpd-custom-code', 'invalid_number_message', $invalid_number_message );
		if( ( is_checkout() || is_page_template('template-fake-checkout.php') || is_page_template('template-fake-checkout-wrapper.php') ) && !is_user_logged_in() ) {
			wp_enqueue_script('rpd-keep-user-information', $this->plugin_url . 'assets/js/woocommerce/rpd-keep-user-information.min.js', array(), false, true);
		}
	}
	
	public function enqueueDatePicker() 
	{
		wp_enqueue_style( 'datetimepicker', $this->plugin_url . 'assets/css/woocommerce/datetimepicker.min.css' );
		wp_enqueue_script('datetimepicker', $this->plugin_url . 'assets/js/woocommerce/datetimepicker.min.js' );
	}

	public function enqueueCheckoutScripts() 
	{
		if( is_checkout() ) {
			wp_enqueue_style( 'rpd-checkout', $this->plugin_url . 'assets/css/woocommerce/rpd-checkout.min.css' );
			wp_enqueue_script('rpd-checkout', $this->plugin_url . 'assets/js/woocommerce/rpd-checkout.min.js', array(), false, true);
			
			wp_enqueue_style( 'modal', $this->plugin_url . 'assets/css/api/modal.min.css' );
			wp_enqueue_script('modal', $this->plugin_url . 'assets/js/api/modal.min.js', array(), false, true);

			wp_enqueue_script('jquery-datetimepicker', $this->plugin_url . 'assets/js/woocommerce/jquery.datetimepicker.min.js', array(), false, true);
			
			if( ! is_user_logged_in() ) {
				wp_enqueue_script('rpd-client-information', $this->plugin_url . 'assets/js/woocommerce/rpd-client-information.min.js', array(), false, false);
			}
		}	
	}

	public function enqueueDisplayCity()
	{
		if( $this->activated( 'diplay_city_in_checkout' ) && is_checkout() ) {
			wp_enqueue_script('rpd-hide-city', $this->plugin_url . 'assets/js/woocommerce/rpd-hide-city.min.js', array(), false, true);
		}
	}

	public function enqueueLiteForgotPassword()
	{
		if( !is_page_template('template-fake-checkout.php') ) {
            return;
        }

		wp_enqueue_style( 'ThemeV2', $this->plugin_url . 'assets/css/api/ThemeV2.css' );
		wp_enqueue_script('vue-js', $this->plugin_url . 'assets/js/api/vue.js', array(), false, true);
		wp_enqueue_script('page-changePassword', $this->plugin_url . 'assets/js/api/page-changePassword.js', array(), false, true);
	}

	public function enqueueCheckoutV2Styles()
	{
		if( !$this->isActiveCheckoutV2() ) {
			return;
		}

		if( is_checkout() || is_page_template('template-fake-checkout.php')  || ( is_order_received_page() && WC()->session->get('fake_checkout') ) || ( isset($_GET['method']) && $_GET['method'] === 'credit_card' ) ) {
			wp_enqueue_style( 'rpd-checkout-v2', $this->plugin_url . 'assets/css/woocommerce/rpd-checkout-v2.min.css' );
		}
		
		if( is_page_template('template-fake-checkout.php')  || ( is_order_received_page() && WC()->session->get('fake_checkout') ) || ( isset($_GET['method']) && $_GET['method'] === 'credit_card' ) ) {
			wp_enqueue_style( 'rpd-checkout-v2-lite', $this->plugin_url . 'assets/css/woocommerce/rpd-checkout-v2-lite.min.css' );
			wp_enqueue_style( 'rpd-checkout-color-themes-lite', $this->plugin_url . 'assets/css/woocommerce/rpd-checkout-color-themes-lite.min.css' );
		}
	}
	
	public function enqueueCheckoutV2Scripts()
	{
		if( !$this->isActiveCheckoutV2() ) {
			return;
		}

		$invalid_login_message = esc_html__('Please add an username and a password.', 'rpd-restaurant-solution');

		wp_enqueue_script('rpd-checkout-v2-js', $this->plugin_url . 'assets/js/woocommerce/rpd-checkout-v2.min.js', array(), false, true);
		wp_localize_script( 'rpd-checkout-v2-js', 'invalid_login_message', $invalid_login_message );
	}

}