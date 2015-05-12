<?php
include("../initializer.php");
//let's build query
$query = "SELECT dr.route_id, dr.status, dm.first, dm.last, dm.longitude, dm.latitude FROM driver_route dr
	JOIN carrier_users dm 
		ON dm.id = dr.driver_id
	WHERE dm.carrier_id = ?
	AND dr.shipper_location_id IN (SELECT sl.location_id FROM shipper_locations sl WHERE ".(($_POST['customer'] == "all")?"'1'":"sl.shipper_id")." = ?)
";//dirty hack

//ETA
//$arEta = explode("-", $_POST['ETA']);
//$query .="
//	AND dr.eta BETWEEN ? AND ?
//	";

////pickupTime
$arPickupTime = explode("-", $_POST['pickupTime']);
$arPickupTime[0] = date("Y-m-d", (time() + $arPickupTime[0]*3600));
$arPickupTime[1] = date("Y-m-d", (time() + $arPickupTime[1]*3600));
$query .="
	AND dr.pickup_time BETWEEN ? AND ?
	";

//customer...
if($_POST['customer'] == "all"){
	$_POST['customer'] = "1";
}

//location
//google.maps.geometry.spherical.computeDistanceBetween(new google.maps.LatLng(-34.397, 140.644), new google.maps.LatLng(-34.397, 140.644))
//location will work in javascript via computeDistanceBetween, so we not need to find smth here

//HoS
$arHos = explode("-", $_POST['HoS']);
$query .="
	AND dr.HoS BETWEEN ? AND ?
	";

$res_tmp = mysql_qw($site->link, $query, $_POST['carrier_id'], $_POST['customer'], $arPickupTime[0], $arPickupTime[1], $arHos[0], $arHos[1]) or die(mysqli_error($site->link));
while ($row = mysqli_fetch_array($res_tmp)){
	//status filter
	if($_POST['status']){
		if(!in_array($row['status'], $_POST['status'])) continue;
	}
	$result[] = $row;
}
if($result){
	echo json_encode(array('result' => $result));
}
else
	echo json_encode(array('empty' => "Y"));
?>