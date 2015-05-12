<?php
// site integrate with Stripe

class carrier_stripe {

		public static function payment_info(&$site_obj, $carrier_id, &$carrier_stripe_id = '', &$trial_date_end = '') {
		//check payment information carrier
		
			$result = FALSE;	
		
				$res_tmp = mysql_qw($site_obj->link, "
					SELECT carrier_id, carrier_stripe_id, trial_date_end FROM carrier_stripe
					WHERE carrier_id = ?",
					$carrier_id);
				$row_tmp = mysqli_fetch_array($res_tmp);

				if($row_tmp['carrier_id'] > 0) {					
					$result = TRUE;	
					$carrier_stripe_id = $row_tmp['carrier_stripe_id'];	
					$trial_date_end = $row_tmp['trial_date_end'];						
				}
				// else {
					// //return form payment
					// $_POST['backurl'] = "/temp/form-payment.php";					
				// }
											
			return $result;
		}

		
		public static function control_payment(&$site_obj, $carrier_id) {
		//control of payment
			
			$result = TRUE;

				//add plans for carrier
					
						//load data from carrier
						$carrier_stripe_id = '';
						$trial_date_end = FALSE;
						$carrier_result = self::payment_info($site_obj, $carrier_id, $carrier_stripe_id, $trial_date_end);
						
							if (EMPTY($carrier_stripe_id)) {
							//no stripe_id		
								$result = FALSE;
								//new stripe_id		
								$carrier_stripe_id = self::new_carrier($site_obj, $carrier_id);
								self::payment_info($site_obj, $carrier_id, $carrier_stripe_id, $trial_date_end);
							}	
							
							//Analize date trial
							$sys_date = time();
							$trial_date_end = strtotime($trial_date_end);
							if ($sys_date < $trial_date_end) {
							//Trial period
								$result = TRUE;
							}
							else {
							//The end Trial
								$result = FALSE;
								//Check subscription plans
								$result = self::plan_driver($site_obj, $carrier_id);							
							}
					
			return $result ;
		}
		
		public static function plan_driver(&$site_obj, $carrier_id) {
		//create plans and subscribe carrier
			
			$result = TRUE;
			if ($_SESSION['type'] == "carrier") {

					//add plans for carrier
					$amount = 1500;		
											
					try {		
							require_once("stripe.php");					
							Stripe::setApiKey(STRIPE_API_KEY);
							
							//load data from carrier
							$carrier_stripe_id = '';
							$trial_date_end = FALSE;
							$carrier_result = self::payment_info($site_obj, $carrier_id, $carrier_stripe_id, $trial_date_end);
							
						//Cards
							$customer = Stripe_Customer::retrieve($carrier_stripe_id);
							$cards_data = Stripe_Customer::retrieve($carrier_stripe_id)->sources->all(array("object" => "card"));
							$cards_data_ids = Array();
							if($cards_data) {								
								foreach($cards_data['data'] as $card) {	
									$cards_data_ids[] = $plan['id'];
								}
							}
							if(count($cards_data_ids)) {
								//Get the list of all plans
								$plans_data = Stripe_Plan::all(array("limit" => 10000));							
								foreach($plans_data['data'] as $plan) {
									$plans_data_ids[] = $plan['id'];
								}
									
								//List users added 
								$res_tmp = mysql_qw($site_obj->link, "SELECT * FROM carrier_users WHERE  (usertype = 'dispatch' or usertype = 'driver') AND carrier_id = ?	", $carrier_id);
								while($row = mysqli_fetch_assoc($res_tmp)) {
									$plan_id = $row['usertype'].$row['id'];
									if (!in_array($plan_id, $plans_data_ids)) {
											//new plan
											$plan = Stripe_Plan::create(array(
											  "amount" => $amount,
											  "interval" => "month",
											  "name" => 'Plan: '.$row['first'].' '.$row['last'].' - '.$row['usertype'].' #'.$row['id'].' - month - '. $amount /100 .'$',
											  "currency" => "usd",
											  "id" => $plan_id
											  )
											);	
											//Subscribe plan	
											$customer->subscriptions->create(array(
												"plan" => $plan->id
											));	
									}
								}
							}
							else {
							//No Cards! Could not subscribe to a payment plan
								$result = FALSE;
							}
								
						}							
				
					catch (Stripe_Error $e)
					{
							// The charge failed for some reason. Stripe's message will explain why.
							$message = $e->getMessage();
					}
					catch (Exception $e) 
					{
							// One or more variables was NULL
							$message = $e->getMessage();
					}	
			}
			
			return $result;
		}	

