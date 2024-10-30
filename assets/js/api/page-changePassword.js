if (document.getElementById("page-changePassword-template")) {
	new Vue({
		el: '#page-changePassword-template',
		data() {
			return {
				emailLg: '',
				newPasswordLg: '',
				pinCode: ''
			}
		},

		computed: {
		},
		watch: {
		},

		created: function () {
			var self = this;
			venueService().getVenueDetails().done(function (responseData) {
				var settings = JSON.parse(responseData.data.websiteSetup);
				jQuery('.maxWidth').css('background-color', settings.mainColor);
				_venueId = responseData.data.id;
			}).fail(function (res) {
			});
		},

		beforeMount: function () {
		},

		methods: {

			sendPinCode() {
				if (this.emailLg !== "") {
					var data = {
						VenueId: _venueId,
						Email: this.emailLg
					};
					userService().getUserByEmail(data).done(function (responseData) {
						if(responseData.data == true) {
							jQuery('#pincCodeTxt').removeClass('hide');
							jQuery('#pinCodeBtn').removeClass('hide');
							jQuery('#pincCodeTxt').addClass('show');
							jQuery('#pinCodeBtn').addClass('show');

							jQuery('#emailTxt').addClass('hide');
							jQuery('#emailBtn').addClass('hide');
							alert('A fost trimis pe e-mail un cod pin pentru resetarea parolei');
						}
					}).fail(function (res) {
						if (res.responseJSON) {
							if (res.responseJSON.status === 404) {
								alert('Email-ul nu exista');
							}
						}
					});
				} else {
					alert('Campul de e-mail este obligatoriu');
				}
			},
	
			receivePinCode() {
				if (this.pinCode !== "") {
					var data = this.pinCode + "," + _venueId;
					userService().getPin(data).done(function (responseData) {
						jQuery('#pincCodeTxt').removeClass('show');
						jQuery('#pinCodeBtn').removeClass('show');
						jQuery('#pincCodeTxt').addClass('hide');
						jQuery('#pinCodeBtn').addClass('hide');
	
						jQuery('#passwordLg').removeClass('hide');
						jQuery('#passwordBtn').removeClass('hide');
						jQuery('#passwordLg').addClass('show');
						jQuery('#passwordBtn').addClass('show');
						_userId = responseData.data.userId;
					}).fail(function (res) {
						if (res.responseJSON) {
							if (res.responseJSON.status === 410) {
								alert('Pin expirat');
							} else {
								alert('Pin invalid');
							}
						}
					});
				} else {
					alert('Campul cod pin este obligatoriu');
				}
			},
	
			changePasswordLg() {
				const self = this;
				if (this.newPasswordLg !== "") {
					var data = {
						"userId": _userId,
						"currentPassword": this.pinCode,
						"newPassword": this.newPasswordLg,
					}
					userService().updatePasswordWithPin(data).done(function (responseData) {
						if (responseData.status == 200) {
							alert('Parola a fost salvata');
							location.reload();
						}
					}).fail(function (res) {
					});
				} else {
					alert('Campul parola este obligatoriu')
				}
			}
		}
	});
}