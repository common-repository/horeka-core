<div class="wrap">
	<h1><?php esc_html_e( 'Horeka Core', 'rpd-restaurant-solution' ); ?></h1>
	<?php settings_errors(); ?>

	<ul class="nav nav-tabs">
		<li class="active"><a href="#tab-1"><?php esc_html_e( 'Manage Settings', 'rpd-restaurant-solution' ); ?></a></li>
		<li><a href="#tab-2"><?php esc_html_e( 'About', 'rpd-restaurant-solution' ); ?></a></li>
	</ul>

	<div class="tab-content">
		<div id="tab-1" class="tab-pane active">

			<form method="post" action="options.php">
				<?php 
					settings_fields( 'rpd_options_group' );
					do_settings_sections( 'rpd_restaurant_solution' );
					submit_button();
				?>
			</form>
			
		</div>

		<div id="tab-2" class="tab-pane">
			<h3><?php esc_html_e( 'About', 'rpd-restaurant-solution' ); ?></h3>
			<p><?php esc_html_e( 'Powered by Roweb', 'rpd-restaurant-solution' ); ?></p>
		</div>
	</div>
</div>