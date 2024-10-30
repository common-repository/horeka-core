<div class="wrap">
	<h1><?php esc_html_e('Distance Manager', 'rpd-restaurant-solution'); ?></h1>
	<?php settings_errors(); ?>

	<ul class="nav nav-tabs">
		<li class="<?php echo !isset($_POST["edit_post"]) ? 'active' : '' ?>"><a href="#tab-1"><?php esc_html_e('Distances', 'rpd-restaurant-solution'); ?></a></li>
		<li class="<?php echo isset($_POST["edit_post"]) ? 'active' : '' ?>">
			<a href="#tab-2">
				<?php echo isset($_POST["edit_post"]) ? esc_html_e('Edit', 'rpd-restaurant-solution' ) : esc_html_e('Add', 'rpd-restaurant-solution' ); ?> <?php esc_html_e('Distance', 'rpd-restaurant-solution'); ?>
			</a>
		</li>
	</ul>

	<div class="tab-content">
		<div id="tab-1" class="tab-pane <?php echo !isset($_POST["edit_post"]) ? 'active' : '' ?>">

			<h3><?php esc_html_e('Manage Your Distances', 'rpd-restaurant-solution'); ?></h3>

			<?php 
				$options = get_option( 'rpd_manage_distances' ) ?: array();

				echo '<table class="cpt-table"><tr><th>' . esc_html__('ID', 'rpd-restaurant-solution') . '</th><th>' . esc_html__('Minimum interval', 'rpd-restaurant-solution') . '</th><th>' . esc_html__('Maximum Interval', 'rpd-restaurant-solution') . '</th><th class="text-center">' . esc_html__('Shipping Cost', 'rpd-restaurant-solution') . '</th><th class="text-center">' . esc_html__('Minimum Amount', 'rpd-restaurant-solution') . '</th><th class="text-center">' . esc_html__('Actions', 'rpd-restaurant-solution') . '</th></tr>';

				foreach ($options as $option) {

					echo "<tr><td>" . str_replace('distance_', '', $option['distance_type']) . "</td><td>{$option['minimum_interval']}</td><td>{$option['maximum_interval']}</td><td>{$option['cost_interval']}</td><td>{$option['minimum_amount_interval']}</td><td class=\"text-center\">";

					echo '<form method="post" action="" class="inline-block">';
					echo '<input type="hidden" name="edit_post" value="' . $option['distance_type'] . '">';
					submit_button( esc_html__('Edit', 'rpd-restaurant-solution'), 'primary small', 'submit', false);
					echo '</form> ';

					echo '<form method="post" action="options.php" class="inline-block">';
					settings_fields( 'rpd_manage_distances_settings' );
					echo '<input type="hidden" name="remove" value="' . $option['distance_type'] . '">';
					submit_button( esc_html__('Delete', 'rpd-restaurant-solution'), 'delete small', 'submit', false, array(
						'onclick' => 'return confirm("Are you sure you want to delete this distance interval?");'
					));
					echo '</form></td></tr>';
				}

				echo '</table>';
			?>
			
		</div>

		<div id="tab-2" class="tab-pane <?php echo isset($_POST["edit_post"]) ? 'active' : '' ?>">
			<form method="post" class="add-form" action="options.php">
				<?php 
					settings_fields( 'rpd_manage_distances_settings' );
					do_settings_sections( 'rpd_manage_distances' );
					submit_button();
				?>
			</form>
		</div>
		
	</div>
</div>