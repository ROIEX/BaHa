<?php
	include(dirname(__FILE__)."/include/initializer.php");
	$shortHeader = "Y";
	include(INC_PATH."/templates/header.php");

	if($_SESSION['type'] == "carrier"): ?>
	
    <div class="carrier_admin_wrapper">
		<!-- block-left -->
            <?php include(INC_PATH."/templates/left-menu.php");?>
			<br/>
		 <h2>The free trial has expired</h2>		
			<br/>
		<div> 
		Your free trial has expired. To access account and dashboard please <a href="<?php echo SCRIPT_PATH_ROOT.'carrier_manager.php' ?>">enter credit card information.</a>  If you feel you have received this message in error please email support.		
		</div>

		
    </div>	

<?php endif?>

<?php include(INC_PATH."/templates/footer.php");?>		