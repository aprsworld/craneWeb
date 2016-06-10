<? 
require_once $_SERVER["DOCUMENT_ROOT"] . "/world_config.php";

$station_id=cleanStationID($_REQUEST["station_id"]);
$db=_open_mysql("worldData");

/* if not public, then we need to be authorized */
if ( 0==authPublic($station_id,$db) ) {
        require $_SERVER["DOCUMENT_ROOT"] . "/auth.php";
}

/* Determine our title and display name */
$deviceInfo=getDeviceInfo($station_id,$db);

/* Check if a CABU unit is assosiated */
$cabu = false;
$sql=sprintf("SELECT * FROM deviceInfo WHERE parent='%s'",$station_id);
$query1=mysql_query($sql,$db);
$rezultz1234=mysql_fetch_array($query1,MYSQL_ASSOC);
if(NULL != $rezultz1234){
	
	$cabu=true;
}
//print_r($r);

$headers = '<script language="javascript" type="text/javascript" src="js/date.js"></script>
<script language="javascript" type="text/javascript" src="js/timeFunctions.js"></script>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.flot.js"></script>
<script> var station_id = "'.$station_id.'"; </script>
<script language="javascript" type="text/javascript" src="js/rdlogger.js"></script><script type="text/javascript" src="js/jQueryRotate.2.2.js"></script>
<script src="https://use.fontawesome.com/2900603c7e.js"></script>';

$head=$title=$deviceInfo["displayName"];
$headline=sprintf("Crane Wind Logger %s<br />Current Conditions",$station_id);
require_once "rdHead.php";

$db =_open_mysql("calibration");
$anemometersql=sprintf("SELECT calibrationAnemometer.* FROM calibrationAnemometer JOIN whereUsed WHERE whereUsed.sensorSerialNumber = calibrationAnemometer.serialnumber AND whereUsed.stationSerialNumber = '%s'", $station_id);
$query2=mysql_query($anemometersql,$db);
$anemometerDetail=mysql_fetch_array($query2,MYSQL_ASSOC);

?>
<div id="wrapper">
	<div id="connection_warn" style="text-align: center; width: 800px; margin-right: auto; margin-left: auto; background-color: orange; color: white; display: none;" onclick="hideWarn()">
		<h1>No Response From Server. Please check to make sure you are still connected to the internet</h1>
	</div>
	<div id="certData">
		<h1>Anemometer Calibration Certificate Data</h1>
		
		<div class="certBlock">
			<h2> Serial Number </h2>
			<p> <? echo $anemometerDetail['serialNumber']; ?></p>
			<h2> Manufacturer </h2>
			<p><? echo $anemometerDetail['manufacturer']; ?> </p>
			<h2> Certificate Number </h2>
			<p> <? echo $anemometerDetail['calibrationCertificateNumber']; ?></p>
		</div>
		<div class="certBlock">
			<h2> Type </h2>
			<p> <? echo $anemometerDetail['description']; ?></p>
			
			<h2> mMS Value</h2>
			<p> <? echo $anemometerDetail['mMS']; ?></p>
			<h2> bMS Value</h2>
			<p><? echo $anemometerDetail['bMS']; ?> </p>
		</div>
		<div class="certBlock">
			<h2> Certificate Date </h2>
			<p> <? echo $anemometerDetail['calibrationCertificateDate']; ?></p>
			<h2> In Service Date </h2>
			<p> <? echo $anemometerDetail['inServiceDate']; ?></p>
			<h2> Expiration Date </h2>
			<p><? echo $anemometerDetail['expiresDate']; ?> </p>
		</div>
	</div>
	
	
	<? if($cert != null){ ?>
		<h1> Certificate Scan </h1>

	  <a class="dlBtn onWhiteLink" href="<? echo "/" . $cert; ?>" download><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Download PDF</a>
	<? } else { ?>
	<h2> Sorry no certificate found for this station. </h2>
	<? }?>
<? //if ($cabu == true){ ?>
	<!--<br><br>
	<span ><a href="cabu.php?serial=<? //echo $r['serialNumber']; ?>&station_id=<? //echo $station_id; ?>" style="display: inline; padding-left: 10px; padding-right: 10px;">View CABU</a></span>
-->
<?//}?>
	<br><br>
	<span class="small">Powered by an APRS World, LLC solution.</span>
</div>
</body>
</html>
