<?
$station_id=$_REQUEST["station_id"];

require_once $_SERVER["DOCUMENT_ROOT"] . "/world_config.php";
$db=_open_mysql("worldData");

/* if not public, then we need to be authorized */
if ( 0==authPublic($station_id,$db) ) {
	require $_SERVER["DOCUMENT_ROOT"] . "/auth.php";
	if(authSerialNumber($_SESSION['username'],$station_id,$db) < 0){
		$docRoot = $_SERVER["DOCUMENT_ROOT"];

		header("Location: /login.php", true);
	}
}

$day=$_REQUEST["day"];
$tzOffset=getTimeZoneOffsetHours($station_id,$db);

$title=$headline="Detailed Wind Data for " . $station_id;
$subtitle="Using data at 1 minute intervals";
$headers='<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>';
$deviceInfo=getDeviceInfo($station_id,$db);
$head=$title=$deviceInfo["displayName"];
require "rdHead.php";

$sql=sprintf("SELECT DATE_ADD(packet_date,INTERVAL %d HOUR) AS packet_date,windSpeed,windGust,windDirectionSector,batteryStateOfCharge FROM rdLoggerCell_%s WHERE LEFT(DATE_ADD(packet_date,INTERVAL %d HOUR),10)='%s' GROUP BY LEFT(packet_date, 16) ORDER BY packet_date",$tzOffset,$station_id,$tzOffset,$day);
$query=mysql_query($sql,$db);
?>

<div id="wrapper">

<table id="table" border="1">
	<thead>
		<tr>
			<th class="Date" rowspan="2">Date</th>
			<th colspan="2">Speed</th>
			<th colspan="2">Gust</th>
			<th class="Direction" rowspan="2">Direction</th>
			<th class="Battery" rowspan="2">Battery</th>
		</tr>
		<tr>
			<th class="mss" >m/s</th>
			<th class="mphs" >MPH</th>
			<th class="msg" >m/s</th>
			<th class="mphg" >MPH</th>
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
