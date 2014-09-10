<!DOCTYPE html>
<html>
    <head>
        <title>LNU WS</title>
        <meta charset="utf-8" />
        <!-- Foundation -->
    	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
    	<link rel="stylesheet" href="css/foundation.css" />
    	<script src="js/vendor/modernizr.js"></script>
    	
    	<!-- Google Map -->
    	<style>
      		#map_canvas {
        		width: 500px;
        		height: 400px;
      		}
    	</style>
    	<script src="https://maps.googleapis.com/maps/api/js"></script>
    </head>
    <body>
    	<div id="myheader">
    		<h1>Linnaeus University - Netatmo Weather Station</h1>
    	</div>

    	<div class="row" id="main-cont">
  			<div class="medium-6 medium-push-6 columns">
  				<dl class="tabs" data-tab>
  					<dd class="active"><a href="#panel1">Info</a></dd>
  					<dd><a href="#panel2">Indoor</a></dd>
  					<dd><a href="#panel3">Outdoor</a></dd>
				</dl>
  				<div class="tabs-content">
  					<div class="content active" id="panel1">
  							

    	<?php
    		require_once("NAApiClient.php");
    		require_once("Config.php");

			$username = $test_username;
			$pwd = $test_password;
			$config = array();
			$config['client_id'] = $client_id;
			$config['client_secret'] = $client_secret;
			$config['scope'] = 'read_station'; //application will have access to station and theromstat
                                               // ex. 'read_station read_thermostat write_thermostat'
			$client = new NAApiClient($config);
			$client->setVariable("username", $username);
			$client->setVariable("password", $pwd);

			try
			{
				$tokens = $client->getAccessToken();        
			    $refresh_token = $tokens["refresh_token"];
			    $access_token = $tokens["access_token"];
			}
			catch(NAClientException $ex)
			{
			    echo "An error happend while trying to retrieve your tokens\n";
			}

			// First retrieve user device list
			try
			{
			    $deviceList = $client->api("devicelist");        
			    if(isset($deviceList["devices"][0]))
			    {	
			    	// Device data
			        $device_id = $deviceList["devices"][0]["_id"];
			        $station_name = $deviceList["devices"][0]["station_name"];
			        $firmware = $deviceList["devices"][0]["firmware"];
			        $wifi_status = $deviceList["devices"][0]["wifi_status"];
			        $place = $deviceList["devices"][0]["place"];
			        $lon = $place["location"][0];
			        $lat = $place["location"][1];

			        // Module data
			        $module_id = $deviceList["modules"][0]["_id"];
			        $out_firmware = $deviceList["modules"][0]["firmware"];
			        $rf_status = $deviceList["modules"][0]["rf_status"];
			        $battery_vp = $deviceList["modules"][0]["battery_vp"];

			        echo "<h3>Device info<h3>";
			        echo "<p>Devide id: ".$device_id."<p>";
			        echo "<p>Firmware: ".$firmware."<p>";
			        echo "<p>Wifi status: ".$wifi_status."<p>";

			        echo "<h3>Module info<h3>";
			        echo "<p>Module id: ".$module_id."<p>";
			        echo "<p>Firmware: ".$out_firmware."<p>";
			        echo "<p>Radio status: ".$rf_status."<p>";
			        echo "<p>Battery life: ".$battery_vp."<p>";

			        echo "</div><div class='content' id='panel2'>";

			        // Ok display dashboard_data
			        if(isset($deviceList["devices"][0]["dahsboard_data"]))
			            echo "<h4>".$deviceList["devices"][0]["dashboard_data"]."</h4>";
			        // Ok now retrieve current mean day temperature,humidity
			        $params = array("scale" =>"max",
			                        "type"=>"Temperature,Humidity,Co2,Pressure,Noise",
			                        "date_end"=>"last",
			                        "device_id"=>$device_id);
			        $res = $client->api("getmeasure", $params);
			        if(isset($res[0]) && isset($res[0]["beg_time"]))
			        {
			            $time = $res[0]["beg_time"];
			            $t = $res[0]["value"][0][0];  
			            $h = $res[0]["value"][0][1];
			            $c = $res[0]["value"][0][2];
			            $p = $res[0]["value"][0][3];
			            $n = $res[0]["value"][0][4];

			            //echo "<p>Measurements @ ".date('c', $time)."</p>";
			            echo "<h3>Weather Measurements</h3>";
			            echo "<p>Temperature is $t Celsius</p>";
			            echo "<p>Humidity is $h %</p>";
			            echo "<p>Co2 is $c ppm</p>";
			            echo "<p>Pressure is $p mbar</p>";
			            echo "<p>Noise is $n db</p>";
			        }
			        
			        // Closing div tag for panel1
			        echo "</div>";
			        echo "<div class='content' id='panel3'>";


			        // Module data
			        $dashboard_data = $deviceList["modules"][0]["dashboard_data"];
			        $out_temp = $dashboard_data["Temperature"];
			        $out_humid = $dashboard_data["Humidity"];

			        echo "<h3>Weather Measurements</h3>";
			        echo "<p>Temperature: ".$out_temp." Celsius<p>";
			        echo "<p>Humidity: ".$out_humid." %<p>";

			        // Closing div tag for panel2 and tabs-content
			        echo "</div></div>";
			    }
			}
			catch(NAClientException $ex)
			{
			    echo "User does not have any devices\n";
			}
		?>
		<script>
      		function initialize() {
      			//Assign the variable from the previous php code to the javascript ones!
      			var lon = '<?php echo $lon; ?>';
      			var lat = '<?php echo $lat; ?>';
      			var stationName = '<?php echo $station_name; ?>';
        		var mapCanvas = document.getElementById('map_canvas');
        		var myLatlng = new google.maps.LatLng(lat, lon);
        		var mapOptions = {
        				//configure the google map latitude and longtitude
          				center: new google.maps.LatLng(lat, lon),
          				zoom: 12,
          				mapTypeId: google.maps.MapTypeId.ROADMAP
        			}
        		var map = new google.maps.Map(mapCanvas, mapOptions);
        		var marker = new google.maps.Marker({
      								position: myLatlng,
      								map: map,
      								title: stationName
  								});
      		}
      		google.maps.event.addDomListener(window, 'load', initialize);
    	</script>
    		</div>
    		<div class="medium-6 medium-pull-6 columns"><div id="map_canvas"></div></div>
		</div>
		<div id="date-cont"><h3>Date of data's retrieval:<?php echo date('c', $time) ?></h3></div>

		<div id="footer">
			© Copyright 2014 by Linnaeus University. All Rights Reserved.
		</div>

		<!-- Foundation -->
		<script src="js/vendor/jquery.js"></script>
    	<script src="js/foundation.min.js"></script>
    	<script>
      		$(document).foundation();
    	</script>
    </body>
</html>