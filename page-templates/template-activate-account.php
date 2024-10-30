<?php   
/**
* Template Name: Add to Cart 
*/
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
	<title></title>
	<style>
		#message{
			font-size: 20px;
			font-weight: 600;
			text-align: center;
			margin-left: auto;
			margin-right: auto;
			display: block;
		}
		.alert-ok{
			color: #66ff66;
		}
		.alert-danger{
			color: #ff1a1a;
		}
	</style>
	<script>
        function getContent() {
            let params = new URLSearchParams(location.search);
            var apiKey = params.get("apiKey");
            var userId = params.get("userId");
            var phone = params.get("phone");
            if (apiKey != null && userId != null && phone != null) {
                var data = {
                    UserId: userId,
                    ApiKey: apiKey,
                    Phone: phone
                };
                $.ajax({
					url: 'https://api.rpd.roweb.ro/api/user/updateuserspecialdescription',
                    type: 'POST',
                    data: (data),
                    success: function (data) {
                        console.log(data);
                        if (data.status == 200) {
                            $('#message').text('Contul a fost activat.');
							$('#message').addClass('alert-ok');
                        } else {
							$('#message').text('Contul nu a fost activat.');
							$('#message').addClass('alert-danger');
						}
                    },
                    error: function (data) {
                        console.log(data);
                    }
                });				
            }
        }
	</script>
</head>
<body onload="getContent()">
    <span id="message"></span>
</body>
</html>