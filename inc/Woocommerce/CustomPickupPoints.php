<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Woocommerce;

use HorekaCore\Base\BaseController;

/**
* 
*/
class CustomPickupPoints extends BaseController
{

    public function register() 
    {
        // If this option is active we use Horeka Core plugin only for importing orders to our API
        if( $this->activated( 'only_import_orders' ) ) {
            return;
        }
        
        if( !$this->activated( 'custom_pickup_points' ) ) {
            return;
        }
        add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
		add_action( 'woocommerce_checkout_process', array( $this, 'changeAddressFieldStructure' ) );

        add_filter( 'woocommerce_form_field', array( $this, 'changePickupPointsFieldStructure' ), 10, 4 );
        add_filter( 'woocommerce_form_field', array( $this, 'changePickupPointsLocationsFieldStructure' ), 10, 4 );
    }

	public function scripts() 
	{
		
		wp_enqueue_style( 'rpd-custom-pickup-point', $this->plugin_url . 'assets/css/woocommerce/rpd-custom-pickup-point.min.css' );
		wp_enqueue_script('rpd-custom-pickup-point-js', $this->plugin_url . 'assets/js/woocommerce/rpd-custom-pickup-point.min.js', array(), false, true);
	}
	
    public function changePickupPointsFieldStructure( $field, $key, $args, $value )
    {
        if( $key === 'pickup_point' ) {
            
            if ( !empty( $args['options'] ) ) {

                $options = '';

                $payment_priority = (int) esc_attr( $args['priority'] ) + 10;

                foreach ( $args['options'] as $option_key => $option_text ) {

                    $options .= '<label class="' . esc_attr( $option_key ) . '">';

                        $options .= '<input type="radio" name="' . esc_attr( $key ) . '" value="' . esc_attr( $option_key ) . '">';
    
                        $options .= '<span>' . esc_html( $option_text ) . '</span>';
    
                    $options .= '</label>';
    
                }

                $field = '<p class="form-row form-row-wide ' . esc_attr( $key ) . ' form-group validate-required custom-pickup-point-section" id="' . esc_attr( $args['id'] ) . '" data-priority="' . esc_attr( $args['priority'] ) . '">';
                   
                    #$field .= '<label for="' . esc_attr( $key ) . '" class="control-label"> ' . $args['label'] . ' </label>';

                    $field .= '<span class="woocommerce-input-wrapper blue-cards">';
                    
                        $field .= $options;    

                    $field .= '</span>';

                $field .= '</p>';
            }

        }

        return $field;
    }

    public function changePickupPointsLocationsFieldStructure( $field, $key, $args, $value )
    {
        if( $key === 'billing_pickup_locations' ) {
            
            if ( !empty( $args['options'] ) ) {

                $options = '';

                $payment_priority = (int) esc_attr( $args['priority'] ) + 10;

                foreach ( $args['options'] as $option_key => $option_text ) {

                    $options .= '<label class="' . esc_attr( $option_key ) . '">';

                        $options .= '<input type="radio" name="' . esc_attr( $key ) . '" value="' . esc_attr( $option_key ) . '">';
    
                        $options .= '<span>' . esc_html( $option_text ) . '</span>';
    
                    $options .= '</label>';
    
                }

                $field = '<p class="form-row form-row-wide ' . esc_attr( $key ) . ' form-group validate-required custom-pickup-point-locations-section" id="' . esc_attr( $args['id'] ) . '" data-priority="' . esc_attr( $args['priority'] ) . '">';
                   
                    #$field .= '<label for="' . esc_attr( $key ) . '" class="control-label"> ' . $args['label'] . ' </label>';

                    $field .= '<span class="woocommerce-input-wrapper blue-cards">';
                    
                        $field .= $options;    

                    $field .= '</span>';

                $field .= '</p>';
            }

        }

        return $field;
    }

    public function changeAddressFieldStructure()
    {
        if( $this->activated('ignore_pickup_points') ) {
            return;
        }
        
        if( isset( $_POST['delivery_method'] ) && $_POST['delivery_method'] != 'ridicare-personala' ) {
            if( isset($_POST['pickup_point']) ) {
                $_POST['pickup_point'] = '';
            }
            if( isset($_POST['billing_pickup_locations']) ) {
                $_POST['billing_pickup_locations'] = '';
            }
        }
    }
    
}