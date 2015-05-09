<?php
class Site{

	public $link;

	function __construct(){
		$this->link = mysqli_connect(MYSQL_SERVER,MYSQL_USER,MYSQL_PASSWORD,MYSQL_DATABASE);

		if (!$this->link) {
		    die('Unable to connect or select database!');
		}
	}

	public function signUp($formData){
		$error_text = array();
		foreach ($formData as $key => $value){
			if(in_array($key, array('first', 'last', 'company', 'phone', 'address1', 'city', 'state', 'zip', 'email', 'username', 'password', 'password2')) && $value == ""){
				$error_text[] = 'Please, fill required fields.';
				break;
			}
		}
		if($formData['type'] == 'carrier'){
			if(($formData['mc'] == "" || $formData['dot'] == "" || $formData['carrierdrivers'] == "") && count($error_text) == 0)
			$error_text[] = 'Please, fill required fields.';
		}
		elseif($formData['type'] == 'shipper'){
            if($formData['shippinglocations'] == "" && count($error_text) == 0){
                $error_text[] = 'Please, fill required fields.';
            }
        }
        elseif($formData['type'] == 'driver'){
            if(($formData['mc'] == "" || $formData['dot'] == "") && count($error_text) == 0)
                $error_text[] = 'Please, fill required fields.';
        }
		//checking mail
		if(!preg_match('#[a-zA-Z0-9_\-.+]+@[a-zA-Z0-9\-]+.[a-zA-Z]+#', $formData['email'])){
			$error_text[] = 'Email is uncorrect.';
		}
		//password
		if(strcmp($formData['password'], $formData['password2']) != 0){
			$error_text[] = 'Passwords do not match';
		}
		elseif(strlen($formData['password']) < 6){
			$error_text[] = "Invalid password (minimum 6 characters)";
		}
		elseif(!preg_match("#[0-9]+#", $formData['password']) ) {
			$error_text[] = "Password must contain at least one number";
		}
		elseif(!preg_match("#[a-z]+#", $formData['password']) ) {
			$error_text[] = "Passwords must contain at least one lowercase letter";
		}
		elseif(!preg_match("#[A-Z]+#", $formData['password']) ) {
			$error_text[] = "Passwords must contain at least one uppercase letter";
		}
		if(count($error_text) > 0){
			$return = array(
				'result' => 'error',
				'error' => $error_text
			);
			return json_encode($return);
		}
		//search for duplicate for carrier and several checks for carrier
		if($formData['type'] == 'carrier'){
			$data = mysql_qw($this->link, "
				SELECT carrier_id FROM carrier_master
				WHERE email = ?",
				$formData['email']
			);// or die(mysql_error());
			$iid = mysqli_fetch_assoc($data);
			if($iid['carrier_id']){
				//$error_text[] = 'Your account is being reviewed. We will contact you shortly.';
				$return = array(
					'result' => 'mail_error'
				);
				return json_encode($return);
			}
			//search for duplicating MC or DOT
			$data = mysql_qw($this->link, "
				SELECT carrier_id FROM carrier_master
				WHERE (mc_num = ? OR dot_num = ?) AND type='carrier'",
				$formData['mc'], $formData['dot']
			);// or die(mysql_error());
			$iid = mysqli_fetch_assoc($data);
			if($iid['carrier_id']){
				$return = array(
					'result' => 'api_error'
				);
				return json_encode($return);
			}

			//validating #DOT and #MC numbers throught http://safer.fmcsa.dot.gov/CompanySnapshot.aspx
			//name1
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://safer.fmcsa.dot.gov/query.asp');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, 'searchtype=ANY&query_type=queryCarrierSnapshot&query_param=MC_MX&query_string='.$formData['mc']);
			$result = curl_exec($ch);
			$saveInfoForAdressParsing = $result;
			//2 steps parsing. cause of greedy / ungreedy algorythm
			preg_match('#(Carrier">Legal Name.*<TD.*>.*<\/TD>.*<\/TR>)#sU', $result, $tmp);
			preg_match('#<TD.*>([\w\s]+).*<\/TD>#s', $tmp[1], $tmp2);
			$legalName1 = $tmp2[1];
			curl_close($ch);
			//name2
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://safer.fmcsa.dot.gov/query.asp');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, 'searchtype=ANY&query_type=queryCarrierSnapshot&query_param=USDOT&query_string='.$formData['dot']);
			$result = curl_exec($ch);
			//2 steps parsing. cause of greedy / ungreedy algorythm
			preg_match('#(Carrier">Legal Name.*<TD.*>.*<\/TD>.*<\/TR>)#sU', $result, $tmp);
			preg_match('#<TD.*>([\w\s]+).*<\/TD>#s', $tmp[1], $tmp2);
			$legalName2 = $tmp2[1];
			curl_close($ch);
			//MC is last check, so
			if($legalName1 != $legalName2 || is_null($legalName1) || is_null($legalName2)){
				$return = array(
					'result' => 'api_error'
				);
				return json_encode($return);
			}
		}
		//search duplicate for shipper
		elseif($formData['type'] == 'shipper'){
			$data = mysql_qw($this->link, "
				SELECT sm.shipper_id FROM shipper_master sm
					JOIN shipper_contacts sc ON sc.shipper_id = sm.shipper_id
				WHERE sc.email = ?",
				$formData['email']
			);// or die(mysql_error());
			$iid = mysqli_fetch_assoc($data);
			if($iid['shipper_id']){
				//$error_text[] = 'Your account is being reviewed. We will contact you shortly.';
				$return = array(
					'result' => 'mail_error'
				);
				return json_encode($return);
			}
		}

		
		//if no error write in DB and send message
		$key = md5($formData['email']."_salt123");
		//write to DB
		//if it's a carrier or driver:
		if($formData['type'] == 'carrier' || $formData['type'] == 'driver'){
			mysql_qw($this->link, "
				INSERT INTO carrier_master
				(type, email, username, password, is_active, activation_code, first, last, carrier_name, phone, position, mc_num, dot_num, carrierdrivers, shippinglocations, registration_date)
				VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())",
				$formData['type'], $formData['email'], $formData['username'], md5($formData['password']."_salt123"), "N", $key, $formData['first'], $formData['last'], $formData['company'], $formData['phone'], $formData['position'], (($formData['type']=='carrier' || $formData['type']=='driver')?$formData['mc']:'NULL'), (($formData['type']=='carrier' || $formData['type']=='driver')?$formData['dot']:'NULL'), (($formData['type']=='carrier')?$formData['carrierdrivers']:'NULL'), (($formData['type']=='shipper')?$formData['shippinglocations']:'NULL')/*, date('m/d/Y')*/
			) or die(mysqli_error($this->link));
			//get id of new user
			$iid = $this->link->insert_id;
			//write down adress data from form
			//city_id, state_id, country_id, zip_code i make varchar, cause of no where fow now get "id". later it weill must be an INT type
			mysql_qw($this->link, "
				INSERT INTO carrier_locations
				(carrier_id, street_addr, street_addr2, city_id, state_id, country_id, zip_code, source)
				VALUES(?,?,?,?,?,NULL,?,'form')",
				$iid, $formData['address1'], $formData['address2'], $formData['city'], $formData['state'], $formData['zip'] 
			) or die(mysqli_error($this->link));

			//parse adress from parser answer $saveInfoForAdressParsing
			preg_match('#(PhysicalAddress">Physical Address.*<TD.*>.*<\/TD>.*<\/TR>)#sU', $saveInfoForAdressParsing, $tmp);
			preg_match('#colspan=\d{1}>(.+).*<\/TD>#s', $tmp[1], $tmp2);
			$addr = $tmp2[1];
			$addr = str_replace("&nbsp;", "", $addr);
			$addr = str_replace("<br>", "", $addr);
			$addr = urlencode($addr);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://maps.googleapis.com/maps/api/geocode/json?address='.$addr.'&language=EN');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			//curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			$addrsrc = curl_exec($ch);
			//$saveInfoForAdressParsing = '{ "results" : [ { "address_components" : [ { "long_name" : "350", "short_name" : "350", "types" : [ "street_number" ] }, { "long_name" : "North Saint Paul Street", "short_name" : "North St. Paul Street", "types" : [ "route" ] }, { "long_name" : "Downtown", "short_name" : "Downtown", "types" : [ "neighborhood", "political" ] }, { "long_name" : "Dallas", "short_name" : "Dallas", "types" : [ "locality", "political" ] }, { "long_name" : "Dallas County", "short_name" : "Dallas County", "types" : [ "administrative_area_level_2", "political" ] }, { "long_name" : "Texas", "short_name" : "TX", "types" : [ "administrative_area_level_1", "political" ] }, { "long_name" : "United States", "short_name" : "US", "types" : [ "country", "political" ] }, { "long_name" : "75201", "short_name" : "75201", "types" : [ "postal_code" ] } ], "formatted_address" : "350 North Saint Paul Street, Dallas, TX 75201, USA", "geometry" : { "location" : { "lat" : 32.784037, "lng" : -96.79679849999999 }, "location_type" : "ROOFTOP", "viewport" : { "northeast" : { "lat" : 32.7853859802915, "lng" : -96.79544951970848 }, "southwest" : { "lat" : 32.7826880197085, "lng" : -96.7981474802915 } } }, "partial_match" : true, "place_id" : "ChIJvWXkkSGZToYRUGuMIAR7uj8", "types" : [ "street_address" ] } ], "status" : "OK" }';
			$addrsrc = json_decode($addrsrc, true);
			curl_close($ch);
			if($addrsrc['status'] == 'OK'){
				foreach($addrsrc['results'][0]['address_components'] as $value){
					if(in_array("street_number", $value['types'])){
						$address_house = $value['long_name'];
					}
					elseif(in_array("route", $value['types'])){
						$address_route = $value['long_name'];
					}
					elseif(in_array("locality", $value['types'])){
						$city = $value['long_name'];
					}
					elseif(in_array("administrative_area_level_1", $value['types'])){
						$state = $value['long_name'];
					}
					elseif(in_array("country", $value['types'])){
						$country = $value['long_name'];
					}
					elseif(in_array("postal_code", $value['types'])){
						$zip = $value['long_name'];
					}		
				}
				//var_dump($address_house." ".$address_route, $city, $state, $country, $zip);
				mysql_qw($this->link, "
					INSERT INTO carrier_locations
					(carrier_id, street_addr, street_addr2, city_id, state_id, country_id, zip_code, source, lat, lng)
					VALUES(?,?,?,?,?,?,?,'parser',?,?)",
					$iid, $address_house." ".$address_route, "", $city, $state, $country, $zip, $addrsrc['results'][0]['geometry']['location']['lat'], $addrsrc['results'][0]['geometry']['location']['lng']
				) or die(mysqli_error($this->link));	
			}
			
			//API for carrier
			if($formData['type'] == 'carrier' || $formData['type'] == 'driver'){				
				//carrier - make API
				//$formData['dot'] = "44110"; - it's a test
				$url = "https://mobile.fmcsa.dot.gov/qc/services/carriers/".$formData['dot']."?webKey=".WEB_KEY_API;
				$APIresilt = @file_get_contents($url);
				if($APIresilt === FALSE){					
					// sleep for 10 seconds
					sleep(10);
					$url = "https://mobile.fmcsa.dot.gov/qc/services/carriers/".$formData['dot']."?webKey=".WEB_KEY_API;
					$APIresilt = file_get_contents($url);					
				}
				//write info in db
				$res_tmp = mysql_qw($this->link, "
					SELECT carrier_id FROM carrier_FMCSA WHERE carrier_id = ?
				", $iid);
				$row_tmp = mysqli_fetch_array($res_tmp);
				if(!$row_tmp['carrier_id']){
					//prepare data from FMCSA.
					$parsedata = json_decode($APIresilt, true);
					$prep['safety_rating'] = $parsedata['content']['carrier']['safetyRating'];
					$prep['cargo_insurance'] = $parsedata['content']['carrier']['cargoInsuranceOnFile'];
					$prep['bipd'] = $parsedata['content']['carrier']['bipdInsuranceOnFile'];
	
					mysql_qw($this->link, "
						INSERT INTO carrier_FMCSA
						(safety_rating, cargo_insurance, bipd, carrier_id)
						VALUES(?,?,?,?)",
						$prep['safety_rating'], $prep['cargo_insurance'], $prep['bipd'], $iid
					);
				}
			}
		}
		elseif($formData['type'] == 'shipper'){
			mysql_qw($this->link, "
				INSERT INTO shipper_master
				(shipper_name, industry_id, registration_date)
				VALUES(?,?,NOW())",
				$formData['company'], ""
			) or die(mysqli_error($this->link));
			//get id of new user
			$iid = $this->link->insert_id;			
			//write down contact and adress data from form
			mysql_qw($this->link, "
				INSERT INTO shipper_locations
				(shipper_id, street_addr, street_addr2, city_id, state_id, country_id, zip_code, latitude, longitude)
				VALUES(?,?,?,?,?,NULL,?,0,0)",
				$iid, $formData['address1'], $formData['address2'], $formData['city'], $formData['state'], $formData['zip'] 
			) or die(mysqli_error($this->link));
			mysql_qw($this->link, "
				INSERT INTO shipper_contacts
				(shipper_id, first, last, title, email, phone, username, password, email_confirm, activation_code, shippinglocations)
				VALUES(?,?,?,'',?,?,?,?,'N',?,?)",
				$iid, $formData['first'], $formData['last'], $formData['email'], $formData['phone'], $formData['username'], md5($formData['password']."_salt123"), $key, $formData['shippinglocations']
			) or die(mysqli_error($this->link));
		}

		//send mail
		if($formData['type'] != 'carrier'){
			//standart mail
			$to      = $formData['email'];
			$subject = 'registration confirm';
			$message = 'Please validate your account by clicking: '.SITE.SCRIPT_PATH_ROOT.'registrationconfirm.php?key='.$key;
			$headers = 'From: '.FROM_MAIL;
		}
		else{
		//beauty mail for carrier
			$message = '
				<html>
				<body style="color: black;
				    font-family: Verdana,Helvetica,sans-serif;
				    font-size: 12px;
				    font-style: normal;
				    line-height: 1.3;">
				<div style="text-align:center;margin-bottom:20px">
				    <img style="display: block; margin: 10px auto 20px;" src="'.SITE.SCRIPT_PATH_ROOT.'images/logo.png" width="140px">
				    <p style="font-size:18px;color:#120073">Welcome to BridgeHaul!</p>
				    <div style="height:1px;background:#8E9DB0;width:80%;margin: 10px auto;"></div>
				    <p>Please click the button below to confirm your email address and activate your<br>
				    account.If the button is not visible please paste the url into your web browser.</p>
				    <a style="padding: 10px; background-color: #376092; text-decoration: none; font-size: 16px; font-weight: bold; color: #fff; display:inline-block;" href="'.SITE.SCRIPT_PATH_ROOT.'registrationconfirm.php?key='.$key.'">Confirm email</a>
				</div>
				<p>'.SITE.SCRIPT_PATH_ROOT.'registrationconfirm.php?key='.$key.'</p>
				<div style="text-align:center;">
					<span style="font-size:14px;">Upon activation you will have the ability to:</span>
				</div>
				<ul style="list-style-type: none;margin: 0 0 20px;">
				    <li> - Add drivers, dispatchers and administrative users to your account</li>
				    <li> - Track drivers location real-time, manage and monitor Hours of Servise logs and Driver Vehicle Inspection Reports once drivers download and launch the mobile application</li>
				</ul>
				<div style="width: 300px;margin: 0 auto;position: relative">
				    <a href="#" style="float:left;">Contact us</a>
				    <a href="#" style="float:right;">Privacy Policy</a>
				</div>
				</body>
				</html>';
			$to      = $formData['email'];
			$subject = 'registration confirm';
			$headers = "MIME-Version: 1.0\r\n";
		    	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			$headers .= 'From: '.FROM_MAIL;				
		}			

		mail($to, $subject, $message, $headers);
		$return = array(
			'result' => 'ok',
		);
		
		return json_encode($return);		
	}
	
