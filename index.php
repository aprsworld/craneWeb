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
$query=mysql_query($sql,$db);
$r=mysql_fetch_array($query,MYSQL_ASSOC);
if(NULL != $r){
	$cabu=true;
}
//print_r($r);

$headers = '<script language="javascript" type="text/javascript" src="http://mybergey.aprsworld.com/data/date.js"></script>
<script language="javascript" type="text/javascript" src="http://ian.aprsworld.com/javascript/timeFunctions.js"></script>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
<script language="javascript" type="text/javascript" src="http://mybergey.aprsworld.com/data/jquery.flot.js"></script>
<script> var station_id = "'.$station_id.'"; </script>
<script language="javascript" type="text/javascript" src="js/rdlogger.js"></script><script type="text/javascript" src="http://ian.aprsworld.com/data/jQueryRotate.2.2.js"></script>';


$head=$title=$deviceInfo["displayName"];
$headline=sprintf("Crane Wind Logger %s<br />Current Conditions",$station_id);
require_once "rdHead.php";
?>
<div id="wrapper">
	<div id="connection_warn" style="text-align: center; width: 800px; margin-right: auto; margin-left: auto; background-color: orange; color: white; display: none;" onclick="hideWarn()">
		<h1>No Response From Server. Please check to make sure you are still connected to the internet</h1>
	</div>
	<div class="line">
		<div class="block">
			<span id="reportDate">Report Date:<br />
			Loading...<br />
			<span class="emph">Loading...</span></span><br />
			<span><span id="age">?</span><br /></span>
		</div>
		<div class="speedBlock">
			<span>Speed:</span><br />
			<span id="windSpeed" class="bignum">...</span><br />
			<span class="unit">m/s</span>
		</div>
		
		<div class="speedBlock">
			<span>Gust:</span><br />
			<span id="windGust" class="bignum">...</span><br />
			<span class="unit">m/s</span>
		</div>
		<div id="maxGust" class="speedBlock">
			<span class="emph">Max Gust</span><br />
			<span>Last Hour:<span class="emph">...</span>MPH</span><br />
			<span>Today:<span class="emph">...</span>MPH</span>
		</div>
	</div>
	<div class="line">
		<div class="battBlock" id="image" >
			<img id="battImg" src="battery.png" alt="battery" title="Shows the data logger's battery state of charge according to the last packet received" />
     			<h2 id="batt_charge">Loading</h2>
		</div>
		<div class="battBlock">
			<span class="emph">Battery</span><br />
			<span>Min Today:<span id="minBatt" class="emph">...%</span></span><br />
			<span>Max Today:<span id="maxBatt" class="emph">...%</span></span>
		</div>
		<div class="arrowBlock">
			<img id="arrow" src="arrow.png" alt="" title="Arrow points towards direction wind is coming from." /><br />
			<span class="emph">Wind Direction</span><br />
			<span class="small">Relative to boom</span><br />
			<span class="small">(Up is direction boom is pointing)</span>
		</div>
	</div>
	<div id="flot" style="width: 100%;height: 200px;font-size: 14px;line-height: 1em;overflow: visible; overflow-x: hidden;"></div>
	<div id="status" class="line">	
		<div class="block">		
			<span class="emph">Status Date:</span><br />
			<span id="statusDate">Loading...<br />
			Received Loading... ago</span><br />
			<span class="small">(hours:minutes:seconds)</span>
		</div>
		<div class="SDBlock" id="SDImage">
			<img src="SDCard.png" alt="" />
     			<h2 id="cardStatus">Loading...</h2>
		</div>
		<div class="block">		
			<span>Crane Wind<br />Logger Uptime:</span><br />
			<span class="emph" id="uptime">Loading...</span> <br />minutes
		</div>
	</div>
	<button id="button" name="show" onclick="showStatus()">Show Status</button>
	<button id="button1" name="unitTog" onclick="toggleUnit()">Change speed unit</button>
<? if ($cabu && false){ ?>
	<br><br>
	<span ><a href="cabu.php?serial=<? echo $r['serialNumber']; ?>&station_id=<? echo $station_id; ?>" style="display: inline; padding-left: 10px; padding-right: 10px;">View CABU</a></span>

<?}?>
	<br><br>
	<span class="small">Powered by an APRS World, LLC solution.</span>
</div>
</body>
</html>
