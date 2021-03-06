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
	<div class="line">
		<div class="vert-align block">
			<span id="reportDate">Report Date:<br />
			Loading...<br />
			<span class="emph">Loading...</span></span><br />
			<span><span id="age">?</span><br /></span>
		</div>
		<div title="Click here to view Wind Speed Graph" class="vert-align speedBlock">
			<span>Speed:</span><br />
			<span id="windSpeed" class="bignum">...</span><br />
			<span class="unit">m/s</span>
		</div>
		
		<div title="Click here to view Wind Speed Graph" class="vert-align speedBlock">
			<span>Gust:</span><br />
			<span id="windGust" class="bignum">...</span><br />
			<span class="unit">m/s</span>
		</div>
		<div id="maxGust" title="Click here to view Wind Speed Graph" class="vert-align speedBlock">
			<span class="emph">Max Gust</span><br />
			<span>Last Hour:<span class="emph">...</span>MPH</span><br />
			<span>Today:<span class="emph">...</span>MPH</span>
		</div>
	</div>
	<div class="line">
		<div title="Click here to view Battery Graph" class="vert-align-bottom battBlock" id="image" >
			<img id="battImg" src="res/battery.png" alt="battery" title="Shows the data logger's battery state of charge according to the last packet received" />
     			<h2 id="batt_charge">Loading</h2>
		</div>
		<div title="Click here to view Battery Graph" class="vert-align-bottom battBlock">
			<span class="emph">Battery</span><br />
			<span>Min Today:<span id="minBatt" class="emph">...%</span></span><br />
			<span>Max Today:<span id="maxBatt" class="emph">...%</span></span>
		</div>
		<div class="vert-align-bottom arrowBlock">
			<img id="arrow" src="res/arrow.png" alt="" title="Arrow points towards direction wind is coming from." /><br />
			<span class="emph">Wind Direction</span><br />
			<span class="small">Relative to boom</span><br />
			<span class="small">(Up is direction boom is pointing)</span>
		</div>
	</div>
	<div  class="line" id="flot" style="width: 100%;height: 200px;font-size: 14px;line-height: 1em;overflow: visible; overflow-x: hidden;"></div>
	<button id="button" name="show">Show Status</button>
	<button id="button1" name="unitTog">Change to m/s</button>
	<button id="graphToggle"> Toggle Battery Graph </button>
	<div id="status" class="line">	
		<div class="vert-align block">		
			<span class="emph">Status Date:</span><br />
			<span id="statusDate">Loading...<br />
			Received Loading... ago</span><br />
			<span class="small">(hours:minutes:seconds)</span>
			
		</div>
		<div class="vert-align SDBlock" id="SDImage">
			<img src="res/SDCard.png" alt="" />
     			<h2 id="cardStatus">Loading...</h2>
		</div>
		<div class="vert-align block">		
			<span>Crane Wind Logger Uptime:</span><br />
			<span class="emph" id="uptime">Loading...</span><br />
			<span>Internal Memory Percent Full:</span><br />
			<span class="emph" id="memPercent">Loading...</span><br />
			<span>Approx. Days Memory Left: </span><br />
			<span class="emph" id="memDaysRemain">Loading...</span><br />
		</div>
	</div>

	
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
