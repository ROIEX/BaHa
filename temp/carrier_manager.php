<?php
include(dirname(__FILE__)."/include/initializer.php");
$shortHeader = "Y";
include(INC_PATH."/templates/header.php");?>
<?php
if($_SESSION['type'] == "carrier" || $_SESSION['type'] == "dispatch"):?>
    <div class="carrier_admin_wrapper">
        <div class="title">Site administration</div> 
        <!-- block-left -->
			<?php include(INC_PATH."/templates/left-menu.php");?>
        <div class="admin_right <?=($_SESSION['type'] == "dispatch")?' admin_right2':''?>">
        	<?if($_SESSION['type'] == "carrier"):?>
            <div class="blueline-container">
                <span class="title1">Billing Methods</span>
                <form id="car_add" method="POST" action="<?=SCRIPT_PATH_ROOT?>cards_form.php" autocomplete="off">
                    <input type="hidden" name="action" value="cards">
                    <input type="hidden" name="backurl" value="<?=SCRIPT_PATH_ROOT?>carrier_manager.php">
					<br/>
					<br/>
					<p align="left"><a class="button js-submit" data-formid="card_add">Add Billing Methods</a></p>
					<br/>
                    <table class="user_data">
                    <thead>
                        <tr>
                            <td>Billing Methods</td>
                            <td>Actions</td>
                            <td>Status</td>
                        </tr>
                    </thead>					
					<tbody>
<?php
//Load data from cards
	require_once("include/stripe/stripe.php");					
	Stripe::setApiKey(STRIPE_API_KEY);
	$carrier_stripe_id	= NULL;
	carrier_stripe::payment_info($site, $_SESSION['uid'], $carrier_stripe_id);
	
	if (!EMPTY($carrier_stripe_id)) {
		$customer = Stripe_Customer::retrieve($carrier_stripe_id);
		$cards_data = Stripe_Customer::retrieve($carrier_stripe_id)->sources->all(array("object" => "card"));
		if($cards_data) {
				foreach($cards_data['data'] as $card) {
					echo '<tr>';					
						echo '<td width="500">';
						echo '<img src="images/card_50px.jpg" align="left" height="20px" style="margin: 5px 30px;">';
						echo '<p align="left" style="margin-top: 5px;">'.$card['brand'].' '.$card['object'].' ending in '.$card['last4'].'</p>';						
						echo '</td>';
						echo '<td width="120">';
						echo '<p align="left"> <a href="cards_form.php?task=card_modify&idc='.$card['id'].'" class="js-submit">Modify</a>';
						if ($customer->default_source !=  $card['id']) { echo '/<a href="cards_form.php?task=card_remove&idc='.$card['id'].'" class="js-submit">remove</a>'; }
						echo '</p>';
						echo '</td>';
						echo '<td width="100">';
						if ($customer->default_source ==  $card['id']) { 
							echo '<p align="center">Primary</p>';
						}
						else {
							echo '<p align="center"> - </p>'; 
						}
						echo '</td>';
					echo '</tr>';						
				}		
		}
	
	}
	else {
		echo '<tr><td> No payment information in your account. </td></tr>';
	}
	
	
?>								                        
                    </tbody>
					</table> 
					<br/>					
                </form>
            </div>
            <?endif?>
  

        </div><!--right block end-->




    </div>



<?php endif?>
<?php include(INC_PATH."/templates/footer.php");?>