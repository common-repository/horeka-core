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
class CheckoutV2 extends BaseController
{
    public static $priority = 0;

    public static $first_field = true;

    public function register() 
    {
        // If this option is active we use Horeka Core plugin only for importing orders to our API
        if( $this->activated( 'only_import_orders' ) ) {
            return;
        }
        
        if( !$this->isActiveCheckoutV2() ) {
            return;
        }
        
        add_filter( 'woocommerce_checkout_fields', array( $this, 'reorderCheckoutFields' ), 100, 1 );
        add_filter( 'woocommerce_form_field', array( $this, 'changeDeliveryMethodFieldStructure' ), 10, 4 );
        add_filter( 'woocommerce_form_field', array( $this, 'addCheckoutWrapper' ), 10, 4 );
        add_filter( 'woocommerce_checkout_fields', array( $this, 'printPaymentMethods' ) );
        add_filter( 'woocommerce_form_field', array( $this, 'changePaymentMethodFieldStructure' ), 10, 4 );
        add_filter( 'woocommerce_cart_totals_coupon_label', array( $this, 'changeCouponStructure' ), 10, 2 );
        add_filter( 'woocommerce_cart_totals_coupon_html', array( $this, 'changeCouponHtml' ), 10, 3 );
        add_filter( 'woocommerce_add_error', array( $this, 'addErrorIcon' ), 10, 1 );
        add_filter( 'woocommerce_add_message', array( $this, 'addMessageIcon' ), 10, 1 );

        add_action( 'wp_head', array( $this, 'removeWoocommerceDefaultActions' ) );
        add_action( 'woocommerce_before_close_billing_form', array( $this, 'addCloseTag' ), 10, 1 );
        #add_action( 'horeka_review_order_before_shipping', array( $this, 'insertOnlineDiscount' ), 10 );
        #add_action( 'horeka_review_order_before_shipping_thank_you_page', array( $this, 'insertOnlineDiscountThankyouPage' ), 10, 1 );
        #add_action( 'horeka_review_pay_order_before_shipping', array( $this, 'insertOnlineDiscountPayOrder' ), 10, 1 );
        add_action( 'woocommerce_checkout_after_order_wrapper', 'woocommerce_checkout_coupon_form', 5 );
        add_action( 'woocommerce_checkout_after_coupon_wrapper', 'woocommerce_checkout_payment', 6 );
        add_action( 'wp_enqueue_scripts', array( $this, 'dequeueCheckoutV2Styles' ), 99 );
        add_action( 'horeka_after_payment_method_thank_you_page', array( $this, 'insertOrderInfoInThankyouPage' ), 10, 1 );
        add_action( 'woocommerce_review_order_before_submit', array( $this, 'insertBackButton' ) );
		add_action( 'woocommerce_before_mini_cart_contents', array( $this, 'minicartCheckOrderValue' ) );
        add_action( 'woocommerce_checkout_process', array( $this, 'changeAddressFieldStructureV2' ) );
        add_action( 'woocommerce_customer_processing_order', array( $this, 'insertMobileAppsBannerInCustomerEmail' ) );
        add_action( 'woocommerce_checkout_before_customer_details', array( $this, 'insertCustomBannerToCheckoutPage' ) );
		add_action( 'woocommerce_widget_shopping_cart_before_buttons', array( $this, 'insertBackButtonInMiniCart' ) );
        add_action( 'order_details_customer_after_address', array( $this, 'insertCustomerAddressDetails' ), 10, 1 );
        add_action( 'woocommerce_add_to_cart' , array( $this, 'setCountry') ); 
    }

    public function dequeueCheckoutV2Styles()
	{
		wp_dequeue_style( 'rpd-custom-light' );
		wp_deregister_style( 'rpd-custom-light' );

		wp_dequeue_style( 'rpd-checkout-light' );
		wp_deregister_style( 'rpd-checkout-light' );

		wp_dequeue_style( 'rpd-custom-light-only-web' );
		wp_deregister_style( 'rpd-custom-light-only-web' );
	}

    public function removeWoocommerceDefaultActions()
    {
        remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
        remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
    }

