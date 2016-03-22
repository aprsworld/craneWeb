<?
$station_id=$_REQUEST["station_id"];
$expr=24;
$type="HOUR";

$web=isset($_REQUEST["web"]);

if( isset( $_REQUEST["expr"] ) ){
	$expr = $_REQUEST["expr"];
}
if( isset( $_REQUEST["type"] ) ){
	$type = $_REQUEST["type"];
}

$_POST["username"]=$_REQUEST["username"];
$_POST["password"]=$_REQUEST["password"];

require_once $_SERVER["DOCUMENT_ROOT"] . "/world_config.php";
$db=_open_mysql("worldData");

if ( 0==authPublic($station_id,$db) ) {
	require "auth.php";
	
}

$sqlTZ = sprintf("SELECT timeZoneOffsetHours FROM deviceInfo WHERE serialNumber='%s'",mysql_real_escape_string($station_id));
$queryTZ=mysql_query($sqlTZ,$db);
$tzR=mysql_fetch_array($queryTZ,MYSQL_ASSOC);



$timeZoneOffsetHours = $tzR["timeZoneOffsetHours"];


$sql=sprintf("SELECT COUNT(*) as count, UNIX_TIMESTAMP(packet_date) as packet_date, ROUND(AVG(windSpeed),1) as speed, MAX(windGust) as gust,  ROUND(AVG(batteryStateOfCharge),1) as battery FROM rdLoggerCell_%s  WHERE packet_date>DATE_SUB(NOW(), INTERVAL %d %s) AND packet_date <=NOW() GROUP BY LEFT(packet_date,16)",mysql_real_escape_string ($station_id), $expr, mysql_real_escape_string ($type));

if($web)
$sql=sprintf("SELECT UNIX_TIMESTAMP(packet_date) as packet_date, ROUND(AVG(windSpeed),1) as speed, MAX(windGust) as gust,  ROUND(AVG(batteryStateOfCharge),1) as battery FROM rdLoggerCell_%s  WHERE packet_date>DATE_SUB(NOW(), INTERVAL %d %s) AND packet_date <=NOW() GROUP BY LEFT(packet_date,15)",mysql_real_escape_string ($station_id), $expr, mysql_real_escape_string ($type));

$query=mysql_query($sql,$db);
$i=0;
while($r=mysql_fetch_array($query,MYSQL_ASSOC)){


//echo "<br>";
//print_r($r);
$jrow["row"+$i]=$r;
$i++;
}

//echo "<BR><br><br>";
if($web){
	echo "{ \"block\":" .json_encode($jrow). ", \"timeZoneOffsetHours\": \"".$timeZoneOffsetHours."\"}";
}else{
	echo json_encode($jrow);
}
//echo $sql;
?>
