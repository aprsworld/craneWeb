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
<title><? echo $title ?> RD Logger WC</title>
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
<div id="nav">
	<ul>
		<li><a href="index.php?station_id=<?echo $station_id;?>">Current Conditions</a></li>
		<li><a href="dailyConditions.php?station_id=<?echo $station_id;?>">Daily Conditions</a></li>

	</ul>
</div>
