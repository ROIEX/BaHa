<?php
include("../initializer.php");
	//submit payment form
		if(($_SESSION['type'] == "carrier") || ($_SESSION['type'] == "dispatch")) {	
				$carrier_id = $_SESSION['uid'];
				$card_number = trim($_POST['card_number']);
				$expiration_month = trim($_POST['expiration_month']);
				$expiration_year = $_POST['expiration_year'];
				$card_security_code = trim($_POST['card_security_code']);
				carrier_stripe::payment_insert($site, $carrier_id, $card_number, $expiration_month, $expiration_year, $card_security_code);
		}
?>