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
$deviceInfo=getDeviceInfo($station_id,$db);
$head=$title=$deviceInfo["displayName"];
$title=$headline="Detailed Wind Data for " . $station_id;
$subtitle="Using hourly data";


require "rdHead.php";

$sql=sprintf("SELECT LEFT(DATE_ADD(packet_date,INTERVAL %d HOUR),15) AS date_hour, ROUND(AVG(windSpeed),2) AS windAverage, MAX(windGust) AS windGust, COUNT(*) AS nPackets FROM rdLoggerCell_%s WHERE LEFT(DATE_ADD(packet_date,INTERVAL %d HOUR),10)='%s' GROUP BY date_hour",$tzOffset,$station_id,$tzOffset,$day);
$query=mysql_query($sql,$db);

?>
<div id="wrapper">
<table border="1">
	<thead>
		<tr>
			<th rowspan="2">Date / Hour</th>
			<th colspan="2">Average Speed</th>
			<th colspan="2">Gust</th>
<!--			<th>Average<br />(1 minute)</th> -->
			<th rowspan="2">Packets Received</th>
		</tr>
		<tr>
			<th>m/s</th>
			<th>MPH</th>
			<th>m/s</th>
			<th>MPH</th>
		</tr>
	</thead>
	<tbody>
<?
while ( $r=mysql_fetch_array($query) ) {
	printf("<tr>\n");
	printf("<td>%s0</td>",$r["date_hour"]);
	printf("<td>%0.1f</td>",$r["windAverage"]);
	printf("<td>%0.1f</td>",$r["windAverage"]*2.23694);
	printf("<td>%0.1f</td>",$r["windGust"]);
	printf("<td>%0.1f</td>",$r["windGust"]*2.23694);
/*
	if ( $r["windCount"] > 0 ) 
		printf("<td>%0.1f</td>",$r["windAverage"]);
	else
		printf("<td>0.0</td>");
*/
	printf("<td>%d%%  (%d total)</td>",($r["nPackets"]/0.6),$r["nPackets"]);
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
