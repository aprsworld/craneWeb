<? 
require_once $_SERVER["DOCUMENT_ROOT"] . "/world_config.php";


//print_r($r);
$serial_id = $r["serialNumber"];

$headers = '<script language="javascript" type="text/javascript" src="js/date.js"></script>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.flot.js"></script>
<script> var station_id = "'.$station_id.'"; </script>
<script language="javascript" type="text/javascript" src="js/rdlogger.js"></script><script type="text/javascript" src="js/jQueryRotate.2.2.js"></script>';


$head=$title=$deviceInfo["displayName"];
$headline=sprintf("Crane Logger");
require_once "rdHead.php";
?>
