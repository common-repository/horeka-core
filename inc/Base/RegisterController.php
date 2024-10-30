<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Base;

use \WC_CART;
use HorekaCore\Base\ErrorLog;
use HorekaCore\Base\BaseController;
use HorekaCore\Api\PushNotification;

/**
* 
*/
class RegisterController extends BaseController
{
    public function register() 
    {
        // If this option is active we use Horeka Core plugin only for importing orders to our API
        if( $this->activated( 'only_import_orders' ) ) {
            return;
        }

        add_action( 'woocommerce_register_form_start', array( $this, 'registerExtraFields' ) );
        add_action( 'woocommerce_register_post', array( $this, 'validateExtraRegisterFields' ), 10, 3 );
        add_action( 'woocommerce_created_customer', array( $this, 'saveExtraRegisterFields' ), 10, 1 );
        add_action( 'woocommerce_created_customer', array( $this, 'createApiUser' ), 11, 1 );
    }

    public function registerExtraFields()
    { ?>
        <p class="form-row form-row-wide">
            <label for="reg_billing_first_name"><?php _e( 'First name', 'woocommerce' ); ?></label><input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" value="<?php if ( ! empty( $_POST['billing_first_name'] ) ) esc_attr_e( $_POST['billing_first_name'] ); ?>" />
        </p>

        <p class="form-row form-row-wide">
            <label for="reg_billing_last_name"><?php _e( 'Last name', 'woocommerce' ); ?></label><input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" value="<?php if ( ! empty( $_POST['billing_last_name'] ) ) esc_attr_e( $_POST['billing_last_name'] ); ?>" />
        </p>

        <p class="form-row form-row-wide">
            <label for="reg_billing_phone"><?php _e( 'Phone', 'woocommerce' ); ?><span class="required">*</span></label><input type="number" class="input-text" name="billing_phone" id="reg_billing_phone" value="<?php if ( ! empty( $_POST['billing_phone'] ) ) esc_attr_e( $_POST['billing_phone'] ); ?>" />
        </p>
    <?php }
    
    public function validateExtraRegisterFields( $username, $email, $validation_errors )
    {
        if (isset($_POST['billing_phone']) && empty($_POST['billing_phone']) ) {
            $validation_errors->add('billing_phone_error', __('Phone number is required.', 'woocommerce'));
        }
        
        return $validation_errors;
    }
    
    public function saveExtraRegisterFields( $customer_id )
    {
        if (isset($_POST['billing_first_name'])) {
            update_user_meta($customer_id, 'billing_first_name', sanitize_text_field($_POST['billing_first_name']));
            update_user_meta($customer_id, 'first_name', sanitize_text_field($_POST['billing_first_name']));
        }
        
        if (isset($_POST['billing_last_name'])) {
            update_user_meta($customer_id, 'billing_last_name', sanitize_text_field($_POST['billing_last_name']));
            update_user_meta($customer_id, 'last_name', sanitize_text_field($_POST['billing_last_name']));
        }

        if (isset($_POST['billing_phone'])) {
            update_user_meta($customer_id, 'billing_phone', sanitize_text_field($_POST['billing_phone']));
        }

        if (isset($_POST['password'])) {
            WC()->session->set( 'user_pass', $_POST['password'] );
        }
    }

    public function createApiUser( $user_id )
    {   
        if( defined('REST_REQUEST') ) {
			return;
		}

        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }
        
        $route = '/user/integration/registeruserandcustomer';
        $api_key = $this->getPluginOptions()['api_key'];
        
        if( $api_key === null || $api_key == '' ) {
            wp_delete_user($user_id);

            return;
        }
        
        $user = get_user_by( 'ID', $user_id );

        if( !$user->data->user_email || null === WC()->session->get( 'user_pass' ) || WC()->session->get( 'user_pass' ) == '' ) {
            wp_delete_user($user_id);

            return;
        }
       
        $data = array(
            'FirstName' => get_user_meta( $user_id, 'first_name', true ),
            'LastName' => get_user_meta( $user_id, 'last_name', true ),
            'Password' => WC()->session->get( 'user_pass' ),
            'Email' => $user->data->user_email,
            'Phone' => get_user_meta( $user_id, 'billing_phone', true ),
            'UserName' => $user->data->user_email,
            'ExternalUserId' => $user_id,
            'VenueApiKey' => $api_key
        );

        $response = PushNotification::makeNotification( $route, $data );
        
        if( $response->status !== 200 ) {
            wp_delete_user($user_id);
        }

        WC()->session->set( 'user_pass', null );
    }   

}