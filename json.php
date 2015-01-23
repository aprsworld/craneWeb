<?
require_once $_SERVER["DOCUMENT_ROOT"] . "/world_config.php";
$station_id=cleanStationID($_REQUEST["station_id"]);

$db=_open_mysql("worldData");

if ( 0==authPublic($station_id,$db) ) {
	require $_SERVER["DOCUMENT_ROOT"] . "/auth.php";
}

$sql=sprintf("SELECT * FROM deviceInfo WHERE serialNumber='%s'",$station_id);
$query=mysql_query($sql,$db);
$deviceInfo=mysql_fetch_array($query,MYSQL_ASSOC);
//print_r($deviceInfo);

foreach ($deviceInfo as $key => $value){
	$row[$key]=$value;
}

$sql=sprintf("SELECT status.packet_date, sec_to_time(unix_timestamp()-unix_timestamp(packet_date)) AS ageTime,(unix_timestamp()-unix_timestamp(packet_date)) AS ageSeconds,deviceInfo.owner, deviceInfo.updateRate, deviceInfo.timeZone, deviceInfo.timeZoneOffsetHours, DATE_ADD(status.packet_date,INTERVAL deviceInfo.timeZoneOffsetHours HOUR) AS packet_date_local FROM status LEFT JOIN (deviceInfo) ON (status.serialNumber=deviceInfo.serialNumber) WHERE status.serialNumber='%s'",$station_id);
$query=mysql_query($sql,$db);
$deviceInfo=mysql_fetch_array($query,MYSQL_ASSOC); 

foreach ($deviceInfo as $key => $value){
	$row[$key]=$value;
}

/* pull actual last record */
$sql=sprintf("SELECT * FROM rdLoggerCell_%s WHERE packet_date='%s'",$station_id,$deviceInfo["packet_date"]);
$query=mysql_query($sql,$db);
$r=mysql_fetch_array($query,MYSQL_ASSOC);

foreach ($r as $key => $value){
	$row[$key."_last"]=$value;
}

/* pull last status record */
$sql=sprintf("SELECT SEC_TO_TIME(UNIX_TIMESTAMP()-UNIX_TIMESTAMP(packet_date)) AS ageTime,(UNIX_TIMESTAMP()-UNIX_TIMESTAMP(packet_date)) AS ageSeconds, DATE_ADD(packet_date,INTERVAL %f HOUR) AS packet_date_local, rdLoggerCellStatus_%s.* FROM rdLoggerCellStatus_%s ORDER BY packet_date DESC LIMIT 1",$deviceInfo['timeZoneOffsetHours'],$station_id,$station_id);
$statusquery=mysql_query($sql,$db);
$status=@mysql_fetch_array($statusquery,MYSQL_ASSOC);

foreach ($status as $key => $value){
	$row[$key."_status"]=$value;
}

/* find total reports for the day */
$sql=sprintf("SELECT COUNT(packet_date) AS nPacketsToday FROM rdLoggerCell_%s WHERE DATE_ADD(packet_date,INTERVAL %f HOUR) >= LEFT(DATE_ADD(now(),INTERVAL %f HOUR),10)",$station_id,$deviceInfo["timeZoneOffsetHours"],$deviceInfo["timeZoneOffsetHours"]);
$query=mysql_query($sql,$db);
$reports=mysql_fetch_array($query,MYSQL_ASSOC);

foreach ($reports as $key => $value){
	$row[$key]=$value;
}

$row["nPacketsToday"]=number_format($row["nPacketsToday"],0);

/* find max hour */
$sql=sprintf("SELECT MAX(windSpeed) AS maxWindSpeed, MAX(windGust) AS maxWindGust FROM rdLoggerCell_%s WHERE packet_date>=DATE_SUB(now(),INTERVAL 1 HOUR)",$station_id);
$query=mysql_query($sql,$db);
$maxHour=mysql_fetch_array($query,MYSQL_ASSOC);

foreach ($maxHour as $key => $value){
	$row[$key."_hour"]=$value;
}

/* find max today */
$sql=sprintf("SELECT MAX(windSpeed) AS maxWindSpeed, MAX(windGust) AS maxWindGust FROM rdLoggerCell_%s WHERE DATE_ADD(packet_date,INTERVAL %f HOUR)>=LEFT(DATE_ADD(now(),INTERVAL %f HOUR),10)",$station_id,$deviceInfo["timeZoneOffsetHours"],$deviceInfo["timeZoneOffsetHours"]);
$query=mysql_query($sql,$db);
$maxToday=mysql_fetch_array($query,MYSQL_ASSOC);

foreach ($maxToday as $key => $value){
	$row[$key."_today"]=$value;
}

$sql=sprintf("SELECT MAX(batteryStateOfCharge) AS maxBatteryStateOfCharge, MIN(batteryStateOfCharge) AS minBatteryStateOfCharge FROM rdLoggerCell_%s WHERE DATE_ADD(packet_date,INTERVAL %f HOUR)>=LEFT(DATE_ADD(now(),INTERVAL %f HOUR),10)",$station_id,$deviceInfo["timeZoneOffsetHours"],$deviceInfo["timeZoneOffsetHours"]);

$query=mysql_query($sql,$db);
$batteryToday=mysql_fetch_array($query);

foreach ($batteryToday as $key => $value){
	$row[$key."_today"]=$value;
}
$row["uptime_status"]=number_format($row["uptime_status"],0);

echo json_encode($row);
?>
