<? 
require_once $_SERVER["DOCUMENT_ROOT"] . "/world_config.php";

$station_id=cleanStationID($_REQUEST["station_id"]);




$db=_open_mysql("worldData");

/* if not public, then we need to be authorized */
if ( 0==authPublic($station_id,$db) ) {
        require $_SERVER["DOCUMENT_ROOT"] . "/auth.php";
		if(authSerialNumber($_SESSION['username'],$station_id,$db) < 0){
			$docRoot = $_SERVER["DOCUMENT_ROOT"];
	
			header("Location:/login.php", true);
		}
}


/* Determine our title and display name */
$deviceInfo=getDeviceInfo($station_id,$db);

/* Check if a CABU unit is assosiated */
$cabu = false;
$sql=sprintf("SELECT * FROM deviceInfo WHERE parent='%s'",$station_id);
$query=mysql_query($sql,$db);
$r=mysql_fetch_array($query,MYSQL_ASSOC);
if(NULL != $r){
	$cabu=true;
} else {
	
	header("Location: http://data.aprsworld.com/" );
	exit;

}
//print_r($r);
$serial_id = $r["serialNumber"];



$head=$title=$deviceInfo["displayName"];
$headline=sprintf("GPS for %s (%s)<br />Current Conditions",$station_id,$serial_id);
require_once "rdHead.php";

$gsql=sprintf("SELECT * FROM gpsUp WHERE station_id='%s' ORDER BY packet_date DESC", $serial_id);
$gquery=mysql_query($gsql,$db);

$gr=mysql_fetch_array($gquery,MYSQL_ASSOC);

?>
<div id="wrapper">
	<div class="line">
		<b>Note:</b>This page does not automatically refresh. You must reload page to check for new data!
		<br />
		<div id="reportBlock" class="block">
			Latest GPS Date:<br />
			<span id="reportDate">
			<? echo $gr['packet_date']; ?>
			</span>
		</div>
	</div>	
	<table class="cabuTable" style="border:none; width:90%">
		<thead>
			<th>GPS Date</th>
			<th>Latitude</th>
			<th>Longitude</th>
			<th>Actions</th>
		</thead>
		<tbody>
<?
do {
	printf("<tr><td>%s UTC</td><td>%s</td><td>%s</td>",$gr['packet_date'],$gr['latitude'],$gr['longitude']);
	printf("<th><a href=\"http://maps.google.com/?q=%s,%s\">Google Map</a></th></tr>\n",$gr['latitude'],$gr['longitude']);

} while ( $gr=mysql_fetch_array($gquery,MYSQL_ASSOC) );
?>

		</tbody>
	</table>
	<br><br>
	<span class="small">Powered by an APRS World, LLC solution.</span>
</div>
</body>
</html>
