<?
$station_id=$_REQUEST["station_id"];

$expr=1;
$type="MINUTE";

if ( isset( $_REQUEST["expr"] ) ) {
	if ( is_numeric($_REQUEST["expr"]) ) {	
		$expr = $_REQUEST["expr"];
	}
}

if ( isset($_REQUEST["type"]) ) {
	$type = strtoupper($_REQUEST["type"]);

	if ( "HOUR" != $type && "MINUTE" != $type ) {
		$type = "MINUTE";
	}
}

require_once $_SERVER["DOCUMENT_ROOT"] . "/world_config.php";
$db=_open_mysql("worldData");

$sql=sprintf("SELECT UNIX_TIMESTAMP(packet_date) AS packet_date, windSpeed, windGust FROM rdLoggerCell_%s  WHERE packet_date>DATE_SUB(NOW(), INTERVAL %d %s) AND packet_date <=NOW()",$station_id, $expr, $type);

$query=mysql_query($sql,$db);
$i=0;
while($r=mysql_fetch_array($query,MYSQL_ASSOC)){


//echo "<br>";
//print_r($r);
$row["row"+$i]=$r;
$i++;
}

//echo "<BR><br><br>";

echo json_encode($row);

?>
