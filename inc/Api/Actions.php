<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Api;

use \DateTime;
use \stdClass;
use \WP_Query;
use \DateTimeZone;

use HorekaCore\Base\ErrorLog;
use HorekaCore\Base\BaseController;
use HorekaCore\Api\PushNotification;

/**
* 
*/
class Actions extends BaseController
{
    public function register() 
    {
        /**
         * Actions
         */
        add_action( 'woocommerce_order_details_after_order_table', array( $this, 'insertOrder' ) );
        add_action( 'woocommerce_checkout_process', array( $this, 'checkRestaurantAvailability' ) );
        add_action( 'admin_post_nopriv_api_login', array( $this, 'loginThroughApi' ) );
        add_action( 'admin_post_api_login', array( $this, 'loginThroughApi' ) );
        add_action( 'init', array( $this, 'getUserDetails' ), 999 );
        add_action( 'init', array( $this, 'getMerchantId' ), 998 );

        /**
         * Filters
         */
        add_filter( 'rtb_insert_booking', array( $this, 'insertReservation' ) );
    }

    public function getLiteSiteRoot()
    {
        $route = '/venue/integration/getvenuedetails';
        $result = PushNotification::makeNotification( $route );
        
        if( $result->status === 200 ) {
            if( $result->data->wooCommerce !== NULL ) {
                $woomcommerceSettings = json_decode($result->data->wooCommerce);
                if( $woomcommerceSettings->ftpMenuRoot != "" ) {
                    return $woomcommerceSettings->ftpMenuRoot;
                }
            }
        }

        return false;
    }

