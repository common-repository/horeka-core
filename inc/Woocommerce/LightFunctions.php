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
class LightFunctions extends BaseController
{
    public function register() 
    {
        /**
         * Actions
         */
        add_action( 'wp_loaded', array( $this, 'clearCart' ), 20 );
        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'storeUserPassword' ) );
        add_action( 'template_redirect', array( $this, 'storeFakeCheckoutVariableInSession' ) );
        add_action( 'wp_footer', array( $this, 'addBackButton' ), 999 );
        add_action( 'template_redirect', array( $this, 'redirectToAllowedPages' ) );
        add_action( 'wp_footer', array( $this, 'insertForgotPasswordTemplate' ), 1 );
		add_action( 'wp_footer', array( $this, 'changeBackButtonLink' ) );
        
        /**
         * Filters
         */
        add_filter( 'body_class', array( $this, 'addFakeCheckoutClassToBodyClasses' ) );
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

    public function clearCart() 
    {
        if ( isset($_GET['clear-cart']) && $_GET['clear-cart'] == 'true' ) {
            WC()->cart->empty_cart();
        }
    }

    public function storeUserPassword( $order_id )
    {
        if( isset( $_POST['createaccount'] ) && $_POST['createaccount'] == '1' && isset( $_POST['account_password'] ) && $_POST['account_password'] != "" ) {
		    if ( !add_post_meta( $order_id, 'currentUserP', sanitize_text_field($_POST['account_password']), true ) ) {
                update_post_meta( $order_id, 'currentUserP', sanitize_text_field($_POST['account_password']) );
            }
        }
    }

    public function storeFakeCheckoutVariableInSession()
    {
        if( is_page_template('template-fake-checkout.php') || is_page_template('template-fake-checkout-wrapper.php') ) {
            WC()->session->set('fake_checkout', 'true');
        }
    }

    public function addFakeCheckoutClassToBodyClasses( $classes )
    {
        if( is_order_received_page() && WC()->session->get('fake_checkout') ) {
            return array_merge( $classes, array( 'fake_checkout' ) );
        }
    
        return $classes;
    }

    public function addBackButton() 
    {
        $appMenuRoot = '';

        if( $this->getLiteSiteRoot() ) {
            $appMenuRoot = $this->getLiteSiteRoot();
            if( is_order_received_page() && WC()->session->get('fake_checkout') ){
                
                echo "<script>if( localStorage.getItem('paymentMethod') != null ){ if( localStorage.getItem('paymentMethod') == 'netopiapayments' ){ localStorage.setItem('isOrderReceived', 1); } } </script>";
                echo "<script>if( localStorage.getItem('paymentMethod') != null ){ if( localStorage.getItem('paymentMethod') == 'netopiapayments' ){ localStorage.setItem('fakeCheckout', 1); } } </script>";
                echo '<script>jQuery( document ).ready(function(){jQuery(window).on("pushstate", function(event) { lsHelper().resetLocalStorage(); location.href = "'. home_url() . '/' . $appMenuRoot .'"; });});</script>';
                echo "<script>",
                    "jQuery(document).ready(function($){",
                        "setTimeout(",
                            "function(){",
                                "if( localStorage.getItem('refreshStatus') != null ){",
                                    "localStorage.removeItem('refreshStatus'); localStorage.removeItem('isOrderReceived'); localStorage.removeItem('fakeCheckout'); localStorage.removeItem('paymentMethod');",
                                    "$('.back-button-light').append( '<a href=\"" . home_url() . "/" . $appMenuRoot . "\">Meniu principal</a>' );",
                                "}",
                            "},", 
                        "1000);",
                    "});",
                    "</script>";
                
                WC()->session->__unset('fake_checkout');
                
            }
            
            echo "<script>jQuery(window).on('load', function() {",
                    "if( localStorage.getItem('paymentMethod') != null ){",
                    "if( localStorage.getItem('paymentMethod') == 'netopiapayments' ){",
                    "if( localStorage.getItem('isOrderReceived') != null && localStorage.getItem('fakeCheckout') != null ){",
                    "if( Boolean(localStorage.getItem('isOrderReceived')) == true && Boolean(localStorage.getItem('fakeCheckout')) == true ){",
                    "if(localStorage.getItem('refreshStatus') != null) {",
                        "if( Boolean(localStorage.getItem('refreshStatus')) == true ) { ",
                            "localStorage.removeItem('refreshStatus'); localStorage.removeItem('isOrderReceived'); localStorage.removeItem('fakeCheckout'); localStorage.removeItem('paymentMethod'); location.href = '". home_url() . '/' . $appMenuRoot ."'; ",
                    "} } }}",
                    "if( localStorage.getItem('isOrderReceived') != null && localStorage.getItem('fakeCheckout') != null ){if( Boolean(localStorage.getItem('isOrderReceived')) == true && Boolean(localStorage.getItem('fakeCheckout')) == true ){localStorage.setItem('refreshStatus', 1);}}",
                    "}",
                    "}",
                    "});</script>";
        }
    }

    public function redirectToAllowedPages()
    {
	
        global $wp;
        $appMenuRoot = '';
        
        if( !$this->activated( 'only_light_version' ) ) {
            return;
        }

        if( $this->getPluginOptions()['allowed_links'] === NULL ) {
            return;
        }

        $allowed_domains = explode( ',', $this->getPluginOptions()['allowed_links'] );

        if( strpos(get_page_template(), 'template-add-to-cart.php') !== false || strpos(get_page_template(), 'template-fake-checkout.php') !== false || is_order_received_page() || ( isset($_GET['method']) && $_GET['method'] === 'credit_card' ) || ( in_array( sanitize_title($wp->request), $allowed_domains)  && !is_404() ) ){
            // Do nothing
        } else {
            if( $this->getLiteSiteRoot() ) {
                $appMenuRoot = $this->getLiteSiteRoot();
            }
            
            if ( wp_redirect( home_url() . '/' . $appMenuRoot ) ) {
                exit;
            }
        }
    }

    public function insertForgotPasswordTemplate()
    {
        if( !is_page_template('template-fake-checkout.php') ) {
            return;
        }

        $plugin_url = $this->plugin_url;
        require_once( $this->plugin_path . 'page-templates/forgot-password-modal.php' );
    }
	
	public function changeBackButtonLink()
    {
        if( ! is_page_template('template-fake-checkout.php') ) {
			return;
		}
		
		$appMenuRoot = '';

        if( $this->getLiteSiteRoot() ) {
            $appMenuRoot = $this->getLiteSiteRoot();

            echo "<script>setTimeout(function(){ jQuery( '.back-to-shop a' ).removeAttr( 'href' ) }, 1000);</script>";

            echo '<script type="text/javascript">',
                    'jQuery( document ).ready(function() {',
                        'jQuery( document ).on( "click", ".back-to-shop a", function(e) {',
                            'e.preventDefault();',
                            'if( window.webkit ){',
								'window.webkit.messageHandlers.cordova_iab.postMessage(JSON.stringify({"my_message" : "back"}));',
							'}else{',
                                'window.parent.location.href="' . home_url() . "/" . $appMenuRoot . '"',
                            '}',
                        '});',
                    '});',
                '</script>';
        }
    }

}