<?php
//DEFINE("SCRIPT_PATH_ROOT","/temp/");
//DEFINE("INC_PATH",dirname(__FILE__).DIRECTORY_SEPARATOR);
include( dirname(__FILE__)."/include/initializer.php");
$shortHeader = "Y";
$dashboardContentModifier = "Y";
include(INC_PATH."/templates/header.php");
?>
<script src="<?=SCRIPT_PATH_ROOT?>js/jquery-ui-1.11.3.custom/jquery-ui.min.js"></script>

<link rel="stylesheet" type="text/css" href="<?=SCRIPT_PATH_ROOT?>js/jquery-ui-1.11.3.custom/jquery-ui.css">

<script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>
<script>
	var map;
	function initialize() {
	var mapOptions = {
	zoom: 8,
	center: new google.maps.LatLng(-34.397, 140.644)
	};
	map = new google.maps.Map(document.getElementById('map'),
	mapOptions);
	}

	google.maps.event.addDomListener(window, 'load', initialize);

	var contentString = '<div id="content">'+
		'<div id="siteNotice">'+
		'</div>'+
		'<h1 id="firstHeading" class="firstHeading">Uluru</h1>'+
		'<div id="bodyContent">'+
		'<p><b>Uluru</b>, also referred to as <b>Ayers Rock</b>, is a large ' +
		'sandstone rock formation in the southern part of the '+
		'Northern Territory, central Australia. It lies 335&#160;km (208&#160;mi) '+
		'south west of the nearest large town, Alice Springs; 450&#160;km '+
		'(280&#160;mi) by road. Kata Tjuta and Uluru are the two major '+
		'features of the Uluru - Kata Tjuta National Park. Uluru is '+
		'sacred to the Pitjantjatjara and Yankunytjatjara, the '+
		'Aboriginal people of the area. It has many springs, waterholes, '+
		'rock caves and ancient paintings. Uluru is listed as a World '+
		'Heritage Site.</p>'+
		'<p>Attribution: Uluru, <a href="https://en.wikipedia.org/w/index.php?title=Uluru&oldid=297882194">'+
		'https://en.wikipedia.org/w/index.php?title=Uluru</a> '+
		'(last visited June 22, 2009).</p>'+
		'</div>'+
		'</div>';

	var infowindow = new google.maps.InfoWindow({
		content: contentString,
		position: new google.maps.LatLng(-34.397, 140.644)
	});



//dashboard
$(document).ready(function() {
    $('.dashboard-button').on('click', function(){
        if($(this).attr('data-goto') == "view"){
            $('.js-dashboard-table').hide();
            $('.js-dashboard-mapview').show();
            google.maps.event.trigger(map, 'resize');
            infowindow.open(map);
        }
        else{
            $('.js-dashboard-table').show();
            $('.js-dashboard-mapview').hide();
        }
    });

    $("#slider-ETA").slider({
        range: true,
        min: 0,
        max: 72,
        values: [ 0, 72 ],
        slide: function( event, ui ) {
            $( "#ETAval" ).val(ui.values[ 0 ]+"-"+ui.values[ 1 ]);
            $('#ETAvalspan').html(ui.values[0]+"hrs - "+ui.values[1]+"hrs");
        }
    });

    $("#slider-pickuptime").slider({
        range: true,
        min: 0,
        max: 72,
        values: [ 0, 72 ],
        slide: function( event, ui ) {
            $( "#pickuptimeval" ).val(ui.values[ 0 ]+"-"+ui.values[ 1 ]);
            $('#pickuptimevalspan').html(ui.values[0]+"hrs - "+ui.values[1]+"hrs");
        }
    });

    $("#slider-HoS").slider({
        range: true,
        min: 0,
        max: 72,
        values: [ 0, 72 ],
        slide: function( event, ui ) {
            $( "#HoSval" ).val(ui.values[ 0 ]+"-"+ui.values[ 1 ]);
            $('#HoSvalspan').html(ui.values[0]+"hrs - "+ui.values[1]+"hrs");
        }
    });

    $('.js-dash-map-form').submit(function(ev){
    	$form = $(this);
        $form.find('.error').html("");
        $form.find('.preloader').show();
    	$.ajax({
            type: $(this).attr('method'),
            url: $(this).attr('action'),
            data: $(this).serialize(),
            dataType: 'json',
            success: function (data) {
                if(data.result == 'ok'){
                    //location.href = $form.find('input[name=backurl]').val();
                }else if(data.result == 'error'){                    
                    $errormsg = "";
                    $.each(data.error, function(key, val){
                        $errormsg += val+"<br>";
                    });
                    $form.find('.preloader').hide();
                    $form.find('.error').html($errormsg);                    
                }
            },
            error: function(jqXHR, textStatus, errorMessage) {
                $form.find('.preloader').hide();
                $form.find('.error').html(errorMessage);
            }
        });

        ev.preventDefault();
    });

});

