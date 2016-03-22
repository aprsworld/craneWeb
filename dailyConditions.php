<?
$station_id=$_REQUEST["station_id"];
$headline=sprintf("Crane Wind Logger %s<br />Daily Conditions",$station_id);

require_once $_SERVER["DOCUMENT_ROOT"] . "/world_config.php";
$station_id=cleanStationID($_REQUEST["station_id"]);
$db=_open_mysql("worldData");

/* if not public, then we need to be authorized */
if ( 0==authPublic($station_id,$db) ) {
        require $_SERVER["DOCUMENT_ROOT"] . "/auth.php";
}

$deviceInfo=getDeviceInfo($station_id,$db);
$head=$title=$deviceInfo["displayName"];
$tzOffset=getTimeZoneOffsetHours($station_id,$db);

$headers = '<script language="javascript" type="text/javascript" src="js/date.js"></script>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.flot.js"></script>
<script> var station_id = "'.$station_id.'"; </script>
<script language="javascript" type="text/javascript" src="js/rdloggerDaily.js"></script><script type="text/javascript" src="js/jQueryRotate.2.2.js"></script>';

require_once "rdHead.php";

?>
<div id="wrapper">
<?


$expr=24;
$type="HOUR";

if( isset( $_REQUEST["expr"] ) ){
	$expr = $_REQUEST["expr"];
}
if( isset( $_REQUEST["type"] ) ){
	$type = $_REQUEST["type"];
}

$db=_open_mysql("worldData");

$sql=sprintf("SELECT DAYOFYEAR(DATE_ADD(packet_date,INTERVAL %s HOUR)) AS dayOfYear, YEAR(DATE_ADD(packet_date,INTERVAL %s HOUR)) AS year, LEFT(DATE_ADD(packet_date,INTERVAL %s HOUR),10) AS day, UNIX_TIMESTAMP(LEFT(DATE_ADD(packet_date,INTERVAL %s HOUR),10)) AS x, MAX(windGust) AS pulseGust0Max, ROUND(AVG(windSpeed),2) AS pulseCurrent0Average FROM rdLoggerCell_%s GROUP BY DATE(DATE_ADD(packet_date,INTERVAL %s HOUR)) ORDER BY YEAR(DATE_ADD(packet_date,INTERVAL %s HOUR)) DESC, DAYOFYEAR(DATE_ADD(packet_date,INTERVAL %s HOUR)) DESC",$tzOffset,$tzOffset,$tzOffset,$tzOffset,$station_id,$tzOffset,$tzOffset,$tzOffset);
//echo $sql;
$query=mysql_query($sql,$db);
$i=0;
?>
<button id="unitButton" onclick="toggleUnit()">Change Speed Unit</button>
<table>
<tr>
	<td colspan="4" style="text-align: center;">
		<img style="float: left;" src="images/prevArrow.png" title="Previous Month" onclick="prevMonth()" id="prevArr" />
		<img style="float: right;" src="images/nextArrow.png" title="Next Month" onclick="nextMonth()" id="nextArr" />
		<h4>Daily Average / Maximum (<span id="speedUnit">MPH</span>) <span id="yearMonth"></span></h4>
		<div id="flot" style="width: 100%;height: 425px;font-size: 14px;line-height: 1em;overflow: visible; overflow-x: hidden;"></div>
	</td>
</tr>
<tr><th>Date</th><th>AVG</th><th>MAX</th><th>Reports</th></tr>
<?
$month="";
$flotScriptSpeed="[";
$flotScriptGust="[";
$maxY=0;
while($r=mysql_fetch_array($query,MYSQL_ASSOC)){
	if($month!=substr($r["day"],0,7)){
		$month=substr($r["day"],0,7);		
		printf("<tr><th colspan=4>%s</th></tr>",$month);
		
	}
	printf("<tr><td>%s</td><td>%s</td><td>%s</td>",$r["day"],($r["pulseCurrent0Average"]." m/s <br><br>".round($r["pulseCurrent0Average"]*2.23,2)." MPH"),$r["pulseGust0Max"]." m/s <br><br>".round($r["pulseGust0Max"]*2.23,2)." MPH");
	printf("\t\t\t<td>");
	printf("<a href=\"dayDetail.php?day=%s&amp;station_id=%s\">Web Table(Minute Data)</a> ",$r["day"],$station_id);
	printf("<a href=\"tenMinuteDetail.php?day=%s&amp;station_id=%s\">Web Table (10 Minute Data)</a> ",$r["day"],$station_id);
	printf("<a href=\"hourDetail.php?day=%s&amp;station_id=%s\">Web Table (Hourly Data)</a> ",$r["day"],$station_id);
	printf("<a href=\"dayDetailCSV.php?mode=text&amp;day=%s&amp;station_id=%s\">Text Table</a> ",$r["day"],$station_id);
	printf("<a href=\"dayDetailCSV.php?mode=csv&amp;day=%s&amp;station_id=%s\">CSV Table</a> ",$r["day"],$station_id);
	printf("</td>");	
	printf("</tr>");
	if( $maxY < $r["pulseGust0Max"] ) $maxY = $r["pulseGust0Max"];
	$flotScriptSpeed.=sprintf("[%s,%s],",$r["x"],$r["pulseCurrent0Average"]);
	$flotScriptGust.=sprintf("[%s,%s],",$r["x"],$r["pulseGust0Max"]);
}
$flotScriptSpeed=substr($flotScriptSpeed,0,-1)."]";
$flotScriptGust=substr($flotScriptGust,0,-1)."]";
?>
</table>
<br><br>
<span class="small">Powered by an APRS World, LLC solution.</span>
</div>
<script>
$(document).ready(function(){
	yMax = <? echo $maxY; ?>;
	speedChart = <? echo $flotScriptSpeed; ?>;
	gustChart = <? echo $flotScriptGust; ?>;
	loadSpeedChart();
	console.log("ok");
	nextMonth();
	prevMonth();	
	
});

</script>
</body>
</html>
