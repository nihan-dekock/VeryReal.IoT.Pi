<?php
header("Access-Control-Allow-Origin: *");

$configs = include('config.php');
$servername = $configs->dbhost;
$username = $configs->dbuser;
$password = $configs->dbpwd;
$dbname = $configs->dbname;

function showStatus()
{
	//Global variables
	global $servername, $username, $password, $dbname;

	//Start date
	$now = date('H:i:s');

	//Last status
	$lastStatus = 0;

	//Read GPIO pins
	exec("gpio -g read 24", $status, $error);
	exec("gpio -g read 25", $siren, $error);
	exec("gpio -g read 18", $lights1, $error);
	exec("gpio -g read 15", $lights2, $error);
	exec("gpio -g read 14", $lights3, $error);
	exec("gpio -g read 22", $lights4, $error);
	//echo ("err=" + $error);
	//echo ("status=" + $status[0]); //or var_dump($status);

	$check=$status[0];

	//Set status text and class names
	$zone1Status = "NOT MONITORING";
	$zone1Class="NotMonitoring";

	$zone2Status = "NOT MONITORING";
	$zone2Class="NotMonitoring";

	$zone3Status = "NOT MONITORING";
	$zone3Class="NotMonitoring";

	$zone4Status = "NOT MONITORING";
	$zone4Class="NotMonitoring";

	$status = "DISARMED";
	$class = "AlarmStatus NotMonitoring";
	$alarmStatusLastDate = "NOT MONITORING";
	$alarmScreamPulse = "pulse-blue";
	$alarmScreamStatus="NO SIREN";
	$alarmScreamText="ALL OK";
	$statusClass="AlarmStatusText NotMonitoring";
	$alarmScreamStatusClass="AlarmStatusText NotMonitoring";

if($check != $lastStatus)
{
	sleep(0.3);
	exec("gpio -g read 24", $status2, $error);
	$check = $status2[0];
}

if($check != $lastStatus)
{
	$lastStatus = $check;
}
	if ($check == "0")
	{
		$status = "ARMED";
		$alarmStatusLastDate = $now;
		$alarmScreamStatus="NO SIREN";
		$class = "AlarmStatus Armed";
		$alarmScreamPulse = "pulse-green";
		$statusClass="AlarmStatusText Armed";
		$alarmScreamStatusClass="AlarmStatusText Armed";
		$zone1Class="DisArmed";
		$zone2Class="DisArmed";
		$zone3Class="DisArmed";
		$zone4Class="DisArmed";
		$zone1Status = "MONITORING";
		$zone2Status = "MONITORING";
		$zone3Status = "MONITORING";
		$zone4Status = "MONITORING";
	}

	if ($siren[0] == "0")
	{
		//Alarm was triggered, set text and CSS
		$status = "ARMED";
		$alarmScreamStatus="SIREN ON!";
		$alarmScreamText="ALARM TRIGGERED!";
		$class = "AlarmStatus Armed";
		$alarmScreamPulse = "pulse-red";
		$statusClass="AlarmStatusText Armed";
		$alarmScreamStatusClass="AlarmStatusText Armed";
	}

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	//Query zone states
	$sql = "SELECT * FROM ZoneState";
	$result = $conn->query($sql);
	//echo ("a" + $result->num_rows);
	if ($result->num_rows > 0) {
		// Output data of each row
		$cnt =0;
    		while($row = $result->fetch_assoc()) {
			//echo($row["ID"]);

			//Set text and CSS if zone was triggered
			if($row["ID"] == 1 && $row["State"] == 1)
			{
				//echo($row["ID"]);
				$zone1Status="TRIGGERED AT " . date( 'H:i', strtotime($row["EventDateTime"]) );
				$zone1Class="Armed";
			}
			if($row["ID"] == 2 && $row["State"] == 1)
			{
				//echo($row["ID"]);
				$zone2Status="TRIGGERED AT " . date( 'H:i', strtotime($row["EventDateTime"]) );
				$zone2Class="Armed";
			}
			if($row["ID"] == 3 && $row["State"] == 1)
			{
				//echo($row["ID"]);
				$zone3Status="TRIGGERED AT " . date( 'H:i', strtotime($row["EventDateTime"]) );
				$zone3Class="Armed";
			}
			if($row["ID"] == 4 && $row["State"] == 1)
			{
				//echo($row["ID"]);
				$zone4Status="TRIGGERED AT " . date( 'H:i', strtotime($row["EventDateTime"]) );
				$zone4Class="Armed";
			}
		}

	}
	$conn->close(); //Close connection

	//TODO - Make this better - NdK
	//Build up JSON response for app to read statuses and CSS
	$resp = "{ ";
	$resp .= "\"responseTime\":\"";
	$resp .= date('d M Y H:i:s');
	$resp .= "\", ";
	$resp .= "\"status\":\"$status\", ";
	$resp .= "\"class\":\"$class\",";
	$resp .= "\"alarmStatusLastDate\":\"$alarmStatusLastDate\",";
	$resp .= "\"alarmScreamPulse\":\"$alarmScreamPulse\",";
	$resp .= "\"statusClass\" : \"$statusClass\",";
	$resp .= "\"alarmScreamStatusClass\": \"$alarmScreamStatusClass\",";
	$resp .= "\"alarmScreamText\":\"$alarmScreamText\",";
	$resp .= "\"alarmScreamStatus\":\"$alarmScreamStatus\",";
	$resp .= "\"zones\": [";
	$resp .= "{ \"name\":\"1\", \"status\": \"$zone1Status\", \"class\" : \"$zone1Class\" },";
	$resp .= "{ \"name\":\"2\", \"status\": \"$zone2Status\", \"class\" : \"$zone2Class\" },";
	$resp .= "{ \"name\":\"3\", \"status\": \"$zone3Status\", \"class\" : \"$zone3Class\" },";
	$resp .= "{ \"name\":\"4\", \"status\": \"$zone4Status\", \"class\" : \"$zone4Class\" }";
    $resp .= "],";
	$resp .= "\"lights\": [";
	$resp .= "{ \"name\":\"1\", \"status\": \"$lights1[0]\"},";
	$resp .= "{ \"name\":\"2\", \"status\": \"$lights2[0]\" },";
	$resp .= "{ \"name\":\"3\", \"status\": \"$lights3[0]\" },";
	$resp .= "{ \"name\":\"4\", \"status\": \"$lights4[0]\" }";
    $resp .= "]";
	$resp .= " }";
	//$resp = "{ \"a\":\"xxx\" }";

	echo ($resp);
}

