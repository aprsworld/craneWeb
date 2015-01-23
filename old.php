<?
$station_id=$_REQUEST["station_id"];

require_once $_SERVER["DOCUMENT_ROOT"] . "/world_config.php";
$db=_open_mysql("worldData");

/* if not public, then we need to be authorized */
if ( 0==authPublic($station_id,$db) ) {
	require $_SERVER["DOCUMENT_ROOT"] . "/auth.php";
}



/* Determine our title and display name */
$sql=sprintf("SELECT * FROM deviceInfo WHERE serialNumber='%s'",$station_id);
$query=mysql_query($sql,$db);
$deviceInfo=mysql_fetch_array($query);

/* display displayName if it is not null */
if ( "" != $deviceInfo["displayName"] ) $displayName=$deviceInfo["displayName"]; else $displayName=$station_id;
$displayName=htmlspecialchars($displayName);


$title=$headline=$displayName . " <br />Current Conditions";
$head_message="<script src=\"http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=true&amp;key=ABQIAAAAGw6G_4UY80xJh8wgTy33gxQjjn3ebVx2crWpUcytJDzwdvFdJBRgDgor-AQ-ICTdKz_al4bcDPpmEA\" type=\"text/javascript\"></script>";
$body_extra="onunload=\"GUnload()\"";
require $_SERVER["DOCUMENT_ROOT"] . "/world_head.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/winddata/windFunctions.php";

$sql=sprintf("SELECT status.packet_date, sec_to_time(unix_timestamp()-unix_timestamp(packet_date)) AS ageTime,(unix_timestamp()-unix_timestamp(packet_date)) AS ageSeconds,deviceInfo.owner, deviceInfo.updateRate, deviceInfo.timeZone, deviceInfo.timeZoneOffsetHours, DATE_ADD(status.packet_date,INTERVAL deviceInfo.timeZoneOffsetHours HOUR) AS packet_date_local FROM status LEFT JOIN (deviceInfo) ON (status.serialNumber=deviceInfo.serialNumber) WHERE status.serialNumber='%s'",$station_id);
$query=mysql_query($sql,$db);
$deviceInfo=mysql_fetch_array($query);

/* calculate a human readable report received at */
if ( $deviceInfo["ageSeconds"] > 59 ) {
	$rr=sprintf("Report received %s (hours:minutes:seconds) ago.",$deviceInfo["ageTime"]);
} else {
	if ( 1 != $deviceInfo["ageSeconds"] ) 
		$s="s";

	$rr=sprintf("Report received %d second%s ago.",$deviceInfo["ageSeconds"],$s);
}
?>

<? if ( $deviceInfo["ageSeconds"] > 60 ) { ?>
<div class="caution">
<p>
This station is marked as supplying live data, however the data appears to be old. Please check the age of the data carefully before using it!
</p>
</div>
<?
} 

/* pull actual last record */
$sql=sprintf("SELECT * FROM rdLoggerCell_%s WHERE packet_date='%s'",$station_id,$deviceInfo["packet_date"]);
$query=mysql_query($sql,$db);
$r=mysql_fetch_array($query);

/* pull last status record */
$sql=sprintf("SELECT SEC_TO_TIME(UNIX_TIMESTAMP()-UNIX_TIMESTAMP(packet_date)) AS ageTime,(UNIX_TIMESTAMP()-UNIX_TIMESTAMP(packet_date)) AS ageSeconds, DATE_ADD(packet_date,INTERVAL %f HOUR) AS packet_date_local, rdLoggerCellStatus_%s.* FROM rdLoggerCellStatus_%s ORDER BY packet_date DESC LIMIT 1",$deviceInfo['timeZoneOffsetHours'],$station_id,$station_id);
$statusquery=mysql_query($sql,$db);
$status=@mysql_fetch_array($statusquery);

?>

<table>
	<tr>
		<td></td>
		<th>Current Conditions</th>
		<th colspan="2">Today's Conditions</th>
	</tr>
	<tr>
		<th>Report Date:</th>
		<td>
			<? echo $deviceInfo["packet_date_local"] . " " . $deviceInfo["timeZone"]; ?><br />
			<? echo $rr; ?>
		</td>
		<td>
<?
/* find total reports for the day */
$sql=sprintf("SELECT COUNT(packet_date) AS nPacketsToday FROM rdLoggerCell_%s WHERE DATE_ADD(packet_date,INTERVAL %d HOUR) >= LEFT(DATE_ADD(now(),INTERVAL %d HOUR),10)",$station_id,$deviceInfo["timeZoneOffsetHours"],$deviceInfo["timeZoneOffsetHours"]);
$query=mysql_query($sql,$db);
$reports=mysql_fetch_array($query);

		printf("%d reports since midnight.",$reports["nPacketsToday"]);
