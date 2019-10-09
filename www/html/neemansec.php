<!DOCTYPE html>
<?php
header("Access-Control-Allow-Origin: *");
session_start();
//Check if user is logged in
if(isset($_COOKIE["member_login"]))
{
	$_SESSION["member_id"] = $_COOKIE["member_login"];
}
//echo($_SESSION["member_id"]);
if(empty($_SESSION["member_id"]) || $_SESSION["member_id"] != "nihan")
{
	//TODO - Make this better, hard-coded for prototyping - NdK
	header('Location: /login.php');
}
?>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <meta charset="utf-8" />
    <link rel="manifest" href="manifest.json">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <script src="scripts/jquery-3.2.1.min.js"></script>
    <link rel="stylesheet" type="text/css" href="styles/app.css">
    <title>NEEMAN &#8486; SYSTEM</title>
</head>
<body>
	
    <table id="main">
        <tr>
            <td class="MainHead">
                <span>NEEMAN</span> <sup>&#8486;</sup> SYSTEM

            </td>
        </tr>
        <tr>
            <td class="SubHead">ALARM STATUS - BEAMS
            </td>
        </tr>
        <tr>
            <td> 
                <table>
                    <tr>
                        <td colspan="2">
                            <div class="AlarmStatus NotMonitoring" id="alarmStatus" onclick="arm(1)">
                                <table>
                                    <tr>
                                        <td style="width: 44%;">
                                            <div class="AlarmStatusText NotMonitoring" id="alarmStatusText">
                                                NOT ARMED
                                            </div>
                                            <div class="LastDate" id="alarmStatusLastDate">
                                                
                                            </div>
                                        </td>
                                        <td style="width: 1%" >
                                            <div class="pulse-search" id="alarmScreamPulse">
                                                <div style="vertical-align:middle; height:100px;">&#8486;</div>
                                            </div>

                                        </td>
                                        <td style="width: 44%;">
                                            <div class="AlarmStatusText NotMonitoring" id="alarmScreamStatus">
                                                NO SIREN
                                                
                                            </div>
                                            <div class="LastDate" id="alarmScreamText">
                                                ALL OK
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
        <tr>
            <td>
                <table style="padding-left: 4px; padding-right:4px;">
                    <tr>
                        <td style="width: 50%;">
                            <div class="ZoneStatus NotMonitoring" id="zone1" onclick="resetZone('1')">
                                <div class="ZoneName">SIDE GARDEN</div>
                                <div class="ZoneStatusText" id="zone1Status">NOT MONITORING</div>
                            </div>
                        </td>
                        <td style="width: 50%;">
                            <div class="ZoneStatus NotMonitoring" id="zone2" onclick="resetZone('2')">
                                <div class="ZoneName">DECK</div>
                                <div class="ZoneStatusText" id="zone2Status">NOT MONITORING</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 50%;">
                            <div class="ZoneStatus NotMonitoring" id="zone3" onclick="resetZone('3')"> 
                                <div class="ZoneName">COURTYARD</div>
                                <div class="ZoneStatusText" id="zone3Status">NOT MONITORING</div>
                            </div>
                        </td>
                        <td style="width: 50%;">
                            <div class="ZoneStatus NotMonitoring" id="zone4" onclick="resetZone('4')">
                                <div class="ZoneName">FRONT GARDEN</div>
                                <div class="ZoneStatusText" id="zone4Status">NOT MONITORING</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="SubHead">ELECTRONICS</div>
            </td>
        </tr>
        <tr>
            <td>
                <table style="padding-left: 4px; padding-right:4px; margin-top:5px; margin-bottom:5px;">
                    <tr>
                        <td style="width: 50%;">
                            <div class="ZoneStatus LightsOff" id="light1" onclick="switchLight('1');">
                                <div class="ZoneName">DINING ROOM</div>
                                <div class="LightStatusText" id="light1Status">ON</div>
                            </div>
                        </td>
                        <td style="width: 50%;">
                            <div class="ZoneStatus LightsOff" id="light2" onclick="switchLight('2')">
                                <div class="ZoneName">LIVING ROOM</div>
                                <div class="LightStatusText" id="light2Status">OFF</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 50%;">
                            <div class="ZoneStatus LightsOff" id="light3" onclick="switchLight('3')">
                                <div class="ZoneName">KITCHEN</div>
                                <div class="LightStatusText" id="light3Status">OFF</div>
                            </div>
                        </td>
                        <td style="width: 50%;">
                            <div class="ZoneStatus LightsOff" id="light4" onclick="switchLight('4')">
                                <div class="ZoneName">OUTSIDE</div>
                                <div class="LightStatusText" id="light4Status">OFF</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="SubHead"><div id="responseTime"></div>
        </tr>
        <tr>
            <td class="SubHead">
            <div id="conn" onclick="refreshNoCache()">Not connected</div>
            
            </td>
        </tr>
    </table>
    
    <audio id="myaudio" preload="auto" controls="false" autoplay="autoplay" style="display:none;">
		<source src="/switch.mp3" />
	 </audio>
    <audio src="/sga_alarm.mp3" preload="auto" controls="none" id="alarmAudio" style="display:none;"/>
    
	<script  type="text/javascript" src="scripts/app.js"></script>

</body>
</html>
