<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Base;

use HorekaCore\Api\SettingsApi;
use HorekaCore\Api\Callbacks\AdminCallbacks;
use HorekaCore\Api\Callbacks\DeliveryCallbacks;

/**
* 
*/
class DeliveryMethodsController extends BaseController
{
    public $settings;

    public $callbacks;

    public $delivery_callbacks;

    public $subpages = array();
    
    public function register()
    {
        $this->settings = new SettingsApi();

        $this->callbacks = new AdminCallbacks();

        $this->delivery_callbacks = new DeliveryCallbacks();

        $this->setSubpages();

		$this->setSettings();

		$this->setSections();

		$this->setFields();

		$this->settings->addSubPages( $this->subpages )->register();
    }

    public function setSubpages()
	{
		$this->subpages = array(
			array(
				'parent_slug' => 'rpd_restaurant_solution', 
				'page_title' => esc_html__('Delivery Methods', 'rpd-restaurant-solution'),
				'menu_title' => esc_html__('Delivery Methods', 'rpd-restaurant-solution'),
				'capability' => 'manage_options', 
				'menu_slug' => 'rpd_delivery_methods', 
				'callback' => array( $this->callbacks, 'adminDeliveryMethods' )
			)
		);
	}

	public function setSettings()
	{
		$args = array(
			array(
				'option_group' => 'rpd_delivery_methods_settings',
				'option_name' => 'rpd_delivery_methods',
				'callback' => array( $this->delivery_callbacks, 'deliverySanitize' )
			)
		);

		$this->settings->setSettings( $args );
	}

	public function setSections()
	{
		$args = array(
			array(
				'id' => 'rpd_delivery_methods_index',
				'title' => esc_html__('Add New Delivery Method', 'rpd-restaurant-solution'),
				'callback' => array( $this->delivery_callbacks, 'deliverySectionManager' ),
				'page' => 'rpd_delivery_methods'
			)
		);

		$this->settings->setSections( $args );
	}

	public function setFields()
	{	
		$args = array(
			array(
				'id' => 'method_name',
				'title' => __('Name', 'rpd-restaurant-solution'),
				'callback' => array( $this->delivery_callbacks, 'textField' ),
				'page' => 'rpd_delivery_methods',
				'section' => 'rpd_delivery_methods_index',
				'args' => array(
					'option_name' => 'rpd_delivery_methods',
					'label_for' => 'method_name',
					'array' => 'method_name'
				)
			),
			array(
				'id' => 'method_discount',
				'title' => __('Discount', 'rpd-restaurant-solution'),
				'callback' => array( $this->delivery_callbacks, 'textField' ),
				'page' => 'rpd_delivery_methods',
				'section' => 'rpd_delivery_methods_index',
				'args' => array(
					'option_name' => 'rpd_delivery_methods',
					'label_for' => 'method_discount',
					'array' => 'method_discount'
				)
			),
			array(
				'id' => 'method_status',
				'title' => __('Active', 'rpd-restaurant-solution'),
				'callback' => array( $this->delivery_callbacks, 'checkboxField' ),
				'page' => 'rpd_delivery_methods',
				'section' => 'rpd_delivery_methods_index',
				'args' => array(
					'option_name' => 'rpd_delivery_methods',
					'label_for' => 'method_status',
					'array' => 'method_status',
					'class' => 'ui-toggle'
				)
			),
			array(
				'id' => 'slug',
				'title' => __('Slug', 'rpd-restaurant-solution'),
				'callback' => array( $this->delivery_callbacks, 'hiddenField' ),
				'page' => 'rpd_delivery_methods',
				'section' => 'rpd_delivery_methods_index',
				'args' => array(
					'option_name' => 'rpd_delivery_methods',
					'label_for' => 'slug',
					'array' => 'slug'
				)
			)
		);

		$this->settings->setFields( $args );
	}

}