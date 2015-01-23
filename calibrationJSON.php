<?
require_once $_SERVER["DOCUMENT_ROOT"] . "/world_config.php";
$station_id=cleanStationID($_REQUEST["station_id"]);

$_POST["username"]=$_REQUEST["username"];
$_POST["password"]=$_REQUEST["password"];

if(isset($_REQUEST["channel"])){
	if(is_numeric($_REQUEST['channel'])){	
		$where.=sprintf(" AND whereUsed.stationChannel=\"%s\" ",$_REQUEST["channel"]);
	}
}

$db=_open_mysql("worldData");

if ( 0==authPublic($station_id,$db) ) {
	require "auth.php";
	
}

$db=_open_mysql("calibration");
$sql=sprintf("SELECT * FROM calibrationAnemometer LEFT JOIN whereUsed ON calibrationAnemometer.serialNumber=whereUsed.sensorSerialNumber WHERE whereUsed.stationSerialNumber=\"%s\"%s;",$station_id,$where);
$query=mysql_query($sql,$db);
$deviceInfo=mysql_fetch_array($query,MYSQL_ASSOC);
//echo $sql;

foreach ($deviceInfo as $key => $value){
	$jrow[$key]=$value;
}

if($jrow!=null){

	echo json_encode($jrow);
} else{
	echo "{}";
}
?>