?>
		</td>
	</tr>
	<tr>
		<th>Wind Speed:</th>
		<td>
			<? printf("%0.1f MPH gusting to %0.1f MPH over last minute<br />(%0.1f m/s gusting to %0.1f m/s)",$r['windSpeed']*2.237,$r['windGust']*2.237,$r["windSpeed"],$r["windGust"]); ?>
<br />
<img src="spark_wind.php?station_id=<? echo $station_id; ?>" alt="Last 24 hours of wind" />
		</td>
		<td>
<?
$sql=sprintf("SELECT max(windSpeed) AS maxWindSpeed, max(windGust) AS maxWindGust FROM rdLoggerCell_%s WHERE packet_date>=DATE_SUB(now(),INTERVAL 1 HOUR)",$station_id);
$query=mysql_query($sql,$db);
$maxHour=mysql_fetch_array($query);

$sql=sprintf("SELECT max(windSpeed) AS maxWindSpeed, max(windGust) AS maxWindGust FROM rdLoggerCell_%s WHERE DATE_ADD(packet_date,INTERVAL %d HOUR)>=LEFT(DATE_ADD(now(),INTERVAL %d HOUR),10)",$station_id,$deviceInfo["timeZoneOffsetHours"],$deviceInfo["timeZoneOffsetHours"]);
$query=mysql_query($sql,$db);
$maxToday=mysql_fetch_array($query);

?>
		<b>Max Gust in Last Hour:</b><br /><? printf("%0.1f MPH (%0.1f m/s)",$maxHour["maxWindGust"]*2.237,$maxHour['maxWindGust']); ?><br />
		<b>Max Gust Today:</b><br /><? printf("%0.1f MPH (%0.1f m/s)",$maxToday["maxWindGust"]*2.237,$maxToday['maxWindGust']); ?>
		</td>
	</tr>
	<tr>
		<th>Wind Direction:</th>
		<td>
			<? printf("%d",$r["windDirectionSector"]*45); ?>&deg;<br />
			0&deg; is direction boom is facing
		</td>
	</tr>
	<tr>
		<th>Battery:</th>
		<td>
			<? printf("%d%%",$r["batteryStateOfCharge"]); ?> charged.
		</td>
		<td>
<?
$sql=sprintf("SELECT max(batteryStateOfCharge) AS maxBatteryStateOfCharge, min(batteryStateOfCharge) AS minBatteryStateOfCharge FROM rdLoggerCell_%s WHERE DATE_ADD(packet_date,INTERVAL %d HOUR)>=LEFT(DATE_ADD(now(),INTERVAL %d HOUR),10)",$station_id,$deviceInfo["timeZoneOffsetHours"],$deviceInfo["timeZoneOffsetHours"]);
$query=mysql_query($sql,$db);
$batteryToday=mysql_fetch_array($query);
?>
		<b>Minimum Today:</b> <? echo $batteryToday["minBatteryStateOfCharge"]; ?>%<br />
		<b>Maximum Today:</b> <? echo $batteryToday["maxBatteryStateOfCharge"]; ?>%<br />

		</td>
	</tr>
<?
function tempStringFromADC($adc) {
	$c=39.394*(5.0/1024.0)*$adc-30.0;
	$f=70.9092*(5.0/1024.0)*$adc-22.0;
	return sprintf("%0.1f&deg;C / %0.1f&deg;F",$c,$f);
}
function rhStringFromADC($adc) {
	return sprintf("%d%%",30.303*(5.0/1024.0)*$adc);
}

if ( isset($r['analog0']) ) {
?>
	<tr>
		<th>Temperature:</th>
		<td>
			<? echo tempStringFromADC($r["analog0"]); ?>
		</td>
		<td>
<?
$sql=sprintf("SELECT MAX(analog0) AS maxAnalog0, MIN(analog0) AS minAnalog0, MAX(analog1) AS maxAnalog1, MIN(analog1) AS minAnalog1 FROM rdLoggerCell_%s WHERE DATE_ADD(packet_date,INTERVAL %d HOUR)>=LEFT(DATE_ADD(now(),INTERVAL %d HOUR),10)",$station_id,$deviceInfo["timeZoneOffsetHours"],$deviceInfo["timeZoneOffsetHours"]);
$query=mysql_query($sql,$db);
$tempToday=mysql_fetch_array($query);
?>
			<b>Minimum Today:</b> <? echo tempStringFromADC($tempToday["minAnalog0"]); ?><br />
			<b>Maximum Today:</b> <? echo tempStringFromADC($tempToday["maxAnalog0"]); ?>

		</td>
	</tr>
	<tr>
		<th>Relative Humidity:</th>
		<td>
			<? echo rhStringFromADC($r["analog1"]); ?>
		</td>
		<td>
			<b>Minimum Today:</b> <? echo rhStringFromADC($tempToday["minAnalog1"]); ?><br />
			<b>Maximum Today:</b> <? echo rhStringFromADC($tempToday["maxAnalog1"]); ?>
		</td>
	</tr>
<?
}
?>
	<tr>
		<th>Data Reports:</th>
		<td colspan="2">