    public function insertOrder( $order )
    {
        if( $this->isAccountPage( 'view-order' ) ) {
            return;
        }

        $allowed_domains = '';
        $valid_transaction = false;
        $payment_method = get_post_meta( $order->get_id(), '_payment_method', true );
        $merchant_id = ( isset($_GET['orderId']) && $_GET['orderId'] != '' ? sanitize_text_field($_GET['orderId']) : '' );
        $netopia_settings = $this->getWoocommerceNetopiapaymentsSettings();
        $current_language = apply_filters( 'wpml_current_language', NULL );
        $getCustomPropertiesRoute = '/venue/integration/getcustomeproperties?venueId=0';

        if( ( $payment_method === 'netopiapayments' && $merchant_id != '' ) ) {
            update_post_meta( $order->get_id(), 'merchant_id', $merchant_id );
            
            if( $netopia_settings['environment'] === 'yes' ) {
                update_post_meta( $order->get_id(), 'netopia_environment', 'sandbox' );
            }

            $valid_transaction = $this->isValidTransaction( $merchant_id, $order->get_id() );
        }

        if ( $order->has_status( 'processing' ) || $order->has_status( 'completed' ) || ( $order->has_status( 'on-hold' ) && $payment_method != 'netopiapayments' ) || $valid_transaction ) {
            
            $route = '/order/integration/addorder';
            $data = array();
            $deliveryMethodId = 0;
            $alreadyTried = false;

            $data = $this->generateOrderData( $order->get_id() );
            
            $response = PushNotification::makeNotification($route, $data);

            if( $response->status === 200 ) {

                echo '<script type="text/javascript">',
                        'jQuery( document ).ready(function() {',				
                        'try{lsHelper().clearBasket();} catch(e){}',
                        'var canLogin = false;',
                        'setInterval(function(){',
                            'if(canLogin === false && window.webkit){',
                                'canLogin = true;',
                                'window.webkit.messageHandlers.cordova_iab.postMessage(JSON.stringify({"my_message" : "success"}));',
                            '}',
                        '}, 1000);',
                        '});',
                    '</script>';
                
                if( $response->data > 0 && $data['customer']['email'] !== NULL && $data['customer']['password'] != NULL ){ // when an account is created from the checkout process
                    
                    $custom_properties = PushNotification::makeNotification($getCustomPropertiesRoute);
					
					if( $custom_properties->status === 200 ) {				
					
						foreach( $custom_properties->data as $property ) {
							if( $property->customPropertyKey === 'emailgroup' && $property->customPropertyValue !== ''  ) {
								$allowed_domains = $property->customPropertyValue;
							}
						}
						
						if( $allowed_domains != '' ) {
							$apiKey = $this->getPluginOptions()['api_key'];
							$domains = explode(",", $allowed_domains);
							
							$email_address = substr($data['customer']['email'], strpos($data['customer']['email'], "@") + 1);
							
							if( in_array($email_address, $domains) ) {
								$to = $data['customer']['email'];
								$subject = __('Activation Link', 'rpd-restaurant-solution');
								$body = '<a href="' . home_url() . '/activate-account?apiKey='.$apiKey.'&userId='.$response->data.'&phone='.$data['customer']['phone'] . '">' . home_url() . '/activate-account?apiKey='.$apiKey.'&userId='.$response->data.'&phone='.$data['customer']['phone'] . '</a>';
								
								$headers[] = 'Content-Type: text/html; charset=UTF-8';
								$headers[] = 'From: ' . get_bloginfo() . ' <'.$this->getPluginOptions()['email_address'].'>';
								 
								wp_mail( $to, $subject, $body, $headers );
							}
						}
					}

                    $route = '/venue/integration/getvenuedetails';
                    $result = PushNotification::makeNotification($route);
                    
                    if( $result->status === 200 ) {
                        $venueId = $result->data->id;
                    
                        echo '<script type="text/javascript">',
                                'jQuery( document ).ready(function() {',
                                'lsHelper().generateDeviceId();',
                                '_venueId='.$venueId.';',
                                'try{lsHelper().loginUser("' . $data['customer']['email'] . '", "' . $data['customer']['password'] . '");} catch(e){console.log(e)}',
                                'var canLogin = false;',
                                'setInterval(function(){',
                                    'if( canLogin === false && window.webkit ){',
                                        'canLogin = true;',
                                        'window.webkit.messageHandlers.cordova_iab.postMessage(JSON.stringify({"my_message" : "login", "email" : "' . $data['customer']['email'] . '", "password" : "' . $data['customer']['password'] . '"}));',
                                        'window.webkit.messageHandlers.cordova_iab.postMessage(JSON.stringify({"my_message" : "success"}));',
                                    '}',
                                '}, 1000);',
                                '});',
                            '</script>';
                    }

                }

                if( $this->activated('only_light_version') && $this->activated('custom_checkout') ) {
                    if( $this->getLiteSiteRoot() ) {
                        $appMenuRoot = $this->getLiteSiteRoot();
                        echo '<script type="text/javascript">',
                            'jQuery( document ).ready(function() {',
                                'window.parent.location.href="' . home_url() . "/" . $appMenuRoot . '/thank-you.html?orderId=' . ( !is_null($this->plugin_options['reference_id_prefix']) && $this->plugin_options['reference_id_prefix'] !== '' ? $this->plugin_options['reference_id_prefix'] . '-' : '' ) . $order->get_id() . '&apiKey=' . $this->getPluginOptions()['api_key'] . '&language=' . ( $current_language && $current_language != null ? $current_language : 'ro' ) . '";',
                            '});',
                         '</script>';
                    }
                }

                // Add API user_id to the Wordpress database
                if ( !add_user_meta(get_current_user_id(), 'apiuserid', $response->data, true ))
                update_user_meta( get_current_user_id(), 'apiuserid', $response->data );
            } elseif( !$alreadyTried ) {
                $alreadyTried = true;
                $log_data['error_title'] = $route;
                $log_data['error_content'] = 'An error has occurred during the connection between WordPress and API for this WordPress ID: ' . ( $order->get_order_number() !== NULL ? $order->get_order_number() : "" );

                $log_meta['customer_ip'] = ( !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '' );
                $log_meta['user_id'] = ( get_current_user_id() ? get_current_user_id() : "" );
                $log_meta['status'] = ( $response->status != NULL ? $response->status : "" );
                
                ErrorLog::log($log_data, $log_meta);

                // Try again to add
                if( $data['customer']['email'] != NULL && $data['customer']['password'] != NULL ) {
                    $newResponse = PushNotification::makeNotification($route, $data);

                    if( $newResponse->status !== 200 ) {
                        $log_meta['status'] = ( $newResponse->status != NULL ? $newResponse->status : "" );
                        ErrorLog::log($log_data, $log_meta);
    
                        if( get_current_user_id() !== 0 && !current_user_can( 'manage_options' ) ){
                            // Delete user from the Wordpress database
                            wp_delete_user(get_current_user_id());
                        }
                    }
                }
                
            }

            // Remove the currentUserP variable from user meta
            if( get_post_meta( $order->get_id(), 'currentUserP', true ) && $order->get_id() != NULL ) {
                delete_post_meta( $order->get_id(), 'currentUserP' ); 
            }

        }
    }

