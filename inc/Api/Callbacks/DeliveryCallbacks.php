<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Api\Callbacks;

class DeliveryCallbacks
{
    public function deliverySectionManager()
	{
		esc_html_e( 'Create as many delivery methods as you want.', 'rpd-restaurant-solution' );
	}

	public function deliverySanitize( $input )
	{
        $output = get_option('rpd_delivery_methods');
        $slug = sanitize_title( $input['method_name'] );

		if ( isset($_POST["remove"]) ) {
			unset($output[sanitize_text_field($_POST["remove"])]);

			return $output;
		}

		if ( count($output) == 0 ) {
			$output[$slug] = $input;

			return $output;
		}

		foreach ($output as $key => $value) {
			if( $input['slug'] === 'livrare-la-domiciliu' || $input['slug'] === 'ridicare-personala' || $input['slug'] === 'servire-la-restaurant' ) {
				$output[$input['slug']]['method_name'] = $input['method_name'];
				$output[$input['slug']]['method_status'] = $input['method_status'];
				$output[$input['slug']]['method_discount'] = str_replace( ' ', '', $input['method_discount']);
			} else if ($slug === $key) {
				$output[$key] = $input;
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