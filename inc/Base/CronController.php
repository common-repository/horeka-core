<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Base;

use HorekaCore\Base\BaseController;
use HorekaCore\Api\PushNotification;

/**
* 
*/
class CronController extends BaseController
{   
        
    public function register()
    {
        add_filter( 'template_redirect', array( $this, 'syncApiOrders' ) );
        add_filter( 'template_redirect', array( $this, 'syncApiUsers' ) );
        add_filter( 'template_redirect', array( $this, 'exportApiUsers' ) );
    }

    function syncApiOrders() 
	{   
        global $wp;        
        global $wpdb;

        $checkOrdersRoute = '/order/integration/getcronorders';
        $addOrderRoute = '/order/integration/addorder';
        $wp_unsynchronized_orders = array();
        $api_unsynchronized_orders = array();
        $synchronized_orders = array();
        $data = array();
        
        if($wp->request == 'cron'){

            $orders = $wpdb->get_results( "SELECT DISTINCT $wpdb->posts.ID FROM $wpdb->posts INNER JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id WHERE $wpdb->posts.post_type = 'shop_order' AND $wpdb->postmeta.post_id NOT IN ( SELECT $wpdb->postmeta.post_id FROM $wpdb->posts INNER JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id WHERE $wpdb->posts.post_type = 'shop_order' AND $wpdb->postmeta.meta_key LIKE '%order_api_status%' )", ARRAY_A );
            
            if( !empty( $orders ) ) {
                
                foreach( $orders as $order ) {
                    $valid_transaction = false;
                    $new_order = wc_get_order( $order['ID'] );
                    $payment_method = get_post_meta( $new_order->get_id(), '_payment_method', true );
                    $merchant_id = get_post_meta( $new_order->get_id(), 'merchant_id', true );
                   
                    if( ( $payment_method === 'netopiapayments' && $merchant_id != '' ) ) {
                        $valid_transaction = $this->isValidTransaction( $merchant_id, $new_order->get_id() );
                    }

                    if ( $new_order->has_status( 'processing' ) || $new_order->has_status( 'completed' ) || ( $new_order->has_status( 'on-hold' ) && $payment_method !== 'netopiapayments' ) || $valid_transaction ) {
                        $wp_unsynchronized_orders['ReferenceNumbers'][] = ( !is_null($this->plugin_options['reference_id_prefix']) && $this->plugin_options['reference_id_prefix'] !== '' ? $this->plugin_options['reference_id_prefix'] . '-' : '' ) . $new_order->get_id();
                    }
                }
            
                if( !empty($wp_unsynchronized_orders) ) {
                    $response = PushNotification::makeNotification( $checkOrdersRoute, $wp_unsynchronized_orders );
                    
                    if( $response->status === 200 ) {
                        if( !empty($response->data) ) {
                            $api_unsynchronized_orders = $response->data;

                            if( !empty( $api_unsynchronized_orders ) ) {
                    
                                $synchronized_orders = array_diff( $wp_unsynchronized_orders['ReferenceNumbers'], $api_unsynchronized_orders );
                                
                                if( !empty( $synchronized_orders ) ) {
                                    foreach( $synchronized_orders as $order ) {
                                        
                                        if( !is_null($this->plugin_options['reference_id_prefix']) && $this->plugin_options['reference_id_prefix'] !== '' ) {
                                            $order = str_replace( $this->getPluginOptions()['reference_id_prefix'] . '-', '', $order );
                                        }
                                        
                                        $valid_transaction = false;
                                        $new_order = wc_get_order( $order );
                                        $payment_method = get_post_meta( $new_order->get_id(), '_payment_method', true );
                                        $merchant_id = get_post_meta( $new_order->get_id(), 'merchant_id', true );
                   
                                        if( ( $payment_method === 'netopiapayments' && $merchant_id != '' ) ) {
                                            $valid_transaction = $this->isValidTransaction( $merchant_id, $new_order->get_id() );
                                        }
                                        
                                        if ( $new_order->has_status( 'processing' ) || $new_order->has_status( 'completed' ) || ( $new_order->has_status( 'on-hold' ) && $payment_method !== 'netopiapayments' ) || $valid_transaction ) {
                                            update_post_meta( $new_order->get_id(), 'order_api_status', 'synchronized' );
                                        }
                                        
                                    }
                                }
                                
                                foreach( $api_unsynchronized_orders as $order ) {
                                    if( !is_null($this->plugin_options['reference_id_prefix']) && $this->plugin_options['reference_id_prefix'] !== '' ) {
                                        $order = str_replace( $this->getPluginOptions()['reference_id_prefix'] . '-', '', $order );
                                    }                                    
                                    
                                    $valid_transaction = false;
                                    $new_order = wc_get_order( $order );
                                    $payment_method = get_post_meta( $new_order->get_id(), '_payment_method', true );
                                    $merchant_id = get_post_meta( $new_order->get_id(), 'merchant_id', true );
                   
                                    if( ( $payment_method === 'netopiapayments' && $merchant_id != '' ) ) {
                                        $valid_transaction = $this->isValidTransaction( $merchant_id, $new_order->get_id() );
                                    }
                                    
                                    if ( $new_order->has_status( 'processing' ) || $new_order->has_status( 'completed' ) || ( $new_order->has_status( 'on-hold' ) && $payment_method !== 'netopiapayments' ) || $valid_transaction ) {
                                        $data = $this->generateOrderData( $new_order->get_id() );
                                    
                                        if( !empty( $data ) ) {
                                            $response = PushNotification::makeNotification( $addOrderRoute, $data );
                                            
                                            if( $response->status === 200 ) {
                                                echo $new_order->get_id() . '<br />';
                                                update_post_meta( $new_order->get_id(), 'order_api_status', 'synchronized' );
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            foreach( $wp_unsynchronized_orders['ReferenceNumbers'] as $order ) {
                                if( !is_null($this->plugin_options['reference_id_prefix']) && $this->plugin_options['reference_id_prefix'] !== '' ) {
                                    $order = str_replace( $this->getPluginOptions()['reference_id_prefix'] . '-', '', $order );
                                }

                                $new_order = wc_get_order( $order );
                                update_post_meta( $new_order->get_id(), 'order_api_status', 'synchronized' );
                            }
                        }
                    }
                }
            }
            die(' ');
        }
	}

    function syncApiUsers() 
	{ 
        global $wp;        
        global $wpdb;

        $users_route = '/user/integration/getexistingusers';
        $data = array(
            "Email" => '',
            "Roles" => array(5)
        );
        
        if($wp->request == 'check-api-users'){

            $users = $wpdb->get_results( "SELECT DISTINCT $wpdb->users.ID, $wpdb->users.user_email FROM $wpdb->users INNER JOIN $wpdb->usermeta ON $wpdb->users.ID = $wpdb->usermeta.user_id WHERE $wpdb->usermeta.user_id NOT IN ( SELECT $wpdb->usermeta.user_id FROM $wpdb->usermeta INNER JOIN $wpdb->users ON $wpdb->usermeta.user_id = $wpdb->users.ID WHERE $wpdb->usermeta.meta_key LIKE '%user_api_status%' )", ARRAY_A );
            
            if( !empty( $users ) ) {
                foreach( $users as $user ) {
                    $user_id = (int)$user['ID'];
                    $user_meta = get_userdata( $user_id );
                    $user_roles = $user_meta->roles;

                    if( $user_roles[0] != 'customer' ) {
                        continue;
                    }
                    
                    $data['Email'] = $user['user_email'];

                    $response = PushNotification::makeNotificationGet( $users_route, $data, 'get' );

                    if( $response->status === 200 ) {
                        if( $response->data ) {
                            if( update_user_meta( $user_id, 'user_api_status', 'synchronized' ) ) {
                                echo 'User synchronized: ' . $user_id;
                                echo '<br>';
                            }
                        } else {
                            if( $user_id > 0 ) {
                                if( wp_delete_user( $user_id ) ) {
                                    echo 'User deleted: ' . $user_id;
                                    echo '<br>';
                                }
                            }
                        }
                    }
                }
            }

            die(' ');
        }
	}

    function exportApiUsers()
    {
        global $wp;        
        global $wpdb;

        $users_route = '/user/integration/adduserandcustomer';
                
        if($wp->request == 'export-api-users'){

            $api_key = ( isset($this->getPluginOptions()['api_key']) && $this->getPluginOptions()['api_key'] != '' ? $this->getPluginOptions()['api_key'] : '' );

            $users = $wpdb->get_results( "SELECT DISTINCT a.ID, a.user_login as userName, a.user_email as email, c.meta_value as firstName, d.meta_value as lastName, e.meta_value as phone, f.meta_value as city
                                            FROM $wpdb->users as a
                                            INNER JOIN $wpdb->usermeta as b
                                            ON a.ID = b.user_id
                                            INNER JOIN $wpdb->usermeta as c
                                            ON a.ID = c.user_id
                                            INNER JOIN $wpdb->usermeta as d
                                            ON a.ID = d.user_id
                                            INNER JOIN $wpdb->usermeta as e
                                            ON a.ID = e.user_id
                                            INNER JOIN $wpdb->usermeta as f
                                            ON a.ID = f.user_id
                                            WHERE a.ID
                                            NOT IN ( SELECT $wpdb->usermeta.user_id FROM $wpdb->usermeta INNER JOIN $wpdb->users ON $wpdb->usermeta.user_id = $wpdb->users.ID WHERE $wpdb->usermeta.meta_key LIKE '%user_api_status%' )
                                            AND c.meta_key = 'billing_first_name'
                                            AND d.meta_key = 'billing_last_name'
                                            AND e.meta_key = 'billing_phone'
                                            AND f.meta_key = 'billing_city'", ARRAY_A );

            if( $api_key != '' && !empty( $users ) ) {
                foreach( $users as $user ) {
                    $data = array();

                    $user_id = (int)$user['ID'];
                    $user_meta = get_userdata( $user_id );
                    $encpass = $user_meta->data->user_pass;
                    $user_roles = $user_meta->roles;

                    if( $user_roles[0] != 'customer' ) {
                        continue;
                    }

                    $data = array(
                        "ExternalUserId" => $user['ID'],
                        "VenueApiKey" => $api_key,
                        "Phone" => $user['phone'],
                        "Email" => $user['email'],
                        "FirstName" => $user['firstName'],
                        "LastName" => $user['lastName'],
                        "Username" => $user['userName'],
                        "Password" => 'admin12x',
                        "City" => $user['city'],
                        "Address" => '',
                        "PostalCode" => ''
                    );

                    $response = PushNotification::makeNotification( $users_route, $data);

                    if( $response->status === 200 ) {
                        if( $response->data ) {
                            if( update_user_meta( $user_id, 'apiuserid', $response->data ) ) {
                                if( update_user_meta( $user_id, 'user_api_status', 'synchronized' ) ) {
                                    echo 'User synchronized: ' . $user_id;
                                    echo '<br>';
                                }
                            }
                        }
                    }
                }
            }

            die(' ');
        }
    }

}