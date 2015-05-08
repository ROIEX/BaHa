<?php
include("../initializer.php");

	if ($_POST['btcansel'] == 'cancel') {
	//Calsel
		header("Location: ".SCRIPT_PATH_ROOT."carrier_manager.php");
		die();	
	}

	//submit payment form
		if(($_SESSION['type'] == "carrier") || (EMPTY($_POST['task']))) {	
				$carrier_id = $_SESSION['uid'];
				$task = $_POST['task'];
				$default_card = trim($_POST['default_card']);
				$id_card = trim($_POST['id_card']);				
				$card_data = Array();
				if ($task == 'car_add') {
					$card_data['number'] = trim($_POST['card_number']);
				}
				if (!EMPTY($_POST['expiration_month'])) { $card_data['exp_month'] = trim($_POST['expiration_month']); }
				if (!EMPTY($_POST['expiration_year'])) { $card_data['exp_year'] = trim($_POST['expiration_year']); }
				$card_data['cvc'] = trim($_POST['card_security_code']);
				if (!EMPTY($_POST['fname'])) { $card_data['metadata']['fname'] = trim($_POST['fname']); }
				if (!EMPTY($_POST['lname'])) { $card_data['metadata']['lname'] = trim($_POST['lname']); }
				if (!EMPTY($_POST['country'])) { $card_data['metadata']['country'] = trim($_POST['country']); }
				if (!EMPTY($_POST['address_line1'])) { $card_data['address_line1'] = trim($_POST['address_line1']); }
				if (!EMPTY($_POST['address_line2'])) { $card_data['address_line2'] = trim($_POST['address_line2']); }
				if (!EMPTY($_POST['address_city'])) { $card_data['address_city'] = trim($_POST['address_city']); }
				if (!EMPTY($_POST['address_zip'])) { $card_data['address_zip'] = trim($_POST['address_zip']); }
				if (!EMPTY($_POST['phone_cod'])) { $card_data['metadata']['phone_cod'] = trim($_POST['phone_cod']); }
				if (!EMPTY($_POST['phone_num'])) { $card_data['metadata']['phone_num'] = trim($_POST['phone_num']); }
					
	
			//task form	
				if ($task == 'car_add') {
					carrier_stripe::car_add($site, $carrier_id, $card_data, $default_card);
				}				
				if ($task == 'card_modify') {
					carrier_stripe::card_modify($site, $carrier_id, $id_card, $card_data, $default_card);
				}
				if ($task == 'card_remove') {
					carrier_stripe::card_remove($site, $carrier_id, $id_card);
				}			
		}
		else {
			header("Location: /");
			die();	
		}

?>