<?
$station_id=$_REQUEST["station_id"];

require_once $_SERVER["DOCUMENT_ROOT"] . "/world_config.php";
$db=_open_mysql("worldData");

/* if not public, then we need to be authorized */
if ( 0==authPublic($station_id,$db) ) {
	require $_SERVER["DOCUMENT_ROOT"] . "/auth.php";
}

$day=$_REQUEST["day"];
$tzOffset=getTimeZoneOffsetHours($station_id,$db);

$title=$headline="Detailed Wind Data for " . $station_id;
$subtitle="Using data at 1 minute intervals";
$headers='
<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
<script language="javascript" type="text/javascript" src="jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="jquery.dataTables.min.css" type="text/css"/>';
$deviceInfo=getDeviceInfo($station_id,$db);
$head=$title=$deviceInfo["displayName"];
require "rdHead.php";

$sql=sprintf("SELECT DATE_ADD(packet_date,INTERVAL %d HOUR) AS packet_date,windSpeed,windGust,windCount,windDirectionSector,batteryStateOfCharge,(windCount/10.0)*0.765 + 0.35 AS windAverage FROM rdLoggerCell_%s WHERE (SECOND(packet_date)<=5 OR SECOND(packet_date)>=55) AND LEFT(DATE_ADD(packet_date,INTERVAL %d HOUR),10)='%s' ORDER BY packet_date",$tzOffset,$station_id,$tzOffset,$day);
$query=mysql_query($sql,$db);
?>

<div id="wrapper">

<table id="table" border="1">
	<thead>
		<tr>
			<th style = "background-color: #80B2CC;" class="Date" rowspan="2">Date</th>
			<th style = "background-color: #80B2CC;" colspan="2">Speed</th>
			<th style = "background-color: #80B2CC;" colspan="2">Gust</th>
			<th style = "background-color: #80B2CC;" class="Direction" rowspan="2">Direction</th>
			<th style = "background-color: #80B2CC;" class="Battery" rowspan="2">Battery</th>
		</tr>
		<tr>
			<th style = "background-color: #80B2CC;" class="mss" >m/s</th>
			<th style = "background-color: #80B2CC;" class="mphs" >MPH</th>
			<th style = "background-color: #80B2CC;" class="msg" >m/s</th>
			<th style = "background-color: #80B2CC;" class="mphg" >MPH</th>
		</tr>
	</thead>
	<tbody>
<?

while ( $r=mysql_fetch_array($query) ) {
	printf("<tr>\n");
	printf("<td>%s</td>",$r["packet_date"]);
	printf("<td>%0.1f</td>",$r["windSpeed"]);
	printf("<td>%0.1f</td>",$r["windSpeed"] * 2.23694);
	printf("<td>%0.1f</td>",$r["windGust"]);
	printf("<td>%0.1f</td>",$r["windGust"] * 2.23694);
/*
	if ( $r["windCount"] > 0 ) 
		printf("<td>%0.1f</td>",$r["windAverage"]);
	else
		printf("<td>0.0</td>");
*/
	printf("<td>%s</td>",$r["windDirectionSector"]);
	printf("<td>%d%%</td>",$r["batteryStateOfCharge"]);
	printf("</tr>\n");
}

?>
	</tbody>
</table>

<br><br>
<span class="small">Powered by an APRS World, LLC solution.</span>

</div>
</body>
</html>