		public static function new_carrier(&$site_obj, $carrier_id) {
		//add new carrier to Stripe

			$result = '';
			
			try {
					require_once("stripe.php");
					Stripe::setApiKey(STRIPE_API_KEY);
					
						// List all customers
						$customer_stripe_id = False;
						$customers_data = Stripe_Customer::all(array("limit" => 100000));

						if($customers_data) {
						//search coctumer ID
							foreach($customers_data['data'] as $customer_one) {
								if ($_SESSION['email'] == $customer_one['email']) {
									$customer_stripe_id = $customer_one['id'];
								}
							}		
						}
						if (EMPTY($customer_stripe_id)) {
						//new token							
							$customer = Stripe_Customer::create(array(
								  "email" => $_SESSION['email'],
								  "description" => 'Customer ID: '.$carrier_id.', email: '.$_SESSION['email']
								)
							);
							$customer_stripe_id = $customer->id;
						}
						
						$return_info = self::payment_info($site_obj, $carrier_id);

						//add payment information into database
						if (!$return_info) {
							$trial_date_end = time() + (30 * 24 * 60 * 60);
									
							$res_tmp = mysql_qw($site_obj->link, "
								INSERT INTO carrier_stripe
								(carrier_id, carrier_stripe_id, trial_date_end)
								VALUES(?,?,?)",
								$carrier_id, $customer_stripe_id, date('Y-m-d', $trial_date_end)
							) or die(mysqli_error($site_obj->link));
						}
						
						$result = $customer_stripe_id;
									
				}
				catch (Stripe_Error $e)
				{
						// The charge failed for some reason. Stripe's message will explain why.
						$message = $e->getMessage();
						echo $message.'<br/>';
						
						//back to url 
						echo '<p><a href="'.SCRIPT_PATH_ROOT.'form-payment.php"><< Back</a>';
				}
				catch (Exception $e) 
				{
						// One or more variables was NULL
						$message = $e->getMessage();
						echo $message.'<br/>';
				}	
				
			return $result;
		}
		
		public static function car_add(&$site_obj, $carrier_id, $card_data, $default_card = '') {
		//add new card
				try {
					require_once("stripe.php");
					Stripe::setApiKey(STRIPE_API_KEY);
					
					$carrier_stripe_id	= NULL;
					carrier_stripe::payment_info($site_obj, $carrier_id, $carrier_stripe_id);
					
					//if carrier no stripe_id
					if ((EMPTY($carrier_stripe_id)) && ($carrier_id > 0)) {
					//add information carrier to Stripe
						//save into database
						$carrier_stripe_id = self::new_carrier($site_obj, $carrier_id);
					}
					
					$customer = Stripe_Customer::retrieve($carrier_stripe_id);					
					$card = $customer->sources->create(array("card" => $card_data));

					//"default_card"
					if ($default_card == 'default_card') {
						$customer->default_source= $card->id;
						$customer->save();  				
					}

					//add meta data
					$card->metadata['phone_cod'] = $card_data['metadata']['phone_cod'];
					$card->metadata['phone_num'] = $card_data['metadata']['phone_num'];
					$card->metadata['fname'] = $card_data['metadata']['fname'];
					$card->metadata['lname'] = $card_data['metadata']['lname'];
					$card->metadata['country'] = $card_data['metadata']['country'];					
					$card->save();
					
					//back to url 
					header("Location: ".SCRIPT_PATH_ROOT."carrier_manager.php");
					die();	
									
				}
				catch (Stripe_Error $e)
				{
						// The charge failed for some reason. Stripe's message will explain why.
						$message = $e->getMessage();
						echo $message.'<br/>';
						
						//back to url 
						echo '<p><a href="'.SCRIPT_PATH_ROOT.'cards_form.php"><< Back</a>';
						
						//delete secret data
						unset($card_data['number']);
						unset($card_data['cvc']);						
						//save form data
						$_SESSION['store'] = $card_data;
				}
				catch (Exception $e) 
				{
						// One or more variables was NULL
						$message = $e->getMessage();
						echo $message.'<br/>';
				}	
				
			return $result;
		}	

