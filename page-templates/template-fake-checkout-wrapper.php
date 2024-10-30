<?php   
/**
* Template Name: Fake CHECKOUT Wrapper
*/

get_header(); ?>
	
	<?php echo '<iframe id="secondCheckout" src="' . home_url() . '/checkout-2" frameborder="0" height="100%" width="100%"></iframe>'; ?>
		
<?php get_footer();