    public function getPaymentMethodImage( $payment_method = 'cod' ) 
    {
        switch ($payment_method) {
            case "cod":
                $svg_image = '<svg width="34" height="34" class="' . $payment_method . '" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M26.9165 12.75H12.7498C11.185 12.75 9.9165 14.0185 9.9165 15.5833V24.0833C9.9165 25.6481 11.185 26.9167 12.7498 26.9167H26.9165C28.4813 26.9167 29.7498 25.6481 29.7498 24.0833V15.5833C29.7498 14.0185 28.4813 12.75 26.9165 12.75Z" stroke="#008EFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19.8333 22.6667C21.3981 22.6667 22.6667 21.3981 22.6667 19.8333C22.6667 18.2685 21.3981 17 19.8333 17C18.2685 17 17 18.2685 17 19.8333C17 21.3981 18.2685 22.6667 19.8333 22.6667Z" stroke="#008EFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M24.0833 12.75V9.91665C24.0833 9.1652 23.7848 8.44453 23.2535 7.91318C22.7221 7.38182 22.0014 7.08331 21.25 7.08331H7.08333C6.33189 7.08331 5.61122 7.38182 5.07986 7.91318C4.54851 8.44453 4.25 9.1652 4.25 9.91665V18.4166C4.25 19.1681 4.54851 19.8888 5.07986 20.4201C5.61122 20.9515 6.33189 21.25 7.08333 21.25H9.91667" stroke="#008EFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                
                break;
            case "offline_gateway":
                $svg_image = '<svg width="34" height="34" class="' . $payment_method . '" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M25.5 7.08331H8.5C6.15279 7.08331 4.25 8.9861 4.25 11.3333V22.6666C4.25 25.0139 6.15279 26.9166 8.5 26.9166H25.5C27.8472 26.9166 29.75 25.0139 29.75 22.6666V11.3333C29.75 8.9861 27.8472 7.08331 25.5 7.08331Z" stroke="#008EFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M4.25 14.1667H29.75" stroke="#008EFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9.9165 21.25H9.93067" stroke="#008EFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15.5835 21.25H18.4168" stroke="#008EFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

                break;
            case "netopiapayments":
                $svg_image = '<svg width="34" height="34" class="' . $payment_method . '" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M25.5 7.08331H8.5C6.15279 7.08331 4.25 8.9861 4.25 11.3333V22.6666C4.25 25.0139 6.15279 26.9166 8.5 26.9166H25.5C27.8472 26.9166 29.75 25.0139 29.75 22.6666V11.3333C29.75 8.9861 27.8472 7.08331 25.5 7.08331Z" stroke="#008EFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M4.25 14.1667H29.75" stroke="#008EFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9.9165 21.25H9.93067" stroke="#008EFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15.5835 21.25H18.4168" stroke="#008EFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

                break;
            default:
                $svg_image = '<svg width="34" height="34" class="cod" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M26.9165 12.75H12.7498C11.185 12.75 9.9165 14.0185 9.9165 15.5833V24.0833C9.9165 25.6481 11.185 26.9167 12.7498 26.9167H26.9165C28.4813 26.9167 29.7498 25.6481 29.7498 24.0833V15.5833C29.7498 14.0185 28.4813 12.75 26.9165 12.75Z" stroke="#008EFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19.8333 22.6667C21.3981 22.6667 22.6667 21.3981 22.6667 19.8333C22.6667 18.2685 21.3981 17 19.8333 17C18.2685 17 17 18.2685 17 19.8333C17 21.3981 18.2685 22.6667 19.8333 22.6667Z" stroke="#008EFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M24.0833 12.75V9.91665C24.0833 9.1652 23.7848 8.44453 23.2535 7.91318C22.7221 7.38182 22.0014 7.08331 21.25 7.08331H7.08333C6.33189 7.08331 5.61122 7.38182 5.07986 7.91318C4.54851 8.44453 4.25 9.1652 4.25 9.91665V18.4166C4.25 19.1681 4.54851 19.8888 5.07986 20.4201C5.61122 20.9515 6.33189 21.25 7.08333 21.25H9.91667" stroke="#008EFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        }

        return $svg_image;
    }

    public function getDeliveryMethodImage( $delivery_method = 'livrare-la-domiciliu' ) 
    {
        switch ($delivery_method) {
            case "livrare-la-domiciliu":
                $svg_image = '<svg width="28" height="28" class="' . $delivery_method . '" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0)"><path d="M26.514 8.6392H21.7657C20.9464 8.6392 20.2797 9.30617 20.2797 10.126V15.5912H18.5037C18.6024 15.2324 18.639 14.8092 18.615 14.3166V14.3166C18.5764 12.2352 18.1987 10.4811 17.4923 9.10284C16.9438 8.03277 16.3037 7.39123 15.7544 7.00771C16.3991 6.34484 16.7719 5.40187 16.7719 4.3143C16.7719 2.35752 15.2109 0.765625 13.2923 0.765625C11.3737 0.765625 9.8128 2.35752 9.8128 4.3143C9.8128 4.54087 9.99644 4.72445 10.223 4.72445H10.5016C10.476 4.87922 10.4623 5.03694 10.4623 5.19712C10.4623 6.57612 11.4477 7.72866 12.7504 7.98695C12.6139 8.49401 12.546 9.09191 12.5463 9.77112L11.5233 10.651L9.03421 10.8523L6.16295 9.90314C5.8998 9.81564 5.60153 9.71677 5.34275 9.90336C5.08774 10.0874 5.08774 10.4095 5.08774 10.5154V12.6501C5.08774 12.8767 5.27138 13.0602 5.4979 13.0602H5.9862L5.23726 15.6483C5.17431 15.8658 5.29966 16.0933 5.51726 16.1563C5.73497 16.2195 5.96231 16.0939 6.02531 15.8763L6.84026 13.0602H8.00631C6.55599 17.4829 7.38352 20.4351 8.6555 23.4227H7.9182L4.84734 19.9891L5.4967 17.7458C5.5597 17.5282 5.43435 17.3008 5.21675 17.2378C4.99915 17.1749 4.7717 17.3002 4.7087 17.5178L4.08762 19.6635C2.16666 19.6315 0.448383 20.9559 0.0105006 22.8614C-0.0401947 23.0821 0.0975631 23.3022 0.318227 23.3529L0.900485 23.4869C0.751735 25.0319 1.7692 26.4896 3.3209 26.8468C3.55655 26.9011 3.79422 26.9279 4.03025 26.9279C4.61234 26.9279 5.18421 26.7647 5.69002 26.4477C6.28365 26.0757 6.72596 25.5288 6.96533 24.8825L7.42476 24.9883C7.56164 25.0199 7.69147 25.0359 7.82119 24.9661C7.94188 24.9011 8.02211 24.7838 8.05219 24.6514C8.08106 24.5172 8.10305 24.3807 8.11831 24.243H16.6965C16.9708 25.9486 18.4219 27.2342 20.155 27.2342C21.8881 27.2342 23.3393 25.9486 23.6136 24.243H23.9267C23.9267 24.243 23.9888 24.2437 24.0029 24.2437C24.1203 24.2437 24.2897 24.2307 24.4288 24.0892C24.4984 24.0184 24.5801 23.8917 24.5738 23.6904C24.5623 23.3052 24.5045 22.933 24.4058 22.5793C24.7325 20.5959 24.4182 19.3879 23.9522 18.4425C24.2468 18.2494 24.4421 17.9167 24.4421 17.5387V16.4114H26.5139C27.3333 16.4114 27.9999 15.7445 27.9999 14.9246V10.126C28 9.30617 27.3334 8.6392 26.514 8.6392ZM5.25443 25.7527C4.72938 26.0818 4.10813 26.1864 3.50503 26.0475H3.50498C2.39493 25.792 1.65435 24.7741 1.70833 23.6735L6.15732 24.6975C5.97324 25.1309 5.6618 25.4974 5.25443 25.7527ZM7.30674 24.1194L0.926626 22.6513C1.41898 21.303 2.74395 20.4105 4.19223 20.4873L7.31768 23.9819C7.31495 24.028 7.31134 24.0738 7.30674 24.1194ZM13.502 16.972C13.383 17.2861 13.383 17.6732 13.383 18.2091V19.7568H11.8811V17.6334C11.8811 15.2328 13.1913 14.8049 13.7793 14.7343H17.8004C17.7842 15.2792 17.6613 15.6665 17.4279 15.9123C17.1559 16.1988 16.6904 16.3383 16.0048 16.339L14.5326 16.3405C14.0076 16.3406 13.6609 16.553 13.502 16.972ZM13.383 20.5771V21.5564C13.3792 21.5564 13.3754 21.5564 13.3715 21.5564H10.4581C10.4909 21.2527 10.5855 21.0177 10.7408 20.855C10.972 20.6132 11.2975 20.5771 11.4709 20.5771H13.383ZM17.7823 13.9141H14.0522C13.9024 13.4427 13.7689 12.9266 13.6591 12.3961L14.7931 11.66C15.4122 11.258 15.7343 10.69 15.6767 10.1015C15.6258 9.57983 15.2711 9.11384 14.7732 8.91439C14.3186 8.73228 13.8292 8.78948 13.3966 9.06604C13.4323 8.67371 13.4987 8.32349 13.5947 8.02621C13.7695 8.00816 13.9432 7.97398 14.1137 7.92384C14.4608 7.84323 14.7815 7.71477 15.0711 7.54441C15.8611 8.01637 17.5985 9.54718 17.7823 13.9141ZM10.6631 3.9042C10.8566 2.59366 11.9619 1.58599 13.2923 1.58599C14.7586 1.58599 15.9516 2.80995 15.9516 4.31435C15.9516 5.53793 15.3648 6.52411 14.4187 6.95811V4.79998C14.4187 4.4263 14.2757 3.96791 13.5947 3.90589C13.5823 3.90474 13.5699 3.9042 13.5575 3.9042H10.6631ZM11.2827 5.19717C11.2827 5.0353 11.3011 4.8772 11.3374 4.72451H13.5373C13.5627 4.72724 13.5811 4.7302 13.5944 4.73287C13.5965 4.74808 13.5984 4.76995 13.5984 4.79998V7.19945C13.5006 7.21383 13.4023 7.2211 13.304 7.22116C13.3031 7.22116 13.3022 7.22116 13.3013 7.22116C12.1881 7.22012 11.2827 6.31258 11.2827 5.19717ZM10.751 11.5365L11.7224 11.458C11.809 11.451 11.8911 11.4167 11.957 11.3601L13.7103 9.85212L13.7105 9.85184C13.8801 9.70577 14.061 9.63085 14.2372 9.63085C14.3157 9.63085 14.3931 9.64573 14.4682 9.67581C14.685 9.76259 14.8389 9.961 14.8604 10.1812C14.8875 10.4583 14.7049 10.7391 14.3466 10.9719L12.3928 12.24H10.7511V11.5365H10.751ZM9.01759 11.6768L9.93065 11.6029V12.2401H8.86966C8.73212 12.2401 8.6986 12.1258 8.69466 12.0299C8.69242 11.9756 8.69756 11.703 9.01759 11.6768ZM8.04683 11.3936C7.92329 11.5962 7.86559 11.8331 7.87506 12.0635C7.87752 12.1232 7.88512 12.1807 7.89573 12.2367C7.89573 12.2378 7.89507 12.239 7.89529 12.24H5.90816V10.6829L8.04842 11.3904C8.04776 11.3915 8.04743 11.3926 8.04683 11.3936ZM8.87195 13.0604H12.5142C12.5934 13.0604 12.671 13.0374 12.7375 12.9942L12.9231 12.8737C13.0125 13.2684 13.1142 13.6526 13.225 14.0154C12.3885 14.2623 11.0607 15.0602 11.0607 17.6334V19.798C10.7729 19.856 10.4325 19.9902 10.1478 20.2882C9.84758 20.6024 9.67613 21.028 9.63501 21.5564H8.80759C7.97382 19.1535 7.65674 16.5966 8.87195 13.0604ZM9.548 23.4213C9.39641 23.0727 9.25028 22.7254 9.11269 22.3768H13.3714C13.9234 22.3768 14.2032 22.1255 14.2032 21.6299V20.1669V18.6192H17.4407C17.6672 18.6192 17.8508 18.4356 17.8508 18.209C17.8508 17.9825 17.6672 17.7989 17.4407 17.7989H14.2062C14.2114 17.5659 14.2264 17.3754 14.2691 17.2628C14.2898 17.2081 14.3077 17.1608 14.533 17.1608L16.0056 17.1593C16.9279 17.1584 17.5877 16.9353 18.0228 16.4771C18.0429 16.4559 18.0619 16.4336 18.081 16.4115H20.6899H23.622V17.5388C23.622 17.6798 23.5032 17.7989 23.3625 17.7989H19.2793C19.0527 17.7989 18.8691 17.9825 18.8691 18.209C18.8691 18.4356 19.0527 18.6192 19.2793 18.6192H23.1215C23.4457 19.2209 23.7373 19.9696 23.7199 21.1526C22.9085 20.0254 21.5948 19.3006 20.1039 19.3006C18.9066 19.3006 17.7831 19.7703 16.9403 20.6231C16.1925 21.3799 15.7433 22.362 15.6509 23.4227L9.548 23.4213ZM20.1551 26.414C18.8729 26.414 17.7915 25.4902 17.5299 24.2431H22.7803C22.5186 25.4901 21.4372 26.414 20.1551 26.414ZM23.2462 23.4228H17.064H16.4748C16.6761 21.5682 18.2269 20.121 20.1039 20.121C21.752 20.121 23.1359 21.2076 23.5943 22.7289C23.5957 22.7337 23.5972 22.7385 23.5989 22.7433C23.6637 22.9615 23.7094 23.1887 23.7342 23.4228H23.2462ZM27.1797 14.9247C27.1797 15.2922 26.881 15.5912 26.514 15.5912H21.1V13.4731H27.1797V14.9247ZM27.1797 12.6528H21.1V11.7805H27.1797V12.6528ZM27.1797 10.9601H21.1V10.126C21.1 9.75849 21.3987 9.45952 21.7657 9.45952H26.514C26.881 9.45952 27.1797 9.75855 27.1797 10.126V10.9601Z" fill="#008EFF" stroke="#008EFF" stroke-width="0.3"/></g><defs><clipPath id="clip0"><rect width="28" height="28" fill="white"/></clipPath></defs></svg>';
                
                break;
            case "ridicare-personala":
                $svg_image = '<svg width="28" height="28" class="' . $delivery_method . '" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.6875 19.6656V14.4375C12.6875 14.3215 12.6414 14.2102 12.5594 14.1281C12.4773 14.0461 12.3661 14 12.25 14C9.57799 14 9.20338 18.5846 9.18873 18.7797C9.18223 18.8663 9.20164 18.9528 9.24449 19.0283C9.28734 19.1039 9.35169 19.1649 9.42935 19.2037L10.0625 19.5204V19.6658L9.72346 23.0569C9.70522 23.2394 9.72541 23.4236 9.78273 23.5978C9.84005 23.772 9.93324 23.9322 10.0563 24.0682C10.1793 24.2041 10.3295 24.3128 10.4971 24.3872C10.6647 24.4616 10.846 24.5 11.0294 24.5H11.7206C11.904 24.5 12.0854 24.4616 12.253 24.3872C12.4206 24.3128 12.5707 24.2041 12.6938 24.0682C12.8168 23.9322 12.91 23.772 12.9673 23.5978C13.0246 23.4236 13.0448 23.2394 13.0266 23.0569L12.6875 19.6656ZM10.0925 18.5571C10.1684 17.9436 10.3033 17.3389 10.4953 16.7514C10.8315 15.762 11.2733 15.16 11.8125 14.9547V19.25H10.9375C10.9375 19.1688 10.9149 19.0891 10.8722 19.02C10.8295 18.9509 10.7684 18.895 10.6957 18.8587L10.0925 18.5571ZM12.0449 23.4811C12.0041 23.5266 11.9541 23.563 11.8982 23.5878C11.8423 23.6126 11.7818 23.6253 11.7206 23.625H11.0294C10.9682 23.625 10.9078 23.6122 10.8519 23.5874C10.796 23.5626 10.7459 23.5264 10.7049 23.481C10.6639 23.4357 10.6328 23.3822 10.6137 23.3241C10.5946 23.266 10.5879 23.2046 10.594 23.1438L10.896 20.125H11.8541L12.156 23.1438C12.1624 23.2046 12.1559 23.2662 12.1368 23.3243C12.1176 23.3825 12.0864 23.4359 12.0451 23.4811H12.0449Z" fill="#008EFF" stroke="#008EFF" stroke-width="0.5"/><path d="M26.6875 0.4375H23.1875C23.0715 0.4375 22.9602 0.483594 22.8781 0.565641C22.7961 0.647688 22.75 0.758968 22.75 0.875V1.3125H13.6162C13.2207 1.31278 12.8315 1.412 12.4842 1.60114L8.20088 3.9375H3.0625C2.32652 3.9375 1.75 4.70619 1.75 5.6875C1.75 6.60341 2.2523 7.33359 2.91763 7.42716L0.945164 14.1045C0.943414 14.1105 0.941773 14.1165 0.940242 14.1224C0.896934 14.2959 0.875023 14.4741 0.875 14.6529V27.125C0.875 27.241 0.921094 27.3523 1.00314 27.4344C1.08519 27.5164 1.19647 27.5625 1.3125 27.5625H21C21.116 27.5625 21.2273 27.5164 21.3094 27.4344C21.3914 27.3523 21.4375 27.241 21.4375 27.125V14.6895C21.4375 14.4806 21.4076 14.2727 21.3486 14.0722L19.5257 7.87462C20.1194 7.87027 20.6897 7.64301 21.1236 7.23791C21.5575 6.8328 21.8234 6.27943 21.8685 5.6875H22.75V6.5625C22.75 6.67853 22.7961 6.78981 22.8781 6.87186C22.9602 6.95391 23.0715 7 23.1875 7H26.6875C26.8035 7 26.9148 6.95391 26.9969 6.87186C27.0789 6.78981 27.125 6.67853 27.125 6.5625V0.875C27.125 0.758968 27.0789 0.647688 26.9969 0.565641C26.9148 0.483594 26.8035 0.4375 26.6875 0.4375ZM2.625 5.6875C2.625 5.15337 2.88411 4.8125 3.0625 4.8125H15.5688L13.8188 6.5625H3.0625C2.88411 6.5625 2.625 6.22163 2.625 5.6875ZM16.2527 14.1225C16.2094 14.296 16.1875 14.4741 16.1875 14.653V26.6875H1.75V14.6529C1.75004 14.5485 1.76251 14.4444 1.78713 14.3429L3.82692 7.4375H13.0273C12.7989 7.82337 12.7122 8.27679 12.7821 8.71971C12.852 9.16262 13.0741 9.5673 13.4102 9.86409C13.7463 10.1609 14.1754 10.3312 14.6236 10.3457C15.0717 10.3602 15.5109 10.218 15.8656 9.94361L17.9703 8.30643L16.2577 14.1046C16.2559 14.1105 16.2543 14.1165 16.2527 14.1225ZM18.8125 25.5562L19.9438 26.6875H17.6812L18.8125 25.5562ZM20.5092 14.3193C20.5446 14.4395 20.5625 14.5642 20.5625 14.6895V26.0688L19.25 24.7563V15.3125C19.25 15.1965 19.2039 15.0852 19.1219 15.0031C19.0398 14.9211 18.9285 14.875 18.8125 14.875C18.6965 14.875 18.5852 14.9211 18.5031 15.0031C18.4211 15.0852 18.375 15.1965 18.375 15.3125V24.7563L17.0625 26.0688V14.6529C17.0625 14.5485 17.075 14.4444 17.0996 14.3429L18.8114 8.54738L20.5092 14.3193ZM22.75 4.8125H21.6985C21.5133 4.81272 21.3358 4.88638 21.2048 5.01733C21.0739 5.14828 21.0002 5.32583 21 5.51102C20.9996 5.90579 20.8425 6.28425 20.5634 6.56339C20.2843 6.84253 19.9058 6.99955 19.511 7H18.4355C18.2991 7.00024 18.1665 7.04572 18.0586 7.12934L15.3282 9.25313C15.1171 9.41753 14.8512 9.49523 14.5847 9.47037C14.3182 9.44552 14.0713 9.31998 13.8941 9.11935C13.717 8.91872 13.6231 8.6581 13.6314 8.3906C13.6398 8.12311 13.7499 7.86887 13.9392 7.67971L16.9343 4.68453C16.9956 4.62336 17.0373 4.54539 17.0542 4.4605C17.0711 4.37561 17.0625 4.28761 17.0293 4.20763C16.9962 4.12765 16.9401 4.0593 16.8682 4.01121C16.7962 3.96313 16.7116 3.93748 16.625 3.9375H10.0283L12.9032 2.36928C13.122 2.25016 13.3671 2.18767 13.6162 2.1875H22.75V4.8125ZM26.25 6.125H23.625V1.3125H26.25V6.125Z" fill="#008EFF" stroke="#008EFF" stroke-width="0.5"/><path d="M8.75 14.4375C8.75 14.3215 8.70391 14.2102 8.62186 14.1281C8.53981 14.0461 8.42853 14 8.3125 14C8.19647 14 8.08519 14.0461 8.00314 14.1281C7.92109 14.2102 7.875 14.3215 7.875 14.4375V15.75H7V14.4375C7 14.3215 6.95391 14.2102 6.87186 14.1281C6.78981 14.0461 6.67853 14 6.5625 14C6.44647 14 6.33519 14.0461 6.25314 14.1281C6.17109 14.2102 6.125 14.3215 6.125 14.4375V15.75H5.25V14.4375C5.25 14.3215 5.20391 14.2102 5.12186 14.1281C5.03981 14.0461 4.92853 14 4.8125 14C4.69647 14 4.58519 14.0461 4.50314 14.1281C4.42109 14.2102 4.375 14.3215 4.375 14.4375V16.1875H4.37582C4.37552 16.2554 4.39106 16.3224 4.42121 16.3832L5.24256 18.0259L4.88059 23.094C4.86774 23.2738 4.89207 23.4543 4.95204 23.6242C5.01202 23.7942 5.10635 23.95 5.22918 24.0819C5.352 24.2138 5.50068 24.319 5.66594 24.3909C5.8312 24.4629 6.00951 24.5 6.18975 24.5H6.93525C7.11549 24.5 7.2938 24.4629 7.45906 24.3909C7.62432 24.319 7.773 24.2138 7.89582 24.0819C8.01865 23.95 8.11298 23.7942 8.17296 23.6242C8.23293 23.4543 8.25726 23.2738 8.24441 23.094L7.88244 18.0259L8.70379 16.3832C8.73394 16.3224 8.74948 16.2554 8.74918 16.1875H8.75V14.4375ZM7.60462 16.625L7.16712 17.5H5.95788L5.52038 16.625H7.60462ZM7.2555 23.4856C7.21472 23.5298 7.16519 23.565 7.11005 23.589C7.05491 23.613 6.99538 23.6253 6.93525 23.625H6.18975C6.12967 23.625 6.07024 23.6126 6.01516 23.5887C5.96007 23.5647 5.91051 23.5296 5.86957 23.4857C5.82863 23.4417 5.79718 23.3898 5.77718 23.3331C5.75719 23.2765 5.74907 23.2163 5.75334 23.1564L6.09487 18.375H7.03002L7.37155 23.1564C7.37611 23.2163 7.36814 23.2765 7.34816 23.3332C7.32818 23.3899 7.29663 23.4418 7.2555 23.4856Z" fill="#008EFF" stroke="#008EFF" stroke-width="0.5"/></svg>';

                break;
            case "servire-la-restaurant":
                $svg_image = '<svg width="34" height="34" class="' . $delivery_method . '" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.0835 5.66667V9.91667C7.0835 11.0438 7.53126 12.1248 8.32829 12.9219C9.12532 13.7189 10.2063 14.1667 11.3335 14.1667C12.4607 14.1667 13.5417 13.7189 14.3387 12.9219C15.1357 12.1248 15.5835 11.0438 15.5835 9.91667V5.66667M26.9168 4.25V21.25H19.8335C19.8009 16.0352 20.0942 10.7582 26.9168 4.25ZM26.9168 21.25V29.75H25.5002V25.5L26.9168 21.25ZM11.3335 5.66667V29.75V5.66667Z" stroke="#008EFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

                break;
            default:
                $svg_image = '<svg width="28" height="28" class="livrare-la-domiciliu" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0)"><path d="M26.514 8.6392H21.7657C20.9464 8.6392 20.2797 9.30617 20.2797 10.126V15.5912H18.5037C18.6024 15.2324 18.639 14.8092 18.615 14.3166V14.3166C18.5764 12.2352 18.1987 10.4811 17.4923 9.10284C16.9438 8.03277 16.3037 7.39123 15.7544 7.00771C16.3991 6.34484 16.7719 5.40187 16.7719 4.3143C16.7719 2.35752 15.2109 0.765625 13.2923 0.765625C11.3737 0.765625 9.8128 2.35752 9.8128 4.3143C9.8128 4.54087 9.99644 4.72445 10.223 4.72445H10.5016C10.476 4.87922 10.4623 5.03694 10.4623 5.19712C10.4623 6.57612 11.4477 7.72866 12.7504 7.98695C12.6139 8.49401 12.546 9.09191 12.5463 9.77112L11.5233 10.651L9.03421 10.8523L6.16295 9.90314C5.8998 9.81564 5.60153 9.71677 5.34275 9.90336C5.08774 10.0874 5.08774 10.4095 5.08774 10.5154V12.6501C5.08774 12.8767 5.27138 13.0602 5.4979 13.0602H5.9862L5.23726 15.6483C5.17431 15.8658 5.29966 16.0933 5.51726 16.1563C5.73497 16.2195 5.96231 16.0939 6.02531 15.8763L6.84026 13.0602H8.00631C6.55599 17.4829 7.38352 20.4351 8.6555 23.4227H7.9182L4.84734 19.9891L5.4967 17.7458C5.5597 17.5282 5.43435 17.3008 5.21675 17.2378C4.99915 17.1749 4.7717 17.3002 4.7087 17.5178L4.08762 19.6635C2.16666 19.6315 0.448383 20.9559 0.0105006 22.8614C-0.0401947 23.0821 0.0975631 23.3022 0.318227 23.3529L0.900485 23.4869C0.751735 25.0319 1.7692 26.4896 3.3209 26.8468C3.55655 26.9011 3.79422 26.9279 4.03025 26.9279C4.61234 26.9279 5.18421 26.7647 5.69002 26.4477C6.28365 26.0757 6.72596 25.5288 6.96533 24.8825L7.42476 24.9883C7.56164 25.0199 7.69147 25.0359 7.82119 24.9661C7.94188 24.9011 8.02211 24.7838 8.05219 24.6514C8.08106 24.5172 8.10305 24.3807 8.11831 24.243H16.6965C16.9708 25.9486 18.4219 27.2342 20.155 27.2342C21.8881 27.2342 23.3393 25.9486 23.6136 24.243H23.9267C23.9267 24.243 23.9888 24.2437 24.0029 24.2437C24.1203 24.2437 24.2897 24.2307 24.4288 24.0892C24.4984 24.0184 24.5801 23.8917 24.5738 23.6904C24.5623 23.3052 24.5045 22.933 24.4058 22.5793C24.7325 20.5959 24.4182 19.3879 23.9522 18.4425C24.2468 18.2494 24.4421 17.9167 24.4421 17.5387V16.4114H26.5139C27.3333 16.4114 27.9999 15.7445 27.9999 14.9246V10.126C28 9.30617 27.3334 8.6392 26.514 8.6392ZM5.25443 25.7527C4.72938 26.0818 4.10813 26.1864 3.50503 26.0475H3.50498C2.39493 25.792 1.65435 24.7741 1.70833 23.6735L6.15732 24.6975C5.97324 25.1309 5.6618 25.4974 5.25443 25.7527ZM7.30674 24.1194L0.926626 22.6513C1.41898 21.303 2.74395 20.4105 4.19223 20.4873L7.31768 23.9819C7.31495 24.028 7.31134 24.0738 7.30674 24.1194ZM13.502 16.972C13.383 17.2861 13.383 17.6732 13.383 18.2091V19.7568H11.8811V17.6334C11.8811 15.2328 13.1913 14.8049 13.7793 14.7343H17.8004C17.7842 15.2792 17.6613 15.6665 17.4279 15.9123C17.1559 16.1988 16.6904 16.3383 16.0048 16.339L14.5326 16.3405C14.0076 16.3406 13.6609 16.553 13.502 16.972ZM13.383 20.5771V21.5564C13.3792 21.5564 13.3754 21.5564 13.3715 21.5564H10.4581C10.4909 21.2527 10.5855 21.0177 10.7408 20.855C10.972 20.6132 11.2975 20.5771 11.4709 20.5771H13.383ZM17.7823 13.9141H14.0522C13.9024 13.4427 13.7689 12.9266 13.6591 12.3961L14.7931 11.66C15.4122 11.258 15.7343 10.69 15.6767 10.1015C15.6258 9.57983 15.2711 9.11384 14.7732 8.91439C14.3186 8.73228 13.8292 8.78948 13.3966 9.06604C13.4323 8.67371 13.4987 8.32349 13.5947 8.02621C13.7695 8.00816 13.9432 7.97398 14.1137 7.92384C14.4608 7.84323 14.7815 7.71477 15.0711 7.54441C15.8611 8.01637 17.5985 9.54718 17.7823 13.9141ZM10.6631 3.9042C10.8566 2.59366 11.9619 1.58599 13.2923 1.58599C14.7586 1.58599 15.9516 2.80995 15.9516 4.31435C15.9516 5.53793 15.3648 6.52411 14.4187 6.95811V4.79998C14.4187 4.4263 14.2757 3.96791 13.5947 3.90589C13.5823 3.90474 13.5699 3.9042 13.5575 3.9042H10.6631ZM11.2827 5.19717C11.2827 5.0353 11.3011 4.8772 11.3374 4.72451H13.5373C13.5627 4.72724 13.5811 4.7302 13.5944 4.73287C13.5965 4.74808 13.5984 4.76995 13.5984 4.79998V7.19945C13.5006 7.21383 13.4023 7.2211 13.304 7.22116C13.3031 7.22116 13.3022 7.22116 13.3013 7.22116C12.1881 7.22012 11.2827 6.31258 11.2827 5.19717ZM10.751 11.5365L11.7224 11.458C11.809 11.451 11.8911 11.4167 11.957 11.3601L13.7103 9.85212L13.7105 9.85184C13.8801 9.70577 14.061 9.63085 14.2372 9.63085C14.3157 9.63085 14.3931 9.64573 14.4682 9.67581C14.685 9.76259 14.8389 9.961 14.8604 10.1812C14.8875 10.4583 14.7049 10.7391 14.3466 10.9719L12.3928 12.24H10.7511V11.5365H10.751ZM9.01759 11.6768L9.93065 11.6029V12.2401H8.86966C8.73212 12.2401 8.6986 12.1258 8.69466 12.0299C8.69242 11.9756 8.69756 11.703 9.01759 11.6768ZM8.04683 11.3936C7.92329 11.5962 7.86559 11.8331 7.87506 12.0635C7.87752 12.1232 7.88512 12.1807 7.89573 12.2367C7.89573 12.2378 7.89507 12.239 7.89529 12.24H5.90816V10.6829L8.04842 11.3904C8.04776 11.3915 8.04743 11.3926 8.04683 11.3936ZM8.87195 13.0604H12.5142C12.5934 13.0604 12.671 13.0374 12.7375 12.9942L12.9231 12.8737C13.0125 13.2684 13.1142 13.6526 13.225 14.0154C12.3885 14.2623 11.0607 15.0602 11.0607 17.6334V19.798C10.7729 19.856 10.4325 19.9902 10.1478 20.2882C9.84758 20.6024 9.67613 21.028 9.63501 21.5564H8.80759C7.97382 19.1535 7.65674 16.5966 8.87195 13.0604ZM9.548 23.4213C9.39641 23.0727 9.25028 22.7254 9.11269 22.3768H13.3714C13.9234 22.3768 14.2032 22.1255 14.2032 21.6299V20.1669V18.6192H17.4407C17.6672 18.6192 17.8508 18.4356 17.8508 18.209C17.8508 17.9825 17.6672 17.7989 17.4407 17.7989H14.2062C14.2114 17.5659 14.2264 17.3754 14.2691 17.2628C14.2898 17.2081 14.3077 17.1608 14.533 17.1608L16.0056 17.1593C16.9279 17.1584 17.5877 16.9353 18.0228 16.4771C18.0429 16.4559 18.0619 16.4336 18.081 16.4115H20.6899H23.622V17.5388C23.622 17.6798 23.5032 17.7989 23.3625 17.7989H19.2793C19.0527 17.7989 18.8691 17.9825 18.8691 18.209C18.8691 18.4356 19.0527 18.6192 19.2793 18.6192H23.1215C23.4457 19.2209 23.7373 19.9696 23.7199 21.1526C22.9085 20.0254 21.5948 19.3006 20.1039 19.3006C18.9066 19.3006 17.7831 19.7703 16.9403 20.6231C16.1925 21.3799 15.7433 22.362 15.6509 23.4227L9.548 23.4213ZM20.1551 26.414C18.8729 26.414 17.7915 25.4902 17.5299 24.2431H22.7803C22.5186 25.4901 21.4372 26.414 20.1551 26.414ZM23.2462 23.4228H17.064H16.4748C16.6761 21.5682 18.2269 20.121 20.1039 20.121C21.752 20.121 23.1359 21.2076 23.5943 22.7289C23.5957 22.7337 23.5972 22.7385 23.5989 22.7433C23.6637 22.9615 23.7094 23.1887 23.7342 23.4228H23.2462ZM27.1797 14.9247C27.1797 15.2922 26.881 15.5912 26.514 15.5912H21.1V13.4731H27.1797V14.9247ZM27.1797 12.6528H21.1V11.7805H27.1797V12.6528ZM27.1797 10.9601H21.1V10.126C21.1 9.75849 21.3987 9.45952 21.7657 9.45952H26.514C26.881 9.45952 27.1797 9.75855 27.1797 10.126V10.9601Z" fill="#008EFF" stroke="#008EFF" stroke-width="0.3"/></g><defs><clipPath id="clip0"><rect width="28" height="28" fill="white"/></clipPath></defs></svg>';
        }

        return $svg_image;
    }

    

    public function reorderCheckoutFields( $fields ) 
    {
        $billing_order = array(
            'delivery_method',
            'pickup_point',
			'billing_pickup_locations',
            'delivery_hour_on',
            'delivery_time',
            'delivery_hour',
            'billing_city',
            'areas',
            'billing_address_1',
            'billing_bloc',
            'billing_apartament',
            'billing_scara',
            'billing_etaj',
            'order_comments',
            'billing_first_name',
            'billing_last_name',
            'billing_phone',
            'billing_email',
            'payment_method',
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
            if( $fields['billing'][$field_name] || $field_name == 'order_comments' ) {
                $count++;
                $fields['billing'][$field_name]['priority'] = $count * $priority;
            }
        }

        // Delivery hour option
        $fields['billing']['delivery_hour_on']['label'] = __('Delivery time', 'rpd-restaurant-solution');
        $fields['billing']['delivery_hour_on']['type'] = 'radio';
        $fields['billing']['delivery_hour_on']['class'] = array('form-row-first');
        $fields['billing']['delivery_hour_on']['required'] = false;

        // Delivery time
        $fields['billing']['delivery_time']['label'] = __('', 'rpd-restaurant-solution');
        $fields['billing']['delivery_time']['class'] = array('form-row-first split-row');

        // Delivery hour
        $fields['billing']['delivery_hour']['label'] = __('', 'rpd-restaurant-solution');
        $fields['billing']['delivery_hour']['class'] = array('form-row-first split-row');
		
		// Billing address
        $fields['billing']['billing_address_1']['class'] = array('form-row-wide');

        // Billing city
        $fields['billing']['billing_city']['class'] = array('form-row-wide');
        
        // Billing address
        $fields['billing']['billing_address_1']['class'] = array('form-row-wide');

        // First name
        $fields['billing']['billing_first_name']['autofocus'] = false;

        // Order notes
        $fields['billing']['order_comments']['type'] = 'textarea';
        $fields['billing']['order_comments']['class'] = array('form-row-wide');
        $fields['billing']['order_comments']['label'] = __('Order notes', 'rpd-restaurant-solution');
        $fields['billing']['order_comments']['placeholder'] = __('Order notes about address and products', 'rpd-restaurant-solution');

        // Delivery method
        $fields['billing']['delivery_method']['type'] = 'radio';

        return $fields;
    }

    
    public function changeDeliveryMethodFieldStructure( $field, $key, $args, $value )
    {
        if( $key === 'delivery_method' ) {
            
            if ( !empty( $args['options'] ) ) {

                $options = '';

                $payment_priority = (int) esc_attr( $args['priority'] ) + 10;

                foreach ( $args['options'] as $option_key => $option_text ) {

                    $options .= '<label class="' . esc_attr( $option_key ) . '">';

                        $options .= '<input type="radio" name="' . esc_attr( $key ) . '" value="' . esc_attr( $option_key ) . '">';
    
                        $options .= $this->getDeliveryMethodImage( esc_attr( $option_key ) );
    
                        $options .= '<span>' . esc_html( $option_text ) . '</span>';
    
                    $options .= '</label>';
    
                }

                $field = '<p class="form-row form-row-wide ' . esc_attr( $key ) . ' form-group validate-required" id="' . esc_attr( $args['id'] ) . '" data-priority="' . esc_attr( $args['priority'] ) . '">';
                   
                    $field .= '<label for="' . esc_attr( $key ) . '" class="control-label"> ' . $args['label'] . ' </label>';

                    $field .= '<span class="woocommerce-input-wrapper blue-cards">';
                    
                        $field .= $options;    

                    $field .= '</span>';

                $field .= '</p>';
            }

        }

        return $field;
    }

    public function printPaymentMethods( $fields )
    {
        if( !$this->activated('display_payment_methods') ) {
            return $fields;
        }

        $available_gateways = array();

        if ( WC()->cart->needs_payment() ) {
            $available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
            WC()->payment_gateways()->set_current_gateway( $available_gateways );
        }

        if ( WC()->cart->needs_payment() ) { 
            if ( ! empty( $available_gateways ) ) {
                foreach ( $available_gateways as $key => $value ) {
                    $available_gateways[esc_attr( $key )] = $value->get_title();
                }
            }
        }

        $fields['billing']['payment_method'] = array(
            'label' => esc_html__('Payment method', 'rpd-restaurant-solution'),
            'required' => false,
            'type' => 'select',
            'class' => array('delivery_method form-row-wide'),
            'options'       => $available_gateways
        );
    
        return $fields;
    }

    public function changePaymentMethodFieldStructure( $field, $key, $args, $value )
    {
        if( $key === 'payment_method' ) {
            
            if ( !empty( $args['options'] ) ) {

                $options = '';

                $payment_priority = (int) esc_attr( $args['priority'] ) + 10;

                foreach ( $args['options'] as $option_key => $option_text ) {

                    $options .= '<label class="' . esc_attr( $option_key ) . '">';

                        $options .= '<input type="radio" name="' . esc_attr( $key ) . '" value="' . esc_attr( $option_key ) . '">';
    
                        $options .= $this->getPaymentMethodImage( esc_attr( $option_key ) );
    
                        $options .= '<span>' . esc_html( $option_text ) . '</span>';
    
                    $options .= '</label>';
    
                }

                $field = '<p class="form-row form-row-wide ' . esc_attr( $key ) . ' form-group validate-required" id="' . esc_attr( $args['id'] ) . '" data-priority="' . esc_attr( $args['priority'] ) . '">';
                   
                    $field .= '<label for="' . esc_attr( $key ) . '" class="control-label"> ' . $args['label'] . ' </label>';

                    $field .= '<span class="woocommerce-input-wrapper blue-cards">';
                    
                        $field .= $options;    

                    $field .= '</span>';

                $field .= '</p>';
            }

        }

        return $field;
    }

    public function addCheckoutWrapper( $field, $key, $args, $value )
    {
        if( self::$first_field ) {
            self::$first_field = false;
            
            echo '<div class="wrapper"><h2><span>1</span>';
                _e('Delivery details', 'rpd-restaurant-solution');
            echo '</h2>';
        }

        if( $key == 'billing_first_name' ) {
            echo '</div>';
            echo '<div class="wrapper"><h2><span>2</span>';
                _e('Personal details', 'rpd-restaurant-solution');
            echo '</h2>';
        }

        if( $key == 'payment_method' ) {
            echo '</div>';
            echo '<div class="wrapper"><h2><span>3</span>';
                _e('Delivery and payment method', 'rpd-restaurant-solution');
            echo '</h2>';
        }

        return $field;
    }

    public function addCloseTag( $checkout ) 
    {
        echo '</div>';
    }

    public function insertOnlineDiscount() 
    {
        $plugin_options = $this->getPluginOptions();
        $onlineDiscount = (int)$plugin_options['online_discount'];
        $cartSubtotal = (int) WC()->cart->get_totals()['subtotal'];

        if( $onlineDiscount > 0 ) {
            $discount = $cartSubtotal - ( $cartSubtotal - ($cartSubtotal * $onlineDiscount / 100));
            echo '<tr class="fee"><th>' . __("Online discount ", 'rpd-restaurant-solution') . $onlineDiscount . '%</th><td> - ' . wc_price( $discount ) . '</td></tr>';
        }
    }

    public function insertOnlineDiscountThankyouPage( $order ) 
    {
        $plugin_options = $this->getPluginOptions();
        $onlineDiscount = (int)$plugin_options['online_discount'];
        $cartSubtotal = $order->get_subtotal();

        if( $onlineDiscount > 0 ) {
            $discount = $cartSubtotal - ( $cartSubtotal - ($cartSubtotal * $onlineDiscount / 100));
            echo '<tr><th scope="row">' . __("Online discount ", 'rpd-restaurant-solution') . $onlineDiscount . '%</th><td> - ' . wc_price( $discount ) . '</td></tr>';
        }
    }

    public function insertOnlineDiscountPayOrder( $order )
    {
        $plugin_options = $this->getPluginOptions();
        $onlineDiscount = (int)$plugin_options['online_discount'];
        $cartSubtotal = $order->get_subtotal();

        if( $onlineDiscount > 0 ) {
            $discount = $cartSubtotal - ( $cartSubtotal - ($cartSubtotal * $onlineDiscount / 100));
            echo '<tr><th scope="row" colspan="2">' . __("Online discount ", 'rpd-restaurant-solution') . $onlineDiscount . '%</th><td class="product-total"> - ' . wc_price( $discount ) . '</td></tr>';
        }
    }

    public function insertOrderInfoInThankyouPage( $order )
    {
        $order_id = $order->get_id();

        $is_asap = false;

        if( get_post_meta( $order_id, 'delivery_hour_on', true ) == 'now' ) {
            $is_asap = true;
        }

        if ( get_post_meta( $order_id, 'delivery_method', true ) ) {
            echo '<li class="woocommerce-order-overview__delivery_method ' . ($order->get_customer_note() ? 'with-order-notes' : '') . '">';
                _e( 'Delivery method:', 'rpd-restaurant-solution' );
                echo '<strong>' . ucfirst(wp_kses_post( str_replace('-', ' ', get_post_meta( $order_id, 'delivery_method', true ))) ) .'</strong>';
            echo '</li>';
        }

        if( $is_asap ) {
            _e( '<li class="woocommerce-order-overview__delivery_hour_time">' );
                _e( 'Delivery date and time: ', 'rpd-restaurant-solution' );
                _e( '<strong>' );
                    _e( 'As soon as posible', 'rpd-restaurant-solution' );
                _e( '</strong>' );
            echo '</li>';
        } else if( get_post_meta( $order_id, 'delivery_time', true ) && get_post_meta( $order_id, 'delivery_hour', true ) ) {
            _e( '<li class="woocommerce-order-overview__delivery_hour_time">' );
                _e( 'Delivery date and time: ', 'rpd-restaurant-solution' );
                _e( '<strong>' );
                    echo wp_kses_post( get_post_meta( $order_id, 'delivery_time', true ) ) . ' ' . wp_kses_post( get_post_meta( $order_id, 'delivery_hour', true ) );
                _e( '</strong>' );
            echo '</li>';
        } else if( get_post_meta( $order_id, 'delivery_time', true ) ) {
            _e( '<li class="woocommerce-order-overview__delivery_hour_time">' );
                _e( 'Delivery date: ', 'rpd-restaurant-solution' );
                _e( '<strong>' );
                    echo wp_kses_post( get_post_meta( $order_id, 'delivery_time', true ) );
                _e( '</strong>' );
            echo '</li>';
        } else if( get_post_meta( $order_id, 'delivery_hour', true ) ) {
            _e( '<li class="woocommerce-order-overview__delivery_hour_time">' );
                _e( 'Delivery hour: ', 'rpd-restaurant-solution' );
                _e( '<strong>' );
                    echo wp_kses_post( get_post_meta( $order_id, 'delivery_hour', true ) ); // delivery_hour_time
                _e( '</strong>' );
            echo '</li>';
        }

        if ( get_post_meta( $order_id, 'pickup_point', true ) ) {
            echo '<li class="woocommerce-order-overview__pickup_point ' . ($order->get_customer_note() ? 'with-order-notes' : '') . '">';
                _e( 'Delivery point:', 'rpd-restaurant-solution' );
                echo '<strong>' . ucfirst(wp_kses_post( str_replace('-', ' ', get_post_meta( $order_id, 'pickup_point', true ))) ) .'</strong>';
            echo '</li>';
        }

        if ( $order->get_customer_note() ) {
            echo '<li class="woocommerce-order-overview__customer_note">';
                _e( 'Note:', 'rpd-restaurant-solution' );
                echo '<strong>' . wp_kses_post( $order->get_customer_note() ) . '</strong>';
            echo '</li>';
        }
    }

    public function insertBackButton()
    {
        echo '<p class="back-to-shop"><a href="' . home_url() . '">' . __('Continue shopping', 'rpd-restaurant-solution') . '</a></p>';
    }

    public function changeCouponStructure( $label, $coupon ) 
    {
        if( $coupon->code ) {
            return '<span class="coupon-label">' . __('Coupon:', 'woocommerce') . '</span>' . ' ' . $coupon->code;
        }
    }

    public function changeCouponHtml( $coupon_html, $coupon, $discount_amount_html )
    {
        return $discount_amount_html . ' <a href="' . esc_url( add_query_arg( 'remove_coupon', rawurlencode( $coupon->get_code() ), wc_get_checkout_url() ) ) . '" class="woocommerce-remove-coupon" data-coupon="' . esc_attr( $coupon->get_code() ) . '">' . '<img src="' . $this->plugin_url . 'assets/images/bin.svg' . '">' . '</a>';
    }

    public function addErrorIcon( $error )
    {   
        if( !$error ) {
            return $error;
        }

        if( isset($_GET['show-reset-form']) && $_GET['show-reset-form'] == true ) {
            $error = '<div class="error-login-wrapper"><img class="error-image" src="' . $this->plugin_url . '/assets/images/alert.svg"><div>' . $error . '</div></div>';
        }

        if( isset($_POST['login']) && $_POST['login'] != '' ) {
			$error = '<div class="error-login-wrapper"><img class="error-image" src="' . $this->plugin_url . '/assets/images/alert.svg"><div>' . $error . '</div></div>';
		}

        if( (isset($_POST['register']) && $_POST['register'] != '') || ( isset($_POST['wc_reset_password']) && $_POST['wc_reset_password'] != '' ) ) {
			$error = '<div class="error-login-wrapper"><img class="error-image" src="' . $this->plugin_url . '/assets/images/alert.svg"><div>' . $error . '</div></div>';
		}

        if( ( isset($_POST['woocommerce-process-checkout-nonce']) && $_POST['woocommerce-process-checkout-nonce'] != '' ) || ( isset($_POST['action']) && $_POST['action'] == 'apply_coupon_via_ajax' ) || ( isset($_POST['s_address']) && $_POST['s_address'] != '' ) ) {
            $error = '<img class="error-image" src="' . $this->plugin_url . '/assets/images/alert.svg">' . '<div>' . $error . '</div>';
        }
        
        return $error;
    }

    public function addMessageIcon( $message )
    {   
        if( !$message ) {
            return $message;
        }

        #if( ( isset($_POST['coupon']) && $_POST['coupon'] != '' ) || ( isset($_POST['code']) && $_POST['code'] != '' ) ) {
        $message = '<div class="custom-message"><img class="error-image" src="' . $this->plugin_url . '/assets/images/info.svg">' . '<div>' . $message . '</div></div>';
        #}
        
        return $message;
    }
	
	public function minicartCheckOrderValue()
    {
        $amount = (int) $this->getPluginOptions()['minim_amount_per_order'];
        
        if( $amount <= 0 || WC()->cart->is_empty() || WC()->cart->cart_contents_total >= $amount ) {
            return;
        }

        printf(
            '<div class="amount-banner"><svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20.5835 9.75H9.75016C8.55355 9.75 7.5835 10.72 7.5835 11.9167V18.4167C7.5835 19.6133 8.55355 20.5833 9.75016 20.5833H20.5835C21.7801 20.5833 22.7502 19.6133 22.7502 18.4167V11.9167C22.7502 10.72 21.7801 9.75 20.5835 9.75Z" stroke="#008EFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15.1667 17.3333C16.3633 17.3333 17.3333 16.3633 17.3333 15.1667C17.3333 13.97 16.3633 13 15.1667 13C13.97 13 13 13.97 13 15.1667C13 16.3633 13.97 17.3333 15.1667 17.3333Z" stroke="#008EFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18.4167 9.75002V7.58335C18.4167 7.00872 18.1884 6.45762 17.7821 6.05129C17.3757 5.64496 16.8246 5.41669 16.25 5.41669H5.41667C4.84203 5.41669 4.29093 5.64496 3.8846 6.05129C3.47827 6.45762 3.25 7.00872 3.25 7.58335V14.0834C3.25 14.658 3.47827 15.2091 3.8846 15.6154C4.29093 16.0217 4.84203 16.25 5.41667 16.25H7.58333" stroke="#008EFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' . __( 'Minimum amount order is %1$s', 'rpd-restaurant-solution' ) . '</div>',
            '<span>' . $amount . ' ' . get_woocommerce_currency_symbol() . '</span>'
        );

    }

    public function changeAddressFieldStructureV2()
    {
        if( isset( $_POST['delivery_method'] ) && $_POST['delivery_method'] != 'livrare-la-domiciliu' ) {
            $_POST['billing_address_1'] = '-';
            if( isset( $_POST['billing_city'] ) ) {
                $_POST['billing_city'] = '-';
            }
            if( isset( $_POST['areas'] ) ) {
                $_POST['areas'] = '-';
            }
            if( isset( $_POST['billing_bloc'] ) ) {
                $_POST['billing_bloc'] = '';
            }
            if( isset( $_POST['billing_apartament'] ) ) {
                $_POST['billing_apartament'] = '';
            }
            if( isset( $_POST['billing_scara'] ) ) {
                $_POST['billing_scara'] = '';
            }
            if( isset( $_POST['billing_etaj'] ) ) {
                $_POST['billing_etaj'] = '';
            }
        }
    }

    public function insertMobileAppsBannerInCustomerEmail()
    {
        $html = $androidUrl = $iosUrl = '';
        $route = '/venue/integration/getvenuedetails';
        $result = PushNotification::makeNotification( $route );

        if( $result->status === 200 ) {
            $websiteDetails = json_decode($result->data->websiteSetup);
            $androidUrl = $websiteDetails->urlAndroidApp;
            $iosUrl = $websiteDetails->urlIosApp;

            if( $androidUrl != '' || $iosUrl != '' ) {

                $html .= '<div id="apps-banner" style="padding: 30px 25px; background: url(' . $this->plugin_url . '/assets/images/emails/background-green.png); background-size: cover; background-position:center; background-repeat: no-repeat;">';

                    $html .= '<div id="title-banner-email">';

                        if( $androidUrl != '' && $iosUrl != '' ) {

                            $html .= '<p style="color: #fff;margin: 0 0 20px 0;font-weight: bold;line-height: 28px;font-size:24px;">' . __( 'Descarca aplicatia noastra pentru Android si iOS', 'rpd-restaurant-solution' ) . '</p>';

                        } else if( $androidUrl != '' ) {

                            $html .= '<p style="color: #fff;margin: 0 0 20px 0;font-weight: bold;line-height: 28px;font-size:24px;">' . __( 'Descarca aplicatia noastra pentru Android', 'rpd-restaurant-solution' ) . '</p>';

                        } else if( $iosUrl != '' ) {

                            $html .= '<p style="color: #fff;margin: 0 0 20px 0;font-weight: bold;line-height: 28px;font-size:24px;">' . __( 'Descarca aplicatia noastra pentru iOS', 'rpd-restaurant-solution' ) . '</p>';

                        }

                    $html .= '</div>';

                    $html .= '<div id="app-buttons-emails">';

                        if( $androidUrl != '' ) {

                            $html .= '<a href="' . $androidUrl . '" target="_blank"><img src="' . $this->plugin_url . '/assets/images/emails/google_store_icon.png" style="max-width:110px;width:100%;" /></a>';

                        }

                        if( $iosUrl != '' ) {

                            $html .= '<a href="' . $iosUrl . '" target="_blank"><img src="' . $this->plugin_url . '/assets/images/emails/app_store_icon.png" style="max-width:110px;width:100%;" /></a>';

                        }

                    $html .= '</div>';

                $html .= '</div><br/>';

                echo $html;
            }
        }
    }

    public function insertCustomBannerToCheckoutPage()
    {   
        $html = '';

        if( isset($this->getPluginOptions()["checkout_banner"]) && $this->getPluginOptions()["checkout_banner"] != '' ) {
            $html .= '<div class="wrapper checkout-custom-banner">';
                $html .= $this->getPluginOptions()["checkout_banner"];
            $html .= '</div>';
        }

        echo $html;
    }
	
	public function insertBackButtonInMiniCart()
    {
        echo '<p class="woocommerce-mini-cart__buttons buttons back-to-shopping"><a class="button checkout close-mini-cart">' . __( 'Continue shopping', 'rpd-restaurant-solution' ) . '</a></p>';
    }

    public function insertCustomerAddressDetails( $order )
    {
        if( get_post_meta( $order->get_id(), '_billing_bloc', true ) ) {
            echo __(' Building nr.:', 'rpd-restaurant-solution') . get_post_meta( $order->get_id(), '_billing_bloc', true );
        }

        if( get_post_meta( $order->get_id(), '_billing_apartament', true ) ) {
            echo __(' Flat nr.:', 'rpd-restaurant-solution') . get_post_meta( $order->get_id(), '_billing_apartament', true );
        }

        if( get_post_meta( $order->get_id(), '_billing_scara', true ) ) {
            echo __(' Scale nr.:', 'rpd-restaurant-solution') . get_post_meta( $order->get_id(), '_billing_scara', true );
        }

        if( get_post_meta( $order->get_id(), '_billing_etaj', true ) ) {
            echo __(' Floor:', 'rpd-restaurant-solution') . get_post_meta( $order->get_id(), '_billing_etaj', true );
        }
    }

    public function setCountry() 
    {
        if( WC()->customer->get_shipping_country() == '' ) {
            WC()->customer->set_country('RO');
            WC()->customer->set_shipping_country('RO');
        }
    }

}