<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Api\Callbacks;

class DistanceCallbacks
{

	public function distanceSectionManager()
	{
		esc_html_e( 'Create as many distance intervals as you want.', 'rpd-restaurant-solution' );
	}

	public function distanceSanitize( $input )
	{
		$output = get_option('rpd_manage_distances');

		if ( isset($_POST["remove"]) ) {
			unset($output[sanitize_text_field($_POST["remove"])]);

			return $output;
		}

		if ( count($output) == 0 ) {
			$output[$input['distance_type']] = $input;

			return $output;
		}

		foreach ($output as $key => $value) {
			if ($input['distance_type'] === $key) {
				$output[$key] = $input;
			} else {
				$output[$input['distance_type']] = $input;
			}
		}
		
		return $output;
	}

	public function textField( $args )
	{
		$name = $args['label_for'];
		//$next_id = $args['next_id'];
		$option_name = $args['option_name'];
		$value = '';

		if ( isset($_POST["edit_post"]) ) {
			$input = get_option( $option_name );
			$value = $input[sanitize_text_field($_POST["edit_post"])][$name];
		}/* else if( $name === 'distance_type' ) {
			$value = 'distance_' . $next_id;
		}*/

		echo '<input type="text" class="regular-text" id="' . $name . '" name="' . $option_name . '[' . $name . ']" value="' . $value . '" placeholder="' . $args['placeholder'] . '" required>';
	}

	public function checkboxField( $args )
	{
		$name = $args['label_for'];
		$classes = $args['class'];
		$option_name = $args['option_name'];
		$checked = false;

		if ( isset($_POST["edit_post"]) ) {
			$checkbox = get_option( $option_name );
			$checked = isset($checkbox[sanitize_text_field($_POST["edit_post"])][$name]) ?: false;
		}

		echo '<div class="' . $classes . '"><input type="checkbox" id="' . $name . '" name="' . $option_name . '[' . $name . ']" value="1" class="" ' . ( $checked ? 'checked' : '') . '><label for="' . $name . '"><div></div></label></div>';
	}

	public function hiddenField( $args )
	{
		$name = $args['label_for'];
		$next_id = $args['next_id'];
		$option_name = $args['option_name'];
		$value = '';

		if ( isset($_POST["edit_post"]) ) {
			$input = get_option( $option_name );
			$value = $input[sanitize_text_field($_POST["edit_post"])][$name];
		} else if( $name === 'distance_type' ) {
			$value = 'distance_' . $next_id;
		}

		echo '<input type="text" id="' . $name . '" name="' . $option_name . '[' . $name . ']" value="' . $value . '" placeholder="' . $args['placeholder'] . '">';
	}

}