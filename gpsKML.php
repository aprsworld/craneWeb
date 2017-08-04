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

// Creates the two Style elements, one for restaurant and one for bar, and append the elements to the Document element.
$restStyleNode = $dom->createElement('Style');
$restStyleNode->setAttribute('id', 'restaurantStyle');
$restIconstyleNode = $dom->createElement('IconStyle');
$restIconstyleNode->setAttribute('id', 'restaurantIcon');
$restIconNode = $dom->createElement('Icon');
$restHref = $dom->createElement('href', 'http://maps.google.com/mapfiles/kml/pal2/icon63.png');
$restIconNode->appendChild($restHref);
$restIconstyleNode->appendChild($restIconNode);
$restStyleNode->appendChild($restIconstyleNode);
$docNode->appendChild($restStyleNode);

$barStyleNode = $dom->createElement('Style');
$barStyleNode->setAttribute('id', 'barStyle');
$barIconstyleNode = $dom->createElement('IconStyle');
$barIconstyleNode->setAttribute('id', 'barIcon');
$barIconNode = $dom->createElement('Icon');
$barHref = $dom->createElement('href', 'http://maps.google.com/mapfiles/kml/pal2/icon27.png');
$barIconNode->appendChild($barHref);
$barIconstyleNode->appendChild($barIconNode);
$barStyleNode->appendChild($barIconstyleNode);
$docNode->appendChild($barStyleNode);

do {
//	printf("<tr><td>%s UTC</td><td>%s</td><td>%s</td>",$gr['packet_date'],$gr['latitude'],$gr['longitude']);
//	printf("<th><a href=\"http://maps.google.com/?q=%s,%s\">Google Map</a></th></tr>\n",$gr['latitude'],$gr['longitude']);

	$node = $dom->createElement('Placemark');
	$placeNode = $docNode->appendChild($node);

	// Creates an id attribute and assign it the value of id column.
	$placeNode->setAttribute('id', 'placemark' . $gr['packet_date_iso']);


	/* name element */
//	$nameNode = $dom->createElement('name',htmlentities($gr['packet_date_iso']));
//	$placeNode->appendChild($nameNode);

	/* description */
/*
{"latitude":"40.4280827","longitude":"-95.22875654","accuracy":"22.0","altitude":"294.6000061035156","provider":"gps","bearing":"104.5","speed":"2.9154758","time":"2017-07-28T17:43:41.424Z","battlevel":"29","charging":"1","secret":"A5138","deviceid":"99000466520546","subscriberid":"311480142231412"}

*/
	$jd=json_decode($gr['additionalJSON'],true);
	$desc =sprintf("<table><thead><th>Element</th><th>Value</th></thead>");
	$desc.=sprintf("<tr><td>Accuracy</td><td>%d meters</td></tr>",$jd['accuracy']);
	$desc.=sprintf("<tr><td>Altitude</td><td>%d meters</td></tr>",$jd['altitude']);
	$desc.=sprintf("<tr><td>Position Provider</td><td>%s</td></tr>",$jd['provider']);
	$desc.=sprintf("<tr><td>Bearing</td><td>%d&deg;</td></tr>",$jd['bearing']);
	$desc.=sprintf("<tr><td>Speed</td><td>%0.1f</td></tr>",$jd['speed']);
	$desc.=sprintf("<tr><td>Battery Level</td><td>%s%%</td></tr>",$jd['battlevel']);
	$desc.=sprintf("<tr><td>Charging</td><td>%s</td></tr>",$jd['charging']);

	$desc.=sprintf("</table>");

//	$descNode = $dom->createElement('description', print_r(json_decode($gr['additionalJSON']),true));
	$descNode = $dom->createElement('description', htmlentities($desc));
	$placeNode->appendChild($descNode);

	/* style */
//	$styleUrl = $dom->createElement('styleUrl', '#' . $row['type'] . 'Style');
//	$placeNode->appendChild($styleUrl);

	/* add timestamp */
	$timeNode = $dom->createElement('TimeStamp');
	$placeNode->appendChild($timeNode);

	$whenNode = $dom->createElement('when',$gr['packet_date_iso']);
	$timeNode->appendChild($whenNode);

	// Creates a Point element.
	$pointNode = $dom->createElement('Point');
	$placeNode->appendChild($pointNode);

	// Creates a coordinates element and gives it the value of the lng and lat columns from the results.
	$coorStr = $gr['longitude'] . ','  . $gr['latitude'];
	$coorNode = $dom->createElement('coordinates', $coorStr);
	$pointNode->appendChild($coorNode);

} while ( $gr=mysql_fetch_array($gquery,MYSQL_ASSOC) );

$kmlOutput = $dom->saveXML();
header('Content-type: application/vnd.google-earth.kml+xml');
echo $kmlOutput;
?>
