<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Api\Callbacks;

use HorekaCore\Base\BaseController;

class ManagerCallbacks extends BaseController
{
	
	public function fieldSanitize( $field )
	{
		$output = array();

		foreach( $this->checkbox_options_manager as $key => $value ) {
			if( $field[$key] ) {
				$output[$key] = true;
			}
		}

		foreach( $this->text_options_manager as $key => $value ) {
			if( isset($field[$key]) && $field[$key] != '' ) {
				$output[$key] = $field[$key];
			}
		}

		foreach( $this->textarea_options_manager as $key => $value ) {
			if( isset($field[$key]) && $field[$key] != '' ) {
				$output[$key] = $field[$key];
			}
		}

		foreach( $this->api_options_manager as $key => $value ) {
			if( isset($field[$key]) && $field[$key] != '' ) {
				$output[$key] = $field[$key];
			}
		}

		foreach( $this->social_options_manager as $key => $value ) {
			if( isset($field[$key]) && $field[$key] != '' ) {
				$output[$key] = $field[$key];
			}
		}
		
		foreach( $this->netopia_options_manager as $key => $value ) {
			if( isset($field[$key]) && $field[$key] != '' ) {
				$output[$key] = $field[$key];
			}
		}

		return $output;
	}

	public function optionsManager()
	{
		esc_html_e( 'Manage the Features of this plugin by activating the checkboxes from the following list.', 'rpd-restaurant-solution' );
	}

	public function textsManager()
	{
		esc_html_e( 'Manage the Inputs of this plugin by adding/editing the values from the following list.', 'rpd-restaurant-solution' );
	}

	public function textareasManager()
	{
		esc_html_e( 'Manage the Textareas of this plugin by adding/editing the values from the following list.', 'rpd-restaurant-solution' );
	}

	public function socialManager()
	{
		esc_html_e( 'Manage the Social Settings of this plugin by adding/editing the values from the following list.', 'rpd-restaurant-solution' );
	}

	public function apiManager()
	{
		esc_html_e( 'Manage the API Settings of this plugin by adding/editing the values from the following list.', 'rpd-restaurant-solution' );
	}

	public function netopiaManager()
	{
		esc_html_e( 'Manage your Netopia credentials by adding/editing the values from the following list.', 'rpd-restaurant-solution' );
	}

	public function checkboxField( $args )
	{
		$name = $args['label_for'];
		$classes = $args['class'];
		$option_name = $args['option_name'];
		$checkbox = get_option( $option_name );

		echo '<div class="' . $classes . '"><input type="checkbox" id="' . $name . '" name="' . $option_name . '[' .$name . ']' . '" value="1"' . ( isset($checkbox[$name]) ? 'checked' : '' ) . '><label for="' . $name . '"><div></div></label></div>';
	}

	public function textField( $args )
	{
		$name = $args['label_for'];
		$classes = $args['class'];
		$option_name = $args['option_name'];
		$text = get_option( $option_name );

		echo '<input type="text" class="' . $classes . '" id="' . $name . '" name="' . $option_name . '[' .$name . ']' . '" value="' . $text[$name] . '">';
	}

	public function passwordField( $args )
	{
		$name = $args['label_for'];
		$classes = $args['class'];
		$option_name = $args['option_name'];
		$text = get_option( $option_name );

		echo '<input type="password" class="' . $classes . '" id="' . $name . '" name="' . $option_name . '[' .$name . ']' . '" value="' . $text[$name] . '">';
	}

	public function textareaField( $args )
	{
		$name = $args['label_for'];
		$classes = $args['class'];
		$option_name = $args['option_name'];
		$texarea = get_option( $option_name );

		echo '<textarea class="' . $classes . '" id="' . $name . '" name="' . $option_name . '[' .$name . ']' . '">' . $texarea[$name] . '</textarea>';
	}

}