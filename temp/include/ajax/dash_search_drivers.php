<?php
include("../initializer.php");
//let's build query
$query = "SELECT dr.route_id FROM driver_route dr
	JOIN driver_master dm 
		ON dm.driver_id = dr.driver_id
	WHERE dm.carrier_id = ?
";
//status
if($_POST['status']){
	$query .= "
	AND dr.status IN (
	";
	$status_count = count($_POST['status']);
	$key = 0;
	foreach($_POST['status'] as $status){
		if($key > 0) $query .= ", ";
		$query .= "?";
		$key ++;
	}
	$query .= ")";	
}
//ETA
$arEta = explode("-", $_POST['ETA']);
$query .="
	AND dr.eta BETWEEN ? AND ?
	";
//pickupTime
$arPickupTime = explode("-", $_POST['pickupTime']);
$query .="
	AND dr.pickup_time BETWEEN ? AND ?
	";
//customer...throught join
///
//location
//look like here firtly need query to google api
//$_POST['loc_around'] - is around of target
//$_POST['loc_target'] - is name of object
//need to find object and make square of lat lng coords
//then - sear drivers in that square

//HoS like a ETA in db named HoS 

echo $query;
//думаю тут лучше переписать и фильтр по статусу накладывать на результат выдачи. это бы упростило. аналогично для широты и долготы.
if($status_count && $status_count == 1){
	$res_tmp = mysql_qw($site->link, $query, $_POST['carrier_id'], $_POST['status'][0], $arEta[0], $arEta[1], $arPickupTime[0], $arPickupTime[1]) or die(mysqli_error($site->link));
}
elseif($status_count == 2){
	$res_tmp = mysql_qw($site->link, $query, $_POST['carrier_id'], $_POST['status'][0], $_POST['status'][1], $arEta[0], $arEta[1], $arPickupTime[0], $arPickupTime[1]) or die(mysqli_error($site->link));
}
elseif($status_count == 3){
	$res_tmp = mysql_qw($site->link, $query, $_POST['carrier_id'], $_POST['status'][0], $_POST['status'][1], $_POST['status'][2], $arEta[0], $arEta[1], $arPickupTime[0], $arPickupTime[1]) or die(mysqli_error($site->link));
}
elseif(!$status_count){
	$res_tmp = mysql_qw($site->link, $query, $_POST['carrier_id'], $arEta[0], $arEta[1], $arPickupTime[0], $arPickupTime[1]) or die(mysqli_error($site->link));
}
$row = mysqli_fetch_array($res_tmp);
echo $row['route_id'];
?>