<?

/*
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
//*/
$time = time();
require_once $_SERVER["DOCUMENT_ROOT"] . "/world_config.php";
$station_id=cleanStationID($_REQUEST["station_id"]);


function batterySOC($voltage) {

	if ( 16 <= $voltage ) {
		$voltage /= 2;
	}

	if ( 12.7 <= $voltage ) {
		return "100%";
	} else if ( 12.5 <= $voltage ) {
		return "90%";
	} else if ( 12.42 <= $voltage ) {
		return "80%";
	} else if ( 12.32 <= $voltage ) {
		return "70%";
	} else if ( 12.20 <= $voltage ) {
		return "60%";
	} else if ( 12.06 <= $voltage ) {
		return "50%";
	} else if ( 11.9 <= $voltage ) {
		return "40%";
	} else if ( 11.75 <= $voltage ) {
		return "30%";
	} else if ( 11.58 <= $voltage ) {
		return "20%";
	} else if ( 10.31 <= $voltage ) {
		return "10%";
	} else {
		return "0%";
	}
}

function k2BatterySOC($voltage) {

	if ( 13.17 <= $voltage ) {
		return "100%";
	} else if ( 13.13 <= $voltage ) {
		return "90%";
	} else if ( 13.09 <= $voltage ) {
		return "80%";
	} else if ( 13.05 <= $voltage ) {
		return "70%";
	} else if ( 13.01 <= $voltage ) {
		return "60%";
	} else if ( 12.96 <= $voltage ) {
		return "50%";
	} else if ( 12.92 <= $voltage ) {
		return "40%";
	} else if ( 12.80 <= $voltage ) {
		return "30%";
	} else if ( 12.65 <= $voltage ) {
		return "20%";
	} else if ( 12.4 <= $voltage ) {
		return "10%";
	} else {
		return "0%";
	}
}




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



$sql=sprintf("SELECT timeZone, timeZoneOffsetHours From deviceInfo WHERE serialNumber='%s'",$row["parent"]);
$query=mysql_query($sql,$db);
$r=mysql_fetch_array($query,MYSQL_ASSOC);

$row["parentTimeZone"] = $r["timeZone"];
$row["parentTimeZoneOffsetHours"] = $r["timeZoneOffsetHours"];
//echo $sql;



$db=_open_mysql("worldDataView");
/* pull actual last record */
$sql=sprintf("SELECT *, DATE_ADD(packet_date, INTERVAL %f HOUR) as packet_date_local FROM view_%s ORDER BY packet_date DESC LIMIT 1",$row["parentTimeZoneOffsetHours"],$station_id);
//echo $sql;
$query=mysql_query($sql,$db);
$r=mysql_fetch_array($query,MYSQL_ASSOC);

foreach ($r as $key => $value){
	$row[$key."_last"]=$value;
}
$row["batteryStateOfCharge_percent_last"]=k2BatterySOC($row["vUPS_last"]);
$row["batteryVehStateOfCharge_percent_last"]=batterySOC($row["vVehicle_last"]);
$start=getOffsetDate($row["parentTimeZoneOffsetHours"]);
//echo $start;

