<?php
include(dirname(__FILE__)."/include/initializer.php");
$shortHeader = "Y";
include(INC_PATH."/templates/header.php");
?>
	<script src="/temp/include/stripe/js/jquery.js"></script>
    <script src="/temp/include/stripe/js/buy-controller.js"></script>
<?php
	if($_SESSION['type'] == "carrier" || $_SESSION['type'] == "dispatch"):?>
	
    <div class="carrier_admin_wrapper">
        <?php if($_SESSION['type'] == "carrier"):?>
            <div class="block-left admin_left">
                <ul class="left-menu">
                    <li><a href="#" class="bold">User management</a></li>
                    <li><a href="#">Users</a></li>
                    <li><a href="#">Sign Up Options</a></li>
                </ul>
                <div class="strangeline"></div>
                <ul class="left-menu">
                    <li><a href="#" class="bold">Billing</a></li>
                    <li><a href="#">Payment details</a></li>
                    <li><a href="#">Past invoices</a></li>
                </ul>
            </div>
        <?php endif?>
		
		<div class="admin_right <?=($_SESSION['type'] == "dispatch")?' admin_right2':''?>">
        <h2>For creation new users, please enter payment information</h2>
     
        <form id="buy-form" method="post" action="/temp/include/stripe/submit-payment.php">
             
            <p class="form-label">First Name:</p>
            <input class="text" id="first-name" name="fname" spellcheck="false"></input>
             
            <p class="form-label">Last Name:</p>
            <input class="text" id="last-name" name="lname" spellcheck="false"></input>
             
            <p class="form-label">Email Address:</p>
            <input class="text" id="email" name="email" spellcheck="false"></input>
             
            <p class="form-label">Credit Card Number:</p>
            <input class="text" id="card-number" name="card_number" autocomplete="off"></input>
			
            <p class="form-label">Expiration Date:</p>
            <select id="expiration-month" name="expiration_month">
            <option value="1">January</option>
            <option value="2">February</option>
            <option value="3">March</option>
            <option value="4">April</option>
            <option value="5">May</option>
            <option value="6">June</option>
            <option value="7">July</option>
            <option value="8">August</option>
            <option value="9">September</option>
            <option value="10">October</option>
            <option value="11">November</option>
            <option value="12">December</option>
            </select>
             
            <select id="expiration-year" name="expiration_year">
                <?php 
                    $yearRange = 20;
                    $thisYear = date('Y');
                    $startYear = ($thisYear + $yearRange);
                 
                    foreach (range($thisYear, $startYear) as $year) 
                    {
                        if ( $year == $thisYear) {
                            print '<option value="'.$year.'" selected="selected">' . $year . '</option>';
                        } else {
                            print '<option value="'.$year.'">' . $year . '</option>';
                        }
                    }
                ?>
            </select>
             
            <p class="form-label">CVC:</p>
            <input class="text" id="card-security-code" name="card_security_code" autocomplete="off"></input>

			
            <p class="form-label">Amount to pay: $ 0 </p>
            <?php 
					$trial_date_end = time() + (30 * 24 * 60 * 60);
					echo '<p class="form-label">Free Trial to: '. date('Y-m-d', $trial_date_end) .'</p>';					
			?>
			
            <input id="buy-submit-button" type="submit" value="Place This Order"></input>
        </form>
		</div>
		
    </div>	

<?php endif?>
	
<?php include(INC_PATH."/templates/footer.php");?>