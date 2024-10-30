<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Api\Callbacks;

class DeliveryPointsCallbacks
{
    public function deliverySectionManager()
	{
		esc_html_e( 'Create as many delivery points as you want.', 'rpd-restaurant-solution' );
	}

	public function deliverySanitize( $input )
	{
        $output = get_option('rpd_delivery_points');
        $slug = sanitize_title( $input['method_name'] );

		if( $output == "" ) {
			$output = array();
		}

		if ( isset($_POST["remove"]) ) {
			unset($output[sanitize_text_field($_POST["remove"])]);

			return $output;
		}

		if ( count($output) == 0 ) {
			$input['slug'] = $slug;
			$output[$slug] = $input;

			return $output;
		}

		foreach ($output as $value) {			
			if( $input['slug'] == $value['slug']  ) {
				$output[$input['slug']] = $input;
			} else {
				$input['slug'] = $slug;
				$output[$slug] = $input;
			}
		}
	
		return $output;
	}

	public function textField( $args )
	{
		$name = $args['label_for'];
		$option_name = $args['option_name'];

		if ( isset($_POST["edit_post"]) ) {
			$input = get_option( $option_name );
			$value = $input[sanitize_text_field($_POST["edit_post"])][$name];
		}

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
		$option_name = $args['option_name'];
		$value = '';

		if ( isset($_POST["edit_post"]) ) {
			$input = get_option( $option_name );
			$value = $input[sanitize_text_field($_POST["edit_post"])][$name];
		}

		echo '<input type="text" id="' . $name . '" name="' . $option_name . '[' . $name . ']" value="' . $value . '" placeholder="' . $args['placeholder'] . '">';
	}

}