		public static function card_modify(&$site_obj, $carrier_id, $id_card, $card_data, $default_card = '') {
		//modify card
				try {
					require_once("stripe.php");
					Stripe::setApiKey(STRIPE_API_KEY);
					
					$carrier_stripe_id	= NULL;
					carrier_stripe::payment_info($site_obj, $carrier_id, $carrier_stripe_id);
					$customer = Stripe_Customer::retrieve($carrier_stripe_id);
					
					$card = $customer->sources->retrieve( $id_card);
					$card->exp_year =  $card_data['exp_year'];
					$card->exp_month = $card_data['exp_month'];
					$card->metadata['country'] = $card_data['metadata']['country'];
					$card->address_line1 = $card_data['address_line1'];
					$card->address_line2 = $card_data['address_line2'];
					$card->address_city = $card_data['address_city'];
					$card->address_zip = $card_data['address_zip'];
					$card->metadata['phone_cod'] = $card_data['metadata']['phone_cod'];
					$card->metadata['phone_num'] = $card_data['metadata']['phone_num'];
					$card->metadata['fname'] = $card_data['metadata']['fname'];
					$card->metadata['lname'] = $card_data['metadata']['lname'];

					$card->save();

					//"default_card"
					if ($default_card == 'default_card') {
						$customer->default_source = $card->id;
						$customer->save();  				
					}						
					
					//back to url 
					header("Location: ".SCRIPT_PATH_ROOT."carrier_manager.php");
					die();	
				}
				catch (Stripe_Error $e)
				{
						// The charge failed for some reason. Stripe's message will explain why.
						$message = $e->getMessage();
						echo $message.'<br/>';
						
						//back to url 
						echo '<p><a href="'.SCRIPT_PATH_ROOT.'cards_form.php"><< Back</a>';
						//delete secret data
						unset($card_data['number']);
						unset($card_data['cvc']);						
						//save form data
						$_SESSION['store'] = $card_data;						
				}
				catch (Exception $e) 
				{
						// One or more variables was NULL
						$message = $e->getMessage();
						echo $message.'<br/>';
				}	
				
			return $result;
		}			
		
		public static function card_remove(&$site_obj, $carrier_id, $id_card) {
		//remove card
				try {
					require_once("stripe.php");
					Stripe::setApiKey(STRIPE_API_KEY);
					
					$carrier_stripe_id	= NULL;
					carrier_stripe::payment_info($site_obj, $carrier_id, $carrier_stripe_id);
					$customer = Stripe_Customer::retrieve($carrier_stripe_id);
					
					$customer->sources->retrieve($id_card)->delete();
					
					//back to url 
					header("Location:".SCRIPT_PATH_ROOT."carrier_manager.php");
					die();	
				}
				catch (Stripe_Error $e)
				{
						// The charge failed for some reason. Stripe's message will explain why.
						$message = $e->getMessage();
						echo $message.'<br/>';
						
						//back to url 
						echo '<p><a href="'.SCRIPT_PATH_ROOT.'carrier_manager.php"><< Back</a>';
				}
				catch (Exception $e) 
				{
						// One or more variables was NULL
						$message = $e->getMessage();
						echo $message.'<br/>';
				}	
				
			return $result;
		}			
}		

class carrier_general {
//general methods
		public static function load_user(&$site_obj, $user_id) {
		//load data from drivers and 
			$user_array = Array();
				$res = mysql_qw($site_obj->link, "
                     SELECT * FROM carrier_users WHERE id = ?",
                      $user_id);
                 while($row = mysqli_fetch_assoc($res)) {
                    $user_array['id'] = $row['id'];
					$user_array['first'] = $row['first'];
					$user_array['last'] = $row['last'];
					$user_array['username'] = $row['username'];
					$user_array['email'] = $row['email'];
					$user_array['status'] = $row['status'];
					$user_array['usertype'] = $row['usertype'];
					$user_array['last_session'] = $row['last_session'];		 
				 }	
			return $user_array;
		}
		
		public static function delete_user(&$site_obj, $user_id, $carrier_id) {
		//load data from drivers and 
			$result = TRUE;
			//delete user plan in Stripe
				require_once("stripe.php");
				Stripe::setApiKey(STRIPE_API_KEY);
				//load data from carrier
					$carrier_stripe_id = '';
					$carrier_result = carrier_stripe::payment_info($site_obj, $carrier_id, $carrier_stripe_id);
					$customer = Stripe_Customer::retrieve($carrier_stripe_id);
				//Load data from user
					$user_array = self::load_user($site_obj, $user_id);
					$plan_id = $user_array['usertype'].$user_id;					
			
					//Cancel a subscription plan
					$plans_customer = $customer->subscriptions['data'];	
					foreach($plans_customer as $plan_customer) {
						if ($plan_customer['plan']->id == $plan_id) {
							$customer_subscriptions_id = $plan_customer['id'];
							$customer->subscriptions->retrieve($customer_subscriptions_id)->cancel();
							//delete plan
							$plan = Stripe_Plan::retrieve($plan_id);
							$plan->delete();
						}
					}	

				//delete in database
					$res = mysql_qw($site_obj->link, "delete FROM carrier_users WHERE id = ? AND carrier_id = ?", $user_id, $carrier_id);
					  
			return $result;
		}		
}	
	
	
?>