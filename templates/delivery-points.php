<div class="wrap">
	<h1><?php esc_html_e( 'Delivery Points', 'rpd-restaurant-solution' ); ?></h1>
	<?php settings_errors(); ?>

	<ul class="nav nav-tabs">
		<li class="<?php echo !isset($_POST["edit_post"]) ? 'active' : '' ?>"><a href="#tab-1"><?php esc_html_e('Delivery Points', 'rpd-restaurant-solution'); ?></a></li>
		<li class="<?php echo isset($_POST["edit_post"]) ? 'active' : '' ?>">
			<a href="#tab-2">
				<?php echo isset($_POST["edit_post"]) ? esc_html__('Edit', 'rpd-restaurant-solution' ) : esc_html__('Add', 'rpd-restaurant-solution' ); ?> <?php esc_html_e( 'Delivery Point', 'rpd-restaurant-solution' ); ?>
			</a>
		</li>
	</ul>

	<div class="tab-content">
		<div id="tab-1" class="tab-pane <?php echo !isset($_POST["edit_post"]) ? 'active' : '' ?>">

			<h3><?php esc_html_e('Manage Your Delivery Points', 'rpd-restaurant-solution'); ?></h3>

			<?php 
				$options = get_option( 'rpd_delivery_points' ) ?: array();

				echo '<table class="cpt-table"><tr><th>' . esc_html__('Name', 'rpd-restaurant-solution') . '</th><th>' . esc_html__('Discount', 'rpd-restaurant-solution') . '</th><th>' . esc_html__('Active', 'rpd-restaurant-solution') . '</th><th>' . esc_html__('Do not send to POS', 'rpd-restaurant-solution') . '</th><th class="text-center">' . esc_html__('Actions', 'rpd-restaurant-solution') . '</th></tr>';

				foreach ($options as $key => $value) {
					
					echo "<tr><td>" . $value['method_name'] . "</td><td>" . $value['method_discount'] . "</td><td>" . ($value['method_status'] ? esc_html__('Yes', 'rpd-restaurant-solution') : esc_html__('No', 'rpd-restaurant-solution') ) . "</td><td>" . ($value['send_to_pos'] ? esc_html__('Yes', 'rpd-restaurant-solution') : esc_html__('No', 'rpd-restaurant-solution') ) . "</td><td class=\"text-center\">";

					echo '<form method="post" action="options.php" class="inline-block '.$key.'">';
					settings_fields( 'rpd_delivery_points_settings' );
					echo '<input type="hidden" name="remove" value="' . $key . '">';
					submit_button( esc_html__('Delete', 'rpd-restaurant-solution'), 'delete small', 'submit', false, array(
						'onclick' => 'return confirm("Are you sure you want to delete this delivery point?");'
					));
					echo '</form>';
					
					echo '</td></tr>';
				}

				echo '</table>';
			?>
			
		</div>

		<div id="tab-2" class="tab-pane <?php echo isset($_POST["edit_post"]) ? 'active' : '' ?>">
			<form method="post" class="add-delivery-method" action="options.php">
				<?php 
					settings_fields( 'rpd_delivery_points_settings' );
					do_settings_sections( 'rpd_delivery_points' );
					submit_button();
				?>
			</form>
		</div>
		
	</div>
</div>