<?php
/**
 * Template Name: Tracking Page
 */
?>
<html>
    <head>
    <?php wp_head(); ?>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <style>
            #message{
                font-size: 20px;
                font-weight: 600;
                text-align: center;
                margin-left: auto;
                margin-right: auto;
                display: block;
                padding: 30px 0 0;
            }
            .orange-bullet{
                display: none!important;
            }
        </style>
    </head>
    <body onload="getContent()">
        <span id="message"><?php echo esc_html__('You will be redirected in a few seconds to the order status tracking page.', 'rpd-restaurant-solution'); ?></span>
        <?php wp_footer(); ?>
        <script>
            function getContent() {
                let params = new URLSearchParams(location.search);
                var apiKey = params.get("apiKey");
                var orderId = params.get("orderId");
                var language = params.get("language");
                
                if (apiKey != null && orderId != null) {
                    var data = {
                        OrderId: orderId,
                        ApiKey: apiKey
                    };
                    jQuery.ajax({
                        beforeSend: function(request) {
                            request.setRequestHeader("Accept-Language", language);
                        },
                        url: 'https://api.rpd.roweb.ro/api/order/integration/gettrackinglink',
                        type: 'POST',
                        data: (data),
                        success: function (result) {
                            if( result.data != undefined ) {
                                window.location.href = result.data;
                            }
                        },
                        error: function (error) {
                            console.log(error);
                        }
                    });				
                }
            }
        </script>
    </body>
</html>