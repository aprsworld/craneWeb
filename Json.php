<?
$station_id=$_REQUEST["station_id"];

$_POST["username"]=$_REQUEST["username"];
$_POST["password"]=$_REQUEST["password"];

require_once $_SERVER["DOCUMENT_ROOT"] . "/world_config.php";
$db=_open_mysql("worldData");

if ( 0==authPublic($station_id,$db) ) {
	require "auth.php";
	
}

/* device info */
$sql=sprintf("SELECT status.packet_date, sec_to_time(unix_timestamp()-unix_timestamp(packet_date)) AS ageTime,(unix_timestamp()-unix_timestamp(packet_date)) AS ageSeconds,deviceInfo.owner, deviceInfo.updateRate, deviceInfo.timeZone, deviceInfo.timeZoneOffsetHours, DATE_ADD(status.packet_date,INTERVAL deviceInfo.timeZoneOffsetHours HOUR) AS packet_date_local FROM status LEFT JOIN (deviceInfo) ON (status.serialNumber=deviceInfo.serialNumber) WHERE status.serialNumber='%s'",$station_id);
$query=mysql_query($sql,$db);
$deviceInfo=mysql_fetch_array($query,MYSQL_ASSOC);

//print_r($deviceInfo);

//echo "<BR>";

/* pull actual last record */
$sql=sprintf("SELECT * FROM rdLoggerCell_%s WHERE packet_date='%s'",$station_id,$deviceInfo["packet_date"]);
$query=mysql_query($sql,$db);
$r=mysql_fetch_array($query,MYSQL_ASSOC);

//print_r($r);

$jrow["auth"] = "true";
$jrow["packet_date"] = $deviceInfo["packet_date"];
$jrow["ageTime"] = $deviceInfo["ageTime"];
$jrow["ageSeconds"] =$deviceInfo["ageSeconds"];
$jrow["owner"] = $deviceInfo["owner"];
$jrow["updateRate"] = $deviceInfo["updateRate"];
$jrow["timeZone"] = $deviceInfo["timeZone"];
$jrow["timeZoneOffsetHours"] = $deviceInfo["timeZoneOffsetHours"];
$jrow["packet_date_local"] = $deviceInfo["packet_date_local"];
$jrow["windSpeed"] = $r["windSpeed"];
$jrow["windGust"] = $r["windGust"];
$jrow["windCount"] = $r["windCount"];
$jrow["windDirectionSector"] = $r["windDirectionSector"];
$jrow["batteryStateOfCharge"] = $r["batteryStateOfCharge"];
$jrow["pulseTime"] = $r["pulseTime"];
$jrow["pulseMinTime"] = $r["pulseMinTime"];

//echo "<BR>";

echo json_encode($jrow);

?>
