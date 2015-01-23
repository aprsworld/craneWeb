<?
require_once $_SERVER["DOCUMENT_ROOT"] . "/world_config.php";
$db=_open_mysql("worldData");

session_start();

/* credentials supplied via POST take precedence to SESSION */
if ( "" != $_POST["username"] && "" !=$_POST["password"] ) {
	$_SESSION["username"]=$_POST["username"];
	$_SESSION["password"]=$_POST["password"];  
} 

/* check login against database */
$validLogin=FALSE;
$sql=sprintf("SELECT * FROM user WHERE username='%s' LIMIT 1",$_SESSION["username"]);
$query=mysql_query($sql,$db);
if ( mysql_num_rows($query) > 0 ) {
	$row=mysql_fetch_array($query);
	if ( $row["password"] == ($_SESSION["password"]) || $row["password"] == sha1($_SESSION["password"]) ) {
		$validLogin=TRUE;
	}
}

if ( ! $validLogin ) {
	//header("Location: http://data.aprsworld.com/login.php?requested=" . urlencode($_SERVER["REQUEST_URI"]));
	$jrow["auth"]="false";
	die(json_encode($jrow));	

	exit;
}

/* return admin state of $username for $serialNumber 
-1 not authorized
 0 not admin
 1 admin
*/
function authSerialNumber($username,$serialNumber,$db) {
	if ( 'admin'==$username )
		return 1;

	if ( FALSE != strpos($serialNumber,'_',1) ) {
		$serialNumber=substr($serialNumber,0,strpos($serialNumber,'_',1));
	}

	//$sql=sprintf("SELECT deviceInfo.serialNumber, userPerm.admin FROM deviceInfo LEFT JOIN userPerm ON userPerm.owner=deviceInfo.owner WHERE userPerm.username='%s' AND deviceInfo.serialNumber='%s'",$username,$serialNumber);
	$sql=sprintf("SELECT deviceInfo.serialNumber, userPerm.admin FROM deviceInfo LEFT JOIN userPerm ON userPerm.owner=deviceInfo.owner WHERE userPerm.username='%s' AND deviceInfo.userPermTable IS NULL AND deviceInfo.serialNumber='%s'",$username,$serialNumber);
	$query=mysql_query($sql,$db);

	if ( 0 == mysql_num_rows($query) )
		return -1;

	$r=mysql_fetch_array($query);
	return $r["admin"];
}

?>
