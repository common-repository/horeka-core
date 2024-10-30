<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Woocommerce;

use HorekaCore\Base\BaseController;

/**
* 
*/
class Mobilpay extends BaseController
{

    public function register() 
    {
        // If this option is active we use Horeka Core plugin only for importing orders to our API
        if( $this->activated( 'only_import_orders' ) ) {
            return;
        }
        
        add_action( 'woocommerce_checkout_billing', array( $this, 'customPaymentSection' ), 99 );
        add_action( 'woocommerce_after_order_notes', array( $this, 'printNetopiaHiddenInput' ) );
        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'saveNetopiaHiddenInput' ) );
    }

    public function customPaymentSection()
    {
        if( !$this->activated('display_payment_methods') ) {
            return;
        }

        if( $this->isActiveCheckoutV2() ) {
            return;
        }

        $available_gateways = array();

        if ( WC()->cart->needs_payment() ) {
            $available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
            WC()->payment_gateways()->set_current_gateway( $available_gateways );
        }

        if ( WC()->cart->needs_payment() ) { ?>
            <p class="form-row form-row-wide payment_methods_wrapper">
                <label for="payment_method" class=""><?php esc_html_e( 'Payment method', 'rpd-restaurant-solution' ); ?></label>
                <?php
                if ( ! empty( $available_gateways ) ) {
                    echo '<span class="woocommerce-input-wrapper"><select name="payment_method">';
    
                    foreach ($available_gateways as $gateway) { ?>
                    
                        <option value="<?php echo sanitize_text_field( $gateway->id ); ?>"><?php echo $gateway->get_title(); ?></option>
    
                    <?php }
    
                    echo '</select></span>';
                } else {
                    echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . apply_filters( 'woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? esc_html__( 'Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) : esc_html__( 'Please fill in your details above to see available payment methods.', 'woocommerce' ) ) . '</li>'; // @codingStandardsIgnoreLine
                }
                ?>
            </p>
        <?php }
    }

    public function printNetopiaHiddenInput( $checkout ) {
        if( !$this->activated('display_payment_methods') ) {
            return;
        }

        echo '<input type="hidden" class="input-hidden" name="netopia_method_pay" id="netopia_method_pay" value="">';
    }

    public function saveNetopiaHiddenInput( $order_id ) {
        if( !$this->activated('display_payment_methods') ) {
            return;
        }
        
        if ( ! empty( $_POST['netopia_method_pay'] ) ) {
            update_post_meta( $order_id, 'netopia_method_pay', sanitize_text_field( $_POST['netopia_method_pay'] ) );
        }
    }

}