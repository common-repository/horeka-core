<div class="popup-wrapper">
	<div class="popup-body">
		<a href="#" class="close-popup">
			<img src="<?php echo $plugin_url; ?>/assets/images/close.svg">
		</a>
		<div id="page-changePassword-template">
			<div class="row">
				<h2 class="changePaswd"><?php esc_html_e( 'Change password', 'rpd-restaurant-solution' ); ?></h2>
				<div id="emailTxt">
				   <div class="form-group">
					   <input type="text" class="form-control" id="email" name="email" placeholder="<?php esc_html_e( 'Email', 'rpd-restaurant-solution' ); ?>" v-model="emailLg">
				   </div>
				</div>
				<div id="emailBtn">
				   <button type="button" class="btn maxWidth" value="Reseteaza parola" v-on:click="sendPinCode"><?php esc_html_e( 'Reset password', 'rpd-restaurant-solution' ); ?></button>
				</div>

				<div class="hide" id="pincCodeTxt">
				   <div class="form-group">
					   <input type="text" class="form-control" id="pinCode" name="pinCode" placeholder="<?php esc_html_e( 'PIN Code', 'rpd-restaurant-solution' ); ?>" v-model="pinCode">
					 </div>
			   </div>
			   <div class="hide" id="pinCodeBtn">
				   <button type="button" class="btn maxWidth" value="Trimite" v-on:click="receivePinCode"><?php esc_html_e( 'Send', 'rpd-restaurant-solution' ); ?></button>
			   </div>

			   <div class="hide" id="passwordLg">
				   <div class="form-group">
					   <input type="text" class="form-control" id="newPasswordLg" name="newPasswordLg" placeholder="<?php esc_html_e( 'New password', 'rpd-restaurant-solution' ); ?>" v-model="newPasswordLg">
					 </div>
			   </div>
			   <div class="hide" id="passwordBtn">
				   <button type="button" class="btn maxWidth" value="Salveaza" v-on:click="changePasswordLg"><?php esc_html_e( 'Save', 'rpd-restaurant-solution' ); ?></button>
			   </div>
			</div>
		</div>
	</div>
</div>