</script>
<?php
//for test period - not check session type
//if($_SESSION['type'] == "carrier" || $_SESSION['type'] == "dispatch"):?>
	<div class="js-dashboard-table">
		<a class="button dashboard-button" data-goto="view">Go to map view</a>
		<div class="title-div"></div>
		<table class="dashboard-table">
			<thead>
				<tr>
					<td>Driver</td>
					<td>Status</td>
					<td class="pic">ETA</td>
					<td class="pic">Pickup time</td>
					<td class="pic">Customer</td>
					<td class="pic">HoS Remaining</td>
					<td class="pic">Bill of Lading</td>
					<td class="pic">Pre-sheduled</td>
				</tr>
			</thead>
			<?
		  	if($_SESSION['type'] == 'carrier'){
                $cid = $_SESSION['uid'];
            }
            elseif($_SESSION['type'] == 'dispatch'){
                $res_tmp = mysql_qw($site->link, "
                SELECT carrier_id FROM carrier_users
                WHERE id = ?",
                $_SESSION['uid']);
                $row_tmp = mysqli_fetch_array($res_tmp);
                $cid = $row_tmp['carrier_id'];
            }
			$res_tmp = mysql_qw($site->link, "
				SELECT dm.first, dm.last, dr.eta, dr.pickup_time, dr.HoS, dr.bill_of_lading, dr.pre_schdeduled, dr.status, 'none(test)' as customer FROM driver_master dm
				JOIN driver_route dr 
					ON dr.driver_id = dm.driver_id
				/*JOIN shipper_location -> shipper name via dr.shipper_location_id*/			
				WHERE dm.carrier_id = ?",
				$cid) or die(mysqli_error($site->link));
			$color = "white";
			while($row = mysqli_fetch_array($res_tmp)):
				if($color == "blue") $color = "white";
				elseif($color == "white") $color = "blue";
				?>
				<tr class="<?=$color?>">
					<td><?=$row['first']?> <?=$row['last']?></td>
					<td><?=$row['status']?></td>
					<td><?=$row['eta']?></td>
					<td><?=$row['pickup_time']?></td>
					<td><?=$row['customer']?></td>
					<td><?=$row['HoS']?></td>
					<td><?=$row['bill_of_lading']?></td>
					<td><?=$row['pre_schdeduled']?></td>
				</tr>
			<?endwhile?>
		</table>
		<div class="clr"></div>
	</div>
	<div class="js-dashboard-mapview displaynone">
		<div class="dash-map-left block-left">
			<a class="button dashboard-button" data-goto="list">Go to list view</a>
			<form name="filter" class="map-filter js-dash-map-form" method="POST" action="<?=SCRIPT_PATH_ROOT?>include/ajax/dash_search_drivers.php">
				<input type="hidden" name="carrier_id" value="<?=$cid?>">
				<p class="label bold">Load status</p>
				<p><input type="checkbox" name="status[]" value="Enroute - on schedule"> Enroute - on schedule </p>
				<p><input type="checkbox" name="status[]" value="Enroute - off schedule"> Enroute - off schedule </p>
				<p><input type="checkbox" name="status[]" value="Unscheduled"> Unscheduled </p>
				<div class="line"></div>
				<p class="label bold">Times</p>
				<p>ETA time</p>
				<input type="hidden" name="ETA" id="ETAval" value="0-72">
				<span id="ETAvalspan" class="smallspan">0hrs - 72hrs</span>
				<div id="slider-ETA"></div>
				<p>Pickup time</p>
				<input type="hidden" name="pickupTime" id="pickuptimeval" value="0-72">
				<span id="pickuptimevalspan" class="smallspan">0hrs - 72hrs</span>
				<div id="slider-pickuptime"></div>
				<br>
				<div class="line"></div>
				<p class="label bold">Customer</p>
				<select name="customer" class="customer">
					<option value="all">All</option>
				</select>
				<br>
				<div class="line"></div>
				<p class="label bold">Location</p>
				<select name="loc_around" class="loc_around">
					<option value="any">Any miles</option>
					<option value="50">50 miles</option>
				</select> from 
				<br><br>
				<input name="loc_target" type="text" class="loc_target">
				<br>
				<div class="line"></div>
				<p class="label bold">Hours of Service</p>
				<input type="hidden" name="HoS" id="HoSval" value="0-80">
				<span class="smallspan">Remaining hours in Cycle</span>
				<br>
				<span id="HoSvalspan" class="smallspan">0hrs - 80hrs</span>
				<div id="slider-HoS"></div>
				<br>
				<a class="button js-submit" style="display:inline-block;">Apply filter<p class="preloader"></p></a>
				<p class="error"></p>				
			</form>
		</div>
		<div class="dash-map-right">
			<div id="map"></div>
			<div class="summary">
				<div class="title">Network Summary</div>
				<p>Total trucks: 7</p>
				<p>Total trucks: 7</p>
			</div>
			<div class="clr"></div>
		</div>
	</div>	
<?//endif?>

<?include(INC_PATH."/templates/footer.php");?>