	static function redirect($url, $statusCode = 303)
	{
		header('Location: ' . $url, true, $statusCode);
		die();
	}

	public function registrate($key){
		//search inactive record with same hash
		$res = mysql_qw($this->link, "
			SELECT carrier_id, type, email FROM carrier_master WHERE is_active = 'N' AND activation_code = ?
		", $key);
		$row = mysqli_fetch_array($res);
		if($row['carrier_id']){
			mysql_qw($this->link, "
			UPDATE carrier_master SET is_active = 'Y' WHERE carrier_id = ?",
			$row['carrier_id']
			);// or die(mysql_error());
			$_SESSION['uid'] = $row['carrier_id'];
			$_SESSION['email'] = $row['email'];
			$_SESSION['type'] = trim($row['type']);
			//send mail to admin
			$to      = ADMIN_MAIL;
			$subject = 'New '.trim($row['type']).' is registred';
			$message = 'New '.trim($row['type']).' is registred. Id = '.$row['carrier_id'];
			$headers = 'From: '.FROM_MAIL.'\r\n';

			mail($to, $subject, $message, $headers);
			//all is ok
			Site::redirect(SCRIPT_PATH_ROOT."carrier_admin.php");
		}
		else{
			//we have not found carrier or free driver, but it's may be a shipper
			$res = mysql_qw($this->link, "
				SELECT shipper_id, 'shipper' as type, email FROM shipper_contacts WHERE email_confirm = 'N' AND activation_code = ?
			", $key);
			$row = mysqli_fetch_array($res);
			if($row['shipper_id']){
				mysql_qw($this->link, "
					UPDATE shipper_contacts SET email_confirm = 'Y' WHERE shipper_id = ?",
					$row['shipper_id']
				);// or die(mysql_error());
				$_SESSION['uid'] = $row['shipper_id'];
				$_SESSION['email'] = $row['email'];
				$_SESSION['type'] = trim($row['type']);
				Site::redirect(SCRIPT_PATH_ROOT);
			}
			else{
				//user not found
				echo "User not found";
			}
		}
	}

	public function auth($login, $pass){
		//auth variants: carrier or free driver, driver or dispatcher, shipper.
		
		//if no try carrier or free driver
		$res = mysql_qw($this->link, "
			SELECT carrier_id as id, email, type FROM carrier_master WHERE is_active = 'Y' AND (username = ? OR email = ?) AND password = ?
		", $login, $login, md5($pass."_salt123")) or die(mysqli_error($this->link));
		$row = mysqli_fetch_array($res);

		//if no, test for dispatch or driver
		if(!$row['id']){
			$res = mysql_qw($this->link, "
				SELECT id, email, usertype as type FROM carrier_users WHERE (username = ? OR email = ?) AND password = ?
			", $login, $login, md5($pass."_salt123")) or die(mysqli_error($this->link));
			$row = mysqli_fetch_array($res);
			if($row['id']){
				//adding session time
				mysql_qw($this->link, "
					UPDATE carrier_users SET last_session = ? WHERE id = ?",
					date('m/d/Y'), $row['id']
				);
				$is_carrier_user = true;
			}
		}		

		//if no, test for shipper
		if(!$row['id']){
			$res = mysql_qw($this->link, "
				SELECT shipper_id as id, email, 'shipper' as type FROM shipper_contacts WHERE (username = ? OR email = ?) AND password = ?
			", $login, $login, md5($pass."_salt123")) or die(mysqli_error($this->link));
			$row = mysqli_fetch_array($res);
		}


		if($row['id']){
			if($row['type'] != "driver" || !$is_carrier_user){
				$_SESSION['uid'] = $row['id'];
				$_SESSION['email'] = $row['email'];
				$_SESSION['type'] = trim($row['type']);
			}			
			//admin - redirect to authorise
			//carrier - redirect to carrier_admin.php
			if(trim($row['type']) == 'admin') Site::redirect(SCRIPT_PATH_ROOT."authorise.php");
			elseif(trim($row['type']) == 'carrier' || trim($row['type']) == 'dispatch') Site::redirect(SCRIPT_PATH_ROOT."carrier_admin.php");
			//if($_POST['backurl']) Site::redirect($_POST['backurl']);
			else Site::redirect(SCRIPT_PATH_ROOT);
		}
		else{
			$_SESSION['ERR_REPORTS']['auth'] = 'Wrong username or password';
			if($_POST['backurl']) Site::redirect(SCRIPT_PATH_ROOT."auth.php?backurl=".$_POST['backurl']);
			else Site::redirect(SCRIPT_PATH_ROOT."auth.php");
		}
		return fasle;
	}

	public function setuppasswd($pass1, $pass2, $key){
		unset($_SESSION['ERR_REPORTS']['passwdset']);
		//password checking
		if($pass1 != $pass2){
			$_SESSION['ERR_REPORTS']['passwdset'] = "Passwords do not match";
		}
		elseif(strlen($pass1) < 6){
			$_SESSION['ERR_REPORTS']['passwdset'] = "Invalid password (minimum 6 characters)";
		}
		elseif(!preg_match("#[0-9]+#", $pass1) ) {
			$_SESSION['ERR_REPORTS']['passwdset'] = "Password must contain at least one number";
		}
		elseif(!preg_match("#[a-z]+#", $pass1) ) {
			$_SESSION['ERR_REPORTS']['passwdset'] = "Passwords must contain at least one lowercase letter";
		}
		elseif(!preg_match("#[A-Z]+#", $pass1) ) {
			$_SESSION['ERR_REPORTS']['passwdset'] = "Passwords must contain at least one uppercase letter";
		}
		if(!$_SESSION['ERR_REPORTS']['passwdset']){
			//it's ok. set status ans session
			$table = "carrier_users";
			$res = mysql_qw($this->link, "
				SELECT id, email, usertype FROM carrier_users WHERE activation_code = ?
			", $key) or die(mysqli_error($this->link));
			$row = mysqli_fetch_array($res);
			//so, any user can reset pass, adding other table
			if(!$row['id']){
				$table = "carrier_master";
				$res = mysql_qw($this->link, "
					SELECT carrier_id as id, email, type as usertype FROM carrier_master WHERE activation_code = ?
				", $key) or die(mysqli_error($this->link));
				$row = mysqli_fetch_array($res);
			}			

			if($row['id']){
				if($table == "carrier_users"){
					mysql_qw($this->link, "
						UPDATE carrier_users SET status = 'active', last_session = ?, password = ? WHERE id = ?",
						date('m/d/Y'), md5($pass1."_salt123"), $row['id']
					);// or die(mysql_error());
					
					// //Subscribe plan for driver with Stripe
					// carrier_stripe::plan_driver($this, $row['id']);
					if($row['usertype'] == 'dispatch'){
						$_SESSION['uid'] = $row['id'];
						$_SESSION['email'] = $row['email'];
						$_SESSION['type'] = trim($row['usertype']);
					}					
				}elseif($table == "carrier_master"){
					mysql_qw($this->link, "
						UPDATE carrier_master SET is_active = 'Y', password = ? WHERE carrier_id = ?",
						md5($pass1."_salt123"), $row['id']
					);// or die(mysql_error());
					$_SESSION['uid'] = $row['id'];
					$_SESSION['email'] = $row['email'];
					$_SESSION['type'] = trim($row['usertype']);
				}								
			}
			else{
				$_SESSION['ERR_REPORTS']['passwdset'] = 'User not found';
			}
		}

		if($_SESSION['ERR_REPORTS']['passwdset']){
			return json_encode(array('result' => 'error', 'error' => array($_SESSION['ERR_REPORTS']['passwdset'])));
		}
		else{
			return json_encode(array('result' => 'ok'));	
		}
	}

	/*error reporting
	* $type action type(auth, registrare и т.д.)
	*/
	public function showErrorReport($type){
		if($_SESSION['ERR_REPORTS'][$type] && $_SESSION['ERR_REPORTS'][$type]!=''){
			$str = $_SESSION['ERR_REPORTS'][$type];
			unset($_SESSION['ERR_REPORTS'][$type]);
			return($str);			
		}
		else return false;
	}

	public function logout(){
		unset($_SESSION['uid'], $_SESSION['email'], $_SESSION['type']);
		Site::redirect(SCRIPT_PATH_ROOT);
	}

}

## Easy placeholders.

// result-set mysql_qw($connection_id, $query, $arg1, $arg2, ...)
//  - or -
// result-set mysql_qw($query, $arg1, $arg2, ...)
// function make query to MySQL throught connection
// $connection_id (if null, then last connected).
// $query may contain symbols ?,
// that will work like placeholders for
// arguments $arg1, $arg2 etc. (by order), security and
// escaped.
function mysql_qw() {
  // Get arguments of function.
  $args = func_get_args();
  // If first argument is resource then it's ID of connection.
  $conn = null;
  if (is_object($args[0])) $conn = array_shift($args);
  // build query.
  $query = call_user_func_array("mysql_make_qw", $args);

        /*var_dump(is_object($args[0]));
        echo "<pre>";
        var_dump($query);
        exit;*/
        //var_dump($query);
  // Call SQL-function.
  return $conn!==null? mysqli_query($conn, $query) : mysqli_query($this->link, $query);
}

// string mysql_make_qw($query, $arg1, $arg2, ...)
// Aunction for make SQL-query on template $query,
// with placeholders.
function mysql_make_qw() {
  $args = func_get_args();
  // get in $tmp LINK on query template.
  $tmpl =& $args[0];
  $tmpl = str_replace("%", "%%", $tmpl);
  $tmpl = str_replace("?", "%s", $tmpl);
  // after that $args[0] will change too.
  // now shield arguments, except first.
  foreach ($args as $i=>$v) {
    if (!$i) continue;        // template
    if (is_int($v)) continue; // int not need to shield
    $args[$i] = "'".addslashes($v)."'";
  }
  // for debug purposes adding 20 last argument with not allowed symbols.
  // if count of "?" will be more that arguments, we will get SQL error
  for ($i=$c=count($args)-1; $i<$c+20; $i++)
    $args[$i+1] = "UNKNOWN_PLACEHOLDER_$i";
  // Create SQL-query.
  return call_user_func_array("sprintf", $args);
}

function beautyfizerAPIdata($data){
	$return = str_replace("[", "", $data);
	$return = str_replace("]", "", $return);
	$return = str_replace("{", "", $return);
	$return = str_replace("}", "", $return);
	$return = str_replace('\"', "", $return);
	$return = str_replace(',', "<br/>", $return);
	return $return;
}

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

?>