$sql=sprintf("SELECT 
		MAX(iCharger) AS maxICharger, 
		MIN(iCharger) AS minICharger, 
		MAX(iLoad) AS maxILoad, 
		MIN(iLoad) AS minILoad, 
		MAX(tempExtC) AS maxTempExtC, 
		MIN(tempExtC) AS minTempExtC, 
		MAX(tempIntC) AS maxTempIntC, 
		MIN(tempIntC) AS minTempIntC, 
		MAX(vVehicle) AS maxVehBatteryStateOfCharge, 
		MIN(vVehicle) AS minVehBatteryStateOfCharge, 
		MAX(vUPS) AS maxBatteryStateOfCharge, 
		MIN(vUPS) AS minBatteryStateOfCharge 
	FROM view_%s 
	WHERE packet_date >= '%s' AND packet_date < DATE_ADD('%s', INTERVAL 1 DAY)",$station_id,$start,$start);
$query=mysql_query($sql,$db);
$r=mysql_fetch_array($query,MYSQL_ASSOC);

//echo $sql;

foreach ($r as $key => $value){
	$row[$key.""]=$value;
	
}



$sql=sprintf("SELECT 
		MAX(batt) AS maxBatt, 
		MIN(batt) AS minBatt 
	FROM view_%s_cell 
	WHERE packet_date >= '%s' AND packet_date < DATE_ADD('%s', INTERVAL 1 DAY)",$station_id,$start,$start);
$query=mysql_query($sql,$db);
$r=mysql_fetch_array($query,MYSQL_ASSOC);

//echo $sql;

foreach ($r as $key => $value){
	$row[$key.""]=$value;
	
}

$sql=sprintf("SELECT batt FROM view_%s_cell ORDER BY packet_date DESC LIMIT 1",$station_id,$start,$start);
$query=mysql_query($sql,$db);
$r=mysql_fetch_array($query,MYSQL_ASSOC);

//echo $sql;

foreach ($r as $key => $value){
	$row[$key.""]=$value;
	
}



/* get time when these values occurred */


$row["minBatt_time"]=getMinMaxDate("batt", $row['minBatt'], $start, $station_id."_cell", $db, $row["parentTimeZoneOffsetHours"]);
$row["maxBatt_time"]=getMinMaxDate("batt", $row['maxBatt'], $start,$station_id."_cell", $db, $row["parentTimeZoneOffsetHours"]);


$row["minBatteryStateOfCharge_time"]=getMinMaxDate("vUPS", $row['minBatteryStateOfCharge'], $start, $station_id, $db, $row["parentTimeZoneOffsetHours"]);
$row["maxBatteryStateOfCharge_time"]=getMinMaxDate("vUPS", $row['maxBatteryStateOfCharge'], $start,$station_id, $db, $row["parentTimeZoneOffsetHours"]);

$row["minVehBatteryStateOfCharge_time"]=getMinMaxDate("vVehicle",$row['minVehBatteryStateOfCharge'],$start, $station_id, $db, $row["parentTimeZoneOffsetHours"]);
$row["maxVehBatteryStateOfCharge_time"]=getMinMaxDate("vVehicle",$row['maxVehBatteryStateOfCharge'],$start, $station_id, $db, $row["parentTimeZoneOffsetHours"]);

$row["minILoad_time"]=getMinMaxDate("iLoad", $row['minILoad'], $start,$station_id, $db, $row["parentTimeZoneOffsetHours"]);
$row["maxILoad_time"]=getMinMaxDate("iLoad",$row['maxILoad'],$start, $station_id, $db, $row["parentTimeZoneOffsetHours"]);

$row["minICharger_time"]=getMinMaxDate("iCharger",$row['minICharger'], $start,$station_id, $db, $row["parentTimeZoneOffsetHours"]);
$row["maxICharger_time"]=getMinMaxDate("iCharger", $row['maxICharger'],$start, $station_id, $db, $row["parentTimeZoneOffsetHours"]);

$row["minExtTemp_time"]=getMinMaxDate("tempExtC",$row['minTempExtC'],$start, $station_id, $db, $row["parentTimeZoneOffsetHours"]);
$row["maxExtTemp_time"]=getMinMaxDate("tempExtC",$row['maxTempExtC'],$start, $station_id, $db, $row["parentTimeZoneOffsetHours"]);

$row["minIntTemp_time"]=getMinMaxDate("tempIntC",$row['minTempIntC'],$start, $station_id, $db, $row["parentTimeZoneOffsetHours"]);
$row["maxIntTemp_time"]=getMinMaxDate("tempIntC",$row['maxTempIntC'],$start, $station_id, $db, $row["parentTimeZoneOffsetHours"]);


/* convert volts to percentage */
$row["minBatteryStateOfCharge_percent"]=k2BatterySOC($row["minBatteryStateOfCharge"]);
$row["maxBatteryStateOfCharge_percent"]=k2BatterySOC($row["maxBatteryStateOfCharge"]);

$row["minVehBatteryStateOfCharge_percent"]=batterySOC($row["minVehBatteryStateOfCharge"]);
$row["maxVehBatteryStateOfCharge_percent"]=batterySOC($row["maxVehBatteryStateOfCharge"]);



//$row["maxBatteryStateOfCharge_time"] = getMinMaxDate("vVehicle",$r["maxVehBatteryStateOfCharge"],$station_id,$db);
//$row["minBatteryStateOfCharge_time"] = getMinMaxDate("vVehicle",$r["minVehBatteryStateOfCharge"],$station_id,$db);
$row["genJSONTime"]=time()-$time . " seconds";

//*
$row["TEST"] = getOffsetDate(-5);//
$row["TEST1"] = new DateTime();
//*/
$row["TEST2"] = getMinMaxDate('batt', '100', '2016-06-29 21:11:00',  'A3448_cell', _open_mysql("worldDataView"), '-5');
$row["TEST3"] = getOffsetDatedebug(-5);
$row["TEST4"] = new DateTime(gmdate('Y-m-d')." 00:00:00",new DateTimeZone("UTC"));
$row["TEST5"] = new DateTime(Date('Y-m-d')." 00:00:00",new DateTimeZone("UTC"));

echo json_encode($row);

//echo $r["minBatteryStateOfCharge"];

function getOffsetDate ($tz) {
	$date = new DateTime(gmdate('Y-m-d')." 00:00:00",new DateTimeZone("UTC"));
	/* Checks if tz is a whole number */
	if (is_numeric($tz) && floor($tz) == $tz ) {
		if ($tz > 0) {		
			$date->sub(new DateInterval(sprintf('PT%sH',$tz)));
		} else {
			$date-> add(new DateInterval(sprintf('PT%sH',abs($tz))));
			
		}
	} else {
		if ($tz > 0) {		
			$date->sub(new DateInterval(sprintf('PT%sH30M',floor($tz))));
		} else {
			$date->add(new DateInterval(sprintf('PT%sH30M',abs(ceil($tz)))));
		}
	}

	return $date->format('Y-m-d H:i:s');
}

function getOffsetDatedebug ($tz) {
	$date = new DateTime(Date('Y-m-d')." 19:00:00",new DateTimeZone("UTC"));
	/* Checks if tz is a whole number */
	if (is_numeric($tz) && floor($tz) == $tz ) {
		if ($tz > 0) {		
			$date->sub(new DateInterval(sprintf('PT%sH',$tz)));
		} else {
			$date-> add(new DateInterval(sprintf('PT%sH',abs($tz))));
			
		}
	} else {
		if ($tz > 0) {		
			$date->sub(new DateInterval(sprintf('PT%sH30M',floor($tz))));
		} else {
			$date->add(new DateInterval(sprintf('PT%sH30M',abs(ceil($tz)))));
		}
	}
	return $date->format('Y-m-d H:i:s');
}

function getMinMaxDate($col, $val, $start,  $station_id, $db, $tzoff){
	//echo $col ." ". $val ." ". $start ." ". $station_id ." ". $db ." ". $tzoff;
	$sql=sprintf('SELECT DATE_ADD(packet_date, INTERVAL %f HOUR) as packet_date , %s 
		FROM  view_%s 
		WHERE 
			packet_date >= "%s"  AND packet_date < DATE_ADD("%s", INTERVAL 1 DAY) AND
			%s = %s ORDER BY packet_date ASC LIMIT 1',$tzoff,$col,$station_id,$start, $start, $col,$val);

	
	$query=mysql_query($sql,$db);
	$time=mysql_fetch_array($query,MYSQL_ASSOC);
	return $time["packet_date"];

}


?>
