<? 
require_once $_SERVER["DOCUMENT_ROOT"] . "/world_config.php";

$station_id=cleanStationID($_REQUEST["station_id"]);




$db=_open_mysql("worldData");

/* if not public, then we need to be authorized */
if ( 0==authPublic($station_id,$db) ) {
        require $_SERVER["DOCUMENT_ROOT"] . "/auth.php";
		if(authSerialNumber($_SESSION['username'],$station_id,$db) < 0){
			$docRoot = $_SERVER["DOCUMENT_ROOT"];
	
			header("Location: /login.php", true);
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

$gsql=sprintf("SELECT *, DATE_FORMAT(packet_date,'%%Y-%%m-%%dT%%TZ') AS packet_date_iso FROM gpsUp WHERE station_id='%s' ORDER BY packet_date DESC", $serial_id);
$gquery=mysql_query($gsql,$db);

$gr=mysql_fetch_array($gquery,MYSQL_ASSOC);


// Creates the Document.
$dom = new DOMDocument('1.0', 'UTF-8');

// Creates the root KML element and appends it to the root document.
$node = $dom->createElementNS('http://earth.google.com/kml/2.1', 'kml');
$parNode = $dom->appendChild($node);

// Creates a KML Document element and append it to the KML element.
$dnode = $dom->createElement('Document');
$docNode = $parNode->appendChild($dnode);


$lastPos='';

do {
	
	$pos=sprintf("%f,%f,0",$gr['longitude'],$gr['latitude']);
	if ( '' == $lastPos ) {
		$lastPos=$pos;
		continue;
	}


	$node = $dom->createElement('Placemark');
	$placeNode = $docNode->appendChild($node);

	// Creates an id attribute and assign it the value of id column.
	$placeNode->setAttribute('id', 'placemark' . $gr['packet_date_iso']);


	/* name element */
//	$nameNode = $dom->createElement('name',htmlentities($gr['packet_date_iso']));
//	$placeNode->appendChild($nameNode);

	// Creates a LineString element.
	$lineStringNode = $dom->createElement('LineString');
	$placeNode->appendChild($lineStringNode);

	// Creates a coordinates element and gives it the value of the lng and lat columns from the results.
	$coorStr = sprintf("%s %s",$lastPos,$pos);
	$coorNode = $dom->createElement('coordinates', $coorStr);
	$lineStringNode->appendChild($coorNode);

	$lineStringNode->appendChild($dom->createElement('extrude','1'));
	$lineStringNode->appendChild($dom->createElement('tessellate','1'));

	$lastPos=$pos;

} while ( $gr=mysql_fetch_array($gquery,MYSQL_ASSOC) );

$kmlOutput = $dom->saveXML();

if ( isset($_REQUEST['action']) && $_REQUEST['action'] == 'download' ) {
	header('Content-Transfer-Encoding: binary');  // For Gecko browsers mainly
	$gdate=gmdate('D, d M Y H:i:s');
	header('Last-Modified: ' . $gdate . ' GMT');
	header('Content-Encoding: none');
	header('Content-Type: application/vnd.google-earth.kml+xml');
	$filename=sprintf("%s_%s.kml",$station_id,gmdate('Ymd_His'));
	header('Content-Disposition: attachment; filename=' . $filename);  // Make the browser display the Save As dialog
} else if ( isset($_REQUEST['action']) && $_REQUEST['action'] == 'text' ) {
	header('Content-type: text/plain');
} else {
	header('Content-type: application/vnd.google-earth.kml+xml');
}

echo $kmlOutput;
?>
