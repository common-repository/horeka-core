<div class="wrap">
	<h1><?php esc_html_e('Areas importer', 'rpd-restaurant-solution'); ?></h1>
	
	<?php settings_errors(); ?>

	<div>
		<form method="post" class="add-areas" action="options.php">
			<?php 
				settings_fields( 'rpd_areas_importer_settings' );
				do_settings_sections( 'rpd_areas_importer' );
				submit_button();
			?>
		</form>
	</div>
</div>