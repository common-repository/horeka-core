<?php   
/**
* Template Name: Fake CHECKOUT
*/

if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
    define( 'WOOCOMMERCE_CHECKOUT', true );
}

get_header(); ?>

    <?php 
		/**
		 * tokoo_before_main_content hook
		 *
		 * @hooked tokoo_wrapper_start - 10 (outputs opening divs for the content)
		 */
		do_action( 'tokoo_before_main_content' );
    ?>
        <div class="posts-holder">
            <article id="post-<?php get_the_ID(); ?>" class="type-page post-7 page status-publish hentry">
                <div class="entry-content">
                    <?php echo do_shortcode('[woocommerce_checkout]'); ?>
                </div>
            </article>
        </div>
    <?php 
		/**
		 * tokoo_after_main_content hook
		 *
		 * @hooked tokoo_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'tokoo_after_main_content' );
    ?>
	
    <div class="terms-popup">
        <iframe src="">
            <p>Your browser does not support iframes.</p>
        </iframe>
        <i class="fa fa-times"></i>
    </div>

<?php get_footer();