<a href="dailyWind.php?station_id=<? echo $_REQUEST["station_id"]; ?>">Daily Conditions (m/s)</a><br />
<a href="dailyWind.php?station_id=<? echo $_REQUEST["station_id"]; ?>&amp;unit0=MPH&amp;scale0=2.23694">Daily Conditions (MPH)</a>
		</td>
	</tr>
</table>


<? 
if ( @mysql_num_rows($statusquery) > 0  && $status["ageSeconds"] < 3600 ) {
?>
<table width="800">
<?
if ( 0 != $status['latitude'] && 0 != $status['longitude']) { 
?>
	<tr>
		<th colspan="2">Last Location:</th>
	</tr>
	<tr>
		<td colspan="2">


    <div id="map" style="width: 550px; height: 450px; margin-left: auto; margin-right: auto;"></div>
    <noscript><b>JavaScript must be enabled in order for you to use Google Maps.</b> 
      However, it seems JavaScript is either disabled or not supported by your browser. 
      To view Google Maps, enable JavaScript by changing your browser options, and then 
      try again.
    </noscript>
 

    <script type="text/javascript">
    //<![CDATA[
    
    if (GBrowserIsCompatible()) { 

      // A function to create the marker and set up the event window
      // Dont try to unroll this function. It has to be here for the function closure
      // Each instance of the function preserves the contends of a different instance
      // of the "marker" and "html" variables which will be needed later when the event triggers.    
      function createMarker(point,html) {
        var marker = new GMarker(point);
        GEvent.addListener(marker, "click", function() {
          marker.openInfoWindowHtml(html);
        });
        return marker;
      }

      // Display the map, with some controls and set the initial location 
      var map = new GMap2(document.getElementById("map"));
      map.addControl(new GLargeMapControl());
      map.addControl(new GMapTypeControl());
      map.setCenter(new GLatLng(<? echo $status['latitude']; ?>,<? echo $status['longitude']; ?>),16);
    

      var point = new GLatLng(<? echo $status['latitude']; ?>,<? echo $status['longitude']; ?>);
      var marker = createMarker(point,'Location as of <? echo $status['packet_date']; ?> UTC')
      map.addOverlay(marker);


    }
    
    // display a warning if the browser was not compatible
    else {
      alert("Sorry, the Google Maps API is not compatible with this browser");
    }

    // This Javascript is based on code provided by the
    // Community Church Javascript Team
    // http://www.bisphamchurch.org.uk/   
    // http://econym.org.uk/gmap/

    //]]>
    </script>
		</td>
	</tr>
<?
}
?>
	<tr>
<?
/* calculate a human readable report received at */
if ( $status["ageSeconds"] > 59 ) {
	$rr=sprintf("Report received %s (hours:minutes:seconds) ago.",$status["ageTime"]);
} else {
	if ( 1 != $status["ageSeconds"] ) 
		$s="s";

	$rr=sprintf("Report received %d second%s ago.",$status["ageSeconds"],$s);
}
?>
		<th>
			Status Date:
			<br />
			<div style="font-size: 0.7em;">(updates once per hour)</div>
		</th>
		<td>
		<? echo $status["packet_date_local"] . " " . $status["timeZone"]; ?><br />
		<? echo $rr; ?>
		</td>
	</tr>
	<tr>
		<th>
			Crane Wind Logger Uptime:
			<br />
			<div style="font-size: 0.7em;">(at time of status packet)</div>
		</th>
		<td><? echo number_format($status['uptime']); ?> minutes</td>
	</tr>
<!--
	<tr>
		<th>
			Cell Connection Uptime:
			<br />
			<div style="font-size: 0.7em;">(at time of status packet)</div>
		</th>
		<td><? echo number_format($status['gprsUptimeMinutes']); ?> minutes</td>
	</tr>
-->
	<tr>
		<th>SD Card Status:</th>
		<td><? if ( 0==$status['sdStatus'] ) echo "Logging"; else echo "Card not inserted!"; ?></td>
	</tr>
	
</table>
<?
}
?>
<?
require $_SERVER["DOCUMENT_ROOT"] . "/world_foot.php";
?>
