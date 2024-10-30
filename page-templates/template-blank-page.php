<?php
/**
 * Template Name: White Template
 * Used for Mobile Application
 */

?>
<html>
    <head>
        <?php wp_head(); ?>
        <meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1">
    </head>
    <body class="blank-template white" id="<?php echo ( function_exists('get_field') ? ( get_field('theme_color', 'option') ? 'light' : 'dark' ) : '' ) ?>">
        <div class="container">
            <div class="main_container">
                <?php the_content(); ?>
            </div>
        </div><!-- #content -->
        <?php wp_footer(); ?>
    </body>
</html>