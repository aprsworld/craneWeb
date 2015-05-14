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

if ( "text" == strtolower($_REQUEST["mode"]) ) {
	header("Content-type: text/plain");
} else {
	header(sprintf('Content-Disposition: attachment; filename=%s_%s_dayDetail.csv',$station_id,str_replace("-","",substr($day,0,10))));
	header("Content-type: text/csv");
}

$sql=sprintf("SELECT DATE_ADD(packet_date,INTERVAL %d HOUR) AS packet_date,windSpeed,windGust,windDirectionSector,batteryStateOfCharge FROM rdLoggerCell_%s WHERE LEFT(DATE_ADD(packet_date,INTERVAL %d HOUR),10)='%s' GROUP BY LEFT(packet_date, 16) ORDER BY packet_date",$tzOffset,$station_id,$tzOffset,$day);
$query=mysql_query($sql,$db);
//date,windSpeedMS,windGustMS,windAvageMS,windDirectionSector,batteryStateOfCharge
?>
dateLocal,windSpeedMPH,windGustMPH,windSpeedMS,windGustMS,windDirectionSector,batteryStateOfCharge
<?
while ( $r=mysql_fetch_array($query) ) {
	printf("%s,%0.1lf,%0.1lf,",$r["packet_date"],2.236*$r["windSpeed"],2.236*$r["windGust"]);
	printf("%0.1lf,%0.1lf,",$r["windSpeed"],$r["windGust"]);
/*
	if ( $r["windCount"] > 0 ) 
		printf("%0.1f,",$r["windAverage"]);
	else
		printf("0.0,");
*/
	printf("%s,%d\n",$r["windDirectionSector"],$r["batteryStateOfCharge"]);
}

?>
