<?
session_start();
$cabu = false;
$sql=sprintf("SELECT * FROM deviceInfo WHERE parent='%s'",$station_id);
$query=mysql_query($sql,$db);
$r=mysql_fetch_array($query,MYSQL_ASSOC);
if(NULL != $r){
	$cabu=true;
}
$docRoot = $_SERVER["DOCUMENT_ROOT"];
$loggedIn=false;

function getTitle($serialNumber,$db) {
	if ( FALSE != strpos($serialNumber,'_',1) ) {
		$serialNumber=substr($serialNumber,0,strpos($serialNumber,'_',1));
	}


	$sql=sprintf("SELECT deviceInfo.displayName FROM deviceInfo WHERE serialNumber='%s'",mysql_real_escape_string($serialNumber));
	$query=mysql_query($sql,$db);

	if ( 0 == mysql_num_rows($query) ) 
		return null;

	return mysql_fetch_array($query,MYSQL_ASSOC);
}

function striptags ($str) {

 return trim(strip_tags(str_replace('<', ' <', $str)));

}

//type is the type of unit we are searching for (i.e. #40HC). Serial is the serial number of the unit 
function getScan ($folder, $type, $serial){
	
		$pattern = $type . "_" . $serial . "*";
		$fileArray = glob($folder . '/' . $pattern);
		return $fileArray;

}



/*Check if there is a cert for the anemometer this unit is using */
function hasCert ($stationID){
	$db=_open_mysql("calibration");
	$sql=sprintf("SELECT calibrationAnemometer.serialnumber as 'sn' FROM calibrationAnemometer JOIN whereUsed WHERE whereUsed.sensorSerialNumber = calibrationAnemometer.serialnumber AND whereUsed.stationSerialNumber = '%s'",$stationID);
	
	$query=mysql_query($sql,$db);
	
	if ( 0 == mysql_num_rows($query) ){
		return null;	
	}
	else{
		$r=mysql_fetch_array($query,MYSQL_ASSOC);
		if(file_exists ($_SERVER["DOCUMENT_ROOT"] . "/calCerts/40HC_" . $r['sn'] . ".pdf" )){
			
			return "calCerts/40HC_" . $r['sn'] . ".pdf";	
		}
		else{
			return "noFile";	
		}
	}
}



$deviceInfo=getTitle($station_id,$db);
//if we are logged in and viewing a private page, we want the display name to be non-generic
if($validLogin){
	$head = $deviceInfo["displayName"];
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="world.css" type="text/css"/>
<link rel="stylesheet" href="signin.css" type="text/css"/>
<link rel="icon" type="image/gif" href="http://data.aprsworld.com/favicon.gif">


<script type="text/javascript" src="js/excanvas.min.js"></script>
<? echo $headers ?>
<title><? echo $head ?> | <? echo striptags($headline, ''); ?></title>
</head>
<body>
<div id=header>


<div id="signInBox" style="text-align: right;">
<?
$aMod="width:200px; background-color:white; color: black;text-align: right;";



if ( $_SESSION["username"] ) {
	printf("Logged in as:<br><span style=\"font-weight: bold;\">%s</span>",$_SESSION["username"]);
?>

<a class="signInA" style="<? printf("%s",$aMod); ?>" href="/logout.php">Logout</a>
<a class="signInA" style="<? printf("%s",$aMod); ?>" href="/account/">My Sites</a>
<a class="signInA" style="<? printf("%s",$aMod); ?>" href="/account/userSettings.php">Account Settings</a>
<?
} else {
?>

<form method="post" action="/account/" style="padding-top: 10px;">
<span>Log in:</span><br>
username: <input type="text" name="username" size="12" value="(username)" onclick="this.value=''" /><br>
password: <input type="password" name="password" size="12" /><br>
<input type="submit" value="login">
</form>

<?
}?>
</div>


	<img id="logo" src="images/logo_250.png" title="APRS World, LLC, Logo" alt="Logo" />
	<h2><? echo $head ?><br><? echo $headline ?></h2>

	
</div>
<? $linkCount = 2;
		$cert = hasCert($station_id);

	if($cabu==true){
		$linkCount++;	
	}
	if($cert != null){
		$linkCount++;	
	}
	
	?>	
<div id="nav">
	<ul>
		<li class="navlink-<? echo $linkCount?> navLink"><a href="index.php?station_id=<?echo $station_id;?>">Current Conditions</a></li>
		<li class="navlink-<? echo $linkCount?> navLink"><a href="dailyConditions.php?station_id=<?echo $station_id;?>">Daily Conditions</a></li>
		<?php if($cabu==true){ ?>
		
		<li class="navlink-<? echo $linkCount?> navLink"><a href="cabu.php?station_id=<?echo $station_id;?>">Cabu Information</a></li>
		
		<?}
		?>
		<?php 
		if($cert != null){ ?>
			<li class="navlink-<? echo $linkCount?> navLink"><a href="certs.php?station_id=<?echo $station_id;?>"> Calibration Certificate </a> </li>
		<?}
		$db=_open_mysql("worldData");

		?>
		
	</ul>
</div>
