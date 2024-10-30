<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Api\Callbacks;

class AreasCallbacks
{

	public function areasSectionManager()
	{
		esc_html_e( 'Select desired cities from the list below:', 'rpd-restaurant-solution' );
	}

	public function areasSanitize( $input )
	{
		$output = array();

		if( get_option('rpd_areas_importer') ) {
			delete_option( 'rpd_areas_importer' );
		}
		
		foreach ($input as $key => $value) {
			$output[$key] = $value;
		}
		
		return $output;
	}

	public function checkboxField( $args )
	{
		$name = $args['label_for'];
		$option_name = $args['option_name'];
		
		$checkbox = get_option( $option_name );
		$checked = isset($checkbox[$name]) ?: false;
		
		echo '<div><input type="checkbox" id="' . $name . '" name="' . $option_name . '[' . $name . ']" value="1" class="" ' . ( $checked ? 'checked' : '') . '><label for="' . $name . '"><div></div></label></div>';
	}

}