    public function insertReservation( $reservation )
    {
        $route = '/booktable/integration/addreservation';
        $data = array();

        $reservationDateUTC = $this->convertDateToUTC( $reservation->date );
        
        $fullName = $reservation->name;
        $fullName = explode(" ",$fullName);    
        $message = $reservation->message;

        if( $message != "" ) {
            $message = str_replace( array('<br />', '<br/>'), ' ', $message );
        }
        
        $data['reservationId'] = $reservation->ID;
        $data['firstName'] = ( isset($fullName[0]) ? $fullName[0] : "-" );
        $data['lastName'] = ( isset($fullName[1]) ? $fullName[1] : "-" );
        $data['bookDate'] = $reservationDateUTC;
        $data['noOfChairs'] = $reservation->party;
        $data['phone'] = $reservation->phone;
        $data['email'] = $reservation->email;
        $data['details'] = ( $message != "" ? $message : "" );
        $data['type'] = "Website";
        $data['destinationType'] = "Restaurant";
                
        $response = PushNotification::makeNotification($route, $data);	
        
        if( $response->status !== 200 ){
            $log_data['error_title'] = $route;
            $log_data['error_content'] = 'An error has occurred during the connection between WordPress and API for this WordPress ID: ' . ( $reservation->ID !== NULL ? $reservation->ID : "" );

            $log_meta['customer_ip'] = ( !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '' );
            $log_meta['user_id'] = ( get_current_user_id() ? get_current_user_id() : "" );
            $log_meta['status'] = ( $response->status != NULL ? $response->status : "" );
            
            ErrorLog::log($log_data, $log_meta);
        }
    }

    public function checkRestaurantAvailability(){
        $route = '/venue/integration/getvenuedetails';
        
        $result = PushNotification::makeNotification($route);
        
        if( $result->status === 200 ){
            $websiteSetup = json_decode($result->data->websiteSetup);
            $interval = $websiteSetup->interval;
            
            $currentDay = strftime("%A");
            
            $startHour = $interval->{"Start".$currentDay};
            $endHour = $interval->{"End".$currentDay};
            
            if( $startHour != "" && $endHour != "" ){
                
                $hourIntervalMessage = ( $websiteSetup->hourIntervalMessage != "" ? $websiteSetup->hourIntervalMessage : "Ne pare rau insa va aflati in afara orelor de livrare. Preluam comenzi doar in intervalul <b>$startHour</b> - <b>$endHour</b>." ) ;
                $date = new DateTime();
                $date->setTimezone(new DateTimeZone('Europe/Bucharest'));
                
                $currentHour = $date->format('H:i');
                
                if( strtotime($currentHour) < strtotime($startHour) || strtotime($currentHour) > strtotime($endHour) ) {
                    wc_add_notice( sprintf( $hourIntervalMessage ), 'error' );
                }
                
            }
        }else{
            $log_data['error_title'] = $route;
            $log_data['error_content'] = 'An error has occurred during the connection between WordPress and API.';
    
            $log_meta['customer_ip'] = ( !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '' );
            $log_meta['user_id'] = ( get_current_user_id() ? get_current_user_id() : "" );
            $log_meta['status'] = ( $response->status != NULL ? $response->status : "" );
            
            ErrorLog::log($log_data, $log_meta);
        }
        
    }

    public function loginThroughApi()
    {
        $route = '/user/integration/checkuserexist';
        $data = array();
        $apiResponse = '';
        $data['customer']['email'] = sanitize_user($_POST['username']);
        $data['customer']['password'] = sanitize_text_field($_POST['password']);
        
        $redirect_url = ( $_POST['redirect'] != "" ? sanitize_text_field($_POST['redirect']) : home_url() );
            
        if( !empty($data) ) {
            $apiResponse = PushNotification::makeNotification($route, $data);
        }

        if( $apiResponse->status === 200 ){
            if( $apiResponse != '' && $apiResponse->data != null ){

                echo '<script type="text/javascript">',
                    'try{window.top.loginUser("' . $data['customer']['email'] . '", "' . $data['customer']['password'] . '");',
                    'window.top.lsHelper().clearBasket();} catch(e){}',
                    'var canLogin = false;',
                    'setInterval(function(){',
                        'if( canLogin === false && window.webkit ){',
                            'canLogin = true;',
                            'window.webkit.messageHandlers.cordova_iab.postMessage(JSON.stringify({"my_message" : "login", "email" : "' . $data['customer']['email'] . '", "password" : "' . $data['customer']['password'] . '"}));',
                            'window.webkit.messageHandlers.cordova_iab.postMessage(JSON.stringify({"my_message" : "success"}));',
                        '}',
                    '}, 1000);',
                    '</script>';
                
                $refferenceId = $apiResponse->data;
                $internalUserId = get_users( array(
                    "meta_key" => "apiuserid",
                    "meta_value" => $refferenceId,
                    "fields" => "ID"
                ) )[0];
                
                $userDetails = get_userdata((int) $internalUserId)->data;
                
                $user_login = 'guest';
                $user_id = $userDetails->ID;

                if( $apiResponse->message != NULL && $apiResponse->message != '' ) {
                    if (!session_id()) {
                        session_start();
                    }
                    
                    $_SESSION["specialDiscount"] = $apiResponse->message;
                }
                
                if( !is_user_logged_in() ){
                    wp_clear_auth_cookie();
                    wp_set_current_user($user_id);
                    wp_set_auth_cookie($user_id);
                }
                
                wp_redirect( $redirect_url );
                exit;
                
            }
        }else{
            $log_data['error_title'] = $route;
            $log_data['error_content'] = 'An error has occurred during the connection between WordPress and API.';

            $log_meta['customer_ip'] = ( !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '' );
            $log_meta['user_id'] = ( get_current_user_id() ? get_current_user_id() : "" );
            $log_meta['status'] = ( $response->status != NULL ? $response->status : "" );
            
            ErrorLog::log($log_data, $log_meta);
        }
    }

