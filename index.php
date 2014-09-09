<!DOCTYPE html>
<html>
    <head>
        <title>LNU WS</title>
    </head>
    <body>
    	<h1>Linnaeus University - Netatmo Weather Station</h1>
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
			        $device_id = $deviceList["devices"][0]["_id"];
			        // Ok display dashboard_data
			        if(isset($deviceList["devices"][0]["dahsboard_data"]))
			            print_r($deviceList["devices"][0]["dashboard_data"]);
			        // Ok now retrieve current mean day temperature,humidity
			        $params = array("scale" =>"1day",
			                        "type"=>"Temperature,Humidity",
			                        "date_end"=>"last",
			                        "device_id"=>$device_id);
			        $res = $client->api("getmeasure", $params);
			        if(isset($res[0]) && isset($res[0]["beg_time"]))
			        {
			            $time = $res[0]["beg_time"];
			            $t = $res[0]["value"][0][0];  
			            $h = $res[0]["value"][0][1];
			            echo "Temperature is $t Celsius @".date('c', $time)."\n";
			            echo "Humidity is $h % @".date('c', $time)."\n";
			        } 
			    }
			}
			catch(NAClientException $ex)
			{
			    echo "User does not have any devices\n";
			}
		?>
    </body>
</html>