<?php
session_start();

include( dirname(__FILE__)."/include/initializer.php");
$shortHeader = "Y";
include(INC_PATH."/templates/header.php");

require_once dirname(__FILE__)."/include/Site.class.php";

//print_r(carrier_stripe::car_add($site, $_SESSION['uid'], '5555555555554444', '7', '2025', '121', 'default_card'));
//carrier_stripe::card_remove($site, $_SESSION['uid'], 'card_15yuVlL9dCisvXvpBiwOybvG');

	require_once("include/stripe/stripe.php");					
	Stripe::setApiKey(STRIPE_API_KEY);	
	
		  // $res_tmp = mysql_qw($site->link, "SELECT * FROM carrier_master");
		  // while($row = mysqli_fetch_assoc($res_tmp)) {
			// print_r($row);
		  // }		
		  
		// $res_tmp = mysql_qw($site->link, "delete FROM carrier_stripe where carrier_id =? ", 91);
		// $cu = Stripe_Customer::retrieve('cus_6CDNDbSRc4ez8c');
			// $cu->delete();
			// $plan = Stripe_Plan::retrieve('driver62');
			// $plan->delete();			

	 $res_tmp = mysql_qw($site->link, "UPDATE carrier_stripe SET trial_date_end = '2015-05-01' WHERE carrier_id = 91");			
			
		  $res_tmp = mysql_qw($site->link, "SELECT * FROM carrier_stripe ");
		  while($row = mysqli_fetch_assoc($res_tmp)) {
			print_r($row);
		  }
		  
	
		  
		  // $res_tmp = mysql_qw($site->link, "SELECT * FROM carrier_users WHERE (usertype = 'dispatch' or usertype = 'driver') ");
		  // while($row = mysqli_fetch_assoc($res_tmp)) {
			// print_r($row);
		  // }
		  
// print_r(carrier_stripe::plan_driver($site, 48 ));	
								// $sys_date = time();
								// $trial_period_day_default = 30;
								// $period_day = 0;
								// $trial_period_days = 0;
							// $trial_date_end = strtotime('2015-06-01');
								
								// //calculate trial count day
									// if ($sys_date < $trial_date_end) {
										// $period_datatime = $trial_date_end - $sys_date;
										// if ($period_datatime > 86400) {
											// $period_day = round($period_datatime /  86400);										
										// }
										// if ($period_day > 0) {
											// $trial_period_days = $period_day;									
										// }										
									// }
									// else {
										// $trial_period_days = $period_day;
									// }

			// print_r($period_day.' day='.	$trial_period_days);					
 
	// require_once("include/stripe/stripe.php");					
	// Stripe::setApiKey(STRIPE_API_KEY);	


			
	// $customer = Stripe_Customer::retrieve('cus_6ATaqL0Uk9AZh7');
	
	// $customer->subscriptions->retrieve('sub_6BBVUBxQsvqNZx')->cancel();	
	// $customer->subscriptions->retrieve('sub_6BBQzS6EUiNwe8')->cancel();	
	// $customer->subscriptions->retrieve('sub_6BB3ZbTChK6SEy')->cancel();	
			// $plan = Stripe_Plan::retrieve('driver48');
			// $plan->delete();	
			// print_r($plan);
			
	//print_r($customer);

	
		  // $res_tmp = mysql_qw($site->link, "SELECT id, carrier_id, email, first, last FROM  carrier_users
						// WHERE id = ? AND status = 'active'  ", 36);
		  // while($row = mysqli_fetch_assoc($res_tmp)) {
			// print_r($row);
		  // }
		  
	//	  print_r(carrier_stripe::plan_driver($site, 36));	
	  
			// $res_tmp = mysql_qw($site->link, "
					// delete FROM carrier_stripe
					// ");
	
				// $res_tmp = mysql_qw($site->link, "
					// delete FROM carrier_users where id = ?
					// ", 36);
					
		// $res_tmp = mysql_qw($site->link, "
				// CREATE TABLE IF NOT EXISTS `carrier_stripe` (
				  // `carrier_id` int(10) unsigned NOT NULL,
				  // `carrier_stripe_id` varchar(255) NOT NULL,
				  // `trial_date_end` datetime NOT NULL,
				  // UNIQUE KEY `carrier_id` (`carrier_id`)
				// ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
				// ");	
				

		  // $res_tmp = mysql_qw($site->link, "SELECT COLUMN_NAME
						// FROM information_schema.COLUMNS
						// WHERE TABLE_SCHEMA = DATABASE()
						  // AND TABLE_NAME = 'carrier_stripe'
						// ORDER BY ORDINAL_POSITION");
		  // while($row = mysqli_fetch_assoc($res_tmp)) {
			// print_r($row);
		  // }

		  		// $res_tmp = mysql_qw($site->link, "
				// CREATE TABLE `countries` (
					// `id` int(11) NOT NULL auto_increment,
					// `country_code` varchar(2) NOT NULL default '',
					// `country_name` varchar(100) NOT NULL default '',
					// PRIMARY KEY (`id`)
					// ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
				// ");	
		
		
			// $res_tmp = mysql_qw($site->link, "SHOW TABLES ");
		  // while($row = mysqli_fetch_assoc($res_tmp)) {
			// print_r($row);
		  // }	
?>