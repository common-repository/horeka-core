<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Woocommerce;

use HorekaCore\Base\BaseController;
use HorekaCore\Api\PushNotification;

/**
* 
*/
class CompanyDiscount extends BaseController
{

    public function register() 
    {
        // If this option is active we use Horeka Core plugin only for importing orders to our API
        if( $this->activated( 'only_import_orders' ) ) {
            return;
        }
        
        if( !$this->activated( 'company_discount' ) ) {
            return;
        }

        add_action( 'woocommerce_checkout_update_order_review', array( $this, 'applyDiscount' ) );
        add_action( 'wp_logout', array( $this, 'unset' ) );
        add_filter( 'woocommerce_coupon_is_valid', array( $this, 'isCouponValid' ), 10, 2 );
    }

    function applyDiscount( $posted_data ) 
    {
        if ( !session_id() ) { 
            session_start();
        }
        		
        if( !isset($_SESSION['specialDiscount']) || $_SESSION['specialDiscount'] == "" || $_SESSION['specialDiscount'] === NULL ) {
            return;
        }
            
        if( ! is_user_logged_in() ) {
            return;
        }
        
        if( $this->activated('company_discount_app_mobile_only') ) {
            if( !WC()->session->get('showNavbar') ) {
                return;
            }
        }

        $post = array();
        $domains = array();
        
        $route = '/venue/integration/getcustomeproperties?venueId=0';
        $allowed_domains = '';
        $coupon_code = $this->getPluginOptions()['company_discount_coupon_code'];
        
        $response = PushNotification::makeNotification( $route );;
                    
        if( $response->status === 200 ) {				
            foreach( $response->data as $property ) {
                if( $property->customPropertyKey === 'emailgroup' && $property->customPropertyValue !== ''  ) {
                    $allowed_domains = $property->customPropertyValue;
                }
            }
        }
    
        $domains = explode(",", $allowed_domains);
    
        if( $coupon_code != "" ) {
    
            if ( WC()->cart->has_discount( $coupon_code ) ){
                WC()->cart->remove_coupon( $coupon_code );
            }
            
            $vars = explode('&', $posted_data);
    
            foreach ($vars as $k => $value){
                $v = explode('=', urldecode($value));
                $post[$v[0]] = $v[1];
            }
            
            $email_address = $post['billing_email'];
            $email_address = substr($email_address, strpos($email_address, "@") + 1);    
            
            if( ! in_array($email_address, $domains) ) {
                return;
            }
			
			WC()->cart->apply_coupon( $coupon_code );
		}
    }
                 
    function unset() 
    {
        if (!session_id()) {
            session_start();
        }
        
        if( isset( $_SESSION['specialDiscount'] ) ) {
            unset( $_SESSION['specialDiscount'] );
        }
    }

    function isCouponValid( $is_valid, $instance ) 
    {
        $coupon_code = '';
        $coupon_code = $instance->get_code();
        
        if( $coupon_code == 'specialdiscount' ) {
            if (!session_id()) { 
                session_start();
            }
                    
            if( ! isset($_SESSION['specialDiscount']) ) {
                $is_valid = false;
            }            

            if( $this->activated('company_discount_app_mobile_only') ) {
                if( !WC()->session->get('showNavbar') ) {
                    $is_valid = false;
                }
            }
        }
            
        return $is_valid; 
    }
    
}