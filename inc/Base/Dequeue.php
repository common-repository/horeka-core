<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Base;

use HorekaCore\Base\BaseController;

/**
* 
*/
class Dequeue extends BaseController
{
	public $ignored_styles;

	public $ignored_scripts;

	public function register() 
	{
		if( !$this->activated( 'only_light_version' ) || is_admin() ) {
			return;
		}

		$this->ignored_styles = array(
			'admin-bar',
			'datetimepicker',
			'tokoo-style-main',
			'tokoo-font-icons',
			'tokoo-fonts',
			'style',
			'woocommerce-layout',
			'woocommerce-smallscreen',
			'woocommerce-general',
			'woocommerce-inline',
			'thwcfd-checkout-style',
			'child-understrap-styles',
			'child-styles',
			'wpb-google-roboto',
			'rpd-checkout-v2',
			'rpd-checkout-v2-lite',
			'rpd-checkout-color-themes-lite',
			'rpd-themes-map',
			'it-gift-dropdown-css',
			'it-gift-modal-style',
			'it-gift-popup',
			'it-gift-style'
		);

		$this->ignored_scripts = array(
			'admin-bar',
			'jquery',
			'jquery-ui-accordion',
			'jquery-ui-tabs',
			'google-maps-api',
			'datetimepicker',
			'wp-embed',
			'rpd-header-scripts',
			'rpd-custom-light',
			'rpd-dataservice',
			'rpd-helper',
			'rpd-settings',
			'rpd-custom-light-only-web',
			'selectWoo',
			'wc-checkout',
			'woocommerce',
			'wc-cart-fragments',
			'jquery-tiptip',
			'rpd-admin',
			'it-gift-dropdown-js',
			'pw-gift-add-jquery-adv',
			'pw-gift-scrollbar-js',
			'child-understrap-scripts',
			'custom-scripts'
		);

		add_action( 'wp_enqueue_scripts', array( $this, 'dequeueStyles' ), 99 );
		add_action( 'wp_print_scripts', array( $this, 'dequeueScripts' ), 99 );
	}

	private function getAllEnqueuedScripts() 
	{

		global $wp_styles;
		global $wp_scripts;

		$result = array();
	
		foreach( $wp_styles->queue as $handle ) {
			$result['styles'][] = $handle;
		}

		foreach( $wp_scripts->queue as $handle ) {
		   $result['scripts'][] = $handle;
		}

		return $result;
	}

	public function dequeueStyles() 
	{
		$all_the_scripts_and_styles = array();
		$all_the_scripts_and_styles = $this->getAllEnqueuedScripts();
		
		if( !empty( $all_the_scripts_and_styles ) ) {
			if( $all_the_scripts_and_styles['styles'] != NULL & !empty($all_the_scripts_and_styles['styles']) ) {
				foreach( $all_the_scripts_and_styles['styles'] as $handle ) {
					if( in_array( $handle, $this->ignored_styles ) ) {
						continue;
					}

					wp_dequeue_style( $handle );
					wp_deregister_style( $handle );
				}
			}
		}
	}

	public function dequeueScripts() 
	{
		$all_the_scripts_and_styles = array();
		$all_the_scripts_and_styles = $this->getAllEnqueuedScripts();
		
		if( !empty( $all_the_scripts_and_styles ) ) {
			if( $all_the_scripts_and_styles['scripts'] != NULL & !empty($all_the_scripts_and_styles['scripts']) ) {
				foreach( $all_the_scripts_and_styles['scripts'] as $handle ) {
					if( in_array( $handle, $this->ignored_scripts ) ) {
						continue;
					}

					wp_dequeue_script( $handle );
				}
				
			}
		}
	}

}