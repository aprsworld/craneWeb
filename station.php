<? 
require_once $_SERVER["DOCUMENT_ROOT"] . "/world_config.php";


//print_r($r);
$serial_id = $r["serialNumber"];

$headers = '<script language="javascript" type="text/javascript" src="http://mybergey.aprsworld.com/data/date.js"></script>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
<script language="javascript" type="text/javascript" src="http://mybergey.aprsworld.com/data/jquery.flot.js"></script>
<script> var station_id = "'.$serial_id.'"; </script>
<script language="javascript" type="text/javascript" src="js/cabu.js"></script><script type="text/javascript" src="http://ian.aprsworld.com/data/jQueryRotate.2.2.js"></script>';


$head=$title=$deviceInfo["displayName"];
$headline=sprintf("Crane Logger");
require_once "rdHead.php";
?>
