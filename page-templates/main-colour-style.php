<?php if( get_field( 'website_main_color', 'option' ) ) { ?>
    <?php $main_color = get_field( 'website_main_color', 'option' ); ?>
    <style type="text/css">
    a, a:hover, a:focus {
        color: <?php echo $main_color; ?>;
    }
    .btn-primary-slide {
        background-color: <?php echo $main_color; ?>;
    }
    .btn-primary-slide:hover {
        box-shadow: none;
    }
    .header .main-header .navigation ul li:hover a, .header .top-header a:hover, .header .main-header .header_action ul li:hover i {
        color: <?php echo $main_color; ?>;
    }
    .cart_trigger .counter {
        background: <?php echo $main_color; ?>;
    }
    .header .main-header .navigation .menu-primary-menu-container ul ul.sub-menu li:hover a {
        background: <?php echo $main_color; ?>;
    }
    .product_box .inner-menu-box .price {
        color: <?php echo $main_color; ?>;
    }
    .btn-outline-menu-white:hover {
        background: <?php echo $main_color; ?>;
        border-color: <?php echo $main_color; ?>;
        box-shadow: none;
    }
    #meniul-nostru h2:after, #recomandate-section h2:after {
        background: <?php echo $main_color; ?>;
    }
    body#light .btn-outline-white:hover {
        background: <?php echo $main_color; ?>;
        border-color: <?php echo $main_color; ?>;
    }
    .product_list .price {
        color: <?php echo $main_color; ?>;
    }
    .footer h3 span {
        border-bottom: 4px solid <?php echo $main_color; ?>;
    }
    .custom-footer #backtotop {
        background: <?php echo $main_color; ?>;
    }
    .header .mobile-header .menu-primary-menu-container > ul > li:hover, .header .mobile-header .menu-primary-menu-container ul ul.sub-menu li:hover {
        background: <?php echo $main_color; ?>;
    }
    .product__details .price .amount {
        color: <?php echo $main_color; ?>;
    }
    .single-product .summary .price .woocommerce-Price-amount.amount .woocommerce-Price-currencySymbol {
        color: <?php echo $main_color; ?>;
    }
    .btn-outline-primary {
        background: <?php echo $main_color; ?>;
    }
    .related.products > h2:after {
        background: <?php echo $main_color; ?>;
    }
    .woocommerce-mini-cart .quantity-plus-minus .btnMinus, .woocommerce-mini-cart .quantity-plus-minus .btnPlus {
        background: <?php echo $main_color; ?>;
    }
    .woocommerce-mini-cart__buttons a.button {
        background: <?php echo $main_color; ?>;
    }
    .woocommerce-mini-cart__buttons a.button.checkout:hover {
        background: <?php echo $main_color; ?>;
        box-shadow: none;
    }
    .woocommerce-info {
        border-top-color: <?php echo $main_color; ?>;
    }
    .woocommerce-info:before {
        color: <?php echo $main_color; ?>;
    }
    .search-form-box {
        border-top-color: <?php echo $main_color; ?>;
    }
    .main-search-form .search-form-box.active .close_btn i {
        color: <?php echo $main_color; ?>;
    }
    #despre-section .inner-image:before {
        background-color: <?php echo $main_color; ?>;
    }
    .rtb-booking-form button {
        background: <?php echo $main_color; ?>;
    }
    .footer address .contact-item .social-icon i {
        color: <?php echo $main_color; ?>;
    }
    .footer .footer-menu ul li a:hover {
        color: <?php echo $main_color; ?>;
    }
    .contact-info-block .telefon-section i, .contact-info-block .locatie-section i {
        color: <?php echo $main_color; ?>;
    }
    .contact-info-block .telefon-section .details .call_action, .contact-info-block .locatie-section .details .call_action {
        color: <?php echo $main_color; ?>;
    }
    .contact-info-block .form-block form input:not([type="submit"]):focus, .contact-info-block .form-block form textarea:focus {
        border-color: <?php echo $main_color; ?>!important;
    }
    .contact-info-block .form-block form input[type="submit"] {
        background: <?php echo $main_color; ?>;
    }
    div.product-addon-totals ul .wc-pao-col1 span {
        color: <?php echo $main_color; ?>;
    }
    .woocommerce .cart .single_add_to_cart_button {
        background-color: <?php echo $main_color; ?>!important;
    }
    .woocommerce-billing-fields__field-wrapper .wrapper h2 span {
        background-color: <?php echo $main_color; ?>!important;
    }
    .blue-cards label.active {
        border: 1px solid <?php echo $main_color; ?>;
    }
    .blue-cards label svg.livrare-la-domiciliu path, .blue-cards label svg.ridicare-personala path {
        fill: <?php echo $main_color; ?>;
    }
    .blue-cards label svg path {
        stroke: <?php echo $main_color; ?>;
    }
    .woocommerce form .form-row .required {
        color: <?php echo $main_color; ?>;
    }
    .wrapper.order-details .cart_item .product-name .product-quantity {
        color: <?php echo $main_color; ?>;
    }
    .coupon-label {
        color: <?php echo $main_color; ?>;
    }
    .woocommerce-checkout a {
        color: <?php echo $main_color; ?>;
    }
    #place_order, #place_order_fake {
        background: <?php echo $main_color; ?>;
    }
    .wrapper.order-details p svg path {
        stroke: <?php echo $main_color; ?>;
    }
    .wrapper.order-receipt table.shop_table td.product-name .product-quantity {
        color: <?php echo $main_color; ?>;
    }
    .orange-bullet {
        background-color: <?php echo $main_color; ?>!important;
    }
    .woocommerce nav.woocommerce-pagination ul li span.current {
        background: <?php echo $main_color; ?>;
        border: 1px solid <?php echo $main_color; ?>;
    }
    .woocommerce nav.woocommerce-pagination ul li a, .woocommerce nav.woocommerce-pagination ul li span {
        color: <?php echo $main_color; ?>;
    }
    .woocommerce nav.woocommerce-pagination ul li a:focus, .woocommerce nav.woocommerce-pagination ul li a:hover {
        background: <?php echo $main_color; ?>;
        border: 1px solid <?php echo $main_color; ?>;
    }
    ul.products .product .image_inner a:before {
        background: <?php echo $main_color; ?>;
    }
    .btn-outline-primary:hover, .btn-outline-primary:focus {
        background: <?php echo $main_color; ?>;
        box-shadow: none;
    }
    .wrapper.login .woocommerce-info .showlogin {
        background-color: <?php echo $main_color; ?>!important;
    }
    .wrapper.login .woocommerce-form-login button {
        background: <?php echo $main_color; ?>;
    }
    .contact-info-block .form-block form input[type="submit"] {
        background: <?php echo $main_color; ?>;
    }
    .wpcf7 form.invalid .wpcf7-response-output, .wpcf7 form.unaccepted .wpcf7-response-output, .wpcf7 form.payment-required .wpcf7-response-output {
        border-color: <?php echo $main_color; ?>;
    }
    .contact-info-block .form-block form input[type="submit"]:focus, .contact-info-block .form-block form input[type="submit"]:active {
        background: <?php echo $main_color; ?>;
    }
    .forms__account h2 svg path {
        stroke: <?php echo $main_color; ?>;
    }
    .toggle_actions_account .button__login .nav-link.active {
        background: <?php echo $main_color; ?>!important;
    }
    .toggle_actions_account .button__register .nav-link.active {
        background: <?php echo $main_color; ?>!important;
    }
    </style>
<?php } ?>