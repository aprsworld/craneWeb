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
<script language="javascript" type="text/javascript" src="js/rdlogger.js"></script><script type="text/javascript" src="js/jQueryRotate.2.2.js"></script>';

$head=$title=$deviceInfo["displayName"];
$headline=sprintf("Crane Wind Logger %s<br />Current Conditions",$station_id);
require_once "rdHead.php";

?>
<div id="wrapper">
	<div id="connection_warn" style="text-align: center; width: 800px; margin-right: auto; margin-left: auto; background-color: orange; color: white; display: none;" onclick="hideWarn()">
		<h1>No Response From Server. Please check to make sure you are still connected to the internet</h1>
	</div>
	<? if($cert != null){ ?>
	<object data="<? echo "/" . $cert; ?>" type="application/pdf" height="700px" width="100%">
	   <p><b>Example fallback content</b>: This browser does not support PDFs. Please download the PDF to view it: <a href="<? echo "/" . $cert; ?>">Download PDF</a>.</p>
	</object>
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
