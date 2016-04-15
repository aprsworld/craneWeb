<? 
require_once $_SERVER["DOCUMENT_ROOT"] . "/world_config.php";

$station_id=cleanStationID($_REQUEST["station_id"]);

$db=_open_mysql("worldData");
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


$headers = '<script language="javascript" type="text/javascript" src="js/date.js"></script>
<script language="javascript" type="text/javascript" src="js/timeFunctions.js"></script>
<script language="javascript" type="text/javascript" src="js/cookies.js"></script>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.flot.js"></script>
<script> var station_id = "'.$serial_id.'"; var parent="'.$serial_id.'"; </script>
<script language="javascript" type="text/javascript" src="js/cabu.js"></script>
<script type="text/javascript" src="js/jQueryRotate.2.2.js"></script>';


$head=$title=$deviceInfo["displayName"];
$headline=sprintf("CABU for %s (%s)<br />Current Conditions",$station_id,$serial_id);
require_once "rdHead.php";

$iconHeight = 50;

?>
<div id="wrapper">
	<div class="line">
		<div class="block">
			Report Date:<br />
			<span id="reportDate">
			Loading...<br />
			<span class="emph">Loading...</span></span><br />
			<span><span id="age">?</span><br /></span>
		</div>
	</div>	
	<span>Hover the cursor over the minimum and maximum values to see what time they occurred.</span>
	<table style="border:none; width:90%">
		<tr><td class="cabutable"></td><td class="cabutable"></td><td class="cabutable"><h3>Current</h3></td><td class="cabutable"><h3>Min Today</h3></td><td class="cabutable"><h3>Max Today</h3></td></tr>
		<tr >
			<td class="cabutable"><img id="battImg" src="craneGate.png" alt="battery" height= <? echo $iconHeight;  ?> title="Shows the CABU unit's battery state of charge according to the last packet received" /></td>
			<td class="cabutable"><span class="">Android Battery</span><br /></td>
			<td class="cabutableC" id="and_batt"></td>
			<td class="cabutable" id="minBatt"></td>
			<td class="cabutable" id="maxBatt"></td>
		
		</tr>
		<tr >
			<td class="cabutable"><img id="battImg" src="battery.png" alt="battery" height= <? echo $iconHeight;  ?> title="Shows the CABU unit's battery state of charge according to the last packet received" /></td>
			<td class="cabutable"><span class="">CABU Battery</span><br /></td>
			<td class="cabutableC" id="batt_charge_cabu"></td>
			<td class="cabutable" id="minCabBatt"></td>
			<td class="cabutable" id="maxCabBatt"></td>
		
		</tr>
		<tr>
			<td class="cabutable"><img id="battVehImg" src="car_battery-web.png"  height=<? echo $iconHeight;  ?> alt="battery" title="Shows the crane's battery state of charge according to the last packet received" /></td>
			<td class="cabutable"><span class="">Crane Battery</span><br /></td>
			<td class="cabutableC" id="batt_veh_charge_cabu"></td>
			<td class="cabutable" id="minVehBatt" ></td>
			<td class="cabutable" id="maxVehBatt"></td>
		</tr>

		<tr>
			<td class="cabutable"><img  height=<? echo $iconHeight;  ?> src="images/lightning-web.png" alt="Lightning" title="Charger Current" /></td>
			<td class="cabutable"><span class="">Charger Current:</span></td>
			
			<td class="cabutableC" id="charger"> <span id="charger_current" class="emph">...</span><br>amps</td>
			<td class="cabutable" id="minCharger"> <span id="min_charger_current" class="emph">...</span><br>amps</td>
			<td class="cabutable" id="maxCharger"> <span id="max_charger_current" class="emph">...</span><br>amps</td>
		</tr>
		<tr>
			<td class="cabutable"><img  height=<? echo $iconHeight;  ?> src="images/lightning-web.png" alt="Lightning" title="Charger Current" /></td>

			<td class="cabutable"><span class="">Load Current:</span></td>
			
			<td class="cabutableC" id="load"> <span id="load_current" class="emph">...</span><br>amps</td>
			<td class="cabutable" id="minLoad"> <span id="min_load_current" class="emph">...</span><br>amps</td>
			<td class="cabutable" id="maxLoad"> <span id="max_load_current" class="emph">...</span><br>amps</td>
		</tr>
		<tr>
			<td class="cabutable"><img height=<? echo $iconHeight;  ?> src="images/thermometer-web.png" alt="Lightning" title="Charger Current" /></td>
			<td class="cabutable"><span class="">External Temperature:</span></td>
			<td class="cabutableC" id="extTemp"> <span id="external_temp" class="emph">...</span></td>
			<td class="cabutable" id="minExtTemp"> <span id="min_external_temp" class="emph">...</span></td>
			<td class="cabutable" id="maxExtTemp"> <span id="max_external_temp" class="emph">...</span></td>
		</tr>
		<tr>
			<td class="cabutable"><img height=<? echo $iconHeight;  ?> src="images/thermometer-web.png" alt="Lightning" title="Charger Current" /></td>
			<td class="cabutable"><span class="">Internal Temperature:</span></td>
			<td class="cabutableC" id="intTemp"> <span id="internal_temp" class="emph">...</span></td>
			<td class="cabutable" id="minIntTemp"> <span id="min_internal_temp" class="emph">...</span></td>
			<td class="cabutable" id="maxIntTemp"> <span id="max_internal_temp" class="emph">...</span></td>
		</tr>
	</table>
	

	
<!--
	<div id="flot" style="width: 100%;height: 200px;font-size: 14px;line-height: 1em;overflow: visible; overflow-x: hidden;"></div>
-->	
<? if ($cabu && false){ ?>
	<br><br>
	<span ><a href="cabu.php?serial=<? echo $r['serialNumber']; ?>&station_id=<? echo $station_id; ?>" style="display: inline; padding-left: 10px; padding-right: 10px;">View CABU</a></span>

<?}?>	
	<button id="button1" name="unitTog" onclick="toggleUnit()">Switch to &deg;F</button>
	<br><br>
	<span class="small">Powered by an APRS World, LLC solution.</span>
</div>
</body>
</html>
