<?php

/*
include(dirname(__FILE__)."/include/initializer.php");

$url = "https://mobile.fmcsa.dot.gov/qc/services/carriers/44110?webKey=".WEB_KEY_API;



$APIresilt = @file_get_contents($url);

if($APIresilt === FALSE){

	echo "error while query to API";

}

else{

	$parsedata = json_decode($APIresilt, true);
	var_dump($parsedata);
	var_dump($parsedata['content']['carrier']['cargoInsuranceOnFile']);
	var_dump($parsedata['content']['carrier']['bipdInsuranceOnFile']);
	
	

}
*/


//$ch = curl_init();
//curl_setopt($ch, CURLOPT_URL, 'http://safer.fmcsa.dot.gov/query.asp');
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_POST, TRUE);
//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
//curl_setopt($ch, CURLOPT_POSTFIELDS, 'searchtype=ANY&query_type=queryCarrierSnapshot&query_param=MC_MX&query_string=1515');
////curl_setopt($ch, CURLOPT_POSTFIELDS, 'searchtype=ANY&query_type=queryCarrierSnapshot&query_param=USDOT&query_string=44110');
//$result = curl_exec($ch);
////2 steps parsing. cause of greedy / ungreedy algorythm
////preg_match('#(Carrier">Legal Name.*<TD.*>.*<\/TD>.*<\/TR>)#sU', $result, $tmp);
////preg_match('#<TD.*>([\w\s]+).*<\/TD>#s', $tmp[1], $tmp2);
////$legalName = $tmp2[1];
////var_dump($legalName);
////serahing address
//preg_match('#(PhysicalAddress">Physical Address.*<TD.*>.*<\/TD>.*<\/TR>)#sU', $result, $tmp);
//preg_match('#colspan=\d{1}>(.+).*<\/TD>#s', $tmp[1], $tmp2);
//$addr = $tmp2[1];
//$addr = str_replace("&nbsp;", "", $addr);
//$addr = str_replace("<br>", "", $addr);
//$addr = urlencode($addr);
//curl_setopt($ch, CURLOPT_URL, 'http://maps.googleapis.com/maps/api/geocode/json?address='.$addr.'&language=EN');
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_POST, TRUE);
//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
//$result = curl_exec($ch);
$result = '{ "results" : [ { "address_components" : [ { "long_name" : "350", "short_name" : "350", "types" : [ "street_number" ] }, { "long_name" : "North Saint Paul Street", "short_name" : "North St. Paul Street", "types" : [ "route" ] }, { "long_name" : "Downtown", "short_name" : "Downtown", "types" : [ "neighborhood", "political" ] }, { "long_name" : "Dallas", "short_name" : "Dallas", "types" : [ "locality", "political" ] }, { "long_name" : "Dallas County", "short_name" : "Dallas County", "types" : [ "administrative_area_level_2", "political" ] }, { "long_name" : "Texas", "short_name" : "TX", "types" : [ "administrative_area_level_1", "political" ] }, { "long_name" : "United States", "short_name" : "US", "types" : [ "country", "political" ] }, { "long_name" : "75201", "short_name" : "75201", "types" : [ "postal_code" ] } ], "formatted_address" : "350 North Saint Paul Street, Dallas, TX 75201, USA", "geometry" : { "location" : { "lat" : 32.784037, "lng" : -96.79679849999999 }, "location_type" : "ROOFTOP", "viewport" : { "northeast" : { "lat" : 32.7853859802915, "lng" : -96.79544951970848 }, "southwest" : { "lat" : 32.7826880197085, "lng" : -96.7981474802915 } } }, "partial_match" : true, "place_id" : "ChIJvWXkkSGZToYRUGuMIAR7uj8", "types" : [ "street_address" ] } ], "status" : "OK" }';
$result = json_decode($result, true);
if($result['status'] == 'OK'){
	foreach($result['results'][0]['address_components'] as $value){
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
}
//var_dump($address_house." ".$address_route, $city, $state, $country, $zip);
var_dump($result['results'][0]['geometry']['location']['lat'], $result['results'][0]['geometry']['location']['lng']);
?>