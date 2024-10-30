<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Base;

use HorekaCore\Api\SettingsApi;
use HorekaCore\Base\BaseController;
use HorekaCore\Api\Callbacks\AdminCallbacks;
use HorekaCore\Api\Callbacks\AreasCallbacks;

/**
* 
*/
class AreasImporterController extends BaseController
{
	public $settings;

	public $callbacks;

	public $areas_callbacks;

	public $subpages = array();

	public function register()
	{
		if ( ! $this->activated( 'display_areas' ) ) return;

		$this->settings = new SettingsApi();

		$this->callbacks = new AdminCallbacks();

		$this->areas_callbacks = new AreasCallbacks();

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
				'page_title' => esc_html__('Areas Importer', 'rpd-restaurant-solution'),
				'menu_title' => esc_html__('Areas Importer', 'rpd-restaurant-solution'),
				'capability' => 'manage_options', 
				'menu_slug' => 'rpd_areas_importer', 
				'callback' => array( $this->callbacks, 'adminAreasImporter' )
			)
		);
	}

	public function setSettings()
	{
		$args = array(
			array(
				'option_group' => 'rpd_areas_importer_settings',
				'option_name' => 'rpd_areas_importer',
				'callback' => array( $this->areas_callbacks, 'areasSanitize' )
			)
		);

		$this->settings->setSettings( $args );
	}

	public function setSections()
	{
		$args = array(
			array(
				'id' => 'rpd_areas_importer_index',
				'title' => esc_html__('', 'rpd-restaurant-solution'),
				'callback' => array( $this->areas_callbacks, 'areasSectionManager' ),
				'page' => 'rpd_areas_importer'
			)
		);

		$this->settings->setSections( $args );
	}

	public function setFields()
	{	
		$args = array();
		$counter = 0;
		
		$areas = get_option( 'rpd_areas' ) ?: array();

		if( !empty( $areas ) ) {
			foreach ($areas as $key => $value) {
				$args[$counter] = array(
					'id' => $key,
					'title' => esc_html__($value['oras'] . ' [' . $value['judet'] . ']', 'rpd-restaurant-solution'),
					'callback' => array( $this->areas_callbacks, 'checkboxField' ),
					'page' => 'rpd_areas_importer',
					'section' => 'rpd_areas_importer_index',
					'args' => array(
						'option_name' => 'rpd_areas_importer',
						'label_for' => $key,
						'array' => $key
					)
				);

				$counter++;
			}

			$this->settings->setFields( $args );
		}		
	}
}