//Handle arming/disarming of alarm
function arm($armed)
{
	global $servername, $username, $password, $dbname;
	if($armed == "1")
	{
		//Send Rf signal from Pi to alarm system
		//Note, you have to intercept your normal alarm remote to learn the correct codes below
		exec("/home/pi/_433D/_433D -t17 -g13000 -b23 -x6 -0 1000 -1 2433 2849116 -f");		
	}
	else if ($armed == "2")
	{
		//Panic! Send pannic Rf signal to alarm system
		exec("/home/pi/_433D/_433D -t17 -g13000 -b23 -x20 -0 1000 -1 2433 2693089 -f");
		// Create connection
		$conn = new mysqli($servername, $username, $password, $dbname);
		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}
		//Update database
		$sql = "UPDATE ZoneState SET State = 1, EventDateTime=timestamp(now())";
		$result = $conn->query($sql);
		$conn->close(); //Close connection
	}
}

//Handle resetting of zones
function resetStatus($zoneNumber)
{
	global $servername, $username, $password, $dbname;
		// Create connection
		$conn2 = new mysqli($servername, $username, $password, $dbname);
		// Check connection
		if ($conn2->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}
		//Update database
		$sql = "UPDATE ZoneState SET State=0, EventDateTime=now() WHERE ID=$zoneNumber";
		$result = $conn2->query($sql);
		$conn2->close(); //Close connection
	}

//Switch lights/electronics on/off
function switchLight($light)
{
	$lightpin = "";
	switch ($light) {
    case "1":
        $lightpin = "18";
        break;
    case "2":
        $lightpin = "15";
        break;
    case "3":
        $lightpin = "14";
        break;
    case "4":
        $lightpin = "22";
        break;
    default:
	}

	if($lightpin != "")
	{
		//Perform the on/off switch
		exec("gpio -g read $lightpin", $lightstatus, $error);
		exec("gpio -g mode $lightpin out");
		//echo($lightpin);
		if($lightstatus[0] == 1)
		{
			exec("gpio -g write $lightpin 0");
		}
		else
		{
			exec("gpio -g write $lightpin 1");
		}
	}
}

//Read Http 
if (isset($_GET['arm'])) {
    arm($_GET['arm']);
	showStatus();
}
else if (isset($_GET['light'])) {
    switchLight($_GET['light']);
	showStatus();
}
else if (isset($_GET['zone'])) {
    resetStatus($_GET['zone']);
	showStatus();
}
else
{
	showStatus();
}
?>
