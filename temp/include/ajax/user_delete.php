<?php
include("../initializer.php");
	
	if (($_POST['delete'] == 'delete') && ($_POST['user_id'] > 0 )) {
	//delete user submit 
		carrier_general::delete_user($site, $_POST['user_id'], $_SESSION['uid']);
		header("Location: ".SCRIPT_PATH_ROOT."carrier_admin.php");
		die();	
	}
	
	
$shortHeader = "Y";
include(INC_PATH."/templates/header.php");

	$id_card = $_GET['idc'];
	if(($_SESSION['type'] == "carrier") || (!EMPTY($id_card))) {	
		$user_array = carrier_general::load_user($site, $id_card);
	}	
	else {
		header("Location: /");
		die();	
	}	
	
?>
<?php
	if($_SESSION['type'] == "carrier"):?>
	
    <div class="carrier_admin_wrapper">
		<!-- block-left -->
            <?php include(INC_PATH."/templates/left-menu.php");?>
		
		<div class="admin_right <?=($_SESSION['type'] == "dispatch")?' admin_right2':''?>">
	        <h2>Delete user</h2>
			
				<br/>
		 
	        <form id="delete-form" method="post" action="<?php echo SCRIPT_PATH_ROOT ?>include/ajax/user_delete.php">
			
				<input type="hidden" name="user_id" value="<?php echo $id_card ?>">				

				<br/>
				<p class="form-label">Delete user <?php echo $user_array['first'].' '.$user_array['last'].'  email: '.$user_array['email']; ?>?</p>
				<br/>
				
				 <p> <button name="delete" value="delete">Delete</button> <a href="<?php echo SCRIPT_PATH_ROOT ?>carrier_admin.php">Cancel</a> </p> 		
	        </form>
		</div>
		
    </div>	

<?php endif?>

<?php include(INC_PATH."/templates/footer.php");?>		