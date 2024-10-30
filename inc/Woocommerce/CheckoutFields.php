<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Woocommerce;

use HorekaCore\Api\PushNotification;
use HorekaCore\Base\BaseController;

/**
* 
*/
class CheckoutFields extends BaseController
{
    private $cities = array();
    
    private $areas = array();
    
    public function register() 
    {   
        $this->setCitiesAndAreas();
        
        // If this option is active we use Horeka Core plugin only for importing orders to our API
        if( $this->activated( 'only_import_orders' ) ) {
            return;
        }

        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'saveDeliveryTime' ) );
        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'saveDeliveryHour' ) );
        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'saveDeliveryMethods' ) );
        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'savePickupPoints' ) );
        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'saveCutleryCheckbox' ) );
        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'saveKeepUserInformation' ) );
        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'saveTermsAndConditions' ) );
        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'saveAreas' ) );
        add_action( 'woocommerce_after_checkout_validation', array( $this, 'overrideCheckoutTermsValidations'), 999, 2);
        add_action( 'woocommerce_review_order_before_submit', array( $this, 'insertTermsAndConditionInfo' ) );

        add_filter( 'woocommerce_checkout_fields', array( $this, 'printAreas') );
        add_filter( 'woocommerce_checkout_fields', array( $this, 'printDeliveryCheckbox' ) );
        add_filter( 'woocommerce_checkout_fields', array( $this, 'printDeliveryTime' ) );
        add_filter( 'woocommerce_checkout_fields', array( $this, 'printDeliveryHour' ) );
        add_filter( 'woocommerce_checkout_fields', array( $this, 'printDeliveryMethods' ) );
        add_filter( 'woocommerce_checkout_fields', array( $this, 'printPickupPoints' ) ); 
        add_filter( 'woocommerce_checkout_fields', array( $this, 'printWpPickupPoints' ) ); 
        add_filter( 'woocommerce_checkout_fields', array( $this, 'printCutleryCheckbox' ) );
        add_filter( 'woocommerce_checkout_fields', array( $this, 'printKeepUserInformation' ) );
        add_filter( 'woocommerce_checkout_fields', array( $this, 'printTermsAndConditions' ) );
        add_filter( "woocommerce_checkout_fields", array( $this, 'addClassesToOldCheckoutFields' ), 10, 1 );
        add_filter( 'woocommerce_checkout_fields', array( $this, 'overrideDefaultAddressFields'), 10, 1 );
        add_filter( 'woocommerce_form_field', array( $this, 'changeAreaFieldStructure' ), 10, 4 );
        add_filter( "woocommerce_checkout_fields", array( $this, "reorderCheckoutFields" ), 10, 1 );
    }

    private function setCitiesAndAreas()
    {
        $cities = $areas = array();
         
        $counter = 0;

        $all_cities = get_option( 'rpd_areas' );
        $selected_cities = get_option( 'rpd_areas_importer' );

        if( $selected_cities && !empty( $selected_cities ) ) {
            foreach( $selected_cities as $key => $value ) {
                if( array_key_exists( $key, $all_cities ) ) {
                    $cities[$counter]['judet'] = $all_cities[$key]['judet'];
                    $cities[$counter]['oras'] = $all_cities[$key]['oras'];
    
                    $areas[$key] = $all_cities[$key]['zone'];
    
                    $counter++;
                }
            }

            $this->cities = $cities;
        }

        if( !empty( $areas ) ) {
            foreach( $areas as $key => $value ) {
                $current_city_areas = explode(",", $value);
                $this->areas[$key] = $current_city_areas;
            }
        }
    }

    private function getCities()
    {
        if( !get_option( 'rpd_areas' ) || empty(get_option( 'rpd_areas' )) ) {
            return false;
        }

        if( !get_option( 'rpd_areas_importer' ) || empty(get_option( 'rpd_areas_importer' )) ) {
            return false;
        }

        return $this->cities;
    }

    private function getAreas()
    {
        if( !get_option( 'rpd_areas' ) || empty(get_option( 'rpd_areas' )) ) {
            return false;
        }

        if( !get_option( 'rpd_areas_importer' ) || empty(get_option( 'rpd_areas_importer' )) ) {
            return false;
        }

        return $this->areas;
    }

    private function getApiTermsAndGdprTexts()
    {
        $terms_properties = $available_languages = array();

        $is_active_multilanguage = false;

        $current_language = apply_filters( 'wpml_current_language', NULL );

        $route = '/venue/integration/getcustomeproperties?venueId=0';

        $result = PushNotification::makeNotification( $route );

        if( $result->status !== 200 ) {
            return false;
        }

        $properties = $result->data;

        foreach( $properties as $property ) {
            if( $property->customPropertyKey == 'availableLanguages' ) {
                if( $property->customPropertyValue != '' ) {
                    $available_languages = json_decode($property->customPropertyValue);
                    foreach( $available_languages as $key => $value ) {
                        if( $current_language && $current_language != 'ro' && $current_language == $key ) {
                            $is_active_multilanguage = true;
                        }
                    }
                }
            }
        }

        foreach( $properties as $property ) {
            if( $is_active_multilanguage ) {
                switch( $property->customPropertyKey ) {
                    case 'termeni_si_conditii' . '_' . $current_language:
                        $terms_properties['termeni_si_conditii'] = $property->customPropertyValue;
                        break;
                    case 'gdpr' . '_' . $current_language:
                        $terms_properties['gdpr'] = $property->customPropertyValue;
                        break;
                    case 'gdpr_email' . '_' . $current_language:
                        $terms_properties['gdpr_email'] = $property->customPropertyValue;
                        break;
                    case 'gdpr_sms' . '_' . $current_language:
                        $terms_properties['gdpr_sms'] = $property->customPropertyValue;
                        break;
                }
            } else {
                switch( $property->customPropertyKey ) {
                    case 'termeni_si_conditii':
                        $terms_properties['termeni_si_conditii'] = $property->customPropertyValue;
                        break;
                    case 'gdpr':
                        $terms_properties['gdpr'] = $property->customPropertyValue;
                        break;
                    case 'gdpr_email':
                        $terms_properties['gdpr_email'] = $property->customPropertyValue;
                        break;
                    case 'gdpr_sms':
                        $terms_properties['gdpr_sms'] = $property->customPropertyValue;
                        break;
                }
            }
        }

        return $terms_properties;
    }

    public function printAreas( $fields ) 
    {
        if( !$this->activated('display_areas') ) {
            return $fields;
        }

        if( !$this->getAreas() || empty( $this->getAreas() ) ) {
            return $fields;
        }

        $fields['billing']['areas'] = array(
            'label' => esc_html__('Area', 'rpd-restaurant-solution'),
            'required' => true,
            'type' => 'select',
            'class' => array('form-row-wide dropdown'),
            'priority' => 60,
            'options'       => array('Something'),
            'default' => 'now'
        );
    
        return $fields;
    }

    public function printDeliveryCheckbox( $fields ) 
    {
        if( !$this->activated('display_delivery_time') ) {
            return $fields;
        }

        $fields['billing']['delivery_hour_on'] = array(
            'label' => esc_html__('When will the delivery / service be made?', 'rpd-restaurant-solution'),
            'required' => true,
            'type' => 'radio',
            'class' => array('input-checkbox'),
            'options'       => array(
                'now' => esc_html__('As soon as possible', 'rpd-restaurant-solution'),
                'choose_date' => esc_html__('Choose the date and hour', 'rpd-restaurant-solution'),
            ),
            'default' => 'now'
        );
    
        return $fields;
    }

    public function printDeliveryTime( $fields ) 
    {
        if( !$this->activated('display_delivery_time') ) {
            return $fields;
        }

        $fields['billing']['delivery_time'] = array(
            'required' => false,
            'type' => 'text',
            'class' => array('delivery_time form-row-wide')
        );
    
        return $fields;
    }

    public function validateDeliveryTime()
    {
        if( !$this->activated('display_delivery_time') ) {
            return;
        }

        $parentCategoryId = 0;

        $route = '/venue/integration/getvenuedetails';
        $result = PushNotification::makeNotification( $route );
        
        if( $result->status === 200 ) {
            if( $result->data->daysIsActive == true ) {
                if( ! WC()->cart->is_empty() ){
                    foreach ( WC()->cart->get_cart() as $cart_item ) {
                        $productId = $cart_item['product_id'];
                        $currentProductCategories = get_the_terms( $productId, 'product_cat' );
                        $parentCategoryId = $currentProductCategories[0]->parent;
                    }
                }

                $route = '/venue/integration/getalloweddays?category='.$parentCategoryId;
                $result = PushNotification::makeNotification( $route );

                if( empty((array)$result->data->allowedDays) ) {
                    wc_clear_notices();
                    wc_add_notice( sprintf( esc_html__('Orders cannot be taken at this time.', 'rpd-restaurant-solution') ), 'error' );

                    return;
                }
            }
        }
    }

    public function saveDeliveryTime( $order_id ) 
    {   
        if ($_POST['delivery_time']) { 
            update_post_meta( $order_id, 'delivery_time', sanitize_text_field($_POST['delivery_time']));
        }
        if ($_POST['delivery_hour_on']) { 
            update_post_meta( $order_id, 'delivery_hour_on', sanitize_text_field($_POST['delivery_hour_on']));
        }
    }

    public function printDeliveryHour( $fields ) 
    {
        if( !$this->activated('display_delivery_time') ) {
            return $fields;
        }

        $fields['billing']['delivery_hour'] = array(
            'required' => false,
            'type' => 'select',
            'class' => array('delivery_hour form-row-wide'),
            'options'		=> array('10:00'),
        );
    
        return $fields;
    }

    public function saveDeliveryHour( $order_id )
    {
        if ($_POST['delivery_hour']) {
            update_post_meta( $order_id, 'delivery_hour', sanitize_text_field($_POST['delivery_hour']));
        }
    }

    public function printDeliveryMethods( $fields )
    {
        if( !$this->activated('display_delivery_methods') ) {
            return $fields;
        }
        
        if( ! $this->plugin_delivery_methods || empty($this->plugin_delivery_methods) ) {
            return $fields;
        }

        $deliveryMethods = array();
        foreach( $this->plugin_delivery_methods as $key => $value ) {
            if( $value['method_status'] === '1' ) {
                $deliveryMethods[$key] = $value['method_name'];
            }
        }

        $route = '/venue/integration/getvenuedetails';
        $result = PushNotification::makeNotification( $route );
    
        if( $result->status === 200 ){
            if( $result->data->daysIsActive == true ){
    
                $parentCategoryId = 0;	
                $route = '/venue/integration/getalloweddays?category='.$parentCategoryId;
                $result = PushNotification::makeNotification( $route );
                
                if( $result->data->deliveryType != "" ){
    
                    $deliveryMethods = array();
    
                    switch ($result->data->deliveryType) {
                        case 'Delivery':
                            $deliveryMethods['livrare-la-domiciliu'] = esc_html__('Delivery', 'rpd-restaurant-solution');
                            break;
                        case 'Take away':
                            $deliveryMethods['ridicare-personala'] = esc_html__('Take away', 'rpd-restaurant-solution');
                            break;
                        case 'Any':
                            $deliveryMethods['livrare-la-domiciliu'] = esc_html__('Delivery', 'rpd-restaurant-solution');
                            $deliveryMethods['ridicare-personala'] = esc_html__('Take away', 'rpd-restaurant-solution');
                            break;
                        default:
                            $deliveryMethods['livrare-la-domiciliu'] = esc_html__('Delivery', 'rpd-restaurant-solution');
                            $deliveryMethods['ridicare-personala'] = esc_html__('Take away', 'rpd-restaurant-solution');
                    }
    
                }
            }
        }

        $deliveryFirstValue = reset($deliveryMethods);

        $deliveryFirstKey = array_search ($deliveryFirstValue, $deliveryMethods);
    
        $fields['billing']['delivery_method'] = array(
            'label' => esc_html__('Delivery method', 'rpd-restaurant-solution'),
            'required' => false,
            'type' => 'select',
            'class' => array('delivery_method form-row-wide'),
            'options'       => $deliveryMethods
        );
    
        return $fields;
    }

    public function saveDeliveryMethods( $order_id ) 
    {
        if ($_POST['delivery_method']) {
            update_post_meta( $order_id, 'delivery_method', sanitize_text_field($_POST['delivery_method']));
        }
    }

    public function printPickupPoints( $fields )
    {
        $parentCategoryId = 0;
        $pickupPoints = array();
	    $pickupPointsDropdown = array();

        if( ! WC()->cart->is_empty() ){
            foreach ( WC()->cart->get_cart() as $cart_item ) {
                $productId = $cart_item['product_id'];
                $currentProductCategories = get_the_terms( $productId, 'product_cat' );
                $parentCategoryId = $currentProductCategories[0]->parent;
            }
        }

        $route = '/venue/integration/getalloweddays?category='.$parentCategoryId;
        $result = PushNotification::makeNotification( $route );
    
        if( $result->status === 200 ) {
            if( $result->data->deliveryPoints !== '' ) {
    
                $pickupPoints = explode( ',', $result->data->deliveryPoints );
    
                $deliveryMethods = array();

                if( !empty( $pickupPoints ) ) {
                    foreach( $pickupPoints as $value ) {
                        $pickupPointsDropdown[sanitize_title($value)] = $value;
                    }
                }

                if( !empty( $pickupPointsDropdown ) ) {
                    $fields['billing']['pickup_point'] = array(
                        'label' => __('Pickup point', 'rpd-restaurant-solution'),
                        'required' => false,
                        'type' => 'select',
                        'class' => array('pickup_point form-row-wide'),
                        'options' => $pickupPointsDropdown
                    );
                
                    return $fields;
                }
            }
        }

        return $fields;
    }

    public function printWpPickupPoints( $fields )
    {   
        if( !$this->activated('display_delivery_points') ) {
            return $fields;
        }
        
        
        
        if( ! $this->plugin_delivery_points || empty($this->plugin_delivery_points) ) {
            return $fields;
        }


        $deliveryPoints = array();
        foreach( $this->plugin_delivery_points as $key => $value ) {
            if( $value['method_status'] === '1' ) {
                $deliveryPoints[$key] = $value['method_name'];
            }
        }
    
        $fields['billing']['pickup_point'] = array(
            'label' => esc_html__('Pickup point', 'rpd-restaurant-solution'),
            'required' => false,
            'type' => 'select',
            'class' => array('pickup_point form-row-wide'),
            'options'       => $deliveryPoints
        );
    
        return $fields;
    }

    public function savePickupPoints( $order_id ) 
    {
        if ($_POST['pickup_point']) {
            update_post_meta( $order_id, 'pickup_point', sanitize_text_field($_POST['pickup_point']));
        }
    }

    public function printCutleryCheckbox( $fields )
    {
        if( !isset($this->getPluginOptions()['cutlery_text']) || $this->getPluginOptions()['cutlery_text'] == '' ) {
            return $fields;
        }
        
        $fields['billing']['wants_cutlery'] = array(
            'label' => esc_html__($this->getPluginOptions()['cutlery_text'], 'rpd-restaurant-solution'),
            'required' => false,
            'type' => 'checkbox',
            'class' => array('input-checkbox')
        );
    
        return $fields;
    }

    public function saveCutleryCheckbox( $order_id ) 
    {
        if ($_POST['wants_cutlery']) {
            update_post_meta( $order_id, 'wants_cutlery', sanitize_text_field($_POST['wants_cutlery']));
        }
    }

    public function printKeepUserInformation( $fields )
    {
        if( !$this->activated('display_keep_information') ) {
            return $fields;
        }
        
        if( is_user_logged_in() ) {
            return $fields;
        }

        $fields['billing']['my_checkbox'] = array(
            'label' => esc_html__('Keep my data for the next order', 'rpd-restaurant-solution'),
            'required' => false,
            'type' => 'checkbox',
            'class' => array('input-checkbox')
        );
    
        return $fields;
    }

    public function saveKeepUserInformation( $order_id ) 
    {
        if ($_POST['my_checkbox']) {
            update_post_meta( $order_id, 'save_info', sanitize_text_field($_POST['my_checkbox']));
        }
    }

    public function printTermsAndConditions( $fields ) 
    {
        if( is_user_logged_in() ) {
            return $fields;
        }
        
        if( !$this->activated('display_terms_and_conditions') ) {
            return $fields;
        }

        $fields['billing']['gdpr_email'] = array(
            'label' => __('Email - I agree to receive commercial information by electronic means.', 'rpd-restaurant-solution'),
            'type' => 'checkbox',
            'class' => array('input-checkbox')
        );

        $fields['billing']['gdpr_sms'] = array(
            'label' => __('SMS - I agree to receive commercial information by phone calls and SMS.', 'rpd-restaurant-solution'),
            'type' => 'checkbox',
            'class' => array('input-checkbox')
        );

        return $fields;
    }

    public function saveTermsAndConditions( $order_id ) 
    {
        if ($_POST['terms_and_conditions']) {
            update_post_meta( $order_id, 'terms_and_conditions', sanitize_text_field($_POST['terms_and_conditions']));
        }
        if ($_POST['gdpr']) {
            update_post_meta( $order_id, 'gdpr', sanitize_text_field($_POST['gdpr']));
        }
        if ($_POST['gdpr_email']) {
            update_post_meta( $order_id, 'gdpr_email', sanitize_text_field($_POST['gdpr_email']));
        }
        if ($_POST['gdpr_sms']) {
            update_post_meta( $order_id, 'gdpr_sms', sanitize_text_field($_POST['gdpr_sms']));
        }
    }

    public function saveAreas( $order_id ) 
    {
        if ($_POST['areas']) {
            update_post_meta( $order_id, 'billing_area', sanitize_text_field($_POST['areas']));
        }
    }

    public function overrideCheckoutTermsValidations( $fields, $errors )
    {
        if( !empty( $errors->get_error_codes() ) ) {
            foreach( $errors->get_error_codes() as $code ) {
                if( $code == 'delivery_hour_on_required' ) {
                    $errors->remove( $code );
                    $errors->add( 'delivery_hour_on_required', __('Please select when you want to receive/pickup your order.', 'rpd-restaurant-solution') );
                }
                
                if( $code == 'terms_and_conditions_required' ) {
                    $errors->remove( $code );
                    $errors->add( 'delivery_hour_on_required', __('Please accept the website\'s terms.', 'rpd-restaurant-solution') );
                }

                if( $code == 'gdpr_required' ) {
                    $errors->remove( $code );
                    $errors->add( 'delivery_hour_on_required', __('Please accept the website\'s gdpr terms.', 'rpd-restaurant-solution') );
                }
            }
        }
    }

    public function addClassesToOldCheckoutFields( $fields )
    {
        if( $this->activated('checkout_v2') ) {
            return $fields;
        }

        if( !$this->activated('display_delivery_time') ) {
            return $fields;
        }

        $fields['billing']['delivery_hour_on']['class'] = array('old-checkout');
        $fields['billing']['delivery_time']['class'] = array('old-checkout');
        $fields['billing']['delivery_hour']['class'] = array('old-checkout');

        $fields['billing']['billing_first_name']['autofocus'] = false;

        return $fields;        
    }

    public function overrideDefaultAddressFields( $fields ) 
    {
        if( !$this->activated( 'display_areas' ) ) {
            return $fields;
        }
        
        if( !$this->getCities() || empty( $this->getCities() ) ) {
            return $fields;
        }

        $city_fields = array();
        $cities = $this->getCities();

        foreach( $cities as $city ) {
            $city_fields[sanitize_title($city['oras'])] = $city['oras'];
        }

        $fields['billing']['billing_city']['type'] = 'select';
        $fields['billing']['billing_city']['class'] = array('form-row-wide dropdown');
        $fields['billing']['billing_city']['priority'] = 50;
        $fields['billing']['billing_city']['default'] = array_values($city_fields)[0];
        $fields['billing']['billing_city']['options'] = $city_fields;  
        
        return $fields;
    }

    public function changeAreaFieldStructure( $field, $key, $args, $value )
    {
        if( !$this->activated('display_areas') ) {
            return $field;
        }

        if( !$this->getAreas() || empty( $this->getAreas() ) ) {
            return $field;
        }

        $areas = $this->getAreas();

        if( $key === 'areas' ) {
            
            if ( !empty( $args['options'] ) ) {

                $options = '';

                foreach ( $areas as $keys => $values ) {

                    foreach( $values as $value ) {

                        $options .= '<option class="' . esc_attr( $keys ) . ' ' . sanitize_title( $value ) .'" value="' .  $value  . '">' . $value . '</option>';

                    }
    
                }

                $field = '<p class="form-row dropdown validate-required form-row-wide ' . esc_attr( $key ) . ' form-group woocommerce-validated" id="' . esc_attr( $args['id'] ) . '" data-priority="' . esc_attr( $args['priority'] ) . '">';
                   
                    $field .= '<label for="' . esc_attr( $key ) . '" class="control-label"> ' . $args['label'] . ' <abbr class="required" title="' . __('required', 'woocommerce') . '">*</abbr> </label>';

                    $field .= '<span class="woocommerce-input-wrapper">';

                        $field .= '<select name="' . esc_attr( $key ) . '" class="select form-control ' . esc_attr( $key ) . '" autocomplete="" data-placeholder="">';
                    
                            $field .= $options;

                        $field .= '</select>';

                    $field .= '</span>';

                $field .= '</p>';
            }

        }

        return $field;
    }

    public function reorderCheckoutFields( $fields ) 
    {
        $billing_order = array(
            'delivery_method',
            'pickup_point',
            'billing_first_name',
            'billing_last_name',
            'billing_address_1',
            'billing_city',
            'billing_phone',
            'billing_email',
            'areas',
            'delivery_hour_on',
            'delivery_time',
            'delivery_hour',
            'my_checkbox',
            'wants_cutlery',
            'terms_and_conditions',
            'termeni_si_conditii',
            'gdpr',
            'gdpr_email',
            'gdpr_sms'
        );

        $count = 0;
        $priority = 10;

        foreach($billing_order as $field_name) {
            if( isset( $fields['billing'][$field_name] ) ) {
                if( $fields['billing'][$field_name] || $field_name == 'order_comments' ) {
                    $count++;
                    $fields['billing'][$field_name]['priority'] = $count * $priority;
                }
            }
        }

        unset( $fields['billing']['terms_and_conditions']['validate'] );
        unset( $fields['billing']['termeni_si_conditii']['validate'] );
        unset( $fields['billing']['gdpr']['validate'] );

        return $fields;
    }

    public function insertTermsAndConditionInfo()
    {   
        if( get_privacy_policy_url() === '' ) {
            return;
        }

        $terms_page = get_privacy_policy_url();

        echo sprintf( __('<p class="terms-and-conditions-info">By placing the order you agree to the <a href="%s" target="_blank">Terms and Conditions</a>.</p>', 'rpd-restaurant-solution'), $terms_page );
    }

}