<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Base;

use HorekaCore\Api\SettingsApi;
use HorekaCore\Base\BaseController;
use HorekaCore\Api\Callbacks\AdminCallbacks;
use HorekaCore\Api\Callbacks\DistanceCallbacks;

/**
* 
*/
class DistanceManagerController extends BaseController
{
	public $settings;

	public $callbacks;

	public $distance_callbacks;

	public $subpages = array();

	public function register()
	{
		if ( ! $this->activated( 'distance_manager' ) ) return;

		$this->settings = new SettingsApi();

		$this->callbacks = new AdminCallbacks();

		$this->distance_callbacks = new DistanceCallbacks();

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
				'page_title' => esc_html__('Distance Manager', 'rpd-restaurant-solution'),
				'menu_title' => esc_html__('Distance Manager', 'rpd-restaurant-solution'),
				'capability' => 'manage_options', 
				'menu_slug' => 'rpd_manage_distances', 
				'callback' => array( $this->callbacks, 'adminDistancesManager' )
			)
		);
	}

	public function setSettings()
	{
		$args = array(
			array(
				'option_group' => 'rpd_manage_distances_settings',
				'option_name' => 'rpd_manage_distances',
				'callback' => array( $this->distance_callbacks, 'distanceSanitize' )
			)
		);

		$this->settings->setSettings( $args );
	}

	public function setSections()
	{
		$args = array(
			array(
				'id' => 'rpd_manage_distances_index',
				'title' => esc_html__('Add a new distance interval', 'rpd-restaurant-solution'),
				'callback' => array( $this->distance_callbacks, 'distanceSectionManager' ),
				'page' => 'rpd_manage_distances'
			)
		);

		$this->settings->setSections( $args );
	}

	public function setFields()
	{	
		$distance_options = $this->plugin_distance_options;

		$next_id = 0;

		if( $distance_options ) {
			$next_id = count($distance_options);
		}

		$args = array(
			array(
				'id' => 'distance_type',
				'title' => esc_html__('Distance ID', 'rpd-restaurant-solution'),
				'callback' => array( $this->distance_callbacks, 'hiddenField' ),
				'page' => 'rpd_manage_distances',
				'section' => 'rpd_manage_distances_index',
				'args' => array(
					'option_name' => 'rpd_manage_distances',
					'next_id' => $next_id,
					'label_for' => 'distance_type',
					'array' => 'distance_type'
				)
			),
			array(
				'id' => 'minimum_interval',
				'title' => esc_html__('Minimum interval (km)', 'rpd-restaurant-solution'),
				'callback' => array( $this->distance_callbacks, 'textField' ),
				'page' => 'rpd_manage_distances',
				'section' => 'rpd_manage_distances_index',
				'args' => array(
					'option_name' => 'rpd_manage_distances',
					'label_for' => 'minimum_interval',
					'placeholder' => 'eg. 0',
					'array' => 'distance_type'
				)
			),
			array(
				'id' => 'maximum_interval',
				'title' => esc_html__('Maximum interval (km)', 'rpd-restaurant-solution'),
				'callback' => array( $this->distance_callbacks, 'textField' ),
				'page' => 'rpd_manage_distances',
				'section' => 'rpd_manage_distances_index',
				'args' => array(
					'option_name' => 'rpd_manage_distances',
					'label_for' => 'maximum_interval',
					'placeholder' => 'eg. 5',
					'array' => 'maximum_interval'
				)
			),
			array(
				'id' => 'cost_interval',
				'title' => esc_html__('Shipping Cost', 'rpd-restaurant-solution'),
				'callback' => array( $this->distance_callbacks, 'textField' ),
				'page' => 'rpd_manage_distances',
				'section' => 'rpd_manage_distances_index',
				'args' => array(
					'option_name' => 'rpd_manage_distances',
					'label_for' => 'cost_interval',
					'placeholder' => 'eg. 20',
					'array' => 'cost_interval'
				)
			),
			array(
				'id' => 'minimum_amount_interval',
				'title' => esc_html__('Minimum Amount', 'rpd-restaurant-solution'),
				'callback' => array( $this->distance_callbacks, 'textField' ),
				'page' => 'rpd_manage_distances',
				'section' => 'rpd_manage_distances_index',
				'args' => array(
					'option_name' => 'rpd_manage_distances',
					'label_for' => 'minimum_amount_interval',
					'placeholder' => 'eg. 50',
					'array' => 'minimum_amount_interval'
				)
			),
		);

		$this->settings->setFields( $args );
	}
}