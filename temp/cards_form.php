<?php
include(dirname(__FILE__)."/include/initializer.php");
$shortHeader = "Y";
include(INC_PATH."/templates/header.php");
	$id_card = $_GET['idc'];
	$card_default = FALSE;
	if(($_SESSION['type'] == "carrier") || (!EMPTY($id_card))) {	
	//task for form
		$task_name = 'Add a Credit or Debit card';
		$button_title = 'Add Card';
		$task = $_GET['task'];
		//Load data from cards
			require_once("include/stripe/stripe.php");					
			Stripe::setApiKey(STRIPE_API_KEY);
			$carrier_stripe_id	= NULL;
			carrier_stripe::payment_info($site, $_SESSION['uid'], $carrier_stripe_id);
			if (!EMPTY($carrier_stripe_id)) {
				$customer = Stripe_Customer::retrieve($carrier_stripe_id);	
			}
		if (EMPTY($task)) {
			$task = 'car_add';		
		}
		if ($task == 'card_modify') {
			$task_name = 'Modify credit card';
			$button_title = 'Save Changes';	
			$card = $customer->sources->retrieve($id_card);	
			if ($customer->default_source ==  $id_card) { $card_default = TRUE; }				
		}
		if ($task == 'card_remove') {
		$card = $customer->sources->retrieve($id_card);	
			$task_name = 'Remove credit card';
			$button_title = 'Yes, Remove';
					
		}		
	}	
	else {
		header("Location: /");
		die();	
	}
	//if a error when entering data - restore them
	if (!EMPTY($_SESSION['store'])) {	
			if (EMPTY($card)) { 
				$card = new StdClass;
				$card->metadata = new StdClass;
			}
			$card = (object) $_SESSION['store'];
			$card->metadata = (object) $_SESSION['store']['metadata'];
			
			unset($_SESSION['store']);
	}
//print_r($card->metadata);
?>
	<script src="/temp/include/stripe/js/jquery.js"></script>
	<?php if('car_add' == $task):?>
		<script src="/temp/include/stripe/js/card-controller.js"></script>
	<?php endif?>