    public function getUserDetails()
    {
        
        if( !isset( $_GET['token'] ) ) {
            return;
        }
    
        $address = '';
        $city = '';
        $token = sanitize_text_field($_GET['token']);
        
        $cookie_args = array(
            'expires' => time()+ 60 *3600,
            'path' => '/',
            'samesite' => 'Lax'
        );
        
        setcookie('token', $token, $cookie_args);
    
        $data['token'] = $token;
            
        $route = '/user/integration/readssotoken';
        
        $responseObject = PushNotification::makeNotification($route, $data);

        if( $responseObject->status === 200 ) {
            if( $responseObject->data->userExternalId != NULL ) {
                // Logout any user logged in
                if( is_user_logged_in() ) {
                    wp_logout();
                }

                if( $responseObject->data->user->specialDescription != NULL && $responseObject->data->user->specialDescription != '' ) {
                    if (!session_id()) { session_start(); }
                    $_SESSION["specialDiscount"] = $responseObject->data->user->specialDescription;
                }
                
                // Delete user from cookies
                setcookie("user", "", time() - 3600);
                
                $user_login = 'guest';
                $user_id = $responseObject->data->userExternalId;
                
                wp_clear_auth_cookie();
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);

                if( isset($responseObject->data->user->address->address) && $responseObject->data->user->address->address != '' ) {
                    $complete_address = explode( ' - ', $responseObject->data->user->address->address );
                    $address = ($complete_address[0] ? $complete_address[0] : '' );
                    $city = ($complete_address[1] ? $complete_address[1] : '' );
                }
                
                $user = new stdClass();

                $user->firstName = $responseObject->data->user->lastName ? $responseObject->data->user->lastName : "";
                $user->lastName = $responseObject->data->user->firstName ? $responseObject->data->user->firstName : "";
                $user->address = $address;
                $user->city = $city;
                $user->phone = $responseObject->data->user->phone ? $responseObject->data->user->phone : "";
                $user->email = $responseObject->data->user->email ? $responseObject->data->user->email : "";
                
                $cookie_args = array(
                    'expires' => time()+3600,
                    'path' => '/',
                    'samesite' => 'Lax'
                );
                
                setcookie('user', json_encode($user), $cookie_args);
            }
        }else{
            $log_data['error_title'] = $route;
            $log_data['error_content'] = 'An error has occurred during the connection between WordPress and API.';

            $log_meta['customer_ip'] = ( !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '' );
            $log_meta['user_id'] = ( get_current_user_id() ? get_current_user_id() : "" );
            $log_meta['status'] = ( $response->status != NULL ? $response->status : "" );
            
            ErrorLog::log($log_data, $log_meta);
        }
    }

    public function getMerchantId()
    {
        if( !isset( $_GET['key'] ) || !isset( $_GET['orderId'] ) ) {
            return;
        }
        
        global $wpdb;

        $post_id = '';
        $order_key = sanitize_text_field($_GET['key']);
        $merchant_id = sanitize_text_field($_GET['orderId']);
        $netopia_settings = $this->getWoocommerceNetopiapaymentsSettings();
        
        $result = $wpdb->get_results( "SELECT post_id from $wpdb->postmeta where meta_value = '$order_key'", ARRAY_A );

        if( !empty($result) ) {
            $post_id = $result[0]['post_id'];
        }
        
        if( $post_id !== '' ) {
            update_post_meta( $post_id, 'merchant_id', $merchant_id );
            if( $netopia_settings['environment'] === 'yes' ) {
                update_post_meta( $post_id, 'netopia_environment', 'sandbox' );
            }   
        }
    }

}