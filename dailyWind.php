<?
$station_id=$_REQUEST["station_id"];

require_once $_SERVER["DOCUMENT_ROOT"] . "/world_config.php";
$db=_open_mysql("worldData");

/* if not public, then we need to be authorized */
if ( 0==authPublic($station_id,$db) ) {
	require $_SERVER["DOCUMENT_ROOT"] . "/auth.php";
	if(authSerialNumber($_SESSION['username'],$station_id,$db) < 0){
		$docRoot = $_SERVER["DOCUMENT_ROOT"];

		header("Location:/login.php", true);
	}
}
	
$title=$headline="Daily Wind Summary";
require $_SERVER["DOCUMENT_ROOT"] . "/world_head.php";


/* allow scaling */
$scale[0]=$scale[1]=$scale[2]=1.0;
if ( is_numeric($_REQUEST["scale0"]) )
	$scale[0]=$_REQUEST["scale0"];
if ( is_numeric($_REQUEST["scale1"]) )
	$scale[1]=$_REQUEST["scale1"];
if ( is_numeric($_REQUEST["scale2"]) )
	$scale[2]=$_REQUEST["scale2"];


$db=open_mysql("worldData");
$tzOffset=getTimeZoneOffsetHours($station_id,$db);

/* allow override of units */
$u[0]=$l["pulse0U"];
$u[1]=$l["pulse1U"];
$u[2]=$l["pulse2U"];
if ( isset($_REQUEST["unit0"]) ) $u[0]=$_REQUEST["unit0"];
if ( isset($_REQUEST["unit1"]) ) $u[1]=$_REQUEST["unit1"];
if ( isset($_REQUEST["unit2"]) ) $u[2]=$_REQUEST["unit2"];



$sql=sprintf("SELECT DAYOFYEAR(DATE_SUB(packet_date,INTERVAL 5 HOUR)) AS dayOfYear, YEAR(DATE_SUB(packet_date,INTERVAL 5 HOUR)) AS year, LEFT(DATE_SUB(packet_date,INTERVAL 5 HOUR),10) AS day, MAX(windGust) AS pulseGust0Max, AVG(windSpeed) AS pulseCurrent0Average FROM rdLoggerCell_%s GROUP BY DAYOFYEAR(DATE_SUB(packet_date,INTERVAL 5 HOUR)) ORDER BY YEAR(DATE_SUB(packet_date,INTERVAL 5 HOUR)) DESC, DAYOFYEAR(DATE_SUB(packet_date,INTERVAL 5 HOUR)) DESC",$station_id);

$query=mysql_query($sql,$db);

$cols=0;

if ( 0 == strlen($l['pulse0L']) )
	$l["pulse0L"]="Anemometer";
if ( 0 == strlen($u[0]) )
	$u[0]="m/s";
?>
<img src="dailyWindPlot.php?title=Daily+Average+(<? echo $u[0]; ?>)&amp;<? echo $_SERVER["QUERY_STRING"]; ?>">
<img src="dailyWindPlot.php?title=Daily+Gust+(<? echo $u[0]; ?>)&amp;mode=gust&amp;<? echo $_SERVER["QUERY_STRING"]; ?>">
<hr />
<table border="1">
	<thead>
		<th>Date</th>
		<? if ( "" != $l["pulse0L"] ) { printf("<th>%s (%s)<br />AVG / MAX</th>",$l["pulse0L"],$u[0]); $cols++; } ?>
		<? if ( "" != $l["pulse1L"] ) { printf("<th>%s<br />(%s)</th>",$l["pulse1L"],$u[1]); $cols++; } ?>
		<? if ( "" != $l["pulse2L"] ) { printf("<th>%s<br />(%s)</th>",$l["pulse2L"],$u[2]); $cols++; } ?>
		<th>Reports</th>
	</thead>
	<tbody>
<?
$lastmonth="";
while ( $r=mysql_fetch_array($query) ) {
	$month=substr($r["day"],0,7);
	if ( $month != $lastmonth ) {
		printf("\t\t<tr><th colspan=\"%d\"><h3 style=\"color: white\">%s</h3></th></tr>",$cols+2,$month);
		$lastmonth=$month;
	}
	printf("\t\t<tr style=\"text-align: right\">\n");
	printf("\t\t\t<td>%s</td>\n",$r["day"]);

	if (""!=$l["pulse0L"])  {
		printf("\t\t\t<td>%s / %s</td>\n",
			number_format($r["pulseCurrent0Average"]*$scale[0],2),
			number_format($r["pulseGust0Max"]*$scale[0],1)
		);
	}
	if (""!=$l["pulse1L"])  {
		printf("\t\t\t<td>%s / %s</td>\n",
			number_format($r["pulseCurrent1Average"]*$scale[1],2),
			number_format($r["pulseGust1Max"]*$scale[1],1)
		);
	}
	if (""!=$l["pulse2L"])  {
		printf("\t\t\t<td>%s / %s</td>\n",
			number_format($r["pulseCurrent2Average"]*$scale[2],2),
			number_format($r["pulseGust2Max"]*$scale[2],1)
		);
	}

	printf("\t\t\t<td>");
	printf("<a href=\"dayDetail.php?day=%s&amp;station_id=%s\">Web Table(Minute Data)</a> ",$r["day"],$station_id);
	printf("<a href=\"hourDetail.php?day=%s&amp;station_id=%s\">Web Table (Hourly Data)</a> ",$r["day"],$station_id);
	printf("<a href=\"dayDetailCSV.php?mode=text&amp;day=%s&amp;station_id=%s\">Text Table</a> ",$r["day"],$station_id);
	printf("<a href=\"dayDetailCSV.php?mode=csv&amp;day=%s&amp;station_id=%s\">CSV Table</a> ",$r["day"],$station_id);
	printf("</td>");

	
	printf("\t\t</tr>\n");
}
?>
	</tbody>
</table>

<?
require $_SERVER["DOCUMENT_ROOT"] . "/world_foot.php";
?>
