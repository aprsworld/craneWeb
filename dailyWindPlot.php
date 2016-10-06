<?php
include $_SERVER["DOCUMENT_ROOT"] . "/jpgraph/src/jpgraph.php";
include $_SERVER["DOCUMENT_ROOT"] . "/jpgraph/src/jpgraph_line.php";
include $_SERVER["DOCUMENT_ROOT"] . "/jpgraph/src/jpgraph_scatter.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/world_config.php";
require $_SERVER["DOCUMENT_ROOT"] . "/datamart/geoFunctions.php";

$station_id=$_REQUEST["station_id"];

$db=_open_mysql("worldData");

/* if not public, then we need to be authorized */
if ( 0==authPublic($station_id,$db) ) {
        require $_SERVER["DOCUMENT_ROOT"] . "/auth.php";
		if(authSerialNumber($_SESSION['username'],$station_id,$db) < 0){
			$docRoot = $_SERVER["DOCUMENT_ROOT"];

			header("Location:/login.php", true);
		}
}


$week=$_REQUEST["week"];
if ( $week < 0 || $week > 53 )
	$week=NULL;

$day=$_REQUEST["day"];
if ( $day < 1 || $day > 366 )
	$day=NULL;

$year=$_REQUEST["year"];
if ( $year < 2000 || $year > 2099 )
	$year=date('Y');

$scale[0]=$scale[1]=$scale[2]=1.0;
if ( is_numeric($_REQUEST["scale0"]) )
	$scale[0]=$_REQUEST["scale0"];
if ( is_numeric($_REQUEST["scale1"]) )
	$scale[1]=$_REQUEST["scale1"];
if ( is_numeric($_REQUEST["scale2"]) )
	$scale[2]=$_REQUEST["scale2"];


if ( "gust" == $_REQUEST["mode"] ) {
	$sql=sprintf("SELECT UNIX_TIMESTAMP(LEFT(DATE_SUB(packet_date,INTERVAL 5 HOUR),10)) AS packet_time, MAX(windGust) AS pulseGust0Max FROM rdLoggerCell_%s GROUP BY DAYOFYEAR(DATE_SUB(packet_date,INTERVAL 5 HOUR)) ORDER BY YEAR(DATE_SUB(packet_date,INTERVAL 5 HOUR)), DAYOFYEAR(DATE_SUB(packet_date,INTERVAL 5 HOUR))",$station_id);
	$title="Daily Peak - ";
} else {
	$sql=sprintf("SELECT UNIX_TIMESTAMP(LEFT(DATE_SUB(packet_date,INTERVAL 5 HOUR),10)) AS packet_time, AVG(windSpeed) AS pulseCurrent0Average FROM rdLoggerCell_%s GROUP BY DAYOFYEAR(DATE_SUB(packet_date,INTERVAL 5 HOUR)) ORDER BY YEAR(DATE_SUB(packet_date,INTERVAL 5 HOUR)), DAYOFYEAR(DATE_SUB(packet_date,INTERVAL 5 HOUR))",$station_id);
	$title="Daily Average - ";
}

if ( isset($_REQUEST['title']) ) {
	$title=$_REQUEST['title'];
}


$query=mysql_query($sql,$db);



/* get field names for everything after packet_time */
$fn=array();
for ( $i=1 ; $i<mysql_num_fields($query) ; $i++ ) {
	$fn[$i-1]=mysql_field_name($query,$i);
}


$max=-400000;
$min=400000;
$n=0;
while ( $r=mysql_fetch_array($query) ) {
	$datax[$n]=$r["packet_time"];

	for ( $i=0 ; $i<count($fn) ; $i++ ) {
		$datay[$i][$n]=$r[$i+1]*$scale[$i];

		if ( $datay[$i][$n] > $max ) 
			$max=$datay[$i][$n];
		if ( $datay[$i][$n] < $min ) 
			$min=$datay[$i][$n];
	}

	$n++;

}
//header("Content-type: text/plain"); print_r($datay); die();


function TimeCallback($aVal) {
	return Date('Y-m-d',$aVal);
}

$width=$_REQUEST["width"];
$height=$_REQUEST["height"];
if ( $width < 100 || $width > 10000 ) 
	$width=700;
if ( $height < 50 || $height > 5000 ) 
	$height=450;

$nomargin=$_REQUEST["nomargin"];
if ( "1" == $nomargin ) {
	$nomargin=true;	
	$noxaxis=true;
} else {
	$nomargin=false;
	$noxaxis=false;
}

// Setup the basic graph
$graph = new Graph($width,$height);
if ( ! $nomargin ) {
	$graph->SetMargin(50,25,30,75);	
} else {
	$graph->SetMargin(50,25,10,10);	
}

/* retrieve our columns from the database */
/* column labels and units */
$sql=sprintf("SELECT * FROM wind2g_labels WHERE serialNumber='%s'",$station_id);
$query=mysql_query($sql,$db);
$r=mysql_fetch_array($query);

/* allow override of units */
$u[0]=$r["pulse0U"];
$u[1]=$r["pulse1U"];
$u[2]=$r["pulse2U"];
if ( isset($_REQUEST["unit0"]) ) $u[0]=$_REQUEST["unit0"];
if ( isset($_REQUEST["unit1"]) ) $u[1]=$_REQUEST["unit1"];
if ( isset($_REQUEST["unit2"]) ) $u[2]=$_REQUEST["unit2"];


if ( "" != $r["pulse0L"] ) 
	$title .= "Red: " . $r["pulse0L"] . " (" . $u[0] . ")\n";
if ( "" != $r["pulse1L"] ) 
	$title .= "Blue: " . $r["pulse1L"] . " (" . $u[1] . ")\n";
if ( "" != $r["pulse2L"] ) 
	$title .= "Green: " . $r["pulse2L"] . " (" . $u[2] . ")\n";

$graph->title->Set($title);
$graph->SetAlphaBlending();

// Setup a manual x-scale (We leave the sentinels for the
// Y-axis at 0 which will then autoscale the Y-axis.)
// We could also use autoscaling for the x-axis but then it
// probably will start a little bit earlier than the first value
// to make the first value an even number as it sees the timestamp
// as an normal integer value.
if ( 0 == $max )
	$max=1;

$max=1.1*$max;
if ( $min < 0 )
	$min=1.1*$min;
else
	$min=0.8*$min;

$min=0.0;
//$max=50000;

$graph->SetScale("intlin",$min,$max,$datax[0],$datax[$n-1]);

if ( ! $noxaxis ) {
	// Setup the x-axis with a format callback to convert the timestamp
	// to a user readable time
	$graph->xaxis->SetTextLabelInterval(1); 
	$graph->xaxis->SetTextTickInterval(1,2);
	$graph->xaxis->SetLabelFormatCallback('TimeCallback');
	$graph->xaxis->SetLabelAngle(90);
} else {
	$graph->xaxis->Hide();
}
//$graph->yaxis->SetTitle(sprintf("MPH"),'middle'); 

/* add the series */
$colors=array("red","blue","green","brown");


for ( $i=0 ; $i<count($fn) ; $i++ ) {
	$lp[$i] = new LinePlot($datay[$i],$datax);
	$lp[$i]->SetColor($colors[$i]);
	$graph->Add($lp[$i]);
}


/* draw the thing */
$graph->Stroke();
?>