<?php
	if($_SESSION['type'] == "carrier"):?>
	
    <div class="carrier_admin_wrapper">
		<!-- block-left -->
            <?php include(INC_PATH."/templates/left-menu.php");?>
		
		<div class="admin_right <?=($_SESSION['type'] == "dispatch")?' admin_right2':''?>">
        <h2><?php echo $task_name; ?></h2>
		
			<br/>
		<?php if(($task == 'car_add') || ($task == 'card_modify')):?>
			<h4>Payment Information</h4>
			<div class="strangeline"></div>
		<?php endif?>
	 
        <form id="buy-form" method="post" action="<?php echo SCRIPT_PATH_ROOT ?>include/stripe/submit_cards_form.php">
		    <input type="hidden" name="task" value="<?php echo $task ?>">
			<input type="hidden" name="id_card" value="<?php echo $id_card ?>">
 
			<?php if($task == 'card_modify'):?>	
				<p class="form-label">Status:</p>				
				<p> <?php if ($card_default) { 
							echo '<p>This is your Primary Payment Method </p>'; 
						} 
						else {
							echo '<p>This is your additional Payment Method</p>'; 
						}				
					?> </p> 
			<?php endif?>	 
			 
			<?php if(($task == 'car_add') || ($task == 'card_modify')):?>
				<p class="form-label">First Name:</p>
				<input class="text" id="first-name" name="fname" size="40" spellcheck="false" value="<?php if (!EMPTY($card->metadata->fname)) { echo $card->metadata->fname; } ?>"></input>
				 
				<p class="form-label">Last Name:</p>
				<input class="text" id="last-name" name="lname"  size="40" spellcheck="false" value="<?php if (!EMPTY($card->metadata->lname)) { echo $card->metadata->lname; } ?>"></input>	
				
			 <?php endif?>			

			<?php if($task == 'card_modify'):?>	
				<p class="form-label">Credit Card Number:</p>
				<p> <?php echo $card['brand'].' '.$card['object'].' ending in '.$card['last4']; ?></p>
				<input type="hidden" value="Credit Card Number" class="text" id="card-number" name="card_number"  size="20" autocomplete="off"></input>	
			<?php endif?>
			
			<?php if($task == 'car_add'):?>	
		
				<p class="form-label">Credit Card Number:</p>
				<input class="text" id="card-number" name="card_number"  size="20" autocomplete="off"></input>	
			
			 <?php endif?>

			<?php if(($task == 'car_add') || ($task == 'card_modify')):?>
			
				<p class="form-label">Expiration Date:</p>
				<select id="expiration-month" name="expiration_month">	
					<?php 
					for ($i = 1; $i <= 12; $i++) {
						if ( (int)$card->exp_month == $i) {
							echo '<option selected value="'. (int) $card->exp_month.'">'.date('F',mktime(0,0,0, (int) $card->exp_month)).'</option>';
						}
						else {
							echo '<option value="'.$i.'">'.date('F',mktime(0,0,0, $i)).'</option>';
						}
					}
					?>												
				</select>
				 
				<select id="expiration-year" name="expiration_year">
					<?php 
						$yearRange = 20;
						$thisYear = date('Y');
						$startYear = ($thisYear + $yearRange);

						foreach (range($thisYear, $startYear) as $year) 
						{
							if (( $year == $thisYear) || ($year == $card->exp_year)) {
								print '<option selected value="'.$year.'">' . $year . '</option>';
							} else {
								print '<option value="'.$year.'">' . $year . '</option>';
							}
						}
						
					?>
				</select>
				
			
				<p class="form-label">CVC:</p>
					<input class="text" id="card-security-code" name="card_security_code" size="4" autocomplete="off"></input>

				<p class="form-label">Country:</p>
				<select id="country" name="country" size="1" size="40">	
					<?php 
						  $res_tmp = mysql_qw($site->link, "SELECT * FROM countries");
						  while($row_tmp = mysqli_fetch_assoc($res_tmp)) {
								if ($row_tmp['country_name'] == $card->metadata->country) {
									print '<option  selected value="'.$row_tmp['country_name'].'">' . $row_tmp['country_name'] . '</option>';								
								}
								else {
									print '<option value="'.$row_tmp['country_name'].'">' . $row_tmp['country_name'] . '</option>';
								}
						  }						
					?>
				</select>

				<p class="form-label">Address:</p>
				<input class="text" id="address-line1" name="address_line1" size="40" spellcheck="false" value="<?php if (!EMPTY($card->address_line1)) { echo $card->address_line1; } ?>"></input>
				<p></p>
				<input class="text" id="address-line2" name="address_line2" size="40" spellcheck="false" value="<?php if (!EMPTY($card->address_line2)) { echo $card->address_line2; } ?>"></input>

				<p class="form-label">City:</p>
				<input class="text" id="address-city" name="address_city" size="40" spellcheck="false" value="<?php if (!EMPTY($card->address_city)) { echo $card->address_city; } ?>"></input>
				
				<p class="form-label">ZIP Code:</p>
				<input class="text" id="address-zip" name="address_zip" size="15" spellcheck="false" value="<?php if (!EMPTY($card->address_zip)) { echo $card->address_zip; } ?>"></input>
				
				<p class="form-label">Phone:</p>
				<div style="float: left;">
					<span> + </span>
					<span> ( </span>
					<input class="text" id="phone-cod" name="phone_cod" size="5" spellcheck="false" value="<?php if (!EMPTY($card->metadata->phone_cod)) { echo $card->metadata->phone_cod; } ?>"></input>
					<span> ) </span>
					<input class="text" id="phone-num" name="phone_num" size="12" spellcheck="false" value="<?php if (!EMPTY($card->metadata->phone_num)) { echo $card->metadata->phone_num; } ?>"></input>
				</div>
				
			<?php endif?>
			
			<?php if(($task == 'car_add') || ($task == 'card_modify')):?>	
				<br/><br/>			
				<p><input type="checkbox" name="default_card" value="default_card" <?php if ($card_default) { echo 'checked'; } ?> >Set the default card </p>
			<?php endif?>			
			
			<?php if($task == 'card_remove'):?>	
				<p class="form-label">Remove your <?php echo $card['brand'].' '.$card['object'].' ending in '.$card['last4']; ?>?</p>
			<?php endif?>
			
			<br/>

			 <p> <button name="<?php echo $task ?>" value="<?php echo $task ?>"><?php echo $button_title ?></button> <a href="<?php echo SCRIPT_PATH_ROOT ?>carrier_manager.php">Cancel</a> </p> 		
        </form>
		</div>
		
    </div>	

<?php endif?>
	
<?php include(INC_PATH."/templates/footer.php");?>