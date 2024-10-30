<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Pages;

use HorekaCore\Api\SettingsApi;
use HorekaCore\Base\BaseController;
use HorekaCore\Api\Callbacks\AdminCallbacks;
use HorekaCore\Api\Callbacks\ManagerCallbacks;

/**
* 
*/
class Dashboard extends BaseController
{
	public $settings;

	public $callbacks;

	public $callbacks_mngr;

	public $pages = array();

	// public $subpages = array();

	public function register() 
	{
		$this->settings = new SettingsApi();

		$this->callbacks = new AdminCallbacks();

		$this->callbacks_mngr = new ManagerCallbacks();

		$this->setPages();

		// $this->setSubpages();

		$this->setSettings();
		$this->setSections();
		$this->setFields();

		$this->settings->addPages( $this->pages )->withSubPage( 'Dashboard' )->register();
	}

	public function setPages() 
	{
		$this->pages = array(
			array(
				'page_title' => esc_html__('Horeka Core', 'rpd-restaurant-solution'),
				'menu_title' => esc_html__('Horeka Core', 'rpd-restaurant-solution'),
				'capability' => 'manage_options', 
				'menu_slug' => 'rpd_restaurant_solution', 
				'callback' => array( $this->callbacks, 'adminDashboard' ), 
				'icon_url' => 'dashicons-store', 
				'position' => 110
			)
		);
	}

	public function setSettings()
	{
		$args = array(
			array(
				'option_group' => 'rpd_options_group',
				'option_name' => 'rpd_restaurant_solution',
				'callback' => array( $this->callbacks_mngr, 'fieldSanitize' )
			)
		);
		
		$this->settings->setSettings( $args );
	}

	public function setSections()
	{
		$args = array(
			array(
				'id' => 'rpd_options_index',
				'title' => esc_html__('Options', 'rpd-restaurant-solution'),
				'callback' => array( $this->callbacks_mngr, 'optionsManager' ),
				'page' => 'rpd_restaurant_solution'
			),
			array(
				'id' => 'rpd_texts_index',
				'title' => esc_html__('Inputs', 'rpd-restaurant-solution'),
				'callback' => array( $this->callbacks_mngr, 'textsManager' ),
				'page' => 'rpd_restaurant_solution'
			),
			array(
				'id' => 'rpd_textareas_index',
				'title' => esc_html__('Textareas', 'rpd-restaurant-solution'),
				'callback' => array( $this->callbacks_mngr, 'textareasManager' ),
				'page' => 'rpd_restaurant_solution'
			),
			array(
				'id' => 'rpd_social_index',
				'title' => esc_html__('Social', 'rpd-restaurant-solution'),
				'callback' => array( $this->callbacks_mngr, 'socialManager' ),
				'page' => 'rpd_restaurant_solution'
			),
			array(
				'id' => 'rpd_api_index',
				'title' => esc_html__('API', 'rpd-restaurant-solution'),
				'callback' => array( $this->callbacks_mngr, 'apiManager' ),
				'page' => 'rpd_restaurant_solution'
			),
			array(
				'id' => 'rpd_netopia_index',
				'title' => esc_html__('Netopia Credentials', 'rpd-restaurant-solution'),
				'callback' => array( $this->callbacks_mngr, 'netopiaManager' ),
				'page' => 'rpd_restaurant_solution'
			)
		);

		$this->settings->setSections( $args );
	}

	public function setFields()
	{
		$args = array();

		// Checkbox Inputs
		foreach( $this->checkbox_options_manager as $key => $value ) {

			$args[] = array(
				'id' => $key,
				'title' => $value,
				'callback' => array( $this->callbacks_mngr, 'checkboxField' ),
				'page' => 'rpd_restaurant_solution',
				'section' => 'rpd_options_index',
				'args' => array(
					'option_name' => 'rpd_restaurant_solution',
					'label_for' => $key,
					'class' => 'ui-toggle'
				)
			);

		}

		// Text Inputs
		foreach( $this->text_options_manager as $key => $value ) {

			$args[] = array(
				'id' => $key,
				'title' => $value,
				'callback' => array( $this->callbacks_mngr, 'textField' ),
				'page' => 'rpd_restaurant_solution',
				'section' => 'rpd_texts_index',
				'args' => array(
					'option_name' => 'rpd_restaurant_solution',
					'label_for' => $key,
					'class' => 'regular-text'
				)
			);

		}

		// Textarea Inputs
		foreach( $this->textarea_options_manager as $key => $value ) {

			$args[] = array(
				'id' => $key,
				'title' => $value,
				'callback' => array( $this->callbacks_mngr, 'textareaField' ),
				'page' => 'rpd_restaurant_solution',
				'section' => 'rpd_textareas_index',
				'args' => array(
					'option_name' => 'rpd_restaurant_solution',
					'label_for' => $key,
					'class' => 'small-text'
				)
			);

		}

		// Social Inputs
		foreach( $this->social_options_manager as $key => $value ) {

			$args[] = array(
				'id' => $key,
				'title' => $value,
				'callback' => array( $this->callbacks_mngr, 'textField' ),
				'page' => 'rpd_restaurant_solution',
				'section' => 'rpd_social_index',
				'args' => array(
					'option_name' => 'rpd_restaurant_solution',
					'label_for' => $key,
					'class' => 'regular-text'
				)
			);

		}

		// API Inputs
		foreach( $this->api_options_manager as $key => $value ) {

			$args[] = array(
				'id' => $key,
				'title' => $value,
				'callback' => array( $this->callbacks_mngr, 'textField' ),
				'page' => 'rpd_restaurant_solution',
				'section' => 'rpd_api_index',
				'args' => array(
					'option_name' => 'rpd_restaurant_solution',
					'label_for' => $key,
					'class' => 'regular-text'
				)
			);

		}

		// Netopia Inputs
		foreach( $this->netopia_options_manager as $key => $value ) {

			if( $key === 'netopia_password' ) {
				$args[] = array(
					'id' => $key,
					'title' => $value,
					'callback' => array( $this->callbacks_mngr, 'passwordField' ),
					'page' => 'rpd_restaurant_solution',
					'section' => 'rpd_netopia_index',
					'args' => array(
						'option_name' => 'rpd_restaurant_solution',
						'label_for' => $key,
						'class' => 'regular-text'
					)
				);
			} else {
				$args[] = array(
					'id' => $key,
					'title' => $value,
					'callback' => array( $this->callbacks_mngr, 'textField' ),
					'page' => 'rpd_restaurant_solution',
					'section' => 'rpd_netopia_index',
					'args' => array(
						'option_name' => 'rpd_restaurant_solution',
						'label_for' => $key,
						'class' => 'regular-text'
					)
				);
			}

		}

		$this->settings->setFields( $args );
	}
}