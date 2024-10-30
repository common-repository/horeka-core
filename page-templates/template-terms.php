<?php
/**
 * Template Name: Blank Template
 * Used for Terms
 */
?>
<html>
    <head>
        <?php wp_head(); ?>
        <meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1">
    </head>
    <body class="blank-template">
        <div class="container">
            <div class="main_container">
                <?php the_content(); ?>
            </div>
        </div><!-- #content -->
        <?php wp_footer(); ?>
    </body>
</html>